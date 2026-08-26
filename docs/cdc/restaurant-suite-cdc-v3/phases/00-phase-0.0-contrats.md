# CDC-00 — Phase 0.0 : contrats de conception obligatoires

**Statut cible :** validable avant tout développement métier.  
**Dépendances :** dépôt Git initialisé et stack locale décrite dans `tooling/CDC-00-outillage-local.md`.  
**Sortie :** contrats versionnés et approuvés.

## 1. Objectif

La phase 0.0 transforme la roadmap en contrats vérifiables. Elle doit empêcher la duplication du catalogue, les statuts incohérents, les accès croisés entre rôles, les événements JavaScript concurrents et les commandes WhatsApp dupliquées. Aucun module public ne sera développé tant que les livrables de cette phase n’existent pas dans le dépôt.

## 2. Périmètre

La phase comprend le contrat de données, le contrat des statuts de commande, le contrat d’idempotence WhatsApp, la matrice de compatibilité, la matrice de permissions, le contrat d’événements JavaScript, la liste des hooks WooCommerce et la matrice de tests. Elle ne comprend pas l’implémentation fonctionnelle du menu, du Quick View, du panier ou du dashboard.

## 3. Décisions obligatoires

WooCommerce est la source de vérité pour les produits, catégories, variations, prix, stock et commandes. La V0.1 supporte les produits simples et les variations natives WooCommerce ; elle ne comporte aucun système propriétaire d’options tarifées. Une remarque libre ne peut jamais modifier le prix. Les allergènes sont des métadonnées validées et affichées côté serveur. Les horaires et réglages restaurant sont stockés dans une option versionnée `crs_settings`.

Les identifiants internes des statuts restent stables même si leurs libellés sont personnalisables. Les transitions sont contrôlées par capacité et ne modifient jamais le montant de la commande. Le mécanisme d’idempotence reçoit une clé unique par tentative et renvoie la commande existante si la même requête est répétée.

## 4. Livrables attendus

| Livrable | Contenu minimal | Critère d’acceptation |
|---|---|---|
| Contrat de données | Propriétaire, stockage, format, lecture/écriture, migration | Aucun champ métier critique sans propriétaire |
| Contrat des statuts | Identifiants, libellés, transitions, capacités | Graphe sans transition implicite |
| Contrat d’idempotence | Clé, durée de conservation, réponse répétée, erreurs | Double requête formellement couverte |
| Matrice de compatibilité | Versions PHP/WP/WC, HPOS, Elementor, blocs | Versions épinglées et testables |
| Matrice de permissions | Rôle × ressource × action | Aucun endpoint sans capacité associée |
| Contrat JS | Store, événements, payloads, erreurs | Un seul propriétaire par état |
| Catalogue de hooks | Hooks WP/WC utilisés, raison et priorité | Aucun hook privé non justifié |
| Matrice de tests | Tests unitaires, intégration, E2E, a11y et sécurité | Chaque critère de roadmap possède un test |

## 5. Contrat d’événements

Chaque événement doit documenter son nom, son producteur, son consommateur, son payload, son caractère annulable, son comportement en erreur et sa compatibilité future. Les noms de référence sont `crs:cart:add`, `crs:cart:update`, `crs:cart:remove`, `crs:cart:refresh`, `crs:quickview:open`, `crs:quickview:close` et `crs:order:created`. Le payload ne doit jamais contenir un prix considéré comme fiable ; le serveur reste l’autorité.

## 6. Hooks et APIs

Le catalogue devra distinguer les hooks publics WordPress/WooCommerce, les APIs CRUD WooCommerce, les endpoints REST ou AJAX du plugin et les fonctions internes. Toute lecture de commande doit passer par les objets CRUD WooCommerce. Les requêtes SQL directes sur les tables de commandes sont interdites. Toute action mutante devra déclarer sa capacité, son nonce, sa validation, son échappement et son journal de résultat.

## 7. Tests de la phase

Les contrôles de sortie sont documentaires et automatisés. `composer validate --strict`, PHP Parallel Lint, PHPCS, PHPStan et la validation du schéma JSON des contrats doivent réussir. Un test PHPUnit doit vérifier que les identifiants de statuts et d’événements ne changent pas par inadvertance. Un test de matrice doit détecter tout endpoint ne possédant pas de règle de permission.

## 8. Definition of Done

La phase est terminée lorsque les six livrables exigés par la roadmap existent, sont relus, versionnés et référencés dans `docs/architecture/`. Les décisions ouvertes sont soit supprimées, soit accompagnées d’une date et d’une version cible. Le dépôt doit passer les contrôles de qualité sans code métier incomplet. Une personne autre que l’auteur doit pouvoir comprendre comment un produit, une commande, un statut et un événement sont représentés.

## 9. Gate de passage

Le passage en V0.1 est autorisé uniquement si le lead developer confirme par écrit que la duplication des données commerciales est interdite, que les options tarifées sont hors V0.1, que les permissions et statuts sont déterminés, que l’idempotence est testable et que la stratégie de compatibilité WooCommerce/HPOS est explicite. En cas d’échec, la phase revient en correction ; aucun contournement dans le code n’est accepté.

## 10. Rollback

Le rollback consiste à supprimer les contrats non approuvés de la branche de travail et à conserver la dernière version approuvée. Aucun changement de base ou de site de staging ne doit être effectué pendant cette phase.

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

