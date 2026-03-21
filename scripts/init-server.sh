#!/usr/bin/env bash
# Première installation : réseaux Docker, .env, build des images, dépendances PHP, migrations, lien public/js.
# Usage : ./scripts/init-server.sh [fichier-compose]
# Ex. dev perso : ./scripts/init-server.sh docker-compose-shaya.dev.yaml
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=scripts/lib/brightshell-common.sh
source "$ROOT/scripts/lib/brightshell-common.sh"

COMPOSE_FILE="${1:-${BRIGHTSHELL_COMPOSE_FILE:-docker-compose.yml}}"
cd "$BRIGHTSHELL_ROOT"

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker est requis." >&2
  exit 1
fi

brightshell_ensure_networks_for_compose "$COMPOSE_FILE"

if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Créé .env depuis .env.example — vérifie APP_URL, DB_*, etc."
fi

echo "Démarrage des conteneurs ($COMPOSE_FILE)…"
docker compose -f "$COMPOSE_FILE" up -d --build

brightshell_wait_db "$COMPOSE_FILE"

PHP_SVC="$(brightshell_php_service_resolved "$COMPOSE_FILE")"
echo "Composer install ($PHP_SVC)…"
docker compose -f "$COMPOSE_FILE" exec -T "$PHP_SVC" composer install --no-interaction --no-progress

if ! grep -qE '^APP_KEY=.+$' .env 2>/dev/null; then
  echo "Génération de APP_KEY…"
  docker compose -f "$COMPOSE_FILE" exec -T "$PHP_SVC" php artisan key:generate --force --ansi
fi

echo "Migrations…"
docker compose -f "$COMPOSE_FILE" exec -T "$PHP_SVC" php artisan migrate --force --ansi

"$ROOT/scripts/setup-public-js.sh"

echo ""
echo "=== Initialisation terminée ==="
echo "Assets front (depuis la racine du projet, hors conteneur PHP) :"
echo "  nvm use   # si .nvmrc"
echo "  npm install && npm run build"
echo "  # ou : npm run build:docker"
echo ""
echo "Premier compte admin (README) :"
echo "  ./scripts/artisan.sh admin:init"
echo "  # ou non-interactif : ./scripts/artisan.sh admin:init --email=... --name='...' --password='...'"
