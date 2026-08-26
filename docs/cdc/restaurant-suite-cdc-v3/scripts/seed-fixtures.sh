#!/usr/bin/env bash
set -euo pipefail
command -v ddev >/dev/null || { echo "DDEV manquant" >&2; exit 1; }
# Remplacer ce template par la création idempotente des produits et comptes synthétiques.
ddev wp option update blogdescription "Restaurant Suite test" >/dev/null
echo "Fixtures à implémenter : produits simples, variable, hors stock, catégories et rôles."
