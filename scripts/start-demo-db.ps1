$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)
$localData = Join-Path (Get-Location) '.runtime\mysql-data'
if (!(Test-Path -LiteralPath $localData)) { throw 'Database lokal belum tersedia. Ikuti README untuk membuat database MySQL.' }
if (Get-NetTCPConnection -LocalPort 3307 -State Listen -ErrorAction SilentlyContinue) { Write-Host 'Port database 3307 sudah aktif.'; exit }
$databaseExecutable = 'D:\XAMPP\mysql\bin\mysqld.exe'
if (!(Test-Path -LiteralPath $databaseExecutable)) { throw 'Sesuaikan lokasi mysqld.exe dengan instalasi MariaDB Anda.' }
Start-Process -FilePath $databaseExecutable -ArgumentList @('--no-defaults', ('--datadir="' + $localData + '"'), '--port=3307', '--bind-address=127.0.0.1', '--console') -WindowStyle Hidden -RedirectStandardOutput .runtime\mysql.out.log -RedirectStandardError .runtime\mysql.err.log
Write-Host 'Database demo dijalankan pada 127.0.0.1:3307.'
