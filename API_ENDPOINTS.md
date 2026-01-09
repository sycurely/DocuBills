# DocuBills API Endpoints & Routes Reference

**Last Updated:** January 9, 2026  
**Project Version:** 1.1.8  
**Document Purpose:** Comprehensive guide to all API endpoints, routes, and request/response formats for LLM understanding

---

## Table of Contents

1. [API Architecture Overview](#1-api-architecture-overview)
2. [Authentication & Session Management](#2-authentication--session-management)
3. [Dashboard API Endpoints](#3-dashboard-api-endpoints)
4. [User Management Endpoints](#4-user-management-endpoints)
5. [Client Management Endpoints](#5-client-management-endpoints)
6. [Invoice Management Endpoints](#6-invoice-management-endpoints)
7. [Expense Management Endpoints](#7-expense-management-endpoints)
8. [Email Template Endpoints](#8-email-template-endpoints)
9. [Settings & Configuration Endpoints](#9-settings--configuration-endpoints)
10. [Webhook Endpoints](#10-webhook-endpoints)
11. [Cron Job Endpoints](#11-cron-job-endpoints)
12. [Page Routes (Full Pages)](#12-page-routes-full-pages)
13. [Error Handling](#13-error-handling)
14. [Common Response Patterns](#14-common-response-patterns)

---

## 1. API Architecture Overview

### Request/Response Pattern

DocuBills uses a **hybrid architecture**:
- **AJAX Endpoints**: Return JSON for dynamic UI updates
- **Form Handlers**: Process POST data and redirect
- **Page Controllers**: Render full HTML pages

### Base URL Structure

```
https://yourdomain.com/
    +-- [AJAX Endpoints]     -> Return JSON
    +-- [Form Handlers]      -> Process POST, redirect
    +-- [Page Controllers]   -> Render HTML
    +-- cron/                -> Scheduled task endpoints
```

### Authentication Model

All endpoints except public pages require PHP session authentication:

```php
session_start();
if (!isset(\['user_id'])) {
    // Redirect to login or return 401
}
```

### Permission Model

Most endpoints check permissions via `middleware.php`:

```php
require_once 'middleware.php';
if (!has_permission('permission_name')) {
    // Return 403 or redirect to access-denied.php
}
```

---

## 2. Authentication & Session Management

### Session Variables

| Variable | Type | Description |
|----------|------|-------------|
| `\['user_id']` | int | Current user's ID |
| `\['user_role']` | string | Role name (super_admin, admin, etc.) |
| `\['avatar']` | string | Path to user avatar |
| `\['permissions']` | array | Cached permission list |

### Login Flow

**Endpoint:** `login.php` (not in current codebase view)

**Session Creation:**
```php
\['user_id'] = \['id'];
\['user_role'] = \['role'];
// Session tracked in user_sessions table
```

### Logout Flow

**Endpoint:** `logout.php`

**Actions:**
1. Update `user_sessions.terminated_at`
2. Destroy PHP session
3. Redirect to login page

---

## 3. Dashboard API Endpoints

### 3.1 Dashboard Data

**Endpoint:** `dashboard-data.php`  
**Method:** GET  
**Auth Required:** Yes (via config.php database connection)  
**Content-Type:** `application/json`

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `period` | string | No | `daily` | Time grouping: `daily`, `monthly`, `yearly`, `all` |
| `paid_clients` | bool | No | false | Return top paying clients |
| `unpaid_clients` | bool | No | false | Return top unpaid clients |

**Request Examples:**

```
GET /dashboard-data.php
GET /dashboard-data.php?period=monthly
GET /dashboard-data.php?paid_clients=true
GET /dashboard-data.php?unpaid_clients=true
```

**Response (Default):**

```json
{
  "status": {
    "paid": 45,
    "unpaid": 12
  },
  "labels": ["2026-01-03", "2026-01-04", "2026-01-05"],
  "paid_series": [5, 8, 3],
  "unpaid_series": [2, 1, 4],
  "total_revenue": 125000.50,
  "top_clients": [
    {"bill_to_name": "Acme Corp", "total": 15},
    {"bill_to_name": "TechStart Inc", "total": 12}
  ],
  "recent_invoices": [
    {
      "invoice_number": "INV-2026-001",
      "bill_to_name": "Acme Corp",
      "total_amount": "1500.00",
      "status": "Paid",
      "created_at": "2026-01-09 14:30:00"
    }
  ]
}
```

**Response (unpaid_clients=true):**

```json
{
  "top_unpaid": [
    {"bill_to_name": "Late Payer LLC", "count": 5},
    {"bill_to_name": "Slow Corp", "count": 3}
  ]
}
```

**Error Response:**

```json
{
  "error": "Database connection failed"
}
```

---

### 3.2 Dashboard Summary

**Endpoint:** `dashboard-summary.php`  
**Method:** GET  
**Auth Required:** Yes  
**Content-Type:** `application/json`

**Request:**

```
GET /dashboard-summary.php
```

**Response:**

```json
{
  "total_revenue": 125000.50,
  "total_deficit": 35000.00,
  "top_clients": [],
  "recent_invoices": [
    {
      "invoice_number": "INV-2026-001",
      "bill_to_name": "Acme Corp",
      "total_amount": "1500.00",
      "status": "Paid",
      "created_at": "2026-01-09"
    }
  ]
}
```

---

## 4. User Management Endpoints

### 4.1 Check Password Validity

**Endpoint:** `ajax-check-password.php`  
**Method:** POST  
**Auth Required:** Yes  
**Content-Type Request:** `application/x-www-form-urlencoded`  
**Content-Type Response:** `application/json`

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `current_password` | string | Yes | Password to validate |

**Request Example:**

```
POST /ajax-check-password.php
Content-Type: application/x-www-form-urlencoded

current_password=MySecretPass123
```

**Success Response:**

```json
{
  "valid": true
}
```

**Failure Response:**

```json
{
  "valid": false
}
```

---

### 4.2 Check Username Availability

**Endpoint:** `ajax-check-username.php`  
**Method:** POST  
**Auth Required:** Yes  
**Content-Type Response:** `application/json`

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `username` | string | Yes | Username to check |

**Response:**

```json
{
  "valid": true,
  "message": "Username is available."
}
```

```json
{
  "valid": false,
  "message": "That username is already taken."
}
```

---

### 4.3 Check Field Availability (Generic)

**Endpoint:** `check_availability.php`  
**Method:** GET  
**Auth Required:** No (but requires config.php)  
**Content-Type:** `application/json`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `field` | string | Yes | Field name: `username` or `email` |
| `value` | string | Yes | Value to check |
| `user_id` | int | No | Exclude this user ID from check |

**Request Example:**

```
GET /check_availability.php?field=username&value=johndoe&user_id=5
```

**Response:**

```json
{
  "status": "available",
  "field": "username"
}
```

```json
{
  "status": "taken",
  "field": "email"
}
```

---

### 4.4 Update Password

**Endpoint:** `ajax-update-password.php`  
**Method:** POST  
**Auth Required:** Yes  
**Content-Type Response:** `application/json`

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `current_password` | string | Yes | Current password |
| `new_password` | string | Yes | New password |
| `confirm_password` | string | Yes | Confirm new password |

**Success Response:**

```json
{
  "success": true
}
```

**Error Responses:**

```json
{
  "success": false,
  "message": "Current password is incorrect."
}
```

```json
{
  "success": false,
  "message": "New passwords do not match."
}
```

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

---

### 4.5 Get User Details

**Endpoint:** `get_user.php`  
**Method:** GET  
**Auth Required:** Yes  
**Content-Type:** `text/html` (returns HTML fragment for modal)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | Yes | User ID to retrieve |

**Request:**

```
GET /get_user.php?id=5
```

**Response:** HTML fragment containing user details for modal display

---

### 4.6 Add User

**Endpoint:** `add_user.php`  
**Method:** POST  
**Auth Required:** Yes  
**Permission:** `manage_users`  
**Redirect:** `users.php`

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `full_name` | string | Yes | User's full name |
| `username` | string | Yes | Unique username |
| `email` | string | Yes | Unique email address |
| `password` | string | Yes | Plain text password (hashed on server) |
| `role_id` | int | Yes | Role ID from roles table |

**Success:** Redirects to `users.php` with `\['success']` message  
**Error:** Redirects to `users.php` with `\['error']` message

---

### 4.7 Edit User Form

**Endpoint:** `edit_user.php`  
**Method:** GET  
**Auth Required:** Yes  
**Permission:** `edit_user`  
**Content-Type:** `text/html` (returns form for modal)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | Yes | User ID to edit |

---

### 4.8 Get Recommended Permissions

**Endpoint:** `get_recommended_permissions.php`  
**Method:** GET  
**Auth Required:** No  
**Content-Type:** `application/json`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `role` | string | Yes | Role name: `super_admin`, `admin`, `manager`, `assistant`, `viewer` |

**Request:**

```
GET /get_recommended_permissions.php?role=admin
```

**Response:**

```json
[
  "view_dashboard",
  "create_invoice",
  "view_invoices",
  "edit_invoice",
  "mark_invoice_paid",
  "download_invoice_pdf",
  "email_invoice"
]
```

---

## 5. Client Management Endpoints

### 5.1 Get Client Details

**Endpoint:** `get-client.php`  
**Method:** GET  
**Auth Required:** Yes  
**Permission:** `view_clients` or `view_all_clients`  
**Content-Type:** `application/json`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | Yes | Client ID |

**Request:**

```
GET /get-client.php?id=42
```

**Success Response:**

```json
{
  "id": 42,
  "company_name": "Acme Corporation",
  "representative": "John Smith",
  "phone": "+1-555-123-4567",
  "email": "john@acme.com",
  "address": "123 Business St, City, State 12345",
  "gst_hst": "123456789RT0001",
  "notes": "VIP client - priority support",
  "created_by": 1,
  "created_at": "2026-01-01 10:00:00",
  "updated_at": "2026-01-05 14:30:00",
  "deleted_at": null
}
```

**Error Responses:**

```json
{"error": "Unauthorized"}           // 401 - Not logged in
{"error": "Client ID required"}     // 400 - Missing ID
{"error": "Client not found"}       // 404 - Not found or no access
{"error": "Server error"}           // 500 - Database error
```

**Security Notes:**
- Returns 404 for both "not found" and "no permission" (privacy-safe)
- Respects `created_by` ownership unless user has `view_all_clients`
- Excludes soft-deleted clients (`deleted_at IS NULL`)

---

### 5.2 Client CRUD Operations

**Endpoint:** `clients.php`  
**Method:** POST  
**Auth Required:** Yes  
**Permissions:** Various (see below)

**Operations via POST:**

| POST Field | Permission Required | Description |
|------------|-------------------|-------------|
| `company_name` (new) | `add_client` | Create new client |
| `client_id` (existing) | `edit_client` | Update existing client |
| `delete_id` | `delete_client` | Soft delete client |
| `delete_all_clients` | `delete_client` | Soft delete all clients |
| `undo_recent` | `restore_clients` | Restore most recent deletion |
| `undo_all` | `restore_clients` | Restore all deleted clients |

**Create/Update Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `client_id` | int | No | If present, update; else create |
| `company_name` | string | Yes | Company name |
| `representative` | string | No | Contact person |
| `phone` | string | Yes | Phone number |
| `email` | string | Yes | Email (validated) |
| `address` | string | Yes | Full address |
| `gst_hst` | string | No | Tax ID number |
| `notes` | string | No | Additional notes |

---

## 6. Invoice Management Endpoints

### 6.1 Save Invoice

**Endpoint:** `save_invoice.php`  
**Method:** POST (via session data)  
**Auth Required:** Yes  
**Permission:** `create_invoice`

**Session Data Expected (`\['invoice_data']**):**

```php
[
    'bill_to' => [
        'Company Name' => 'Acme Corp',
        'Contact Name' => 'John Smith',
        'Address' => '123 Street',
        'Phone' => '+1-555-1234',
        'Email' => 'john@acme.com',
        'gst_hst' => '123456789',
        'notes' => 'Net 30'
    ],
    'items' => [
        ['description' => 'Service', 'quantity' => 1, 'price' => 100.00]
    ],
    'total' => 100.00,
    'currency_code' => 'USD',
    'due_date' => '2026-02-09'
]
```

**Actions Performed:**
1. Parse invoice data from session
2. Create/find client record
3. Generate invoice HTML
4. Generate PDF via DomPDF
5. Create Stripe payment link (if enabled)
6. Save to database
7. Optionally send email to client

**Response:** Redirects to `history.php` or displays error

---

### 6.2 Generate Invoice PDF

**Endpoint:** `generate_invoice.php`  
**Method:** GET  
**Auth Required:** Yes

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `invoice` | string | Yes | Invoice number |
| `download` | bool | No | Force download vs inline display |

**Request:**

```
GET /generate_invoice.php?invoice=INV-2026-001
GET /generate_invoice.php?invoice=INV-2026-001&download=1
```

**Response:** PDF file (inline or attachment)

---

### 6.3 Invoice History Operations

**Endpoint:** `history.php`  
**Method:** GET/POST  
**Auth Required:** Yes  
**Permission:** `view_invoices` or `view_invoice_history`

**POST Operations:**

| POST Field | Permission | Description |
|------------|------------|-------------|
| `delete_id` | `delete_invoice` | Soft delete invoice |
| `mark_paid` | `mark_invoice_paid` | Mark invoice as paid |
| `send_email` | `email_invoice` | Resend invoice email |

---

## 7. Expense Management Endpoints

### 7.1 Export Expenses

**Endpoint:** `export_expenses.php`  
**Method:** GET  
**Auth Required:** Yes  
**Permission:** `view_expenses`  
**Content-Type:** `text/csv`

**Request:**

```
GET /export_expenses.php
```

**Response:** CSV file download with headers:
- `#` (row number)
- `Date`
- `Vendor`
- `Category`
- `Amount`
- `Status`
- `Payment Method`
- `Proof`

---

### 7.2 Expense CRUD Operations

**Endpoint:** `expenses.php`  
**Method:** POST  
**Auth Required:** Yes  
**Permission:** `access_expenses_tab`

**POST Operations:**

| POST Field | Permission | Description |
|------------|------------|-------------|
| `undo_recent` | `undo_recent_expense` | Restore last deleted |
| `undo_all` | `undo_all_expenses` | Restore all deleted |
| `delete_id` | `delete_expense` | Soft delete expense |

---

### 7.3 Add Expense

**Endpoint:** `add-expense.php`  
**Method:** POST  
**Auth Required:** Yes  
**Permission:** `add_expense`

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `expense_date` | date | Yes | Date of expense |
| `vendor` | string | Yes | Vendor/supplier name |
| `amount` | decimal | Yes | Expense amount |
| `category` | string | No | Expense category |
| `notes` | string | No | Additional notes |
| `client_id` | int | No | Associated client |
| `is_recurring` | bool | No | Recurring flag |
| `receipt` | file | No | Receipt upload |

---

## 8. Email Template Endpoints

### 8.1 Get Email Template

**Endpoint:** `ajax_get_email_template.php`  
**Method:** GET  
**Auth Required:** Yes  
**Permission:** `access_email_templates_page`  
**Content-Type:** `application/json`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | Yes | Template ID |

**Request:**

```
GET /ajax_get_email_template.php?id=5
```

**Success Response:**

```json
{
  "ok": true,
  "template": {
    "id": 5,
    "template_name": "Invoice Payment Reminder",
    "assigned_notification_type": "payment_reminder",
    "cc_emails": "manager@company.com",
    "bcc_emails": "archive@company.com",
    "template_html": "<p>Dear {{client_name}},</p>...",
    "design_json": "{...}"
  }
}
```

**Error Responses:**

```json
{"ok": false, "error": "Not logged in"}      // 401
{"ok": false, "error": "Access denied"}      // 403
{"ok": false, "error": "Invalid template id"} // 400
{"ok": false, "error": "Template not found"}  // 404
{"ok": false, "error": "Server error"}        // 500
```

---

### 8.2 Delete Email Template

**Endpoint:** `delete_email_template.php`  
**Method:** GET  
**Auth Required:** No (should be secured)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | int | Yes | Template ID to delete |

**Request:**

```
GET /delete_email_template.php?id=5
```

**Response:** Redirects to `settings-email-templates.php` with success/error message

---

## 9. Settings & Configuration Endpoints

### 9.1 Settings from Database

Settings are stored in the `settings` table and accessed via the `get_setting()` function:

```php
// In config.php
function get_setting(\, \ = '') {
    global \;
    \ = \->prepare("SELECT key_value FROM settings WHERE key_name = ? LIMIT 1");
    \->execute([\]);
    \ = \->fetch();
    return (\ && isset(\['key_value'])) ? \['key_value'] : \;
}
```

**Common Settings Keys:**

| Key | Description |
|-----|-------------|
| `stripe_publishable_key` | Stripe public key |
| `stripe_secret_key` | Stripe secret key |
| `test_mode` | Payment test mode flag |
| `smtp_host` | SMTP server hostname |
| `smtp_port` | SMTP port number |
| `smtp_username` | SMTP authentication user |
| `smtp_password` | SMTP authentication password |
| `email_from_name` | Email sender name |
| `email_from_address` | Email sender address |
| `company_name` | Company display name |
| `company_logo` | Logo file path |
| `app_logo_url` | Application logo URL |
| `invoice_prefix` | Invoice number prefix |
| `cron_secret` | Cron job authentication token |

---

## 10. Webhook Endpoints

### 10.1 Stripe Payment Success

**Endpoint:** `payment-success.php`  
**Method:** GET (redirect from Stripe)  
**Auth Required:** No (public callback)

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `invoice_id` | int | Conditional | Invoice ID (numeric) |
| `invoice` | string | Conditional | Invoice ID or number |

**Supported Formats:**

```
GET /payment-success.php?invoice_id=420
GET /payment-success.php?invoice=420          (numeric = ID)
GET /payment-success.php?invoice=INV-2026-001 (string = invoice_number)
```

**Actions Performed:**
1. Look up invoice by ID or number
2. Update invoice status to "Paid"
3. Update HTML file (add PAID watermark, remove Pay button)
4. Regenerate PDF with paid status
5. Send payment confirmation email to client
6. Redirect to `view-invoice.php?invoice=XXX&celebrate=1`

---

## 11. Cron Job Endpoints

### 11.1 Send Invoice Reminders

**Endpoint:** `cron/send_invoice_reminders.php`  
**Method:** GET or CLI  
**Auth Required:** Token-based  
**Schedule:** Hourly (recommended)

**Authentication:**

```
# Via URL
GET /cron/send_invoice_reminders.php?token=YOUR_CRON_SECRET

# Via CLI
php cron/send_invoice_reminders.php --token=YOUR_CRON_SECRET
```

**Token Validation:**
- Token compared against `settings.cron_secret` using `hash_equals()`
- Returns 403 if invalid

**Reminder Types Processed:**

| Key | Description |
|-----|-------------|
| `before_due` | X days before due date |
| `on_due` | On the due date |
| `after_3` | 3 days after due |
| `after_7` | 7 days after due |
| `after_14` | 14 days after due |
| `after_21` | 21 days after due |

**Response (Text):**

```
DONE
Sent: 5
Failed: 1
Skipped (disabled): 2
Skipped (no template): 1
```

**Database Tables Used:**
- `settings` (reminder configuration)
- `invoices` (find unpaid invoices)
- `clients` (get client email)
- `email_templates` (get reminder template)
- `invoice_reminder_logs` (prevent duplicates, log sends)

---

### 11.2 Cleanup Sessions

**Endpoint:** `cleanup_sessions.php`  
**Method:** GET  
**Auth Required:** Token-based (recommended)  
**Schedule:** Daily

**Actions:**
- Mark expired sessions as terminated
- Clean up `user_sessions` table

---

## 12. Page Routes (Full Pages)

### Main Application Pages

| Route | Permission Required | Description |
|-------|-------------------|-------------|
| `index.php` | `view_dashboard` | Dashboard with charts |
| `clients.php` | `view_clients` | Client management |
| `expenses.php` | `access_expenses_tab` | Expense tracking |
| `history.php` | `view_invoices` | Invoice history |
| `create-invoice.php` | `create_invoice` | Invoice creation wizard |
| `users.php` | `manage_users_page` | User administration |
| `settings-permissions.php` | `manage_permissions` | Permission matrix |
| `manage-email-templates.php` | `access_email_templates_page` | Email templates |

### Public/Auth Pages

| Route | Auth | Description |
|-------|------|-------------|
| `login.php` | No | Login form |
| `logout.php` | Yes | Logout handler |
| `access-denied.php` | No | Permission denied page |
| `payment-success.php` | No | Stripe callback |

### Landing Pages

| Route | Auth | Description |
|-------|------|-------------|
| `homelandingpage1.php` | No | Landing page variant 1 |
| `homelandingpage2.php` | No | Landing page variant 2 |
| `homelandingpage5.php` | No | Landing page variant 5 |
| `homelandingpage6.php` | No | Landing page variant 6 |

---

## 13. Error Handling

### HTTP Status Codes Used

| Code | Meaning | Used By |
|------|---------|---------|
| 200 | Success | All successful requests |
| 400 | Bad Request | Invalid parameters |
| 401 | Unauthorized | Not logged in |
| 403 | Forbidden | No permission |
| 404 | Not Found | Resource not found |
| 500 | Server Error | Database/PHP errors |

### Error Response Formats

**JSON Endpoints:**

```json
{
  "error": "Error message here"
}
```

Or with more detail:

```json
{
  "ok": false,
  "error": "Detailed error message"
}
```

Or with success flag:

```json
{
  "success": false,
  "message": "Error description"
}
```

**Form Handlers:**

```php
\['error'] = "Error message";
header("Location: original_page.php");
```

---

## 14. Common Response Patterns

### Success Patterns

**Boolean Success:**
```json
{"success": true}
{"valid": true}
{"ok": true}
```

**Data Response:**
```json
{
  "ok": true,
  "data": { ... }
}
```

**With Message:**
```json
{
  "success": true,
  "message": "Operation completed successfully"
}
```

### Pagination Pattern

Not currently implemented - all lists return full results.  
**Recommendation:** Add pagination for large datasets:

```json
{
  "data": [...],
  "pagination": {
    "page": 1,
    "per_page": 25,
    "total": 150,
    "total_pages": 6
  }
}
```

### Date Formats

| Context | Format | Example |
|---------|--------|---------|
| Database | `YYYY-MM-DD HH:MM:SS` | `2026-01-09 14:30:00` |
| JSON Response | `YYYY-MM-DD` | `2026-01-09` |
| Display | Various | `Jan 09, 2026` |

---

## Quick Reference: All AJAX Endpoints

| Endpoint | Method | Auth | Returns |
|----------|--------|------|---------|
| `dashboard-data.php` | GET | Yes | JSON |
| `dashboard-summary.php` | GET | Yes | JSON |
| `ajax-check-password.php` | POST | Yes | JSON |
| `ajax-check-username.php` | POST | Yes | JSON |
| `ajax-update-password.php` | POST | Yes | JSON |
| `check_availability.php` | GET | No | JSON |
| `get-client.php` | GET | Yes | JSON |
| `get_user.php` | GET | Yes | HTML |
| `ajax_get_email_template.php` | GET | Yes | JSON |
| `get_recommended_permissions.php` | GET | No | JSON |
| `export_expenses.php` | GET | Yes | CSV |

---

**Document Version:** 1.0  
**Author:** Claude Code  
**Date:** January 9, 2026
