# Gate de matérialisation du dépôt

## Statut attendu

Ce document doit être signé avant le gate des contrats métier 0.0. Il prouve que le dépôt est exécutable ; il ne valide pas encore le modèle métier.

| Preuve | Résultat | Artefact |
|---|---|---|
| Arborescence canonique | À remplir | `find`/rapport |
| DDEV propre | À remplir | `ddev version`, logs |
| WordPress/WooCommerce | À remplir | versions et état |
| Fixtures | À remplir | produits/comptes attendus |
| Makefile | À remplir | sortie des cibles |
| Lockfiles | À remplir | checksums/versions |
| CI runner propre | À remplir | URL du run et artefacts |
| Packaging | À remplir | ZIP, manifeste, SHA-256 |
| Staging réel | À remplir | checklist staging |

## Ordre obligatoire

1. Matérialiser le dépôt.
2. Faire passer `make doctor`.
3. Faire passer `make install`, `make reset`, `make validate` et `make package`.
4. Installer sur le WordPress vierge réel et exécuter les smoke tests.
5. Approuver par écrit le gate de matérialisation.
6. Finaliser et approuver les contrats métier de la phase 0.0.
7. Commencer seulement ensuite la V0.1.
