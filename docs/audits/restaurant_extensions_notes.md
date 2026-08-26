# Analyse des extensions — notes de travail

## Inventaire confirmé

L’installation comporte 12 extensions : Elementor 4.2.3, Essential Addons for Elementor 6.7.3, Food Menu - Restaurant Menu & Online Ordering for WooCommerce 6.0.4 (inactive), Hostinger Tools 3.0.77, LiteSpeed Cache 7.9, OneClick Chat to Order 1.1.2, Reno Quick View 2.3.0, Restaurant Owner Dashboard 1.0.0 (Custom), Royal Elementor Addons 1.7.1066, Side Cart WooCommerce 2.8.0, WooCommerce 10.6.1 et WPCode Lite 2.3.8. Onze sont actives et une est inactive.

## Dashboard Restaurant Owner Dashboard

Le dashboard est une extension personnalisée en version 1.0.0. Son écran visible est « Restaurant Dashboard — Réglages » et comporte des onglets Design, Logo, Compte et Sécurité. Les réglages visibles comprennent le nom du restaurant « Chef Anass », la devise MAD, plusieurs couleurs, les polices des titres/prix et des textes/boutons, ainsi qu’un aperçu d’interface qui semble afficher un catalogue générique de démonstration avec pizzas et desserts.

Le dashboard paraît donc être surtout une interface de gestion front-end personnalisée, mais son aperçu n’est pas cohérent avec le catalogue Chef Anass. Il faut vérifier ses écrans de gestion réels, ses permissions et sa capacité à gérer commandes, stocks, variations et utilisateurs avant de le choisir comme outil opérationnel principal.

## Test fonctionnel du dashboard

La page front-end `/dashboard/` affiche 22 produits, 22 actifs et 0 désactivé. Le filtre Pâtes retrouve correctement les six produits de cette catégorie, ce qui confirme que les produits existent bien et que le défaut « No Products Found! » se situe dans les pages publiques/Menu, pas dans le catalogue WooCommerce ni dans le dashboard.

Le dashboard affiche aussi des catégories inutilisées ou vides : Penne 0, Suppléments 0 et Tagliatelle 0, malgré des produits de pâtes correspondants. Ces catégories semblent être des taxonomies supplémentaires ou mal utilisées. Elles devraient être regroupées ou nettoyées pour éviter la confusion.

L’édition d’un produit permet de changer la photo, le nom, le prix, le statut, les catégories, la description courte, la description complète et l’état de stock. Elle ne montre pas de gestion visible des attributs, variations, options de sauce, prix variables, taxes, SKU, stock avancé, commande ou préparation. Pour Chef Anass, elle est pratique pour cacher rapidement un produit ou corriger un prix, mais elle ne remplace pas une vraie console de commandes et de cuisine.

L’aperçu de configuration du dashboard utilise des produits génériques « Pizza Margherita » et « Fondant Chocolat », alors que l’interface réelle charge Chef Anass. Cela ressemble à un aperçu de démonstration non personnalisé et réduit la confiance lors de la configuration.

## Sources externes vérifiées

- WPCafe WordPress.org : https://wordpress.org/plugins/wp-cafe/ — version publiée affichée 3.0.18, dernière modification indiquée le 16 août 2026. La page décrit un menu responsive, commandes WooCommerce, livraison/retrait avec créneaux, réservations, QR ordering, alertes cuisine, rapports, add-ons, multi-sites/branches et dashboard. WooCommerce est requis pour la commande en ligne. Certaines fonctions avancées sont Pro.
- WPCafe éditeur : https://themewinter.com/wp-cafe/ — annonce des templates de menu, mini-panier, add-ons, commandes, réservations, livraison/retrait, QR, notifications, rapports et dashboard front-end ; l’éditeur annonce des plans payants à partir de 89 USD/an pour 2 domaines et des fonctions Pro.
- Food Menu Pro / RadiusTheme : https://www.radiustheme.com/downloads/food-menu-pro-wordpress/ — annonce intégration WooCommerce, plus de 20 layouts, mini-cart, add-ons, livraison/retrait, réservation visuelle, QR, statuts de commande, dashboard front-end, inventaire avancé, kitchen monitor, impression POS, notifications SMS/WhatsApp et rôles. La page annonce 49 USD/an pour 1 site ou 143 USD en licence lifetime au moment de la consultation ; les prix et fonctions doivent être revérifiés avant achat.
- Orderable : https://orderable.com/ — annonce horaires, livraison/retrait avec créneaux, dine-in par QR, add-ons, statuts, notifications, impression de tickets, multi-sites et intégration WooCommerce. L’offre est orientée commande complète plutôt que simple menu WhatsApp.
- Restaurant for WooCommerce : https://woocommerce.com/products/restaurant-for-woocommerce/ — extension WooCommerce payante annoncée à 149 USD/an, avec menu, dashboard de performance, add-ons, livraison, horaires, mini-cart, QR, pickup/dine-in et créneaux. La page indique 300+ installations actives, note 3,9/5 sur 27 avis et compatibilité testée avec WooCommerce 10.9.4 ; elle n’est pas nécessairement adaptée à un fonctionnement WhatsApp-only.
- WooCommerce Mobile : https://woocommerce.com/mobile/ — application officielle pour créer des produits, gérer les commandes et consulter les indicateurs depuis iOS/Android. Elle peut remplacer une partie du besoin de dashboard si les commandes sont réellement enregistrées dans WooCommerce, mais elle ne remplace pas l’édition front-end personnalisée du site.
- OneClick Chat to Order : https://wordpress.org/plugins/oneclick-whatsapp-order/ — version 1.1.2, dernière modification affichée le 26 mai 2026. La page annonce numéros multiples, boutons produit/boutique/panier, messages personnalisés, variations, floating button, GDPR, compatibilité HPOS et support WooCommerce. Elle mentionne aussi des correctifs de sécurité dans les versions 1.1.0 et 1.0.9 ; la version installée du site correspond à 1.1.2.
- Royal Elementor Addons : https://royal-elementor-addons.com/ — annonce plus de 100 widgets, WooCommerce builder, filtres, mega menu, formulaires, popups, Google Maps et theme builder. Les pages du site montrent de nombreuses classes `wpr-`, ce qui indique un usage réel de Royal Addons.
- Essential Addons : https://essential-addons.com/ — annonce plus de 100 widgets et 20+ widgets WooCommerce, mais le contrôle des contenus de pages du site n’a détecté aucune occurrence `eael-`. Cela suggère que l’extension est probablement installée mais non utilisée sur les pages publiées, à confirmer avant désactivation.

## Sécurité affichée par le dashboard

L’onglet Sécurité affiche HTTPS activé, WP_DEBUG désactivé, WordPress récent selon son propre contrôle (il affiche v6.9.7 alors que l’écran WordPress signale une mise à jour vers 7.1), rôle owner sans accès wp-admin, dossier uploads protégé par .htaccess, nonces CSRF, rate limiting AJAX annoncé à 60 requêtes/minute/IP et validation MIME réelle des images. Ces protections sont positives, mais elles sont des affirmations du plugin personnalisé ; elles ne remplacent pas une vérification indépendante de la configuration serveur, des rôles et des sauvegardes. Le message interne indiquant « WordPress récent » est incohérent avec l’avertissement de mise à jour visible dans wp-admin.

Le dashboard est donc prometteur pour la gestion rapide des produits, mais il ne présente pas de commandes, de tickets cuisine, de statuts, de rapports, de rôles détaillés ou de workflow de livraison. Il ne devrait pas être considéré comme un système de gestion restaurant complet sans développement supplémentaire.
