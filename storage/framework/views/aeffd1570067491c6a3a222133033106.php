<?php
  $activeMenu = 'settings';
  $activeTab = 'payments';
?>

<?php $__env->startSection('title', 'Settings - Payment Methods'); ?>

<?php $__env->startSection('content'); ?>
  <div class="page-header">
    <h1 class="page-title">Payment Methods</h1>
    <p class="page-subtitle">Manage Stripe payments and default bank transfer details used in invoice generation.</p>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
  <?php endif; ?>
  <?php if($errors->any()): ?>
    <div class="alert alert-danger">
      <strong>There were some problems with your input.</strong>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?php echo e(route('settings.payment-methods.update')); ?>">
    <?php echo csrf_field(); ?>

    <?php if(has_permission('manage_card_payments')): ?>
      <div class="settings-card max-w-900">
        <h3>Card Payments (Stripe)</h3>
        <div class="form-row">
          <label for="stripe_publishable_key">Stripe Publishable Key</label>
          <input id="stripe_publishable_key" type="text" name="settings[stripe_publishable_key]" value="<?php echo e(setting('stripe_publishable_key')); ?>">
        </div>
        <div class="form-row">
          <label for="stripe_secret_key">Stripe Secret Key</label>
          <input id="stripe_secret_key" type="text" name="settings[stripe_secret_key]" value="<?php echo e(setting('stripe_secret_key')); ?>">
        </div>
        <div class="form-row">
          <label for="stripe_webhook_secret">Stripe Webhook Secret</label>
          <input id="stripe_webhook_secret" type="text" name="settings[stripe_webhook_secret]" value="<?php echo e(setting('stripe_webhook_secret')); ?>">
        </div>
        <div class="form-row">
          <label for="test_mode">Test Mode</label>
          <select id="test_mode" name="settings[test_mode]">
            <option value="0" <?php echo e(setting('test_mode', '0') === '0' ? 'selected' : ''); ?>>Disabled</option>
            <option value="1" <?php echo e(setting('test_mode', '0') === '1' ? 'selected' : ''); ?>>Enabled</option>
          </select>
          <small>Use Stripe test keys to create test Checkout sessions.</small>
        </div>
      </div>
    <?php endif; ?>

    <?php if(has_permission('manage_bank_details')): ?>
      <div class="settings-card max-w-900">
        <h3>Bank Transfer Details</h3>
        <div class="form-row">
          <label for="bank_account_name">Account Holder Name</label>
          <input id="bank_account_name" type="text" name="settings[bank_account_name]" value="<?php echo e(setting('bank_account_name')); ?>">
        </div>
        <div class="form-row">
          <label for="bank_name">Bank Name</label>
          <input id="bank_name" type="text" name="settings[bank_name]" value="<?php echo e(setting('bank_name')); ?>">
        </div>
        <div class="form-row">
          <label for="bank_account_number">Account Number</label>
          <input id="bank_account_number" type="text" name="settings[bank_account_number]" value="<?php echo e(setting('bank_account_number')); ?>">
        </div>
        <div class="form-row">
          <label for="bank_iban">IBAN</label>
          <input id="bank_iban" type="text" name="settings[bank_iban]" value="<?php echo e(setting('bank_iban')); ?>">
        </div>
        <div class="form-row">
          <label for="bank_swift">SWIFT / BIC</label>
          <input id="bank_swift" type="text" name="settings[bank_swift]" value="<?php echo e(setting('bank_swift')); ?>">
        </div>
        <div class="form-row">
          <label for="bank_routing">Routing / Sort Code</label>
          <input id="bank_routing" type="text" name="settings[bank_routing]" value="<?php echo e(setting('bank_routing')); ?>">
        </div>
        <div class="form-row">
          <label for="bank_additional_info">Additional Payment Instructions</label>
          <textarea id="bank_additional_info" rows="3" name="settings[bank_additional_info]"><?php echo e(setting('bank_additional_info')); ?></textarea>
        </div>
      </div>
    <?php endif; ?>

    <div class="settings-actions">
      <button type="submit" class="btn btn-primary">Save Payment Methods</button>
    </div>
  </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/settings/payment-methods.blade.php ENDPATH**/ ?>