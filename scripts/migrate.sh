#!/usr/bin/env bash
# Migrations Laravel (passe les arguments à artisan migrate).
# Détecte automatiquement le conteneur PHP si la stack Docker tourne.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=scripts/lib/brightshell-common.sh
source "$ROOT/scripts/lib/brightshell-common.sh"
cd "$BRIGHTSHELL_ROOT"
brightshell_artisan migrate "$@"
