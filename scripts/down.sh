#!/usr/bin/env bash
# Arrête la stack Docker.
# Usage : ./scripts/down.sh [fichier-compose] [args passés à docker compose down…]
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

exec docker compose -f "$COMPOSE_FILE" down "$@"
