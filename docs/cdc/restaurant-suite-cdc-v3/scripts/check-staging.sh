#!/usr/bin/env bash
set -euo pipefail
: "${BASE_URL:?BASE_URL est obligatoire}"
curl --fail --silent --show-error --location --max-time 20 "$BASE_URL" >/dev/null
printf 'Staging accessible: %s\n' "$BASE_URL"
