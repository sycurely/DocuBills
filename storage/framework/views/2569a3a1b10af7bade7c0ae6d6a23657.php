<?php $__env->startSection('title', 'Configure Invoice Pricing'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  .card {
    background: var(--card-bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  .page-title {
    color: var(--primary);
    margin-bottom: 1rem;
  }
  .section-title {
    color: var(--primary);
    font-size: 1.9rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .pricing-box {
    border: 2px solid #4361ee;
    border-radius: 12px;
    padding: 1rem;
    background: rgba(67, 97, 238, 0.04);
    margin-top: 0.75rem;
  }
  .columns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.5rem 1rem;
    margin-top: 0.75rem;
  }
  .hint {
    color: #6c757d;
    font-size: 0.92rem;
  }
  .warning-note {
    margin-top: 0.75rem;
    background: #f4efdf;
    border-left: 4px solid #f8961e;
    padding: 0.8rem;
    border-radius: 6px;
  }
  .actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1rem;
  }
  .btn-link {
    text-decoration: none;
  }
  .error {
    color: var(--danger);
    font-size: 0.9rem;
    margin-top: 0.35rem;
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
  <?php
    $validationMessages = \App\Services\InvoiceValidationContract::uiMessages();
    $oldMode = old('price_mode', 'automatic');
    if ($oldMode === 'column') {
      $oldMode = 'automatic';
    }
    $selectedPriceColumn = old('price_column', $recommendedPriceColumn ?? '');
    $selectedIncludeCols = old('include_cols', $recommendedIncludeCols ?? array_slice($headers, 0, 15));
    if (!is_array($selectedIncludeCols)) {
      $selectedIncludeCols = $recommendedIncludeCols ?? array_slice($headers, 0, 15);
    }
  ?>
  <h1 class="section-title"><i class="fas fa-money-bill-wave"></i> Configure Invoice Pricing</h1>

  <form method="POST" action="<?php echo e(route('invoices.price-select.save')); ?>">
    <?php echo csrf_field(); ?>

    <?php if(session('error')): ?>
      <div class="card" style="border-left:4px solid var(--danger);">
        <div class="error" style="margin-top:0;"><?php echo e(session('error')); ?></div>
      </div>
    <?php endif; ?>

    <div class="card">
      <h2 class="page-title">Pricing Method</h2>

      <label style="display:block; margin-bottom:0.6rem;">
        <input type="radio" name="price_mode" value="automatic" <?php echo e($oldMode === 'automatic' ? 'checked' : ''); ?>>
        <strong>Automatic Pricing</strong> - Use a column from my data
      </label>

      <div class="pricing-box" id="automatic-box">
        <p>Select which column contains item prices:</p>
        <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <label style="display:block; margin:0.45rem 0;">
            <input type="radio" name="price_column" value="<?php echo e($header); ?>" <?php echo e($selectedPriceColumn === $header ? 'checked' : ''); ?>>
            Column: <strong><?php echo e($header); ?></strong>
          </label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php $__errorArgs = ['price_column'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
          <div class="error"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <label style="display:block; margin-top:1.2rem; margin-bottom:0.6rem;">
        <input type="radio" name="price_mode" value="manual" <?php echo e($oldMode === 'manual' ? 'checked' : ''); ?>>
        <strong>Manual Pricing</strong> - I'll enter the total invoice amount myself
      </label>

      <div class="warning-note" id="manual-box">
        <p class="hint">You'll enter the total amount on the next screen.</p>
      </div>

      <?php $__errorArgs = ['price_mode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="error"><?php echo e($message); ?></div>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="card">
      <h2 class="page-title">Columns to Include <span class="hint">(max 15)</span></h2>
      <div class="columns-grid">
        <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <label>
            <input
              type="checkbox"
              name="include_cols[]"
              value="<?php echo e($header); ?>"
              <?php echo e(in_array($header, $selectedIncludeCols, true) ? 'checked' : ''); ?>

            >
            <?php echo e($header); ?>

          </label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <p class="hint" style="margin-top:0.75rem;">Selected columns will be included in each imported line item description.</p>
      <div id="includeColsError" class="error" style="display:none;"></div>
      <?php $__errorArgs = ['include_cols'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="error"><?php echo e($message); ?></div>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="card">
      <h2 class="page-title">Invoice Currency</h2>
      <p class="hint">This locks the invoice display currency for the next step.</p>
      <div style="max-width: 240px;">
        <select name="currency_code" class="form-control">
          <?php $__currentLoopData = $currencyOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option
              value="<?php echo e($code); ?>"
              <?php echo e(old('currency_code', $defaultCurrencyCode) === $code ? 'selected' : ''); ?>

            >
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
        <div class="error"><?php echo e($message); ?></div>
      <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="actions">
      <a href="<?php echo e(route('invoices.create')); ?>" class="btn btn-secondary btn-link">Back</a>
      <button type="submit" class="btn btn-primary">Continue</button>
    </div>
  </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
  const VALIDATION_MSG = <?php echo json_encode($validationMessages, 15, 512) ?>;
  function setPricingMode(mode) {
    const automaticBox = document.getElementById('automatic-box');
    const manualBox = document.getElementById('manual-box');
    const priceRadios = document.querySelectorAll('input[name="price_column"]');

    const automatic = mode === 'automatic';

    automaticBox.style.opacity = automatic ? '1' : '0.55';
    manualBox.style.opacity = automatic ? '0.55' : '1';

    priceRadios.forEach(el => el.disabled = !automatic);

    if (!automatic) {
      priceRadios.forEach(el => el.checked = false);
    }
  }

  document.querySelectorAll('input[name="price_mode"]').forEach(el => {
    el.addEventListener('change', () => setPricingMode(el.value));
  });

  const initialMode = document.querySelector('input[name="price_mode"]:checked')?.value || 'automatic';
  setPricingMode(initialMode);

  document.querySelector('form').addEventListener('submit', function (e) {
    const includeColsError = document.getElementById('includeColsError');
    if (includeColsError) {
      includeColsError.textContent = '';
      includeColsError.style.display = 'none';
    }

    const checked = document.querySelectorAll('input[name="include_cols[]"]:checked');
    if (checked.length === 0) {
      e.preventDefault();
      if (includeColsError) {
        includeColsError.textContent = VALIDATION_MSG.include_cols_min;
        includeColsError.style.display = 'block';
      }
      return;
    }
    if (checked.length > 15) {
      e.preventDefault();
      if (includeColsError) {
        includeColsError.textContent = VALIDATION_MSG.include_cols_max;
        includeColsError.style.display = 'block';
      }
    }
  });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/invoices/price-select.blade.php ENDPATH**/ ?>