

<?php $__env->startSection('title', 'Invoice Management'); ?>

<?php $__env->startPush('styles'); ?>
<style>

    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --dark: #212529;
      --light: #f8f9fa;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
      --radius: 10px;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .page-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
    }

    .btn {
      padding: 0.75rem 1.5rem;
      border-radius: var(--radius);
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--secondary);
    }

    .btn-success {
      background: var(--success);
      color: white;
    }

    .btn-danger {
      background: var(--danger);
      color: white;
    }

    .table-container {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }

    th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
    }

    tbody tr:hover {
      background: rgba(67, 97, 238, 0.05);
    }

    .badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.875rem;
      font-weight: 600;
    }

    .badge-success {
      background: var(--success);
      color: white;
    }

    .badge-danger {
      background: var(--danger);
      color: white;
    }

    .search-filters {
      display: flex;
      gap: 1rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }

    .search-input, .filter-select {
      padding: 0.75rem;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      font-size: 1rem;
    }

    .search-input {
      flex: 1;
      min-width: 200px;
    }
    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1200;
      padding: 1rem;
    }
    .modal-backdrop.show {
      display: flex;
    }
    .modal-card {
      background: #f8f9fa;
      border-radius: 12px;
      box-shadow: var(--shadow);
      width: 100%;
      max-width: 680px;
      padding: 1.8rem;
      position: relative;
    }
    .modal-close {
      position: absolute;
      right: 1rem;
      top: 0.8rem;
      border: none;
      background: none;
      font-size: 1.6rem;
      color: #334155;
      cursor: pointer;
      line-height: 1;
    }
    .modal-title {
      margin: 0 0 1.5rem 0;
      text-align: center;
      color: var(--secondary);
      font-size: 1.9rem;
      font-weight: 700;
    }
    .modal-field {
      margin-bottom: 1.2rem;
    }
    .modal-label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    .modal-select {
      width: 100%;
      max-width: 280px;
      padding: 0.7rem 0.8rem;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 1rem;
      background: #fff;
    }
    .proof-dropzone {
      display: block;
      border: 2px dashed #4895ef;
      padding: 2rem;
      border-radius: 12px;
      background: #eef4ff;
      text-align: center;
      cursor: pointer;
    }
    .proof-dropzone i {
      font-size: 2rem;
      color: #4895ef;
      margin-bottom: 0.4rem;
    }
    .proof-dropzone p {
      margin: 0;
      color: #1f2937;
      font-size: 1.05rem;
    }
    .modal-actions {
      margin-top: 1.8rem;
      display: flex;
      justify-content: flex-end;
      gap: 0.8rem;
    }
    .btn-cancel {
      background: #adb5bd;
      color: white;
    }
    .btn-action-icon {
      padding: 0.5rem;
      background: var(--success);
      color: white;
    }
  
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Invoice Management</h1>
      <?php if(has_permission('create_invoice')): ?>
        <a href="<?php echo e(route('invoices.create')); ?>" class="btn btn-primary">
          <i class="fas fa-plus"></i> Create Invoice
        </a>
      <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
      <div style="background: var(--success); color: white; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
        <?php echo e(session('success')); ?>

      </div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
      <div style="background: #fef2f2; color: #b91c1c; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; border: 1px solid #fecaca;">
        <?php echo e($errors->first()); ?>

      </div>
    <?php endif; ?>

    <form method="GET" action="<?php echo e(route('invoices.index')); ?>" class="search-filters">
      <input type="text" name="search" class="search-input" placeholder="Search by invoice number or client..." value="<?php echo e(request('search')); ?>">
      <select name="status" class="filter-select">
        <option value="">All Statuses</option>
        <option value="Paid" <?php echo e(request('status') === 'Paid' ? 'selected' : ''); ?>>Paid</option>
        <option value="Unpaid" <?php echo e(request('status') === 'Unpaid' ? 'selected' : ''); ?>>Unpaid</option>
      </select>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-search"></i> Search
      </button>
    </form>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Invoice Number</th>
            <th>Client</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><strong><?php echo e($invoice->invoice_number); ?></strong></td>
              <td><?php echo e($invoice->bill_to_name ?? ($invoice->client->company_name ?? 'N/A')); ?></td>
              <td><?php echo e($invoice->currency_display ?? $invoice->currency_code); ?> <?php echo e(number_format($invoice->total_amount, 2)); ?></td>
              <td><?php echo e($invoice->invoice_date->format('Y-m-d')); ?></td>
              <td><?php echo e($invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'N/A'); ?></td>
              <td>
                <span class="badge <?php echo e($invoice->status === 'Paid' ? 'badge-success' : 'badge-danger'); ?>">
                  <?php echo e($invoice->status); ?>

                </span>
              </td>
              <td>
                <a href="<?php echo e(route('invoices.show', $invoice)); ?>" class="btn" style="padding: 0.5rem; background: var(--primary); color: white;">
                  <i class="fas fa-eye"></i>
                </a>
                <?php if(has_permission('download_invoice_pdf')): ?>
                  <a href="<?php echo e(route('invoices.download-pdf', $invoice)); ?>" class="btn" style="padding: 0.5rem; background: var(--success); color: white;">
                    <i class="fas fa-download"></i>
                  </a>
                <?php endif; ?>
                <?php if(has_permission('mark_invoice_paid') && $invoice->status === 'Unpaid'): ?>
                  <button
                    type="button"
                    class="btn btn-action-icon js-mark-paid-btn"
                    data-action="<?php echo e(route('invoices.mark-paid', $invoice)); ?>"
                    data-invoice="<?php echo e($invoice->invoice_number); ?>"
                    aria-label="Mark invoice <?php echo e($invoice->invoice_number); ?> as paid"
                  >
                    <i class="fas fa-check"></i>
                  </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem;">
                No invoices found.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div style="margin-top: 1.5rem;">
      <?php echo e($invoices->links()); ?>

    </div>
  </div>

  <div class="modal-backdrop" id="markPaidModal">
    <div class="modal-card">
      <button type="button" class="modal-close" id="closeMarkPaidModal" aria-label="Close">&times;</button>
      <h2 class="modal-title"><i class="fas fa-receipt"></i> Confirm Invoice Payment</h2>

      <form id="markPaidForm" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <input type="hidden" id="markPaidInvoiceNumber" value="">

        <div class="modal-field">
          <label class="modal-label" for="payment_method">Payment Method</label>
          <select class="modal-select" name="payment_method" id="payment_method" required>
            <option value="">-- Select Method --</option>
            <option value="Cheque">Cheque</option>
            <option value="Direct Debit">Direct Debit</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Cash">Cash</option>
          </select>
        </div>

        <div class="modal-field">
          <label class="modal-label" for="payment_proof">Upload Proof of Payment (optional)</label>
          <label class="proof-dropzone" for="payment_proof">
            <i class="fas fa-file-upload"></i>
            <p id="proofLabel">Click to browse or drag a file here</p>
          </label>
          <input type="file" name="payment_proof" id="payment_proof" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-cancel" id="cancelMarkPaid">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Mark as Paid</button>
        </div>
      </form>
    </div>
  </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
  (() => {
    const modal = document.getElementById('markPaidModal');
    const closeBtn = document.getElementById('closeMarkPaidModal');
    const cancelBtn = document.getElementById('cancelMarkPaid');
    const form = document.getElementById('markPaidForm');
    const proofInput = document.getElementById('payment_proof');
    const proofLabel = document.getElementById('proofLabel');
    const methodSelect = document.getElementById('payment_method');
    const invoiceInput = document.getElementById('markPaidInvoiceNumber');

    const openModal = (actionUrl, invoiceNumber) => {
      form.action = actionUrl;
      invoiceInput.value = invoiceNumber || '';
      methodSelect.value = '';
      proofInput.value = '';
      proofLabel.textContent = 'Click to browse or drag a file here';
      modal.classList.add('show');
    };

    const closeModal = () => {
      modal.classList.remove('show');
    };

    document.querySelectorAll('.js-mark-paid-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        openModal(btn.dataset.action, btn.dataset.invoice || '');
      });
    });

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
      if (event.target === modal) {
        closeModal();
      }
    });

    proofInput.addEventListener('change', () => {
      const file = proofInput.files && proofInput.files.length > 0 ? proofInput.files[0] : null;
      proofLabel.textContent = file ? file.name : 'Click to browse or drag a file here';
    });
  })();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/invoices/index.blade.php ENDPATH**/ ?>