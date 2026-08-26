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

## 26 août 2026 — fixtures WooCommerce V0.1

Les fixtures ont été créées via l’API d’administration WooCommerce avec des SKU préfixés `crs-test-`, sans client, adresse, paiement ni commande. La catégorie active est `crs-test-active` (ID 16) et la catégorie vide est `crs-test-empty` (ID 17). Le produit simple achetable est `[CRS TEST] Burger Simple` (ID 16, SKU `crs-test-simple-20260826`, prix 12,50, stock disponible). Le produit hors stock est `[CRS TEST] Plat Hors Stock` (ID 17, SKU `crs-test-oos-20260826`, stock indisponible). Le produit variable est `[CRS TEST] Tacos Variable` (ID 18, SKU `crs-test-variable-20260826`) avec deux variations : ID 19 à 10,00 et ID 20 à 14,00. Les identifiants servent uniquement à la vérification du staging et devront être supprimés avec les fixtures avant toute réutilisation du site.

Deux pages de test ont été créées avec le shortcode propriétaire : `/crs-test-menu/` (ID 21) affiche la catégorie active et `/crs-test-menu-empty/` (ID 22) affiche la catégorie vide. Elles sont destinées exclusivement au staging, ne contiennent aucune donnée client et devront être supprimées avec les fixtures après la campagne de tests.

## 26 août 2026 — vérification menu et panier

La page publique `/crs-test-menu/` affiche bien les trois fixtures de la catégorie active avec leur nom, description, prix WooCommerce, lien produit et états appropriés. Le produit simple affiche « Ajouter au panier », le produit hors stock affiche « Indisponible » sans action d’achat et le produit variable affiche « Voir les options » vers sa fiche. Le produit simple a été ajouté via son lien natif `?add-to-cart=16` ; WooCommerce a confirmé l’ajout et la page Panier a affiché une seule ligne `[CRS TEST] Burger Simple`, quantité 1 et total 12,50. Aucune commande n’a été créée.

La page est encore protégée par le mode « Bientôt disponible » et reste accessible uniquement à la session de test. Le rendu utilise le thème Twenty Twenty-Five ; le thème Restaurant Base Theme n’est pas encore déployé.

Les assertions DOM sur `/crs-test-menu/` passent : conteneur `data-crs-menu` présent, 3 cartes, produit simple, prix, état `Indisponible`, CTA variable et lien `add-to-cart=16` présents. Aucun script Restaurant Suite/CRS n’est chargé par cette V0.1, ce qui confirme le rendu serveur sans dépendance JavaScript métier. `/crs-test-menu-empty/` affiche bien `Aucun plat disponible dans cette catégorie.` avec un conteneur d’état vide.

La page `/crs-test-menu-block/` (ID 23) contenant `restaurant-suite/menu` en bloc dynamique affiche exactement les trois fixtures et les mêmes CTA/états que le shortcode. Le point d’entrée bloc partage donc bien le renderer serveur ; aucune erreur visible ni commande n’a été générée.

## 26 août 2026 — incident d’activation V0.2 et blocage de remplacement

L’activation du premier package `restaurant-suite-core-0.2.0` a produit un fatal sur `CRS\\QuickView\\QuickViewEndpoint` : son autoloader minuscule par erreur le segment de répertoire `QuickView` et cherchait `src/quickview/` sous l’environnement Linux Hostinger. Le correctif a été développé, testé localement et poussé dans le commit `40481cf`.

Le staging a ensuite gardé l’ancien package V0.2.0 actif et le fatal se produit avant le chargement de toutes les pages d’administration. Les tentatives de désactivation directe, d’accès à `plugins.php` et d’accès à `update.php?action=upload-plugin` restent bloquées par le même fatal, car WordPress charge les extensions actives avant ces actions. Aucun produit, panier, commande ou réglage WooCommerce n’a été supprimé. Le package corrigé n’est pas encore installé sur le staging et V0.2 ne doit pas être considérée comme validée sur staging tant que l’ancien package n’est pas désactivé ou remplacé via un accès fichier/hosting ou le mode récupération WordPress.

Après renommage FTP du dossier défectueux en `restaurant-suite-core-0.2.0_disabled`, l’administration WordPress est revenue. WordPress indique que le fichier de l’extension originale n’existe plus et l’a désactivée ; aucune donnée WooCommerce n’a été modifiée. Le déploiement du package corrigé peut reprendre.

Le package V0.2.0 corrigé a été réinstallé après la désactivation FTP de l’ancienne copie, puis activé avec succès. WordPress affiche « Extension activée », sans fatal error. La copie `restaurant-suite-core-0.2.0_disabled` reste hors chargement et les anciennes versions 0.1.0 sont inactives. Les tests fonctionnels Quick View peuvent reprendre sur les pages CRS existantes.

Pour remplacer le code nonce sans conserver l’ancien runtime, V0.2.0 actif a été désactivé depuis l’administration. Les deux anciennes copies 0.1.0 et `0.2.0_disabled` restent inactives ; le catalogue et les pages de test sont conservés.

La dernière build V0.2.0 intégrant la sanitation dédiée du formulaire variable a été téléversée, choisie en remplacement de l’ancienne extension puis activée avec succès. WordPress reste administrable ; le test fonctionnel variable peut être rejoué.

Sur `https://lightpink-cat-987595.hostingersite.com/crs-test-menu/`, après la dernière mise à jour, le Quick View du produit variable `[CRS TEST] Tacos Variable` affiche correctement le prix, la sélection `Taille CRS` avec les options `Petite`/`Grande`, la quantité, le bouton « Ajouter au panier » et le lien « Voir la fiche produit ». La sanitation dédiée a donc résolu la perte du formulaire variable constatée dans l’itération précédente. Le rendu reste dans la modale accessible, sans création de commande.
