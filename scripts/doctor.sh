#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
for cmd in docker ddev git; do command -v "$cmd" >/dev/null || { echo "$cmd manquant" >&2; exit 1; }; done
[ -f .ddev/config.yaml ] || { echo ".ddev/config.yaml manquant" >&2; exit 1; }
docker version >/dev/null
ddev version
ddev wp cli version
ddev wp core version
if [ "${WP_DOCTOR_REQUIRED:-0}" = "1" ]; then
  ddev wp doctor check --all
elif ddev wp doctor check --all >/dev/null 2>&1; then
  echo "WP-CLI Doctor: OK"
else
  echo "WP-CLI Doctor non installé : contrôle non bloquant hors staging/release"
fi
