$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)
$localPhp = Join-Path (Get-Location) '.runtime\php\php.exe'
if (!(Test-Path -LiteralPath $localPhp)) { $localPhp = (Get-Command php -ErrorAction Stop).Source }
Write-Host 'SIM Inventory: http://localhost:8000 (Ctrl+C untuk berhenti)'
& $localPhp -d memory_limit=512M -S localhost:8000 -t public router.php
