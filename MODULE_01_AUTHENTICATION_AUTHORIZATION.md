# Module 01: Authentication & Authorization

**Last Updated:** January 9, 2026  
**Module Version:** 1.1.8

## Overview

The Authentication & Authorization module provides secure user authentication and role-based access control (RBAC) for DocuBills. It manages user sessions, validates credentials, and enforces granular permissions.

## Key Features

- Session-based authentication with database tracking
- Role-based access control (5 roles: super_admin, admin, manager, assistant, viewer)
- Granular permission system (100+ permissions)
- Login audit logging
- Password hashing (bcrypt)
- Account suspension capability
- Session management and cleanup

## Key Files

- `config.php` - Session initialization, database connection
- `middleware.php` - Permission checking functions
- `header.php` - Authentication checks on every page
- `login.php` - Login form and handler
- `logout.php` - Session termination
- `users.php` - User administration
- `settings-permissions.php` - Permission matrix configuration

## Database Tables

- `users` - User accounts
- `roles` - Role definitions
- `permissions` - Available permissions
- `role_permissions` - Role-permission mapping
- `user_sessions` - Active session tracking
- `login_logs` - Authentication audit trail

## Authentication Flow

1. User submits credentials
2. Validate user exists and not suspended
3. Verify password with password_verify()
4. Create PHP session
5. Record session in database
6. Log successful login
7. Load user permissions
8. Redirect to dashboard

## Authorization Flow

1. User attempts action
2. Check has_permission('permission_name')
3. Query role_permissions table
4. Grant or deny access
5. Check row-level ownership if applicable

## Permission Categories

- Invoice Permissions (20+)
- Client Permissions (10+)
- Expense Permissions (14+)
- User Management (6+)
- System Permissions (20+)

## API Endpoints

- `login.php` - POST - User login
- `logout.php` - GET - User logout
- `ajax-check-username.php` - POST - Username availability
- `ajax-check-password.php` - POST - Password validation
- `ajax-update-password.php` - POST - Change password
- `get_user.php` - GET - Get user details

## Security Features

- Password hashing (bcrypt)
- Session tracking in database
- Login audit logging
- IP and user agent tracking
- Account suspension
- Permission-based access control

## Usage Example

```php
// Check permission
if (!has_permission('delete_invoice')) {
    header('Location: access-denied.php');
    exit;
}
```

For detailed documentation, see ARCHITECTURE.md and DATABASE.md.
