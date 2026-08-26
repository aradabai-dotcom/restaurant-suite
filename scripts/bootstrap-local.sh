#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
command -v docker >/dev/null || { echo "Docker manquant" >&2; exit 1; }
command -v ddev >/dev/null || { echo "DDEV manquant" >&2; exit 1; }
[ -f .ddev/config.yaml ] || { echo ".ddev/config.yaml manquant" >&2; exit 1; }
ddev start
if ! ddev wp core is-installed >/dev/null 2>&1; then
  ddev wp core download
  ddev wp core install --url="${DDEV_PRIMARY_URL:-https://restaurant-suite.ddev.site}" --title="Restaurant Suite Test" --admin_user="${WP_ADMIN_USER:-admin}" --admin_password="${WP_ADMIN_PASSWORD:?WP_ADMIN_PASSWORD est obligatoire}" --admin_email="${WP_ADMIN_EMAIL:-admin@example.test}"
fi
ddev wp plugin is-installed woocommerce || ddev wp plugin install woocommerce --activate --version="${WOOCOMMERCE_VERSION:-}"
ddev wp plugin activate restaurant-suite-core
ddev wp theme activate restaurant-base-theme
ddev wp rewrite structure '/%postname%/'
ddev wp rewrite flush
./scripts/seed-fixtures.sh
