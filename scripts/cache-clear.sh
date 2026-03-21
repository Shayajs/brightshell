#!/usr/bin/env bash
# Vide les caches Laravel (optimize:clear).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=scripts/lib/brightshell-common.sh
source "$ROOT/scripts/lib/brightshell-common.sh"
cd "$BRIGHTSHELL_ROOT"
brightshell_artisan optimize:clear "$@"
