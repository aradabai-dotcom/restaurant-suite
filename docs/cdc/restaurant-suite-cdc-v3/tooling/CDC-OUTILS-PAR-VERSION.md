# Matrice outillage v2 — installation, exécution et blocage

Cette matrice complète les CDC de phase. Elle évite de repousser l’installation d’un outil jusqu’au moment où la fonctionnalité apparaît, tout en évitant de bloquer une version sur un contrôle qui ne peut pas encore être pertinent.

| Phase | Installation/préparation | Exécutions attendues | Contrôles bloquants |
|---|---|---|---|
| 0.0 | Composer, Node, Playwright, axe, Lighthouse, Infection, Rector, ZAP/k6 si staging disponible, WP-CLI Doctor | `make doctor`, validation contrats, Rector dry-run, diagnostics staging si disponible | Syntaxe, PHPCS, PHPStan, contrats, permissions et versions |
| 0.1 | Fixtures DDEV, Query Monitor staging, ZAP baseline, k6 smoke | Menu public, HTML, requêtes, headers, installation ZIP | Tests PHP/JS/E2E du menu et installation propre |
| 0.2 | Snapshots visuels et Infection ciblé | Quick View, clavier, axe, mutation des services | Accessibilité critique/sérieuse, tests Quick View, budget assets |
| 0.3 | Traces réseau et budgets panier | Cart Drawer, cache, sessions invitées, k6 léger | Cohérence panier, absence de doubles refresh et budget public |
| 0.4 | WP Crontrol et scénarios ZAP ciblés | WhatsApp, commande serveur, idempotence, cron, email test | Prix serveur, commande unique, erreurs et sécurité endpoints |
| 0.5 | User Switching, comptes de rôles et scan authentifié préparé | Dashboard, permissions, statuts et disponibilité | Aucun accès croisé, transitions et permissions |
| 0.6 | RTL et BrowserStack si annoncés | Thème, Elementor on/off, HTML initial, Lighthouse | Fallback, accessibilité, performance et templates |
| 0.7 | Import/export, checks Doctor et rollback | Assistant, migrations, installation sur deux sites | Aucune perte, preview, rollback et checksum |
| 1.0 | Matrice complète CI/release | WPScan, ZAP authentifié, k6 contrôlé, navigateurs étendus | Tous les contrôles annoncés et rollback démontré |

## Règle de sécurité

Les scans et tests de charge ne ciblent que le WordPress vierge du projet ou une URL explicitement autorisée. Les rapports masquent téléphones, emails, adresses, cookies, tokens, URLs signées et messages WhatsApp. Aucun outil d’audit temporaire ne doit entrer dans le ZIP client.

## Références

[1]: https://make.wordpress.org/cli/handbook/guides/doctor/ "WP-CLI Doctor"
[2]: https://wordpress.org/plugins/query-monitor/ "Query Monitor"
[3]: https://wpscan.com/wordpress-cli-scanner/ "WPScan CLI Scanner"
[4]: https://playwright.dev/docs/intro "Playwright"
