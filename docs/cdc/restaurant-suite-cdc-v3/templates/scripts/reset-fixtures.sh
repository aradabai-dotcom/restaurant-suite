#!/usr/bin/env bash
set -euo pipefail
command -v ddev >/dev/null || { echo "DDEV manquant" >&2; exit 1; }
# Le reset doit restaurer un état connu sans supprimer le code ni les commandes de production.
./templates/scripts/seed-fixtures.sh
