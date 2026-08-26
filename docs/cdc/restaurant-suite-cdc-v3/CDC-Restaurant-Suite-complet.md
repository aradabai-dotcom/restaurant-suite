# Cahier des charges senior — Restaurant Suite Core et Restaurant Base Theme

**Version :** 1.0  
**Statut :** prêt pour lancement de la phase 0.0  
**Périmètre :** plugin WordPress/WooCommerce modulaire, thème léger compatible Elementor et outillage local de validation.  
**Document maître :** Manus AI

## 1. Objet du projet

Le projet consiste à créer une base WordPress duplicable pour plusieurs restaurants, sans plugins payants obligatoires, en conservant WooCommerce comme source unique de vérité et en regroupant les fonctions restaurant dans `Restaurant Suite Core`. `Restaurant Base Theme` fournit les templates, le responsive et le design system. Elementor est compatible mais optionnel pour les fonctions métier.

La roadmap est exécutée par versions strictement séquentielles. Une version n’est validée que lorsque ses critères fonctionnels, qualité, sécurité, accessibilité, performance et réversibilité sont satisfaits. Les extensions actuelles ne sont retirées que sur staging, une à une, après validation du remplacement correspondant.

## 2. Principes non négociables

| Principe | Exigence |
|---|---|
| Source de vérité | WooCommerce possède produits, prix, variations, stock, panier et commandes. |
| Pas de duplication | Aucune table parallèle pour les produits ou les commandes sans justification approuvée. |
| Panier unique | Un seul store public, une seule session panier et des événements stables. |
| Sécurité serveur | Prix, taxes, frais, stock et statut sont recalculés côté serveur. |
| Elementor optionnel | Le plugin reste fonctionnel avec Gutenberg, shortcode et thème sans Elementor. |
| Progressive enhancement | Le contenu public reste lisible sans JavaScript ; le dynamique améliore le parcours. |
| Réversibilité | Chaque étape possède sauvegarde, rapport et procédure de rollback. |
| Qualité bloquante | Lint, statique, tests, sécurité et E2E peuvent bloquer une fusion ou une release. |
| Pas de dépendance lourde publique | Pas de framework admin tiers, jQuery obligatoire, Font Awesome complet, Masonry ou police d’icônes globale. |

## 3. Environnement de référence

Le dispositif de validation comprend trois environnements complémentaires. La sandbox de préparation sert au front-end, aux scripts, à l’analyse d’archives, aux tests JavaScript, aux smoke tests HTTP et au packaging. Elle ne doit pas être utilisée comme preuve de compatibilité PHP/WooCommerce/HPOS si Docker, PHP, Composer et DDEV n’y sont pas disponibles.

L’environnement canonique réinitialisable est **DDEV + Docker + WordPress + WooCommerce + MariaDB/MySQL + WP-CLI + Mailpit + Xdebug**. PHPUnit, PHPStan et PHPCS sont exécutés via Composer dans l’environnement PHP du projet. Node.js, TypeScript, ESLint, Prettier, Stylelint, Vitest et Playwright sont gérés depuis le dépôt.

Un **WordPress vierge réel**, créé exclusivement pour le projet et accessible aux personnes autorisées, sert de staging d’hébergement. Il permet de vérifier les différences réelles de PHP, serveur web, HTTPS, cache, cron, emails, permissions de fichiers et configuration de l’hébergeur. Il ne remplace pas DDEV, ne reçoit aucune donnée personnelle ou commande réelle et doit être sauvegardé avant chaque campagne. Query Monitor, User Switching, WP Crontrol et WPScan y sont des outils temporaires d’audit ; ils ne sont jamais distribués dans le package client. OWASP ZAP et k6 ne sont ajoutés qu’au moment où les routes et la charge justifient leur usage, uniquement sur une cible autorisée.

WordPress Playground est optionnel pour les previews et E2E éphémères ; il ne remplace pas DDEV pour WooCommerce, HPOS, le panier, les commandes et la migration. Le détail d’installation se trouve dans `tooling/INSTALL-local.md`. Le contrat complet d’outillage et les versions se trouvent dans `tooling/CDC-00-outillage-local.md`.

## 4. Versions et dépendances

La matrice de compatibilité doit épingler PHP, WordPress, WooCommerce, Node, Elementor, navigateurs et versions du plugin. Les builds reproductibles n’utilisent pas `latest`. Les packages sont verrouillés par `composer.lock` et `package-lock.json` ou `pnpm-lock.yaml`.

PHPUnit, WP-CLI, `wp-phpunit/wp-phpunit` et les polyfills Yoast suivent le flux de test documenté par WordPress [1]. DDEV fournit la base d’environnement local [3]. Playwright fournit runner, assertions, navigateurs, rapports HTML, traces et mode UI [9]. WordPress Playground peut démarrer des environnements éphémères avec Blueprints et Playwright [8].

## 5. Versions de roadmap et gates

| Version | Objet | Dépendance de sortie |
|---|---|---|
| 0.0 | Contrats de conception | Six contrats et matrice de tests approuvés |
| 0.1 | Fondation et menu serveur | Catalogue WooCommerce lisible et ajoutable |
| 0.2 | Quick View et live preview | Modale/panneau accessible, preview validée |
| 0.3 | Cart Drawer | Panier cohérent sur tous les scénarios prioritaires |
| 0.4 | WhatsApp et règles simples | Commande serveur unique et lien relançable |
| 0.5 | Dashboard | Journée gérable par rôle depuis mobile |
| 0.6 | Thème et Elementor | Package fonctionnel avec ou sans Elementor |
| 0.7 | Duplication client | Onboarding, import/export et rollback reproductibles |
| 1.0 | Production | Matrice, migration staging, packaging et livraison validés |

Les CDC détaillés sont dans `phases/00-phase-0.0-contrats.md` à `phases/08-v1.0-production-migration.md`.

## 6. Contrat de validation commun

Avant de déclarer une étape terminée, le pipeline exécute syntaxe PHP, standards PHP, analyse statique, tests unitaires, tests WordPress/WooCommerce, build JavaScript, lint JS/CSS, tests Vitest, Playwright, axe-core, audit des dépendances et packaging selon le périmètre de la version. Les artefacts d’échec sont conservés.

| Contrôle | Outil | Blocage |
|---|---|---|
| Syntaxe PHP | PHP Parallel Lint | Oui |
| Standards | PHPCS/WPCS, ESLint, Stylelint, Prettier | Oui |
| Analyse statique | PHPStan + stubs | Oui |
| Unitaire | PHPUnit, Vitest | Oui |
| WordPress | WP test suite | Oui dès V0.1 |
| WooCommerce | Intégration DDEV | Oui dès V0.1 pour les fonctions concernées |
| Browser | Playwright | Oui dès V0.1 |
| Accessibilité | axe-core + clavier manuel | Oui pour UI publique |
| Performance | Lighthouse CI et budget assets | Oui avant V1.0, ciblé par phase |
| Sécurité | Tests authz/nonce, Composer Audit, npm audit, Gitleaks | Oui |
| Packaging | ZIP, checksum, installation et rollback | Oui avant chaque package client |

## 7. Règle de décision en cas d’échec

Une anomalie de sécurité, perte de données, prix incorrect, doublon de commande, accès croisé, panne sur le parcours principal ou impossibilité de rollback est bloquante. Une anomalie esthétique non bloquante peut être documentée et planifiée, mais elle ne doit pas masquer une régression fonctionnelle. Toute exception est inscrite dans le rapport de version avec propriétaire, risque et date de correction.

## 8. Livrables de gouvernance

Chaque version produit un rapport de validation, une liste de changements, un manifeste de versions, les logs des commandes, les rapports PHPUnit/Playwright/Lighthouse, les screenshots d’échec ou de référence, le ZIP et le checksum. La branche de release doit être reproductible par une personne qui n’a pas écrit le code.

## 9. Migration et production

La migration commence par une copie staging. Le plugin propriétaire est activé en observation ; le nouveau menu est comparé à l’ancien. Puis Quick View, Cart Drawer, WhatsApp et dashboard sont remplacés dans l’ordre de la roadmap. Reno, Side Cart, OneClick et l’ancien dashboard ne sont retirés qu’après validation de leur remplacement. Essential Addons et WPCode sont audités avant suppression. WooCommerce est conservé.

La mise en production exige sauvegarde vérifiée, fenêtre de déploiement, plan de rollback, responsable de surveillance et test post-déploiement du menu, ajout panier, commande et accès dashboard. Les commandes apparues pendant une opération ne sont jamais supprimées automatiquement lors d’un rollback.

## 10. Références

[1]: https://developer.wordpress.org/news/2025/12/how-to-add-automated-unit-tests-to-your-wordpress-plugin/ "WordPress Developer Blog — Automated unit tests for a WordPress plugin"

[2]: https://developer.wordpress.org/plugins/ "WordPress Plugin Developer Handbook"

[3]: https://docs.ddev.com/en/stable/users/quickstart/ "DDEV Documentation — CMS Quickstarts"

[4]: https://developer.woocommerce.com/docs/features/orders/high-performance-order-storage/ "WooCommerce Developer Documentation — High-Performance Order Storage"

[5]: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/ "WordPress PHP Coding Standards"

[6]: https://phpstan.org/user-guide/extension-library "PHPStan Extension Library"

[7]: https://github.com/WordPress/WordPress-Coding-Standards "WordPress Coding Standards repository"

[8]: https://wordpress.github.io/wordpress-playground/guides/e2e-testing-with-playwright/ "WordPress Playground — E2E Testing with Playwright"

[9]: https://playwright.dev/docs/intro "Playwright — Installation and test reports"

## Addendum v2 — politique d’outillage anticipée

La règle officielle est désormais de distinguer **installation**, **première exécution** et **contrôle bloquant**. Lorsqu’un outil peut être installé sans créer de dépendance de production, il est installé dès la phase 0.0. Il n’est exécuté que lorsque la fonctionnalité correspondante existe, et il ne bloque une validation qu’à partir du niveau indiqué dans la matrice.

| Outil | Installation | Première exécution | Bloquant à partir de | Périmètre |
|---|---|---|---|---|
| Infection | 0.0 | Services PHP purs en V0.1/V0.2 | V0.2/V0.3 sur services critiques | Code propriétaire uniquement |
| Rector | 0.0 | `dry-run` dès 0.0 | Avant release/migration PHP | Aucun changement automatique sans revue |
| Psalm | Optionnel 0.0 | Analyse exploratoire du Core | Jamais au démarrage | PHPStan reste principal |
| OWASP ZAP | 0.0 si cible autorisée | Baseline/passif V0.1 | Routes AJAX/REST en V0.4 | Staging autorisé seulement |
| k6 | 0.0 si cible autorisée | Smoke léger V0.1 | Panier/commande en V0.3/V0.4 | Seuils et arrêt d’urgence obligatoires |
| BrowserStack | Optionnel V0.1 | Après premiers composants UI | V1.0 seulement si annoncé | Service distant, pas dépendance locale |
| WP-CLI Doctor | 0.0 | 0.0, 0.7 et 1.0 | Gates de staging/release | Checks personnalisés versionnés |
| Query Monitor | Staging 0.1 | Dès menu disponible | Analyse performance avant release | Jamais dans le ZIP client |
| User Switching | Staging 0.5 | Dès matrice de rôles | Revue manuelle des rôles | Les E2E ne doivent pas en dépendre |
| WP Crontrol | Staging 0.4 | Dès cron/Actions Scheduler | Avant release si cron utilisé | Jamais en production client par défaut |
| WPScan | 0.0/CI | Avant release | Avant chaque release | Cible autorisée uniquement |

L’installation anticipée ne dispense pas des garde-fous : ZAP, k6 et WPScan ne ciblent que le staging autorisé ; les rapports nettoient les secrets ; BrowserStack reste optionnel ; Infection ne mute jamais WordPress ou WooCommerce en entier.

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

