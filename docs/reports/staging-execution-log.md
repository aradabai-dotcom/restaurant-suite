# Journal d’exécution du staging

## 26 août 2026 — baseline et activation WooCommerce

L’administration du staging `lightpink-cat-987595.hostingersite.com` est accessible avec la session administrateur déjà ouverte. Le site était vierge, avec WordPress 7.1, le thème Twenty Twenty-Five et les extensions Hostinger. WooCommerce 11.0.1 a été installé depuis le répertoire officiel WordPress puis activé. Le tableau de bord WooCommerce est maintenant visible ; aucune commande ni aucun produit n’existe encore. La boutique reste en mode « Boutique bientôt disponible » afin d’éviter toute exposition commerciale pendant les tests.

## 26 août 2026 — dépendances gratuites de test

Elementor 4.2.3 a été installé depuis le répertoire officiel WordPress et activé. Query Monitor 4.0.7 a été installé et activé pour observer les requêtes, hooks, erreurs et temps de réponse pendant les tests. User Switching 1.12.1 a été installé et activé afin de permettre ultérieurement les contrôles de permissions par rôle métier. Ces extensions sont des outils de staging et ne constituent pas des dépendances fonctionnelles de Restaurant Suite.

Aucun paiement, aucune commande réelle, aucune donnée de production et aucun service externe irréversible n’a été configuré. Les créations de données de test et le déploiement du package Restaurant Suite restent à documenter après exécution effective.

## 26 août 2026 — baseline technique WooCommerce

L’écran d’état WooCommerce confirme la baseline suivante : WordPress 7.1, WooCommerce 11.0.1, PHP 8.3.30, serveur LiteSpeed sous Linux x86_64, MariaDB 11.8.8, mémoire WordPress 512 Mo, `post_max_size` 256 Mo, téléversement maximal 256 Mo, limite d’exécution PHP 300 secondes et 5 000 variables d’entrée. Le site n’est pas multisite, la langue est `fr_FR`, le type d’environnement déclaré par WooCommerce est `production` malgré son usage de staging, et le débogage WordPress n’est pas activé.

La base de données WooCommerce utilise le préfixe `wp_`, avec une taille totale observée de 3,62 Mo. L’écran d’état indique que le cache objet externe est actif. Les données du catalogue et des commandes sont encore vides ; aucune commande ni aucun produit de production n’a été importé. L’écran des fonctionnalités indique notamment HPOS parmi les fonctionnalités WooCommerce disponibles ; l’état détaillé du stockage des commandes doit être recontrôlé après l’installation de Restaurant Suite et la création de fixtures synthétiques.

Le rapport contient uniquement des caractéristiques techniques non sensibles ; aucun chemin d’administration avec nonce, identifiant de connexion, adresse de client ou donnée de commande n’est archivé.

## 26 août 2026 — premier déploiement du package propriétaire

Le ZIP `restaurant-suite-core-0.0.1.zip` a été téléversé via l’installateur WordPress. L’installation a été confirmée, puis le plugin a été activé depuis l’administration sans erreur fatale ni écran de récupération. Le code installé correspond au squelette de phase 0.0 : il enregistre les contrats et ne déclare encore aucun menu, panier, Quick View, commande WhatsApp ou dashboard public.

La structure racine du ZIP a été acceptée par WordPress après la correction du script de packaging. Le thème n’a pas encore été téléversé ; il sera déployé après ajout des éléments minimaux nécessaires au rendu et au test Elementor.
