<?php $__env->startSection('title', 'Invoice Preview'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  .page-header-preview {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.2rem;
  }
  .page-title-preview {
    color: #1540d6;
    font-size: 2.45rem;
    font-weight: 700;
  }
  .preview-card {
    background: #f6f7fb;
    border: 1px solid #d8dde7;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 0.85rem;
  }
  .company-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    align-items: start;
  }
  .logo-box {
    width: 64px;
    height: 64px;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
    background: #ffdc00;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .logo-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .company-name {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0.15rem 0;
  }
  .company-lines, .billto-lines {
    line-height: 1.45;
  }
  .billto-title {
    font-size: 2rem;
    font-weight: 700;
    text-align: right;
    margin-bottom: 0.35rem;
  }
  .billto-lines {
    text-align: right;
  }

  .section-title {
    font-weight: 700;
    margin-bottom: 0.55rem;
  }
  .swatch-row {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
  }
  .swatch {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: 2px solid transparent;
    cursor: pointer;
  }
  .swatch.active {
    box-shadow: 0 0 0 3px #d7e1ff;
    border-color: #1540d6;
  }
  .invoice-title-preview {
    margin-top: 0.4rem;
    border-radius: 12px;
    padding: 0.7rem;
    text-align: center;
    font-weight: 800;
    letter-spacing: 0.5px;
    font-size: 1.55rem;
  }

  .columns-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .cols-list {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
  }
  .col-pill {
    border: 1px solid #c8cfdb;
    background: #eceff6;
    border-radius: 6px;
    padding: 0.2rem 0.5rem;
    font-size: 0.92rem;
  }
  .tax-controls {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
  }
  .tax-options {
    display: none;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
  }
  .tax-options.show {
    display: inline-flex;
  }
  .tax-options-title {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 600;
  }
  .tax-options button {
    padding: 0.2rem 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
    font-size: 0.8rem;
  }
  .switch {
    position: relative;
    display: inline-block;
    width: 42px;
    height: 24px;
  }
  .switch input { opacity: 0; width: 0; height: 0; }
  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cfd7e4;
    transition: .2s;
    border-radius: 20px;
  }
  .slider:before {
    position: absolute;
    content: '';
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .2s;
    border-radius: 50%;
  }
  .switch input:checked + .slider { background-color: #1540d6; }
  .switch input:checked + .slider:before { transform: translateX(18px); }

  .table-wrap {
    overflow-x: auto;
    border: 1px solid #d4d9e4;
    border-radius: 8px;
    background: #fff;
  }
  table.invoice-preview {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }
  .invoice-preview th,
  .invoice-preview td {
    border-bottom: 1px solid #d9dbe2;
    border-right: 1px solid #d9dbe2;
    padding: 0.58rem;
    font-size: 0.92rem;
    text-align: left;
  }
  .invoice-preview th:last-child,
  .invoice-preview td:last-child { border-right: none; }
  .invoice-preview thead th {
    background: #103fd6;
    color: white;
    font-weight: 700;
  }
  .invoice-preview tbody td {
    background: #f3f1d8;
  }
  .cell-input {
    width: 100%;
    border: 1px solid transparent;
    background: transparent;
    padding: 0.2rem 0.25rem;
    border-radius: 4px;
    font-size: 0.92rem;
    color: inherit;
  }
  .cell-input:focus {
    outline: none;
    border-color: #1540d6;
    background: #fffef0;
  }
  .cell-input.cell-number {
    text-align: right;
  }
  .cell-input.is-invalid {
    border-color: var(--danger);
    background: #fff1f4;
  }
  .invoice-preview .check-col {
    width: 64px;
    text-align: center;
  }
  .invoice-preview .num-col,
  .invoice-preview .money-col { width: 120px; }
  .totals-block {
    margin-top: 0.85rem;
    display: flex;
    justify-content: flex-end;
  }
  .totals-card {
    min-width: 280px;
    border: 1px solid #d6dbe6;
    border-radius: 8px;
    padding: 0.7rem;
    background: #fff;
  }
  .currency-inline {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.55rem;
    margin-bottom: 0.45rem;
  }
  .totals-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.35rem;
  }
  .actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.7rem;
    margin-top: 1rem;
  }
  .table-tools {
    margin-top: 0.6rem;
    display: flex;
    justify-content: flex-start;
  }
  .remove-row-btn {
    border: none;
    background: transparent;
    color: #c62828;
    cursor: pointer;
    font-size: 0.95rem;
    margin-left: 0.35rem;
  }
  .remove-row-btn:hover {
    color: #8e0000;
  }
  .line-tax-col {
    min-width: 210px;
  }
  .line-tax-cell-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    align-items: flex-start;
  }
  .line-tax-detail {
    font-size: 0.78rem;
    line-height: 1.3;
    color: #1f2937;
    white-space: normal;
  }
  .recurring-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.85rem;
    flex-wrap: wrap;
  }
  .recurring-row-label span {
    color: #6b7280;
  }
  .recurring-toggle {
    border: 1px solid #b91c1c;
    background: #c81e1e;
    color: #fff;
    border-radius: 999px;
    padding: 0.4rem 1rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
  }
  .recurring-toggle.recurring-on {
    border-color: #17722f;
    background: #1f8b3a;
  }
  .recurring-toggle:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
  .bank-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 0.55rem;
  }
  .bank-sub {
    margin: 0.25rem 0 0;
    color: #6b7280;
    font-size: 0.9rem;
  }
  .bank-drawer {
    display: none;
  }
  .bank-drawer.open {
    display: block;
  }
  .bank-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(240px, 1fr));
    gap: 0.75rem 1rem;
  }
  @media (max-width: 880px) {
    .bank-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
  <?php
    $oldEditedRows = old('edited_rows', []);
    $oldAddedRows = old('added_rows', []);
    $oldSelectedRows = old('selected_rows', []);
    $oldLineTaxMatrix = json_decode((string) old('line_tax_matrix', '{}'), true);
    if (!is_array($oldLineTaxMatrix)) {
      $oldLineTaxMatrix = [];
    }
    $lineTaxPayload = $lineTaxes->map(function ($tax) {
      return [
        'id' => (int) $tax->id,
        'name' => (string) $tax->name,
        'percentage' => (float) $tax->percentage,
      ];
    })->values()->all();
    $oldPreviewIncludeCols = old('preview_include_cols', $includeCols);
    if (!is_array($oldPreviewIncludeCols)) {
      $oldPreviewIncludeCols = $includeCols;
    }
    $previewIncludeCols = array_values(array_intersect($includeCols, $oldPreviewIncludeCols));
    if ($priceMode === 'automatic' && $priceColumn !== '' && in_array($priceColumn, $includeCols, true) && !in_array($priceColumn, $previewIncludeCols, true)) {
      $previewIncludeCols[] = $priceColumn;
    }
    $bankDefaults = is_array($bankDefaults ?? null) ? $bankDefaults : [];
    $showBankDetailsOnInvoice = old('show_bank_details', '1') === '1';
    $recurringEnabled = old('is_recurring', '0') === '1';
    $oldDeliveryTemplateId = old('delivery_template_id');
    $oldPaymentTemplateId = old('payment_confirmation_template_id');
    $defaultReminderBindings = is_array($defaultReminderBindings ?? null) ? $defaultReminderBindings : [];
    $oldReminderBindings = old('reminder_bindings', $defaultReminderBindings);
    if (!is_array($oldReminderBindings) || empty($oldReminderBindings)) {
      $oldReminderBindings = [['rule_id' => '', 'template_id' => '']];
    }
  ?>
  <div class="page-header-preview">
    <h1 class="page-title-preview">Invoice Preview</h1>
    <button type="submit" form="generateInvoiceForm" class="btn btn-primary">Save Invoice</button>
  </div>

  <form id="generateInvoiceForm" method="POST" action="<?php echo e(route('invoices.generate.save')); ?>">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="invoice_title_bg" id="invoiceTitleBg" value="<?php echo e($initialTitleBg); ?>">
    <input type="hidden" name="line_tax_matrix" id="lineTaxMatrixInput" value="<?php echo e(old('line_tax_matrix', '{}')); ?>">
    <input type="hidden" name="line_tax_matrix_mode" value="1">
    <input type="hidden" name="show_bank_details" id="showBankDetailsFlag" value="<?php echo e($showBankDetailsOnInvoice ? '1' : '0'); ?>">
    <input type="hidden" name="is_recurring" id="isRecurringFlag" value="<?php echo e($recurringEnabled ? '1' : '0'); ?>">
    <?php if(session('error')): ?>
      <div class="preview-card" style="border-left:4px solid var(--danger);">
        <div style="color:var(--danger);font-weight:600;"><?php echo e(session('error')); ?></div>
      </div>
    <?php endif; ?>

    <div class="preview-card">
      <div class="company-row">
        <div>
          <div class="logo-box">
            <?php if(!empty($company['logo'])): ?>
              <img src="<?php echo e($company['logo']); ?>" alt="Company logo">
            <?php else: ?>
              <span style="font-weight:700;color:#103fd6;">LOGO</span>
            <?php endif; ?>
          </div>
          <div class="company-name"><?php echo e($company['name'] ?: 'DocuBills'); ?></div>
          <div class="company-lines">
            <?php echo e($company['address'] ?: '-'); ?><br>
            <?php echo e($company['phone'] ?: '-'); ?><br>
            <?php echo e($company['email'] ?: '-'); ?><br>
            GST/HST: <?php echo e($company['gst_hst'] ?: '-'); ?>

          </div>
        </div>

        <div>
          <div class="billto-title">Bill To:</div>
          <div class="billto-lines">
            <?php echo e($billTo['Company Name'] ?? '-'); ?><br>
            <?php echo e($billTo['Contact Name'] ?? ''); ?><br>
            <?php echo e($billTo['Address'] ?? ''); ?><br>
            <?php echo e($billTo['Phone'] ?? ''); ?><br>
            <?php echo e($billTo['Email'] ?? ''); ?>

          </div>
        </div>
      </div>
    </div>

    <div class="preview-card">
      <div class="section-title">Invoice Title Bar Color (PDF Heading)</div>
      <div class="swatch-row" id="titleSwatches">
        <button type="button" class="swatch" data-color="#0033D9" style="background:#0033D9"></button>
        <button type="button" class="swatch" data-color="#169E18" style="background:#169E18"></button>
        <button type="button" class="swatch" data-color="#000000" style="background:#000000"></button>
        <button type="button" class="swatch active" data-color="#FFDC00" style="background:#FFDC00"></button>
        <button type="button" class="swatch" data-color="#5E17EB" style="background:#5E17EB"></button>
      </div>
      <div class="invoice-title-preview" id="invoiceTitlePreview" style="background:#FFDC00;color:#0033D9;">INVOICE</div>
    </div>

    <div class="preview-card columns-row">
      <div>
        <div class="section-title" style="margin-bottom:0.35rem;">Columns to include:</div>
        <div class="cols-list">
          <?php $__currentLoopData = $includeCols; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $isRequiredTotalCol = $priceMode === 'automatic' && $priceColumn !== '' && $col === $priceColumn;
            ?>
            <label class="col-pill">
              <input
                type="checkbox"
                class="js-include-col"
                name="preview_include_cols[]"
                value="<?php echo e($col); ?>"
                data-col-label="<?php echo e($col); ?>"
                <?php echo e(in_array($col, $previewIncludeCols, true) ? 'checked' : ''); ?>

                <?php echo e($isRequiredTotalCol ? 'checked disabled' : ''); ?>

              >
              <?php echo e($col); ?>

              <?php if($isRequiredTotalCol): ?>
                <strong style="color:#1540d6;">(Required for Total)</strong>
              <?php endif; ?>
            </label>
            <?php if($isRequiredTotalCol): ?>
              <input type="hidden" name="preview_include_cols[]" value="<?php echo e($col); ?>">
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>

      <div class="tax-controls">
        <label class="switch">
          <input type="checkbox" id="taxableInvoiceToggle" <?php echo e(old('taxable_invoice') ? 'checked' : ''); ?> <?php echo e(!($canEditInvoice ?? false) ? 'disabled' : ''); ?>>
          <span class="slider"></span>
        </label>
        <strong>Taxable invoice</strong>
        <input type="hidden" name="taxable_invoice" id="taxableInvoiceFlag" value="<?php echo e(old('taxable_invoice') && ($canEditInvoice ?? false) ? '1' : '0'); ?>">
        <div id="lineTaxOptions" class="tax-options<?php echo e(old('taxable_invoice') ? ' show' : ''); ?>">
          <span class="tax-options-title">Line Taxes</span>
          <button type="button" id="selectAllLineTaxes">Select all</button>
          <button type="button" id="clearLineTaxes">Clear</button>
          <?php $__currentLoopData = $lineTaxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label>
              <input
                type="checkbox"
                class="tax-check"
                name="line_tax_ids[]"
                value="<?php echo e($tax->id); ?>"
                data-tax-id="<?php echo e($tax->id); ?>"
                data-tax-name="<?php echo e($tax->name); ?>"
                data-tax-rate="<?php echo e((float) $tax->percentage); ?>"
                <?php echo e(old('taxable_invoice') && ($canEditInvoice ?? false) ? '' : 'disabled'); ?>

                <?php echo e(in_array((string) $tax->id, array_map('strval', (array) old('line_tax_ids', [])), true) ? 'checked' : ''); ?>

              >
              <?php echo e($tax->name); ?> (<?php echo e(rtrim(rtrim(number_format((float)$tax->percentage, 2, '.', ''), '0'), '.')); ?>%)
            </label>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div id="invoiceTaxOptions" class="tax-options<?php echo e(old('taxable_invoice') ? ' show' : ''); ?>">
          <span class="tax-options-title">Invoice Taxes</span>
          <button type="button" id="selectAllInvoiceTaxes">Select all</button>
          <button type="button" id="clearInvoiceTaxes">Clear</button>
          <?php if(($invoiceTaxes ?? collect())->isNotEmpty()): ?>
            <?php $__currentLoopData = $invoiceTaxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <label>
                <input
                  type="checkbox"
                  class="invoice-tax-check"
                  name="invoice_tax_ids[]"
                  value="<?php echo e($tax->id); ?>"
                  data-tax-rate="<?php echo e((float) $tax->percentage); ?>"
                  data-tax-order="<?php echo e((int) $tax->calc_order); ?>"
                  <?php echo e(old('taxable_invoice') && ($canEditInvoice ?? false) ? '' : 'disabled'); ?>

                  <?php echo e(in_array((string) $tax->id, array_map('strval', (array) old('invoice_tax_ids', [])), true) ? 'checked' : ''); ?>

                >
                <?php echo e($tax->name); ?> (<?php echo e(rtrim(rtrim(number_format((float)$tax->percentage, 2, '.', ''), '0'), '.')); ?>%)
              </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php endif; ?>
        </div>
        <?php if(!($canEditInvoice ?? false)): ?>
          <span style="font-size:0.85rem; color:#6c757d;">Tax controls are disabled for your role.</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="table-wrap">
      <table class="invoice-preview" id="invoicePreviewTable">
        <thead>
          <tr>
            <th class="check-col"><input type="checkbox" id="checkAll" checked></th>
            <?php $__currentLoopData = $includeCols; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <th data-col-index="<?php echo e($loop->index); ?>" data-col-label="<?php echo e($col); ?>"><?php echo e($col); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php $__currentLoopData = $lineTaxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <th
                class="line-tax-col line-tax-col-head"
                data-line-tax-id="<?php echo e($tax->id); ?>"
                style="display:none;"
              >
                <?php echo e($tax->name); ?> Line Tax
              </th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $tableRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $isChecked = empty($oldSelectedRows) ? true : in_array((string) $row['index'], array_map('strval', $oldSelectedRows), true);
            ?>
            <tr data-row-key="row-<?php echo e($row['index']); ?>">
              <td class="check-col"><input type="checkbox" class="row-check" name="selected_rows[]" value="<?php echo e($row['index']); ?>" <?php echo e($isChecked ? 'checked' : ''); ?>></td>
              <?php $__currentLoopData = $includeCols; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $colIndex = $loop->index;
                  $rawValue = $oldEditedRows[$row['index']]['cells'][$colIndex] ?? ($row['cells'][$col] ?? '');
                  $label = strtolower(trim($col));
                  $isNumeric = str_contains($label, 'qty') || str_contains($label, 'quantity') || str_contains($label, 'price') || str_contains($label, 'rate') || str_contains($label, 'amount') || str_contains($label, 'total');
                ?>
                <td>
                  <input
                    type="text"
                    name="edited_rows[<?php echo e($row['index']); ?>][cells][<?php echo e($colIndex); ?>]"
                    value="<?php echo e($rawValue); ?>"
                    class="cell-input <?php echo e($isNumeric ? 'cell-number js-number' : ''); ?>"
                    data-col-index="<?php echo e($colIndex); ?>"
                    data-row-index="<?php echo e($row['index']); ?>"
                    data-col-label="<?php echo e($col); ?>"
                  >
                </td>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php $__currentLoopData = $lineTaxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $rowKey = 'row-' . $row['index'];
                  $rowTaxIds = is_array($oldLineTaxMatrix[$rowKey] ?? null) ? array_map('strval', (array) $oldLineTaxMatrix[$rowKey]) : [];
                  $isRowTaxChecked = in_array((string) $tax->id, $rowTaxIds, true);
                ?>
                <td class="line-tax-col line-tax-col-cell" data-line-tax-id="<?php echo e($tax->id); ?>" style="display:none;">
                  <div class="line-tax-cell-wrap">
                    <label>
                      <input
                        type="checkbox"
                        class="row-line-tax-check"
                        data-row-key="<?php echo e($rowKey); ?>"
                        data-tax-id="<?php echo e($tax->id); ?>"
                        data-tax-name="<?php echo e($tax->name); ?>"
                        data-tax-rate="<?php echo e((float) $tax->percentage); ?>"
                        <?php echo e($isRowTaxChecked ? 'checked' : ''); ?>

                        <?php echo e(old('taxable_invoice') && ($canEditInvoice ?? false) ? '' : 'disabled'); ?>

                      >
                      Apply
                    </label>
                    <div class="line-tax-detail"></div>
                  </div>
                </td>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

          <?php $__currentLoopData = $oldAddedRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addIdx => $added): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
              $cells = (array) ($added['cells'] ?? []);
              $selected = ((string) ($added['selected'] ?? '')) === '1';
            ?>
            <tr data-added-row="1" data-added-index="<?php echo e($addIdx); ?>" data-row-key="new-<?php echo e($addIdx); ?>">
              <td class="check-col">
                <input type="checkbox" class="row-check" name="added_rows[<?php echo e($addIdx); ?>][selected]" value="1" <?php echo e($selected ? 'checked' : ''); ?>>
                <button type="button" class="remove-row-btn" title="Remove added line item">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
              <?php $__currentLoopData = $includeCols; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $colIndex = $loop->index;
                  $val = (string) ($cells[$colIndex] ?? '');
                  $label = strtolower(trim($col));
                  $isNumeric = str_contains($label, 'qty') || str_contains($label, 'quantity') || str_contains($label, 'price') || str_contains($label, 'rate') || str_contains($label, 'amount') || str_contains($label, 'total');
                ?>
                <td>
                  <input
                    type="text"
                    name="added_rows[<?php echo e($addIdx); ?>][cells][<?php echo e($colIndex); ?>]"
                    value="<?php echo e($val); ?>"
                    class="cell-input <?php echo e($isNumeric ? 'cell-number js-number' : ''); ?>"
                    data-col-index="<?php echo e($colIndex); ?>"
                    data-row-index="new-<?php echo e($addIdx); ?>"
                    data-col-label="<?php echo e($col); ?>"
                  >
                </td>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php $__currentLoopData = $lineTaxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $rowKey = 'new-' . $addIdx;
                  $rowTaxIds = is_array($oldLineTaxMatrix[$rowKey] ?? null) ? array_map('strval', (array) $oldLineTaxMatrix[$rowKey]) : [];
                  $isRowTaxChecked = in_array((string) $tax->id, $rowTaxIds, true);
                ?>
                <td class="line-tax-col line-tax-col-cell" data-line-tax-id="<?php echo e($tax->id); ?>" style="display:none;">
                  <div class="line-tax-cell-wrap">
                    <label>
                      <input
                        type="checkbox"
                        class="row-line-tax-check"
                        data-row-key="<?php echo e($rowKey); ?>"
                        data-tax-id="<?php echo e($tax->id); ?>"
                        data-tax-name="<?php echo e($tax->name); ?>"
                        data-tax-rate="<?php echo e((float) $tax->percentage); ?>"
                        <?php echo e($isRowTaxChecked ? 'checked' : ''); ?>

                        <?php echo e(old('taxable_invoice') && ($canEditInvoice ?? false) ? '' : 'disabled'); ?>

                      >
                      Apply
                    </label>
                    <div class="line-tax-detail"></div>
                  </div>
                </td>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
    <div class="table-tools">
      <button
        type="button"
        class="btn btn-secondary"
        id="addLineItemBtn"
        <?php echo e($priceMode === 'automatic' ? '' : 'disabled'); ?>

        title="<?php echo e($priceMode === 'automatic' ? 'Add a new editable line item row' : 'Available in automatic pricing mode'); ?>"
      >
        + Add Line Item
      </button>
    </div>
    <?php if($priceMode === 'automatic'): ?>
      <?php $__errorArgs = ['selected_rows'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div style="color:var(--danger); margin-top:0.5rem;"><?php echo e($message); ?></div>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      <?php $__errorArgs = ['edited_rows'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div style="color:var(--danger); margin-top:0.5rem;"><?php echo e($message); ?></div>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    <?php endif; ?>

    <?php if($priceMode === 'manual'): ?>
      <div class="preview-card" style="margin-top:0.85rem;">
        <div class="currency-inline" style="max-width:420px;">
          <label for="currency_code_manual" class="section-title" style="font-size:1rem; margin:0;">Currency</label>
          <select id="currency_code_manual" name="currency_code" class="form-control" style="max-width:190px;">
            <?php $__currentLoopData = $currencyOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($code); ?>" <?php echo e(old('currency_code', $currencyCode) === $code ? 'selected' : ''); ?>>
                <?php echo e($meta['label']); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <?php $__errorArgs = ['currency_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <label for="manual_total" class="section-title" style="font-size:1rem;">Manual Total Amount</label>
        <input id="manual_total" type="number" name="manual_total" step="0.01" min="0.01" value="<?php echo e(old('manual_total')); ?>" required class="form-control" style="max-width:320px;">
        <?php $__errorArgs = ['manual_total'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
    <?php else: ?>
      <div class="totals-block">
        <div class="totals-card">
          <div class="currency-inline">
            <label for="currency_code_auto" style="font-weight:600;">Currency</label>
            <select id="currency_code_auto" name="currency_code" class="form-control" style="max-width:190px;">
              <?php $__currentLoopData = $currencyOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($code); ?>" <?php echo e(old('currency_code', $currencyCode) === $code ? 'selected' : ''); ?>>
                  <?php echo e($meta['label']); ?>

                </option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <?php $__errorArgs = ['currency_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          <div class="totals-line" id="selectedTaxRow" style="display:none;"><span>Total Taxes</span><strong id="selectedTaxTotal">0.00</strong></div>
          <div class="totals-line"><span>Total</span><strong id="selectedTotal"><?php echo e(number_format((float) $previewTotal, 2)); ?></strong></div>
        </div>
      </div>
    <?php endif; ?>

    <div class="preview-card" style="display:flex;gap:1rem;flex-wrap:wrap;">
      <div>
        <label for="invoice_date" class="section-title" style="font-size:1rem;">Invoice Date</label>
        <input id="invoice_date" type="date" name="invoice_date" class="form-control" value="<?php echo e(old('invoice_date', $initialInvoiceDate)); ?>" required>
        <?php $__errorArgs = ['invoice_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
      <div>
        <label for="due_date" class="section-title" style="font-size:1rem;">Due Date</label>
        <input id="due_date" type="date" name="due_date" class="form-control" value="<?php echo e(old('due_date', $initialDueDate)); ?>" required>
        <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
    </div>

    <div class="preview-card">
      <div class="section-title">Email & Reminder Setup</div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:0.8rem;">
        <div>
          <label for="delivery_template_id" class="section-title" style="font-size:0.95rem;">Invoice Delivery Template</label>
          <select id="delivery_template_id" name="delivery_template_id" class="form-control">
            <option value="">Use default email content</option>
            <?php $__currentLoopData = $emailTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($template->id); ?>" <?php echo e((string) $oldDeliveryTemplateId === (string) $template->id ? 'selected' : ''); ?>>
                <?php echo e($template->template_name); ?> (#<?php echo e($template->id); ?>)
              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <?php $__errorArgs = ['delivery_template_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div>
          <label for="payment_confirmation_template_id" class="section-title" style="font-size:0.95rem;">Payment Confirmation Template</label>
          <select id="payment_confirmation_template_id" name="payment_confirmation_template_id" class="form-control">
            <option value="">Use default email content</option>
            <?php $__currentLoopData = $emailTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($template->id); ?>" <?php echo e((string) $oldPaymentTemplateId === (string) $template->id ? 'selected' : ''); ?>>
                <?php echo e($template->template_name); ?> (#<?php echo e($template->id); ?>)
              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <?php $__errorArgs = ['payment_confirmation_template_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
      </div>

      <div style="margin-top:1rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:0.75rem;flex-wrap:wrap;">
          <strong>Reminder Rule-Template Bindings</strong>
          <button type="button" id="addReminderBindingBtn" class="btn btn-secondary btn-sm">Add Binding</button>
        </div>
        <p style="margin:0.4rem 0 0.7rem;color:#6b7280;font-size:0.9rem;">
          You can bind multiple templates to the same rule; all mapped templates will be sent.
        </p>
        <div id="reminderBindingsList">
          <?php $__currentLoopData = $oldReminderBindings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $binding): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="reminder-binding-row" style="display:grid;grid-template-columns:1fr 1fr auto;gap:0.6rem;align-items:end;margin-bottom:0.6rem;">
              <div>
                <label class="section-title" style="font-size:0.9rem;">Rule</label>
                <select name="reminder_bindings[<?php echo e($idx); ?>][rule_id]" class="form-control js-reminder-rule">
                  <option value="">Select rule</option>
                  <?php $__currentLoopData = $reminderRules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                      $ruleLabel = $rule['label'] ?? $rule['name'] ?? $rule['id'] ?? '';
                    ?>
                    <option value="<?php echo e($rule['id']); ?>" <?php echo e((string) ($binding['rule_id'] ?? '') === (string) $rule['id'] ? 'selected' : ''); ?>>
                      <?php echo e($ruleLabel); ?> (<?php echo e($rule['id']); ?>)
                    </option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
              <div>
                <label class="section-title" style="font-size:0.9rem;">Template</label>
                <select name="reminder_bindings[<?php echo e($idx); ?>][template_id]" class="form-control">
                  <option value="">Select template</option>
                  <?php $__currentLoopData = $emailTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($template->id); ?>" <?php echo e((string) ($binding['template_id'] ?? '') === (string) $template->id ? 'selected' : ''); ?>>
                      <?php echo e($template->template_name); ?> (#<?php echo e($template->id); ?>)
                    </option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
              <div>
                <button type="button" class="btn btn-danger btn-sm remove-reminder-binding-btn">Remove</button>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php $__errorArgs = ['reminder_bindings'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <?php $__errorArgs = ['reminder_bindings.*.rule_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <?php $__errorArgs = ['reminder_bindings.*.template_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div style="margin-top:1rem;padding-top:0.8rem;border-top:1px dashed #e5e7eb;">
        <strong>Custom Reminder</strong>
        <p style="margin:0.4rem 0 0.7rem;color:#6b7280;font-size:0.9rem;">
          Set a specific reminder date or choose days after the due date. Use only one.
        </p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:0.6rem;">
          <div>
            <label for="reminder_date" class="section-title" style="font-size:0.9rem;">Reminder Date</label>
            <input id="reminder_date" type="date" name="reminder_date" class="form-control" value="<?php echo e(old('reminder_date')); ?>">
          </div>
          <div>
            <label for="reminder_days_after" class="section-title" style="font-size:0.9rem;">Days After Due Date</label>
            <input id="reminder_days_after" type="number" min="0" max="365" name="reminder_days_after" class="form-control" value="<?php echo e(old('reminder_days_after')); ?>">
          </div>
        </div>
        <?php $__errorArgs = ['reminder_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <?php $__errorArgs = ['reminder_days_after'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div style="color:var(--danger); margin-top:0.35rem;"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
    </div>

    <div class="preview-card recurring-row">
      <div class="recurring-row-label">
        <strong>Recurring Invoice:</strong>
        <span>Send this same amount to the same client every month on this invoice date.</span>
      </div>
      <button
        type="button"
        id="recurringToggle"
        class="recurring-toggle <?php echo e($recurringEnabled ? 'recurring-on' : 'recurring-off'); ?>"
        <?php echo e(!($canManageRecurring ?? false) ? 'disabled' : ''); ?>

      >
        <i class="fas fa-sync-alt"></i>
        <span id="recurringToggleText"><?php echo e($recurringEnabled ? 'Enabled (Monthly)' : 'Disabled (One-time)'); ?></span>
      </button>
      <?php if(!($canManageRecurring ?? false)): ?>
        <span style="font-size:0.85rem; color:#6c757d;">Recurring billing is disabled for your role.</span>
      <?php endif; ?>
    </div>

    <div class="preview-card">
      <div class="bank-head">
        <div>
          <label class="section-title" style="font-size:1.08rem;">Banking Details (for this invoice)</label>
          <p class="bank-sub">These fields are pre-filled from Settings -> Payment Methods. You can adjust them for this invoice only.</p>
        </div>
        <label style="font-size:0.95rem; white-space:nowrap; cursor:pointer;">
          <input type="checkbox" id="toggleBankDetails" <?php echo e($showBankDetailsOnInvoice ? 'checked' : ''); ?>>
          Show on this invoice
        </label>
      </div>

      <div id="bankingDrawer" class="bank-drawer <?php echo e($showBankDetailsOnInvoice ? 'open' : ''); ?>">
        <div class="bank-grid">
          <div>
            <label for="bank_account_holder" class="section-title" style="font-size:0.95rem;">Account Holder Name</label>
            <input type="text" id="bank_account_holder" name="bank_account_holder" class="form-control" value="<?php echo e(old('bank_account_holder', $bankDefaults['bank_account_holder'] ?? '')); ?>">
          </div>
          <div>
            <label for="bank_name" class="section-title" style="font-size:0.95rem;">Bank Name</label>
            <input type="text" id="bank_name" name="bank_name" class="form-control" value="<?php echo e(old('bank_name', $bankDefaults['bank_name'] ?? '')); ?>">
          </div>
          <div>
            <label for="bank_account_number" class="section-title" style="font-size:0.95rem;">Account Number</label>
            <input type="text" id="bank_account_number" name="bank_account_number" class="form-control" value="<?php echo e(old('bank_account_number', $bankDefaults['bank_account_number'] ?? '')); ?>">
          </div>
          <div>
            <label for="bank_iban" class="section-title" style="font-size:0.95rem;">IBAN</label>
            <input type="text" id="bank_iban" name="bank_iban" class="form-control" value="<?php echo e(old('bank_iban', $bankDefaults['bank_iban'] ?? '')); ?>">
          </div>
          <div>
            <label for="bank_swift" class="section-title" style="font-size:0.95rem;">SWIFT / BIC</label>
            <input type="text" id="bank_swift" name="bank_swift" class="form-control" value="<?php echo e(old('bank_swift', $bankDefaults['bank_swift'] ?? '')); ?>">
          </div>
          <div>
            <label for="bank_routing_code" class="section-title" style="font-size:0.95rem;">Routing / Sort Code</label>
            <input type="text" id="bank_routing_code" name="bank_routing_code" class="form-control" value="<?php echo e(old('bank_routing_code', $bankDefaults['bank_routing_code'] ?? '')); ?>">
          </div>
        </div>
        <div style="margin-top:0.75rem;">
          <label for="bank_payment_instructions" class="section-title" style="font-size:0.95rem;">Additional Payment Instructions</label>
          <textarea id="bank_payment_instructions" name="bank_payment_instructions" class="form-control" rows="3"><?php echo e(old('bank_payment_instructions', $bankDefaults['bank_payment_instructions'] ?? '')); ?></textarea>
        </div>
      </div>
    </div>

    <div class="actions">
      <a href="<?php echo e(route('invoices.price-select')); ?>" class="btn btn-secondary">Back</a>
      <button type="submit" class="btn btn-primary">Save Invoice</button>
    </div>
  </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
  (function () {
    const INCLUDE_COLS = <?php echo json_encode(array_values($includeCols), 15, 512) ?>;
    const LINE_TAXES = <?php echo json_encode($lineTaxPayload, 15, 512) ?>;
    const IS_AUTOMATIC = <?php echo json_encode($priceMode === 'automatic', 15, 512) ?>;
    const PRICE_COLUMN = <?php echo json_encode($priceColumn ?? '', 15, 512) ?>;
    const INITIAL_PREVIEW_INCLUDE_COLS = <?php echo json_encode(array_values($previewIncludeCols), 15, 512) ?>;
    const REMINDER_RULES = <?php echo json_encode(array_values($reminderRules), 15, 512) ?>;
    const EMAIL_TEMPLATES = <?php echo json_encode($emailTemplates->map(fn($t) => ['id' => $t->id, 'template_name' => $t->template_name])->values(), 512) ?>;
    const REMINDER_TEMPLATE_MAP = <?php echo json_encode($defaultReminderTemplateMap ?? [], 15, 512) ?>;
    const swatches = document.querySelectorAll('#titleSwatches .swatch');
    const hiddenBg = document.getElementById('invoiceTitleBg');
    const preview = document.getElementById('invoiceTitlePreview');

    function textColor(bg) {
      return bg.toUpperCase() === '#FFDC00' ? '#0033D9' : '#FFFFFF';
    }

    swatches.forEach(btn => {
      btn.addEventListener('click', () => {
        swatches.forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        const color = btn.dataset.color;
        hiddenBg.value = color;
        preview.style.background = color;
        preview.style.color = textColor(color);
      });
    });

    const checkAll = document.getElementById('checkAll');
    const selectedTotalEl = document.getElementById('selectedTotal');
    const selectedTaxTotalEl = document.getElementById('selectedTaxTotal');
    const selectedTaxRowEl = document.getElementById('selectedTaxRow');
    const taxableFlag = document.getElementById('taxableInvoiceFlag');
    const lineTaxMatrixInput = document.getElementById('lineTaxMatrixInput');
    const recurringToggle = document.getElementById('recurringToggle');
    const recurringToggleText = document.getElementById('recurringToggleText');
    const isRecurringFlag = document.getElementById('isRecurringFlag');
    const toggleBankDetails = document.getElementById('toggleBankDetails');
    const bankingDrawer = document.getElementById('bankingDrawer');
    const showBankDetailsFlag = document.getElementById('showBankDetailsFlag');
    const reminderBindingsList = document.getElementById('reminderBindingsList');
    const addReminderBindingBtn = document.getElementById('addReminderBindingBtn');

    function buildReminderBindingRow(index) {
      const ruleOptions = ['<option value="">Select rule</option>']
        .concat(REMINDER_RULES.map((rule) => {
          const label = rule.label || rule.name || rule.id || 'Rule';
          return `<option value="${rule.id}">${label} (${rule.id})</option>`;
        }))
        .join('');
      const templateOptions = ['<option value="">Select template</option>']
        .concat(EMAIL_TEMPLATES.map((tpl) => `<option value="${tpl.id}">${tpl.template_name} (#${tpl.id})</option>`))
        .join('');

      return `
        <div class="reminder-binding-row" style="display:grid;grid-template-columns:1fr 1fr auto;gap:0.6rem;align-items:end;margin-bottom:0.6rem;">
          <div>
            <label class="section-title" style="font-size:0.9rem;">Rule</label>
            <select name="reminder_bindings[${index}][rule_id]" class="form-control js-reminder-rule">${ruleOptions}</select>
          </div>
          <div>
            <label class="section-title" style="font-size:0.9rem;">Template</label>
            <select name="reminder_bindings[${index}][template_id]" class="form-control">${templateOptions}</select>
          </div>
          <div>
            <button type="button" class="btn btn-danger btn-sm remove-reminder-binding-btn">Remove</button>
          </div>
        </div>
      `;
    }

    function applyTemplateForRule(ruleSelect) {
      if (!ruleSelect) return;
      const ruleId = (ruleSelect.value || '').trim();
      if (!ruleId) return;
      const row = ruleSelect.closest('.reminder-binding-row');
      if (!row) return;
      const templateSelect = row.querySelector('select[name*="[template_id]"]');
      if (!templateSelect || (templateSelect.value || '').trim() !== '') return;
      const mapped = REMINDER_TEMPLATE_MAP[ruleId];
      if (mapped) {
        templateSelect.value = String(mapped);
      }
    }

    function bindReminderRemoveButtons() {
      document.querySelectorAll('.remove-reminder-binding-btn').forEach((btn) => {
        btn.onclick = () => {
          const rows = document.querySelectorAll('.reminder-binding-row');
          if (rows.length <= 1) {
            const firstRow = rows[0];
            if (!firstRow) return;
            firstRow.querySelectorAll('select').forEach((sel) => {
              sel.value = '';
            });
            return;
          }
          const row = btn.closest('.reminder-binding-row');
          if (row) {
            row.remove();
          }
        };
      });
    }

    addReminderBindingBtn?.addEventListener('click', () => {
      const index = document.querySelectorAll('.reminder-binding-row').length;
      reminderBindingsList?.insertAdjacentHTML('beforeend', buildReminderBindingRow(index));
      bindReminderRemoveButtons();
      const newRow = reminderBindingsList?.querySelector('.reminder-binding-row:last-child');
      const ruleSelect = newRow?.querySelector('.js-reminder-rule');
      if (ruleSelect) {
        ruleSelect.addEventListener('change', () => applyTemplateForRule(ruleSelect));
      }
    });

    bindReminderRemoveButtons();
    document.querySelectorAll('.js-reminder-rule').forEach((ruleSelect) => {
      ruleSelect.addEventListener('change', () => applyTemplateForRule(ruleSelect));
      applyTemplateForRule(ruleSelect);
    });

    const reminderDateInput = document.getElementById('reminder_date');
    const reminderDaysInput = document.getElementById('reminder_days_after');
    reminderDateInput?.addEventListener('change', () => {
      if (reminderDateInput.value) {
        reminderDaysInput.value = '';
      }
    });
    reminderDaysInput?.addEventListener('input', () => {
      if (reminderDaysInput.value) {
        reminderDateInput.value = '';
      }
    });

    function parseNum(value) {
      const safeValue = (value === null || value === undefined) ? '' : value;
      const raw = String(safeValue).replace(/,/g, '').trim();
      if (raw === '') return NaN;
      const n = Number(raw);
      return Number.isFinite(n) ? n : NaN;
    }

    function sanitizeNumericInput(input) {
      if (!input) return;
      const before = String((input.value === null || input.value === undefined) ? '' : input.value);
      let v = before.replace(/[^\d.]/g, '');
      const firstDot = v.indexOf('.');
      if (firstDot !== -1) {
        v = v.slice(0, firstDot + 1) + v.slice(firstDot + 1).replace(/\./g, '');
      }
      if (v.startsWith('.')) {
        v = '0' + v;
      }
      if (v !== before) {
        input.value = v;
      }

      if (v === '') {
        input.classList.remove('is-invalid');
        return;
      }
      const n = Number(v);
      input.classList.toggle('is-invalid', !Number.isFinite(n));
    }

    function round2(v) {
      return Math.round((Number(v) + Number.EPSILON) * 100) / 100;
    }

    function setRecurringState(isOn) {
      if (isRecurringFlag) {
        isRecurringFlag.value = isOn ? '1' : '0';
      }
      if (recurringToggle) {
        recurringToggle.classList.toggle('recurring-on', isOn);
        recurringToggle.classList.toggle('recurring-off', !isOn);
      }
      if (recurringToggleText) {
        recurringToggleText.textContent = isOn ? 'Enabled (Monthly)' : 'Disabled (One-time)';
      }
    }

    function setBankDetailsState(showOnInvoice) {
      if (showBankDetailsFlag) {
        showBankDetailsFlag.value = showOnInvoice ? '1' : '0';
      }
      if (bankingDrawer) {
        bankingDrawer.classList.toggle('open', showOnInvoice);
      }
      if (toggleBankDetails) {
        toggleBankDetails.checked = showOnInvoice;
      }
    }

    function activeLineTaxIds() {
      return new Set(
        Array.from(document.querySelectorAll('.tax-check:checked')).map((el) => String(el.value || ''))
      );
    }

    function applyLineTaxColumnVisibility() {
      const taxable = document.getElementById('taxableInvoiceToggle');
      const taxableOn = !!(taxable && taxable.checked);
      const activeTaxIds = activeLineTaxIds();
      const allLineTaxCols = Array.from(document.querySelectorAll('.line-tax-col'));
      const rowTaxChecks = Array.from(document.querySelectorAll('.row-line-tax-check'));

      allLineTaxCols.forEach((col) => {
        const taxId = String(col.dataset.lineTaxId || '');
        const visible = taxableOn && activeTaxIds.has(taxId);
        col.style.display = visible ? '' : 'none';
      });

      rowTaxChecks.forEach((chk) => {
        const taxId = String(chk.dataset.taxId || '');
        const enabled = taxableOn && activeTaxIds.has(taxId);
        chk.disabled = !enabled;
        if (!enabled) {
          chk.checked = false;
          const detail = chk.closest('.line-tax-cell-wrap')?.querySelector('.line-tax-detail');
          if (detail) {
            detail.textContent = '';
          }
        }
      });
    }

    function findRowAmountInput(rowEl) {
      if (!rowEl) return null;
      const all = Array.from(rowEl.querySelectorAll('input[data-col-label]')).filter((input) => {
        if (input.disabled) return false;
        const td = input.closest('td');
        return !(td && td.style.display === 'none');
      });
      const explicit = all.find((input) => {
        const label = String(input.dataset.colLabel || '').toLowerCase();
        return label.includes('total amount') || label.includes('line total');
      });
      if (explicit) return explicit;

      const generic = all.find((input) => {
        const label = String(input.dataset.colLabel || '').toLowerCase();
        return label.includes('total') || label.includes('amount');
      });
      if (generic) return generic;

      const byPriceColumn = all.find((input) => {
        return String(input.dataset.colLabel || '').trim().toLowerCase() === String(PRICE_COLUMN || '').trim().toLowerCase();
      });
      return byPriceColumn || null;
    }

    function isTotalLabel(label) {
      const l = String(label || '').toLowerCase();
      return l.includes('subtotal') ||
        l.includes('sub total') ||
        l.includes('line total') ||
        l.includes('total amount') ||
        l.includes('amount') ||
        l.includes('total');
    }

    function recalcRowAmount(rowEl) {
      if (!rowEl) return;
      const inputs = rowEl.querySelectorAll('input[data-col-label]');
      let qtyInput = null;
      let unitInput = null;
      const amountInput = findRowAmountInput(rowEl);

      inputs.forEach((input) => {
        const label = String(input.dataset.colLabel || '').toLowerCase();
        if (!qtyInput && (label.includes('qty') || label.includes('quantity'))) {
          qtyInput = input;
        }
        if (!unitInput && (label.includes('unit price') || label.includes('price') || label.includes('rate'))) {
          unitInput = input;
        }
      });

      if (!amountInput || !qtyInput || !unitInput) return;
      if (isTotalLabel(amountInput.dataset.colLabel || '') && String(amountInput.value || '').trim() !== '') {
        return;
      }
      if (amountInput === unitInput || amountInput === qtyInput) return;

      const qty = parseNum(qtyInput.value);
      const unit = parseNum(unitInput.value);
      if (Number.isFinite(qty) && Number.isFinite(unit)) {
        amountInput.value = (qty * unit).toFixed(2);
      }
    }

    function computeRowAmount(rowEl) {
      if (!rowEl) return 0;
      const inputs = rowEl.querySelectorAll('input[data-col-label]');
      let qtyInput = null;
      let unitInput = null;
      const amountInput = findRowAmountInput(rowEl);

      inputs.forEach((input) => {
        if (input.disabled) return;
        const td = input.closest('td');
        if (td && td.style.display === 'none') return;
        const label = String(input.dataset.colLabel || '').toLowerCase();
        if (!qtyInput && (label.includes('qty') || label.includes('quantity'))) {
          qtyInput = input;
        }
        if (!unitInput && (label.includes('unit price') || label.includes('price') || label.includes('rate'))) {
          unitInput = input;
        }
      });

      if (amountInput && isTotalLabel(amountInput.dataset.colLabel || '')) {
        const amount = parseNum(amountInput.value);
        if (Number.isFinite(amount) && amount > 0) {
          return amount;
        }
      }

      if (qtyInput && unitInput) {
        const qty = parseNum(qtyInput.value);
        const unit = parseNum(unitInput.value);
        if (Number.isFinite(qty) && Number.isFinite(unit)) {
          return qty * unit;
        }
      }

      if (amountInput) {
        const amount = parseNum(amountInput.value);
        return Number.isFinite(amount) ? amount : 0;
      }

      return 0;
    }

    function recalcTotal() {
      if (!selectedTotalEl) return;
      let netTotal = 0;
      let lineTaxTotal = 0;
      const rows = document.querySelectorAll('#invoicePreviewTable tbody tr');
      const taxable = document.getElementById('taxableInvoiceToggle');
      const taxableOn = !!(taxable && taxable.checked);

      document.querySelectorAll('.line-tax-detail').forEach((el) => {
        el.textContent = '';
      });

      rows.forEach((row, idx) => {
        const check = row.querySelector('.row-check');
        const checked = !!(check && check.checked);
        const rowBase = computeRowAmount(row);
        if (!checked) return;
        netTotal += rowBase;

        if (taxableOn) {
          const rowTaxChecks = Array.from(row.querySelectorAll('.row-line-tax-check:checked')).filter((chk) => !chk.disabled);
          rowTaxChecks.forEach((taxCheck) => {
            const rate = Number(taxCheck.dataset.taxRate || 0);
            if (!Number.isFinite(rate) || rate <= 0) return;
            const taxAmount = round2(rowBase * (rate / 100));
            lineTaxTotal += taxAmount;

            const taxName = String(taxCheck.dataset.taxName || 'Line Tax');
            const detail = taxCheck.closest('.line-tax-cell-wrap')?.querySelector('.line-tax-detail');
            if (detail) {
              const incremented = round2(rowBase + taxAmount);
              detail.textContent = `Base ${rowBase.toFixed(2)} | ${taxName} (${rate}%) +${taxAmount.toFixed(2)} | Total ${incremented.toFixed(2)}`;
            }
          });
        }
      });

      if (taxableOn) {
        const visibleUncheckedDetails = Array.from(document.querySelectorAll('.row-line-tax-check:not(:checked)')).filter((chk) => !chk.disabled);
        visibleUncheckedDetails.forEach((taxCheck) => {
          const detail = taxCheck.closest('.line-tax-cell-wrap')?.querySelector('.line-tax-detail');
          if (detail) {
            detail.textContent = '';
          }
        });
      }

      const subtotal = netTotal + lineTaxTotal;
      let invoiceTaxTotal = 0;
      let subtotalStageTaxTotal = 0;
      const adjustedStageChecks = [];

      if (taxableOn) {
        const invoiceTaxChecks = Array.from(document.querySelectorAll('.invoice-tax-check:checked'));
        invoiceTaxChecks.sort((a, b) => {
          const ao = Number(a.dataset.taxOrder || 1);
          const bo = Number(b.dataset.taxOrder || 1);
          if (ao !== bo) return ao - bo;
          return Number(a.value || 0) - Number(b.value || 0);
        });

        invoiceTaxChecks.forEach((chk) => {
          const rate = Number(chk.dataset.taxRate || 0);
          if (!Number.isFinite(rate) || rate <= 0) return;
          const order = Number(chk.dataset.taxOrder || 1);
          if (order === 3) {
            adjustedStageChecks.push(chk);
            return;
          }
          subtotalStageTaxTotal += subtotal * (rate / 100);
        });

        const adjustedSubtotal = subtotal + subtotalStageTaxTotal;
        adjustedStageChecks.forEach((chk) => {
          const rate = Number(chk.dataset.taxRate || 0);
          if (!Number.isFinite(rate) || rate <= 0) return;
          invoiceTaxTotal += adjustedSubtotal * (rate / 100);
        });
        invoiceTaxTotal += subtotalStageTaxTotal;
      }

      const total = subtotal + invoiceTaxTotal;
      if (selectedTaxTotalEl) {
        selectedTaxTotalEl.textContent = (lineTaxTotal + invoiceTaxTotal).toFixed(2);
      }
      if (selectedTaxRowEl) {
        selectedTaxRowEl.style.display = taxableOn && (lineTaxTotal + invoiceTaxTotal) > 0 ? 'flex' : 'none';
      }
      selectedTotalEl.textContent = total.toFixed(2);
    }

    if (checkAll) {
      checkAll.addEventListener('change', () => {
        document.querySelectorAll('.row-check').forEach(chk => {
          chk.checked = checkAll.checked;
        });
        recalcTotal();
      });
    }

    document.addEventListener('change', (e) => {
      if (!e.target.classList || !e.target.classList.contains('row-check')) return;
      if (checkAll) {
        const allChecks = Array.from(document.querySelectorAll('.row-check'));
        checkAll.checked = allChecks.length > 0 && allChecks.every(c => c.checked);
      }
      recalcTotal();
    });

    function bindEditableRow(row) {
      if (!row || row.dataset.boundRow === '1') return;
      row.dataset.boundRow = '1';
      row.querySelectorAll('input.cell-input').forEach((input) => {
        if (input.classList.contains('js-number')) {
          input.setAttribute('inputmode', 'decimal');
          input.setAttribute('autocomplete', 'off');
          sanitizeNumericInput(input);
          input.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text') || '';
            input.value = text;
            sanitizeNumericInput(input);
            recalcRowAmount(row);
            recalcTotal();
          });
        }
        input.addEventListener('input', () => {
          if (input.classList.contains('js-number')) {
            sanitizeNumericInput(input);
          }
          recalcRowAmount(row);
          recalcTotal();
        });
      });
      recalcRowAmount(row);
    }

    document.querySelectorAll('#invoicePreviewTable tbody tr').forEach((row) => bindEditableRow(row));

    function createCellInput(name, value, isNumeric, rowKey, colLabel) {
      const input = document.createElement('input');
      input.type = 'text';
      input.name = name;
      input.value = value || '';
      input.className = 'cell-input' + (isNumeric ? ' cell-number js-number' : '');
      input.dataset.rowIndex = rowKey;
      input.dataset.colLabel = colLabel;
      return input;
    }

    function createLineTaxCell(rowKey, tax) {
      const td = document.createElement('td');
      td.className = 'line-tax-col line-tax-col-cell';
      td.dataset.lineTaxId = String(tax.id);
      td.style.display = 'none';

      const wrap = document.createElement('div');
      wrap.className = 'line-tax-cell-wrap';

      const label = document.createElement('label');
      const check = document.createElement('input');
      check.type = 'checkbox';
      check.className = 'row-line-tax-check';
      check.dataset.rowKey = rowKey;
      check.dataset.taxId = String(tax.id);
      check.dataset.taxName = String(tax.name);
      check.dataset.taxRate = String(tax.percentage);
      check.disabled = true;
      label.appendChild(check);
      label.appendChild(document.createTextNode(' Apply'));

      const detail = document.createElement('div');
      detail.className = 'line-tax-detail';

      wrap.appendChild(label);
      wrap.appendChild(detail);
      td.appendChild(wrap);
      return td;
    }

    let addRowCounter = <?php echo e(count($oldAddedRows)); ?>;
    const addBtn = document.getElementById('addLineItemBtn');
    const tbody = document.querySelector('#invoicePreviewTable tbody');
    const includeColChecks = Array.from(document.querySelectorAll('.js-include-col'));

    function isNumericColumn(label) {
      const l = String(label || '').toLowerCase();
      return l.includes('qty') || l.includes('quantity') || l.includes('price') || l.includes('rate') || l.includes('amount') || l.includes('total');
    }

    function selectedPreviewCols() {
      const selected = includeColChecks
        .filter((el) => el.checked)
        .map((el) => String(el.value || ''));
      if (IS_AUTOMATIC && PRICE_COLUMN && !selected.includes(PRICE_COLUMN)) {
        selected.push(PRICE_COLUMN);
      }
      return selected;
    }

    function applyColumnVisibility() {
      const selected = selectedPreviewCols();
      const table = document.getElementById('invoicePreviewTable');
      if (!table) return;

      INCLUDE_COLS.forEach((col, index) => {
        const visible = selected.includes(col);
        const th = table.querySelector(`thead th[data-col-index="${index}"]`);
        if (th) {
          th.style.display = visible ? '' : 'none';
        }

        const inputs = table.querySelectorAll(`tbody input[data-col-index="${index}"]`);
        inputs.forEach((input) => {
          const td = input.closest('td');
          if (td) {
            td.style.display = visible ? '' : 'none';
          }
          input.disabled = !visible;
        });
      });

      recalcTotal();
    }

    if (addBtn && tbody && IS_AUTOMATIC) {
      addBtn.addEventListener('click', () => {
        const idx = addRowCounter++;
        const rowKey = `new-${idx}`;
        const tr = document.createElement('tr');
        tr.setAttribute('data-added-row', '1');
        tr.setAttribute('data-added-index', String(idx));
        tr.setAttribute('data-row-key', rowKey);

        const checkTd = document.createElement('td');
        checkTd.className = 'check-col';
        const chk = document.createElement('input');
        chk.type = 'checkbox';
        chk.className = 'row-check';
        chk.name = `added_rows[${idx}][selected]`;
        chk.value = '1';
        chk.checked = true;
        checkTd.appendChild(chk);
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'remove-row-btn';
        removeBtn.title = 'Remove added line item';
        removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
        checkTd.appendChild(removeBtn);
        tr.appendChild(checkTd);

        INCLUDE_COLS.forEach((col, colIndex) => {
          const td = document.createElement('td');
          const input = createCellInput(`added_rows[${idx}][cells][${colIndex}]`, '', isNumericColumn(col), rowKey, col);
          input.dataset.colIndex = String(colIndex);
          td.appendChild(input);
          tr.appendChild(td);
        });
        LINE_TAXES.forEach((tax) => {
          tr.appendChild(createLineTaxCell(rowKey, tax));
        });

        tbody.appendChild(tr);
        bindEditableRow(tr);
        applyColumnVisibility();
        applyLineTaxColumnVisibility();
        recalcTotal();
      });
    }

    includeColChecks.forEach((checkbox) => {
      checkbox.addEventListener('change', () => {
        applyColumnVisibility();
      });
    });

    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.remove-row-btn');
      if (!btn) return;
      const row = btn.closest('tr[data-added-row="1"]');
      if (!row) return;
      row.remove();
      if (checkAll) {
        const allChecks = Array.from(document.querySelectorAll('.row-check'));
        checkAll.checked = allChecks.length > 0 && allChecks.every(c => c.checked);
      }
      recalcTotal();
    });

    const taxable = document.getElementById('taxableInvoiceToggle');
    const taxChecks = document.querySelectorAll('.tax-check');
    const invoiceTaxChecks = document.querySelectorAll('.invoice-tax-check');
    const lineTaxOptions = document.getElementById('lineTaxOptions');
    const invoiceTaxOptions = document.getElementById('invoiceTaxOptions');
    const selectAllLineTaxes = document.getElementById('selectAllLineTaxes');
    const clearLineTaxes = document.getElementById('clearLineTaxes');
    const selectAllInvoiceTaxes = document.getElementById('selectAllInvoiceTaxes');
    const clearInvoiceTaxes = document.getElementById('clearInvoiceTaxes');

    function applyTaxableState() {
      if (!taxable) return;
      const isOn = taxable.checked;
      if (lineTaxOptions) lineTaxOptions.classList.toggle('show', isOn);
      if (invoiceTaxOptions) invoiceTaxOptions.classList.toggle('show', isOn);
      taxChecks.forEach((chk) => {
        chk.disabled = !isOn;
        if (!isOn) chk.checked = false;
      });
      invoiceTaxChecks.forEach((chk) => {
        chk.disabled = !isOn;
        if (!isOn) chk.checked = false;
      });
      document.querySelectorAll('.row-line-tax-check').forEach((chk) => {
        chk.disabled = !isOn;
        if (!isOn) chk.checked = false;
      });
      if (taxableFlag) {
        taxableFlag.value = isOn ? '1' : '0';
      }
      applyLineTaxColumnVisibility();
    }

    if (taxable) {
      taxable.addEventListener('change', () => {
        applyTaxableState();
        recalcTotal();
      });
      applyTaxableState();
    }

    if (selectAllLineTaxes) {
      selectAllLineTaxes.addEventListener('click', () => {
        if (!taxable || !taxable.checked) return;
        taxChecks.forEach((chk) => {
          chk.checked = true;
        });
        recalcTotal();
      });
    }
    if (clearLineTaxes) {
      clearLineTaxes.addEventListener('click', () => {
        taxChecks.forEach((chk) => {
          chk.checked = false;
        });
        recalcTotal();
      });
    }
    if (selectAllInvoiceTaxes) {
      selectAllInvoiceTaxes.addEventListener('click', () => {
        if (!taxable || !taxable.checked) return;
        invoiceTaxChecks.forEach((chk) => {
          chk.checked = true;
        });
        recalcTotal();
      });
    }
    if (clearInvoiceTaxes) {
      clearInvoiceTaxes.addEventListener('click', () => {
        invoiceTaxChecks.forEach((chk) => {
          chk.checked = false;
        });
        recalcTotal();
      });
    }

    taxChecks.forEach((chk) => {
      chk.addEventListener('change', () => {
        applyLineTaxColumnVisibility();
        recalcTotal();
      });
    });

    invoiceTaxChecks.forEach((chk) => {
      chk.addEventListener('change', () => {
        recalcTotal();
      });
    });

    document.addEventListener('change', (e) => {
      if (!e.target.classList || !e.target.classList.contains('row-line-tax-check')) return;
      recalcTotal();
    });

    const generateForm = document.getElementById('generateInvoiceForm');
    if (generateForm) {
      generateForm.addEventListener('submit', () => {
        if (!lineTaxMatrixInput) return;
        const matrix = {};
        document.querySelectorAll('#invoicePreviewTable tbody tr').forEach((row) => {
          const rowCheck = row.querySelector('.row-check');
          if (!(rowCheck && rowCheck.checked)) return;
          const rowKey = String(row.dataset.rowKey || '');
          if (rowKey === '') return;
          const taxIds = Array.from(row.querySelectorAll('.row-line-tax-check:checked'))
            .filter((chk) => !chk.disabled)
            .map((chk) => Number(chk.dataset.taxId || 0))
            .filter((id) => Number.isInteger(id) && id > 0);
          if (taxIds.length > 0) {
            matrix[rowKey] = Array.from(new Set(taxIds));
          }
        });
        lineTaxMatrixInput.value = JSON.stringify(matrix);
      });
    }

    if (recurringToggle && !recurringToggle.disabled) {
      recurringToggle.addEventListener('click', () => {
        const next = !(isRecurringFlag && isRecurringFlag.value === '1');
        setRecurringState(next);
      });
    }

    if (toggleBankDetails) {
      toggleBankDetails.addEventListener('change', () => {
        setBankDetailsState(!!toggleBankDetails.checked);
      });
    }

    if (includeColChecks.length > 0) {
      includeColChecks.forEach((checkbox) => {
        checkbox.checked = INITIAL_PREVIEW_INCLUDE_COLS.includes(String(checkbox.value || ''));
      });
    }
    setRecurringState(!!(isRecurringFlag && isRecurringFlag.value === '1'));
    setBankDetailsState(!!(showBankDetailsFlag && showBankDetailsFlag.value === '1'));
    applyColumnVisibility();
    applyLineTaxColumnVisibility();
    recalcTotal();
  })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/invoices/generate.blade.php ENDPATH**/ ?>