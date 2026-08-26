# Addendum — WordPress vierge réel dédié aux tests

**Décision :** validé comme environnement complémentaire du CDC.  
**Rôle :** staging technique hébergé, distinct de la sandbox et de DDEV.  
**Interdiction :** aucune donnée client, commande réelle ou secret de production.

## 1. Verdict

L’existence d’un site WordPress vierge créé uniquement pour tester Restaurant Suite renforce nettement le dispositif. Il permet de valider les éléments qu’un environnement local ne reproduit pas toujours fidèlement : HTTPS réel, serveur web, cache LiteSpeed/Hostinger, cron, permissions de fichiers, limites PHP, emails, compression, proxy, configuration DNS et comportement mobile sur une URL accessible.

Ce site ne doit pas devenir le seul environnement de référence. DDEV reste la base réinitialisable pour les tests déterministes et le WordPress vierge réel devient la preuve de compatibilité avec l’hébergement cible.

## 2. Matrice des environnements

| Environnement | Finalité | Données | Outils |
|---|---|---|---|
| Sandbox de préparation | Front, scripts, analyse, packaging, smoke HTTP | Fichiers et fixtures non sensibles | Node/pnpm, Chromium, Git, curl, jq, ZIP |
| DDEV local | PHP, WP, WooCommerce, HPOS, panier, commandes, fixtures et migration | Données synthétiques réinitialisables | Docker, DDEV, PHP, Composer, WP-CLI, MariaDB, Mailpit, Xdebug |
| WordPress vierge réel | Hébergement, cache, HTTPS, cron, email test, permissions, régression | Données de test uniquement | WordPress, WooCommerce, package candidat, Query Monitor temporaire, User Switching, WP Crontrol, WPScan autorisé |
| CI | Validation reproductible PR/release | Fixtures CI | Composer, PHPStan, PHPCS, PHPUnit, Node, Playwright, axe, Lighthouse, audits |

## 3. Préparation obligatoire du site réel

Le site doit avoir un nom clairement identifié comme staging, une authentification forte pour l’administration, une sauvegarde restaurable, une adresse email de test, des comptes de test par rôle et une procédure de reset. Les extensions concurrentes ne sont installées que pour une campagne de comparaison ; elles ne doivent jamais être activées simultanément avec le module remplacé sur la même page.

Avant chaque campagne, relever PHP, WordPress, WooCommerce, serveur web, extensions PHP, mémoire, upload, cron, HTTPS, timezone, locale, cache, HPOS, plugins actifs et version du package. Ce relevé est enregistré dans `reports/staging/<date>/environment.md`.

## 4. Outils temporaires autorisés

| Outil | Campagne | Règle |
|---|---|---|
| Query Monitor | V0.1 à V1.0 | Inspection des requêtes, hooks, erreurs, scripts, AJAX et capacités ; retirer avant livraison |
| User Switching | V0.5 à V1.0 | Audit manuel des rôles ; un test Playwright indépendant reste obligatoire |
| WP Crontrol | V0.4 à V1.0 | Contrôle des tâches cron et Scheduled Actions |
| WP-CLI Doctor | 0.0, 0.7, 1.0 | Diagnostics versionnés de core, plugins, thèmes et réglages |
| WPScan | Avant release | Scan strictement autorisé, limité à la cible de test |
| OWASP ZAP | Après existence des routes | DAST ciblé avec fenêtre approuvée, jamais agressif par défaut |
| k6 | Avant V1.0 si nécessaire | Charge contrôlée sur staging autorisé, avec seuils et arrêt d’urgence |

Aucun de ces outils ne doit entrer dans le ZIP client. Ils ne sont pas des dépendances de production.

## 5. Campagnes par version

| Version | Tests supplémentaires sur le site réel |
|---|---|
| 0.1 | Installation ZIP, activation, menu, Elementor actif/désactivé, HTTPS et assets conditionnels |
| 0.2 | Quick View, cache contourné/actif, mobile, clavier, erreurs réseau et rendu réel |
| 0.3 | Cart Drawer, session invité, cache, fragments, retour arrière et panier mobile |
| 0.4 | Commande test, idempotence, email test, cron, statut et relance WhatsApp |
| 0.5 | Comptes par rôle, accès croisés, changements de statut et disponibilité produit |
| 0.6 | Thème natif, Elementor, templates, HTML initial, SEO technique, RTL si annoncé |
| 0.7 | Assistant, import/export, sauvegarde, mise à jour et rollback sans perte |
| 1.0 | Matrice complète, retrait progressif, audit sécurité, performance et répétition du rollback |

## 6. Règles de sécurité et confidentialité

Le site doit être exclu des moteurs de recherche et clairement marqué staging. Les téléphones, adresses et emails sont synthétiques. Les exports publics ne contiennent ni secret ni données personnelles. Toute URL de scan ou de charge doit être autorisée par le propriétaire du site. Les rapports ne doivent pas inclure de mots de passe, cookies, tokens ou messages WhatsApp complets.

## 7. Critères de validation du staging

Le staging est validé lorsqu’il accepte l’installation et la mise à jour du package, que le menu et le panier fonctionnent en HTTPS, que les pages dynamiques ne servent pas de réponses obsolètes depuis le cache, que les emails de test arrivent, que cron et permissions sont corrects, que les rôles respectent la matrice et qu’un rollback est démontré. Toute divergence entre DDEV et le site réel est documentée et classée comme défaut, limitation d’hébergement ou incompatibilité supportée.
