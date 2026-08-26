# Améliorations prioritaires du pack CDC — Restaurant Suite

## Verdict général

Le pack CDC est déjà solide. Les améliorations nécessaires concernent surtout sa **mise en œuvre exécutable**, la réduction des ambiguïtés et la preuve que les scripts et la CI fonctionnent réellement. Il ne faut pas refaire l’architecture ; il faut transformer les bonnes intentions du CDC en artefacts vérifiables.

## Priorité P0 — Obligatoire avant la V0.1

| Priorité | Amélioration | Pourquoi c’est important | Preuve attendue |
|---|---|---|---|
| P0.1 | Ajouter `bootstrap-local.sh` | DDEV doit pouvoir installer WordPress, WooCommerce, le thème, le plugin et les fixtures depuis zéro | Une installation propre réussie avec une seule commande |
| P0.2 | Ajouter les scripts référencés | `reset-fixtures.sh` et `package-release.sh` sont appelés par Makefile/CI mais absents du pack | Scripts présents, documentés et exécutables |
| P0.3 | Corriger la CI DDEV | Le workflow appelle `ddev start` sans installer/configurer DDEV ni initialiser WordPress | CI verte sur un runner propre |
| P0.4 | Générer les lockfiles | Les versions doivent être reproductibles et non dépendantes de `latest` | `composer.lock` et lockfile Node versionnés |
| P0.5 | Créer la matrice de compatibilité | Les versions PHP, WordPress, WooCommerce, Elementor et navigateurs doivent être explicites | `docs/compatibility-matrix.md` rempli |
| P0.6 | Finaliser les six contrats de phase 0.0 | Les données, statuts, permissions, idempotence, événements et hooks doivent être figés avant le code | Validation écrite du gate 0.0 |
| P0.7 | Définir les erreurs et logs | Les erreurs réseau, double clic, produit indisponible et commande créée sans WhatsApp doivent être traçables | Catalogue d’erreurs et logs nettoyés |

### Correction CI indispensable

Le workflow actuel est une bonne structure, mais il doit préparer l’environnement avant `ddev start`. Il doit installer ou appeler une action DDEV fiable, vérifier Docker, créer la configuration DDEV, installer WordPress et WooCommerce, activer les composants, charger les fixtures, puis lancer Playwright. Sinon, `ddev start` risque d’échouer sur un runner qui ne connaît pas DDEV.

La CI doit également vérifier les scripts et dossiers nécessaires avant de lancer les tests. Une CI senior doit échouer avec un message explicite, par exemple « `.ddev/config.yaml absent » ou « `scripts/reset-fixtures.sh` absent », plutôt que produire une erreur peu lisible au milieu de Playwright.

## Priorité P0 — Contrats métier à préciser

### Options et suppléments

La V0.1 doit rester limitée aux produits simples et aux variations WooCommerce natives. Une option qui modifie le prix ou le stock devra être une variation ou un produit associé ; une remarque libre ne pourra jamais modifier le prix ; un allergène sera descriptif et rendu côté serveur. Les options combinatoires doivent être repoussées tant que leur modèle n’est pas validé.

### Statuts

Les identifiants internes doivent rester stables, même si les libellés sont personnalisables. Les transitions autorisées doivent être écrites dans une table et testées par rôle. Un changement de statut ne doit jamais modifier le montant de commande.

### Idempotence

Chaque tentative WhatsApp doit utiliser une clé unique, vérifiée côté serveur avant création. La répétition doit renvoyer la commande existante. Si la commande est créée mais que WhatsApp ne s’ouvre pas, il faut relancer uniquement le lien WhatsApp, jamais créer une nouvelle commande.

### Données personnelles

Les téléphones, adresses, emails, tokens, cookies et messages WhatsApp complets doivent être absents des logs, screenshots et artefacts CI. Les données de test doivent être synthétiques et les exports publics doivent être filtrés.

## Priorité P1 — À ajouter avant la V0.3/V0.4

| Amélioration | Bénéfice |
|---|---|
| Ajouter des budgets d’assets versionnés | Empêche que Quick View ou Cart Drawer augmente progressivement le poids du menu |
| Ajouter Query Monitor dans la campagne staging | Permet de mesurer requêtes, hooks, scripts, styles, AJAX et appels HTTP par plugin/thème [1] |
| Ajouter WP-CLI Doctor avec checks personnalisés | Automatise les vérifications de core, plugins, thèmes, options, cron et état du site [2] |
| Ajouter un test de deux requêtes concurrentes | Vérifie l’idempotence réelle et le stock limite |
| Ajouter un test de cache actif puis contourné | Vérifie que panier, checkout, dashboard et AJAX ne servent pas une réponse obsolète |
| Ajouter un contrôle HTML | Complète axe-core et Lighthouse sur les templates et composants serveur |
| Ajouter le test Elementor actif/désactivé | Garantit que le plugin métier n’est pas dépendant d’Elementor |
| Ajouter les tests RTL si l’arabe est annoncé | Évite de promettre la compatibilité RTL sans génération et validation CSS |

Le budget de performance devrait être enregistré dans un fichier tel que `docs/performance-budgets.json`. Il doit distinguer page accueil, menu, fiche produit, Cart Drawer et dashboard. Le dashboard peut être plus lourd que le menu public, mais son bundle ne doit jamais être chargé pour un visiteur.

## Priorité P1 — Rendre la CI réellement complète

La CI devrait être divisée en trois niveaux.

| Pipeline | Contenu | Fréquence |
|---|---|---|
| PR rapide | Lint PHP, PHPCS, PHPStan, PHPUnit unitaires, build/lint JS, Vitest et smoke Playwright | Chaque PR |
| PR intégration | DDEV propre, WordPress/WooCommerce, fixtures, PHPUnit intégration, Playwright Chromium/mobile et axe-core | PR touchant plugin, thème ou données |
| Release | Matrice PHP/WP/WC, HPOS, Elementor actif/désactivé, Firefox/WebKit, Lighthouse, WPScan, Gitleaks, audit dépendances, packaging et rollback | Tag de release |

Le workflow doit publier les artefacts d’échec : rapports PHPUnit, logs WordPress, rapport Playwright, traces, screenshots, rapport axe-core, Lighthouse, checksum et ZIP. Les artefacts doivent être nettoyés des données sensibles avant upload.

Le job packaging doit vérifier que le ZIP final ne contient ni `.git`, ni `node_modules`, ni secrets, ni tests sensibles, ni assets promotionnels hérités. Il doit générer un manifeste de versions et un checksum SHA-256.

## Priorité P1 — Améliorer le modèle de dépôt

Le dépôt doit avoir une structure réelle et non seulement une structure décrite :

```text
restaurant-suite/
├── .ddev/
├── plugin/restaurant-suite-core/
├── theme/restaurant-base-theme/
├── scripts/
├── tests/fixtures/
├── tests/e2e/
├── docs/
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json ou pnpm-lock.yaml
├── Makefile
└── README.md
```

Chaque script utilisé par Makefile ou CI doit exister. Chaque commande documentée doit avoir un équivalent `make` ou `ddev`. Le dépôt doit pouvoir être repris par un développeur qui n’a pas écrit le code.

## Priorité P2 — Améliorations de maturité après la V0.5

| Amélioration | Décision |
|---|---|
| Mutation testing avec Infection | Utile après stabilisation des services métier |
| Rector PHP | Utile pour les migrations de syntaxe et versions PHP |
| OWASP ZAP | À lancer quand les routes AJAX/REST et le dashboard existent, uniquement sur cible autorisée |
| k6 | À ajouter si plusieurs commandes simultanées doivent être simulées |
| Snapshots visuels | À limiter aux composants stables, avec approbation manuelle des changements |
| Firefox/WebKit | À exécuter en release si la matrice de compatibilité les annonce |
| Second analyseur PHP | À éviter au départ ; PHPStan + WPCS suffisent pour réduire le bruit |
| BrowserStack | À éviter au départ ; ajouter uniquement si un problème appareil réel est identifié |

## Ce qu’il ne faut pas améliorer

Il ne faut pas ajouter des fonctions uniquement pour rendre le CDC plus impressionnant. Il ne faut pas intégrer simultanément deux runners JavaScript, deux gestionnaires de paquets, plusieurs solutions cache, `wp-env` et DDEV comme références concurrentes.

Il ne faut pas développer le dashboard avant le panier et la commande. Il ne faut pas ajouter des options de produits complexes en V0.1. Il ne faut pas annoncer les blocs Panier/Checkout comme compatibles tant que les tests spécifiques ne sont pas verts.

Il ne faut pas installer Query Monitor, WPScan, User Switching ou WP Crontrol dans le package client. Ce sont des outils d’audit temporaires.

## Checklist de validation avant V0.1

| Contrôle | Bloquant |
|---|---|
| DDEV démarre sur une installation propre | Oui |
| WordPress et WooCommerce sont installés avec versions fixées | Oui |
| Plugin et thème se chargent sans erreur PHP | Oui |
| Fixtures produits simples/variables/hors stock disponibles | Oui |
| `make doctor` fonctionne réellement | Oui |
| `make validate` fonctionne réellement | Oui |
| Composer et Node utilisent des lockfiles | Oui |
| Les six contrats phase 0.0 sont approuvés | Oui |
| CI complète au moins le pipeline PR rapide | Oui |
| Rapport de versions généré | Oui |
| Aucun secret dans le dépôt | Oui |
| Rollback documenté et testé sur staging | Non pour entrer en V0.1, obligatoire avant V1.0 |

## Verdict

Le pack CDC n’a pas besoin d’une nouvelle architecture. Il a besoin d’être **matérialisé**. Les corrections les plus importantes sont la CI DDEV, les scripts manquants, les lockfiles, la matrice de compatibilité et les six contrats phase 0.0.

Une fois ces éléments ajoutés, le pack passera d’un excellent cadre de conception à un **système de développement senior réellement reproductible**. La prochaine action correcte est de créer le dépôt et les scripts de bootstrap, puis de faire passer `make doctor` avant d’écrire le moindre composant métier.

## Références

[1]: https://wordpress.org/plugins/query-monitor/ "Query Monitor — WordPress.org"

[2]: https://make.wordpress.org/cli/handbook/guides/doctor/ "WP-CLI — Doctor Guides"

[3]: https://developer.woocommerce.com/docs/features/orders/high-performance-order-storage/ "WooCommerce — High-Performance Order Storage"

[4]: https://developer.wordpress.org/plugins/ "WordPress Plugin Developer Handbook"
