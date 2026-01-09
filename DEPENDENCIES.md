# DocuBills Dependencies & External Integrations

**Last Updated:** January 9, 2026  
**Project Version:** 1.1.8  
**Document Purpose:** Identify all dependencies, external integrations, and inter-module relationships

---

## Table of Contents

1. [Dependency Overview](#1-dependency-overview)
2. [PHP Dependencies (Composer)](#2-php-dependencies-composer)
3. [Local Libraries](#3-local-libraries)
4. [External Services & APIs](#4-external-services--apis)
5. [Frontend Dependencies (CDN)](#5-frontend-dependencies-cdn)
6. [Module Dependency Matrix](#6-module-dependency-matrix)
7. [File Dependency Map](#7-file-dependency-map)
8. [Database Dependencies](#8-database-dependencies)
9. [Environment Requirements](#9-environment-requirements)
10. [Integration Points](#10-integration-points)

---

## 1. Dependency Overview

### Quick Reference

| Category | Count | Examples |
|----------|-------|----------|
| Composer Packages | 3 direct, 15+ transitive | PHPSpreadsheet, Stripe, PHPMailer |
| Local Libraries | 3 | DomPDF, PHPMailer (local), SimpleXLS |
| External Services | 3 | Stripe, SMTP, MySQL |
| CDN Libraries | 4 | Font Awesome, Chart.js, Google Fonts, SweetAlert2 |
| Internal Modules | 8 | Invoice, Client, Expense, User, etc. |

### Dependency Architecture

```
+---------------------------------------------------------------------+
|                         DOCUBILLS APPLICATION                        |
+---------------------------------------------------------------------+
|                                                                     |
|  +-----------+  +-----------+  +-----------+  +----------+          |
|  |  INVOICE  |  |  CLIENT   |  |  EXPENSE  |  |   USER   |          |
|  |  MODULE   |  |  MODULE   |  |  MODULE   |  |  MODULE  |          |
|  +-----+-----+  +-----+-----+  +-----+-----+  +----+-----+          |
|        |              |              |              |                |
|  +-----+--------------+--------------+--------------+------+        |
|  |                  CORE SERVICES LAYER                    |        |
|  |  +--------+ +----------+ +--------+ +----------------+  |        |
|  |  | config | |middleware| | mailer | | styles/scripts |  |        |
|  |  |  .php  | |   .php   | |  .php  | |      .php      |  |        |
|  |  +---+----+ +----+-----+ +---+----+ +-------+--------+  |        |
|  +------+----------+------------+-------------+-----------+         |
|         |          |            |             |                     |
+---------+----------+------------+-------------+---------------------+
|         |          |            |             |                     |
|  +------+----------+------------+-------------+----------------+    |
|  |                   EXTERNAL DEPENDENCIES                     |    |
|  |                                                             |    |
|  |  +-------------------------------------------------------+  |    |
|  |  |                 COMPOSER PACKAGES                      |  |    |
|  |  |  PHPSpreadsheet | Stripe PHP | PHPMailer (Composer)   |  |    |
|  |  +-------------------------------------------------------+  |    |
|  |                                                             |    |
|  |  +-------------------------------------------------------+  |    |
|  |  |                 LOCAL LIBRARIES                        |  |    |
|  |  |    DomPDF     |  PHPMailer (Local)  |  SimpleXLS/X    |  |    |
|  |  +-------------------------------------------------------+  |    |
|  |                                                             |    |
|  |  +-------------------------------------------------------+  |    |
|  |  |                 EXTERNAL SERVICES                      |  |    |
|  |  |    Stripe     |     SMTP Server     |     MySQL       |  |    |
|  |  +-------------------------------------------------------+  |    |
|  +-------------------------------------------------------------+    |
+---------------------------------------------------------------------+
```

---

## 2. PHP Dependencies (Composer)

### Direct Dependencies

Defined in `composer.json`:

```json
{
    "require": {
        "phpoffice/phpspreadsheet": "^4.2",
        "stripe/stripe-php": "^17.2",
        "phpmailer/phpmailer": "^6.10"
    }
}
```

### Detailed Dependency Analysis

#### 1. PHPOffice/PHPSpreadsheet (^4.2)

| Attribute | Value |
|-----------|-------|
| **Purpose** | Excel/CSV file parsing for invoice creation |
| **Used By** | `create-invoice.php`, `save_invoice.php` |
| **Features Used** | Read .xlsx, .xls, .csv files |
| **Transitive Dependencies** | composer/pcre, maennchen/zipstream-php, markbaker/complex, markbaker/matrix |

**Files Requiring This Package:**
- `create-invoice.php` - Upload and parse Excel/CSV files
- `save_invoice.php` - Process parsed data
- `assets/SimpleXLSX.php` - Alternative parser (backup)

---

#### 2. Stripe/stripe-php (^17.2)

| Attribute | Value |
|-----------|-------|
| **Purpose** | Payment processing and checkout links |
| **Used By** | `save_invoice.php`, `payment-success.php` |
| **Features Used** | Checkout Sessions, Webhooks, Payment Links |
| **API Version** | 2024+ |

**Configuration (stored in `settings` table):**
- `stripe_publishable_key` - Public key for frontend
- `stripe_secret_key` - Secret key for backend
- `test_mode` - Enable/disable test mode
- `STRIPE_WEBHOOK_SECRET` - Webhook signature (in `config.php`)

**Files Requiring This Package:**
- `save_invoice.php` - Create payment links
- `payment-success.php` - Handle successful payments
- `fake-checkout.php` - Test payment flow

---

#### 3. PHPMailer/phpmailer (^6.10)

| Attribute | Value |
|-----------|-------|
| **Purpose** | Email sending via SMTP |
| **Used By** | `mailer.php`, `cron/send_invoice_reminders.php` |
| **Features Used** | SMTP authentication, HTML emails, attachments |

**Configuration (stored in `settings` table):**
- `smtp_host` - SMTP server hostname
- `smtp_port` - SMTP port (587/465/25)
- `smtp_username` - SMTP authentication username
- `smtp_password` - SMTP authentication password
- `email_from_name` - Sender display name
- `email_from_address` - Sender email address

---

### Transitive Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| composer/pcre | 3.3.2 | PCRE regex wrapper |
| maennchen/zipstream-php | 3.1.2 | ZIP file streaming |
| markbaker/complex | * | Complex number math |
| markbaker/matrix | * | Matrix calculations |

---

## 3. Local Libraries

### Directory Structure

```
libs/
+-- dompdf/
|   +-- autoload.inc.php      <- Entry point
|   +-- README.md
|   +-- LICENSE.LGPL
|   +-- VERSION
|
+-- PHPMailer/
    +-- PHPMailer.php         <- Main class
    +-- SMTP.php              <- SMTP class
    +-- Exception.php         <- Exception handler
    +-- src/
    |   +-- DSNConfigurator.php
    |   +-- OAuth.php
    |   +-- OAuthTokenProvider.php
    |   +-- POP3.php
    +-- language/
        +-- phpmailer.lang-*.php (50+ language files)

assets/
+-- SimpleXLSX.php            <- .xlsx parser (alternative)
+-- SimpleXLS.php             <- .xls parser (legacy)
```

### 1. DomPDF

| Attribute | Value |
|-----------|-------|
| **Location** | `libs/dompdf/` |
| **Purpose** | Convert HTML to PDF |
| **Used By** | `save_invoice.php`, `payment-success.php`, `generate_invoice.php` |
| **License** | LGPL |

**Files Requiring This Library:**
- `save_invoice.php` - Generate invoice PDFs
- `payment-success.php` - Regenerate paid invoice PDF
- `generate_invoice.php` - PDF generation endpoint

---

### 2. PHPMailer (Local)

| Attribute | Value |
|-----------|-------|
| **Location** | `libs/PHPMailer/` |
| **Purpose** | Email sending with SMTP |
| **Used By** | `mailer.php` |
| **Features** | Multi-language support (50+ languages) |

---

### 3. SimpleXLSX / SimpleXLS

| Attribute | Value |
|-----------|-------|
| **Location** | `assets/SimpleXLSX.php`, `assets/SimpleXLS.php` |
| **Purpose** | Lightweight Excel parsing (alternative to PHPSpreadsheet) |
| **Formats** | .xlsx (SimpleXLSX), .xls (SimpleXLS) |

---

## 4. External Services & APIs

### 1. Stripe Payment Gateway

| Attribute | Value |
|-----------|-------|
| **Service Type** | Payment Processing |
| **Integration Method** | REST API via PHP SDK |
| **Environment** | Test mode / Live mode configurable |
| **Webhook URL** | `payment-success.php` |

**API Endpoints Used:**
- `POST /v1/checkout/sessions` - Create checkout session
- Webhook events: `checkout.session.completed`

---

### 2. SMTP Email Server

| Attribute | Value |
|-----------|-------|
| **Service Type** | Email Delivery |
| **Integration Method** | SMTP Protocol via PHPMailer |
| **Supported Providers** | Any SMTP server (Gmail, SendGrid, Mailgun, etc.) |

**Email Types Sent:**
1. **Invoice Delivery** - New invoice to client
2. **Payment Confirmation** - Payment received notification
3. **Invoice Reminders** - Automated overdue reminders (via cron)
4. **Password Reset** - User password changes

---

### 3. MySQL/MariaDB Database

| Attribute | Value |
|-----------|-------|
| **Service Type** | Relational Database |
| **Version** | MariaDB 10.11.15+ |
| **Driver** | PDO MySQL |
| **Charset** | utf8mb4 |

---

## 5. Frontend Dependencies (CDN)

### CSS Libraries

| Library | Version | Purpose |
|---------|---------|---------|
| Font Awesome | 6.4.0 | Icons |
| Google Fonts | - | Typography (Poppins, Inter) |

### JavaScript Libraries

| Library | Version | Purpose |
|---------|---------|---------|
| Chart.js | Latest | Dashboard charts |
| SweetAlert2 | 11 | Toast notifications |

### Files Using Each CDN

| CDN Library | Files |
|-------------|-------|
| Font Awesome | `header.php`, `index.php`, `clients.php`, `expenses.php`, `users.php`, `create-invoice.php`, `history.php` |
| Chart.js | `index.php` (dashboard) |
| SweetAlert2 | `users.php`, `clients.php`, `expenses.php` |
| Google Fonts | `homelandingpage*.php` |

---

## 6. Module Dependency Matrix

### Core Modules

| Module | Required Files | External Services | Database Tables |
|--------|---------------|-------------------|-----------------|
| **Authentication** | config.php, middleware.php | MySQL | users, roles, permissions, role_permissions, user_sessions, login_logs |
| **Invoice Management** | config.php, middleware.php, mailer.php, libs/dompdf | Stripe, SMTP, MySQL | invoices, clients, invoice_templates, invoice_reminder_logs |
| **Client Management** | config.php, middleware.php | MySQL | clients |
| **Expense Management** | config.php, middleware.php | MySQL | expenses, clients |
| **User Management** | config.php, middleware.php | MySQL | users, roles |
| **Dashboard** | config.php, middleware.php | MySQL | invoices, expenses, clients |
| **Email System** | config.php, mailer.php, libs/PHPMailer | SMTP | email_templates, settings |
| **Settings** | config.php, middleware.php | MySQL | settings, permissions, role_permissions |

---

## 7. File Dependency Map

### Core Files (Required by Most Modules)

| File | Purpose | Required By |
|------|---------|-------------|
| `config.php` | Database connection, constants, helper functions | All PHP files |
| `middleware.php` | Permission checking (`has_permission()`) | All authenticated pages |
| `header.php` | Navigation, user profile, theme toggle | All authenticated pages |
| `styles.php` | Global CSS styles | All pages with UI |
| `scripts.php` | Global JavaScript functions | All pages with UI |

### Module-Specific Dependencies

#### Invoice Module Files

| File | Dependencies | External Services |
|------|--------------|-------------------|
| `create-invoice.php` | config, middleware, styles, PHPSpreadsheet | - |
| `save_invoice.php` | config, middleware, mailer, DomPDF, Stripe | Stripe API, SMTP |
| `generate_invoice.php` | config, DomPDF | - |
| `history.php` | config, middleware, styles | - |
| `payment-success.php` | config, mailer, DomPDF | Stripe Webhook |

#### Client Module Files

| File | Dependencies | External Services |
|------|--------------|-------------------|
| `clients.php` | config, middleware, styles | - |
| `get-client.php` | config | - |

#### Expense Module Files

| File | Dependencies | External Services |
|------|--------------|-------------------|
| `expenses.php` | config, middleware, styles | - |
| `add-expense.php` | config, middleware, styles | - |
| `export_expenses.php` | config, middleware | - |

#### User Module Files

| File | Dependencies | External Services |
|------|--------------|-------------------|
| `users.php` | config, middleware, styles | - |
| `add_user.php` | config, middleware | - |
| `edit_user.php` | config, middleware | - |
| `get_user.php` | config | - |

---

## 8. Database Dependencies

### Table Usage by Module

| Table | Modules Using |
|-------|---------------|
| `users` | Auth, User Management, all modules (ownership) |
| `roles` | Auth, User Management, Settings |
| `permissions` | Auth, Settings |
| `role_permissions` | Auth, Settings |
| `invoices` | Invoice, Dashboard, Email Reminders |
| `clients` | Client, Invoice, Expense, Dashboard |
| `expenses` | Expense, Dashboard |
| `email_templates` | Email System, Invoice |
| `invoice_reminder_logs` | Email Reminders, Cron |
| `user_sessions` | Auth, Header |
| `login_logs` | Auth |
| `settings` | All modules (configuration) |

---

## 9. Environment Requirements

### Server Requirements

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| PHP Version | 8.0 | 8.2+ |
| MySQL/MariaDB | 5.7 | 10.11+ |
| Web Server | Apache/Nginx | Apache with mod_rewrite |
| Memory Limit | 128M | 256M |
| Max Execution | 30s | 60s |
| Upload Size | 32M | 128M |

### PHP Extensions Required

| Extension | Purpose | Required By |
|-----------|---------|-------------|
| `pdo_mysql` | Database connectivity | config.php |
| `mbstring` | Multi-byte string support | PHPSpreadsheet, DomPDF |
| `gd` | Image processing | DomPDF |
| `zip` | ZIP file handling | PHPSpreadsheet |
| `xml` | XML parsing | PHPSpreadsheet |
| `openssl` | HTTPS/SMTP encryption | Stripe, PHPMailer |
| `curl` | HTTP requests | Stripe |
| `fileinfo` | File type detection | Upload handling |

### Directory Permissions

| Directory | Permission | Purpose |
|-----------|------------|---------|
| `/invoices/` | 775 | Store generated PDFs/HTML |
| `/uploads/` | 775 | User uploads (avatars, receipts) |
| `/uploads/avatars/` | 775 | User profile pictures |
| `/assets/uploads/` | 775 | Application assets |

---

## 10. Integration Points

### API Endpoints (Internal)

| Endpoint | Method | Purpose | Auth Required |
|----------|--------|---------|---------------|
| `dashboard-data.php` | GET | Dashboard analytics | Yes |
| `dashboard-summary.php` | GET | Summary statistics | Yes |
| `ajax_get_email_template.php` | GET | Fetch email template | Yes |
| `ajax-check-password.php` | POST | Validate password | Yes |
| `ajax-check-username.php` | POST | Check username availability | Yes |
| `ajax-update-password.php` | POST | Change password | Yes |
| `get-client.php` | GET | Retrieve client data | Yes |
| `get_user.php` | GET | Retrieve user data | Yes |

### Webhook Endpoints (External)

| Endpoint | Service | Purpose |
|----------|---------|---------|
| `payment-success.php` | Stripe | Payment completion callback |

### Cron Jobs

| Script | Schedule | Purpose | Dependencies |
|--------|----------|---------|--------------|
| `cron/send_invoice_reminders.php` | Hourly | Send overdue reminders | SMTP, settings |
| `cleanup_sessions.php` | Daily | Clean expired sessions | MySQL |

---

## Summary

### Critical Dependencies

1. **Stripe** - Payment processing (required for paid invoices)
2. **SMTP Server** - Email delivery (required for notifications)
3. **MySQL** - Data persistence (required for all functionality)
4. **DomPDF** - PDF generation (required for invoice creation)
5. **PHPMailer** - Email sending (required for all email features)

### Optional Dependencies

1. **PHPSpreadsheet** - Excel parsing (can use SimpleXLS as fallback)
2. **Chart.js** - Dashboard charts (UI enhancement only)
3. **SweetAlert2** - Toast notifications (UI enhancement only)

### Fallback Strategy

| Primary | Fallback | Impact |
|---------|----------|--------|
| PHPSpreadsheet | SimpleXLSX/SimpleXLS | Limited format support |
| Stripe | Test mode / fake-checkout.php | No real payments |
| SMTP | Local mail() function | Unreliable delivery |

---

**Document Version:** 1.0  
**Author:** Claude Code  
**Date:** January 9, 2026
