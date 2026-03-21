#!/usr/bin/env bash
# Génère public/build/manifest.json (Vite) — obligatoire en prod si @vite() est utilisé.
# Usage : ./scripts/build-assets.sh           → npm ci + build (Node ≥ 20 recommandé, voir .nvmrc)
#         ./scripts/build-assets.sh --docker   → build dans un conteneur Node (pas besoin de Node local)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ "${1:-}" == "--docker" ]]; then
  npm run build:docker
else
  npm ci
  npm run build
fi

if [[ ! -f public/build/manifest.json ]]; then
  echo "Échec : public/build/manifest.json manquant après le build." >&2
  exit 1
fi
echo "OK : public/build/manifest.json"
