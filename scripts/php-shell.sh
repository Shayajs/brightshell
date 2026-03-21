#!/usr/bin/env bash
# Shell interactif dans le conteneur PHP (sh Alpine).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=scripts/lib/brightshell-common.sh
source "$ROOT/scripts/lib/brightshell-common.sh"
cd "$BRIGHTSHELL_ROOT"

CF="$(brightshell_compose_file_for_ops)"
if [[ -z "$CF" ]]; then
  echo "Aucune stack PHP Docker détectée. Démarre avec docker compose ou exporte BRIGHTSHELL_COMPOSE_FILE." >&2
  exit 1
fi
SVC="$(brightshell_php_service_resolved "$CF")"
exec docker compose -f "$CF" exec "$SVC" sh
