# Module 03: Client Management

**Last Updated:** January 9, 2026  
**Module Version:** 1.1.8

## Overview

The Client Management module handles customer/client information for invoicing and expense tracking. It provides CRUD operations with permission-based access control and soft deletion support.

## Key Features

- Client CRUD operations
- Contact information tracking
- GST/HST number storage
- Client notes and metadata
- Soft deletion with restore
- Permission-based visibility
- User ownership tracking
- Search and filter capabilities

## Key Files

- `clients.php` - Client management interface
- `get-client.php` - Client data retrieval API
- `search_clients.php` - Client search functionality

## Database Tables

- `clients` - Client records
  - id, company_name, representative, phone, email
  - address, gst_hst, notes
  - created_by, created_at, updated_at, deleted_at

## Client Operations

### Create Client
- Manual entry via form
- Auto-creation from invoice data
- Validation (email format, required fields)

### Update Client
- Edit existing client information
- Permission: `edit_client`
- Ownership check (unless `view_all_clients`)

### Delete Client
- Soft delete (sets deleted_at)
- Permission: `delete_client`
- Restore functionality available

### Search Clients
- Search by company name, email, phone
- Permission-based filtering
- Supports partial matches

## Permission Requirements

- `view_clients` - View own clients
- `view_all_clients` - View all clients
- `add_client` - Create new client
- `edit_client` - Update client
- `delete_client` - Delete client
- `restore_clients` - Restore deleted clients

## API Endpoints

- `clients.php` - GET/POST - Client CRUD operations
- `get-client.php` - GET - Retrieve client data (JSON)
- `search_clients.php` - GET - Search clients

## Data Relationships

- One-to-Many: clients → invoices
- One-to-Many: clients → expenses
- Many-to-One: clients → users (created_by)

## Usage Example

```php
// Get client
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

// Create client
$stmt = $pdo->prepare("
    INSERT INTO clients (company_name, email, created_by)
    VALUES (?, ?, ?)
");
$stmt->execute([$company_name, $email, $_SESSION['user_id']]);
```

For detailed documentation, see ARCHITECTURE.md, API_ENDPOINTS.md, and DATABASE.md.
