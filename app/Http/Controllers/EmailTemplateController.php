<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of email templates.
     */
    public function index()
    {
        if (!has_permission('access_email_templates_page')) {
            abort(403, 'Unauthorized action.');
        }

        $templates = EmailTemplate::with('creator')
            ->whereNull('deleted_at')
            ->orderBy('template_name')
            ->get();

        return view('email-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new email template.
     */
    public function create()
    {
        if (!has_permission('add_email_template')) {
            abort(403, 'Unauthorized action.');
        }

        return view('email-templates.create');
    }

    /**
     * Store a newly created email template.
     */
    public function store(Request $request)
    {
        if (!has_permission('add_email_template')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'html_content' => 'nullable|string',
            'design_json' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }

                    json_decode((string) $value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::warning('Invalid design_json on template store', [
                            'user_id' => Auth::id(),
                            'error' => json_last_error_msg(),
                        ]);
                        $fail('The design data must be valid JSON.');
                    }
                },
            ],
            'cc_emails' => 'nullable|string',
            'bcc_emails' => 'nullable|string',
        ]);

        if (empty($validated['html_content'])) {
            $validated['html_content'] = $validated['body'];
        }

        $validated['created_by'] = Auth::id();

        EmailTemplate::create($validated);

        return redirect()->route('email-templates.index')->with('success', 'Email template created successfully.');
    }

    /**
     * Display the specified email template.
     */
    public function show(EmailTemplate $emailTemplate)
    {
        if (!has_permission('access_email_templates_page')) {
            abort(403, 'Unauthorized action.');
        }

        return view('email-templates.show', compact('emailTemplate'));
    }

    /**
     * Show the form for editing the specified email template.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        if (!has_permission('edit_email_template')) {
            abort(403, 'Unauthorized action.');
        }

        return view('email-templates.edit', compact('emailTemplate'));
    }

    /**
     * Update the specified email template.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        if (!has_permission('edit_email_template')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'html_content' => 'nullable|string',
            'design_json' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($emailTemplate): void {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }

                    json_decode((string) $value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::warning('Invalid design_json on template update', [
                            'user_id' => Auth::id(),
                            'template_id' => $emailTemplate->id,
                            'error' => json_last_error_msg(),
                        ]);
                        $fail('The design data must be valid JSON.');
                    }
                },
            ],
            'cc_emails' => 'nullable|string',
            'bcc_emails' => 'nullable|string',
        ]);

        if (empty($validated['html_content'])) {
            $validated['html_content'] = $validated['body'];
        }

        $emailTemplate->update($validated);

        return redirect()->route('email-templates.index')->with('success', 'Email template updated successfully.');
    }

    /**
     * Remove the specified email template.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        if (!has_permission('delete_email_template')) {
            abort(403, 'Unauthorized action.');
        }

        $emailTemplate->delete();

        return redirect()->route('email-templates.index')->with('success', 'Email template deleted successfully.');
    }

    /**
     * Get email template by category (AJAX).
     */
    public function getByCategory(Request $request)
    {
        if (!has_permission('access_email_templates_page')) {
            abort(403, 'Unauthorized action.');
        }

        $templateId = (int) $request->input('template_id', 0);
        $template = $templateId > 0
            ? EmailTemplate::where('id', $templateId)->whereNull('deleted_at')->first()
            : null;

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $template->id,
                'template_name' => $template->template_name,
                'subject' => $template->subject,
                'body' => $template->getRenderableBody(),
                'html_content' => $template->html_content,
                'design_json' => $template->design_json,
                'cc_emails' => $template->cc_emails,
                'bcc_emails' => $template->bcc_emails,
            ],
        ]);
    }
}
