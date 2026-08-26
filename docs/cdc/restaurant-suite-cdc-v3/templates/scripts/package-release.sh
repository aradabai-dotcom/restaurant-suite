#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
DIST="$ROOT/dist"
rm -rf "$DIST" && mkdir -p "$DIST"
# Le projet réel doit remplacer ces chemins par les dossiers plugin/thème construits.
[ -d "$ROOT/plugin/restaurant-suite-core" ] && zip -qr "$DIST/restaurant-suite-core.zip" "$ROOT/plugin/restaurant-suite-core" -x '*/node_modules/*' '*/tests/*'
[ -d "$ROOT/theme/restaurant-base-theme" ] && zip -qr "$DIST/restaurant-base-theme.zip" "$ROOT/theme/restaurant-base-theme" -x '*/node_modules/*' '*/tests/*'
sha256sum "$DIST"/*.zip 2>/dev/null > "$DIST/checksums.txt" || true
