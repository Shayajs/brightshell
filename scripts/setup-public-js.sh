#!/usr/bin/env bash
# Expose le dossier racine js/ en /js/* (nécessaire pour public/real/clipped.html, intégrations, etc.)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT/public"
rm -rf js 2>/dev/null || true
ln -sfn ../js js
echo "OK: $ROOT/public/js -> ../js"
