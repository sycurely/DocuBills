<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LoginLogsController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReminderSettingsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\TrashBinController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Home/Landing page (public, no auth required)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Settings routes (protected)
Route::middleware(['auth', 'session.active'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/payment-methods', [SettingsController::class, 'paymentMethods'])->name('settings.payment-methods');
    Route::post('/settings/payment-methods', [SettingsController::class, 'updatePaymentMethods'])->name('settings.payment-methods.update');
    Route::get('/settings/reminders', [ReminderSettingsController::class, 'index'])->name('settings.reminders')->middleware('permission:manage_reminder_settings');
    Route::post('/settings/reminders', [ReminderSettingsController::class, 'update'])->name('settings.reminders.update')->middleware('permission:manage_reminder_settings');
    Route::post('/settings/reminders/preview', [ReminderSettingsController::class, 'preview'])->name('settings.reminders.preview')->middleware('permission:manage_reminder_settings');
    Route::get('/settings/permissions', [PermissionController::class, 'index'])->name('settings.permissions');
    Route::post('/settings/permissions/{role}', [PermissionController::class, 'update'])->name('settings.permissions.update');
    Route::get('/api/settings/recommended-permissions', [PermissionController::class, 'getRecommended'])->name('api.settings.recommended-permissions');
    
    // Tax routes (protected by settings permissions)
    Route::get('/settings/taxes', [TaxController::class, 'index'])->name('settings.taxes');
    Route::post('/api/taxes', [TaxController::class, 'api'])->name('api.taxes');
    
    // Client routes
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/export', [ClientController::class, 'export'])->name('clients.export');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}', [ClientController::class, 'showPage'])->name('clients.show');
    Route::get('/api/clients/search', [ClientController::class, 'search'])->name('api.clients.search');
    Route::get('/api/clients/{client}', [ClientController::class, 'show'])->name('api.clients.show')->whereNumber('client');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::post('/clients/{id}/restore', [ClientController::class, 'restore'])->name('clients.restore');
    Route::post('/clients/restore-all', [ClientController::class, 'restoreAll'])->name('clients.restore-all');
    Route::post('/clients/undo-recent', [ClientController::class, 'undoRecent'])->name('clients.undo-recent');
    Route::post('/clients/delete-all', [ClientController::class, 'deleteAll'])->name('clients.delete-all');

    // User Management routes (protected by user management permissions)
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('permission:manage_users_page');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('permission:add_user');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:edit_user');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:edit_user');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:delete_user');
    Route::post('/users/{user}/toggle-suspend', [UserController::class, 'toggleSuspend'])->name('users.toggle-suspend')->middleware('permission:suspend_users');
    Route::get('/api/users/check-username', [UserController::class, 'checkUsername'])->name('api.users.check-username');
    Route::get('/api/users/check-email', [UserController::class, 'checkEmail'])->name('api.users.check-email');
    Route::post('/api/users/update-password', [UserController::class, 'updatePassword'])->name('api.users.update-password');

    // Trash bin routes
    Route::get('/trash-bin', [TrashBinController::class, 'index'])->name('trash-bin.index')->middleware('permission:access_trashbin');
    Route::post('/trash-bin/{type}/{id}/restore', [TrashBinController::class, 'restore'])
        ->name('trash-bin.restore')
        ->whereNumber('id')
        ->middleware('permission:access_trashbin');
    Route::delete('/trash-bin/{type}/{id}/force-delete', [TrashBinController::class, 'forceDelete'])
        ->name('trash-bin.force-delete')
        ->whereNumber('id')
        ->middleware('permission:access_trashbin');

    // Invoice routes (protected by invoice permissions)
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index')->middleware('permission:view_invoices');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create')->middleware('permission:create_invoice');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store')->middleware('permission:create_invoice');
    Route::post('/invoices/import', [InvoiceController::class, 'import'])->name('invoices.import')->middleware('permission:create_invoice');
    Route::post('/invoices/import-source', [InvoiceController::class, 'importFromSource'])->name('invoices.import-source')->middleware('permission:create_invoice');
    Route::get('/invoices/price-select', [InvoiceController::class, 'showPriceSelect'])->name('invoices.price-select')->middleware('permission:create_invoice');
    Route::post('/invoices/price-select', [InvoiceController::class, 'savePriceSelect'])->name('invoices.price-select.save')->middleware('permission:create_invoice');
    Route::get('/invoices/generate', [InvoiceController::class, 'showGenerateFromImport'])->name('invoices.generate')->middleware('permission:create_invoice');
    Route::post('/invoices/generate', [InvoiceController::class, 'saveGenerateFromImport'])->name('invoices.generate.save')->middleware('permission:create_invoice');
    Route::get('/invoices/manual-pricing', [InvoiceController::class, 'showManualPricing'])->name('invoices.manual-pricing')->middleware('permission:create_invoice');
    Route::post('/invoices/manual-pricing', [InvoiceController::class, 'saveManualPricing'])->name('invoices.manual-pricing.save')->middleware('permission:create_invoice');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show')->middleware('permission:view_invoices');
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid')->middleware('permission:mark_invoice_paid');
    Route::get('/invoices/{invoice}/download-pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.download-pdf')->middleware('permission:download_invoice_pdf');

    // Expense routes (protected by expense permissions)
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index')->middleware('permission:access_expenses_tab');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create')->middleware('permission:add_expense');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store')->middleware('permission:add_expense');
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show')->middleware('permission:view_expenses');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit')->middleware('permission:edit_expense');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update')->middleware('permission:edit_expense');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy')->middleware('permission:delete_expense');
    Route::post('/expenses/{id}/restore', [ExpenseController::class, 'restore'])->name('expenses.restore')->middleware('permission:undo_recent_expense');
    Route::post('/expenses/restore-all', [ExpenseController::class, 'restoreAll'])->name('expenses.restore-all')->middleware('permission:undo_all_expenses');
    Route::post('/expenses/undo-recent', [ExpenseController::class, 'undoRecent'])->name('expenses.undo-recent')->middleware('permission:undo_recent_expense');
    Route::post('/expenses/{expense}/change-status', [ExpenseController::class, 'changeStatus'])->name('expenses.change-status')->middleware('permission:change_expense_status');
    Route::get('/expenses/export/csv', [ExpenseController::class, 'export'])->name('expenses.export')->middleware('permission:export_expenses');

    // Email Template routes (protected by email template permissions)
    Route::get('/settings/email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index')->middleware('permission:access_email_templates_page');
    Route::get('/settings/email-templates/create', [EmailTemplateController::class, 'create'])->name('email-templates.create')->middleware('permission:add_email_template');
    Route::post('/settings/email-templates', [EmailTemplateController::class, 'store'])->name('email-templates.store')->middleware('permission:add_email_template');
    Route::get('/settings/email-templates/{emailTemplate}', [EmailTemplateController::class, 'show'])->name('email-templates.show')->middleware('permission:access_email_templates_page');
    Route::get('/settings/email-templates/{emailTemplate}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit')->middleware('permission:edit_email_template');
    Route::put('/settings/email-templates/{emailTemplate}', [EmailTemplateController::class, 'update'])->name('email-templates.update')->middleware('permission:edit_email_template');
    Route::delete('/settings/email-templates/{emailTemplate}', [EmailTemplateController::class, 'destroy'])->name('email-templates.destroy')->middleware('permission:delete_email_template');
    Route::get('/api/email-templates/by-category', [EmailTemplateController::class, 'getByCategory'])->name('api.email-templates.by-category')->middleware('permission:access_email_templates_page');
});

// Dashboard routes (protected)
Route::middleware(['auth', 'session.active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:view_dashboard');
    Route::get('/api/dashboard/data', [DashboardController::class, 'getData'])->name('api.dashboard.data')->middleware('permission:view_dashboard');
    Route::get('/api/dashboard/summary', [DashboardController::class, 'getSummary'])->name('api.dashboard.summary')->middleware('permission:view_dashboard');

    // Login logs module
    Route::get('/login-logs', [LoginLogsController::class, 'index'])->name('login-logs.index')->middleware('permission:view_login_logs');
    Route::post('/login-logs/sessions/{session}/terminate', [LoginLogsController::class, 'terminateSession'])
        ->name('login-logs.terminate-session')
        ->middleware('permission:view_login_logs');
});

// Payment callback (no auth required)
Route::get('/payment-success', [InvoiceController::class, 'paymentSuccess'])->name('payment.success');
Route::post('/stripe/webhook', [InvoiceController::class, 'stripeWebhook'])->name('stripe.webhook');
