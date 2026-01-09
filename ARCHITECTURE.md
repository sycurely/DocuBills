# DocuBills System Architecture

**Last Updated:** January 9, 2026  
**Project Version:** 1.1.8  
**Document Purpose:** Comprehensive system architecture reference for developers and LLMs

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [System Context](#2-system-context)
3. [Application Layers](#3-application-layers)
4. [Directory Structure](#4-directory-structure)
5. [Component Architecture](#5-component-architecture)
6. [Data Architecture](#6-data-architecture)
7. [Security Architecture](#7-security-architecture)
8. [Frontend Architecture](#8-frontend-architecture)
9. [Integration Architecture](#9-integration-architecture)
10. [Deployment Architecture](#10-deployment-architecture)
11. [Design Patterns](#11-design-patterns)
12. [Technology Decisions](#12-technology-decisions)

---

## 1. Architecture Overview

### Architecture Style

DocuBills follows a **Monolithic Server-Side Rendered (SSR)** architecture with **AJAX enhancements** for dynamic interactions.

```
+------------------------------------------------------------------+
|                        CLIENT BROWSER                             |
|  +------------------------------------------------------------+  |
|  |  HTML/CSS/JS  |  Chart.js  |  Font Awesome  |  SweetAlert  |  |
|  +------------------------------------------------------------+  |
+------------------------------------------------------------------+
                              |
                              | HTTP/HTTPS
                              v
+------------------------------------------------------------------+
|                      APACHE WEB SERVER                            |
|                        (PHP 8.2 Handler)                          |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
|                    DOCUBILLS APPLICATION                          |
|  +------------------------------------------------------------+  |
|  |                   PHP APPLICATION LAYER                     |  |
|  |  +--------+  +----------+  +--------+  +----------------+  |  |
|  |  | Pages  |  |   AJAX   |  |  Cron  |  |   Webhooks     |  |  |
|  |  +--------+  +----------+  +--------+  +----------------+  |  |
|  +------------------------------------------------------------+  |
|  |                     SHARED SERVICES                         |  |
|  |  +--------+  +----------+  +--------+  +----------------+  |  |
|  |  | Config |  |Middleware|  | Mailer |  | Styles/Scripts |  |  |
|  |  +--------+  +----------+  +--------+  +----------------+  |  |
|  +------------------------------------------------------------+  |
|  |                    LIBRARY LAYER                            |  |
|  |  +----------+  +---------+  +-------------+  +-----------+ |  |
|  |  |  DomPDF  |  |PHPMailer|  |PHPSpreadsheet|  |  Stripe  | |  |
|  |  +----------+  +---------+  +-------------+  +-----------+ |  |
|  +------------------------------------------------------------+  |
+------------------------------------------------------------------+
                              |
              +---------------+---------------+
              |               |               |
              v               v               v
        +---------+     +---------+     +-----------+
        |  MySQL  |     |  SMTP   |     |  Stripe   |
        | Database|     | Server  |     |    API    |
        +---------+     +---------+     +-----------+
```

### Key Characteristics

| Characteristic | Description |
|----------------|-------------|
| **Architecture** | Monolithic PHP application |
| **Rendering** | Server-side with AJAX enhancements |
| **State Management** | PHP Sessions + Database |
| **Authentication** | Session-based with database tracking |
| **Authorization** | Role-Based Access Control (RBAC) |
| **API Style** | Internal AJAX endpoints (not RESTful) |
| **Database** | MySQL/MariaDB with PDO |

---

## 2. System Context

### External Systems

```
                                    +------------------+
                                    |   END USERS      |
                                    | (Web Browsers)   |
                                    +--------+---------+
                                             |
                                             v
+----------------+              +------------------------+              +----------------+
|    STRIPE      |<------------>|       DOCUBILLS        |<------------>|  SMTP SERVER   |
| Payment Gateway|   Webhooks   |    (Main Application)  |    SMTP      | (Email Delivery)|
+----------------+              +------------------------+              +----------------+
                                             |
                                             v
                                    +------------------+
                                    |  MySQL Database  |
                                    +------------------+
```

### User Roles

| Role | Description | Typical Permissions |
|------|-------------|---------------------|
| **Super Admin** | Full system access | All permissions |
| **Admin** | Administrative access | Most permissions except system config |
| **Manager** | Supervisory access | View and report permissions |
| **Assistant** | Limited operational | Basic view permissions |
| **Viewer** | Read-only access | Dashboard only |

---

## 3. Application Layers

### Layer Diagram

```
+------------------------------------------------------------------+
|                      PRESENTATION LAYER                           |
|  +------------------------------------------------------------+  |
|  |  Page Controllers  |  AJAX Endpoints  |  Shared Templates  |  |
|  |  (index.php, etc.) | (dashboard-*.php)|  (header, styles)  |  |
|  +------------------------------------------------------------+  |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
|                       BUSINESS LAYER                              |
|  +------------------------------------------------------------+  |
|  |  Authentication  |  Authorization  |  Business Logic       |  |
|  |  (session mgmt)  |  (middleware)   |  (inline in pages)    |  |
|  +------------------------------------------------------------+  |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
|                        DATA LAYER                                 |
|  +------------------------------------------------------------+  |
|  |        PDO Database Access (config.php)                     |  |
|  |  +--------+  +----------+  +----------+  +---------------+ |  |
|  |  | Users  |  | Invoices |  |  Clients |  |   Settings    | |  |
|  |  +--------+  +----------+  +----------+  +---------------+ |  |
|  +------------------------------------------------------------+  |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
|                     INTEGRATION LAYER                             |
|  +------------------------------------------------------------+  |
|  |   Stripe SDK   |   PHPMailer   |   DomPDF   | PHPSpreadsheet|  |
|  +------------------------------------------------------------+  |
+------------------------------------------------------------------+
```

### Layer Responsibilities

#### Presentation Layer
- **Page Controllers**: Full HTML page rendering
- **AJAX Endpoints**: JSON data for dynamic updates
- **Shared Templates**: Reusable UI components (header.php, styles.php, scripts.php)

#### Business Layer
- **Authentication**: Session management, login/logout
- **Authorization**: Permission checking via middleware.php
- **Business Logic**: Invoice creation, payment processing, email sending

#### Data Layer
- **Database Access**: PDO with prepared statements
- **Settings Management**: Key-value store in settings table
- **File Storage**: Invoice PDFs, user uploads

#### Integration Layer
- **Stripe**: Payment processing
- **PHPMailer**: Email delivery
- **DomPDF**: PDF generation
- **PHPSpreadsheet**: Excel file processing

---

## 4. Directory Structure

### Root Directory Layout

```
DocuBills/
|
+-- .claude/                    # Claude AI configuration
+-- .vscode/                    # VS Code settings (SFTP config)
|
+-- assets/                     # Static assets
|   +-- brand/                  # Brand assets (logos)
|   +-- receipts/               # Receipt storage
|   +-- uploads/                # Dynamic uploads (app logos)
|   +-- script.js               # Landing page scripts
|   +-- style.css               # Landing page styles
|   +-- SimpleXLS.php           # Excel parser (legacy)
|   +-- SimpleXLSX.php          # Excel parser (modern)
|
+-- cron/                       # Scheduled tasks
|   +-- send_invoice_reminders.php
|
+-- fonts/                      # Custom fonts for PDF
|   +-- NotoNaskhArabic-Regular.ttf
|   +-- NotoSans-Regular.ttf
|
+-- invoices/                   # Generated invoice files
|   +-- *.html                  # Invoice HTML files
|   +-- *.pdf                   # Invoice PDF files
|
+-- libs/                       # Local libraries
|   +-- dompdf/                 # PDF generation
|   +-- PHPMailer/              # Email sending
|
+-- uploads/                    # User uploads
|   +-- avatars/                # User profile pictures
|   +-- expense_receipts/       # Expense receipts
|   +-- payment_proofs/         # Payment proof files
|
+-- vendor/                     # Composer dependencies
```

### PHP File Categories

**CORE CONFIGURATION:**
- `config.php` - Database, constants, helpers
- `config.example.php` - Template for config.php
- `middleware.php` - Permission checking
- `mailer.php` - Email functions

**SHARED UI COMPONENTS:**
- `header.php` - Navigation, user profile
- `styles.php` - Global CSS (PHP-generated)
- `scripts.php` - Global JavaScript (PHP-generated)

**PAGE CONTROLLERS:**
- `index.php` - Dashboard
- `clients.php` - Client management
- `expenses.php` - Expense tracking
- `history.php` - Invoice history
- `users.php` - User administration
- `create-invoice.php` - Invoice creation
- `settings-permissions.php` - Permission matrix
- `manage-email-templates.php` - Email templates

**AJAX ENDPOINTS:**
- `dashboard-data.php` - Dashboard chart data
- `dashboard-summary.php` - Dashboard summary
- `ajax-check-password.php` - Password validation
- `ajax-check-username.php` - Username availability
- `ajax-update-password.php` - Password change
- `ajax_get_email_template.php` - Get email template
- `get-client.php` - Get client data
- `get_user.php` - Get user details

**FORM HANDLERS:**
- `add_user.php` - Create user
- `edit_user.php` - Edit user form
- `add-expense.php` - Create expense
- `save_invoice.php` - Save invoice + PDF + Stripe

**PAYMENT & WEBHOOKS:**
- `payment-success.php` - Stripe success callback
- `fake-checkout.php` - Test payment flow

---

## 5. Component Architecture

### Core Components

```
    +------------------+
    |   config.php     |<-----------------------------------------+
    | - Database conn  |                                          |
    | - Constants      |                                          |
    | - get_setting()  |                                          |
    +--------+---------+                                          |
             |                                                    |
             v                                                    |
    +------------------+     +------------------+                  |
    | middleware.php   |     |   mailer.php     |                  |
    | - has_permission |     | - sendInvoice    |                  |
    | - check_role     |     |   Email()        |                  |
    +--------+---------+     +--------+---------+                  |
             |                        |                            |
             v                        v                            |
    +------------------+     +------------------+     +------------+
    |   header.php     |     |   styles.php     |     | scripts.php|
    | - Navigation     |     | - CSS Variables  |     | - Theme    |
    | - User profile   |     | - Dark mode      |     | - Modals   |
    | - Theme toggle   |     | - Components     |     | - Tables   |
    +--------+---------+     +------------------+     +------------+
             |
             v
    +------------------------------------------------------------------+
    |                    PAGE CONTROLLERS                               |
    |  +----------+ +--------+ +--------+ +-------+ +----------------+  |
    |  | index.php| |clients | |expenses| | users | | create-invoice |  |
    |  |Dashboard | |  .php  | |  .php  | |  .php |     .php        |  |
    |  +----------+ +--------+ +--------+ +-------+ +----------------+  |
    +------------------------------------------------------------------+
             |
             v
    +------------------------------------------------------------------+
    |                    EXTERNAL LIBRARIES                             |
    |  +----------+  +-----------+  +---------------+  +------------+  |
    |  |  DomPDF  |  | PHPMailer |  | PHPSpreadsheet|  | Stripe SDK |  |
    |  +----------+  +-----------+  +---------------+  +------------+  |
    +------------------------------------------------------------------+
```

### Invoice Module Flow

```
+------------------+     +------------------+     +------------------+
| create-invoice   |---->|  save_invoice    |---->| generate_invoice |
|      .php        |     |      .php        |     |      .php        |
| - Upload Excel   |     | - Parse data     |     | - Render HTML    |
| - Manual entry   |     | - Create client  |     | - Generate PDF   |
| - Preview        |     | - Generate HTML  |     | - Stream/Download|
+------------------+     | - Create PDF     |     +------------------+
                         | - Stripe link    |
                         | - Send email     |
                         +--------+---------+
                                  |
                                  v
                         +------------------+     +------------------+
                         |   history.php    |<----|payment-success   |
                         | - List invoices  |     |      .php        |
                         | - Filter/sort    |     | - Mark paid      |
                         | - Actions        |     | - Update PDF     |
                         +------------------+     | - Send receipt   |
                                                  +------------------+
```

---

## 6. Data Architecture

### Entity Relationships

```
                         +-------------+
                         |    USERS    |
                         +------+------+
                                |
            +-------------------+-------------------+
            |                   |                   |
            v                   v                   v
     +------+------+     +------+------+     +------+------+
     |   ROLES     |     |  INVOICES   |     |   CLIENTS   |
     +------+------+     +------+------+     +------+------+
            |                   |                   |
            v                   |                   |
     +------+------+            |                   |
     | PERMISSIONS |            |                   |
     +------+------+            |                   |
            |                   |                   |
            v                   v                   v
     +------+------+     +------+------+     +------+------+
     |ROLE_PERMS   |     |REMINDER_LOGS|     |  EXPENSES   |
     +-------------+     +-------------+     +-------------+

     +-------------+     +-------------+     +-------------+
     |  SETTINGS   |     |EMAIL_TEMPL  |     |USER_SESSIONS|
     +-------------+     +-------------+     +-------------+
```

### Data Flow: Invoice Creation

```
[Excel/CSV Upload] --> [PHPSpreadsheet Parse] --> [Session Storage]
                                                        |
                                                        v
                                               [Client Lookup/Create]
                                                        |
                                                        v
                                               [Invoice HTML Generation]
                                                        |
                           +----------------------------+
                           |                            |
                           v                            v
                    [File System]               [Database]
                    - HTML file                 - invoices table
                    - PDF file                  - HTML column
                           |
                           v
                    [Stripe API] --> [Create Payment Link]
                           |
                           v
                    [PHPMailer] --> [Send to Client]
```

---

## 7. Security Architecture

### Security Layers

```
+------------------------------------------------------------------+
| LAYER 1: TRANSPORT SECURITY                                       |
|   - HTTPS (enforced via hosting)                                  |
|   - Secure cookies                                                |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
| LAYER 2: SESSION SECURITY                                         |
|   - PHP session management                                        |
|   - Session tracking in database                                  |
|   - Session termination on logout                                 |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
| LAYER 3: AUTHENTICATION                                           |
|   - Password hashing (bcrypt)                                     |
|   - Login attempt logging                                         |
|   - Account suspension capability                                 |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
| LAYER 4: AUTHORIZATION (RBAC)                                     |
|   - Role-based permissions                                        |
|   - Per-action permission checks                                  |
|   - Row-level ownership (created_by)                              |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
| LAYER 5: INPUT VALIDATION                                         |
|   - PDO prepared statements                                       |
|   - htmlspecialchars() for output                                 |
|   - filter_var() for email/URL                                    |
+------------------------------------------------------------------+
                              |
                              v
+------------------------------------------------------------------+
| LAYER 6: DATA PROTECTION                                          |
|   - Soft deletes (deleted_at)                                     |
|   - Audit timestamps (created_at, updated_at)                     |
|   - Ownership tracking (created_by)                               |
+------------------------------------------------------------------+
```

### Authentication Flow

```
[Login Request] --> [Validate Credentials] --> [password_verify()]
                                                        |
                                                        v
                                               [Session Create]
                                               $_SESSION['user_id']
                                                        |
                                                        v
                                               [Database Track]
                                               INSERT user_sessions
                                                        |
                                                        v
                                               [Load Permissions]
```

### Authorization Flow

```
[Page Request] --> [Session Check] --> [Load Middleware]
                                               |
                                               v
                                       [Permission Check]
                                       has_permission('xxx')
                                               |
                                       +-------+-------+
                                       |               |
                                       v               v
                                   [Allow]         [Deny]
                                       |               |
                                       v               v
                                   [Page]     [access-denied.php]
```

---

## 8. Frontend Architecture

### UI Structure

```
+------------------------------------------------------------------+
|                        BROWSER                                    |
|  +------------------------------------------------------------+  |
|  |  <html>                                                    |  |
|  |    <head>                                                  |  |
|  |      - styles.php (CSS)                                    |  |
|  |      - CDN links (Font Awesome, Chart.js)                  |  |
|  |    </head>                                                 |  |
|  |    <body>                                                  |  |
|  |      +--------------------------------------------+        |  |
|  |      |  header.php                                |        |  |
|  |      |  - Logo, navigation, user profile          |        |  |
|  |      +--------------------------------------------+        |  |
|  |      |  +----------+  +-------------------------+ |        |  |
|  |      |  | SIDEBAR  |  |     MAIN CONTENT        | |        |  |
|  |      |  | - Menu   |  |  (Page-specific HTML)   | |        |  |
|  |      |  | - Links  |  |                         | |        |  |
|  |      |  +----------+  +-------------------------+ |        |  |
|  |      +--------------------------------------------+        |  |
|  |      scripts.php (JavaScript)                              |  |
|  |    </body>                                                 |  |
|  |  </html>                                                   |  |
|  +------------------------------------------------------------+  |
+------------------------------------------------------------------+
```

### CSS Architecture (styles.php)

```
CSS Variables (:root)
    +-- Colors (--primary, --secondary, etc.)
    +-- Spacing (--header-height, --sidebar-width)
    +-- Effects (--shadow, --transition, --radius)

Dark Mode Variables (body.dark-mode)
    +-- Inverted colors
    +-- Adjusted shadows

Layout Components
    +-- .app-container (flex layout)
    +-- .header (fixed top bar)
    +-- .sidebar (fixed left nav)
    +-- .main-content (scrollable area)

UI Components
    +-- .btn, .btn-primary, .btn-danger
    +-- .card
    +-- .form-group, .form-control
    +-- .table, .data-table
    +-- .modal, .modal-overlay
```

### JavaScript Architecture (scripts.php)

```
Theme Management
    +-- Dark mode toggle
    +-- localStorage persistence

UI Interactions
    +-- Profile menu toggle
    +-- Modal open/close functions
    +-- Form validation

AJAX Functions
    +-- Password validation (real-time)
    +-- Username availability check
    +-- Dashboard data loading

Table Sorting
    +-- Text, Numeric, Currency, Date sorting

Chart Rendering (Chart.js)
    +-- Doughnut chart (paid/unpaid)
    +-- Bar chart (time series)
```

---

## 9. Integration Architecture

### External Service Integrations

```
+------------------+     +------------------+     +------------------+
|    STRIPE        |     |      SMTP        |     |     MySQL        |
+------------------+     +------------------+     +------------------+
| Protocol: HTTPS  |     | Protocol: SMTP   |     | Protocol: TCP    |
| Auth: API Keys   |     | Auth: User/Pass  |     | Auth: User/Pass  |
| SDK: stripe-php  |     | Lib: PHPMailer   |     | Driver: PDO      |
+--------+---------+     +--------+---------+     +--------+---------+
         |                        |                        |
         v                        v                        v
+------------------------------------------------------------------+
|                    DOCUBILLS APPLICATION                          |
+------------------------------------------------------------------+
```

**Integration Points:**

1. **STRIPE**
   - `save_invoice.php` - Create checkout session
   - `payment-success.php` - Handle webhook
   - `config.php` - API keys from settings

2. **SMTP**
   - `mailer.php` - Core email functions
   - `save_invoice.php` - Send invoice
   - `payment-success.php` - Send receipt
   - `cron/send_invoice_reminders.php` - Auto reminders

3. **MySQL**
   - `config.php` - PDO connection
   - All PHP files - Query via $pdo

---

## 10. Deployment Architecture

### Production Environment

```
                    +---------------------------+
                    |      INTERNET             |
                    +-------------+-------------+
                                  |
                                  v
                    +---------------------------+
                    |      cPanel Hosting       |
                    |  +---------------------+  |
                    |  |    Apache 2.4       |  |
                    |  |  (mod_php / LSAPI)  |  |
                    |  +----------+----------+  |
                    |             |             |
                    |             v             |
                    |  +---------------------+  |
                    |  |     PHP 8.2         |  |
                    |  | (ea-php82 handler)  |  |
                    |  +----------+----------+  |
                    |             |             |
                    |  +----------+----------+  |
                    |  |                     |  |
                    |  v                     v  |
                    | +----------+ +----------+ |
                    | |DocuBills | | MariaDB  | |
                    | |   App    | | 10.11+   | |
                    | +----------+ +----------+ |
                    +---------------------------+
```

### Server Configuration

| Component | Configuration |
|-----------|--------------|
| **PHP Version** | 8.2 (ea-php82) |
| **Memory Limit** | 256M |
| **Max Execution** | 30s |
| **Upload Max** | 128M |
| **Post Max** | 128M |
| **Session Handler** | Files |

### File Permissions

```
DocuBills/
+-- [PHP Files]         644 (rw-r--r--)
+-- [Directories]       755 (rwxr-xr-x)
+-- invoices/           775 (rwxrwxr-x)  <- Writable
+-- uploads/            775 (rwxrwxr-x)  <- Writable
+-- config.php          640 (rw-r-----)  <- Restricted
```

---

## 11. Design Patterns

### Patterns in Use

#### 1. Front Controller (Partial)
Each page acts as its own controller with shared includes:

```php
session_start();
require_once 'config.php';
require_once 'middleware.php';
require 'styles.php';
require 'header.php';
// ... page-specific logic
require 'scripts.php';
```

#### 2. Template Method
Shared UI with customizable content:

```php
$activeMenu = 'dashboard';
require 'header.php';
```

#### 3. Repository Pattern (Implicit)
Database queries through shared $pdo connection.

#### 4. Strategy Pattern (Permissions)
Different permission checks based on context.

#### 5. Observer Pattern (Implicit)
Payment success triggers multiple actions.

---

## 12. Technology Decisions

### Why These Technologies?

| Technology | Reason |
|------------|--------|
| **PHP 8.2** | Shared hosting compatibility, mature ecosystem |
| **MySQL/MariaDB** | cPanel standard, reliable |
| **PDO** | SQL injection prevention |
| **DomPDF** | Server-side PDF without external services |
| **PHPMailer** | Robust SMTP handling |
| **PHPSpreadsheet** | Excel/CSV parsing |
| **Stripe** | Industry-standard payments |
| **Chart.js** | Lightweight charts |
| **Font Awesome** | Comprehensive icons |
| **Vanilla JS** | No framework overhead |

### Trade-offs

| Decision | Pros | Cons |
|----------|------|------|
| **Monolithic** | Simple deployment | Hard to scale |
| **No Framework** | Full control | More boilerplate |
| **SSR + AJAX** | SEO-friendly | More page reloads |
| **File-based PDFs** | Simple, reliable | Disk space |
| **Session Auth** | Simple, stateful | Not API-friendly |

---

## Summary

DocuBills is a **well-structured monolithic PHP application** with:

1. **Server-Side Rendering** with AJAX enhancements
2. **Role-Based Access Control** with granular permissions
3. **External integrations** for payments, email, and PDF
4. **File-based storage** for generated documents
5. **Session-based authentication** with database tracking

---

**Document Version:** 1.0  
**Author:** Claude Code  
**Date:** January 9, 2026
