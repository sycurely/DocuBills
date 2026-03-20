<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Invoice <?php echo e($invoiceNumber); ?></title>
  <style>
    @page {
      size: A4 portrait;
      margin: 11mm;
    }
    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      color: #10233c;
      font-size: 10px;
      margin: 0;
      line-height: 1.25;
      background: #ffffff;
    }
    @media print {
      body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }
    .invoice-shell {
      width: 100%;
    }
    .invoice-header {
      border-bottom: 1px solid #dbe2ea;
      padding-bottom: 8px;
      margin-bottom: 8px;
    }
    .header-grid {
      width: 100%;
      border-collapse: collapse;
    }
    .header-grid td {
      border: none;
      vertical-align: top;
      padding: 0;
    }
    .company-col {
      width: 56%;
      padding-right: 12px;
    }
    .billto-col {
      width: 44%;
      text-align: right;
    }
    .logo-wrap {
      margin-bottom: 6px;
      min-height: 24px;
    }
    .logo-wrap img {
      max-width: 120px;
      max-height: 44px;
    }
    .logo-fallback {
      display: inline-block;
      font-size: 15px;
      font-weight: 700;
      color: #0b4bd8;
    }
    .company-name {
      font-size: 15px;
      font-weight: 700;
      margin: 0 0 2px;
      color: #0d274f;
    }
    .muted {
      color: #4e6078;
    }
    .invoice-band {
      margin-top: 6px;
      display: inline-block;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      background: <?php echo e($titleBg); ?>;
      color: <?php echo e($titleText); ?>;
    }
    .billto-title {
      font-weight: 700;
      margin-bottom: 6px;
      color: #10233c;
    }
    .invoice-meta {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .invoice-meta td {
      border: none;
      padding: 0;
      vertical-align: top;
    }
    .meta-right {
      text-align: right;
    }
    .meta-label {
      font-size: 9px;
      color: #4e6078;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }
    .meta-value {
      font-size: 10px;
      font-weight: 600;
      color: #10233c;
      margin-bottom: 2px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    .line-table {
      margin-bottom: 8px;
    }
    .line-table-wide {
      table-layout: fixed;
    }
    .line-table-wide th,
    .line-table-wide td {
      font-size: 8px;
      padding: 3px 4px;
      word-break: break-word;
    }
    .line-table-wide td {
      white-space: normal;
    }
    .line-table th,
    .line-table td {
      border: 1px solid #dbe2ea;
      padding: 4px 5px;
      vertical-align: top;
    }
    .line-table th {
      background: #f3f6fb;
      color: #153157;
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .line-table td {
      font-size: 10px;
    }
    .amount {
      text-align: right;
      white-space: nowrap;
    }
    .line-meta {
      margin-top: 2px;
      font-size: 8px;
      color: #5a6e89;
    }
    .line-meta span {
      white-space: nowrap;
    }
    .totals-wrap {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .totals-wrap td {
      border: none;
      vertical-align: top;
      padding: 0;
    }
    .totals-box {
      width: 46%;
      margin-left: auto;
    }
    .totals-box table td {
      border: none;
      padding: 1px 0;
      font-size: 10px;
    }
    .totals-box .label {
      color: #3d4f67;
    }
    .totals-box .value {
      text-align: right;
      font-weight: 600;
      color: #10233c;
      white-space: nowrap;
    }
    .totals-box .grand .label,
    .totals-box .grand .value {
      font-size: 11px;
      font-weight: 700;
      color: #10233c;
      padding-top: 3px;
      border-top: 1px solid #dbe2ea;
    }
    .tax-summary {
      margin-top: 7px;
      border-top: 1px solid #dbe2ea;
      padding-top: 6px;
    }
    .pay-now-row {
      margin-top: 4px;
      margin-bottom: 7px;
      text-align: left;
    }
    .pay-now-button {
      display: inline-block;
      background: #0d6efd;
      color: #ffffff;
      text-decoration: none;
      padding: 4px 9px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 0.2px;
    }
    .pay-now-button-disabled {
      background: #8a94a8;
      pointer-events: none;
    }
    .tax-summary h4 {
      margin: 0 0 3px;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #2a3f60;
    }
    .tax-summary table td {
      border: none;
      padding: 1px 0;
      font-size: 9px;
    }
    .tax-summary table td:last-child {
      text-align: right;
      white-space: nowrap;
      font-weight: 600;
    }
    .invoice-footer {
      margin-top: 8px;
      border-top: 1px solid #dbe2ea;
      padding-top: 6px;
      font-size: 8px;
      color: #526784;
      text-align: center;
    }
  </style>
</head>
<body>
  <?php
    $summary = is_array($taxSummary ?? null) ? $taxSummary : [];
    $netTotal = (float) ($summary['net_total'] ?? $subtotal ?? 0);
    $lineTaxTotalDisplay = (float) ($summary['line_tax_total'] ?? $lineTaxTotal ?? 0);
    $subtotalDisplay = (float) ($summary['subtotal'] ?? ($subtotal ?? 0));
    $invoiceSubtotalTaxes = is_array($summary['invoice_subtotal_taxes'] ?? null) ? $summary['invoice_subtotal_taxes'] : [];
    $invoiceAdjustedTaxes = is_array($summary['invoice_adjusted_taxes'] ?? null) ? $summary['invoice_adjusted_taxes'] : [];
    $adjustedSubtotal = (float) ($summary['adjusted_subtotal'] ?? $subtotalDisplay);
    $grandTotal = (float) ($summary['grand_total'] ?? $total ?? 0);
    $showAdjustedSubtotal = !empty($invoiceSubtotalTaxes) || !empty($invoiceAdjustedTaxes);
    $showBankDetails = (bool) ($showBankDetails ?? true);

    $company = is_array($company ?? null) ? $company : [];
    $companyName = trim((string) ($company['name'] ?? 'DocuBills'));
    $companyAddress = trim((string) ($company['address'] ?? ''));
    $companyPhone = trim((string) ($company['phone'] ?? ''));
    $companyEmail = trim((string) ($company['email'] ?? ''));
    $companyGst = trim((string) ($company['gst_hst'] ?? ''));
    $companyLogo = trim((string) ($company['logo'] ?? ''));
    $invoiceFooter = trim((string) ($company['invoice_footer'] ?? ''));

    $meta = is_array($documentMeta ?? null) ? $documentMeta : [];
    $displayCurrencyCode = trim((string) ($meta['currency_code'] ?? ''));
    $displayCurrencySymbol = trim((string) ($meta['currency_display'] ?? ($currencyDisplay ?? '')));

    $bankAccountHolder = trim((string) ($billTo['Bank Account Holder'] ?? ''));
    $bankName = trim((string) ($billTo['Bank Name'] ?? ''));
    $bankAccountNumber = trim((string) ($billTo['Bank Account Number'] ?? ''));
    $bankIban = trim((string) ($billTo['Bank IBAN'] ?? ''));
    $bankSwift = trim((string) ($billTo['Bank SWIFT'] ?? ''));
    $bankRoutingCode = trim((string) ($billTo['Bank Routing Code'] ?? ''));
    $paymentInstructions = trim((string) ($billTo['Payment Instructions'] ?? ''));
    $hasAnyBankDetail = $bankAccountHolder !== '' || $bankName !== '' || $bankAccountNumber !== '' || $bankIban !== '' || $bankSwift !== '' || $bankRoutingCode !== '' || $paymentInstructions !== '';
    $effectiveStatus = strtolower(trim((string) ($invoiceStatus ?? 'Unpaid')));
    $normalizedPaymentLink = trim((string) ($paymentLink ?? ''));
    $showPayNow = $effectiveStatus === 'unpaid';
    $payNowButtonClass = $normalizedPaymentLink === '' ? 'pay-now-button pay-now-button-disabled' : 'pay-now-button';
    $payNowHref = $normalizedPaymentLink === '' ? '#' : $normalizedPaymentLink;
  ?>

  <div class="invoice-shell">
    <div class="invoice-header">
      <table class="header-grid">
        <tr>
          <td class="company-col">
            <div class="logo-wrap">
              <?php if($companyLogo !== ''): ?>
                <img src="<?php echo e($companyLogo); ?>" alt="Company logo">
              <?php else: ?>
                <span class="logo-fallback"><?php echo e($companyName !== '' ? $companyName : 'DocuBills'); ?></span>
              <?php endif; ?>
            </div>
            <div class="company-name"><?php echo e($companyName); ?></div>
            <?php if($companyAddress !== ''): ?><div class="muted"><?php echo e($companyAddress); ?></div><?php endif; ?>
            <?php if($companyPhone !== ''): ?><div class="muted"><?php echo e($companyPhone); ?></div><?php endif; ?>
            <?php if($companyEmail !== ''): ?><div class="muted"><?php echo e($companyEmail); ?></div><?php endif; ?>
            <?php if($companyGst !== ''): ?><div class="muted">GST/HST: <?php echo e($companyGst); ?></div><?php endif; ?>
            <div class="invoice-band">Invoice</div>
          </td>
          <td class="billto-col">
            <div class="billto-title">Bill To</div>
            <div><?php echo e($billTo['Company Name'] ?? ''); ?></div>
            <?php if(!empty($billTo['Contact Name'])): ?><div><?php echo e($billTo['Contact Name']); ?></div><?php endif; ?>
            <?php if(!empty($billTo['Address'])): ?><div><?php echo e($billTo['Address']); ?></div><?php endif; ?>
            <?php if(!empty($billTo['Phone'])): ?><div><?php echo e($billTo['Phone']); ?></div><?php endif; ?>
            <?php if(!empty($billTo['Email'])): ?><div><?php echo e($billTo['Email']); ?></div><?php endif; ?>
            <?php if($showBankDetails && $hasAnyBankDetail): ?>
              <div style="margin-top:8px; font-size:11px;">
                <strong>Banking Details</strong><br>
                <?php if($bankAccountHolder !== ''): ?> Account Holder: <?php echo e($bankAccountHolder); ?><br><?php endif; ?>
                <?php if($bankName !== ''): ?> Bank: <?php echo e($bankName); ?><br><?php endif; ?>
                <?php if($bankAccountNumber !== ''): ?> Account No: <?php echo e($bankAccountNumber); ?><br><?php endif; ?>
                <?php if($bankIban !== ''): ?> IBAN: <?php echo e($bankIban); ?><br><?php endif; ?>
                <?php if($bankSwift !== ''): ?> SWIFT/BIC: <?php echo e($bankSwift); ?><br><?php endif; ?>
                <?php if($bankRoutingCode !== ''): ?> Routing Code: <?php echo e($bankRoutingCode); ?><br><?php endif; ?>
                <?php if($paymentInstructions !== ''): ?> Instructions: <?php echo e($paymentInstructions); ?><?php endif; ?>
              </div>
            <?php endif; ?>
          </td>
        </tr>
      </table>
    </div>

    <table class="invoice-meta">
      <tr>
        <td>
          <div class="meta-label">Invoice Number</div>
          <div class="meta-value"><?php echo e($invoiceNumber); ?></div>
        </td>
        <td class="meta-right">
          <div class="meta-label">Currency</div>
          <div class="meta-value"><?php echo e($displayCurrencyCode !== '' ? $displayCurrencyCode : 'N/A'); ?> <?php echo e($displayCurrencySymbol); ?></div>
        </td>
      </tr>
    </table>

    <?php
      $excelHeaders = [];
      foreach ($lineItems as $item) {
        $allFields = is_array($item['meta_all_fields'] ?? null) ? $item['meta_all_fields'] : [];
        foreach ($allFields as $field) {
          $label = trim((string) ($field['label'] ?? ''));
          $value = trim((string) ($field['value'] ?? ''));
          if ($label === '' || $value === '') {
            continue;
          }
          if (!in_array($label, $excelHeaders, true)) {
            $excelHeaders[] = $label;
          }
        }
      }
      $useExcelColumns = !empty($excelHeaders);
    ?>

    <?php if($useExcelColumns): ?>
      <table class="line-table line-table-wide">
        <thead>
          <tr>
            <?php $__currentLoopData = $excelHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <th><?php echo e($header); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $lineItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $allFields = is_array($item['meta_all_fields'] ?? null) ? $item['meta_all_fields'] : [];
              $fieldMap = [];
              foreach ($allFields as $field) {
                $label = trim((string) ($field['label'] ?? ''));
                $value = trim((string) ($field['value'] ?? ''));
                if ($label === '' || $value === '') {
                  continue;
                }
                $fieldMap[$label] = $value;
              }
            ?>
            <tr>
              <?php $__currentLoopData = $excelHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td><?php echo e($fieldMap[$header] ?? '-'); ?></td>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    <?php else: ?>
      <table class="line-table">
        <thead>
          <tr>
            <th style="width:8%;">Item #</th>
            <th style="width:34%;">Description</th>
          <th style="width:12%;">Qty</th>
          <th style="width:15%;">Rate</th>
          <th style="width:15%;">Tax</th>
          <th style="width:16%;">Line Total</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $lineItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php
            $metaFields = is_array($item['meta_fields'] ?? null) ? $item['meta_fields'] : [];
            $itemNumber = (string) $loop->iteration;
            $lineMeta = [];
            foreach ($metaFields as $metaField) {
              $metaLabel = strtolower(trim((string) ($metaField['label'] ?? '')));
              $metaValue = trim((string) ($metaField['value'] ?? ''));
              $isItemNo = preg_match('/^(item\\s*(no|number|#)|s\\.?\\s*no|sr\\.?\\s*no|serial\\s*(no|number)?)$/i', $metaLabel) === 1;
              if ($isItemNo && $metaValue !== '') {
                $itemNumber = $metaValue;
                continue;
              }
              $lineMeta[] = $metaField;
            }
          ?>
          <tr>
            <td><?php echo e($itemNumber); ?></td>
            <td>
              <?php echo e($item['description']); ?>

              <?php if(!empty($lineMeta)): ?>
                <div class="line-meta">
                  <?php $__currentLoopData = $lineMeta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metaField): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span><?php echo e($metaField['label']); ?>: <?php echo e($metaField['value']); ?></span><?php if(!$loop->last): ?> | <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
              <?php endif; ?>
            </td>
            <td class="amount"><?php echo e(number_format((float) $item['quantity'], 2)); ?></td>
            <td class="amount"><?php echo e($currencyDisplay); ?> <?php echo e(number_format((float) $item['rate'], 2)); ?></td>
            <td><?php echo e($item['tax_label']); ?></td>
            <td class="amount"><?php echo e($currencyDisplay); ?> <?php echo e(number_format((float) $item['line_total'], 2)); ?></td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
    <?php endif; ?>

    <table class="totals-wrap">
      <tr>
        <td></td>
        <td class="totals-box">
          <table>
            <tr><td class="label">Net Total</td><td class="value"><?php echo e($currencyDisplay); ?> <?php echo e(number_format($netTotal, 2)); ?></td></tr>
            <tr><td class="label">Line-Level Taxes</td><td class="value"><?php echo e($currencyDisplay); ?> <?php echo e(number_format($lineTaxTotalDisplay, 2)); ?></td></tr>
            <tr><td class="label">Subtotal</td><td class="value"><?php echo e($currencyDisplay); ?> <?php echo e(number_format($subtotalDisplay, 2)); ?></td></tr>
            <?php $__currentLoopData = $invoiceSubtotalTaxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr><td class="label"><?php echo e($tax['label']); ?></td><td class="value"><?php echo e($currencyDisplay); ?> <?php echo e(number_format((float) ($tax['amount'] ?? 0), 2)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($showAdjustedSubtotal): ?>
              <tr><td class="label">Adjusted Subtotal</td><td class="value"><?php echo e($currencyDisplay); ?> <?php echo e(number_format($adjustedSubtotal, 2)); ?></td></tr>
            <?php endif; ?>
            <?php $__currentLoopData = $invoiceAdjustedTaxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr><td class="label"><?php echo e($tax['label']); ?></td><td class="value"><?php echo e($currencyDisplay); ?> <?php echo e(number_format((float) ($tax['amount'] ?? 0), 2)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($invoiceSubtotalTaxes) && empty($invoiceAdjustedTaxes)): ?>
              <?php $__currentLoopData = $invoiceTaxLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr><td class="label"><?php echo e($tax['label']); ?></td><td class="value"><?php echo e($currencyDisplay); ?> <?php echo e(number_format((float) ($tax['amount'] ?? 0), 2)); ?></td></tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
            <tr><td class="label">Total Taxes</td><td class="value"><?php echo e($currencyDisplay); ?> <?php echo e(number_format((float) ($summary['total_taxes'] ?? ($lineTaxTotalDisplay + array_sum(array_map(fn ($t) => (float) ($t['amount'] ?? 0), $invoiceTaxLines ?? [])))), 2)); ?></td></tr>
            <tr class="grand"><td class="label">Grand Total</td><td class="value"><?php echo e($currencyDisplay); ?> <?php echo e(number_format($grandTotal, 2)); ?></td></tr>
          </table>
        </td>
      </tr>
    </table>

    <?php if($showPayNow): ?>
      <!-- PAY_NOW_BLOCK_START -->
      <div class="pay-now-row">
        <a href="<?php echo e($payNowHref); ?>" class="<?php echo e($payNowButtonClass); ?>" target="_blank">Pay Now</a>
      </div>
      <!-- PAY_NOW_BLOCK_END -->
    <?php endif; ?>

    <div class="tax-summary">
      <h4>Tax Summary</h4>
      <table>
        <tr><td>Net Total</td><td><?php echo e($currencyDisplay); ?> <?php echo e(number_format($netTotal, 2)); ?></td></tr>
        <tr><td>Total Line-Level Taxes</td><td><?php echo e($currencyDisplay); ?> <?php echo e(number_format($lineTaxTotalDisplay, 2)); ?></td></tr>
        <?php $__currentLoopData = $invoiceSubtotalTaxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr><td><?php echo e($tax['label']); ?></td><td><?php echo e($currencyDisplay); ?> <?php echo e(number_format((float) ($tax['amount'] ?? 0), 2)); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php $__currentLoopData = $invoiceAdjustedTaxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr><td><?php echo e($tax['label']); ?></td><td><?php echo e($currencyDisplay); ?> <?php echo e(number_format((float) ($tax['amount'] ?? 0), 2)); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if(empty($invoiceSubtotalTaxes) && empty($invoiceAdjustedTaxes)): ?>
          <?php $__currentLoopData = $invoiceTaxLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><?php echo e($tax['label']); ?></td><td><?php echo e($currencyDisplay); ?> <?php echo e(number_format((float) ($tax['amount'] ?? 0), 2)); ?></td></tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        <tr><td>Total Taxes</td><td><?php echo e($currencyDisplay); ?> <?php echo e(number_format((float) ($summary['total_taxes'] ?? ($lineTaxTotalDisplay + array_sum(array_map(fn ($t) => (float) ($t['amount'] ?? 0), $invoiceTaxLines ?? [])))), 2)); ?></td></tr>
      </table>
    </div>

    <div class="invoice-footer">
      <?php if($invoiceFooter !== ''): ?>
        <?php echo e($invoiceFooter); ?>

      <?php else: ?>
        This invoice is generated according to your company settings and tax configuration.
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
<?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/invoices/template.blade.php ENDPATH**/ ?>