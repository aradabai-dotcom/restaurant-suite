#!/usr/bin/env bash
set -euo pipefail
TARGET="${1:?répertoire d’artefacts obligatoire}"
find "$TARGET" -type f -print0 | xargs -0 sed -Ei 's/(password|token|secret|authorization|cookie)[^=]*=[^[:space:]]+/=[REDACTED]/Ig' 2>/dev/null || true
