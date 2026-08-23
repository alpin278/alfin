#!/bin/sh
set -e

echo "==> Preparing DTE VirtualLab Backend Container..."

# 1. Pastikan symlink storage terhubung
echo "==> Creating storage symlink..."
php artisan storage:link --force || true

# 2. Cache konfigurasi, rute, dan tampilan Blade untuk performa optimal
echo "==> Caching configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Jalankan migrasi database otomatis tanpa konfirmasi interaktif
echo "==> Running database migrations..."
php artisan migrate --force

# 4. Jalankan Laravel HTTP Server (Support PORT dari Koyeb atau default 8000)
PORT="${PORT:-8000}"
echo "==> Starting application on 0.0.0.0:${PORT}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT}"