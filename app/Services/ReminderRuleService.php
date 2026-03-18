<?php

namespace App\Services;

class ReminderRuleService
{
    /**
     * Get normalized reminder rules from v2 setting with legacy fallback.
     */
    public static function getRules(): array
    {
        $raw = SettingService::getSetting('invoice_email_reminders', '');
        $decoded = json_decode((string) $raw, true);

        if (is_array($decoded) && !empty($decoded)) {
            return self::normalizeRules($decoded);
        }

        return self::legacyRules();
    }

    /**
     * Get reminder template mapping (rule_id => template_id) from settings.
     */
    public static function getTemplateMap(): array
    {
        $raw = SettingService::getSetting('invoice_email_reminder_templates', '{}');
        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $normalized = [];
        foreach ($decoded as $ruleId => $templateId) {
            $rid = trim((string) $ruleId);
            if ($rid === '') {
                continue;
            }
            $normalized[$rid] = (int) $templateId;
        }

        return $normalized;
    }

    /**
     * Build YYYY-MM-DD due-date target for a rule relative to provided "today".
     */
    public static function targetDueDateForRule(array $rule, ?\DateTimeInterface $today = null): string
    {
        $offset = (int) ($rule['offset_days'] ?? 0);
        $base = $today ? \Carbon\Carbon::instance(\DateTime::createFromInterface($today)) : now();

        // today = due_date + offset  => due_date = today - offset
        return $base->copy()->startOfDay()->subDays($offset)->toDateString();
    }

    /**
     * Human reminder label for template placeholders.
     */
    public static function reminderTypeLabel(array $rule): string
    {
        $name = trim((string) ($rule['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $id = (string) ($rule['id'] ?? '');
        return match ($id) {
            'before_due' => 'Reminder (Before Due)',
            'on_due' => 'Reminder (On Due Date)',
            'after_3' => 'Reminder (3 Days After Due)',
            'after_7' => 'Reminder (7 Days After Due)',
            'after_14' => 'Reminder (14 Days After Due)',
            'after_21' => 'Reminder (21 Days After Due)',
            'custom_date' => 'Reminder (Custom Date)',
            default => 'Invoice Reminder',
        };
    }

    private static function normalizeRules(array $rules): array
    {
        $normalized = [];
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }

            $id = trim((string) ($rule['id'] ?? ''));
            if ($id === '') {
                continue;
            }

            $direction = strtolower(trim((string) ($rule['direction'] ?? '')));
            if (!in_array($direction, ['before', 'on', 'after'], true)) {
                continue;
            }

            $days = max(0, (int) ($rule['days'] ?? 0));
            $offset = (int) ($rule['offset_days'] ?? ($direction === 'before' ? -$days : $days));

            $normalized[] = [
                'id' => $id,
                'name' => trim((string) ($rule['name'] ?? '')),
                'enabled' => (bool) ($rule['enabled'] ?? false),
                'direction' => $direction,
                'days' => $days,
                'offset_days' => $offset,
            ];
        }

        return $normalized;
    }

    private static function legacyRules(): array
    {
        $beforeDays = (int) SettingService::getSetting('reminder_before_due', 0);

        return [
            ['id' => 'before_due', 'name' => 'Before due date', 'enabled' => $beforeDays > 0, 'direction' => 'before', 'days' => max($beforeDays, 0), 'offset_days' => 0 - max($beforeDays, 0)],
            ['id' => 'on_due', 'name' => 'On due date', 'enabled' => self::toBool(SettingService::getSetting('reminder_on_due', '1')), 'direction' => 'on', 'days' => 0, 'offset_days' => 0],
            ['id' => 'after_3', 'name' => '3 days after due', 'enabled' => self::toBool(SettingService::getSetting('reminder_after_3', '1')), 'direction' => 'after', 'days' => 3, 'offset_days' => 3],
            ['id' => 'after_7', 'name' => '7 days after due', 'enabled' => self::toBool(SettingService::getSetting('reminder_after_7', '1')), 'direction' => 'after', 'days' => 7, 'offset_days' => 7],
            ['id' => 'after_14', 'name' => '14 days after due', 'enabled' => self::toBool(SettingService::getSetting('reminder_after_14', '1')), 'direction' => 'after', 'days' => 14, 'offset_days' => 14],
            ['id' => 'after_21', 'name' => '21 days after due', 'enabled' => self::toBool(SettingService::getSetting('reminder_after_21', '1')), 'direction' => 'after', 'days' => 21, 'offset_days' => 21],
        ];
    }

    private static function toBool($raw): bool
    {
        return in_array((string) $raw, ['1', 'true', 'yes', 'on'], true);
    }
}
