<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInvoiceReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $invoiceId,
        public array $rule,
        public int $templateId,
        public string $statusSentScope
    ) {
    }

    public function handle(): void
    {
        $invoice = Invoice::query()->find($this->invoiceId);
        if (!$invoice) {
            return;
        }

        EmailService::sendReminderNow($invoice, $this->rule, $this->templateId, $this->statusSentScope);
    }
}
