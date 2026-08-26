#!/usr/bin/env bash
set -euo pipefail
command -v ddev >/dev/null || { echo "DDEV manquant" >&2; exit 1; }
ddev version
ddev wp cli version
ddev wp core version
ddev wp plugin list
ddev wp theme list
ddev wp doctor check --all 2>/dev/null || echo "WP-CLI Doctor non installé : installer le package avant le gate staging"
