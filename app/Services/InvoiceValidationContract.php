<?php

namespace App\Services;

class InvoiceValidationContract
{
    public const SESSION_IMPORT = 'invoice_import';
    public const SESSION_PRICE_CONFIG = 'invoice_import_price_config';

    public const FLASH_ERROR = 'error';
    public const FLASH_IMPORT_ERROR = 'import_error';

    public const PRICE_MODE_AUTOMATIC = 'automatic';
    public const PRICE_MODE_MANUAL = 'manual';
    public const PRICE_MODE_COLUMN_ALIAS = 'column';

    public const PRICE_MODE_VALIDATION_LIST = 'automatic,manual,column';
    public const INCLUDE_COLS_MIN = 1;
    public const INCLUDE_COLS_MAX = 15;

    // Import/source messages
    public const MSG_ZIP_REQUIRED = 'XLSX import requires the PHP Zip extension. Enable ZipArchive or upload a CSV file.';
    public const MSG_ALLOWED_UPLOAD_TYPES = 'Only .xls, .xlsx, or .csv files are allowed.';
    public const MSG_NO_FILE_ROWS = 'No data rows found in file.';
    public const MSG_INVALID_GOOGLE_URL = 'Invalid Google Sheet URL. Use a standard docs.google.com/spreadsheets URL.';
    public const MSG_GOOGLE_FETCH_FAILED = 'Unable to fetch Google Sheet. Ensure sharing is set to "Anyone with the link can view".';
    public const MSG_NO_GOOGLE_ROWS = 'No data rows found in Google Sheet.';
    public const MSG_NO_USABLE_ROWS = 'No usable rows found in the provided source.';
    public const MSG_UPLOAD_FILE_REQUIRED = 'Please choose an Excel file (.xls, .xlsx, or .csv) before continuing.';
    public const MSG_UPLOAD_CSV_REQUIRED = 'Please choose a CSV file before continuing.';
    public const MSG_GOOGLE_URL_REQUIRED = 'Please paste a Google Sheets URL before continuing.';
    public const MSG_GOOGLE_URL_FORMAT = 'Please provide a valid Google Sheets URL (https://docs.google.com/spreadsheets/...).';
    public const MSG_GOOGLE_URL_HTTPS = 'Google Sheets URL must start with https://.';

    // Flow/session messages
    public const MSG_NO_IMPORT_DATA = 'No import data found. Please upload or link data first.';
    public const MSG_IMPORT_EXPIRED = 'Import data expired. Please try again.';
    public const MSG_IMPORT_SESSION_EXPIRED = 'Import session expired. Please start again.';
    public const MSG_INVALID_PRICE_COLUMN = 'Please select a valid price column.';
    public const MSG_RESELECT_PRICING_COLUMN = 'Please re-select a valid pricing column.';
    public const MSG_NO_VALID_PRICED_ROWS = 'No valid priced rows found in selected column.';
    public const MSG_INCLUDE_COLS_MIN = 'Select at least one column to include.';
    public const MSG_INCLUDE_COLS_MAX = 'Select at most 15 columns.';
    public const MSG_SELECT_AT_LEAST_ONE_ROW = 'Select at least one row to include in the invoice.';

    /**
     * Normalize incoming pricing modes to canonical values.
     */
    public static function normalizePriceMode(?string $mode): ?string
    {
        $mode = strtolower(trim((string) $mode));

        return match ($mode) {
            self::PRICE_MODE_AUTOMATIC, self::PRICE_MODE_COLUMN_ALIAS => self::PRICE_MODE_AUTOMATIC,
            self::PRICE_MODE_MANUAL => self::PRICE_MODE_MANUAL,
            default => null,
        };
    }

    /**
     * Frontend-safe message payload for Blade/JS.
     */
    public static function uiMessages(): array
    {
        return [
            'allowed_upload_types' => self::MSG_ALLOWED_UPLOAD_TYPES,
            'upload_file_required' => self::MSG_UPLOAD_FILE_REQUIRED,
            'upload_csv_required' => self::MSG_UPLOAD_CSV_REQUIRED,
            'google_url_required' => self::MSG_GOOGLE_URL_REQUIRED,
            'google_url_format' => self::MSG_GOOGLE_URL_FORMAT,
            'google_url_https' => self::MSG_GOOGLE_URL_HTTPS,
            'include_cols_min' => self::MSG_INCLUDE_COLS_MIN,
            'include_cols_max' => self::MSG_INCLUDE_COLS_MAX,
            'select_at_least_one_row' => self::MSG_SELECT_AT_LEAST_ONE_ROW,
        ];
    }
}
