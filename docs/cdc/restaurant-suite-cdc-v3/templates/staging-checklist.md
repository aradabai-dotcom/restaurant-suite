# Checklist staging — WordPress vierge réel

**Campagne :**  
**Version du package :**  
**Date :**  
**Responsable :**  
**URL staging :**  
**Sauvegarde avant campagne :**  

## Identification de l’environnement

| Contrôle | Valeur | Conforme |
|---|---|---|
| PHP |  | Oui / Non |
| WordPress |  | Oui / Non |
| WooCommerce |  | Oui / Non |
| MariaDB/MySQL |  | Oui / Non |
| Serveur web |  | Oui / Non |
| Extensions PHP |  | Oui / Non |
| `memory_limit` |  | Oui / Non |
| `upload_max_filesize` |  | Oui / Non |
| Cron |  | Oui / Non |
| HTTPS |  | Oui / Non |
| Timezone/locale |  | Oui / Non |
| Cache |  | Oui / Non |
| HPOS |  | Oui / Non |
| Elementor |  | Oui / Non |

## Sécurité et hygiène

Le site est identifié comme staging, exclu des moteurs de recherche et protégé par des comptes de test. Aucune donnée réelle, commande réelle, clé de production ou export sensible n’est présente. Les sauvegardes et rapports sont stockés hors du package client.

## Installation et smoke test

| Test | Résultat | Preuve |
|---|---|---|
| Installation du ZIP plugin | Pass / Fail |  |
| Installation du ZIP thème | Pass / Fail |  |
| Activation/désactivation | Pass / Fail |  |
| Menu public | Pass / Fail |  |
| Produit simple | Pass / Fail |  |
| Produit variable | Pass / Fail |  |
| Quick View | Pass / Fail |  |
| Cart Drawer | Pass / Fail |  |
| Commande test | Pass / Fail |  |
| Email de test | Pass / Fail |  |
| Dashboard par rôle | Pass / Fail |  |
| Import/export | Pass / Fail |  |
| Rollback | Pass / Fail |  |

## Cache, cron et réseau

Tester avec cache actif, cache contourné, une réponse lente, une perte réseau contrôlée et une actualisation après mutation. Vérifier que le panier, checkout, dashboard, AJAX/REST et statuts ne servent pas une réponse obsolète. Vérifier les tâches cron et Scheduled Actions avec WP Crontrol si installé.

## Rôles

Créer au minimum propriétaire, manager, cuisine et livreur. Vérifier la visibilité des menus, la lecture des commandes, les transitions, l’accès aux coordonnées et l’impossibilité de modifier le prix ou les réglages non autorisés. User Switching peut faciliter la revue, mais les tests automatisés ne doivent pas en dépendre.

## Audit temporaire

Query Monitor, WP-CLI Doctor et WPScan sont exécutés selon le périmètre. OWASP ZAP et k6 exigent une autorisation et une fenêtre de test. Retirer les outils temporaires avant le package final.

## Décision

| Décision | Valeur |
|---|---|
| Défauts bloquants ouverts |  |
| Défauts majeurs ouverts |  |
| Rollback démontré | Oui / Non |
| Validation technique | Nom / date |
| Validation fonctionnelle | Nom / date |
| Passage autorisé | Oui / Non |
