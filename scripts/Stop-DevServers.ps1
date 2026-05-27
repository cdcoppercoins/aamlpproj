# Stops leftover php artisan serve processes (they lock project folders on Windows).
Stop-Process -Name php -Force -ErrorAction SilentlyContinue
Write-Host "Stopped PHP processes. You can delete or rename public\history if needed."
Write-Host "Start the site again with: php artisan serve"
