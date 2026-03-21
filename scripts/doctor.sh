#!/usr/bin/env bash
# Vérifications rapides : Docker, compose, migrate:status, about, Node recommandé.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=scripts/lib/brightshell-common.sh
source "$ROOT/scripts/lib/brightshell-common.sh"
cd "$BRIGHTSHELL_ROOT"

echo "=== Brightshell doctor ==="
echo "Racine : $BRIGHTSHELL_ROOT"
echo ""

if command -v docker >/dev/null 2>&1; then
  echo "[Docker] $(docker --version)"
else
  echo "[Docker] non installé ou absent du PATH"
fi

local_cf=""
local_cf="$(brightshell_compose_file_for_ops || true)"
if [[ -n "$local_cf" ]]; then
  echo "[Compose actif] $local_cf"
  docker compose -f "$local_cf" ps
  echo ""
  echo "--- migrate:status ---"
  brightshell_artisan migrate:status || true
  echo ""
  echo "--- php artisan about (extrait) ---"
  brightshell_artisan about --only=environment 2>/dev/null || brightshell_artisan about
else
  echo "[Compose] aucune stack détectée (docker-compose-shaya.dev.yaml ou docker-compose.yml avec PHP up)."
  if [[ -f docker-compose.yml ]]; then
    echo "--- docker compose ps (docker-compose.yml) ---"
    docker compose -f docker-compose.yml ps 2>/dev/null || true
  fi
  echo ""
  if command -v php >/dev/null 2>&1; then
    echo "--- migrate:status (PHP hôte) ---"
    php artisan migrate:status 2>/dev/null || echo "(échec — lancer la stack Docker ou composer install)"
  else
    echo "[PHP hôte] absent du PATH"
  fi
fi

echo ""
if command -v node >/dev/null 2>&1; then
  echo "[Node] $(node --version)"
else
  echo "[Node] absent du PATH"
fi
if [[ -f .nvmrc ]]; then
  echo "[.nvmrc] $(cat .nvmrc)"
fi
if [[ -f package.json ]]; then
  echo "[package.json engines.node] $(node -p "try{require('./package.json').engines.node}catch(e){'?'}" 2>/dev/null || echo '?')"
fi

echo ""
echo "Variables utiles : BRIGHTSHELL_COMPOSE_FILE, BRIGHTSHELL_PHP_SERVICE, BRIGHTSHELL_DB_SERVICE"
