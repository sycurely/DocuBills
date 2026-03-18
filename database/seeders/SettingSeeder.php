<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Company Settings
            ['key_name' => 'company_name', 'key_value' => 'DocuBills'],
            ['key_name' => 'company_email', 'key_value' => ''],
            ['key_name' => 'company_phone', 'key_value' => ''],
            ['key_name' => 'company_address', 'key_value' => ''],
            ['key_name' => 'company_logo', 'key_value' => ''],
            ['key_name' => 'app_logo_url', 'key_value' => ''],

            // Invoice Settings
            ['key_name' => 'invoice_prefix', 'key_value' => 'INV'],
            ['key_name' => 'currency_code', 'key_value' => 'USD'],
            ['key_name' => 'currency_symbol', 'key_value' => '$'],

            // Payment Settings
            ['key_name' => 'stripe_publishable_key', 'key_value' => ''],
            ['key_name' => 'stripe_secret_key', 'key_value' => ''],
            ['key_name' => 'stripe_webhook_secret', 'key_value' => ''],
            ['key_name' => 'test_mode', 'key_value' => '1'],
            ['key_name' => 'payment_provider', 'key_value' => 'stripe'],

            // Email Settings
            ['key_name' => 'smtp_host', 'key_value' => ''],
            ['key_name' => 'smtp_port', 'key_value' => '587'],
            ['key_name' => 'smtp_username', 'key_value' => ''],
            ['key_name' => 'smtp_password', 'key_value' => ''],
            ['key_name' => 'email_from_name', 'key_value' => 'DocuBills'],
            ['key_name' => 'email_from_address', 'key_value' => ''],

            // Security Settings
            ['key_name' => 'cron_secret', 'key_value' => bin2hex(random_bytes(32))],
            ['key_name' => 'session_timeout', 'key_value' => '30'],

            // Reminder Settings
            ['key_name' => 'reminder_before_due', 'key_value' => '0'],
            ['key_name' => 'reminder_on_due', 'key_value' => '1'],
            ['key_name' => 'reminder_after_3', 'key_value' => '1'],
            ['key_name' => 'reminder_after_7', 'key_value' => '1'],
            ['key_name' => 'reminder_after_14', 'key_value' => '1'],
            ['key_name' => 'reminder_after_21', 'key_value' => '1'],
            ['key_name' => 'invoice_email_reminders', 'key_value' => json_encode([
                ['id' => 'before_due', 'name' => 'Before due date', 'enabled' => true, 'direction' => 'before', 'days' => 3, 'offset_days' => -3],
                ['id' => 'on_due', 'name' => 'On due date', 'enabled' => true, 'direction' => 'on', 'days' => 0, 'offset_days' => 0],
                ['id' => 'after_3', 'name' => '3 days after due', 'enabled' => true, 'direction' => 'after', 'days' => 3, 'offset_days' => 3],
                ['id' => 'after_7', 'name' => '7 days after due', 'enabled' => true, 'direction' => 'after', 'days' => 7, 'offset_days' => 7],
                ['id' => 'after_14', 'name' => '14 days after due', 'enabled' => true, 'direction' => 'after', 'days' => 14, 'offset_days' => 14],
                ['id' => 'after_21', 'name' => '21 days after due', 'enabled' => true, 'direction' => 'after', 'days' => 21, 'offset_days' => 21],
            ])],
            ['key_name' => 'invoice_email_reminder_templates', 'key_value' => json_encode((object) [])],
            ['key_name' => 'reminders_v2_enabled', 'key_value' => '1'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key_name' => $setting['key_name']],
                $setting
            );
        }
    }
}
