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

## Correctif staging — autoload V0.2 — 26 août 2026

Lors de l’activation staging de `restaurant-suite-core-0.2.0`, WordPress a signalé `Class "CRS\\QuickView\\QuickViewEndpoint" not found`. L’autoloader transformait tout le namespace en minuscules et cherchait donc `src/quickview/`, alors que le package contient `src/QuickView/` ; l’erreur n’apparaissait pas en environnement local insensible à la casse attendue.

Le correctif conserve la casse des segments de répertoire et ne minuscule que le nom de classe après le préfixe `class-`. La version 0.2.0 a ensuite été revalidée localement avec syntaxe PHP, Parallel Lint, PHPCS, PHPStan, PHPUnit 10 tests/26 assertions, ESLint, Stylelint, Vitest, build et packaging. Le package staging devra être remplacé par cet artefact corrigé avant toute validation fonctionnelle V0.2.

## Correctifs staging V0.2 — 26 août 2026

Le premier appel REST a exposé HTTP 403 `rest_cookie_invalid_nonce` : l’action de nonce propriétaire ne pouvait pas franchir la validation REST WordPress. Le code utilise désormais l’action standard `wp_rest` pour `X-WP-Nonce`, avec validation locale et endpoint staging HTTP 200.

La modale simple a ensuite été vérifiée sur staging : fragment WooCommerce présent, rôle dialog, `aria-modal`, titre référencé, focus dans la modale, scroll verrouillé, fermeture Échap et retour du focus au déclencheur. Le produit variable a révélé que `wp_kses_post()` retirait le formulaire natif et ses variations. Une sanitation dédiée autorise maintenant form/select/input et les attributs `data-*` nécessaires à WooCommerce. Contrôles locaux après correction : PHPUnit 10 tests/28 assertions, PHPStan sans erreur, PHPCS, Parallel Lint, ESLint, Stylelint, Vitest, contrats, build et package tous verts. Le staging doit recevoir cette dernière itération avant la clôture V0.2.

## Implémentation V0.3 Cart Drawer — 27 août 2026

La V0.3 ajoute `CRS\\Cart\\CartEndpoint` et le bundle `cart-drawer.js`. Le endpoint expose `add`, `update`, `remove` et `refresh` via REST, vérifie le nonce `wp_rest`, délègue toutes les mutations à `WC()->cart`, normalise les notices/lignes/totaux et n’écrit jamais dans une table catalogue ou panier parallèle. Le renderer menu charge le drawer conditionnellement, localise les URLs panier/checkout et transforme l’ajout simple en déclencheur du store unique. Les formulaires natifs du Quick View sont interceptés pour conserver les variations WooCommerce.

Le drawer gère état loading/error, sérialisation par ligne, compteur, panier vide, notices, quantités, suppression, liens panier/checkout, focus trap, Échap, clic extérieur et `prefers-reduced-motion`. Les stubs et tests PHPUnit couvrent nonce, refresh, lignes échappées, notices, totaux, panier vide et mutations add/update/remove. Contrôles locaux V0.3 : 19 fichiers Parallel Lint, PHPCS vert, PHPStan sans erreur avec avertissement de version ancienne, PHPUnit 13 tests/43 assertions, build, ESLint, Stylelint, Vitest, contrats, packaging et diff check verts. Le staging doit encore valider les mutations réseau, l’actualisation du compteur et l’observabilité avant passage à V0.4.

## Correctif V0.3 — initialisation REST du panier

Le premier test staging du bouton `data-crs-cart-add` a révélé HTTP 503 `crs_cart_unavailable`. Dans le contexte REST, WooCommerce était actif mais son objet `WC()->cart` n’était pas encore initialisé. Le endpoint appelle désormais `wc_load_cart()` lorsqu’un panier n’est pas disponible, puis reprend le panier WooCommerce comme seule source de vérité. Le correctif passe localement tous les contrôles V0.3 et doit remplacer le package staging avant de conclure.

## Correctif V0.3 — remove idempotent

Le scénario staging add/update/remove/refresh a exposé un HTTP 409 sur `remove`, alors que WooCommerce pouvait déjà avoir supprimé la ligne avant de retourner `false`. Le endpoint relit désormais `get_cart()` et considère l’action réussie lorsque la clé n’existe plus. Validation locale : 14 tests PHPUnit / 47 assertions et tous les contrôles `make validate`/packaging verts. Nouveau retest staging requis.

## Clôture V0.3 Cart Drawer — 27 août 2026

Le endpoint REST a été renforcé après le 503 staging : il appelle `wc_load_cart()` si nécessaire, relit l’instance retournée par `WC()`, initialise la session/le panier lorsqu’une méthode est disponible et force `get_cart()` avant mutation. La suppression est idempotente : si WooCommerce retourne `false` mais que la clé n’existe plus dans `get_cart()`, la réponse est traitée comme réussie. Les tests de régression couvrent ces chemins ainsi que la résolution serveur de variation via `get_matching_variation()`.

Le Quick View variable envoyait `variation_id=0` après sélection d’un attribut ; le endpoint résout maintenant la variation côté WooCommerce. Le parcours Grande a été validé sur staging avec la ligne Tacos Variable - Grande et le prix WooCommerce correspondant. Pour fermer la modale après succès, Quick View expose `window.CRS_QUICK_VIEW_CLOSE`, appelé par Cart Drawer après `crs:cart:add`. Le flux final a produit `crs:cart:add` puis `crs:quickview:close`, a ouvert le drawer et a restitué le focus au déclencheur.

La campagne UI finale a validé sur un panier synthétique l’ajout menu, l’ouverture automatique du drawer, la quantité `1 → 2 → 1`, la suppression, l’état vide, les liens Panier/Commander, Escape et la restitution du focus via `[data-crs-cart-open]`. Une erreur de harnais intermédiaire a ciblé le lien de navigation `/panier/` au lieu du bouton flottant ; elle est conservée comme incident de test, puis corrigée par l’identification explicite du sélecteur. Le panier est laissé vide, aucune commande n’a été créée et aucune action de paiement ou WhatsApp n’a été exécutée.

Validation locale finale : 15 tests PHPUnit / 50 assertions, Parallel Lint 19 fichiers, PHPCS 9 fichiers, PHPStan sans erreur avec avertissement de version ancienne, contrats JSON, build, ESLint, Stylelint, Vitest, packaging et `git diff --check` verts. Docker/DDEV, HPOS et Playwright complet restent non revendiqués car indisponibles dans la sandbox.

Le staging ne conserve qu’une seule copie V0.3.0 active (`restaurant-suite-core-0.3.0-3`) ; les copies historiques restent inactives pour rollback. Les détails et limites sont dans `docs/reports/v0.3-cart-drawer-validation.md` et `docs/reports/staging-execution-log.md`.
