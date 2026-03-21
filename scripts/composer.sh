#!/usr/bin/env bash
# Proxy vers composer (dans le conteneur PHP si la stack Docker tourne).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=scripts/lib/brightshell-common.sh
source "$ROOT/scripts/lib/brightshell-common.sh"
cd "$BRIGHTSHELL_ROOT"
brightshell_composer "$@"
