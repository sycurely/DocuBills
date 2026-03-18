<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['slug' => 'invoice_delivery', 'label' => 'Invoice Delivery'],
            ['slug' => 'payment_confirmation', 'label' => 'Payment Confirmation'],
            ['slug' => 'before_due', 'label' => 'Reminder (Before Due)'],
            ['slug' => 'on_due', 'label' => 'Reminder (On Due Date)'],
            ['slug' => 'after_3', 'label' => 'Reminder (3 Days After Due)'],
            ['slug' => 'after_7', 'label' => 'Reminder (7 Days After Due)'],
            ['slug' => 'after_14', 'label' => 'Reminder (14 Days After Due)'],
            ['slug' => 'after_21', 'label' => 'Reminder (21 Days After Due)'],
        ];

        foreach ($types as $type) {
            DB::table('notification_types')->updateOrInsert(
                ['slug' => $type['slug']],
                [
                    'label' => $type['label'],
                    'deleted_at' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
