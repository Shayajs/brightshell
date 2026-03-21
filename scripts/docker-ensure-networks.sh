#!/usr/bin/env bash
# Crée les réseaux Docker externes attendus par docker-compose.yml ou le compose dev perso.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=scripts/lib/brightshell-common.sh
source "$ROOT/scripts/lib/brightshell-common.sh"

COMPOSE_FILE="${1:-${BRIGHTSHELL_COMPOSE_FILE:-docker-compose.yml}}"
brightshell_ensure_networks_for_compose "$COMPOSE_FILE"
echo "Réseaux OK pour : $COMPOSE_FILE"
