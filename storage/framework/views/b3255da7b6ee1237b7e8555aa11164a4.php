<?php
  $activeMenu = $activeMenu ?? 'dashboard';
  $activeTab = $activeTab ?? '';
  $activeSub = $activeSub ?? '';
  $isSettingsPage = ($activeMenu === 'settings');
  $isExpensesPage = in_array($activeMenu, ['expenses', 'expenses_trash']);
?>

<div class="sidebar">
  <div class="sidebar-menu">
    <!-- MAIN LINKS - Always show for authenticated users; routes enforce permissions -->
    <a href="<?php echo e(route('dashboard')); ?>" class="menu-item <?php echo e($activeMenu === 'dashboard' ? 'active' : ''); ?>">
      <span class="material-icons-outlined">monitoring</span>
      <span class="menu-text">Dashboard</span>
    </a>

    <a href="<?php echo e(route('invoices.create')); ?>" class="menu-item <?php echo e($activeMenu === 'create-invoice' ? 'active' : ''); ?>">
      <span class="material-icons-outlined">post_add</span>
      <span class="menu-text">Create Invoice</span>
    </a>

    <a href="<?php echo e(route('invoices.index')); ?>" class="menu-item <?php echo e($activeMenu === 'invoices' ? 'active' : ''); ?>">
      <span class="material-icons-outlined">history</span>
      <span class="menu-text">Invoice History</span>
    </a>

    <a href="<?php echo e(route('clients.index')); ?>" class="menu-item <?php echo e($activeMenu === 'clients' ? 'active' : ''); ?>">
      <span class="material-icons-outlined">groups</span>
      <span class="menu-text">Clients</span>
    </a>

    <div class="menu-item has-submenu <?php echo e($isExpensesPage ? 'active' : ''); ?>">
      <span class="material-icons-outlined">account_balance_wallet</span>
      <span class="menu-text">Expenses</span>
      <span class="material-icons-outlined submenu-toggle-icon">expand_more</span>
    </div>
    <div class="submenu <?php echo e($isExpensesPage ? 'show' : ''); ?>">
      <a href="<?php echo e(route('expenses.index')); ?>" class="submenu-item <?php echo e($activeMenu === 'expenses' ? 'active' : ''); ?>">
        <span class="material-icons-outlined">list</span> All Expenses
      </a>
    </div>

    <div class="menu-item has-submenu <?php echo e($isSettingsPage ? 'active' : ''); ?>">
      <span class="material-icons-outlined">settings</span>
      <span class="menu-text">Settings</span>
      <span class="material-icons-outlined submenu-toggle-icon">expand_more</span>
    </div>
    <div class="submenu <?php echo e($isSettingsPage ? 'show' : ''); ?>">
      <a href="<?php echo e(route('settings.index')); ?>" class="submenu-item <?php echo e($activeTab === 'basic' ? 'active' : ''); ?>">
        <span class="material-icons-outlined">tune</span> Basic
      </a>
      <a href="<?php echo e(route('users.index')); ?>" class="submenu-item <?php echo e($activeTab === 'users' ? 'active' : ''); ?>">
        <span class="material-icons-outlined">admin_panel_settings</span> Users
      </a>
      <a href="<?php echo e(route('settings.permissions')); ?>" class="submenu-item <?php echo e($activeTab === 'permissions' ? 'active' : ''); ?>">
        <span class="material-icons-outlined">key</span> Permissions
      </a>
      <?php if(
          has_permission('manage_payment_methods') ||
          has_permission('update_basic_settings') ||
          has_permission('manage_card_payments') ||
          has_permission('manage_bank_details')
      ): ?>
        <a href="<?php echo e(route('settings.payment-methods')); ?>" class="submenu-item <?php echo e($activeTab === 'payments' ? 'active' : ''); ?>">
          <span class="material-icons-outlined">credit_card</span> Payment Methods
        </a>
      <?php endif; ?>
      <?php if(has_permission('manage_reminder_settings')): ?>
        <a href="<?php echo e(route('settings.reminders')); ?>" class="submenu-item <?php echo e($activeTab === 'reminders' ? 'active' : ''); ?>">
          <span class="material-icons-outlined">notifications_active</span> Reminder Settings
        </a>
      <?php endif; ?>
      <a href="<?php echo e(route('settings.taxes')); ?>" class="submenu-item <?php echo e($activeTab === 'taxes' ? 'active' : ''); ?>">
        <span class="material-icons-outlined">percent</span> Tax Classes
      </a>
      <a href="<?php echo e(route('email-templates.index')); ?>" class="submenu-item <?php echo e($activeTab === 'email_templates' ? 'active' : ''); ?>">
        <span class="material-icons-outlined">mail</span> Email Templates
      </a>
    </div>

    <?php if(has_permission('view_login_logs')): ?>
      <a href="<?php echo e(route('login-logs.index')); ?>" class="menu-item <?php echo e(request()->routeIs('login-logs.*') || $activeMenu === 'login-logs' ? 'active' : ''); ?>">
        <span class="material-icons-outlined">fact_check</span>
        <span class="menu-text">Login Logs</span>
      </a>
    <?php endif; ?>

    <?php if(has_permission('access_trashbin')): ?>
      <a href="<?php echo e(route('trash-bin.index')); ?>" class="menu-item <?php echo e(request()->routeIs('trash-bin.*') || $activeMenu === 'trashbin' ? 'active' : ''); ?>">
        <span class="material-icons-outlined">restore_from_trash</span>
        <span class="menu-text">Trash Bin</span>
      </a>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline-form">
      <?php echo csrf_field(); ?>
      <button type="submit" class="menu-item menu-item-logout unstyled-button full-width text-left">
        <span class="material-icons-outlined">logout</span>
        <span class="menu-text">Logout</span>
      </button>
    </form>
  </div>
</div>
<?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>