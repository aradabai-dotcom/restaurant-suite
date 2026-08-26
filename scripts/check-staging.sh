#!/usr/bin/env bash
set -euo pipefail
: "${BASE_URL:?BASE_URL obligatoire}"
curl --fail --silent --show-error --location --max-time 20 -D /tmp/crs-headers "$BASE_URL" -o /tmp/crs-home.html
grep -qi '^strict-transport-security:' /tmp/crs-headers || echo "HSTS non détecté : classer comme observation"
grep -qi '<main\|<header\|<nav' /tmp/crs-home.html || { echo "HTML public inattendu" >&2; exit 1; }
printf 'Staging accessible: %s
' "$BASE_URL"
