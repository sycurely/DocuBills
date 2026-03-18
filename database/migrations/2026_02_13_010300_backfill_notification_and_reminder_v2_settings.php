<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        if (
            Schema::hasTable('email_templates')
            && Schema::hasColumn('email_templates', 'assigned_notification_type')
            && Schema::hasColumn('email_templates', 'category')
        ) {
            DB::table('email_templates')
                ->whereNull('assigned_notification_type')
                ->whereNotNull('category')
                ->orderBy('id')
                ->get(['id', 'category'])
                ->each(function ($template) {
                    $mapped = match ((string) $template->category) {
                        'invoice_delivery' => 'invoice_delivery',
                        'payment_confirmation' => 'payment_confirmation',
                        'payment_reminder' => 'on_due',
                        default => null,
                    };

                    if ($mapped) {
                        DB::table('email_templates')
                            ->where('id', $template->id)
                            ->update(['assigned_notification_type' => $mapped]);
                    }
                });
        }

        $existingRulesJson = DB::table('settings')->where('key_name', 'invoice_email_reminders')->value('key_value');
        if (empty($existingRulesJson)) {
            $rules = $this->buildRulesFromLegacySettings();
            $this->upsertSetting('invoice_email_reminders', json_encode($rules));
        }

        $existingMapJson = DB::table('settings')->where('key_name', 'invoice_email_reminder_templates')->value('key_value');
        if (empty($existingMapJson)) {
            $this->upsertSetting('invoice_email_reminder_templates', json_encode((object) []));
        }

        $existingFlag = DB::table('settings')->where('key_name', 'reminders_v2_enabled')->value('key_value');
        if ($existingFlag === null) {
            $this->upsertSetting('reminders_v2_enabled', '1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally keep migrated settings/data.
    }

    private function buildRulesFromLegacySettings(): array
    {
        $beforeDays = (int) ($this->getSetting('reminder_before_due', '0') ?? 0);

        return [
            ['id' => 'before_due', 'name' => 'Before due date', 'enabled' => $beforeDays > 0, 'direction' => 'before', 'days' => max($beforeDays, 0), 'offset_days' => 0 - max($beforeDays, 0)],
            ['id' => 'on_due', 'name' => 'On due date', 'enabled' => $this->legacyBool('reminder_on_due', true), 'direction' => 'on', 'days' => 0, 'offset_days' => 0],
            ['id' => 'after_3', 'name' => '3 days after due', 'enabled' => $this->legacyBool('reminder_after_3', true), 'direction' => 'after', 'days' => 3, 'offset_days' => 3],
            ['id' => 'after_7', 'name' => '7 days after due', 'enabled' => $this->legacyBool('reminder_after_7', true), 'direction' => 'after', 'days' => 7, 'offset_days' => 7],
            ['id' => 'after_14', 'name' => '14 days after due', 'enabled' => $this->legacyBool('reminder_after_14', true), 'direction' => 'after', 'days' => 14, 'offset_days' => 14],
            ['id' => 'after_21', 'name' => '21 days after due', 'enabled' => $this->legacyBool('reminder_after_21', true), 'direction' => 'after', 'days' => 21, 'offset_days' => 21],
        ];
    }

    private function legacyBool(string $key, bool $default): bool
    {
        $raw = (string) ($this->getSetting($key, $default ? '1' : '0') ?? ($default ? '1' : '0'));
        return in_array($raw, ['1', 'true', 'yes', 'on'], true);
    }

    private function getSetting(string $key, ?string $default = null): ?string
    {
        $value = DB::table('settings')->where('key_name', $key)->value('key_value');
        return $value ?? $default;
    }

    private function upsertSetting(string $key, string $value): void
    {
        $hasUpdatedAt = Schema::hasColumn('settings', 'updated_at');
        $hasCreatedAt = Schema::hasColumn('settings', 'created_at');

        $exists = DB::table('settings')->where('key_name', $key)->exists();
        if ($exists) {
            $payload = [
                'key_value' => $value,
            ];
            if ($hasUpdatedAt) {
                $payload['updated_at'] = now();
            }
            DB::table('settings')->where('key_name', $key)->update($payload);
            return;
        }

        $payload = [
            'key_name' => $key,
            'key_value' => $value,
        ];
        if ($hasCreatedAt) {
            $payload['created_at'] = now();
        }
        if ($hasUpdatedAt) {
            $payload['updated_at'] = now();
        }

        DB::table('settings')->insert($payload);
    }
};
