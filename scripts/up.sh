#!/usr/bin/env bash
# Démarre la stack Docker (réseaux externes + up -d).
# Usage : ./scripts/up.sh [fichier-compose] [args passés à docker compose up…]
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=scripts/lib/brightshell-common.sh
source "$ROOT/scripts/lib/brightshell-common.sh"
cd "$BRIGHTSHELL_ROOT"

COMPOSE_FILE="${BRIGHTSHELL_COMPOSE_FILE:-docker-compose.yml}"
if [[ -n "${1:-}" ]] && [[ -f "$1" ]]; then
  COMPOSE_FILE="$1"
  shift
fi
EXTRA=("$@")

brightshell_ensure_networks_for_compose "$COMPOSE_FILE"
exec docker compose -f "$COMPOSE_FILE" up -d "${EXTRA[@]}"
