#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
[ -f .ddev/config.yaml ] || { echo ".ddev/config.yaml manquant" >&2; exit 1; }
export WP_ADMIN_PASSWORD="${WP_ADMIN_PASSWORD:-ci-only-password}"
export WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
export WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@example.test}"
./scripts/bootstrap-local.sh
