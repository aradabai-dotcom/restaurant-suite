# Spécification de matérialisation du dépôt — Restaurant Suite

## Objectif

Le CDC doit distinguer explicitement deux livrables :

1. **Le pack documentaire**, qui décrit l’architecture, les phases, les contrats et les critères.
2. **Le dépôt exécutable**, qui contient les fichiers, scripts, configurations, fixtures, dépendances verrouillées et workflows permettant de vérifier ces critères.

Le pack ne sera considéré comme matérialisé que lorsqu’une personne qui n’a pas écrit le code pourra cloner le dépôt, exécuter `make install`, réinitialiser l’environnement avec `make reset`, lancer `make validate` et générer un package avec `make package`.

> Un fichier template ou un script syntaxiquement valide ne constitue pas une preuve d’exécution. Chaque commande documentée doit produire l’effet annoncé, retourner un code d’erreur en cas d’échec et laisser un artefact vérifiable.

## 1. Arborescence canonique à ajouter au CDC

Le CDC doit imposer une arborescence unique. Les scripts ne doivent pas être dans `templates/` dans le dépôt final ; ils doivent être à la racine dans `scripts/`.

```text
restaurant-suite/
├── .ddev/
│   ├── config.yaml
│   ├── commands/web/rs-bootstrap
│   ├── commands/web/rs-doctor
│   ├── commands/web/rs-reset
│   └── docker-compose.redis.yaml              # seulement si un besoin réel est validé
├── .github/
│   └── workflows/
│       ├── pull-request.yml
│       ├── integration.yml
│       └── release.yml
├── plugin/
│   └── restaurant-suite-core/
│       ├── restaurant-suite-core.php
│       ├── src/
│       ├── templates/
│       ├── assets/src/
│       ├── assets/build/
│       ├── languages/
│       ├── tests/unit/
│       ├── tests/integration/
│       ├── phpunit.xml.dist
│       ├── phpstan.neon.dist
│       ├── phpcs.xml.dist
│       ├── composer.json
│       ├── composer.lock
│       └── readme.txt
├── theme/
│   └── restaurant-base-theme/
│       ├── style.css
│       ├── theme.json
│       ├── functions.php
│       ├── templates/
│       ├── parts/
│       ├── patterns/
│       ├── assets/src/
│       ├── assets/build/
│       └── tests/
├── tests/
│   ├── fixtures/
│   │   ├── products.json
│   │   ├── users.json
│   │   ├── settings.json
│   │   └── media/
│   ├── integration/
│   └── e2e/
├── scripts/
│   ├── bootstrap-local.sh
│   ├── bootstrap-ci.sh
│   ├── doctor.sh
│   ├── seed-fixtures.sh
│   ├── reset-fixtures.sh
│   ├── package-release.sh
│   ├── check-staging.sh
│   ├── sanitize-artifacts.sh
│   └── lib/
├── docs/
│   ├── compatibility-matrix.md
│   ├── data-contract.md
│   ├── permissions-matrix.md
│   ├── events-contract.md
│   ├── hooks-contract.md
│   ├── error-catalog.md
│   ├── performance-budgets.json
│   └── release-checklist.md
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json                         # ou pnpm-lock.yaml, mais un seul choix
├── tsconfig.json
├── playwright.config.ts
├── lighthouserc.json
├── Makefile
├── LICENSE
├── CHANGELOG.md
└── README.md
```

Le CDC doit préciser quels fichiers sont obligatoires, quels fichiers peuvent être générés et quels fichiers ne doivent jamais entrer dans un package client. `node_modules/`, `.git/`, les fichiers `.env`, les logs contenant des secrets et les tests avec identifiants réels sont exclus des ZIP de livraison.

## 2. Scripts exécutables obligatoires

Chaque script doit utiliser `set -euo pipefail`, vérifier ses prérequis, afficher une erreur exploitable et être idempotent lorsqu’il est prévu pour être relancé.

| Script | Responsabilité | Résultat attendu |
|---|---|---|
| `bootstrap-local.sh` | Préparer DDEV, WordPress, WooCommerce, plugin, thème et comptes locaux | Installation locale complète depuis zéro |
| `bootstrap-ci.sh` | Préparer l’environnement dans le runner CI | WordPress et WooCommerce prêts pour les tests |
| `doctor.sh` | Vérifier versions, configuration, plugins, thème, Doctor et état du site | Code non nul si une dépendance obligatoire manque |
| `seed-fixtures.sh` | Créer produits, catégories, utilisateurs, pages et réglages de test | État de test connu et reproductible |
| `reset-fixtures.sh` | Restaurer cet état sans toucher au code de livraison | Même résultat à chaque exécution |
| `package-release.sh` | Construire et vérifier les ZIP plugin/thème | ZIP complets, manifeste et checksum |
| `check-staging.sh` | Vérifier une URL staging avec contrôles non destructifs | Rapport HTTP, HTTPS, headers et disponibilité |
| `sanitize-artifacts.sh` | Masquer données personnelles et secrets dans logs/screenshots | Artefacts exportables sans données sensibles |

### Conditions minimales des scripts

`bootstrap-local.sh` doit refuser de continuer si DDEV ou Docker sont absents, vérifier la présence de `.ddev/config.yaml`, démarrer DDEV, installer WordPress si nécessaire, installer la version exacte de WooCommerce définie dans la matrice, configurer les permaliens, activer les composants et appeler les fixtures.

Les mots de passe doivent être fournis par variables d’environnement locales ou générés pour la session. Aucun mot de passe ne doit être committé dans le script. Les identifiants locaux peuvent être documentés comme exemples mais jamais réutilisés sur le staging réel.

`doctor.sh` doit échouer si WP-CLI Doctor est déclaré obligatoire mais absent. Il ne doit pas remplacer une erreur par un message et un code de sortie 0.

`package-release.sh` doit échouer si le plugin, le thème, le build ou un ZIP attendu manque. Il ne doit pas utiliser `|| true` autour de la génération de checksum. Il doit produire :

```text
dist/
├── restaurant-suite-core-X.Y.Z.zip
├── restaurant-base-theme-X.Y.Z.zip
├── manifest.json
├── checksums.txt
└── release-report.md
```

## 3. Fixtures WordPress/WooCommerce obligatoires

Les fixtures doivent être synthétiques, versionnées et réinitialisables. Elles ne doivent jamais utiliser les données réelles de Chef Anass ni de futurs clients.

### Catalogue

| Fixture | Propriétés |
|---|---|
| Produit simple disponible | Prix, image, description courte, catégorie active |
| Produit simple hors stock | Stock nul et affichage indisponible |
| Produit variable | Deux variations, prix différents, attribut obligatoire |
| Catégorie vide | Catégorie publiée sans produit |
| Catégorie active | Plusieurs produits et ordre connu |
| Produit sans image | Vérification du fallback visuel |
| Description accentuée | Caractères français, arabe si RTL annoncé et contenu long |
| Produit limite | Prix décimal, stock limite et variation supprimable |

### Comptes de test

Le script doit créer un propriétaire, un manager, un membre cuisine, un livreur, un client connecté et un utilisateur invité simulable. Les rôles doivent être supprimables ou réinitialisables sans toucher au compte administrateur de récupération.

### Pages et réglages

Les fixtures doivent créer une page menu, une page produit test, une page panier, une page checkout, une page dashboard de staging, des réglages horaires, un numéro WhatsApp de test, un minimum de commande, une zone de livraison et un jeu de taxes de test si le scénario le nécessite.

### Commandes

À partir de la V0.4, les fixtures doivent créer des commandes synthétiques dans chaque statut prévu. Elles doivent inclure produit simple, produit variable, livraison, retrait, commande refusée et commande idempotente. Les fixtures ne doivent jamais supprimer automatiquement des commandes de production ; le reset destructif est autorisé uniquement sur l’environnement identifié comme local ou staging de test.

## 4. Configuration DDEV obligatoire

Le CDC doit fournir un `.ddev/config.yaml` réel et documenter sa version. Il doit définir le type de projet, le docroot, le nom du projet, la version PHP de référence et les services nécessaires. Le projet doit utiliser DDEV comme environnement canonique réinitialisable ; le site hébergé réel reste un staging de compatibilité.

La configuration DDEV doit permettre :

- WordPress accessible en HTTPS local.
- PHP 8.2 et 8.3 testables selon la matrice.
- MariaDB ou MySQL selon la combinaison déclarée.
- WP-CLI fonctionnel via `ddev wp`.
- Composer fonctionnel via `ddev composer`.
- Mailpit accessible pour les emails de test.
- Xdebug activable à la demande.
- Réinitialisation de la base et des fixtures.
- Installation de WooCommerce avec une version épinglée.
- Activation de HPOS dans au moins un environnement de release.

Le bootstrap doit vérifier la présence de Docker et DDEV avant toute opération. Il doit refuser une configuration incomplète au lieu de la créer silencieusement avec des valeurs non documentées.

Les environnements ne doivent pas être mélangés : DDEV pour PHP/WooCommerce/HPOS/migration, sandbox pour préparation et outils Node disponibles, staging réel pour HTTPS/cache/cron/emails/permissions/hébergement. Les tests DDEV doivent être réinitialisables et indépendants de l’ordre des campagnes.

## 5. Lockfiles et matrice de versions

Le CDC doit imposer un seul gestionnaire de paquets Node. Le choix recommandé est **pnpm** si le projet veut profiter de la sandbox actuelle, ou npm si l’équipe préfère la compatibilité CI la plus simple. Il ne faut pas committer `package-lock.json` et `pnpm-lock.yaml` en même temps.

Les fichiers obligatoires sont :

```text
composer.json
composer.lock
package.json
pnpm-lock.yaml ou package-lock.json
```

La matrice doit au minimum contenir :

| Composant | Version de PR | Version de release | Statut |
|---|---|---|---|
| PHP | 8.2 ou 8.3 | Matrice 8.2/8.3 | Supporté après tests |
| WordPress | Version épinglée | Version mineure suivante | Testé, jamais `latest` |
| WooCommerce | Version épinglée | Version de compatibilité suivante | HPOS inclus |
| Node.js | LTS épinglée | Même version LTS | Lockfile obligatoire |
| Playwright | Version verrouillée | Navigateurs annoncés | Chromium PR, Firefox/WebKit release |
| Elementor | Actif et désactivé | Même scénario | Elementor non obligatoire |
| Panier/Checkout blocs | Reportés ou supportés | Seulement si tests verts | Ne pas supposer les fragments |

Chaque mise à jour de dépendance doit produire un diff, passer les audits et être liée à un rapport de compatibilité. Aucun `composer update` ou upgrade Node implicite ne doit avoir lieu dans le bootstrap.

## 6. CI réellement exécutable

Le CDC doit remplacer le workflow minimal par trois niveaux explicites.

### Pipeline pull request rapide

Le runner installe PHP, Composer, Node et les dépendances verrouillées. Il exécute PHP Parallel Lint, PHPCS, PHPStan, PHPUnit unitaire, build JS, ESLint, Stylelint, format check, Vitest et un smoke test Playwright.

### Pipeline intégration

Le runner installe Docker et DDEV, démarre le projet, installe WordPress/WooCommerce, charge les fixtures, active le plugin et le thème, puis exécute PHPUnit intégration, Playwright Chromium/mobile et axe-core. Le workflow doit explicitement vérifier que DDEV existe avant `ddev start`.

### Pipeline release

Le pipeline exécute la matrice de versions, HPOS, Elementor actif/désactivé, Firefox/WebKit, Lighthouse, Composer Audit, npm/pnpm audit, Gitleaks, WPScan contre une cible autorisée, ZAP baseline ou authentifié selon le périmètre, k6 contrôlé, packaging, checksum et scénario de rollback.

Chaque job doit avoir :

- un timeout maximum ;
- une version d’action épinglée autant que possible ;
- un environnement propre ;
- des artefacts en cas d’échec ;
- des logs nettoyés ;
- un code de sortie bloquant si le contrôle est requis par la phase.

Le job packaging ne doit pas pouvoir réussir si le build ou les ZIP attendus sont absents. Le job Playwright ne doit pas appeler `reset-fixtures.sh` avant que WordPress, WooCommerce, le plugin, le thème et les fixtures existent réellement.

## 7. Contrat des commandes Make

Le CDC doit fournir un Makefile aligné avec les chemins réels :

```text
make doctor       # prérequis et état de l’environnement
make install      # bootstrap local + dépendances
make start        # ddev start
make stop         # ddev stop
make reset        # reset de la base/fixtures de test
make php-lint     # syntaxe PHP
make phpcs        # standards WordPress
make stan         # PHPStan
make unit         # PHPUnit unitaire
make integration  # PHPUnit WordPress/WooCommerce
make js-build     # build front
make js-lint      # ESLint + Stylelint
make js-test      # Vitest
make e2e          # Playwright
make a11y         # axe-core
make perf         # Lighthouse CI
make security     # audits statiques et scans autorisés
make package      # ZIP + manifeste + checksum
make validate     # doctor + qualité + tests selon le niveau
```

Chaque cible doit pointer vers un fichier qui existe. Les cibles nécessitant DDEV doivent échouer clairement si DDEV n’est pas présent. `make validate` doit afficher la version du projet et le résumé de chaque sous-contrôle.

## 8. Preuves de validation à archiver

Chaque version doit créer un dossier de rapport :

```text
reports/<version>/<date>/
├── environment.md
├── compatibility.md
├── make-output.log
├── phpunit-unit.xml
├── phpunit-integration.xml
├── phpstan.txt
├── phpcs.txt
├── vitest.txt
├── playwright-report/
├── axe-report.json
├── lighthouse-report/
├── security-report.md
├── screenshots/
├── manifest.json
├── checksums.txt
└── decision.md
```

Le rapport doit indiquer la version, la cible, la date, l’opérateur, les versions, les commandes exécutées, les résultats, les anomalies, les décisions d’acceptation et le rollback disponible. Les screenshots et logs doivent être nettoyés des données sensibles.

## 9. Critères d’acceptation de matérialisation

Le dépôt est considéré comme matérialisé uniquement si tous les critères suivants sont vrais :

| Critère | Bloquant |
|---|---|
| Le dépôt contient l’arborescence canonique | Oui |
| Les scripts sont sous `scripts/` et les chemins sont cohérents | Oui |
| `make install` fonctionne sur DDEV propre | Oui |
| WordPress et WooCommerce sont installés avec versions fixées | Oui |
| Les fixtures créent les produits et comptes attendus | Oui |
| `make reset` restaure un état connu | Oui |
| Composer et Node installent depuis lockfiles | Oui |
| `make doctor` échoue lorsqu’un outil obligatoire manque | Oui |
| `make validate` exécute réellement les contrôles annoncés | Oui |
| La CI passe sur un runner vierge | Oui avant V0.1 |
| `make package` refuse un package incomplet | Oui |
| Le ZIP s’installe sur WordPress vierge | Oui avant V0.1 |
| Le staging réel passe installation, HTTPS, cache et smoke test | Oui avant migration |
| Le rollback est démontré | Oui avant V1.0 |

## 10. Ordre d’implémentation recommandé

| Étape | Livrable |
|---|---|
| 1 | Créer le dépôt et l’arborescence réelle |
| 2 | Ajouter `.ddev/config.yaml` et vérifier Docker/DDEV |
| 3 | Ajouter `composer.json`, `package.json` et choisir un lockfile Node |
| 4 | Ajouter tous les scripts sous `scripts/` |
| 5 | Implémenter les fixtures et le reset idempotent |
| 6 | Ajouter le Makefile aligné |
| 7 | Corriger la CI et l’exécuter sur runner propre |
| 8 | Produire le rapport `make doctor` et `make validate` |
| 9 | Commencer seulement ensuite les six contrats de phase 0.0 |
| 10 | Lancer la V0.1 après approbation écrite du gate 0.0 |

## Conclusion

Ce qu’il faut ajouter au CDC n’est pas davantage de fonctionnalités métier. Il faut ajouter un **contrat de matérialisation** : arborescence canonique, scripts réels, fixtures déterministes, configuration DDEV, lockfiles, CI installable et preuves archivées.

La distinction essentielle est la suivante : le CDC décrit la qualité attendue ; le dépôt et les rapports doivent démontrer que cette qualité existe. Une fois ces éléments présents et vérifiés, Restaurant Suite pourra être développé par plusieurs personnes et dupliqué chez plusieurs clients sans dépendre de la mémoire de son créateur.

## Références

[1]: https://docs.ddev.com/en/stable/users/quickstart/ "DDEV Documentation — CMS Quickstarts"

[2]: https://developer.wordpress.org/plugins/ "WordPress Plugin Developer Handbook"

[3]: https://developer.wordpress.org/news/2025/12/how-to-add-automated-unit-tests-to-your-wordpress-plugin/ "WordPress Developer Blog — Automated unit tests for a WordPress plugin"

[4]: https://developer.woocommerce.com/docs/features/orders/high-performance-order-storage/ "WooCommerce Developer Documentation — High-Performance Order Storage"

[5]: https://playwright.dev/docs/intro "Playwright Documentation — Installation and test reports"
