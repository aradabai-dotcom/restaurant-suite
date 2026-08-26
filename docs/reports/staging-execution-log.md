# Journal d’exécution du staging

## 26 août 2026 — baseline et activation WooCommerce

L’administration du staging `lightpink-cat-987595.hostingersite.com` est accessible avec la session administrateur déjà ouverte. Le site était vierge, avec WordPress 7.1, le thème Twenty Twenty-Five et les extensions Hostinger. WooCommerce 11.0.1 a été installé depuis le répertoire officiel WordPress puis activé. Le tableau de bord WooCommerce est maintenant visible ; aucune commande ni aucun produit n’existe encore. La boutique reste en mode « Boutique bientôt disponible » afin d’éviter toute exposition commerciale pendant les tests.

## 26 août 2026 — dépendances gratuites de test

Elementor 4.2.3 a été installé depuis le répertoire officiel WordPress et activé. Query Monitor 4.0.7 a été installé et activé pour observer les requêtes, hooks, erreurs et temps de réponse pendant les tests. User Switching 1.12.1 a été installé et activé afin de permettre ultérieurement les contrôles de permissions par rôle métier. Ces extensions sont des outils de staging et ne constituent pas des dépendances fonctionnelles de Restaurant Suite.

Aucun paiement, aucune commande réelle, aucune donnée de production et aucun service externe irréversible n’a été configuré. Les créations de données de test et le déploiement du package Restaurant Suite restent à documenter après exécution effective.
