<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceCustomReminder;
use App\Models\InvoiceReminderLog;
use App\Models\InvoiceReminderTemplateBinding;
use App\Services\EmailService;
use App\Services\ReminderRuleService;
use App\Services\TemplateResolutionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated invoice payment reminders using invoice bindings with settings/template fallback';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rules = ReminderRuleService::getRules();
        $templateMap = ReminderRuleService::getTemplateMap();

        $metrics = [
            'candidates' => 0,
            'queued' => 0,
            'skipped_no_template' => 0,
            'skipped_no_recipient' => 0,
            'dedup_skipped' => 0,
            'skipped_new_invoice' => 0,
            'disabled_rules' => 0,
            'custom_candidates' => 0,
            'custom_sent' => 0,
            'custom_failed' => 0,
            'custom_skipped' => 0,
        ];

        foreach ($rules as $rule) {
            if (!(bool) ($rule['enabled'] ?? false)) {
                $metrics['disabled_rules']++;
                continue;
            }

            $ruleId = (string) ($rule['id'] ?? '');
            if ($ruleId === '') {
                continue;
            }

            $targetDueDate = ReminderRuleService::targetDueDateForRule($rule);
            $statusSentScope = $targetDueDate;

            $query = Invoice::query()
                ->where('status', 'Unpaid')
                ->whereNull('deleted_at')
                ->whereDate('due_date', '=', $targetDueDate);

            $invoices = $query->get();
            foreach ($invoices as $invoice) {
                $metrics['candidates']++;
                $createdDate = optional($invoice->created_at)->toDateString();
                if ($createdDate === $statusSentScope) {
                    $metrics['skipped_new_invoice']++;
                    continue;
                }

                $bindings = InvoiceReminderTemplateBinding::query()
                    ->where('invoice_id', $invoice->id)
                    ->where('rule_id', $ruleId)
                    ->orderBy('id')
                    ->get(['template_id']);

                $templateIds = $bindings->pluck('template_id')
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0)
                    ->values()
                    ->all();

                if (empty($templateIds)) {
                    $mappedTemplateId = isset($templateMap[$ruleId]) && (int) $templateMap[$ruleId] > 0
                        ? (int) $templateMap[$ruleId]
                        : null;
                    $resolvedTemplate = TemplateResolutionService::resolveReminderTemplate($ruleId, $mappedTemplateId);
                    if ($resolvedTemplate) {
                        $templateIds[] = (int) $resolvedTemplate->id;
                    }
                }

                $templateIds = array_values(array_unique($templateIds));
                if (empty($templateIds)) {
                    $metrics['skipped_no_template']++;
                    continue;
                }

                $billTo = $invoice->bill_to_json ?? [];
                $recipient = (string) ($billTo['Email'] ?? '');
                if ($recipient === '' && $invoice->relationLoaded('client') && $invoice->client) {
                    $recipient = (string) ($invoice->client->email ?? '');
                }
                if ($recipient === '') {
                    $invoice->loadMissing('client');
                    $recipient = (string) ($billTo['Email'] ?? ($invoice->client->email ?? ''));
                }
                if ($recipient === '') {
                    $metrics['skipped_no_recipient']++;
                    continue;
                }

                foreach ($templateIds as $templateId) {

                    $alreadySentForTemplate = InvoiceReminderLog::query()
                        ->where('invoice_id', $invoice->id)
                        ->where('reminder_type', $ruleId)
                        ->where('rule_id', $ruleId)
                        ->where('template_id', $templateId)
                        ->whereDate('status_sent_scope', $statusSentScope)
                        ->where('status', 'sent')
                        ->exists();

                    if ($alreadySentForTemplate) {
                        $metrics['dedup_skipped']++;
                        continue;
                    }

                    EmailService::queueReminderEmail($invoice, $rule, $templateId, $statusSentScope);
                    $metrics['queued']++;
                }
            }
        }

        $this->processCustomReminders($metrics);

        $this->info('Invoice reminders processed.');
        foreach ($metrics as $key => $value) {
            $this->line(sprintf('%s: %d', $key, $value));
        }

        Log::info('Invoice reminder run summary', $metrics);

        return self::SUCCESS;
    }

    /**
     * Send custom reminder schedules due today.
     */
    private function processCustomReminders(array &$metrics): void
    {
        $today = now()->toDateString();
        $customReminders = InvoiceCustomReminder::query()
            ->whereDate('reminder_date', $today)
            ->where('status', 'pending')
            ->with('invoice')
            ->get();

        foreach ($customReminders as $reminder) {
            $metrics['custom_candidates']++;
            $invoice = $reminder->invoice;
            if (!$invoice || $invoice->deleted_at || !$invoice->isUnpaid()) {
                $metrics['custom_skipped']++;
                $reminder->update([
                    'status' => 'skipped',
                    'last_error' => 'Invoice missing, deleted, or not unpaid.',
                ]);
                continue;
            }

            $rule = [
                'id' => 'custom_date',
                'name' => 'Custom reminder date',
                'direction' => 'custom',
                'days' => 0,
                'offset_days' => 0,
            ];
            $statusSentScope = $today;
            $templateId = (int) ($reminder->template_id ?? 0);

            $extra = [
                'reminder_scheduled_for' => $reminder->reminder_date?->format('Y-m-d') ?? '',
                'reminder_offset_days' => $reminder->offset_days !== null ? (string) $reminder->offset_days : '',
                'reminder_offset_base' => (string) ($reminder->offset_base ?? ''),
            ];
            $sent = EmailService::sendReminderNow($invoice, $rule, $templateId, $statusSentScope, $extra);
            if ($sent) {
                $metrics['custom_sent']++;
                $reminder->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'last_error' => null,
                ]);
            } else {
                $metrics['custom_failed']++;
                $reminder->update([
                    'status' => 'failed',
                    'last_error' => 'Reminder send failed.',
                ]);
            }
        }
    }
}
