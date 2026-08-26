#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
[ "${CRS_ALLOW_FIXTURE_RESET:-0}" = "1" ] || { echo "Reset bloqué : définir CRS_ALLOW_FIXTURE_RESET=1 sur local/staging de test" >&2; exit 1; }
ddev wp eval-file tests/fixtures/reset.php
ddev wp eval-file tests/fixtures/seed.php
