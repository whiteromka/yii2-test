#!/bin/sh

echo "Fixing permissions..."

mkdir -p runtime web/assets

chown -R appuser:appuser runtime web/assets
chmod -R 775 runtime web/assets

exec "$@"
