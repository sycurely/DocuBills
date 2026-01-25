# Module 02: Invoice Management

**Last Updated:** January 9, 2026  
**Module Version:** 1.1.8

## Overview

The Invoice Management module handles the complete invoice lifecycle from creation to payment, including Excel/CSV import, HTML/PDF generation, Stripe payment links, and automated email reminders.

## Key Features

- Excel/CSV bulk import
- Manual invoice creation wizard
- PDF generation using DomPDF
- Stripe payment integration
- Multi-currency support (USD, CAD, GBP, EUR)
- Recurring invoice support
- Automated email reminders
- Invoice status tracking (Paid/Unpaid)
- Custom branding (colors, logos)
- Soft deletion (trash bin)

## Key Files

- `create-invoice.php` - Invoice creation interface
- `save_invoice.php` - Invoice persistence, PDF generation, Stripe link
- `generate_invoice.php` - PDF generation endpoint
- `history.php` - Invoice listing and management
- `template_invoice.php` - Invoice HTML template
- `parse_excel.php` - Excel/CSV parsing utility
- `payment-success.php` - Stripe payment callback
- `cron/send_invoice_reminders.php` - Automated reminders

## Database Tables

- `invoices` - Invoice records
- `clients` - Client information
- `invoice_templates` - Design templates
- `invoice_reminder_logs` - Email reminder tracking

## Invoice Creation Workflow

1. Upload Excel/CSV OR manual entry
2. Parse data (PHPSpreadsheet)
3. Find or create client record
4. Generate invoice number
5. Render HTML from template
6. Generate PDF (DomPDF)
7. Create Stripe payment link
8. Save to database
9. Send email to client

## PDF Generation

- Uses DomPDF library
- Custom fonts supported
- HTML5 rendering
- Stored in `/invoices/` directory
- Watermarks for paid invoices

## Payment Integration

- Stripe checkout sessions
- Payment links stored in database
- Automatic status update on payment
- Payment confirmation emails

## API Endpoints

- `create-invoice.php` - GET - Invoice creation form
- `save_invoice.php` - POST - Save invoice
- `generate_invoice.php` - GET - Generate PDF
- `history.php` - GET/POST - Invoice management
- `payment-success.php` - GET - Payment callback

## File Structure

- `/invoices/` - PDF and HTML files
- `template_invoice.php` - HTML template

For detailed documentation, see ARCHITECTURE.md, API_ENDPOINTS.md, and DATABASE.md.
