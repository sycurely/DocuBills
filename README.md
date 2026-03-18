# DocuBills

DocuBills is a Laravel-based billing and invoicing application for managing clients, invoices, expenses, taxes, email workflows, and user access from a single PHP codebase.

## Stack

- PHP 8.2+
- Laravel
- MySQL or MariaDB
- Composer
- Blade views and server-rendered pages

## Key Directories

- `app/` application logic, controllers, models, jobs, and services
- `config/` framework and app configuration
- `database/` migrations, seeders, and local database assets
- `public/` public web assets and entry files
- `resources/` views and frontend resources
- `routes/` route definitions
- `storage/` runtime-generated files
- `reference_project_php_docubills_old/` legacy reference project

## Local Setup

1. Install PHP 8.2+ and Composer.
2. Run `composer install`.
3. Create `.env` from your local environment values.
4. Configure the database connection.
5. Run migrations if needed.
6. Start the app with Laravel's local server or your web server of choice.

## Notes

- `.env`, cache files, logs, and oversized archive artifacts are intentionally ignored.
- The repository includes a legacy reference project alongside the current Laravel application.
