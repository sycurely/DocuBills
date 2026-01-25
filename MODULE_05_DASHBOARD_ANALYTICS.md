# Module 05: Dashboard & Analytics

**Last Updated:** January 9, 2026  
**Module Version:** 1.1.8

## Overview

The Dashboard & Analytics module provides real-time financial insights, visualizations, and summary statistics. It displays revenue, expenses, invoice status, and client analytics using Chart.js.

## Key Features

- Revenue and deficit summaries
- Paid vs Unpaid invoice charts (Chart.js)
- Time-series analysis (daily/monthly/yearly)
- Top clients by revenue
- Recent invoice listings
- Sortable data tables
- Real-time data updates via AJAX
- Period filtering (daily, monthly, yearly, all)

## Key Files

- `index.php` - Dashboard UI
- `dashboard-data.php` - Analytics data API (JSON)
- `dashboard-summary.php` - Summary statistics API (JSON)
- `scripts.php` - Chart.js rendering and table sorting

## Database Tables Used

- `invoices` - Revenue and invoice data
- `expenses` - Expense tracking
- `clients` - Client statistics
- `users` - User activity

## Dashboard Components

### Financial Summary
- Total Revenue (paid invoices)
- Total Deficit (unpaid invoices)
- Net Income calculation
- Expense totals

### Visualizations

#### Doughnut Chart
- Paid vs Unpaid invoices
- Visual representation of payment status
- Click to filter

#### Bar Chart
- Revenue over time
- Configurable period (daily/monthly/yearly)
- Paid and unpaid series

### Data Tables
- Recent invoices
- Top clients
- Sortable columns (text, number, currency, date)
- Real-time updates

## API Endpoints

### dashboard-data.php
**Method:** GET  
**Returns:** JSON

**Query Parameters:**
- `period` - Time grouping (daily, monthly, yearly, all)
- `paid_clients` - Return top paying clients (boolean)
- `unpaid_clients` - Return top unpaid clients (boolean)

**Response:**
```json
{
  "status": {"paid": 45, "unpaid": 12},
  "labels": ["2026-01-03", "2026-01-04"],
  "paid_series": [5, 8],
  "unpaid_series": [2, 1],
  "total_revenue": 125000.50,
  "top_clients": [...],
  "recent_invoices": [...]
}
```

### dashboard-summary.php
**Method:** GET  
**Returns:** JSON

**Response:**
```json
{
  "total_revenue": 125000.50,
  "total_deficit": 35000.00,
  "top_clients": [...],
  "recent_invoices": [...]
}
```

## Chart.js Integration

- Doughnut chart for invoice status
- Bar chart for time-series revenue
- Responsive design
- Interactive tooltips
- Color-coded series

## Data Queries

### Revenue Calculation
```sql
SELECT SUM(total_amount) as revenue
FROM invoices
WHERE status = 'Paid' AND deleted_at IS NULL;
```

### Deficit Calculation
```sql
SELECT SUM(total_amount) as deficit
FROM invoices
WHERE status = 'Unpaid' AND deleted_at IS NULL;
```

### Revenue by Period
```sql
SELECT DATE(created_at) as date, SUM(total_amount) as total
FROM invoices
WHERE status = 'Paid' AND deleted_at IS NULL
  AND created_at >= ?
GROUP BY DATE(created_at);
```

### Top Clients
```sql
SELECT c.company_name, SUM(i.total_amount) as total
FROM invoices i
JOIN clients c ON i.client_id = c.id
WHERE i.status = 'Paid' AND i.deleted_at IS NULL
GROUP BY c.id
ORDER BY total DESC
LIMIT 10;
```

## Permission Requirements

- `view_dashboard` - Access dashboard page
- `view_invoices` - View invoice data
- `view_expenses` - View expense data
- `view_clients` - View client data

## Frontend Features

- Dark mode support
- Responsive design
- Real-time data loading
- Table sorting (text, numeric, currency, date)
- Chart interactions
- Period selector

## Usage Example

```javascript
// Load dashboard data
fetch('dashboard-data.php?period=monthly')
  .then(response => response.json())
  .then(data => {
    // Update charts
    updateDoughnutChart(data.status);
    updateBarChart(data.labels, data.paid_series, data.unpaid_series);
    // Update tables
    updateRecentInvoices(data.recent_invoices);
  });
```

For detailed documentation, see ARCHITECTURE.md, API_ENDPOINTS.md, and DATABASE.md.
