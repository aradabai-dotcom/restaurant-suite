# Rapport d’exécution — phase 0.0

**Date :** 26 août 2026
**Environnement :** sandbox Manus, Ubuntu, PHP 8.3.6, Composer 2.7.1, Node/pnpm 22/11.24.0
**Dépôt :** `aradabai-dotcom/restaurant-suite`

## Statut

La matérialisation documentaire et le squelette technique de la phase 0.0 sont en place. Les contrôles disponibles hors DDEV sont verts. La validation d’installation WordPress/WooCommerce, HPOS, fixtures et CI intégration reste à exécuter dans un environnement Docker/DDEV-capable.

## Contrôles exécutés

| Contrôle | Résultat | Preuve |
|---|---|---|
| Arborescence canonique | Vert | `.ddev`, plugin, thème, scripts, tests, docs et workflows présents |
| Syntaxe Bash | Vert | `bash -n scripts/*.sh` |
| Manifeste Composer | Vert | `composer validate --strict` |
| Lockfile Composer | Vert | `composer.lock` généré et installation réussie |
| Lockfile Node | Vert | `pnpm-lock.yaml` généré et `pnpm install --frozen-lockfile` réussi |
| PHP Parallel Lint | Vert | 7 fichiers, aucune erreur de syntaxe |
| PHPCS | Vert | `composer cs` avec `phpcs.xml.dist` |
| PHPStan | Vert | `composer stan`, aucune erreur |
| PHPUnit | Vert | 3 tests, 6 assertions |
| Contrats PHP | Vert | JSON et transitions valides |
| ESLint/Stylelint | Vert | `pnpm run lint` |
| Prettier | Vert | `pnpm run format:check` |
| Build Node | Vert | `pnpm run build` |
| Vitest | Vert | 1 fichier, 2 tests |
| Contrats JavaScript | Vert | `pnpm run contracts` |
| Packaging | Vert | `make package`, ZIP plugin/thème, manifeste et checksum |
| `make doctor` | Bloqué attendu | Docker et DDEV absents dans la sandbox |
| Installation WP/WC/HPOS | Non exécuté | Environnement Docker/DDEV requis |
| E2E Playwright | Non exécuté | WordPress local non démarré |
| ZAP/k6/staging réel | Non exécuté | URL staging à autoriser et configurer |

## Corrections réalisées pendant l’exécution

La classe de contrat a été ajoutée pour empêcher la validation de réussir sans code propriétaire analysé. Les tests PHPUnit ont été rendus découvrables sans abandonner les conventions WordPress. La configuration PHPCS exclut explicitement les tests PHPUnit de la convention de nommage des fichiers, et le manifeste Node limite ESLint/Stylelint aux fichiers du projet afin de ne pas analyser les dépendances tierces.

Le Makefile appelle maintenant des commandes existantes, inclut les tests PHP/JS et le contrôle de formatage. Le script de packaging échoue si le plugin ou le thème n’existe pas et produit des checksums. Les fixtures sont présentes comme contrat de phase et doivent encore être testées dans WordPress/WooCommerce réel.

## Blocage restant

La sandbox ne possède pas Docker ni DDEV. Le gate local complet ne peut donc pas encore être déclaré vert. Le prochain environnement d’exécution doit fournir Docker, DDEV, WordPress, WooCommerce et une base MariaDB/MySQL. Après démarrage, il faudra exécuter :

```bash
make install
CRS_ALLOW_FIXTURE_RESET=1 make reset
make doctor
make validate
make package
```

Puis archiver les logs et répéter l’installation du package sur le WordPress vierge réel.

## Décision

**Phase 0.0 — code et contrats : partiellement validée.**
**Gate de matérialisation DDEV : en attente d’un environnement Docker/DDEV-capable.**
**V0.1 : interdite tant que le gate DDEV et les contrats métier ne sont pas approuvés par écrit.**

## Revalidation après corrections — 26 août 2026

La commande `make validate && make package` a été relancée après le durcissement du bootstrap. Les contrôles Composer, lint PHP, PHPCS, PHPStan, PHPUnit, contrats JSON/PHP/JavaScript, build Node, ESLint, Stylelint, Vitest et packaging restent verts. PHPStan affiche uniquement son avertissement informatif concernant l’ancienne série 1.12 ; aucune erreur d’analyse n’est présente.

Le script `scripts/package-release.sh` a été corrigé afin que les ZIP aient directement les fichiers du plugin ou du thème à leur racine, ce qui correspond au format attendu par l’installeur WordPress. Les tests PHPUnit, `phpunit.xml.dist` et les caches de résultats sont désormais exclus des artefacts. La structure des deux ZIP a été contrôlée avec `unzip -l`.

Cette revalidation ne change pas la limite principale : Docker et DDEV ne sont toujours pas disponibles dans la sandbox. La phase 0.0 est donc validée pour le code, les contrats, l’outillage et le packaging local, mais pas pour l’installation WordPress/DDEV, HPOS, les fixtures WooCommerce, l’E2E ou la CI d’intégration.
