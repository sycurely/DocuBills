# Module 08: Settings & Configuration

**Last Updated:** January 9, 2026  
**Module Version:** 1.1.8

## Overview

The Settings & Configuration module manages system-wide settings, permission matrix configuration, and application preferences. It provides a centralized configuration interface for administrators.

## Key Features

- System-wide settings (key-value store)
- Permission matrix configuration
- Role permission assignment
- Company information management
- Stripe payment configuration
- SMTP email configuration
- Currency settings
- Invoice prefix customization
- Logo/branding upload
- Cron job token management

## Key Files

- `settings-permissions.php` - Permission matrix and settings UI
- `config.php` - Settings retrieval functions
- `get_recommended_permissions.php` - Permission recommendations

## Database Tables

- `settings` - System configuration (key-value)
  - id, setting_key, setting_value, updated_at

- `permissions` - Available permissions
  - id, name, description

- `roles` - Role definitions
  - id, name

- `role_permissions` - Permission assignments
  - role_id, permission_id

## Settings Categories

### Company Settings
- `company_name` - Company display name
- `company_email` - Company email address
- `company_phone` - Company phone number
- `company_address` - Company address
- `company_logo` - Logo file path
- `app_logo_url` - Application logo URL

### Invoice Settings
- `invoice_prefix` - Invoice number prefix (e.g., "INV")
- `currency_code` - Default currency (USD, CAD, etc.)
- `currency_symbol` - Currency symbol ($, €, £, etc.)

### Payment Settings
- `stripe_publishable_key` - Stripe public key
- `stripe_secret_key` - Stripe secret key
- `test_mode` - Payment test mode flag
- `payment_provider` - Payment gateway (stripe/test)

### Email Settings
- `smtp_host` - SMTP server hostname
- `smtp_port` - SMTP port (587, 465)
- `smtp_username` - SMTP username
- `smtp_password` - SMTP password
- `email_from_name` - Sender display name
- `email_from_address` - Sender email

### Security Settings
- `cron_secret` - Cron job authentication token
- `session_timeout` - Session timeout (minutes)

### Reminder Settings
- `reminder_before_due` - Days before due date
- `reminder_on_due` - Send on due date
- `reminder_after_3` - 3 days after due
- `reminder_after_7` - 7 days after due
- `reminder_after_14` - 14 days after due
- `reminder_after_21` - 21 days after due

## Permission Matrix

The permission matrix allows administrators to:
- View all available permissions
- Assign permissions to roles
- See recommended permissions per role
- Bulk assign/remove permissions

### Permission Categories
- Invoice Permissions (20+)
- Client Permissions (10+)
- Expense Permissions (14+)
- User Management (6+)
- System Permissions (20+)

## Settings Retrieval

### get_setting() Function

```php
// In config.php
function get_setting($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT setting_value FROM settings 
        WHERE setting_key = ? LIMIT 1
    ");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return ($result && isset($result['setting_value'])) 
        ? $result['setting_value'] 
        : $default;
}
```

### Usage

```php
// Get setting
$company_name = get_setting('company_name', 'My Company');
$stripe_key = get_setting('stripe_secret_key');
$smtp_host = get_setting('smtp_host', 'smtp.gmail.com');
```

## Settings Management

### Update Setting

```php
// Update or insert setting
$stmt = $pdo->prepare("
    INSERT INTO settings (setting_key, setting_value)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE 
        setting_value = ?,
        updated_at = NOW()
");
$stmt->execute([$key, $value, $value]);
```

### Permission Assignment

```php
// Assign permission to role
$stmt = $pdo->prepare("
    INSERT INTO role_permissions (role_id, permission_id)
    VALUES (?, ?)
");
$stmt->execute([$role_id, $permission_id]);

// Remove permission from role
$stmt = $pdo->prepare("
    DELETE FROM role_permissions
    WHERE role_id = ? AND permission_id = ?
");
$stmt->execute([$role_id, $permission_id]);
```

## Permission Recommendations

The system provides recommended permissions for each role:

- **super_admin** - All permissions
- **admin** - Most permissions except system config
- **manager** - View and report permissions
- **assistant** - Basic operational permissions
- **viewer** - Read-only permissions

## API Endpoints

- `settings-permissions.php` - GET/POST - Settings and permission management
- `get_recommended_permissions.php` - GET - Get recommended permissions for role

## Permission Requirements

- `manage_permissions` - Access permission matrix
- `update_settings` - Update system settings
- `update_basic_settings` - Update basic company settings
- `access_basic_settings` - Access settings page

## Configuration Files

- `config.php` - Settings retrieval functions
- `config.example.php` - Configuration template
- `.htaccess` - Apache configuration
- `.user.ini` - PHP INI directives

## Usage Example

```php
// Get company name
$company_name = get_setting('company_name');

// Get Stripe key
$stripe_key = get_setting('stripe_secret_key');

// Update setting
$stmt = $pdo->prepare("
    INSERT INTO settings (setting_key, setting_value)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE setting_value = ?
");
$stmt->execute(['company_name', 'New Company Name', 'New Company Name']);
```

## Security Considerations

- Sensitive settings (API keys, passwords) should be encrypted
- Use environment variables for production secrets (recommended)
- Restrict access to settings page (admin only)
- Audit log for setting changes (recommended)

For detailed documentation, see ARCHITECTURE.md, API_ENDPOINTS.md, and DATABASE.md.
