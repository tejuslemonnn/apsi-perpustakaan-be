# E2E smoke test for Perpustakaan API (Windows PowerShell version)
# Usage: pwsh scripts/be-smoke.ps1
# Requires: PowerShell 7+, the server already running on $BaseUrl

$ErrorActionPreference = 'Stop'

$BaseUrl = if ($env:BASE_URL) { $env:BASE_URL } else { 'http://127.0.0.1:8000' }
$CookieJar = New-TemporaryFile

Write-Host "=== Perpustakaan API E2E smoke test ===" -ForegroundColor Cyan
Write-Host "Base URL: $BaseUrl"
Write-Host ""

try {
    # 1. Get CSRF cookie
    Write-Host "1. GET /sanctum/csrf-cookie..."
    Invoke-WebRequest -Uri "$BaseUrl/sanctum/csrf-cookie" -SessionVariable $null -UseBasicParsing | Out-Null
    # PowerShell cookie handling is more complex; use a simpler approach
    $session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    Invoke-WebRequest -Uri "$BaseUrl/sanctum/csrf-cookie" -WebSession $session -UseBasicParsing | Out-Null
    $xsrfToken = ($session.Cookies.GetCookies($BaseUrl) | Where-Object { $_.Name -eq 'XSRF-TOKEN' }).Value
    Write-Host "   XSRF-TOKEN: $($xsrfToken.Substring(0, [Math]::Min(20, $xsrfToken.Length)))..."

    # 2. Login
    Write-Host "2. POST /api/login (admin1/admin123)..."
    $loginBody = @{ username = 'admin1'; password = 'admin123' } | ConvertTo-Json
    $loginResponse = Invoke-WebRequest -Uri "$BaseUrl/api/login" -Method POST -WebSession $session -Headers @{
        'Accept' = 'application/json'
        'Content-Type' = 'application/json'
        'X-XSRF-TOKEN' = $xsrfToken
    } -Body $loginBody -UseBasicParsing
    Write-Host "   Status: $($loginResponse.StatusCode)"
    Write-Host "   Body: $($loginResponse.Content)"

    if ($loginResponse.Content -match '"username":"admin1"') {
        Write-Host "   ✓ Login OK" -ForegroundColor Green
    } else {
        Write-Host "   ✗ Login FAILED" -ForegroundColor Red
        exit 1
    }

    # 3. Dashboard
    Write-Host "3. GET /api/admin/dashboard..."
    $dashboardResponse = Invoke-WebRequest -Uri "$BaseUrl/api/admin/dashboard" -Method GET -WebSession $session -Headers @{
        'Accept' = 'application/json'
        'X-XSRF-TOKEN' = $xsrfToken
    } -UseBasicParsing
    Write-Host "   Status: $($dashboardResponse.StatusCode)"
    if ($dashboardResponse.Content -match '"totalBuku"') {
        Write-Host "   ✓ Dashboard OK" -ForegroundColor Green
    } else {
        Write-Host "   ✗ Dashboard FAILED" -ForegroundColor Red
        exit 1
    }

    # 4. Logout
    Write-Host "4. POST /api/logout..."
    $logoutResponse = Invoke-WebRequest -Uri "$BaseUrl/api/logout" -Method POST -WebSession $session -Headers @{
        'Accept' = 'application/json'
        'X-XSRF-TOKEN' = $xsrfToken
    } -UseBasicParsing
    Write-Host "   Status: $($logoutResponse.StatusCode)"
    if ($logoutResponse.Content -match 'Logout berhasil') {
        Write-Host "   ✓ Logout OK" -ForegroundColor Green
    } else {
        Write-Host "   ✗ Logout FAILED" -ForegroundColor Red
        exit 1
    }

    # 5. Verify /api/me returns 401 after logout
    Write-Host "5. GET /api/me (after logout, expect 401)..."
    try {
        $meResponse = Invoke-WebRequest -Uri "$BaseUrl/api/me" -Method GET -WebSession $session -Headers @{
            'Accept' = 'application/json'
            'X-XSRF-TOKEN' = $xsrfToken
        } -UseBasicParsing
        Write-Host "   Status: $($meResponse.StatusCode)"
        if ($meResponse.StatusCode -eq 401) {
            Write-Host "   ✓ /api/me correctly returns 401" -ForegroundColor Green
        } else {
            Write-Host "   ✗ Expected 401, got $($meResponse.StatusCode)" -ForegroundColor Red
            exit 1
        }
    } catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        if ($statusCode -eq 401) {
            Write-Host "   ✓ /api/me correctly returns 401" -ForegroundColor Green
        } else {
            throw $_
        }
    }

    Write-Host ""
    Write-Host "=== All smoke tests passed ===" -ForegroundColor Green
} finally {
    Remove-Item $CookieJar -ErrorAction SilentlyContinue
}
