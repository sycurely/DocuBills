# Module 06: Email System

**Last Updated:** January 9, 2026  
**Module Version:** 1.1.8

## Overview

The Email System module handles all email communications including invoice delivery, payment confirmations, and automated reminders. It uses PHPMailer for SMTP delivery and supports customizable email templates with placeholders.

## Key Features

- PHPMailer SMTP integration
- Customizable email templates
- Template placeholders/variables
- HTML email support
- Automated invoice reminders (cron job)
- Email logging and tracking
- Multi-language support
- CC/BCC support
- Attachment support (PDF invoices)

## Key Files

- `mailer.php` - Core email functions
- `manage-email-templates.php` - Template management UI
- `ajax_get_email_template.php` - Template retrieval API
- `delete_email_template.php` - Template deletion
- `cron/send_invoice_reminders.php` - Automated reminder cron job

## Database Tables

- `email_templates` - Email template storage
  - id, name, subject, body, category
  - created_by, created_at, updated_at

- `invoice_reminder_logs` - Reminder tracking
  - id, invoice_id, sent_at, recipient_email, status

- `settings` - SMTP configuration
  - smtp_host, smtp_port, smtp_username, smtp_password
  - email_from_name, email_from_address

## Email Types

### Invoice Delivery
- Sent when invoice is created
- Includes PDF attachment
- Contains payment link
- Template: `invoice_delivery`

### Payment Confirmation
- Sent after successful payment
- Confirms payment receipt
- Template: `payment_confirmation`

### Invoice Reminders
- Automated overdue reminders
- Multiple reminder types:
  - Before due date
  - On due date
  - 3, 7, 14, 21 days after due
- Template: `payment_reminder`

### Custom Notifications
- User-defined templates
- Custom categories
- Manual sending

## Template Placeholders

Common placeholders:
- `{client_name}` - Client company name
- `{invoice_number}` - Invoice number
- `{total_amount}` - Invoice total
- `{due_date}` - Payment due date
- `{company_name}` - Your company name
- `{payment_link}` - Stripe payment link
- `{invoice_date}` - Invoice creation date

## Email Template Management

### Create Template
1. Navigate to Email Templates page
2. Click "Add Template"
3. Enter name, subject, body
4. Select category/notification type
5. Use placeholders for dynamic content
6. Save template

### Edit Template
- Update subject or body
- Modify placeholders
- Change category

### Delete Template
- Soft delete (if implemented)
- Cannot delete if in use

## Automated Reminders

### Cron Job Configuration
- File: `cron/send_invoice_reminders.php`
- Schedule: Hourly (recommended)
- Authentication: Token-based (cron_secret)

### Reminder Logic
1. Find unpaid invoices with due dates
2. Check if reminder already sent (last 7 days)
3. Get appropriate template for reminder type
4. Replace placeholders with invoice data
5. Send email via PHPMailer
6. Log reminder in invoice_reminder_logs

### Reminder Types
- `before_due` - X days before due date
- `on_due` - On the due date
- `after_3` - 3 days after due
- `after_7` - 7 days after due
- `after_14` - 14 days after due
- `after_21` - 21 days after due

## SMTP Configuration

Stored in `settings` table:
- `smtp_host` - SMTP server hostname
- `smtp_port` - SMTP port (usually 587 or 465)
- `smtp_username` - SMTP authentication username
- `smtp_password` - SMTP password (encrypted recommended)
- `email_from_name` - Sender display name
- `email_from_address` - Sender email address

## PHPMailer Integration

```php
// In mailer.php
require_once 'libs/PHPMailer/PHPMailer.php';
require_once 'libs/PHPMailer/SMTP.php';

$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->Host = get_setting('smtp_host');
$mail->Port = get_setting('smtp_port');
$mail->SMTPAuth = true;
$mail->Username = get_setting('smtp_username');
$mail->Password = get_setting('smtp_password');
$mail->SMTPSecure = 'tls';

$mail->setFrom(get_setting('email_from_address'), get_setting('email_from_name'));
$mail->addAddress($recipient_email);
$mail->Subject = $subject;
$mail->Body = $body;
$mail->isHTML(true);
$mail->addAttachment($pdf_path);

$mail->send();
```

## API Endpoints

- `ajax_get_email_template.php` - GET - Retrieve template (JSON)
- `delete_email_template.php` - GET - Delete template
- `manage-email-templates.php` - GET/POST - Template CRUD

## Permission Requirements

- `access_email_templates_page` - Access template management
- `add_email_template` - Create template
- `edit_email_template` - Update template
- `delete_email_template` - Delete template
- `email_invoice` - Send invoice emails

## Usage Example

```php
// Send invoice email
function sendInvoiceEmail($client_email, $invoice_number, $pdf_path, $payment_link) {
    // Get template
    $template = getEmailTemplate('invoice_delivery');
    
    // Replace placeholders
    $subject = str_replace('{invoice_number}', $invoice_number, $template['subject']);
    $body = str_replace('{invoice_number}', $invoice_number, $template['body']);
    $body = str_replace('{payment_link}', $payment_link, $body);
    
    // Send via PHPMailer
    sendEmail($client_email, $subject, $body, $pdf_path);
}
```

For detailed documentation, see ARCHITECTURE.md, API_ENDPOINTS.md, and DATABASE.md.
