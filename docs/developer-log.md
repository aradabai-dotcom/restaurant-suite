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
