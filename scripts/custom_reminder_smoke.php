<?php

declare(strict_types=1);

use App\Http\Controllers\InvoiceController;
use App\Models\Invoice;
use App\Models\InvoiceCustomReminder;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

require __DIR__ . '/../vendor/autoload.php';

$basePath = realpath(__DIR__ . '/..');
if ($basePath === false) {
    fwrite(STDERR, "Unable to resolve base path.\n");
    exit(1);
}

$dbPath = $basePath . '/storage/testing.sqlite';
@unlink($dbPath);
touch($dbPath);

putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=' . $dbPath);
putenv('CACHE_DRIVER=array');
putenv('SESSION_DRIVER=array');
putenv('QUEUE_CONNECTION=sync');

$app = require $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Artisan::call('migrate', ['--force' => true]);
Artisan::call('db:seed', ['--class' => Database\Seeders\SettingSeeder::class, '--force' => true]);
Setting::set('email_provider', 'log');

$invoice = Invoice::create([
    'invoice_number' => 'INV-TEST-01',
    'bill_to_name' => 'Acme Inc',
    'bill_to_json' => [
        'Company Name' => 'Acme Inc',
        'Contact Name' => 'Jane Doe',
        'Email' => 'jane@example.com',
    ],
    'total_amount' => 1234.56,
    'invoice_date' => Carbon::now()->startOfDay(),
    'due_date' => Carbon::now()->addDays(10)->startOfDay(),
    'status' => 'Unpaid',
    'currency_code' => 'USD',
    'currency_display' => '$',
]);

$controller = $app->make(InvoiceController::class);
$ref = new ReflectionClass($controller);
$method = $ref->getMethod('persistCustomReminderSchedule');
$method->setAccessible(true);
$method->invoke($controller, $invoice, Carbon::now()->toDateString(), null);

$customReminder = InvoiceCustomReminder::query()->first();
if (!$customReminder) {
    fwrite(STDERR, "Custom reminder was not saved.\n");
    exit(1);
}

Artisan::call('invoices:send-reminders');

$customReminder->refresh();
$status = $customReminder->status;

$mailLog = $basePath . '/storage/logs/mail.log';
$mailLogContents = is_file($mailLog) ? file_get_contents($mailLog) : '';
$hasInvoiceNumber = str_contains($mailLogContents, 'INV-TEST-01');
$hasScheduledDate = str_contains($mailLogContents, Carbon::now()->toDateString());

echo "Custom reminder status: {$status}\n";
echo "Mail log has invoice number: " . ($hasInvoiceNumber ? 'yes' : 'no') . "\n";
echo "Mail log has scheduled date: " . ($hasScheduledDate ? 'yes' : 'no') . "\n";
