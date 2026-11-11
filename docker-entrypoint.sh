#!/bin/sh
set -eu
if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY belum diatur. Buat nilainya dengan php artisan key:generate --show." >&2
  exit 1
fi
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
  db_file="${DB_DATABASE:-/app/database/database.sqlite}"
  mkdir -p "$(dirname "$db_file")"
  touch "$db_file"
fi
if [ "${DB_CONNECTION:-sqlite}" = "pgsql" ] && [ -z "${DB_URL:-${DATABASE_URL:-}}" ]; then
  echo "PostgreSQL memerlukan DB_URL atau DATABASE_URL." >&2
  exit 1
fi
php artisan migrate --force
php artisan app:create-admin --from-env
php artisan view:cache
exec frankenphp run --config /etc/caddy/Caddyfile
