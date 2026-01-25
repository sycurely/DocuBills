# Module 04: Expense Management

**Last Updated:** January 9, 2026  
**Module Version:** 1.1.8

## Overview

The Expense Management module tracks business expenses with categorization, receipt uploads, payment status, and client association. It supports recurring expenses and provides export functionality.

## Key Features

- Expense tracking with categories
- Receipt upload and storage
- Payment status tracking (Paid/Unpaid)
- Payment method tracking
- Payment proof uploads
- Recurring expense support
- Client association
- Email CC/BCC for notifications
- CSV export functionality
- Soft deletion with restore

## Key Files

- `expenses.php` - Expense management interface
- `add-expense.php` - Expense creation form handler
- `export_expenses.php` - CSV export functionality

## Database Tables

- `expenses` - Expense records
  - id, expense_date, vendor, amount, category
  - notes, receipt_url, payment_proof
  - is_recurring, client_id, status
  - payment_method, email_cc, email_bcc
  - created_by, created_at, updated_at, deleted_at

## Expense Operations

### Create Expense
- Date, vendor, amount, category
- Optional: receipt upload, client association
- Recurring flag support
- Payment method selection

### Update Expense
- Edit expense details
- Update payment status
- Upload payment proof
- Change category or vendor

### Delete Expense
- Soft delete (sets deleted_at)
- Restore functionality
- Bulk delete operations

### Export Expenses
- CSV format export
- Filtered by date range, status, category
- Includes all expense fields

## Permission Requirements

- `view_expenses` - View own expenses
- `view_all_expenses` - View all expenses
- `add_expense` - Create expense
- `edit_expense` - Update expense
- `delete_expense` - Delete expense
- `export_expenses` - Export to CSV
- `access_expenses_tab` - Access expenses page

## File Storage

- Receipts: `/uploads/expense_receipts/`
- Payment Proofs: `/uploads/expense_receipts/`
- File naming: `{expense_id}_{timestamp}.{ext}`

## Expense Categories

Categories are free-form text fields. Common categories:
- Office Supplies
- Travel
- Meals & Entertainment
- Software Subscriptions
- Utilities
- Professional Services

## Payment Methods

- Cash
- Check
- Bank Transfer
- Credit Card
- Debit Card
- Other

## API Endpoints

- `expenses.php` - GET/POST - Expense CRUD operations
- `add-expense.php` - POST - Create expense
- `export_expenses.php` - GET - Export to CSV

## Data Relationships

- Many-to-One: expenses → clients
- Many-to-One: expenses → users (created_by)

## Usage Example

```php
// Create expense
$stmt = $pdo->prepare("
    INSERT INTO expenses (expense_date, vendor, amount, category, created_by)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$date, $vendor, $amount, $category, $_SESSION['user_id']]);

// Get expenses by client
$stmt = $pdo->prepare("
    SELECT * FROM expenses
    WHERE client_id = ? AND deleted_at IS NULL
    ORDER BY expense_date DESC
");
$stmt->execute([$client_id]);
```

For detailed documentation, see ARCHITECTURE.md, API_ENDPOINTS.md, and DATABASE.md.
