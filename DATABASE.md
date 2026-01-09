# DocuBills Database Documentation

**Last Updated:** January 9, 2026
**Database Type:** MySQL/MariaDB 10.11.15
**Total Tables:** 16

---

## Table of Contents

1. [Database Overview](#database-overview)
2. [Entity Relationship Diagram](#entity-relationship-diagram)
3. [Complete Table Reference](#complete-table-reference)
4. [Module-to-Database Mapping](#module-to-database-mapping)
5. [Data Flow Diagrams](#data-flow-diagrams)
6. [Common Query Patterns](#common-query-patterns)
7. [Database Best Practices](#database-best-practices)

---

## Database Overview

The DocuBills database implements a multi-tenant invoice and expense management system with role-based access control. The schema supports:

- **User Management** with granular permissions
- **Invoice Generation** with payment tracking
- **Client Management** with soft deletion
- **Expense Tracking** with recurring support
- **Email Template** management
- **Session Tracking** and audit logging

### Database Characteristics

- **Character Set:** latin1_swedish_ci (mixed with utf8mb4 for users table)
- **Engine:** InnoDB (supports transactions and foreign keys)
- **Soft Deletes:** Implemented via `deleted_at` timestamp
- **Audit Trail:** `created_at`, `updated_at` timestamps on all major tables
- **Ownership Tracking:** `created_by` field links to users

---

## Entity Relationship Diagram

```
┌─────────────────┐
│     USERS       │
│  (id, username, │
│   email, role)  │
└────────┬────────┘
         │
         │ role_id
         ▼
┌─────────────────┐         ┌──────────────────┐
│     ROLES       │◄────────┤ ROLE_PERMISSIONS │
│  (id, name)     │         │ (role_id,        │
└─────────────────┘         │  permission_id)  │
                            └────────┬─────────┘
                                     │
                                     ▼
                            ┌─────────────────┐
                            │  PERMISSIONS    │
                            │ (id, name,      │
                            │  description)   │
                            └─────────────────┘

┌─────────────────┐
│     USERS       │
└────────┬────────┘
         │ created_by
         │
         ├──────────────────┬──────────────────┬──────────────────┐
         ▼                  ▼                  ▼                  ▼
┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
│    INVOICES     │ │    CLIENTS      │ │    EXPENSES     │ │ EMAIL_TEMPLATES │
│ (id, invoice_   │ │ (id, company_   │ │ (id, expense_   │ │ (id, subject,   │
│  number, total) │ │  name, email)   │ │  date, amount)  │ │  body)          │
└────────┬────────┘ └────────┬────────┘ └────────┬────────┘ └─────────────────┘
         │                   │                   │
         │ client_id         │                   │ client_id
         └───────────────────┘                   │
                                                 └─────────────────┐
                                                                   │
                    ┌──────────────────────────────────────────────┘
                    ▼
            ┌─────────────────┐
            │    CLIENTS      │
            └─────────────────┘

┌─────────────────┐         ┌──────────────────────┐
│     USERS       │◄────────┤   USER_SESSIONS      │
│                 │         │ (id, user_id,        │
└─────────────────┘         │  session_id, ip)     │
                            └──────────────────────┘

┌─────────────────┐         ┌──────────────────────┐
│     USERS       │◄────────┤   LOGIN_LOGS         │
│                 │         │ (id, user_id,        │
└─────────────────┘         │  login_time)         │
                            └──────────────────────┘

┌─────────────────┐         ┌──────────────────────────┐
│    INVOICES     │◄────────┤ INVOICE_REMINDER_LOGS    │
│                 │         │ (id, invoice_id,         │
└─────────────────┘         │  sent_at)                │
                            └──────────────────────────┘
```

---

## Complete Table Reference

### 1. users

**Purpose:** Stores user accounts with role-based access control

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| username | VARCHAR(100) | NO | - | Unique username (UTF8MB4) |
| email | VARCHAR(255) | NO | - | User email address |
| full_name | VARCHAR(255) | YES | NULL | Display name |
| password | VARCHAR(255) | NO | - | Bcrypt hashed password |
| role | ENUM | YES | viewer | Legacy role field (super_admin, admin, manager, assistant, viewer) |
| role_id | INT(11) | YES | 5 | Foreign key to roles table |
| is_suspended | TINYINT(1) | NO | 0 | Suspension status |
| deleted_at | DATETIME | YES | NULL | Soft delete timestamp |
| avatar | VARCHAR(255) | YES | NULL | Path to avatar image |
| active_username | VARCHAR(191) | - | - | VIRTUAL: username when not deleted |
| active_email | VARCHAR(191) | - | - | VIRTUAL: email when not deleted |
| created_at | DATETIME | YES | CURRENT_TIMESTAMP | Creation timestamp |

**Indexes:**
- PRIMARY KEY: `id`
- UNIQUE: `active_username`, `active_email`
- FOREIGN KEY: `role_id` → `roles.id` (ON DELETE SET NULL)

**Relationships:**
- **One-to-Many:** invoices (created_by)
- **One-to-Many:** clients (created_by)
- **One-to-Many:** expenses (created_by)
- **One-to-Many:** email_templates (created_by)
- **One-to-Many:** user_sessions (user_id)
- **One-to-Many:** login_logs (user_id)
- **Many-to-One:** roles (role_id)

**Module Usage:** Authentication, User Management, All CRUD operations (ownership tracking)

---

### 2. roles

**Purpose:** Defines user roles for RBAC system

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| name | VARCHAR(50) | NO | - | Role name (e.g., super_admin) |

**Data:**
```
1: super_admin
2: admin
3: manager
4: assistant
5: viewer
```

**Relationships:**
- **One-to-Many:** users (role_id)
- **Many-to-Many:** permissions (via role_permissions)

**Module Usage:** Authentication, Authorization, Permission Management

---

### 3. permissions

**Purpose:** Stores all available system permissions

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| name | VARCHAR(100) | NO | - | Permission identifier (e.g., create_invoice) |
| description | TEXT | YES | NULL | Human-readable description |

**Permission Categories:**

**Invoice Permissions (20):**
- `view_invoices`, `create_invoice`, `edit_invoice`, `delete_invoice`
- `save_invoice`, `mark_invoice_paid`, `download_invoice_pdf`, `email_invoice`
- `view_invoice_history`, `view_invoice_logs`, `view_invoice_payment_info`
- `create_recurring_invoice`, `edit_recurring_invoice`, `delete_recurring_invoice`, `view_recurring_invoice`
- `manage_recurring_invoices`, `add_invoice_field`, `show_due_date`, `show_due_time`, `show_invoice_date`, etc.

**Client Permissions (10):**
- `view_clients`, `manage_clients`, `add_client`, `edit_client`, `delete_client`
- `restore_clients`, `undo_recent_client`, `undo_all_clients`, `export_clients`, `search_clients`
- `view_all_clients`, `access_clients_tab`

**Expense Permissions (14):**
- `view_expenses`, `add_expense`, `edit_expense`, `delete_expense`
- `restore_expenses`, `delete_expense_forever`, `view_expenses_trashbin`, `view_all_expenses_trashbin`
- `undo_recent_expense`, `undo_all_expenses`, `change_expense_status`, `view_expense_details`
- `search_expenses`, `export_expenses`, `access_expenses_tab`, `view_all_expenses`

**User Management (6):**
- `manage_users`, `add_user`, `edit_user`, `delete_user`, `suspend_users`
- `manage_users_page`

**System Permissions (20+):**
- `view_dashboard`, `access_reports`, `access_admin_panel`
- `manage_permissions`, `assign_roles`, `manage_role_viewable`
- `update_settings`, `update_basic_settings`, `access_basic_settings`
- `manage_email_templates`, `add_email_template`, `edit_email_template`, `delete_email_template`
- `manage_payment_methods`, `manage_bank_details`, `manage_card_payments`, `toggle_bank_details`
- `manage_reminder_settings`, `manage_notification_categories`
- `access_trashbin`, `restore_deleted_items`, `delete_forever`, `view_all_trash`
- `view_login_logs`, `terminate_sessions`, `terminate_own_session`, `set_session_retention_days`
- `access_support`

**Relationships:**
- **Many-to-Many:** roles (via role_permissions)

**Module Usage:** Authorization system, Permission configuration

---

### 4. role_permissions

**Purpose:** Junction table linking roles to permissions (Many-to-Many)

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| role_id | INT(11) | NO | - | Foreign key to roles |
| permission_id | INT(11) | NO | - | Foreign key to permissions |

**Indexes:**
- PRIMARY KEY: `(role_id, permission_id)`
- FOREIGN KEY: `role_id` → `roles.id` (ON DELETE CASCADE)
- FOREIGN KEY: `permission_id` → `permissions.id` (ON DELETE CASCADE)

**Module Usage:** Authorization system (checks if user's role has specific permission)

---

### 5. role_column_visibility

**Purpose:** Controls which roles can see data from other roles (row-level security)

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| target_role_id | INT(11) | NO | - | Role whose data is being viewed |
| viewer_role_id | INT(11) | NO | - | Role that can view the data |
| created_at | TIMESTAMP | YES | CURRENT_TIMESTAMP | Record creation time |

**Example:**
```sql
-- Super Admin (role 1) can view Admin's data (role 2)
target_role_id = 2, viewer_role_id = 1
```

**Module Usage:** Advanced permission filtering, data visibility control

---

### 6. permission_row_visibility

**Purpose:** Advanced row-level access control (structure similar to role_column_visibility)

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| target_role_id | INT(11) | NO | - | Target role |
| viewer_role_id | INT(11) | NO | - | Viewing role |
| created_at | TIMESTAMP | YES | CURRENT_TIMESTAMP | Creation time |

**Indexes:**
- FOREIGN KEY: `target_role_id` → `roles.id` (ON DELETE CASCADE)
- FOREIGN KEY: `viewer_role_id` → `roles.id` (ON DELETE CASCADE)

**Module Usage:** Granular data access control

---

### 7. clients

**Purpose:** Stores client/customer information for invoicing

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| created_by | INT(11) | YES | NULL | User who created this client |
| company_name | VARCHAR(255) | NO | - | Client company name |
| representative | VARCHAR(255) | YES | NULL | Contact person name |
| phone | VARCHAR(50) | YES | NULL | Phone number |
| email | VARCHAR(255) | YES | NULL | Email address |
| address | TEXT | YES | NULL | Full address |
| gst_hst | VARCHAR(50) | YES | NULL | Tax identification number |
| notes | TEXT | YES | NULL | Additional notes |
| created_at | DATETIME | YES | CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | DATETIME | YES | CURRENT_TIMESTAMP | Last update timestamp |
| deleted_at | DATETIME | YES | NULL | Soft delete timestamp |

**Relationships:**
- **One-to-Many:** invoices (client_id)
- **One-to-Many:** expenses (client_id)
- **Many-to-One:** users (created_by)

**Soft Delete:** Records are marked deleted, not removed

**Module Usage:** Client Management, Invoice Creation, Expense Tracking

---

### 8. invoices

**Purpose:** Stores invoice records with payment tracking

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| invoice_number | VARCHAR(20) | YES | NULL | Unique invoice identifier (e.g., INV-001) |
| bill_to_name | VARCHAR(100) | YES | NULL | Client name on invoice |
| bill_to_json | TEXT | YES | NULL | JSON: Full billing details |
| total_amount | DECIMAL(10,2) | YES | NULL | Invoice total |
| status | VARCHAR(20) | YES | Unpaid | Payment status (Paid/Unpaid) |
| payment_link | VARCHAR(255) | YES | NULL | Stripe payment URL |
| due_date | DATE | YES | NULL | Payment due date |
| payment_provider | VARCHAR(50) | YES | NULL | Payment gateway (stripe/test) |
| html | MEDIUMTEXT | YES | NULL | Rendered invoice HTML |
| client_id | INT(11) | YES | NULL | Foreign key to clients |
| created_by | INT(11) | YES | NULL | User who created invoice |
| is_recurring | TINYINT(1) | YES | 0 | Recurring invoice flag |
| recurrence_type | VARCHAR(20) | YES | NULL | Frequency (daily/weekly/monthly/yearly) |
| currency_code | VARCHAR(10) | YES | USD | Currency (USD/CAD/GBP/EUR) |
| invoice_title_bg | VARCHAR(20) | YES | NULL | Title background color |
| invoice_title_text | VARCHAR(20) | YES | NULL | Title text color |
| created_at | DATETIME | YES | CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | DATETIME | YES | CURRENT_TIMESTAMP | Last update timestamp |
| deleted_at | DATETIME | YES | NULL | Soft delete timestamp |

**Additional Fields (not shown):**
- Bank toggle settings
- Email CC/BCC fields
- Template references

**Relationships:**
- **Many-to-One:** clients (client_id)
- **Many-to-One:** users (created_by)
- **One-to-Many:** invoice_reminder_logs (invoice_id)

**File Storage:**
- HTML stored in database (mediumtext)
- PDF files stored in `/invoices/` directory

**Module Usage:** Invoice Management, Payment Processing, Email System

---

### 9. expenses

**Purpose:** Tracks business expenses with categorization

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| created_by | INT(11) | YES | NULL | User who created expense |
| expense_date | DATE | NO | - | Expense date |
| vendor | VARCHAR(255) | YES | NULL | Vendor/supplier name |
| amount | DECIMAL(10,2) | NO | - | Expense amount |
| category | VARCHAR(100) | YES | NULL | Expense category |
| notes | TEXT | YES | NULL | Additional details |
| receipt_url | VARCHAR(255) | YES | NULL | Path to receipt file |
| is_recurring | TINYINT(1) | YES | 0 | Recurring expense flag |
| client_id | INT(11) | YES | NULL | Associated client |
| status | VARCHAR(20) | NO | Unpaid | Payment status (Paid/Unpaid) |
| payment_method | VARCHAR(50) | YES | NULL | Payment method (Cash/Check/Bank Transfer/Credit Card) |
| payment_proof | VARCHAR(255) | YES | NULL | Path to payment proof file |
| email_cc | TEXT | YES | NULL | CC email addresses |
| email_bcc | TEXT | YES | NULL | BCC email addresses |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | Last update timestamp |
| deleted_at | DATETIME | YES | NULL | Soft delete timestamp |

**Relationships:**
- **Many-to-One:** clients (client_id)
- **Many-to-One:** users (created_by)

**File Storage:**
- Receipts: `/uploads/expense_receipts/`
- Payment proofs: `/uploads/expense_receipts/`

**Module Usage:** Expense Management, Financial Reporting

---

### 10. email_templates

**Purpose:** Stores reusable email templates with placeholders

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| created_by | INT(11) | YES | NULL | User who created template |
| name | VARCHAR(100) | NO | - | Template name |
| subject | VARCHAR(255) | NO | - | Email subject line |
| body | TEXT | NO | - | Email body (HTML) |
| category | VARCHAR(50) | YES | NULL | Template category |
| created_at | DATETIME | YES | CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | DATETIME | YES | CURRENT_TIMESTAMP | Last update timestamp |

**Template Placeholders:**
- `{client_name}`, `{invoice_number}`, `{total_amount}`
- `{due_date}`, `{company_name}`, `{payment_link}`
- Custom placeholders as needed

**Module Usage:** Email System, Invoice Reminders, Communication

---

### 11. invoice_templates

**Purpose:** Stores invoice design templates

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| name | VARCHAR(100) | NO | - | Template name |
| html_template | TEXT | NO | - | Invoice HTML structure |
| created_at | DATETIME | YES | CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | DATETIME | YES | CURRENT_TIMESTAMP | Last update timestamp |

**Module Usage:** Invoice Generation, PDF Creation

---

### 12. user_sessions

**Purpose:** Tracks active user sessions for security

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| user_id | INT(11) | NO | - | Foreign key to users |
| session_id | VARCHAR(255) | NO | - | PHP session ID |
| ip_address | VARCHAR(45) | YES | NULL | User's IP address |
| user_agent | TEXT | YES | NULL | Browser user agent |
| created_at | DATETIME | YES | CURRENT_TIMESTAMP | Session start time |
| last_activity | DATETIME | YES | CURRENT_TIMESTAMP | Last activity timestamp |
| terminated_at | DATETIME | YES | NULL | Session end time |
| termination_reason | ENUM | YES | NULL | Logout or terminated |

**Relationships:**
- **Many-to-One:** users (user_id)

**Module Usage:** Authentication, Session Management, Security Monitoring

---

### 13. login_logs

**Purpose:** Audit trail for authentication attempts

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| user_id | INT(11) | YES | NULL | Foreign key to users |
| username | VARCHAR(100) | YES | NULL | Username used |
| login_time | DATETIME | YES | CURRENT_TIMESTAMP | Login timestamp |
| ip_address | VARCHAR(45) | YES | NULL | IP address |
| user_agent | TEXT | YES | NULL | Browser info |
| status | VARCHAR(20) | YES | NULL | Success or failure |

**Relationships:**
- **Many-to-One:** users (user_id)

**Module Usage:** Security Auditing, Login Monitoring

---

### 14. invoice_reminder_logs

**Purpose:** Tracks automated invoice reminder emails

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| invoice_id | INT(11) | NO | - | Foreign key to invoices |
| sent_at | DATETIME | YES | CURRENT_TIMESTAMP | Email sent timestamp |
| recipient_email | VARCHAR(255) | YES | NULL | Recipient address |
| status | VARCHAR(20) | YES | NULL | Sent or failed |

**Relationships:**
- **Many-to-One:** invoices (invoice_id)

**Module Usage:** Email System, Cron Jobs (send_invoice_reminders.php)

---

### 15. notification_types

**Purpose:** Defines email notification categories

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| name | VARCHAR(100) | NO | - | Category name |
| description | TEXT | YES | NULL | Category description |
| created_at | DATETIME | YES | CURRENT_TIMESTAMP | Creation timestamp |

**Module Usage:** Email Template Management

---

### 16. settings

**Purpose:** System-wide configuration (key-value store)

**Structure:**

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | INT(11) | NO | - | Primary key |
| setting_key | VARCHAR(100) | NO | - | Unique setting name |
| setting_value | TEXT | YES | NULL | Setting value |
| created_at | DATETIME | YES | CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | DATETIME | YES | CURRENT_TIMESTAMP | Last update timestamp |

**Common Settings:**
- `company_name`, `company_email`, `company_address`
- `company_logo`, `invoice_prefix`
- `currency_symbol`, `currency_code`
- `stripe_publishable_key`, `stripe_secret_key`, `test_mode`
- `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`
- `email_from_name`, `email_from_address`
- `cron_token` (for securing cron job endpoints)
- Banking details (account number, routing, etc.)

**Module Usage:** All modules (global configuration)

---

## Module-to-Database Mapping

This section maps each application module to the database tables it uses.

### Module 1: Authentication & Authorization

**Primary Tables:**
- `users` - User accounts
- `roles` - Role definitions
- `permissions` - Available permissions
- `role_permissions` - Role-permission mapping
- `user_sessions` - Active sessions
- `login_logs` - Authentication audit

**Read Operations:**
```sql
-- Check user credentials
SELECT * FROM users WHERE username = ? AND deleted_at IS NULL

-- Get user permissions
SELECT p.* FROM permissions p
JOIN role_permissions rp ON p.id = rp.permission_id
JOIN users u ON u.role_id = rp.role_id
WHERE u.id = ?

-- Verify active session
SELECT * FROM user_sessions
WHERE user_id = ? AND session_id = ? AND terminated_at IS NULL
```

**Write Operations:**
```sql
-- Create session
INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent)
VALUES (?, ?, ?, ?)

-- Log login attempt
INSERT INTO login_logs (user_id, username, ip_address, status)
VALUES (?, ?, ?, 'success')

-- Terminate session
UPDATE user_sessions SET terminated_at = NOW(), termination_reason = 'logout'
WHERE session_id = ?
```

**Files Using This Module:**
- [config.php](D:/Docubills/config.php) - Session initialization
- [header.php](D:/Docubills/header.php) - Authentication checks
- [users.php](D:/Docubills/users.php) - User management
- [settings-permissions.php](D:/Docubills/settings-permissions.php)

---

### Module 2: Invoice Management

**Primary Tables:**
- `invoices` - Invoice records
- `clients` - Client information
- `invoice_templates` - Design templates
- `invoice_reminder_logs` - Email tracking
- `settings` - Invoice configuration

**Read Operations:**
```sql
-- Get invoice with client details
SELECT i.*, c.company_name, c.email, c.address
FROM invoices i
LEFT JOIN clients c ON i.client_id = c.id
WHERE i.id = ? AND i.deleted_at IS NULL

-- Get unpaid invoices for reminders
SELECT * FROM invoices
WHERE status = 'Unpaid'
  AND due_date < NOW()
  AND deleted_at IS NULL

-- Get user's invoices (permission-based)
SELECT * FROM invoices
WHERE created_by = ? AND deleted_at IS NULL
ORDER BY created_at DESC
```

**Write Operations:**
```sql
-- Create invoice
INSERT INTO invoices (
  invoice_number, bill_to_name, bill_to_json, total_amount,
  client_id, created_by, html, currency_code
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)

-- Update invoice status
UPDATE invoices SET status = 'Paid', updated_at = NOW()
WHERE id = ?

-- Mark invoice as paid
UPDATE invoices SET status = 'Paid' WHERE invoice_number = ?

-- Soft delete
UPDATE invoices SET deleted_at = NOW() WHERE id = ?

-- Log reminder sent
INSERT INTO invoice_reminder_logs (invoice_id, recipient_email, status)
VALUES (?, ?, 'sent')
```

**Data Flow:**
1. User uploads Excel/CSV → Parsed into invoice data
2. Invoice data + Template → Rendered HTML
3. HTML → DomPDF → PDF file saved to `/invoices/`
4. Invoice record saved with HTML, PDF path, payment link
5. Email sent to client with payment link
6. Cron job checks due dates → Sends reminders

**Files Using This Module:**
- [create-invoice.php](D:/Docubills/create-invoice.php) - Invoice creation UI
- [save_invoice.php](D:/Docubills/save_invoice.php) - Invoice persistence
- [generate_invoice.php](D:/Docubills/generate_invoice.php) - PDF generation
- [history.php](D:/Docubills/history.php) - Invoice listing
- [cron/send_invoice_reminders.php](D:/Docubills/cron/send_invoice_reminders.php)

---

### Module 3: Client Management

**Primary Tables:**
- `clients` - Client records
- `users` - Ownership tracking

**Read Operations:**
```sql
-- Get all active clients for user
SELECT * FROM clients
WHERE deleted_at IS NULL
  AND (created_by = ? OR ? IN (
    SELECT 1 FROM role_permissions WHERE permission_id =
    (SELECT id FROM permissions WHERE name = 'view_all_clients')
  ))
ORDER BY company_name

-- Get client by ID
SELECT * FROM clients WHERE id = ? AND deleted_at IS NULL

-- Search clients
SELECT * FROM clients
WHERE deleted_at IS NULL
  AND (company_name LIKE ? OR email LIKE ? OR phone LIKE ?)
```

**Write Operations:**
```sql
-- Create client
INSERT INTO clients (
  company_name, representative, phone, email, address, gst_hst, created_by
) VALUES (?, ?, ?, ?, ?, ?, ?)

-- Update client
UPDATE clients SET
  company_name = ?, representative = ?, phone = ?, email = ?,
  address = ?, gst_hst = ?, notes = ?, updated_at = NOW()
WHERE id = ?

-- Soft delete
UPDATE clients SET deleted_at = NOW() WHERE id = ?

-- Restore from trash
UPDATE clients SET deleted_at = NULL WHERE id = ?
```

**Files Using This Module:**
- [clients.php](D:/Docubills/clients.php) - CRUD interface
- [get-client.php](D:/Docubills/get-client.php) - API endpoint

---

### Module 4: Expense Management

**Primary Tables:**
- `expenses` - Expense records
- `clients` - Client association
- `users` - Ownership tracking

**Read Operations:**
```sql
-- Get user's expenses
SELECT e.*, c.company_name
FROM expenses e
LEFT JOIN clients c ON e.client_id = c.id
WHERE e.deleted_at IS NULL
  AND (e.created_by = ? OR ? HAS permission 'view_all_expenses')
ORDER BY expense_date DESC

-- Get expenses by client
SELECT * FROM expenses
WHERE client_id = ? AND deleted_at IS NULL

-- Calculate total expenses
SELECT SUM(amount) as total
FROM expenses
WHERE deleted_at IS NULL AND status = 'Paid'
```

**Write Operations:**
```sql
-- Create expense
INSERT INTO expenses (
  expense_date, vendor, amount, category, notes, client_id,
  created_by, is_recurring, payment_method
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)

-- Update expense status
UPDATE expenses SET status = 'Paid', payment_method = ?,
  payment_proof = ?, updated_at = NOW()
WHERE id = ?

-- Soft delete
UPDATE expenses SET deleted_at = NOW() WHERE id = ?
```

**Files Using This Module:**
- [expenses.php](D:/Docubills/expenses.php) - CRUD interface
- [add-expense.php](D:/Docubills/add-expense.php) - Creation form
- [export_expenses.php](D:/Docubills/export_expenses.php) - Export functionality

---

### Module 5: Dashboard & Analytics

**Primary Tables:**
- `invoices` - Revenue data
- `expenses` - Expense data
- `clients` - Client statistics
- `users` - User activity

**Read Operations:**
```sql
-- Total revenue (paid invoices)
SELECT SUM(total_amount) as revenue
FROM invoices
WHERE status = 'Paid' AND deleted_at IS NULL

-- Total deficit (unpaid invoices)
SELECT SUM(total_amount) as deficit
FROM invoices
WHERE status = 'Unpaid' AND deleted_at IS NULL

-- Revenue by time period
SELECT DATE(created_at) as date, SUM(total_amount) as total
FROM invoices
WHERE status = 'Paid' AND deleted_at IS NULL
  AND created_at >= ?
GROUP BY DATE(created_at)

-- Top clients by revenue
SELECT c.company_name, SUM(i.total_amount) as total
FROM invoices i
JOIN clients c ON i.client_id = c.id
WHERE i.status = 'Paid' AND i.deleted_at IS NULL
GROUP BY c.id
ORDER BY total DESC
LIMIT 10

-- Recent invoices
SELECT * FROM invoices
WHERE deleted_at IS NULL
ORDER BY created_at DESC
LIMIT 10
```

**Files Using This Module:**
- [index.php](D:/Docubills/index.php) - Dashboard UI
- [dashboard-data.php](D:/Docubills/dashboard-data.php) - Analytics API
- [dashboard-summary.php](D:/Docubills/dashboard-summary.php) - Summary API

---

### Module 6: Email System

**Primary Tables:**
- `email_templates` - Template storage
- `invoice_reminder_logs` - Reminder tracking
- `settings` - SMTP configuration
- `notification_types` - Categories

**Read Operations:**
```sql
-- Get email template by ID
SELECT * FROM email_templates WHERE id = ?

-- Get templates by category
SELECT * FROM email_templates WHERE category = ?

-- Get SMTP settings
SELECT * FROM settings WHERE setting_key LIKE 'smtp_%'

-- Check if reminder already sent
SELECT * FROM invoice_reminder_logs
WHERE invoice_id = ? AND sent_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
```

**Write Operations:**
```sql
-- Save email template
INSERT INTO email_templates (name, subject, body, category, created_by)
VALUES (?, ?, ?, ?, ?)

-- Log reminder sent
INSERT INTO invoice_reminder_logs (invoice_id, recipient_email, status)
VALUES (?, ?, 'sent')
```

**Template Processing:**
```php
// Replace placeholders
$body = str_replace('{client_name}', $client_name, $template_body);
$body = str_replace('{invoice_number}', $invoice_number, $body);
$body = str_replace('{total_amount}', $total_amount, $body);
```

**Files Using This Module:**
- [manage-email-templates.php](D:/Docubills/manage-email-templates.php)
- [ajax_get_email_template.php](D:/Docubills/ajax_get_email_template.php)
- [cron/send_invoice_reminders.php](D:/Docubills/cron/send_invoice_reminders.php)
- libs/PHPMailer/

---

### Module 7: User Management

**Primary Tables:**
- `users` - User accounts
- `roles` - Role assignment
- `user_sessions` - Session management

**Read Operations:**
```sql
-- Get all active users
SELECT u.*, r.name as role_name
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
WHERE u.deleted_at IS NULL

-- Check username availability
SELECT COUNT(*) FROM users
WHERE active_username = ? AND id != ?

-- Get user sessions
SELECT * FROM user_sessions
WHERE user_id = ? AND terminated_at IS NULL
```

**Write Operations:**
```sql
-- Create user
INSERT INTO users (username, email, full_name, password, role_id)
VALUES (?, ?, ?, ?, ?)

-- Update user
UPDATE users SET
  username = ?, email = ?, full_name = ?, role_id = ?,
  avatar = ?
WHERE id = ?

-- Suspend user
UPDATE users SET is_suspended = 1 WHERE id = ?

-- Change password
UPDATE users SET password = ? WHERE id = ?

-- Soft delete
UPDATE users SET deleted_at = NOW() WHERE id = ?
```

**Files Using This Module:**
- [users.php](D:/Docubills/users.php) - User administration
- [add_user.php](D:/Docubills/add_user.php) - User creation
- [edit_user.php](D:/Docubills/edit_user.php) - User editing
- [ajax-check-username.php](D:/Docubills/ajax-check-username.php)
- [ajax-update-password.php](D:/Docubills/ajax-update-password.php)

---

### Module 8: Settings & Configuration

**Primary Tables:**
- `settings` - System configuration
- `permissions` - Permission definitions
- `role_permissions` - Permission matrix

**Read Operations:**
```sql
-- Get all settings
SELECT * FROM settings

-- Get specific setting
SELECT setting_value FROM settings WHERE setting_key = ?

-- Get role permissions
SELECT p.name, p.description
FROM permissions p
JOIN role_permissions rp ON p.id = rp.permission_id
WHERE rp.role_id = ?
```

**Write Operations:**
```sql
-- Update setting
INSERT INTO settings (setting_key, setting_value)
VALUES (?, ?)
ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()

-- Assign permission to role
INSERT INTO role_permissions (role_id, permission_id)
VALUES (?, ?)

-- Remove permission from role
DELETE FROM role_permissions
WHERE role_id = ? AND permission_id = ?
```

**Files Using This Module:**
- [settings-permissions.php](D:/Docubills/settings-permissions.php)
- [config.php](D:/Docubills/config.php)

---

## Data Flow Diagrams

### Invoice Creation Flow

```
┌──────────────┐
│ User Uploads │
│  Excel/CSV   │
└──────┬───────┘
       │
       ▼
┌──────────────────────┐
│ Parse File           │
│ (PHPSpreadsheet)     │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Extract Data:        │
│ - Client info        │
│ - Line items         │
│ - Totals             │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ SELECT client_id     │
│ FROM clients         │
│ WHERE email = ?      │
└──────┬───────────────┘
       │
       ├─── Client exists ───┐
       │                     │
       └─── Create new ──────┤
                             ▼
                    ┌─────────────────┐
                    │ INSERT INTO     │
                    │ clients         │
                    └────────┬────────┘
                             │
       ┌─────────────────────┘
       │
       ▼
┌──────────────────────┐
│ Load invoice_        │
│ templates            │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Render HTML with     │
│ client data +        │
│ line items           │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Generate PDF         │
│ (DomPDF)             │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Save files:          │
│ - invoice.html       │
│ - invoice.pdf        │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ INSERT INTO invoices │
│ (html, total, etc.)  │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Generate Stripe      │
│ payment link         │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ UPDATE invoices      │
│ SET payment_link = ? │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Send email to client │
│ (PHPMailer)          │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Display success      │
└──────────────────────┘
```

### User Authentication Flow

```
┌──────────────┐
│ User submits │
│ credentials  │
└──────┬───────┘
       │
       ▼
┌─────────────────────────┐
│ SELECT * FROM users     │
│ WHERE username = ?      │
│   AND deleted_at IS NULL│
└──────┬──────────────────┘
       │
       ├─── User not found ──► Log failed attempt ──► Return error
       │
       ▼
┌─────────────────────────┐
│ Verify password         │
│ password_verify()       │
└──────┬──────────────────┘
       │
       ├─── Wrong password ──► Log failed attempt ──► Return error
       │
       ▼
┌─────────────────────────┐
│ Check is_suspended      │
└──────┬──────────────────┘
       │
       ├─── Suspended ──────► Return error
       │
       ▼
┌─────────────────────────┐
│ Generate session_id     │
│ session_start()         │
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ INSERT INTO             │
│ user_sessions           │
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ INSERT INTO login_logs  │
│ (status = 'success')    │
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ Load user permissions   │
│ via role_permissions    │
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ Redirect to dashboard   │
└─────────────────────────┘
```

### Permission Check Flow

```
┌──────────────────┐
│ User attempts    │
│ action           │
└──────┬───────────┘
       │
       ▼
┌───────────────────────────┐
│ Get required permission   │
│ e.g., 'delete_invoice'    │
└──────┬────────────────────┘
       │
       ▼
┌───────────────────────────┐
│ SELECT COUNT(*)           │
│ FROM role_permissions rp  │
│ JOIN permissions p        │
│   ON p.id = rp.permission_│
│      id                   │
│ JOIN users u              │
│   ON u.role_id = rp.role_id│
│ WHERE u.id = ?            │
│   AND p.name = ?          │
└──────┬────────────────────┘
       │
       ├─── Count = 0 ──► Redirect to access-denied.php
       │
       ▼
┌───────────────────────────┐
│ Check row-level access    │
│ (if applicable)           │
└──────┬────────────────────┘
       │
       ├─── created_by != user_id AND !view_all permission
       │                        ──► Access denied
       │
       ▼
┌───────────────────────────┐
│ Allow action              │
└───────────────────────────┘
```

### Email Reminder Cron Flow

```
┌──────────────────┐
│ Cron job         │
│ (every hour)     │
└──────┬───────────┘
       │
       ▼
┌─────────────────────────┐
│ Verify cron_token       │
│ from settings table     │
└──────┬──────────────────┘
       │
       ├─── Invalid ──► Exit
       │
       ▼
┌─────────────────────────┐
│ SELECT * FROM invoices  │
│ WHERE status = 'Unpaid' │
│   AND due_date < NOW()  │
│   AND deleted_at IS NULL│
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ For each invoice:       │
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ Check if reminder sent  │
│ in last 7 days          │
└──────┬──────────────────┘
       │
       ├─── Already sent ──► Skip
       │
       ▼
┌─────────────────────────┐
│ Get client email        │
│ from clients table      │
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ Get email template      │
│ from email_templates    │
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ Replace placeholders    │
│ {invoice_number}, etc.  │
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ Send email              │
│ (PHPMailer/SMTP)        │
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ INSERT INTO             │
│ invoice_reminder_logs   │
└──────┬──────────────────┘
       │
       ▼
┌─────────────────────────┐
│ Log results             │
└─────────────────────────┘
```

---

## Common Query Patterns

### User & Permission Queries

**Check if user has permission:**
```sql
SELECT COUNT(*) as has_permission
FROM users u
JOIN role_permissions rp ON u.role_id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
WHERE u.id = ? AND p.name = ? AND u.deleted_at IS NULL
```

**Get all permissions for a user:**
```sql
SELECT p.id, p.name, p.description
FROM permissions p
JOIN role_permissions rp ON p.id = rp.permission_id
JOIN users u ON u.role_id = rp.role_id
WHERE u.id = ?
ORDER BY p.name
```

**Get users with specific role:**
```sql
SELECT u.*, r.name as role_name
FROM users u
JOIN roles r ON u.role_id = r.id
WHERE r.name = 'super_admin' AND u.deleted_at IS NULL
```

### Invoice Queries

**Get invoice with full client details:**
```sql
SELECT
  i.*,
  c.company_name,
  c.representative,
  c.email,
  c.phone,
  c.address,
  u.full_name as created_by_name
FROM invoices i
LEFT JOIN clients c ON i.client_id = c.id
LEFT JOIN users u ON i.created_by = u.id
WHERE i.id = ? AND i.deleted_at IS NULL
```

**Get overdue unpaid invoices:**
```sql
SELECT i.*, c.email as client_email
FROM invoices i
JOIN clients c ON i.client_id = c.id
WHERE i.status = 'Unpaid'
  AND i.due_date < CURDATE()
  AND i.deleted_at IS NULL
ORDER BY i.due_date ASC
```

**Revenue by month:**
```sql
SELECT
  DATE_FORMAT(created_at, '%Y-%m') as month,
  COUNT(*) as invoice_count,
  SUM(total_amount) as revenue
FROM invoices
WHERE status = 'Paid' AND deleted_at IS NULL
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY month DESC
```

**Top paying clients:**
```sql
SELECT
  c.id,
  c.company_name,
  COUNT(i.id) as invoice_count,
  SUM(i.total_amount) as total_revenue
FROM clients c
JOIN invoices i ON c.id = i.client_id
WHERE i.status = 'Paid' AND i.deleted_at IS NULL
GROUP BY c.id
ORDER BY total_revenue DESC
LIMIT 10
```

### Client Queries

**Search clients (with permission filter):**
```sql
SELECT c.*
FROM clients c
WHERE c.deleted_at IS NULL
  AND (
    c.company_name LIKE CONCAT('%', ?, '%')
    OR c.email LIKE CONCAT('%', ?, '%')
    OR c.phone LIKE CONCAT('%', ?, '%')
  )
  AND (
    c.created_by = ?  -- Current user
    OR ? IN (  -- User has view_all_clients permission
      SELECT 1 FROM role_permissions rp
      JOIN permissions p ON rp.permission_id = p.id
      JOIN users u ON u.role_id = rp.role_id
      WHERE u.id = ? AND p.name = 'view_all_clients'
    )
  )
ORDER BY c.company_name
```

**Client with invoice summary:**
```sql
SELECT
  c.*,
  COUNT(i.id) as total_invoices,
  SUM(CASE WHEN i.status = 'Paid' THEN i.total_amount ELSE 0 END) as total_paid,
  SUM(CASE WHEN i.status = 'Unpaid' THEN i.total_amount ELSE 0 END) as total_unpaid
FROM clients c
LEFT JOIN invoices i ON c.id = i.client_id AND i.deleted_at IS NULL
WHERE c.id = ? AND c.deleted_at IS NULL
GROUP BY c.id
```

### Expense Queries

**Monthly expense report:**
```sql
SELECT
  DATE_FORMAT(expense_date, '%Y-%m') as month,
  category,
  SUM(amount) as total,
  COUNT(*) as count
FROM expenses
WHERE deleted_at IS NULL
  AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY month, category
ORDER BY month DESC, total DESC
```

**Expenses by client:**
```sql
SELECT
  e.*,
  c.company_name
FROM expenses e
LEFT JOIN clients c ON e.client_id = c.id
WHERE e.client_id = ? AND e.deleted_at IS NULL
ORDER BY e.expense_date DESC
```

**Unpaid recurring expenses:**
```sql
SELECT e.*, c.company_name
FROM expenses e
LEFT JOIN clients c ON e.client_id = c.id
WHERE e.is_recurring = 1
  AND e.status = 'Unpaid'
  AND e.deleted_at IS NULL
```

### Dashboard Queries

**Financial summary:**
```sql
SELECT
  (SELECT SUM(total_amount) FROM invoices
   WHERE status = 'Paid' AND deleted_at IS NULL) as total_revenue,
  (SELECT SUM(total_amount) FROM invoices
   WHERE status = 'Unpaid' AND deleted_at IS NULL) as total_unpaid,
  (SELECT SUM(amount) FROM expenses
   WHERE status = 'Paid' AND deleted_at IS NULL) as total_expenses,
  (SELECT COUNT(*) FROM clients
   WHERE deleted_at IS NULL) as total_clients
```

**Recent activity:**
```sql
(SELECT 'invoice' as type, id, invoice_number as reference,
        total_amount as amount, created_at
 FROM invoices WHERE deleted_at IS NULL
 ORDER BY created_at DESC LIMIT 5)
UNION ALL
(SELECT 'expense' as type, id, vendor as reference,
        amount, created_at
 FROM expenses WHERE deleted_at IS NULL
 ORDER BY created_at DESC LIMIT 5)
ORDER BY created_at DESC
LIMIT 10
```

### Session & Security Queries

**Active sessions for user:**
```sql
SELECT
  id,
  session_id,
  ip_address,
  user_agent,
  created_at,
  last_activity,
  TIMESTAMPDIFF(MINUTE, last_activity, NOW()) as idle_minutes
FROM user_sessions
WHERE user_id = ?
  AND terminated_at IS NULL
ORDER BY last_activity DESC
```

**Cleanup old sessions:**
```sql
UPDATE user_sessions
SET terminated_at = NOW(), termination_reason = 'expired'
WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR)
  AND terminated_at IS NULL
```

**Login history for user:**
```sql
SELECT
  login_time,
  ip_address,
  user_agent,
  status
FROM login_logs
WHERE user_id = ?
ORDER BY login_time DESC
LIMIT 50
```

---

## Database Best Practices

### Current Implementation

**Good Practices:**

1. **Soft Deletes**
   - Uses `deleted_at` timestamp instead of hard deletes
   - Preserves data for audit and recovery
   - Virtual columns (`active_username`, `active_email`) for unique constraints

2. **Audit Trail**
   - `created_at` and `updated_at` timestamps on all major tables
   - Login logs for authentication tracking
   - Invoice reminder logs for email tracking

3. **Foreign Key Constraints**
   - Referential integrity enforced
   - CASCADE deletes for junction tables
   - SET NULL for user references (preserve data when user deleted)

4. **InnoDB Engine**
   - Transaction support
   - Foreign key support
   - Row-level locking

5. **Password Security**
   - Bcrypt hashing (via PHP `password_hash()`)
   - Passwords stored as VARCHAR(255)

### Recommendations for Improvement

**Critical:**

1. **Add Database Indexes**
   ```sql
   -- Performance indexes
   CREATE INDEX idx_invoices_status ON invoices(status);
   CREATE INDEX idx_invoices_created_by ON invoices(created_by);
   CREATE INDEX idx_invoices_client_id ON invoices(client_id);
   CREATE INDEX idx_invoices_deleted_at ON invoices(deleted_at);
   CREATE INDEX idx_clients_created_by ON clients(created_by);
   CREATE INDEX idx_clients_deleted_at ON clients(deleted_at);
   CREATE INDEX idx_expenses_created_by ON expenses(created_by);
   CREATE INDEX idx_expenses_client_id ON expenses(client_id);
   CREATE INDEX idx_expenses_deleted_at ON expenses(deleted_at);
   CREATE INDEX idx_user_sessions_user_id ON user_sessions(user_id);
   CREATE INDEX idx_user_sessions_terminated_at ON user_sessions(terminated_at);

   -- Composite indexes for common queries
   CREATE INDEX idx_invoices_status_deleted ON invoices(status, deleted_at);
   CREATE INDEX idx_invoices_due_date_status ON invoices(due_date, status);
   ```

2. **Standardize Character Set**
   ```sql
   -- Convert all tables to UTF8MB4
   ALTER TABLE clients CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ALTER TABLE invoices CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   -- ... repeat for all tables
   ```

3. **Add Missing Foreign Keys**
   ```sql
   ALTER TABLE invoices
     ADD CONSTRAINT fk_invoices_client
     FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL;

   ALTER TABLE invoices
     ADD CONSTRAINT fk_invoices_creator
     FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

   ALTER TABLE expenses
     ADD CONSTRAINT fk_expenses_client
     FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL;

   ALTER TABLE expenses
     ADD CONSTRAINT fk_expenses_creator
     FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;

   ALTER TABLE clients
     ADD CONSTRAINT fk_clients_creator
     FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
   ```

**Medium Priority:**

4. **Normalize Settings Table**
   ```sql
   -- Instead of key-value, create specific tables
   CREATE TABLE company_settings (
     id INT PRIMARY KEY AUTO_INCREMENT,
     company_name VARCHAR(255),
     company_email VARCHAR(255),
     company_phone VARCHAR(50),
     company_address TEXT,
     company_logo VARCHAR(255),
     invoice_prefix VARCHAR(20),
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   );

   CREATE TABLE email_settings (
     id INT PRIMARY KEY AUTO_INCREMENT,
     smtp_host VARCHAR(255),
     smtp_port INT,
     smtp_username VARCHAR(255),
     smtp_password VARCHAR(255),  -- Should be encrypted
     email_from_name VARCHAR(255),
     email_from_address VARCHAR(255),
     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
     updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   );
   ```

5. **Add Full-Text Search**
   ```sql
   ALTER TABLE clients ADD FULLTEXT INDEX ft_clients_search
     (company_name, representative, email, notes);

   ALTER TABLE invoices ADD FULLTEXT INDEX ft_invoices_search
     (invoice_number, bill_to_name);
   ```

6. **Partition Large Tables**
   ```sql
   -- Partition invoices by year for better query performance
   ALTER TABLE invoices
   PARTITION BY RANGE (YEAR(created_at)) (
     PARTITION p2024 VALUES LESS THAN (2025),
     PARTITION p2025 VALUES LESS THAN (2026),
     PARTITION p2026 VALUES LESS THAN (2027),
     PARTITION p_future VALUES LESS THAN MAXVALUE
   );
   ```

**Low Priority:**

7. **Add Database Views**
   ```sql
   -- View for active invoices with client details
   CREATE VIEW v_active_invoices AS
   SELECT
     i.id,
     i.invoice_number,
     i.total_amount,
     i.status,
     i.due_date,
     c.company_name,
     c.email as client_email,
     u.full_name as created_by_name
   FROM invoices i
   LEFT JOIN clients c ON i.client_id = c.id
   LEFT JOIN users u ON i.created_by = u.id
   WHERE i.deleted_at IS NULL;

   -- View for user permissions
   CREATE VIEW v_user_permissions AS
   SELECT
     u.id as user_id,
     u.username,
     r.name as role_name,
     p.name as permission_name,
     p.description as permission_description
   FROM users u
   JOIN roles r ON u.role_id = r.id
   JOIN role_permissions rp ON r.id = rp.role_id
   JOIN permissions p ON rp.permission_id = p.id
   WHERE u.deleted_at IS NULL;
   ```

8. **Database Migrations System**
   - Implement versioned migrations (e.g., using Phinx or custom)
   - Track schema changes in version control
   - Create rollback capabilities

### Query Optimization Tips

**Use EXPLAIN to analyze queries:**
```sql
EXPLAIN SELECT * FROM invoices
WHERE status = 'Unpaid' AND deleted_at IS NULL;
```

**Avoid SELECT *:**
```sql
-- Bad
SELECT * FROM invoices WHERE id = 1;

-- Good
SELECT id, invoice_number, total_amount, status
FROM invoices WHERE id = 1;
```

**Use JOINs instead of subqueries when possible:**
```sql
-- Less efficient
SELECT * FROM invoices WHERE client_id IN
  (SELECT id FROM clients WHERE company_name LIKE '%Tech%');

-- More efficient
SELECT i.* FROM invoices i
JOIN clients c ON i.client_id = c.id
WHERE c.company_name LIKE '%Tech%';
```

**Batch inserts:**
```sql
-- Instead of multiple INSERTs
INSERT INTO invoice_reminder_logs (invoice_id, sent_at)
VALUES (1, NOW()), (2, NOW()), (3, NOW());
```

---

## Conclusion

This database documentation provides a complete reference for understanding how DocuBills stores and manages data. The schema implements a robust multi-tenant system with:

- **16 tables** supporting all application features
- **Role-based access control** with granular permissions
- **Soft deletion** for data preservation
- **Audit trails** for security and compliance
- **Flexible architecture** supporting future growth

### Key Takeaways for LLMs:

1. **Ownership Model:** Most records have `created_by` linking to `users.id`
2. **Soft Deletes:** Always check `deleted_at IS NULL` in queries
3. **Permission System:** User → Role → Permissions (via junction table)
4. **Client Relationships:** Clients link to both Invoices and Expenses
5. **File Storage:** Files stored on filesystem, paths in database
6. **Session Management:** Database-backed sessions for security
7. **Multi-Currency:** Invoices support different currencies via `currency_code`

### Quick Reference for Common Tasks:

| Task | Primary Tables | Key Fields |
|------|---------------|------------|
| Create Invoice | invoices, clients | invoice_number, total_amount, client_id |
| Check Permission | users, roles, permissions, role_permissions | user.role_id, permission.name |
| Track Expense | expenses, clients | amount, category, client_id |
| Send Email | email_templates, invoice_reminder_logs | subject, body, invoice_id |
| Manage User | users, roles, user_sessions | username, role_id, session_id |
| Financial Report | invoices, expenses | total_amount, amount, status |

---

**Document Version:** 1.0
**Author:** Claude Code
**Date:** January 9, 2026
