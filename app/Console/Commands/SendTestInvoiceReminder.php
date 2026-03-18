<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Services\EmailService;
use App\Services\ReminderRuleService;
use Illuminate\Console\Command;

class SendTestInvoiceReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-test-reminder
                            {invoice_number : Invoice Number to use}
                            {rule_id : Reminder rule ID from settings.invoice_email_reminders}
                            {template_id : Email template ID to render}
                            {--to=visitwritersco@gmail.com : Recipient email override}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a single test invoice reminder email using a specific rule + template';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $invoiceNumber = trim((string) $this->argument('invoice_number'));
        if ($invoiceNumber === '') {
            $this->error('Invalid invoice_number. It cannot be empty.');
            return self::FAILURE;
        }

        $ruleId = trim((string) $this->argument('rule_id'));
        $templateIdRaw = (string) $this->argument('template_id');
        $to = trim((string) $this->option('to'));

        if ($ruleId === '') {
            $this->error('Invalid rule_id. It cannot be empty.');
            return self::FAILURE;
        }

        if (!ctype_digit($templateIdRaw) || (int) $templateIdRaw < 1) {
            $this->error('Invalid template_id. It must be a positive integer.');
            return self::FAILURE;
        }
        $templateId = (int) $templateIdRaw;

        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid recipient email: {$to}");
            return self::FAILURE;
        }

        $invoice = Invoice::query()
            ->whereNull('deleted_at')
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if (!$invoice) {
            $this->error("Invoice not found for Invoice Number {$invoiceNumber}.");
            return self::FAILURE;
        }

        $rules = ReminderRuleService::getRules();
        $rule = collect($rules)->first(function ($candidate) use ($ruleId) {
            return is_array($candidate) && (string) ($candidate['id'] ?? '') === $ruleId;
        });

        if (!$rule) {
            $this->error("Reminder rule not found for rule_id '{$ruleId}'.");
            return self::FAILURE;
        }

        $template = EmailTemplate::query()
            ->whereNull('deleted_at')
            ->find($templateId);
        if (!$template) {
            $this->error("Template not found for template_id '{$templateId}'.");
            return self::FAILURE;
        }

        $missingEmailSettings = EmailService::missingEmailSettings();
        if (!empty($missingEmailSettings)) {
            $this->error('Missing email settings in database: ' . implode(', ', $missingEmailSettings));
            $this->line('Configure required settings keys before running this test command.');
            return self::FAILURE;
        }

        $payload = EmailService::buildReminderPreview($invoice, $rule, $templateId);
        $recipientName = (string) ($payload['recipient']['name'] ?? 'Valued Client');

        $pdfPath = "invoices/{$invoice->invoice_number}.pdf";
        $sent = EmailService::sendRenderedReminderNow(
            toEmail: $to,
            toName: $recipientName,
            subject: (string) $payload['subject'],
            body: (string) $payload['body'],
            ccList: (array) ($payload['cc'] ?? []),
            bccList: (array) ($payload['bcc'] ?? []),
            invoice: $invoice,
            pdfPath: $pdfPath
        );

        $this->line('Test reminder details:');
        $this->line("  Invoice Number: {$invoice->invoice_number}");
        $this->line("  Rule ID: {$ruleId}");
        $this->line('  Template ID: ' . (string) $templateId);
        $this->line("  Recipient: {$to}");
        $this->line('  Subject: ' . (string) $payload['subject']);

        if (!$sent) {
            $this->error('Test reminder send failed. Check mail transport logs for details.');
            return self::FAILURE;
        }

        $this->info('Test reminder sent successfully.');
        return self::SUCCESS;
    }
}
