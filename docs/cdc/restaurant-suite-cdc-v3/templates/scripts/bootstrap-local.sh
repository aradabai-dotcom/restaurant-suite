#!/usr/bin/env bash
set -euo pipefail
command -v ddev >/dev/null || { echo "DDEV manquant" >&2; exit 1; }
ddev start
ddev wp core is-installed || ddev wp core install --url="https://restaurant-suite.ddev.site" --title="Restaurant Suite Test" --admin_user=admin --admin_password="admin-local-only" --admin_email=admin@example.test
ddev wp plugin is-installed woocommerce || ddev wp plugin install woocommerce --activate
ddev wp plugin activate restaurant-suite-core 2>/dev/null || true
ddev wp theme activate restaurant-base-theme 2>/dev/null || true
./templates/scripts/seed-fixtures.sh
