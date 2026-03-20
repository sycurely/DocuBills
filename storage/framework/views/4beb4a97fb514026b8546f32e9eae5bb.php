<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $__env->yieldContent('title', 'DocuBills'); ?></title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
    // Add dark-mode class early before CSS is evaluated
    if (localStorage.getItem('darkMode') === '1') {
      document.documentElement.classList.add('dark-mode');
    }
  </script>
  <?php echo $__env->make('partials.styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
  <div class="app-container">
    <?php echo $__env->make('partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <div class="main-content-wrapper">
      <div class="main-content">
        <?php if(session('success')): ?>
          <div class="alert alert-success">
            <span class="material-icons-outlined">check_circle</span>
            <?php echo e(session('success')); ?>

          </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
          <div class="alert alert-danger">
            <span class="material-icons-outlined">error</span>
            <?php echo e(session('error')); ?>

          </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
      </div>
      
      <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
  </div>

  <?php echo $__env->yieldPushContent('scripts'); ?>
  <script>
    // Theme toggle functionality
    document.addEventListener('DOMContentLoaded', () => {
      const themeToggle = document.getElementById('themeToggle');
      if (themeToggle) {
        const icon = themeToggle.querySelector('.material-icons-outlined');
        const darkPref = localStorage.getItem('darkMode');
        const isDarkInitially = darkPref === '1';
        document.documentElement.classList.toggle('dark-mode', isDarkInitially);
        icon.textContent = isDarkInitially ? 'light_mode' : 'dark_mode';
        
        themeToggle.addEventListener('click', () => {
          const nowDark = document.documentElement.classList.toggle('dark-mode');
          localStorage.setItem('darkMode', nowDark ? '1' : '0');
          icon.textContent = nowDark ? 'light_mode' : 'dark_mode';
        });
      }

      // Sidebar submenu toggle
      document.querySelectorAll('.has-submenu').forEach(el => {
        el.addEventListener('click', () => {
          const submenu = el.nextElementSibling;
          if (submenu && submenu.classList.contains('submenu')) {
            submenu.classList.toggle('show');
            el.classList.toggle('active');
          }
        });
      });

      // User profile menu toggle
      const userProfileTrigger = document.getElementById('userProfileTrigger');
      const profileMenu = document.getElementById('profileMenu');
      if (userProfileTrigger && profileMenu) {
        userProfileTrigger.addEventListener('click', (e) => {
          e.stopPropagation();
          profileMenu.style.display = profileMenu.style.display === 'flex' ? 'none' : 'flex';
        });

        document.addEventListener('click', (e) => {
          if (!userProfileTrigger.contains(e.target) && !profileMenu.contains(e.target)) {
            profileMenu.style.display = 'none';
          }
        });
      }
    });
  </script>
</body>
</html>
<?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/layouts/app.blade.php ENDPATH**/ ?>