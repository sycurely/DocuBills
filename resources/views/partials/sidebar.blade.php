@php
  $activeMenu = $activeMenu ?? 'dashboard';
  $activeTab = $activeTab ?? '';
  $activeSub = $activeSub ?? '';
  $isSettingsPage = ($activeMenu === 'settings');
  $isExpensesPage = in_array($activeMenu, ['expenses', 'expenses_trash']);
@endphp

<div class="sidebar">
  <div class="sidebar-menu">
    <!-- MAIN LINKS - Always show for authenticated users; routes enforce permissions -->
    <a href="{{ route('dashboard') }}" class="menu-item {{ $activeMenu === 'dashboard' ? 'active' : '' }}">
      <span class="material-icons-outlined">monitoring</span>
      <span class="menu-text">Dashboard</span>
    </a>

    <a href="{{ route('invoices.create') }}" class="menu-item {{ $activeMenu === 'create-invoice' ? 'active' : '' }}">
      <span class="material-icons-outlined">post_add</span>
      <span class="menu-text">Create Invoice</span>
    </a>

    <a href="{{ route('invoices.index') }}" class="menu-item {{ $activeMenu === 'invoices' ? 'active' : '' }}">
      <span class="material-icons-outlined">history</span>
      <span class="menu-text">Invoice History</span>
    </a>

    <a href="{{ route('clients.index') }}" class="menu-item {{ $activeMenu === 'clients' ? 'active' : '' }}">
      <span class="material-icons-outlined">groups</span>
      <span class="menu-text">Clients</span>
    </a>

    <div class="menu-item has-submenu {{ $isExpensesPage ? 'active' : '' }}">
      <span class="material-icons-outlined">account_balance_wallet</span>
      <span class="menu-text">Expenses</span>
      <span class="material-icons-outlined submenu-toggle-icon">expand_more</span>
    </div>
    <div class="submenu {{ $isExpensesPage ? 'show' : '' }}">
      <a href="{{ route('expenses.index') }}" class="submenu-item {{ $activeMenu === 'expenses' ? 'active' : '' }}">
        <span class="material-icons-outlined">list</span> All Expenses
      </a>
    </div>

    <div class="menu-item has-submenu {{ $isSettingsPage ? 'active' : '' }}">
      <span class="material-icons-outlined">settings</span>
      <span class="menu-text">Settings</span>
      <span class="material-icons-outlined submenu-toggle-icon">expand_more</span>
    </div>
    <div class="submenu {{ $isSettingsPage ? 'show' : '' }}">
      <a href="{{ route('settings.index') }}" class="submenu-item {{ $activeTab === 'basic' ? 'active' : '' }}">
        <span class="material-icons-outlined">tune</span> Basic
      </a>
      <a href="{{ route('users.index') }}" class="submenu-item {{ $activeTab === 'users' ? 'active' : '' }}">
        <span class="material-icons-outlined">admin_panel_settings</span> Users
      </a>
      <a href="{{ route('settings.permissions') }}" class="submenu-item {{ $activeTab === 'permissions' ? 'active' : '' }}">
        <span class="material-icons-outlined">key</span> Permissions
      </a>
      @if(
          has_permission('manage_payment_methods') ||
          has_permission('update_basic_settings') ||
          has_permission('manage_card_payments') ||
          has_permission('manage_bank_details')
      )
        <a href="{{ route('settings.payment-methods') }}" class="submenu-item {{ $activeTab === 'payments' ? 'active' : '' }}">
          <span class="material-icons-outlined">credit_card</span> Payment Methods
        </a>
      @endif
      @if(has_permission('manage_reminder_settings'))
        <a href="{{ route('settings.reminders') }}" class="submenu-item {{ $activeTab === 'reminders' ? 'active' : '' }}">
          <span class="material-icons-outlined">notifications_active</span> Reminder Settings
        </a>
      @endif
      <a href="{{ route('settings.taxes') }}" class="submenu-item {{ $activeTab === 'taxes' ? 'active' : '' }}">
        <span class="material-icons-outlined">percent</span> Tax Classes
      </a>
      <a href="{{ route('email-templates.index') }}" class="submenu-item {{ $activeTab === 'email_templates' ? 'active' : '' }}">
        <span class="material-icons-outlined">mail</span> Email Templates
      </a>
    </div>

    @if(has_permission('view_login_logs'))
      <a href="{{ route('login-logs.index') }}" class="menu-item {{ request()->routeIs('login-logs.*') || $activeMenu === 'login-logs' ? 'active' : '' }}">
        <span class="material-icons-outlined">fact_check</span>
        <span class="menu-text">Login Logs</span>
      </a>
    @endif

    @if(has_permission('access_trashbin'))
      <a href="{{ route('trash-bin.index') }}" class="menu-item {{ request()->routeIs('trash-bin.*') || $activeMenu === 'trashbin' ? 'active' : '' }}">
        <span class="material-icons-outlined">restore_from_trash</span>
        <span class="menu-text">Trash Bin</span>
      </a>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="inline-form">
      @csrf
      <button type="submit" class="menu-item menu-item-logout unstyled-button full-width text-left">
        <span class="material-icons-outlined">logout</span>
        <span class="menu-text">Logout</span>
      </button>
    </form>
  </div>
</div>
