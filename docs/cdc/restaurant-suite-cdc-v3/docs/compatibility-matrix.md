# Matrice de compatibilité initiale

| Composant | Support principal | Release étendue | Notes |
|---|---|---|---|
| PHP | 8.2 et 8.3 | Version suivante supportée après test | PHP du projet dans DDEV |
| WordPress | Version fixée dans lock/CI | Version mineure suivante | Pas de `latest` |
| WooCommerce | Version fixée dans lock/CI | HPOS activé en release | APIs CRUD obligatoires |
| Elementor | Actif et désactivé | — | Fonctionnement sans dépendance métier |
| Panier/Checkout blocs | Reportés | Annoncés seulement après tests | Ne pas supposer la compatibilité fragments |
| Navigateurs | Chromium desktop/mobile en PR | Firefox/WebKit en release | BrowserStack optionnel |
| Hébergement | DDEV + staging réel | — | Cache, HTTPS, cron et email comparés |
