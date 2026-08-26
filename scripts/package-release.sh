#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
[ -f plugin/restaurant-suite-core/restaurant-suite-core.php ] || { echo "Plugin absent" >&2; exit 1; }
[ -f theme/restaurant-base-theme/style.css ] || { echo "Thème absent" >&2; exit 1; }
VERSION="$(sed -n 's/^ *\* Version: *//p' plugin/restaurant-suite-core/restaurant-suite-core.php | head -n1)"
[ -n "$VERSION" ] || { echo "Version plugin absente" >&2; exit 1; }
rm -rf dist && mkdir -p dist
zip -qr "dist/restaurant-suite-core-${VERSION}.zip" plugin/restaurant-suite-core -x '*/tests/*' '*/assets/src/*' '*/node_modules/*'
zip -qr "dist/restaurant-base-theme-${VERSION}.zip" theme/restaurant-base-theme -x '*/tests/*' '*/assets/src/*' '*/node_modules/*'
sha256sum dist/*.zip > dist/checksums.txt
printf '{"version":"%s","plugin":"restaurant-suite-core-%s.zip","theme":"restaurant-base-theme-%s.zip"}
' "$VERSION" "$VERSION" "$VERSION" > dist/manifest.json
[ -s dist/checksums.txt ] && [ -s dist/manifest.json ]
