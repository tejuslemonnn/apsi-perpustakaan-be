#!/usr/bin/env bash
# railway-migrate.sh
# Jalankan migration + cache + storage:link di Railway production.
# Prasyarat: railway link --project <id>; railway login sudah.

set -euo pipefail

SERVICE="${SERVICE:-apsi-perpustakaan-be}"

run_in_service() {
  echo ">>> railway run --service $SERVICE $*" >&2
  railway run --service "$SERVICE" -- "$@"
}

echo "=== 1. Cek apakah BE bisa connect ke MySQL ===" >&2
run_in_service php artisan db:show --no-ansi 2>&1 | head -20

echo "" >&2
echo "=== 2. Jalankan migration ===" >&2
run_in_service php artisan migrate --force --no-ansi 2>&1 | tail -30

echo "" >&2
echo "=== 3. (opsional) Seed jika tabel users/anggota/buku kosong ===" >&2
# Hapus baris di bawah jika tidak mau seed
# run_in_service php artisan db:seed --force --no-ansi 2>&1 | tail -20

echo "" >&2
echo "=== 4. Cache config + route (untuk performance) ===" >&2
run_in_service php artisan config:cache --no-ansi 2>&1 | tail -10
run_in_service php artisan route:cache --no-ansi 2>&1 | tail -10

echo "" >&2
echo "=== 5. Test /up langsung ===" >&2
run_in_service curl -s -o /dev/null -w "HTTP %{http_code} in %{time_total}s\n" https://apsi-perpustakaan-be-production.up.railway.app/up 2>&1

echo "" >&2
echo "Done. /up sekarang harus return 200, dan healthcheck Railway akan pass." >&2
