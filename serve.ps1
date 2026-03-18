$php84 = "C:\Users\mrsha\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

if (-not (Test-Path $php84)) {
    Write-Host "PHP 8.4 not found at: $php84"
    Write-Host "Please install PHP 8.4 or update this path, then re-run."
    exit 1
}

& $php84 artisan serve --host=127.0.0.1 --port=8080
