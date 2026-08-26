# Journal développeur — Restaurant Suite

Ce document est la source de vérité opérationnelle pour suivre ce qui a été réellement implémenté. Chaque entrée doit préciser la phase, le périmètre, les fichiers touchés, les tests exécutés, les limites connues et le commit associé.

## Statut actuel

| Élément | Statut |
|---|---|
| Phase 0.0 — contrats | Implémentée partiellement et testée localement |
| Dépôt canonique | Matérialisé |
| V0.1 — menu public | À implémenter |
| V0.2 — Quick View/live preview | À implémenter |
| V0.3 — Cart Drawer | À implémenter |
| V0.4 — WhatsApp/règles restaurant | À implémenter |
| V0.5 — dashboard | À implémenter |
| V0.6 — thème/Elementor | Squelette initial seulement |
| DDEV/WooCommerce/HPOS | En attente d’un environnement Docker/DDEV-capable |

## Règle de traçabilité

Aucune fonctionnalité ne doit être déclarée comme terminée uniquement parce que ses fichiers existent. Une entrée doit être ajoutée après chaque réalisation avec les commandes de test, les résultats réels et les contrôles non exécutés.

## Entrée — Phase 0.0 initiale

**Périmètre :** matérialisation du dépôt, contrats machine, registre des statuts/événements, fixtures initiales, scripts, Makefile, DDEV, lockfiles et CI.

**Réalisé :** ajout de `.ddev/config.yaml`, des manifests Composer/Node, des contrats JSON, du registre PHP `ContractRegistry`, des tests PHPUnit/Vitest, des scripts de bootstrap/doctor/fixtures/reset/package, du thème de base, du plugin Core et des workflows GitHub Actions.

**Tests locaux réussis :** `composer validate --strict`, PHP Parallel Lint, PHPCS, PHPStan, PHPUnit, validation des contrats PHP/JS, ESLint, Stylelint, Prettier, build Node, Vitest et packaging.

**Limites :** Docker et DDEV ne sont pas disponibles dans l’environnement d’exécution actuel. L’installation réelle de WordPress/WooCommerce, HPOS, les fixtures WordPress, les tests E2E, ZAP, k6 et le staging réel restent à exécuter dans un environnement autorisé et Docker-capable.

**Décision :** la phase 0.0 métier et la V0.1 ne sont pas déclarées complètement validées avant la démonstration DDEV et staging.

## Modèle pour les prochaines entrées

### Entrée — [phase/version] — [date]

**Objectif :** à compléter.

**Fichiers modifiés :** à compléter.

**Comportement implémenté :** à compléter.

**Tests exécutés :** à compléter.

**Résultats :** à compléter.

**Limites et risques :** à compléter.

**Commit :** à compléter.

## Entrée — staging — 26 août 2026

**Objectif :** préparer un staging WordPress vierge pour les validations réelles de Restaurant Suite sans ajouter de dépendance payante ni ouvrir la boutique.

**Fichiers modifiés :** `docs/reports/staging-execution-log.md`.

**Comportement réalisé :** WooCommerce 11.0.1 était déjà installé et activé. Elementor 4.2.3, Query Monitor 4.0.7 et User Switching 1.12.1 ont ensuite été installés depuis le répertoire officiel WordPress et activés. Le mode « Boutique bientôt disponible » a été conservé ; aucun paiement, produit, commande réelle ou service externe n’a été configuré.

**Tests exécutés :** confirmation visuelle de l’installation et de l’activation depuis l’administration WordPress ; présence du panneau Query Monitor dans l’administration ; vérification de la liste des extensions actives.

**Résultats :** le staging dispose désormais de WooCommerce, Elementor et des outils gratuits de diagnostic et de test de rôles requis pour la suite. La fonctionnalité Restaurant Suite n’est pas encore déployée.

**Limites et risques :** Docker/DDEV ne sont pas disponibles dans la sandbox ; les validations WordPress automatisées, HPOS, E2E et les fixtures restent à effectuer. Les outils de test ajoutés devront être retirés ou désactivés avant une mise en production.

**Commit :** `1d2c0b6` — `docs: record staging test dependencies`.

## Entrée — phase 0.0 — revalidation packaging — 26 août 2026

**Objectif :** confirmer que les corrections du bootstrap et du packaging ne régressent pas les contrôles locaux.

**Fichiers modifiés :** `scripts/bootstrap-local.sh`, `scripts/package-release.sh`, `docs/reports/phase-0.0-local-validation.md`.

**Comportement implémenté :** le bootstrap n’envoie plus `--version=` lorsque `WOOCOMMERCE_VERSION` est vide. Les ZIP de release ont maintenant une racine WordPress correcte et excluent les tests, la configuration PHPUnit et les caches de résultats.

**Tests exécutés :** `bash -n scripts/bootstrap-local.sh`, `bash -n scripts/package-release.sh`, `make validate`, `make package`, `unzip -l` sur les deux artefacts et `git diff --check`.

**Résultats :** tous les contrôles disponibles hors Docker/DDEV restent verts ; les deux ZIP contiennent uniquement leur code installable et leurs ressources prévues.

**Limites et risques :** l’installation réelle WordPress/WooCommerce, HPOS, les fixtures, les tests E2E et la CI d’intégration ne sont pas validés dans la sandbox sans Docker/DDEV.

**Commit :** à associer au commit de publication de cette entrée.

## Entrée — V0.1 menu serveur — 26 août 2026

**Objectif :** implémenter la fondation menu sur WooCommerce sans source catalogue parallèle, avec shortcode, bloc dynamique et widget Elementor optionnel.

**Fichiers modifiés :** `plugin/restaurant-suite-core/restaurant-suite-core.php`, `src/class-plugin.php`, `src/class-menucontroller.php`, `src/Menu/class-menuarguments.php`, `src/Menu/class-menuquery.php`, `src/Menu/class-menurenderer.php`, `src/Blocks/menu/block.json`, `src/Integrations/class-elementormenuwidget.php`, `assets/build/menu.css`, `tests/unit/MenuArgumentsTest.php`, `tests/unit/MenuQueryTest.php`, `phpstan.neon.dist`, `stubs/wordpress-woocommerce-elementor.php`, `docs/reports/v0.1-menu-validation.md`.

**Comportement implémenté :** le catalogue est interrogé avec `WC_Product_Query`; prix, disponibilité, images et liens utilisent les getters WooCommerce. Le renderer serveur commun est partagé par les trois points d’entrée. Les produits simples achetables proposent l’ajout au panier WooCommerce, les variables renvoient vers la fiche pour sélectionner une variation et les indisponibles restent visibles avec un état textuel. Le CSS est préfixé `crs-` et responsive.

**Tests exécutés :** `make validate`, `make package`, `bash -n`, PHPCS/WPCS, PHPStan niveau 6, PHPUnit, ESLint, Stylelint, Vitest, validation des contrats, inspection `unzip -l` et `git diff --check`.

**Résultats :** 7 tests PHPUnit et 17 assertions passent ; les analyseurs et le packaging passent. Le staging contient encore le package 0.0.1 ; le déploiement 0.1.0 et les fixtures WooCommerce sont la prochaine vérification réelle.

**Limites et risques :** Docker/DDEV restent indisponibles. Elementor n’est pas une dépendance obligatoire, mais son widget ne peut être validé qu’avec le package installé et activé sur WordPress. Les tests E2E, mobile et JavaScript désactivé restent à exécuter.

**Commit :** à associer au commit de publication de cette entrée.

## Entrée — V0.2 Quick View — 26 août 2026

**Objectif :** ajouter une preview rapide WooCommerce accessible, sans Reno Quick View et sans panier parallèle.

**Implémentation :** endpoint REST `crs/v1/quick-view/{product_id}` protégé par nonce ; contrôles serveur du statut, de la visibilité et de l’achetablité ; fragment HTML construit depuis les getters WooCommerce ; formulaire d’achat natif et fallback fiche produit. Le menu V0.1 expose un bouton déclencheur. Le contrôleur navigateur crée une modale, place le focus, verrouille le scroll, gère le clavier et restitue le focus au déclencheur. Les événements publics sont `crs:quickview:open` et `crs:quickview:close`.

**Fichiers :** `src/QuickView/class-quickviewendpoint.php`, `assets/src/quick-view.js`, `assets/build/quick-view.js`, `assets/build/menu.css`, `src/Menu/class-menurenderer.php`, `src/class-plugin.php`, `scripts/build.mjs`, `eslint.config.mjs`, `package.json`, stubs et tests Quick View.

**Contrôles locaux :** `make validate`, `make package`, Parallel Lint, PHPCS, PHPStan niveau 6, PHPUnit 10 tests/26 assertions, ESLint, Stylelint, Vitest et inspection du ZIP. Tous passent ; PHPStan affiche seulement son avertissement de version 1.12 ancienne.

**Limites :** l’endpoint, la modale, le focus clavier, Échap, erreur réseau et axe-core doivent encore être vérifiés sur staging après activation du package V0.2. Docker/DDEV restent indisponibles. Les fixtures staging restent synthétiques et aucune commande ou paiement ne sera effectué.

**Commit :** à associer au commit de publication V0.2.
