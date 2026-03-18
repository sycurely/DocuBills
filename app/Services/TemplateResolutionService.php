<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\Invoice;
use Illuminate\Support\Collection;

class TemplateResolutionService
{
    /**
     * Resolve reminder template by explicit mapped id first, then rule notification type, then legacy category.
     */
    public static function resolveReminderTemplate(?string $ruleId, ?int $mappedTemplateId = null): ?EmailTemplate
    {
        if ($mappedTemplateId && $mappedTemplateId > 0) {
            $mapped = EmailTemplate::query()
                ->where('id', $mappedTemplateId)
                ->whereNull('deleted_at')
                ->first();
            if ($mapped) {
                return $mapped;
            }
        }

        if (!empty($ruleId)) {
            $byNotification = EmailTemplate::query()
                ->where('assigned_notification_type', $ruleId)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->first();
            if ($byNotification) {
                return $byNotification;
            }
        }

        return EmailTemplate::query()
            ->where('category', 'payment_reminder')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Resolve per-invoice delivery template.
     */
    public static function resolveInvoiceDeliveryTemplate(Invoice $invoice): ?EmailTemplate
    {
        $config = $invoice->emailConfiguration;
        $templateId = (int) ($config->delivery_template_id ?? 0);
        if ($templateId <= 0) {
            return null;
        }

        return EmailTemplate::query()
            ->where('id', $templateId)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Resolve per-invoice payment confirmation template.
     */
    public static function resolvePaymentConfirmationTemplate(Invoice $invoice): ?EmailTemplate
    {
        $config = $invoice->emailConfiguration;
        $templateId = (int) ($config->payment_confirmation_template_id ?? 0);
        if ($templateId <= 0) {
            return null;
        }

        return EmailTemplate::query()
            ->where('id', $templateId)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Resolve per-invoice reminder templates for a rule.
     *
     * @return Collection<int, EmailTemplate>
     */
    public static function resolveReminderTemplates(Invoice $invoice, string $ruleId): Collection
    {
        if (trim($ruleId) === '') {
            return collect();
        }

        return EmailTemplate::query()
            ->select('email_templates.*')
            ->join('invoice_reminder_template_bindings as bindings', 'bindings.template_id', '=', 'email_templates.id')
            ->where('bindings.invoice_id', $invoice->id)
            ->where('bindings.rule_id', $ruleId)
            ->whereNull('email_templates.deleted_at')
            ->orderBy('bindings.id')
            ->get();
    }
}
