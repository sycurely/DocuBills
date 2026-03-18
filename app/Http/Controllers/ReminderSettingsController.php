<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Services\EmailService;
use App\Services\ReminderRuleService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReminderSettingsController extends Controller
{
    /**
     * Display reminder settings page.
     */
    public function index()
    {
        if (!has_permission('manage_reminder_settings')) {
            abort(403, 'Unauthorized action.');
        }

        $rules = ReminderRuleService::getRules();
        $templates = EmailTemplate::query()
            ->whereNull('deleted_at')
            ->orderBy('template_name')
            ->get(['id', 'template_name']);
        $previewInvoices = Invoice::query()
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get(['invoice_number', 'bill_to_name']);

        return view('settings.reminders', [
            'rules' => $rules,
            'templates' => $templates,
            'previewInvoices' => $previewInvoices,
        ]);
    }

    /**
     * Persist reminder settings.
     */
    public function update(Request $request)
    {
        if (!has_permission('manage_reminder_settings')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'rules' => 'required|array|min:1',
            'rules.*.id' => 'nullable|string|max:64',
            'rules.*.name' => 'required|string|max:255',
            'rules.*.enabled' => 'nullable|boolean',
            'rules.*.direction' => 'required|in:before,on,after',
            'rules.*.days' => 'required|integer|min:0|max:365',
            'rules.*.offset_days' => 'required|integer|min:-365|max:365',
        ]);

        $usedIds = [];
        $rules = [];

        foreach (($validated['rules'] ?? []) as $ruleEntry) {
            $rid = $this->resolveRuleId($ruleEntry, $usedIds);
            $usedIds[] = $rid;

            $normalizedRule = [
                'id' => $rid,
                'name' => trim((string) $ruleEntry['name']),
                'enabled' => (bool) ($ruleEntry['enabled'] ?? false),
                'direction' => $ruleEntry['direction'],
                'days' => (int) $ruleEntry['days'],
                'offset_days' => (int) $ruleEntry['offset_days'],
            ];
            $rules[] = $normalizedRule;
        }

        SettingService::set('invoice_email_reminders', json_encode($rules));
        SettingService::set('reminders_v2_enabled', '1');

        return redirect()->route('settings.reminders')->with('success', 'Reminder settings updated successfully.');
    }

    /**
     * Resolve a stable reminder rule ID; generate one when user input is blank.
     */
    private function resolveRuleId(array $rule, array $usedIds): string
    {
        $existingId = trim((string) ($rule['id'] ?? ''));
        if ($existingId !== '' && !in_array($existingId, $usedIds, true)) {
            return $existingId;
        }

        $name = trim((string) ($rule['name'] ?? ''));
        $direction = trim((string) ($rule['direction'] ?? 'rule'));
        $days = (int) ($rule['days'] ?? 0);
        $base = Str::slug($name, '_');
        if ($base === '') {
            $base = $direction . '_' . $days;
        }

        $candidate = Str::limit($base, 56, '');
        if ($candidate === '') {
            $candidate = 'rule';
        }

        $suffix = 1;
        $id = $candidate;
        while (in_array($id, $usedIds, true)) {
            $id = Str::limit($candidate, 56, '') . '_' . $suffix;
            $suffix++;
        }

        return $id;
    }

    /**
     * Preview reminder render for sample invoice.
     */
    public function preview(Request $request)
    {
        if (!has_permission('manage_reminder_settings')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'invoice_number' => 'required|string|exists:invoices,invoice_number',
            'rule_id' => 'required|string|max:64',
            'template_id' => 'required|integer|exists:email_templates,id',
        ]);

        $invoice = Invoice::query()
            ->whereNull('deleted_at')
            ->where('invoice_number', (string) $request->invoice_number)
            ->firstOrFail();
        $rules = ReminderRuleService::getRules();
        $rule = collect($rules)->firstWhere('id', $request->rule_id);
        if (!$rule) {
            return response()->json(['success' => false, 'message' => 'Rule not found'], 404);
        }

        $payload = EmailService::buildReminderPreview($invoice, $rule, (int) $request->template_id);

        return response()->json([
            'success' => true,
            'message' => 'Preview generated.',
            'recipient' => $payload['recipient']['email'] ?? 'N/A',
            'subject' => $payload['subject'],
            'body' => $payload['body'],
        ]);
    }
}
