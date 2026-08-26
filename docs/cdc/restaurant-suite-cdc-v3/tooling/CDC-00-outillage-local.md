# CDC-00 — Outillage local et environnement de validation

**Projet :** Restaurant Suite Core + Restaurant Base Theme  
**Version du document :** 1.0  
**Statut :** obligatoire avant le développement métier  
**Responsable de validation :** lead developer / architecte  

## 1. Objet

Ce document définit l’environnement local reproductible utilisé pour développer, tester, auditer et packager le plugin `restaurant-suite-core` et le thème `restaurant-base-theme`. Aucun code métier ne pourra être déclaré validé uniquement parce qu’il fonctionne dans le navigateur d’un développeur. Chaque étape de la roadmap devra passer par des tests déterministes, des contrôles statiques, des tests d’intégration et, lorsque le comportement est visible, des scénarios Playwright.

L’environnement primaire sera **DDEV + Docker**, car il permet de reproduire un site WordPress avec PHP, base de données, WP-CLI, HTTPS local, Xdebug et services auxiliaires. WordPress Playground pourra servir à des tests rapides de plugin ou de thème et à des previews éphémères, mais il ne remplacera pas l’environnement DDEV pour WooCommerce, HPOS, le panier, les commandes et la migration. La documentation WordPress Playground décrit Playwright et les Blueprints pour des tests E2E sans Docker ; cette approche est utile en complément, pas comme seule preuve de compatibilité WooCommerce [8].

## 2. Séparation des environnements

Le dispositif de validation comprend trois environnements complémentaires. La sandbox de préparation sert au code front, aux tests JavaScript, à l’analyse d’archives, au packaging et aux smoke tests HTTP ; elle ne doit pas être présentée comme un environnement WordPress complet si Docker, PHP, Composer et DDEV n’y sont pas disponibles. DDEV est l’environnement canonique local et réinitialisable pour PHP, WordPress, WooCommerce, panier, commandes, HPOS, fixtures et migration. Un WordPress vierge réel, créé exclusivement pour le projet et accessible à l’équipe autorisée, sert de staging d’hébergement : il révèle les différences de PHP, serveur web, HTTPS, cache, permissions de fichiers, cron, emails et configuration réelle.

Le WordPress vierge réel ne remplace pas DDEV et ne doit jamais recevoir de données personnelles ou de commandes réelles. Il doit être protégé par authentification, être identifié comme environnement de test, utiliser des comptes et adresses de test, et disposer d’une sauvegarde restaurable avant chaque campagne. Les scans WPScan, OWASP ZAP et k6 ne sont exécutés que contre ce site ou une cible explicitement autorisée, avec une fenêtre de test connue.

| Environnement | Rôle | Outils et données autorisés | Gate principal |
|---|---|---|---|
| Sandbox de préparation | Préparer et analyser | Node, pnpm/npm, Chromium, Git, jq, curl, ZIP, tests JS | Build et packaging |
| DDEV local canonique | Reproduire et réinitialiser | Docker, DDEV, PHP, Composer, WP-CLI, WP/WC, MariaDB, Mailpit, Xdebug | Tests PHP/WP/WC/HPOS |
| WordPress vierge réel | Vérifier l’hébergement | HTTPS, cache réel, cron, email test, Query Monitor temporaire, User Switching, WP Crontrol, WPScan autorisé | Staging et régression |

Le site réel doit fournir une fiche de contrôle avant chaque campagne : URL, versions PHP/WordPress/WooCommerce, serveur web, extensions PHP, mémoire, upload, cron, HTTPS, cache, timezone, locale, mode HPOS, plugins actifs et date de sauvegarde. Les résultats sont comparés à `docs/compatibility-matrix.md`.

## 3. Architecture des dépôts

Le dépôt devra contenir une séparation claire entre code PHP, assets publics, dashboard, thème, tests et scripts d’environnement. L’arborescence de référence est la suivante :

```text
restaurant-suite/
├── .ddev/
│   ├── config.yaml
│   ├── commands/web/rs-test
│   ├── commands/web/rs-reset
│   └── docker-compose.*.yaml
├── plugin/restaurant-suite-core/
│   ├── restaurant-suite-core.php
│   ├── src/
│   ├── templates/
│   ├── assets/src/
│   ├── assets/build/
│   ├── languages/
│   ├── tests/unit/
│   ├── tests/integration/
│   ├── tests/e2e/
│   ├── phpunit.xml.dist
│   ├── phpstan.neon.dist
│   ├── phpcs.xml.dist
│   └── composer.json
├── theme/restaurant-base-theme/
│   ├── style.css
│   ├── theme.json
│   ├── templates/
│   ├── parts/
│   ├── patterns/
│   ├── assets/
│   └── tests/
├── tests/e2e/
├── tests/fixtures/
├── scripts/
├── docs/
├── package.json
├── playwright.config.ts
├── composer.json
├── Makefile
└── README.md
```

Le plugin et le thème peuvent être distribués séparément, mais leur développement doit rester testable depuis un même dépôt d’intégration. Les versions PHP, WordPress, WooCommerce, Node et des navigateurs doivent être déclarées dans `docs/compatibility-matrix.md` et dans les fichiers de verrouillage. Les builds reproductibles ne devront pas dépendre de `latest`.

## 3. Outils à installer

| Catégorie | Outil | Usage obligatoire | Installation ou gestion |
|---|---|---|---|
| Conteneurs | Docker Engine + Compose | Exécuter DDEV et les services WordPress | Installation système recommandée par DDEV |
| Environnement | DDEV | PHP, WordPress, base, HTTPS, Xdebug, Mailpit | Binaire DDEV + `.ddev/config.yaml` |
| PHP | PHP 8.2 et 8.3 au minimum de la matrice | Compatibilité et exécution | Dans DDEV ; éviter de dépendre du PHP hôte |
| Gestion PHP | Composer 2 | Dépendances et scripts | `composer.json` + `composer.lock` |
| CMS | WP-CLI | Installation, fixtures, activation, export et reset | Fourni ou ajouté à DDEV |
| CMS | WordPress | SUT principal | Installation dans DDEV |
| E-commerce | WooCommerce | Catalogue, panier et commandes | Version épinglée de la matrice |
| Base | MariaDB ou MySQL | Intégration WooCommerce/HPOS | Service DDEV |
| Courriel | Mailpit | Vérification des emails sans envoi réel | Service DDEV |
| Debug PHP | Xdebug | Pas à pas et couverture locale | Activé à la demande |
| Qualité PHP | PHPUnit | Tests unitaires et intégration WordPress | Composer |
| Qualité PHP | PHPStan + stubs WordPress | Analyse statique | Composer |
| Style PHP | PHPCS + WordPress Coding Standards | Standards WordPress | Composer |
| Syntaxe PHP | PHP Parallel Lint | Détection rapide d’erreurs de syntaxe | Composer |
| Sécurité dépendances | Composer Audit | Vulnérabilités Composer | Composer |
| Diagnostic WordPress | WP-CLI Doctor | Checks versionnés du core, plugins, thèmes, cron et configuration | DDEV et staging |
| Inspection WordPress | Query Monitor | Requêtes, hooks, erreurs PHP, scripts, styles, AJAX, HTTP et capacités | Temporaire en développement/staging |
| Audit des rôles | User Switching | Changement rapide de comptes de test | Temporaire en développement/staging |
| Cron | WP Crontrol | Inspection des cron et Scheduled Actions | Temporaire en développement/staging |
| Scan WordPress | WPScan CLI | Exposition du core, plugins, thèmes et fichiers | Staging autorisé et release |
| Traductions | gettext | `msgfmt`, `msgmerge` et validation des fichiers .po/.mo | DDEV/CI |
| RTL | rtlcss | Génération et validation CSS RTL | npm/pnpm |
| HTML | html-validate | Validation du HTML produit | npm/pnpm |
| Blocs | @wordpress/scripts | Build officiel des blocs Gutenberg | npm/pnpm si bloc natif |
| Sécurité dépendances | Composer Audit | Vulnérabilités Composer | Composer |
| JS/Build | Node.js LTS, npm ou pnpm | Build et outillage front | Version épinglée |
| JS | TypeScript | Types du store et événements si retenu | npm/pnpm |
| JS | ESLint | Qualité JavaScript/TypeScript | npm/pnpm |
| Formatage | Prettier | Formatage PHP/JS/JSON/Markdown selon configuration | npm/pnpm |
| CSS | Stylelint | Qualité CSS et variables design | npm/pnpm |
| Tests JS | Vitest | Tests du store et fonctions pures | npm/pnpm |
| E2E | Playwright Test | Parcours navigateur et régression | `@playwright/test` |
| E2E WP optionnel | WordPress Playground CLI | Fixtures et tests éphémères | `@wp-playground/cli` |
| Accessibilité | axe-core avec Playwright | Contrôles automatisés a11y | `@axe-core/playwright` |
| Performance | Lighthouse CI | Pages publiques, assets et budgets | `@lhci/cli` |
| API | curl + jq | Tests d’endpoint et diagnostics | Outils système |
| Git | Git + GitHub CLI | Branches, PR et CI | Outils système |
| CI | GitHub Actions | Exécution reproductible sur PR/tag | `.github/workflows/` |
| Secrets | Gitleaks ou équivalent | Détection de secrets committés | Binaire ou CI |
| Sécurité | Semgrep Community, si accepté | Règles complémentaires PHP/JS | Binaire ou CI |
| Packaging | zip, sha256sum | ZIP de livraison et checksum | Outils système |

## 4. Dépendances PHP proposées

Les versions exactes devront être figées dans `composer.lock`, mais le socle attendu est le suivant :

```json
{
  "require-dev": {
    "phpunit/phpunit": "^9.6 || ^10.5",
    "yoast/phpunit-polyfills": "^1.0",
    "wp-phpunit/wp-phpunit": "^6.3",
    "phpstan/phpstan": "^2.0",
    "szepeviktor/phpstan-wordpress": "*",
    "php-stubs/woocommerce-stubs": "*",
    "dealerdirect/phpcodesniffer-composer-installer": "^1.0",
    "phpcompatibility/php-compatibility": "^9.0",
    "wp-coding-standards/wpcs": "^3.0",
    "php-parallel-lint/php-parallel-lint": "^1.4",
    "phpunit/php-code-coverage": "^9.2 || ^10.0"
  }
}
```

Le choix de PHPUnit 9 ou 10 doit être déterminé par la version de PHP et par les dépendances WordPress retenues. Il ne faut pas mélanger des versions incompatibles uniquement pour avoir la version la plus récente. La référence WordPress de tests automatisés utilise WP-CLI, `wp-phpunit/wp-phpunit` et les polyfills Yoast [1].

## 5. Dépendances JavaScript proposées

```json
{
  "devDependencies": {
    "@playwright/test": "^1.0.0",
    "@wp-playground/cli": "^1.0.0",
    "@axe-core/playwright": "^4.0.0",
    "typescript": "^5.0.0",
    "vitest": "^3.0.0",
    "eslint": "^9.0.0",
    "prettier": "^3.0.0",
    "stylelint": "^16.0.0",
    "stylelint-config-standard": "^37.0.0",
    "@lhci/cli": "^0.0.0"
  }
}
```

Les versions doivent être remplacées par des versions réellement testées au moment de l’initialisation. La documentation Playwright recommande Node.js récent, installe le runner et les navigateurs, et fournit un rapport HTML, l’UI mode, les traces et les screenshots comme outils de diagnostic [9].

## 6. Bootstrap DDEV de référence

Le projet devra fournir un script idempotent `scripts/bootstrap-local.sh`. Il devra vérifier Docker, DDEV, Composer, Node et Git, créer ou vérifier la configuration DDEV, démarrer le projet, installer WordPress, installer WooCommerce, installer les plugins développés, créer les pages de test et charger les fixtures.

Commandes de référence :

```bash
ddev config --project-type=wordpress --docroot=web

ddev start
ddev launch

ddev wp core download
ddev wp core install \
  --url=https://restaurant-suite.ddev.site \
  --title='Restaurant Suite Test' \
  --admin_user=admin \
  --admin_password='change-me-local-only' \
  --admin_email=admin@example.test

ddev wp plugin install woocommerce --activate --version=<version-epinglee>
ddev wp plugin activate restaurant-suite-core
ddev restart
```

Les identifiants de test doivent être locaux, non réutilisés en staging ou en production. Le script devra pouvoir reconstruire la base de test depuis zéro. Un second script devra sauvegarder un état de fixture et un troisième devra restaurer cet état.

## 7. Commandes de qualité obligatoires

Le `composer.json` devra exposer au minimum les scripts suivants :

```json
{
  "scripts": {
    "lint": "parallel-lint src tests",
    "cs": "phpcs",
    "cs:fix": "phpcbf",
    "stan": "phpstan analyse --memory-limit=1G",
    "test:unit": "phpunit --testsuite unit",
    "test:integration": "phpunit --testsuite integration",
    "test": "composer lint && composer cs && composer stan && composer test:unit && composer test:integration",
    "audit": "composer validate --strict && composer audit"
  }
}
```

Le `package.json` devra exposer :

```json
{
  "scripts": {
    "build": "node scripts/build.mjs",
    "lint": "eslint . && stylelint '**/*.{css,scss}'",
    "format:check": "prettier --check .",
    "format:write": "prettier --write .",
    "test:js": "vitest run",
    "e2e": "playwright test",
    "e2e:ui": "playwright test --ui",
    "e2e:debug": "playwright test --headed --debug",
    "a11y": "playwright test tests/e2e/accessibility.spec.ts",
    "perf": "lhci autorun",
    "validate": "npm run build && npm run lint && npm run format:check && npm run test:js && npm run e2e"
  }
}
```

Le script global `./bin/validate` ou `make validate` devra enchaîner les contrôles PHP, JS, E2E, accessibilité, packaging et état Git. Une étape échouée bloque la validation de la version.

## 8. Configuration Playwright

Playwright devra démarrer contre l’URL DDEV, avec un utilisateur WooCommerce invité, un client connecté et un utilisateur restaurant selon les besoins. Les tests devront utiliser des locators sémantiques ; des attributs `data-testid` stables seront ajoutés uniquement aux composants contrôlés par le plugin. Les attentes devront être web-first et ne pas utiliser de délais arbitraires.

Configuration minimale :

```ts
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  workers: 1,
  forbidOnly: Boolean(process.env.CI),
  retries: process.env.CI ? 2 : 0,
  timeout: 120_000,
  expect: { timeout: 30_000 },
  reporter: [['html', { open: 'never' }], ['list']],
  use: {
    baseURL: process.env.BASE_URL ?? 'https://restaurant-suite.ddev.site',
    screenshot: 'only-on-failure',
    trace: 'on-first-retry',
    video: 'retain-on-failure',
    ignoreHTTPSErrors: true
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    { name: 'mobile-chrome', use: { ...devices['Pixel 5'] } }
  ]
});
```

Chromium et mobile Chromium seront exécutés sur chaque PR. Firefox, WebKit, tablette et les scénarios de compatibilité plus coûteux seront exécutés dans la validation de release. La documentation WordPress Playground recommande notamment un seul worker lorsqu’une instance de serveur est partagée, des timeouts adaptés au démarrage WordPress, les screenshots en cas d’échec et les traces lors d’une nouvelle tentative [8].

## 9. Couverture minimale par niveau

| Niveau | Objectif | Outils | Blocage |
|---|---|---|---|
| Syntaxe | Aucun fichier PHP invalide | PHP Parallel Lint | Oui |
| Standards | Code lisible et conforme | PHPCS/WPCS, ESLint, Stylelint, Prettier | Oui |
| Statique | Types, appels et retours cohérents | PHPStan, stubs WP/WooCommerce | Oui à partir de 0.0 |
| Unitaire | Fonctions pures, normalisation, règles | PHPUnit, Vitest | Oui |
| WordPress | Hooks, options, capacités et rendu | WP test suite/PHPUnit | Oui |
| Intégration WooCommerce | Produits, panier, commandes, HPOS | PHPUnit + DDEV | Oui selon phase |
| E2E | Parcours réel dans le navigateur | Playwright | Oui selon phase |
| Accessibilité | Violations critiques et navigation | axe-core, Playwright, revue clavier | Oui |
| Performance | Budgets publics | Lighthouse CI, analyse assets | Oui avant 1.0 |
| Sécurité | Authz, nonce, échappement et dépendances | PHPUnit, Playwright, audit | Oui |
| Packaging | Installation et rollback | WP-CLI, ZIP, checksum | Oui avant chaque release |

## 10. Fixtures de test obligatoires

Le script de fixtures devra créer un produit simple disponible, un produit simple hors stock, un produit variable avec au moins deux variations de prix différents, une catégorie vide, une catégorie active et des produits contenant des descriptions longues, images manquantes et caractères accentués. Il devra créer un client invité simulable, un client connecté, un propriétaire, un manager, un membre cuisine et un livreur.

Les fixtures ne doivent jamais utiliser les données personnelles réelles de Chef Anass. Les images de test doivent être neutres et libres d’usage. Chaque suite doit pouvoir repartir d’un état connu afin d’éviter les tests dépendants de l’ordre d’exécution.

## 11. CI minimale

La CI devra être organisée en jobs séparés : `php-lint`, `phpcs`, `phpstan`, `phpunit`, `js-quality`, `playwright-chromium`, `security`, `package`. Les artefacts d’échec devront conserver le rapport PHPUnit, le rapport Playwright, la trace, les screenshots, le log WordPress, le résultat Lighthouse et le ZIP de test.

La CI devra tester au moins une combinaison supportée de PHP, WordPress et WooCommerce sur chaque PR. La matrice complète de versions, HPOS, Elementor actif/désactivé et navigateur étendu sera déclenchée avant une release. Les jobs qui touchent à la base ou au navigateur devront travailler sur une installation propre, non sur une base partagée entre PR.

## 12. Règle d’installation et d’acceptation

Un outil n’est pas considéré comme installé parce qu’il est présent dans le système. Il est accepté seulement lorsque son numéro de version, son rôle, sa commande de vérification et son artefact de sortie sont documentés.

| Outil | Vérification | Preuve attendue |
|---|---|---|
| DDEV | `ddev version` | Version enregistrée |
| Docker | `docker version` | Client et serveur disponibles |
| WP-CLI | `ddev wp cli version` | Version et sortie sans erreur |
| Composer | `composer --version` | Version 2 enregistrée |
| PHPStan | `ddev composer stan` | Analyse terminée |
| PHPUnit | `ddev composer test:unit` | Rapport vert |
| PHPCS | `ddev composer cs` | Aucun blocage |
| Node | `node --version` | Version LTS enregistrée |
| Playwright | `npx playwright --version` | Version et navigateurs installés |
| Lighthouse | `lhci --version` | Binaire disponible |
| WP-CLI Doctor | `ddev wp doctor check --all` | Diagnostics sans erreur critique |
| Staging réel | `wp --info` ou fiche hébergeur | Versions et extensions documentées |
| Git | `git status` | Arbre propre ou état documenté |

## 13. Références

[1]: https://developer.wordpress.org/news/2025/12/how-to-add-automated-unit-tests-to-your-wordpress-plugin/ "WordPress Developer Blog — Automated unit tests for a WordPress plugin"

[2]: https://developer.wordpress.org/plugins/ "WordPress Plugin Developer Handbook"

[3]: https://docs.ddev.com/en/stable/users/quickstart/ "DDEV Documentation — CMS Quickstarts"

[4]: https://developer.woocommerce.com/docs/features/orders/high-performance-order-storage/ "WooCommerce Developer Documentation — HPOS"

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

