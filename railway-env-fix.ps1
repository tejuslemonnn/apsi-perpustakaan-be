# railway-env-fix.ps1 v2
# SameSite=none + Secure=true untuk cross-origin XHR

$ErrorActionPreference = 'Stop'
$SERVICE = 'apsi-perpustakaan-be'

function Set-Var($key, $val) {
  Write-Host "  $key = '$val'" -ForegroundColor Gray
  railway variables set --service $SERVICE "$key=$val" 2>&1 | Out-Null
}

Write-Host "Removing SESSION_DOMAIN..." -ForegroundColor Cyan
$null = railway variables unset --service $SERVICE SESSION_DOMAIN 2>&1
if ($LASTEXITCODE -ne 0) {
  Set-Var 'SESSION_DOMAIN' ''
}

Set-Var 'APP_DEBUG' 'false'

Write-Host "Setting env vars..." -ForegroundColor Cyan
Set-Var 'APP_ENV'                  'production'
Set-Var 'APP_URL'                  'https://apsi-perpustakaan-be-production.up.railway.app'
Set-Var 'LOG_CHANNEL'              'stderr'
Set-Var 'LOG_LEVEL'                'info'
Set-Var 'SESSION_SECURE_COOKIE'    'true'
Set-Var 'SESSION_SAME_SITE'        'none'
Set-Var 'SESSION_DRIVER'           'database'
Set-Var 'SESSION_LIFETIME'         '120'
Set-Var 'SANCTUM_STATEFUL_DOMAINS' 'apsi-perpustakaan-fe-production.up.railway.app,localhost:5173,127.0.0.1:5173'
Set-Var 'CACHE_STORE'              'database'
Set-Var 'QUEUE_CONNECTION'         'database'
Set-Var 'DB_CONNECTION'            'mysql'
Set-Var 'FRONTEND_URL'             'https://apsi-perpustakaan-fe-production.up.railway.app'
Set-Var 'BROADCAST_CONNECTION'     'log'
Set-Var 'FILESYSTEM_DISK'          'local'

Write-Host "Triggering redeploy..." -ForegroundColor Cyan
railway redeploy --service $SERVICE 2>&1 | Select-Object -Last 5

Write-Host "Waiting 25s..." -ForegroundColor Cyan
Start-Sleep -Seconds 25

Write-Host "`n=== Verifikasi ===" -ForegroundColor Cyan
Write-Host "SESSION_DOMAIN (harus tidak ada):" -ForegroundColor Yellow
railway variables get SESSION_DOMAIN --service $SERVICE 2>&1
Write-Host "SESSION_SAME_SITE (harus 'none'):" -ForegroundColor Yellow
railway variables get SESSION_SAME_SITE --service $SERVICE 2>&1
Write-Host "SESSION_SECURE_COOKIE (harus 'true'):" -ForegroundColor Yellow
railway variables get SESSION_SECURE_COOKIE --service $SERVICE 2>&1
Write-Host "SANCTUM_STATEFUL_DOMAINS:" -ForegroundColor Yellow
railway variables get SANCTUM_STATEFUL_DOMAINS --service $SERVICE 2>&1

Write-Host "`nLogs terbaru:" -ForegroundColor Cyan
railway logs --service $SERVICE --lines 20 2>&1 | Select-Object -Last 25
