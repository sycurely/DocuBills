<?php 
$activeMenu = $activeMenu ?? 'dashboard'; 
$activeTab  = $activeTab  ?? '';
$activeSub  = $activeSub  ?? '';
require_once 'middleware.php'; // ✅ for has_permission()

// Determine if settings submenu should be expanded
$isSettingsPage = ($activeMenu === 'settings');
?>

<style>

   /* Sidebar Styles */
    .sidebar {
      width: var(--sidebar-width);
      background: var(--sidebar-bg);
      color: white;
      height: calc(100vh - var(--header-height));
      position: fixed;
      top: var(--header-height);
      left: 0;
      overflow-y: auto;
      transition: var(--transition);
      z-index: 90;
    }

    .sidebar-menu {
      padding: 1.5rem 0;
    }

    .menu-item {
      padding: 0.8rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 12px;
      cursor: pointer;
      transition: var(--transition);
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      font-weight: 500;
    }

    .menu-item:hover, 
    .menu-item.active {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      border-left: 4px solid var(--primary-light);
    }

    .menu-item i {
      width: 24px;
      text-align: center;
    }

    /* Main Content Styles */
    .main-content {
      flex: 1;
      margin-left: var(--sidebar-width);
      padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem;
      transition: var(--transition);
    }

.menu-item.active,
.submenu-item.active {
  background-color: rgba(255, 255, 255, 0.1);
  position: relative;
}

.submenu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 4px;
  height: 100%;
  background-color: #4361ee;
}

.menu-item.has-submenu.active .submenu-toggle-icon {
  transform: rotate(180deg);
}

.menu-item.has-submenu .submenu-toggle-icon {
  transition: transform 0.3s ease;
}

.submenu {
  display: none;
  flex-direction: column;
  padding-left: 1.5rem;
}

.submenu.show {
  display: flex;
}

.submenu-item {
  padding: 0.5rem 1rem 0.5rem 2.25rem;
  color: #f0f0f0;
  font-size: 0.95rem;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.submenu-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.submenu-toggle-icon {
  margin-left: auto;
  transition: transform 0.3s ease;
}
</style>

<div class="sidebar">
  <div class="sidebar-menu">

    <!-- MAIN LINKS -->
    <?php if (has_permission('view_dashboard')): ?>
        <a href="index.php" class="menu-item <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
          <i class="fas fa-chart-line"></i>
          <span class="menu-text">Dashboard</span>
        </a>
    <?php endif; ?>

    <?php if (has_permission('create_invoice')): ?>
    <a href="create-invoice.php" class="menu-item <?= $activeMenu === 'create-invoice' ? 'active' : '' ?>">
      <i class="fas fa-file-invoice"></i>
      <span class="menu-text">Create Invoice</span>
    </a>
    <?php endif; ?>

    <?php if (has_permission('view_invoice_history')): ?>
      <a href="history.php" class="menu-item <?= $activeMenu === 'history' ? 'active' : '' ?>">
        <i class="fas fa-history"></i>
        <span class="menu-text">Invoice History</span>
      </a>
    <?php endif; ?>

    <?php if (has_permission('access_clients_tab')): ?>
    <a href="clients.php" class="menu-item <?= $activeMenu === 'clients' ? 'active' : '' ?>">
      <i class="fas fa-users"></i>
      <span class="menu-text">Clients</span>
    </a>
    <?php endif; ?>

    
    <?php $isExpensesPage = in_array($activeMenu, ['expenses', 'expenses_trash']); ?>
    <?php if (has_permission('access_expenses_tab')): ?>

    <div class="menu-item has-submenu <?= $isExpensesPage ? 'active' : '' ?>">
      <i class="fas fa-wallet"></i>
      <span class="menu-text">Expenses</span>
      <i class="fas fa-chevron-down submenu-toggle-icon"></i>
    </div>
    <div class="submenu <?= $isExpensesPage ? 'show' : '' ?>">
      <?php if (has_permission('access_expenses_tab')): ?>
          <a href="expenses.php" class="submenu-item <?= $activeMenu === 'expenses' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> All Expenses
          </a>
      <?php endif; ?>
      <?php if (has_permission('view_expenses_trashbin')): ?>
          <a href="trashbin-expenses.php" class="submenu-item <?= $activeMenu === 'expenses_trash' ? 'active' : '' ?>">
            <i class="fas fa-trash"></i> Trash Bin
          </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php
    $showSettings =
        has_permission('access_basic_settings')
        || has_permission('manage_users_page')
        || has_permission('manage_permissions')
        || has_permission('manage_payment_methods')
        || has_permission('manage_reminder_settings')
        || has_permission('access_email_templates_page')
        || has_permission('manage_invoice_templates');
    ?>
    
    <?php if ($showSettings): ?>
      <div class="menu-item has-submenu <?= $isSettingsPage ? 'active' : '' ?>">
        <i class="fas fa-cog"></i>
        <span class="menu-text">Settings</span>
        <i class="fas fa-chevron-down submenu-toggle-icon"></i>
      </div>
    
      <div class="submenu <?= $isSettingsPage ? 'show' : '' ?>">
        <?php if (has_permission('access_basic_settings')): ?>
          <a href="settings-basic.php" class="submenu-item <?= $activeTab === 'basic' ? 'active' : '' ?>">
            <i class="fas fa-sliders-h"></i> Basic
          </a>
        <?php endif; ?>
    
        <?php if (has_permission('manage_users_page')): ?>
        <a href="users.php" class="submenu-item <?= $activeTab === 'users' ? 'active' : '' ?>">
          <i class="fas fa-user-shield"></i> Users
        </a>
        <?php endif; ?>
    
        <?php if (has_permission('manage_permissions')): ?>
        <a href="settings-permissions.php" class="submenu-item <?= $activeTab === 'permissions' ? 'active' : '' ?>">
          <i class="fas fa-key"></i> Permissions
        </a>
        <?php endif; ?>
    
        <?php if (has_permission('manage_payment_methods')): ?>
        <a href="settings-payments.php" class="submenu-item <?= $activeTab === 'payments' ? 'active' : '' ?>">
          <i class="fas fa-credit-card"></i> Payment Methods
        </a>
        <?php endif; ?>

        <a href="settings-taxes.php" class="submenu-item <?= $activeTab === 'taxes' ? 'active' : '' ?>">
          <i class="fas fa-percent"></i> Tax Classes
        </a>
    
        <?php if (has_permission('manage_reminder_settings')): ?>
          <a href="settings-reminders.php" class="submenu-item <?= $activeTab === 'reminder_settings' ? 'active' : '' ?>">
            <i class="fas fa-bell"></i> Reminder Settings
          </a>
        <?php endif; ?>

        <?php if (has_permission('access_email_templates_page')): ?>
        <a href="manage-email-templates.php" class="submenu-item <?= $activeTab === 'email_templates' && $activeSub === '' ? 'active' : '' ?>">
          <i class="fas fa-envelope"></i> Manage Email Templates
        </a>
        <a href="settings-email-templates-list.php" class="submenu-item <?= $activeTab === 'email_templates' && $activeSub === 'existing_templates' ? 'active' : '' ?>">
          <i class="fas fa-layer-group"></i> Existing Email Templates
        </a>
        <?php endif; ?>
        
        <?php if (has_permission('manage_invoice_templates')): ?>
          <a href="settings-invoice-templates.php" class="submenu-item <?= $activeTab==='invoice_templates' && $activeSub==='' ? 'active':'' ?>">
            <i class="fas fa-file-invoice"></i> Invoice Templates
          </a>
          <a href="settings-invoice-templates-list.php" class="submenu-item <?= $activeTab==='invoice_templates' && $activeSub==='existing_templates' ? 'active':'' ?>">
            <i class="fas fa-layer-group"></i> Existing Invoice Templates
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>


    <!-- OTHER ITEMS -->
        
    <?php if (has_permission('view_login_logs')): ?>
    <a href="login-logs.php" class="menu-item <?= $activeMenu === 'login-logs' ? 'active' : '' ?>">
      <i class="fas fa-list-alt"></i>
      <span class="menu-text">Login Logs</span>
    </a>
    <?php endif; ?>
    
    <?php if (has_permission('access_trashbin')): ?>
    <a href="trashbin.php" class="menu-item <?= $activeMenu === 'trashbin' ? 'active' : '' ?>">
      <i class="fas fa-trash-restore"></i>
      <span class="menu-text">Trash Bin</span>
    </a>
    <?php endif; ?>
    
    <?php if (has_permission('access_support')): ?>
    <a href="help.php" class="menu-item <?= $activeMenu === 'help' ? 'active' : '' ?>">
      <i class="fas fa-question-circle"></i>
      <span class="menu-text">Help & Support</span>
    </a>
    <?php endif; ?>
    
    <a href="logout.php" class="menu-item">
      <i class="fas fa-sign-out-alt"></i>
      <span class="menu-text">Logout</span>
    </a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.has-submenu').forEach(el => {
    el.addEventListener('click', () => {
      const submenu = el.nextElementSibling;
      submenu.classList.toggle('show');
      el.classList.toggle('active');
    });
  });
});
</script>
