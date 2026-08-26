# Gate phase 0.0

## Gate précédent : matérialisation du dépôt

Le dépôt doit contenir l’arborescence canonique, `.ddev/config.yaml`, les scripts sous `scripts/`, les fixtures, les manifests, les lockfiles, le Makefile et les workflows CI. Les commandes `make install`, `make doctor`, `make reset`, `make validate` et `make package` doivent être démontrées sur un environnement propre.

## Gate métier 0.0

Après matérialisation, les contrats de données, statuts, idempotence, permissions, événements, hooks, compatibilité et tests doivent être relus et approuvés. Aucun module public V0.1 ne commence avant cette approbation.

## Preuves

Le rapport doit contenir les versions, sorties de commandes, résultats JSON, logs nettoyés, checksum, statut CI et décision signée. Une erreur connue doit être classée ; elle ne doit pas être masquée par `|| true`.
