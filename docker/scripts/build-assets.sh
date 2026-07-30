#!/bin/sh
set -e

cd /var/www/html

manifest="public/build/manifest.json"

needs_build=0
if [ ! -f "$manifest" ]; then
  needs_build=1
elif [ package-lock.json -nt "$manifest" ] || [ package.json -nt "$manifest" ]; then
  needs_build=1
fi

if [ "$needs_build" -eq 0 ]; then
  echo "Frontend assets up to date, skipping build."
  exit 0
fi

echo "Building frontend assets..."
npm ci --prefer-offline --no-audit --no-fund
npm run build
