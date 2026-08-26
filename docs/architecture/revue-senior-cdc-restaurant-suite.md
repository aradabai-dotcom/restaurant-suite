# Revue senior du pack CDC — Restaurant Suite

## Verdict

**Oui, le pack est digne d’un travail de développeur senior au niveau architecture, cadrage produit, qualité, sécurité et stratégie de test.** Je lui attribue un niveau de maturité de **8,5/10 comme pack de conception**.

Je ne lui attribue pas encore 10/10 comme **repository immédiatement exécutable**, car l’archive contient principalement les CDC, les templates et les guides, pas encore le projet WordPress/DDEV complet avec tous les scripts référencés. Cela est normal si le pack est destiné à préparer le développement, mais il faut le dire clairement : le CDC est prêt à lancer la phase 0.0 ; il n’est pas encore un dépôt de code prêt à faire passer `make validate` sans créer les fichiers manquants.

| Domaine | Verdict senior | Commentaire |
|---|---|---|
| Architecture | **Excellent** | WooCommerce source unique, plugin/thème séparés, modules et contrats explicites |
| Gestion du périmètre | **Excellent** | Phase 0.0 obligatoire et V0.1 volontairement limitée |
| UX et Elementor | **Très bon** | Fallback sans Elementor et intégration progressive bien cadrés |
| WooCommerce/HPOS | **Très bon** | APIs CRUD, tests HPOS et blocs explicitement considérés |
| Sécurité | **Très bon** | Permissions, nonces, idempotence, données personnelles et scans autorisés |
| Tests | **Excellent** | Unitaires, intégration, E2E, accessibilité, performance et staging réel |
| Staging réel | **Excellent** | Addendum très utile, avec séparation sandbox/DDEV/site réel |
| CI exécutable | **À corriger** | Le workflow fourni contient encore des prérequis et scripts absents |
| Prêt à coder | **Oui pour phase 0.0** | Pas encore pour V0.1 sans créer les artefacts techniques manquants |

## Ce qui a été nettement amélioré

La nouvelle archive apporte exactement les éléments qui manquaient auparavant : un addendum consacré au WordPress vierge réel, une checklist de staging, une séparation explicite entre sandbox, DDEV et staging hébergé, les règles de sécurité pour les scans, les gates de passage et les rollbacks par phase.

La phase 0.0 est maintenant correctement cadrée. Elle contient le contrat de données, les statuts, l’idempotence WhatsApp, la matrice de compatibilité, les permissions, les événements JavaScript, les hooks WooCommerce et la matrice de tests. Elle interdit explicitement l’implémentation fonctionnelle avant la validation écrite. C’est une décision de niveau senior, car elle empêche le code de devenir le lieu où les décisions métier sont improvisées.

Le nouveau pack a aussi corrigé la promesse excessive autour du site réel. Le document précise que le site hébergé est un **staging technique** et non un remplacement de DDEV. Il interdit les commandes réelles, les données personnelles, les secrets de production et les scans non autorisés. Cette distinction est importante pour un projet qui devra être réutilisé chez plusieurs clients.

La logique de rollback est également bien traitée. Elle ne supprime pas automatiquement les commandes créées pendant une fenêtre de déploiement, elle restaure les réglages versionnés et elle prévoit une vérification du parcours principal. C’est beaucoup plus professionnel qu’un simple « désactiver le plugin si quelque chose casse ».

## Les points bloquants à corriger

### 1. Le workflow GitHub Actions ne prépare pas réellement DDEV

Dans `templates/ci-wordpress.yml`, le job Playwright exécute `ddev start`, mais le workflow ne montre aucune étape d’installation de DDEV ni de configuration Docker. Un runner GitHub Actions vierge ne doit pas être supposé disposer de DDEV. Il faut ajouter une étape officielle d’installation, ou utiliser un workflow réutilisable maîtrisé et documenté.

La correction doit inclure : installation/validation de Docker, installation de DDEV, vérification de `ddev version`, copie ou création de `.ddev/config.yaml`, démarrage, installation de WordPress et WooCommerce, puis chargement des fixtures. Tant que cela n’est pas fait, le workflow est une bonne maquette CI, mais pas un pipeline garanti exécutable.

### 2. Le job Playwright ne montre pas l’installation complète de WordPress

Le workflow fait `ddev start`, puis `./scripts/reset-fixtures.sh`, mais l’archive fournie ne contient pas ce script et le workflow ne montre pas l’installation de WordPress, de WooCommerce, du plugin, du thème ni la création de l’utilisateur de test.

Il faut soit ajouter un script idempotent `scripts/bootstrap-local.sh` appelé par la CI, soit détailler toutes les commandes dans le workflow. Le script devra vérifier ou créer le site, installer la version fixée de WooCommerce, activer les composants développés, configurer les permaliens, créer les comptes, charger les fixtures et retourner une erreur claire si l’installation est incomplète.

### 3. Plusieurs scripts référencés ne sont pas dans le pack

Le Makefile et la CI référencent notamment `scripts/reset-fixtures.sh` et `scripts/package-release.sh`, mais ces scripts ne sont pas présents dans l’archive CDC. Ce n’est pas un défaut de conception, mais c’est un défaut de complétude si l’archive est présentée comme un pack permettant de lancer immédiatement `make validate`.

La solution senior est d’ajouter au moins des scripts squelettes exécutables, avec des erreurs explicites lorsque le projet n’est pas initialisé. Il faut aussi ajouter un `scripts/bootstrap-local.sh`, un `scripts/doctor.sh`, un `scripts/package-release.sh`, un `scripts/reset-fixtures.sh`, un `scripts/seed-fixtures.sh` et un `scripts/check-staging.sh`.

### 4. Les lockfiles et versions réelles doivent être présents au moment de coder

Les guides indiquent correctement que les versions doivent être épinglées, mais l’archive CDC seule ne contient pas encore le projet effectif, ses `composer.lock`, `package-lock.json` ou `pnpm-lock.yaml`. Avant V0.1, il faudra choisir npm ou pnpm, générer un lockfile, fixer les versions PHP/WordPress/WooCommerce/Node/Playwright et enregistrer la matrice de compatibilité.

Il ne faut pas utiliser `latest` dans le bootstrap ni accepter une mise à jour automatique pendant les tests. Une version testée doit être lisible dans les rapports de CI et de staging.

### 5. La CI ne couvre pas encore tout ce que le CDC promet

Le workflow minimal couvre PHP, JavaScript, Playwright et packaging. Le CDC prévoit aussi axe-core, Lighthouse, Gitleaks, WPScan, Semgrep éventuel, les artefacts de logs, la matrice de versions et les tests complets de release. Il faut donc distinguer clairement :

| Pipeline | Contenu |
|---|---|
| PR rapide | PHP lint, PHPCS, PHPStan, PHPUnit unitaires, build/lint JS, Vitest, Playwright Chromium et mobile |
| PR intégration | DDEV, WordPress, WooCommerce, fixtures, PHPUnit intégration, Playwright complet et axe-core |
| Release | Matrice PHP/WP/WC, HPOS, Elementor actif/désactivé, Firefox/WebKit, Lighthouse, WPScan, Gitleaks, audit dépendances, packaging et rollback |

L’absence de tout ce bloc dans le workflow minimal n’est pas grave, à condition que le CDC dise explicitement quelles vérifications appartiennent à chaque pipeline. Le document de production le fait déjà en partie ; il faut aligner les templates CI avec cette hiérarchie.

## Points non bloquants mais à améliorer

### Observabilité des performances

L’utilisation de Query Monitor est une excellente décision. Il faut toutefois enregistrer dans les rapports le nombre de requêtes, le temps DB, le temps PHP, le nombre de scripts/styles et les appels HTTP par page de référence. Cela permettra de fixer un budget régressif plutôt que de faire une simple observation ponctuelle.

### Budgets d’assets

Le CDC parle de budgets JavaScript et CSS, mais il faut fixer des seuils dans un fichier versionné, par exemple `docs/performance-budgets.json`. Le budget doit être séparé pour page accueil, menu, produit, panier et dashboard. Le dashboard peut être plus lourd que le menu public, mais son bundle ne doit jamais être chargé côté visiteur.

### Tests de concurrence

Le test d’idempotence WhatsApp est bien présent. Pour aller au niveau senior complet, ajouter un test d’accès concurrent avec deux requêtes quasi simultanées et un test de stock limite. Le but est de vérifier qu’une double validation ne crée pas deux commandes et ne dépasse pas la disponibilité.

### Gestion des données sensibles dans les rapports

Le CDC interdit correctement mots de passe, cookies, tokens et messages WhatsApp complets. Il faut également masquer les téléphones, adresses, emails, URLs signées et identifiants de commande dans les screenshots et logs CI. Un script de nettoyage des artefacts serait préférable avant upload GitHub Actions.

### Compatibilité des blocs

La décision de reporter les blocs Panier/Checkout est saine. Le pack doit simplement maintenir une liste explicite des fonctions supportées et non supportées dans `docs/compatibility-matrix.md`. Cela évitera qu’un widget Elementor ou une page client suppose une compatibilité qui n’a pas été testée.

## Avis sur le niveau de documentation

La documentation est structurée comme un vrai dossier de cadrage : document maître, phases séparées, outillage, installation, templates CI, checklist de staging et addendum du site réel. Elle contient des critères d’acceptation, des gates et des rollbacks. C’est nettement supérieur à un simple cahier des charges fonctionnel.

Ce qui manque encore pour atteindre un niveau de livraison senior complet est le **dépôt initial réel** : arborescence créée, scripts exécutables, configuration DDEV, dépendances verrouillées, fixtures, rapports exemples et un premier test vert. La documentation dit quoi construire ; la prochaine étape doit matérialiser cette structure.

## Décision finale

| Question | Réponse |
|---|---|
| Le pack est-il cohérent ? | **Oui** |
| Est-il digne d’un développeur senior ? | **Oui, clairement au niveau architecture et QA** |
| Peut-on commencer la phase 0.0 ? | **Oui, immédiatement** |
| Peut-on coder la V0.1 avec l’archive seule ? | **Oui après création du dépôt et des scripts manquants** |
| La CI est-elle déjà garantie exécutable ? | **Non, DDEV/bootstrap/scripts doivent être complétés** |
| Le WordPress vierge réel améliore-t-il le dispositif ? | **Oui, à condition de le garder comme staging non production** |
| Faut-il encore ajouter des outils ? | **Peu : les ajouts essentiels sont déjà intégrés** |

## Verdict professionnel

**Je valide le pack comme CDC senior et comme base officielle du projet.** Je ne validerais pas encore une release de développement en disant « tout est prêt » tant que le bootstrap DDEV, les scripts référencés, les lockfiles et le workflow CI réellement exécutable ne sont pas présents.

La prochaine étape professionnelle est donc : créer le dépôt `restaurant-suite`, matérialiser l’arborescence du plugin et du thème, ajouter les scripts DDEV/fixtures/packaging, générer les lockfiles, puis faire passer `make doctor`. Après cela seulement, nous rédigerons et validerons les six contrats de la phase 0.0.

> **Conclusion :** ce pack est digne d’un senior parce qu’il traite les décisions, les risques, les tests et les rollbacks avant le code. Pour devenir un pack senior complet de production, il lui reste à transformer les templates CI et les scripts référencés en artefacts réellement exécutables.

## Références

[1]: https://developer.wordpress.org/plugins/ "WordPress Plugin Developer Handbook"

[2]: https://developer.woocommerce.com/docs/features/orders/high-performance-order-storage/ "WooCommerce Developer Documentation — High-Performance Order Storage"

[3]: https://docs.ddev.com/en/stable/users/quickstart/ "DDEV Documentation — CMS Quickstarts"

[4]: https://make.wordpress.org/cli/handbook/guides/doctor/ "WP-CLI — Doctor Guides"

[5]: https://wordpress.org/plugins/query-monitor/ "Query Monitor — WordPress.org"

[6]: https://wpscan.com/wordpress-cli-scanner/ "WPScan — WordPress CLI Scanner"

[7]: https://playwright.dev/docs/intro "Playwright Documentation — Installation and Test Reports"
