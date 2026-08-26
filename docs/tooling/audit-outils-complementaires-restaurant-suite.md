# Audit des outils complémentaires — Restaurant Suite

## Avis sur le pack CDC

Le pack CDC est **très bon et suffisamment complet pour démarrer**. Il couvre déjà DDEV, Docker, WordPress, WooCommerce, MariaDB/MySQL, WP-CLI, Mailpit, Xdebug, Composer, PHPUnit, PHPStan, PHPCS/WPCS, PHP Parallel Lint, Composer Audit, Node.js, TypeScript, ESLint, Prettier, Stylelint, Vitest, Playwright, WordPress Playground, axe-core, Lighthouse CI, Git, GitHub Actions, Gitleaks, Semgrep, ZIP et checksum.

La stratégie DDEV comme environnement canonique est la bonne pour les tests WooCommerce, HPOS, panier, commandes et migration. WordPress Playground doit rester un outil complémentaire pour des tests éphémères. Il ne faut pas installer simultanément DDEV, `wp-env` et une installation WordPress manuelle comme trois environnements de référence : cela donnerait des résultats difficiles à comparer.

Le CDC a toutefois quelques compléments importants à ajouter pour obtenir des tests plus réalistes, en particulier pour les rôles, les requêtes WordPress, les tâches cron, la sécurité externe, le multilingue et la régression visuelle.

## État réel de la sandbox vérifiée

| Outil | État observé | Conséquence |
|---|---|---|
| Node.js | Présent, v22.13.0 | Suffisant pour le tooling front, avec versions de projet verrouillées |
| npm | Présent, v10.9.2 | Disponible |
| pnpm | Présent, v11.24.0 | Disponible, choisir npm ou pnpm et ne pas mélanger |
| Git | Présent | Disponible |
| GitHub CLI | Présent | Disponible |
| jq / curl | Présents | Disponibles pour diagnostics et API |
| MySQL client | Présent | Disponible, mais pas un serveur de base de données local isolé |
| Chromium | Présent | Utile pour smoke tests et navigateur de référence |
| Playwright package | Non installé dans le projet | À installer dans le dépôt, avec les navigateurs nécessaires |
| PHP | Absent | À fournir dans DDEV ou un environnement Docker capable |
| Composer | Absent | À fournir dans DDEV ou un environnement PHP capable |
| Docker | Absent | Bloquant pour exécuter DDEV dans cette sandbox |
| DDEV | Absent | Bloquant pour l’environnement local canonique |
| WP-CLI global | Absent | À utiliser dans DDEV plutôt qu’en installation hôte |
| Lighthouse CI | Absent | À installer dans le projet Node |
| WPScan | Absent | À ajouter pour le scan autorisé de WordPress |

La sandbox actuelle dispose déjà du front-end et des outils système de base, mais elle ne dispose pas de Docker, DDEV, PHP ni Composer. Par conséquent, je ne dois pas promettre que DDEV fonctionnera ici avant d’avoir un runtime Docker réellement disponible. Pour les tests d’intégration complets, il faudra utiliser soit ton ordinateur avec Docker/DDEV, soit une VM persistante capable d’exécuter Docker. La sandbox peut néanmoins servir à préparer le code, lancer les contrôles Node, analyser les archives, empaqueter les ZIP et exécuter des tests HTTP/navigateur légers.

## Outils supplémentaires recommandés

### Priorité P0 — À ajouter avant la V0.1

| Outil | Usage | Pourquoi il manque au CDC | Décision |
|---|---|---|---|
| **WP-CLI Doctor** | Diagnostics automatisés de l’installation | Il transforme les contrôles WordPress récurrents en checks versionnés ; il permet aussi des checks personnalisés [1] | Ajouter comme package WP-CLI dans DDEV |
| **Query Monitor** | Requêtes SQL, erreurs PHP, hooks, scripts, styles, appels HTTP, capacités et AJAX | Le CDC prévoit les tests mais pas l’outil d’inspection interactive ; Query Monitor permet de grouper les coûts par plugin/thème [2] | Installer temporairement sur le site de test, jamais dans le package client |
| **User Switching** | Tester propriétaire, manager, cuisine et livreur | Indispensable pour vérifier rapidement les vues et permissions sans multiplier les connexions ; l’outil est également recommandé comme extension de développement par Query Monitor [2] | Installer uniquement en développement/staging |
| **WP Crontrol** | Inspecter les cron WordPress et Scheduled Actions | Utile pour vérifier les tâches de migration, notifications et synchronisations | Installer uniquement en développement/staging |
| **WPScan CLI** | Scan WordPress autorisé du core, plugins, thèmes, fichiers exposés et configurations dangereuses | Le CDC couvre les dépendances mais pas l’exposition externe de l’installation ; WPScan est un scanner black-box dédié à WordPress [3] | Ajouter avant release et lancer uniquement sur sites autorisés |
| **gettext** | `msgfmt`, `msgmerge`, validation des fichiers de traduction | Nécessaire pour français, arabe et traductions du plugin/thème | Ajouter au système ou au conteneur DDEV |
| **RTL CSS** | Générer/valider la version RTL | Important si les restaurants utilisent arabe ou interface RTL | Ajouter `rtlcss` dans le pipeline front |
| **HTML Validate** | Valider le HTML produit par le thème et les composants | Complète axe-core et Lighthouse en détectant structure/attributs invalides | Ajouter comme contrôle npm léger |
| **@wordpress/scripts** | Build officiel des blocs Gutenberg et compatibilité des dépendances WordPress | Le CDC prévoit un bloc Gutenberg mais ne verrouille pas son outillage de build | Ajouter si le plugin fournit des blocs natifs |

WP-CLI Doctor est particulièrement intéressant pour le duplicateur : nous pouvons écrire des contrôles qui vérifient les pages attendues, les réglages, les extensions obligatoires, l’état du cache, les tâches cron et l’absence d’un ancien système concurrent.

Query Monitor doit être vu comme une **loupe temporaire**, pas comme une extension à distribuer aux restaurateurs. Il permet notamment de vérifier si Restaurant Suite charge un script sur une page qui n’en a pas besoin, si un hook produit trop de requêtes et si une extension existante ajoute des dépendances inattendues [2].

### Priorité P1 — À ajouter avant la V0.3/V0.4

| Outil | Usage | Décision |
|---|---|---|
| **OWASP ZAP** | Scan DAST autorisé des endpoints publics, AJAX/REST et headers | Ajouter en job de sécurité ciblé après que les routes existent ; ne pas lancer agressivement sur le site réel sans fenêtre de test |
| **k6** | Tests de charge reproductibles sur endpoints et pages publiques | Ajouter avant V0.4 si le site vise plusieurs commandes simultanées ; inutile pour la toute première carte produit |
| **Pixelmatch** ou snapshots Playwright | Comparaison visuelle des états Quick View, Cart Drawer et dashboard | Ajouter quand les composants visuels deviennent stables |
| **Rector PHP** | Refactorings PHP contrôlés et migrations de syntaxe | Ajouter si le code doit rester compatible avec plusieurs versions PHP |
| **PHPCompatibility** | Détection d’APIs incompatibles selon la version PHP | Le CDC le référence déjà dans Composer ; le rendre obligatoire dans la CI, pas seulement installé |
| **WP-CLI dist-archive** | Packaging WordPress standard | Ajouter au Makefile pour produire le ZIP depuis le dépôt proprement |
| **WP-CLI checksum** | Vérification de fichiers WordPress | Ajouter aux diagnostics de staging et au rapport de migration |

OWASP ZAP et k6 ne sont pas nécessaires pour prétendre que la V0.1 est terminée. Ils deviennent pertinents lorsque nous aurons des endpoints de commande, un dashboard authentifié et un flux WhatsApp. Ils doivent être utilisés uniquement contre un site appartenant au projet ou explicitement autorisé.

### Priorité P2 — Outils de maturité à ajouter après la V0.5

| Outil | Usage | Décision |
|---|---|---|
| **Infection** | Mutation testing pour vérifier que les tests détectent réellement les régressions | Optionnel après stabilisation des services métier |
| **Psalm** | Seconde analyse statique PHP | Pas nécessaire au départ : PHPStan + WPCS suffisent ; éviter deux outils qui produisent du bruit |
| **Snyk ou équivalent commercial** | Surveillance centralisée de dépendances | Pas nécessaire pour l’objectif sans outil payant ; Composer Audit, npm audit, Gitleaks et WPScan couvrent le socle |
| **BrowserStack** | Tests de nombreux appareils réels | Non nécessaire au début : Playwright Chromium/Firefox/WebKit couvre la base ; utiliser un service externe seulement si un problème réel apparaît |
| **MinIO** | Simulation S3 | À ajouter seulement si le produit introduit un stockage externe d’images ou de sauvegardes |

## Ce que je n’ajouterais pas maintenant

Je n’ajouterais pas Elementor, les plugins de production, plusieurs plugins de cache, `wp-env`, un second serveur MySQL ou plusieurs frameworks JavaScript dans l’environnement de test. Le but est de tester Restaurant Suite, pas de recréer les conflits de l’installation actuelle.

Je n’ajouterais pas non plus Query Monitor, User Switching, WP Crontrol ou WPScan dans le package final des clients. Ce sont des outils d’audit. Ils peuvent être activés sur staging puis supprimés avant livraison.

Je ne choisirais pas Vitest et Jest en parallèle, ni npm et pnpm en parallèle. Le dépôt doit choisir un seul gestionnaire Node et un seul runner JS. La proposition la plus simple est pnpm, puisque la sandbox le possède déjà, avec un lockfile committé.

## Matrice d’environnement recommandée

| Environnement | Outils | Usage |
|---|---|---|
| **Sandbox de préparation** | Node, pnpm, Chromium, Git, jq, curl, ZIP, scripts d’analyse | Build front, tests JS, analyse statique d’archives, packaging, smoke HTTP |
| **DDEV local canonique** | Docker, DDEV, PHP, Composer, WordPress, WooCommerce, MariaDB/MySQL, WP-CLI, Mailpit, Xdebug | Tests PHP/WordPress/WooCommerce/HPOS, fixtures, migration, commandes |
| **Staging WordPress vierge réel** | WordPress, WooCommerce, Restaurant Suite, Query Monitor temporaire, WPScan autorisé | Vérification sur hébergement réel, HTTPS, cache, email, permissions et compatibilité serveur |
| **CI** | Composer, PHPStan, PHPCS, PHPUnit, Node, Vitest, Playwright, axe, Lighthouse, Gitleaks, audit | Validation reproductible sur chaque PR et release |

Le site WordPress vierge réel est très utile, mais il ne doit pas remplacer DDEV. DDEV donne une base réinitialisable et reproductible ; le site réel révèle les différences de PHP, cache, serveur, HTTPS, permissions de fichiers, cron et hébergement.

## Tests réalistes supplémentaires à intégrer

### Test de compatibilité serveur réel

Sur le WordPress vierge, il faudra relever les versions PHP, WordPress, WooCommerce, MariaDB/MySQL, serveur web, extensions PHP, limites mémoire, taille maximale d’upload, cron, HTTPS et cache. Le rapport de staging devra être comparé à la matrice DDEV.

### Test de réseau réel

Le navigateur doit être testé avec latence, perte réseau et réponse lente simulées dans Playwright. Le Quick View, le Cart Drawer et WhatsApp doivent afficher un état de chargement et une erreur actionnable, sans laisser le bouton bloqué.

### Test d’emails réel sans envoi client

Mailpit couvrira les tests locaux. Sur le staging, il faudra utiliser une adresse de test et vérifier les emails WooCommerce, mais aucune adresse client réelle. Les liens, le numéro de commande et le statut devront être contrôlés.

### Test cache réel

Le staging doit être testé avec le cache Hostinger/LiteSpeed actif puis temporairement contourné. Le rapport doit confirmer que le panier, le checkout, le dashboard, les endpoints AJAX/REST et les statuts de commande ne sont pas servis depuis une réponse obsolète.

### Test multi-rôles réel

Créer des comptes de test propriétaire, manager, cuisine et livreur. User Switching facilite l’audit, mais un test Playwright indépendant doit confirmer les permissions sans dépendre de l’extension d’aide.

### Test duplication/rollback

Installer Restaurant Suite sur un WordPress vierge, importer une configuration de restaurant, créer des produits et commandes de test, lancer une mise à jour de réglages, puis restaurer l’état précédent. Le test doit prouver qu’aucune commande ni ligne produit ne disparaît.

## Liste d’installation finale que je recommande

### À installer dans un environnement Docker/DDEV capable

Docker Engine, DDEV, PHP 8.2 et 8.3 dans la matrice, Composer 2, WordPress, WooCommerce, MariaDB/MySQL, WP-CLI, Mailpit et Xdebug sont nécessaires selon le CDC. Ajouter WP-CLI Doctor, gettext et les outils de test WordPress. Le PHP et Composer doivent être utilisés dans DDEV, pas depuis l’hôte, afin que les tests restent reproductibles.

### À installer dans le dépôt Node

TypeScript, ESLint, Prettier, Stylelint, Vitest, Playwright Test, axe-core, Lighthouse CI, `html-validate`, `rtlcss`, `@wordpress/scripts` si des blocs sont construits et un outil de comparaison visuelle si les snapshots sont activés. Le dépôt doit épingler les versions et committer son lockfile.

### À installer en outils de développement temporaires

Query Monitor, User Switching, WP Crontrol et WPScan sont les ajouts les plus utiles pour un test réaliste du WordPress vierge. OWASP ZAP et k6 seront ajoutés lorsque les routes de commande et le dashboard seront disponibles.

## Verdict final

**Oui, le pack CDC est validé.** Il ne faut pas ajouter une grande quantité d’outils simplement pour donner une impression de maturité. Les ajouts réellement utiles sont : **WP-CLI Doctor, Query Monitor, User Switching, WP Crontrol, WPScan, gettext, rtlcss, html-validate et @wordpress/scripts**. Ensuite seulement, selon les versions, **OWASP ZAP, k6 et la régression visuelle**.

Le point le plus important n’est pas l’installation d’un outil supplémentaire : c’est la séparation entre **sandbox de préparation**, **DDEV canonique** et **WordPress vierge réel**. La sandbox actuelle ne possède pas Docker, DDEV, PHP ni Composer ; il faudra donc exécuter DDEV dans un environnement Docker-capable. Je peux continuer à préparer et tester le front dans la sandbox, mais la validation WooCommerce/HPOS/migration doit rester sur DDEV et être confirmée sur ton WordPress réel de test.

## Références

[1]: https://make.wordpress.org/cli/handbook/guides/doctor/ "WP-CLI — Doctor Guides"

[2]: https://wordpress.org/plugins/query-monitor/ "Query Monitor — WordPress.org"

[3]: https://wpscan.com/wordpress-cli-scanner/ "WPScan — WordPress CLI Scanner"

[4]: https://docs.ddev.com/en/stable/users/quickstart/ "DDEV Documentation — CMS Quickstarts"

[5]: https://playwright.dev/docs/intro "Playwright Documentation — Installation and Test Reports"

[6]: https://developer.woocommerce.com/docs/features/orders/high-performance-order-storage/ "WooCommerce Developer Documentation — High-Performance Order Storage"

[7]: https://developer.wordpress.org/plugins/ "WordPress Plugin Developer Handbook"
