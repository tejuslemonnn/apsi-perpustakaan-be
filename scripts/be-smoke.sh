#!/usr/bin/env bash
# E2E smoke test for Perpustakaan API
# Usage: bash scripts/be-smoke.sh
# Requires: curl, jq (for JSON parsing)

set -e

BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
COOKIE_JAR="$(mktemp)"
trap "rm -f $COOKIE_JAR" EXIT

echo "=== Perpustakaan API E2E smoke test ==="
echo "Base URL: $BASE_URL"
echo

# 1. Get CSRF cookie
echo "1. GET /sanctum/csrf-cookie (get XSRF-TOKEN)..."
curl -sS -c "$COOKIE_JAR" -b "$COOKIE_JAR" "$BASE_URL/sanctum/csrf-cookie" -o /dev/null
XSRF_TOKEN=$(grep XSRF-TOKEN "$COOKIE_JAR" | awk '{print $7}')
echo "   XSRF-TOKEN: ${XSRF_TOKEN:0:20}..."

# 2. Login as admin
echo "2. POST /api/login (admin1/admin123)..."
LOGIN_RESPONSE=$(curl -sS -c "$COOKIE_JAR" -b "$COOKIE_JAR" \
  -X POST "$BASE_URL/api/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  -d '{"username":"admin1","password":"admin123"}')
echo "   Response: $LOGIN_RESPONSE"

# Verify login succeeded
if echo "$LOGIN_RESPONSE" | grep -q '"username":"admin1"'; then
  echo "   ✓ Login OK"
else
  echo "   ✗ Login FAILED"
  exit 1
fi

# 3. Get dashboard
echo "3. GET /api/admin/dashboard..."
DASHBOARD=$(curl -sS -c "$COOKIE_JAR" -b "$COOKIE_JAR" \
  -X GET "$BASE_URL/api/admin/dashboard" \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN")
echo "   Response: $DASHBOARD"
if echo "$DASHBOARD" | grep -q '"totalBuku"'; then
  echo "   ✓ Dashboard OK"
else
  echo "   ✗ Dashboard FAILED"
  exit 1
fi

# 4. Logout
echo "4. POST /api/logout..."
LOGOUT=$(curl -sS -c "$COOKIE_JAR" -b "$COOKIE_JAR" \
  -X POST "$BASE_URL/api/logout" \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN")
echo "   Response: $LOGOUT"
if echo "$LOGOUT" | grep -q '"Logout berhasil"'; then
  echo "   ✓ Logout OK"
else
  echo "   ✗ Logout FAILED"
  exit 1
fi

# 5. Verify /api/me returns 401 after logout
echo "5. GET /api/me (after logout, expect 401)..."
ME_STATUS=$(curl -sS -c "$COOKIE_JAR" -b "$COOKIE_JAR" \
  -X GET "$BASE_URL/api/me" \
  -H "Accept: application/json" \
  -H "X-XSRF-TOKEN: $XSRF_TOKEN" \
  -o /dev/null -w "%{http_code}")
echo "   Status: $ME_STATUS"
if [ "$ME_STATUS" = "401" ]; then
  echo "   ✓ /api/me correctly returns 401 after logout"
else
  echo "   ✗ /api/me returned $ME_STATUS, expected 401"
  exit 1
fi

echo
echo "=== All smoke tests passed ==="
