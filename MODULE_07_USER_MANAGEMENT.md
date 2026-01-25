# Module 07: User Management

**Last Updated:** January 9, 2026  
**Module Version:** 1.1.8

## Overview

The User Management module handles user account administration including creation, editing, role assignment, password management, and account suspension. It provides a comprehensive interface for managing system users.

## Key Features

- User CRUD operations
- Role assignment
- Avatar upload/management
- Password change functionality
- Account suspension/activation
- Username/email uniqueness validation
- Profile management
- Session management
- User activity tracking

## Key Files

- `users.php` - User administration interface
- `add_user.php` - User creation form handler
- `edit_user.php` - User editing form handler
- `get_user.php` - User data retrieval API
- `ajax-check-username.php` - Username availability check
- `ajax-check-password.php` - Password validation
- `ajax-update-password.php` - Password change handler

## Database Tables

- `users` - User accounts
  - id, username, email, full_name, password
  - role_id, is_suspended, avatar, deleted_at

- `roles` - Role definitions
  - id, name (super_admin, admin, manager, assistant, viewer)

- `user_sessions` - Active sessions
  - user_id, session_id, ip_address, last_activity

## User Operations

### Create User
- Username (unique)
- Email (unique)
- Full name
- Password (hashed with bcrypt)
- Role assignment
- Optional avatar upload

### Update User
- Edit username, email, full name
- Change role
- Update avatar
- Cannot change password (separate function)

### Delete User
- Soft delete (sets deleted_at)
- Preserves user data
- Cannot delete own account

### Suspend User
- Set is_suspended = 1
- Prevents login
- Can be reactivated

### Change Password
- Requires current password verification
- New password validation
- Password hashing (bcrypt)
- Updates password in database

## Permission Requirements

- `manage_users` - Access user management
- `manage_users_page` - View users page
- `add_user` - Create new user
- `edit_user` - Update user
- `delete_user` - Delete user
- `suspend_users` - Suspend/activate users

## Role Assignment

Available roles:
1. **super_admin** - Full system access
2. **admin** - Administrative access
3. **manager** - Supervisory access
4. **assistant** - Limited operational access
5. **viewer** - Read-only access

## Avatar Management

- Upload location: `/uploads/avatars/`
- Supported formats: JPG, PNG, GIF
- File naming: `{user_id}_{timestamp}.{ext}`
- Max file size: 2MB (configurable)
- Automatic resizing (if implemented)

## Password Management

### Password Requirements
- Minimum length: 8 characters (recommended)
- Complexity: Not enforced (recommended)
- Hashing: bcrypt with cost factor 10+

### Password Change Flow
1. User enters current password
2. Validate current password
3. Enter new password
4. Confirm new password
5. Hash new password
6. Update database

## API Endpoints

- `users.php` - GET/POST - User CRUD operations
- `add_user.php` - POST - Create user
- `edit_user.php` - GET/POST - Edit user
- `get_user.php` - GET - Get user details (HTML modal)
- `ajax-check-username.php` - POST - Username availability
- `ajax-check-password.php` - POST - Validate password
- `ajax-update-password.php` - POST - Change password

## Validation

### Username Validation
- Must be unique
- Real-time availability check
- Excludes current user (for edits)
- Case-insensitive check

### Email Validation
- Must be unique
- Email format validation
- Real-time availability check

### Password Validation
- Current password verification
- New password confirmation match
- Minimum length check (if enforced)

## Usage Example

```php
// Create user
$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role_id = $_POST['role_id'];

$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password, role_id)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$username, $email, $password, $role_id]);

// Check username availability
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM users
    WHERE active_username = ? AND id != ?
");
$stmt->execute([$username, $user_id]);
$exists = $stmt->fetchColumn() > 0;
```

## Security Features

- Password hashing (bcrypt)
- Username/email uniqueness
- Account suspension
- Session tracking
- Permission-based access
- Soft deletion (data preservation)

For detailed documentation, see ARCHITECTURE.md, API_ENDPOINTS.md, and DATABASE.md.
