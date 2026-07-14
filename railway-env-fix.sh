#!/usr/bin/env bash
# railway-env-fix.sh v2
# SameSite=none + Secure=true untuk cross-origin XHR
# Jalankan: railway link --project <id>; ./railway-env-fix.sh

set -euo pipefail

SERVICE="${SERVICE:-apsi-perpustakaan-be}"

set_var() {
  local key="$1"
  local val="$2"
  printf "  %s = '%s'\n" "$key" "$val"
  railway variables set --service "$SERVICE" "$key=$val" >/dev/null
}

# Hapus SESSION_DOMAIN (harus kosong, bukan "localhost")
echo "Removing SESSION_DOMAIN..." >&2
railway variables unset --service "$SERVICE" SESSION_DOMAIN 2>/dev/null || \
  railway variables set --service "$SERVICE" "SESSION_DOMAIN=''" >/dev/null

# Paksa APP_DEBUG=false (yang lama true, harus override)
echo "Forcing APP_DEBUG=false..." >&2
set_var APP_DEBUG "false"

echo "Setting env vars..." >&2
set_var APP_ENV                  "production"
set_var APP_URL                  "https://apsi-perpustakaan-be-production.up.railway.app"
set_var LOG_CHANNEL              "stderr"
set_var LOG_LEVEL                "info"
set_var SESSION_SECURE_COOKIE    "true"
set_var SESSION_SAME_SITE        "none"
set_var SESSION_DRIVER           "database"
set_var SESSION_LIFETIME         "120"
set_var SANCTUM_STATEFUL_DOMAINS "apsi-perpustakaan-be-production.up.railway.app,localhost:5173,127.0.0.1:5173"
set_var CACHE_STORE              "database"
set_var QUEUE_CONNECTION         "database"
set_var DB_CONNECTION            "mysql"
set_var FRONTEND_URL             "https://apsi-perpustakaan-fe-production.up.railway.app"
set_var BROADCAST_CONNECTION     "log"
set_var FILESYSTEM_DISK          "local"

# Trigger redeploy
echo "Triggering redeploy..." >&2
railway redeploy --service "$SERVICE" 2>&1 | tail -5

# Tunggu restart
echo "Waiting 25s for restart..." >&2
sleep 25

# Verifikasi
echo "" >&2
echo "=== Verifikasi runtime ===" >&2
echo "Cek SESSION_DOMAIN (harus TIDAK ADA):" >&2
railway variables get SESSION_DOMAIN --service "$SERVICE" 2>&1 || echo "  (tidak ada - benar)" >&2
echo "" >&2
echo "Cek SESSION_SAME_SITE (harus 'none'):" >&2
railway variables get SESSION_SAME_SITE --service "$SERVICE" 2>&1
echo "" >&2
echo "Cek SESSION_SECURE_COOKIE (harus 'true'):" >&2
railway variables get SESSION_SECURE_COOKIE --service "$SERVICE" 2>&1
echo "" >&2
echo "Cek SANCTUM_STATEFUL_DOMAINS:" >&2
railway variables get SANCTUM_STATEFUL_DOMAINS --service "$SERVICE" 2>&1
echo "" >&2
echo "Logs terbaru:" >&2
railway logs --service "$SERVICE" --lines 20 2>&1 | tail -25
