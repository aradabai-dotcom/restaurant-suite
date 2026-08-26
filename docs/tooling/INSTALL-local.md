# Guide d’installation local — Restaurant Suite

Ce guide installe l’environnement de développement et de validation sur Ubuntu 24.04. Les commandes sont données pour une machine de développement ou une sandbox disposant de Docker. Les versions exactes de WordPress, WooCommerce, PHP et Node seront ensuite épinglées dans la matrice du projet.

## 1. Principe de stack

DDEV est l’environnement canonique. Il fournit le site WordPress, PHP, la base, HTTPS local, WP-CLI, Mailpit et Xdebug. Le PHP et la base de l’hôte ne doivent pas être utilisés pour exécuter les tests d’intégration. Cette règle évite qu’un développeur obtienne un résultat différent selon sa machine.

Le projet n’utilisera pas simultanément DDEV, `wp-env` et une installation WordPress manuelle comme environnements de référence. WordPress Playground est optionnel pour les previews et E2E éphémères. Les tests WooCommerce, HPOS, cache et migration se font obligatoirement sur DDEV.

## 2. Les trois environnements

Le projet utilise trois environnements, chacun avec un rôle distinct. La sandbox de préparation sert au front-end, aux scripts, à l’analyse et au packaging. DDEV est l’environnement local canonique, réinitialisable, pour PHP, WordPress, WooCommerce, HPOS, panier, commandes et fixtures. Le site WordPress vierge réel est un staging technique séparé, créé uniquement pour le projet, qui permet de valider HTTPS, cache, cron, emails, permissions de fichiers et différences d’hébergement.

Le site réel doit être protégé, ne contenir aucune donnée client, utiliser des comptes et adresses de test, et être sauvegardé avant chaque campagne. Il ne doit jamais être utilisé comme destination de commandes réelles. WPScan, OWASP ZAP et k6 ne peuvent cibler que ce site ou une autre cible explicitement autorisée.

| Environnement | Tests autorisés | Ne doit pas servir à |
|---|---|---|
| Sandbox | Build, tests JS, analyse, packaging, smoke HTTP | Valider PHP/WooCommerce/HPOS si Docker/DDEV absents |
| DDEV | PHP, WordPress, WooCommerce, HPOS, panier, commandes, migration | Reproduire exactement le cache et l’hébergement réel |
| WordPress vierge réel | Staging HTTPS, cache, cron, emails test, permissions et régression | Remplacer DDEV, recevoir des données réelles ou devenir production |

## 3. Paquets système

```bash
sudo apt-get update
sudo apt-get install -y \
  ca-certificates curl wget git unzip zip jq make \
  build-essential libnss3 libatk-bridge2.0-0 libdrm2 libxkbcommon0 \
  libgtk-3-0 libgbm1 libasound2t64 libxshmfence1 \
  php-cli php-mbstring php-xml php-curl php-zip php-mysql \
  mariadb-client
```

Docker et DDEV doivent être installés selon leur documentation officielle afin de rester compatibles avec les versions supportées. Après installation, vérifier :

```bash
docker version
docker compose version
ddev version
```

Le développeur doit pouvoir exécuter Docker sans privilèges inattendus. Après une modification du groupe Docker, ouvrir une nouvelle session puis vérifier à nouveau.

## 4. PHP et Composer

Le projet utilise PHP dans DDEV. Composer 2 doit être disponible dans le conteneur web et, si souhaité, sur l’hôte pour l’édition des dépendances.

```bash
composer --version
ddev composer --version
ddev php -v
```

Initialiser les dépendances du plugin :

```bash
cd /chemin/restaurant-suite/plugin/restaurant-suite-core
ddev composer install
```

Les dépendances de production et de développement doivent être séparées. Le fichier `composer.lock` est versionné. Aucun `composer update` non documenté ne doit être exécuté avant une release.

## 5. Node.js, Playwright et front-end

Utiliser une version Node LTS indiquée dans la matrice de compatibilité. Vérifier :

```bash
node --version
npm --version
```

Installer les dépendances depuis la racine du dépôt :

```bash
npm ci
npx playwright install --with-deps chromium
```

L’installation officielle Playwright crée ou utilise `playwright.config.ts`, le runner, les assertions, les traces et le rapport HTML [9]. La documentation WordPress Playground ajoute `@wp-playground/cli` lorsqu’une instance WordPress éphémère est nécessaire [8] :

```bash
npm install --save-dev @playwright/test @wp-playground/cli @axe-core/playwright
```

Le navigateur Chromium est obligatoire en développement et en PR. Firefox et WebKit sont ajoutés au job de release si la matrice de compatibilité les annonce.

## 6. DDEV et WordPress

Depuis le dossier du projet :

```bash
ddev config --project-type=wordpress --docroot=web --project-name=restaurant-suite
ddev start
ddev wp core download
ddev wp core install \
  --url=https://restaurant-suite.ddev.site \
  --title='Restaurant Suite Test' \
  --admin_user=admin \
  --admin_password='admin-local-only-change-me' \
  --admin_email=admin@example.test
```

Installer WooCommerce avec une version fixée dans la matrice :

```bash
ddev wp plugin install woocommerce --activate --version=<VERSION_WOOCOMMERCE>
ddev wp option update woocommerce_store_address '1 rue de test'
ddev wp option update woocommerce_default_country 'FR'
ddev wp rewrite structure '/%postname%/'
ddev wp rewrite flush
```

Installer et activer les composants développés :

```bash
ddev wp plugin activate restaurant-suite-core
ddev wp theme activate restaurant-base-theme
```

Le projet doit fournir un `scripts/reset-fixtures.sh` idempotent. Il supprime uniquement les données de test identifiées par un préfixe ou un groupe de fixtures et ne doit jamais cibler un site de production.

## 7. Mailpit et Xdebug

Mailpit sert à capturer les emails sans envoi réel. Son URL doit être fournie par DDEV. Tester une notification de commande sans dépendre d’un SMTP externe. Xdebug est activé uniquement pour débogage ou couverture locale, pas pour mesurer les performances.

```bash
ddev describe
ddev xdebug on
ddev xdebug off
```

## 8. PHPUnit WordPress

Le flux de base suit les recommandations WordPress : scaffold des tests par WP-CLI, installation des polyfills et de `wp-phpunit`, puis scripts Composer [1].

```bash
ddev wp scaffold plugin-tests restaurant-suite-core --ci=github
ddev composer require --dev yoast/phpunit-polyfills:^1.0 wp-phpunit/wp-phpunit:^6.3
```

Les tests seront organisés ainsi :

```text
tests/
├── unit/          # classes pures, normalisation, règles et payloads
├── integration/   # WordPress, hooks, options, capacités et WooCommerce
├── fixtures/      # données déterministes
└── e2e/           # parcours navigateur Playwright
```

Les tests unitaires ne bootent pas WordPress. Les tests WordPress utilisent `WP_UnitTestCase`. Les tests WooCommerce qui nécessitent panier, produits, commandes ou HPOS s’exécutent sur la version WooCommerce présente dans le site DDEV ou dans une suite WordPress dédiée clairement configurée. La CI ne doit pas mélanger silencieusement les deux bases.

## 9. PHPStan et stubs

PHPStan doit être exécuté au niveau choisi progressivement. Le niveau initial est documenté ; l’objectif est de l’augmenter à chaque version sans masquer les problèmes par une exclusion globale. Les stubs WordPress et WooCommerce doivent couvrir les fonctions et objets utilisés. Les exclusions sont locales, commentées et réévaluées.

```bash
ddev composer stan
```

Le résultat PHPStan doit être traité comme une erreur de build à partir de la phase 0.0 pour les contrats et à partir de V0.1 pour le code métier.

## 10. PHPCS et standards WordPress

Installer WPCS par Composer et déclarer la règle dans `phpcs.xml.dist`. Les exceptions doivent être limitées aux cas justifiés par WordPress ou WooCommerce. Le formateur automatique `phpcbf` ne remplace pas une revue de code.

```bash
ddev composer cs
ddev composer cs:fix
```

## 11. Playwright et E2E

Le serveur de test est lancé avant la suite ou réutilise une instance DDEV déjà prête. Les tests utilisent une URL configurable par `BASE_URL`. Les traces et screenshots sont conservés seulement en cas d’échec ou de retry pour limiter les artefacts.

```bash
BASE_URL=https://restaurant-suite.ddev.site npx playwright test
npx playwright show-report
npx playwright test --ui
npx playwright codegen https://restaurant-suite.ddev.site
```

Les scénarios doivent préférer rôles, labels et textes visibles. Les `data-testid` sont réservés au markup du plugin, documentés et stables. Les sélecteurs privés d’Elementor et les délais fixes sont interdits.

## 12. Lighthouse et accessibilité

Lighthouse CI doit être lancé sur des pages réalistes : accueil, menu, fiche produit, panier et confirmation. Les seuils sont documentés dans `lighthouserc.json`. axe-core est lancé via Playwright sur menu, Quick View, drawer, formulaire WhatsApp et dashboard.

```bash
npx lhci autorun
npm run a11y
```

Les seuils de performance ne remplacent pas une analyse des assets et du rendu mobile. Les violations d’accessibilité critiques ou sérieuses bloquent une version.

## 13. Contrôle installation complète

La machine est prête lorsque la commande suivante réussit sans intervention manuelle :

```bash
make doctor
make install
make reset
make validate
make package
```

`make doctor` vérifie outils et versions. `make install` installe les dépendances. `make reset` reconstruit fixtures et comptes de test. `make validate` exécute qualité PHP, qualité JS, tests unitaires, intégration, E2E et accessibilité. `make package` produit ZIP, checksum et manifeste de versions.

## 14. Préparer le WordPress vierge réel

Le site réel doit être une installation WordPress vierge créée exclusivement pour les tests. Avant chaque campagne, relever URL, version PHP, version WordPress, version WooCommerce, serveur web, extensions PHP, mémoire, limites d’upload, cron, HTTPS, timezone, locale, cache, mode HPOS et plugins actifs. Enregistrer une sauvegarde restaurable avant l’installation ou la mise à jour du package.

Installer uniquement les éléments nécessaires : WooCommerce, Restaurant Suite, Restaurant Base Theme si testé, Elementor si la campagne le prévoit, puis les outils d’audit temporaires Query Monitor, User Switching et WP Crontrol. WP-CLI Doctor et WPScan sont utilisés selon les accès disponibles et les règles d’autorisation de l’hébergement. Mailpit reste l’outil local ; sur le staging, utiliser uniquement des adresses de test.

Les tests réels obligatoires sont l’installation du ZIP, activation/désactivation, HTTPS, cache actif puis contourné, cron, emails de test, rôles, permissions, E2E mobile, ajout panier, commande de test, import/export et rollback. Un rapport compare ces résultats avec DDEV. Le site réel ne doit recevoir aucune donnée client ou commande de production.

## 15. Ce qui ne doit pas être installé comme dépendance métier

Le projet ne doit pas ajouter jQuery, Font Awesome complet, Masonry, Magic Animations, une police d’icônes globale, un framework d’administration tiers ou une seconde bibliothèque de panier uniquement pour reproduire un comportement existant. Les outils d’audit peuvent être lourds en développement, mais ils doivent être activés temporairement puis retirés avant livraison.

## 16. Références

[1]: https://developer.wordpress.org/news/2025/12/how-to-add-automated-unit-tests-to-your-wordpress-plugin/ "WordPress — automated unit tests for plugins"

[8]: https://wordpress.github.io/wordpress-playground/guides/e2e-testing-with-playwright/ "WordPress Playground — E2E with Playwright"

[9]: https://playwright.dev/docs/intro "Playwright — installation and reports"

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

