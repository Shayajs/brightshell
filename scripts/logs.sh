#!/usr/bin/env bash
# docker compose logs -f (compose détecté comme artisan, ou fichier passé en $1).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=scripts/lib/brightshell-common.sh
source "$ROOT/scripts/lib/brightshell-common.sh"
cd "$BRIGHTSHELL_ROOT"

if [[ -n "${1:-}" ]] && [[ -f "$1" ]]; then
  CF="$1"
  shift
elif [[ -n "${BRIGHTSHELL_COMPOSE_FILE:-}" ]]; then
  CF="$BRIGHTSHELL_COMPOSE_FILE"
else
  CF="$(brightshell_compose_file_for_ops || true)"
  [[ -n "$CF" ]] || CF="docker-compose.yml"
fi

exec docker compose -f "$CF" logs -f "$@"
