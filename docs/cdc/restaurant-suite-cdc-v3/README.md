# Pack CDC — Restaurant Suite

Ce pack contient le cahier des charges senior de `Restaurant Suite Core`, `Restaurant Base Theme` et de l’environnement local de test. Il est aligné sur la roadmap finale fournie et organise le travail en portes de validation successives.

## Lecture recommandée

| Ordre | Fichier | Usage |
|---|---|---|
| 1 | `CDC-Restaurant-Suite-complet.md` | Vision, architecture, phases et gouvernance |
| 2 | `tooling/INSTALL-local.md` | Installation de la stack locale Ubuntu/DDEV |
| 3 | `tooling/CDC-00-outillage-local.md` | Outils, versions, commandes, CI et seuils |
| 4 | `phases/00-phase-0.0-contrats.md` | Contrats avant tout code métier |
| 5 | `phases/01-v0.1-fondation-menu.md` | Fondation et menu public |
| 6 | `phases/02-v0.2-quick-view-preview.md` | Quick View et live preview |
| 7 | `phases/03-v0.3-cart-drawer.md` | Panier latéral |
| 8 | `phases/04-v0.4-whatsapp-regles.md` | Commande WhatsApp et règles simples |
| 9 | `phases/05-v0.5-dashboard.md` | Dashboard et rôles |
| 10 | `phases/06-v0.6-theme-elementor.md` | Thème et Elementor |
| 11 | `phases/07-v0.7-duplication-onboarding.md` | Duplication client et onboarding |
| 12 | `phases/08-v1.0-production-migration.md` | Release, migration et production |
| 13 | `ADDENDUM-wordpress-vierge-reel.md` | Règles du staging WordPress réel dédié aux tests |
| 14 | `templates/staging-checklist.md` | Checklist de chaque campagne staging |
| 15 | `templates/` | Makefile, manifests Composer/Node, Playwright et CI |

## Règle d’utilisation

Une phase ne passe à la suivante que lorsque son CDC, ses tests, ses rapports et son rollback sont validés. Le dossier contient des modèles prêts à copier dans le dépôt ; les versions de dépendances doivent être confirmées puis verrouillées dans `composer.lock`, `package-lock.json` ou `pnpm-lock.yaml`.

## Premier sprint recommandé

Le premier sprint ne code pas le dashboard. Il installe DDEV, initialise WordPress et WooCommerce, crée le dépôt, renseigne la matrice de compatibilité, produit les six contrats de phase 0.0, installe les outils de qualité et fait passer `make doctor` puis les tests de smoke. Après approbation de la phase 0.0, le développement de la V0.1 peut commencer.

## Sources

Les références officielles utilisées sont indiquées dans les CDC : DDEV, WordPress Developer Handbook, article de tests WordPress, WooCommerce HPOS, WordPress Coding Standards, PHPStan, WordPress Playground et Playwright.

## Mise à jour v2 — premier sprint corrigé

Le premier sprint installe les outils dès la phase 0.0 lorsque l’environnement le permet, mais chaque outil possède une date de première exécution et une date de blocage. Infection et Rector sont installés tôt ; ZAP commence par un baseline passif sur le staging autorisé ; k6 commence par un smoke test ; Psalm reste exploratoire ; BrowserStack reste optionnel.

Avant de commencer la V0.1, le dépôt réel doit contenir `.ddev/`, `scripts/bootstrap-local.sh`, `scripts/reset-fixtures.sh`, `scripts/seed-fixtures.sh`, `scripts/package-release.sh`, `scripts/doctor.sh`, les lockfiles, la matrice de compatibilité et les budgets de performance. `make doctor` et `make validate` doivent être exécutés sur une installation propre.

## Addendum v3 — dépôt exécutable avant contrats métier

Le projet distingue désormais deux gates successifs :

1. **Gate de matérialisation du dépôt** : l’arborescence, DDEV, les scripts, les fixtures, le Makefile, les lockfiles et la CI existent et produisent les preuves annoncées.
2. **Gate des contrats métier 0.0** : le contrat de données, les statuts, les permissions, l’idempotence, les événements, les hooks et la matrice de tests sont approuvés.

Le dépôt doit être opérationnel pour accueillir la phase 0.0, mais cette matérialisation ne remplace pas les contrats métier. Aucun code fonctionnel V0.1 ne commence avant l’approbation écrite des contrats 0.0.

### Gouvernance Composer

| Fichier | Responsabilité | Commande de référence |
|---|---|---|
| `composer.json` racine | Outillage global du dépôt, scripts qualité et dépendances communes | `composer install` depuis la racine |
| `composer.lock` racine | Lockfile de l’outillage et de la CI | `composer install --no-interaction --prefer-dist` |
| `plugin/restaurant-suite-core/composer.json` | Autoload et dépendances propres au plugin si nécessaires | Appelé par le build/package du plugin |
| `plugin/composer.lock` | Éviter au démarrage | À ajouter seulement si le plugin devient un package Composer indépendant |

PHPUnit, PHPStan et PHPCS doivent être lancés depuis un emplacement documenté et unique. Le dépôt ne doit pas avoir deux `composer.json` concurrents qui exécutent des scripts différents sans règle explicite. Si le plugin n’a pas de dépendance Composer de production, son `composer.json` local peut être supprimé au profit du `composer.json` racine.

### Contrat des scripts

Tous les chemins canoniques sont à la racine : `scripts/bootstrap-local.sh`, `scripts/bootstrap-ci.sh`, `scripts/doctor.sh`, `scripts/seed-fixtures.sh`, `scripts/reset-fixtures.sh`, `scripts/package-release.sh`, `scripts/check-staging.sh` et `scripts/sanitize-artifacts.sh`. Le dossier `templates/` peut conserver des exemples, mais aucun Makefile ou workflow ne doit appeler un script uniquement présent dans `templates/`.

### Gate de matérialisation

La matérialisation est acceptée lorsque `make install`, `make doctor`, `make reset`, `make validate` et `make package` fonctionnent sur un DDEV propre, que la CI passe sur un runner vierge et que le package est installé sur le WordPress vierge réel. Le rapport doit contenir les versions, les logs, les fixtures, les résultats de tests, le manifeste et le checksum.

