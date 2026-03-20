<?php
  $appName = setting('company_name', 'DocuBills');
  $currentYear = date('Y');
?>

<footer class="app-footer">
  <div class="footer-content">
    <div class="footer-text">
      <p>&copy; <?php echo e($currentYear); ?> <?php echo e($appName); ?>. All rights reserved.</p>
    </div>
    <div class="footer-links">
      <a href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
      <span class="separator">|</span>
      <a href="<?php echo e(route('settings.index')); ?>">Settings</a>
    </div>
  </div>
</footer>
<?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/partials/footer.blade.php ENDPATH**/ ?>