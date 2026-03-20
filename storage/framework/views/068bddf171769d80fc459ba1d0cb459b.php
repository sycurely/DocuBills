

<?php $__env->startSection('title', 'Login - DocuBills'); ?>

<?php $__env->startSection('content'); ?>
  <h2 class="auth-title">Login</h2>

  <form method="POST" action="<?php echo e(route('login')); ?>">
    <?php echo csrf_field(); ?>
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" value="<?php echo e(old('username')); ?>" required autofocus>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" name="password" id="password" required>
    </div>

    <button type="submit" class="btn">Login</button>
  </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/auth/login.blade.php ENDPATH**/ ?>