#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY must be set in the hosting provider environment." >&2
    exit 1
fi

php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
