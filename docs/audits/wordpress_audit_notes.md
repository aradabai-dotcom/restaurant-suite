# Notes d’audit — Chef Anass

## Constatations initiales

- URL publique : https://lightsteelblue-butterfly-772504.hostingersite.com/
- La page publique affiche actuellement : « Briefly unavailable for scheduled maintenance. Check back in a minute. »
- L’administration WordPress est accessible après connexion avec le compte « chef Anass ».
- Le tableau de bord affiche WordPress 7.1.
- Le tableau de bord signale 66 mises à jour disponibles.
- L’installation comprend notamment Elementor, WooCommerce, Essential Addons for Elementor, Royal Addons, WPCode, OneClick WhatsApp Order, Side Cart, LiteSpeed Cache et un module Restaurant.
- Le menu d’administration expose les contenus, produits, commandes, réglages WooCommerce, thèmes, extensions, comptes, santé du site et réglages généraux.
- L’audit doit rester en lecture seule : aucune modification, publication, commande, paiement ou envoi ne doit être effectué.

## Points à vérifier

- Pages, articles, produits et médias existants.
- Thème actif, personnalisation, menus, en-tête et pied de page.
- Configuration WooCommerce et visibilité publique.
- Extensions, mises à jour, santé du site et éventuels conflits.
- Réglages de lecture, permaliens, langue, identité du site et confidentialité.
- Expérience utilisateur, contenu, conversion, mobile et SEO visible.

## Accueil — constats

Après connexion administrateur, la page d’accueil est visible malgré le mode maintenance pour les visiteurs non connectés. Elle présente une identité noire et dorée autour du logo « Chef Anass », une accroche de restauration rapide et des boutons « Menu » et « Localisation ». Les catégories mises en avant sont Plats, Tacos, Burgers et Tagliatelle/Pâtes.

Le menu de navigation contient notamment « Home », « Notre Menu », « Nos spécialités » et « Contact ». La page affiche ensuite une longue liste de produits avec images, descriptions, prix en dirhams et boutons « Ajouter », ainsi qu’une FAQ portant sur la livraison, les horaires, l’adresse et les ingrédients.

Points positifs visibles : identité de marque identifiable, visuels produits nombreux, prix affichés directement, catégories compréhensibles, accès au panier et présence de questions FAQ utiles pour le référencement local.

Points de vigilance visibles : la page est très longue et dense ; le logo et le texte héro occupent beaucoup d’espace avant l’offre ; les libellés alternent français et anglais (« Home », « Contact », « Store List », « My Orders », « Checkout ») ; « Pates » devrait être accentué en « Pâtes » ; plusieurs formulations et accords sont à corriger (« commande supérieure à », « horaires d’ouverture », « oignons caramélisés », « cornflakes », « pâtes »). Les visuels et cartes de produits sont nombreux, mais la hiérarchie entre découverte de la marque, catégories et commande pourrait être plus directe.

La page publique comporte une icône panier flottante et des boutons « Ajouter ». Le parcours de commande doit être testé sans finaliser d’achat, en particulier le panier, le checkout, les options de sauce et la livraison.

## Menu et contact — constats

La page « Notre Menu » propose une recherche produit et quatre onglets de catégorie. Les catégories Tacos, Plats Chicken et Burgers contiennent des produits visibles avec images, prix et boutons d’ajout ; la section Pâtes affiche toutefois « No Products Found! » à deux reprises, alors que la page d’accueil annonce des pâtes et que plusieurs produits de pâtes existent dans l’inventaire WordPress. Il s’agit d’un défaut fonctionnel prioritaire qui peut faire perdre des commandes.

La page Contact utilise un formulaire Elementor dont les libellés et placeholders sont en anglais (« Name », « Email », « Message », « Send »), alors que le reste du site est principalement en français. Un texte de remplissage en anglais (« Attachment apartments in delightful... ») est encore présent dans le bloc de contact et doit être remplacé. Les informations locales sont présentes : numéro 0616258582, adresse 77 Rue Ishak, El Marouni, Casablanca 20000, horaires lundi-samedi de midi à minuit et dimanche de 14h à minuit, ainsi qu’une liste de quartiers desservis.

La règle de livraison n’est pas formulée de manière cohérente partout : la FAQ parle d’une commande supérieure à 50 Dhs, tandis que la section zones de livraison évoque « à partir de 2 commandes ». Il faut choisir une règle unique, la préciser avec le montant des frais en dessous du seuil et l’afficher près du panier/checkout. Le contact gagnerait à proposer un bouton d’appel cliquable, un bouton WhatsApp explicite, une carte ou un lien d’itinéraire et un formulaire entièrement francisé.

## Panier et maintenance technique — constats

Le panier fonctionne avec un produit de test déjà présent (« Plat Nuggets - Blanche ») et affiche quantité, sous-total et total. Le bouton principal « Commandez » redirige vers WhatsApp avec un message prérempli ; aucun paiement ou validation automatique n’a été tenté. Le parcours est donc principalement une commande par WhatsApp, ce qui doit être expliqué clairement au client. Le panier affiche aussi un lien « Commander » dans le panneau flottant qui renvoie vers le panier, formulation potentiellement ambiguë.

La page des mises à jour indique que le site utilise actuellement WordPress 6.9.7, avec une mise à jour WordPress 7.1 disponible. WooCommerce 10.6.1 peut être mis à jour vers 11.0.1, mais la compatibilité avec WordPress 7.1 est indiquée comme inconnue. Quatre thèmes disposent aussi de mises à jour. Le site affiche donc un risque de maintenance important ; il faut sauvegarder fichiers et base de données, vérifier la compatibilité des extensions et tester sur une copie avant toute mise à jour. La page publique de maintenance est cohérente avec un site laissé en mode maintenance, mais ce mode doit être désactivé avant mise en ligne.

La liste des extensions indique 12 extensions au total, dont 11 actives et 1 désactivée, avec Elementor 4.2.3, Essential Addons 6.7.3 et WooCommerce 10.6.1 visibles. WooCommerce est requis par OneClick Chat to Order, Reno Quick View et Side Cart WooCommerce. L’empilement Elementor + Essential Addons + Royal Addons + widgets de panier/WhatsApp augmente la surface de dépendances et le risque de conflits ; un nettoyage des extensions réellement nécessaires est recommandé.

## Santé du site — constats

La Santé du site signale quatre améliorations recommandées : mise à jour de WordPress vers 7.1, suppression des extensions inactives, suppression des thèmes inactifs et un événement planifié en retard. Aucun problème critique n’est affiché dans le résumé visible, mais ces recommandations confirment que l’installation n’est pas encore prête pour une mise en ligne sans maintenance préalable.

L’inventaire visible comprend notamment Elementor, Essential Addons for Elementor, Hostinger Tools, LiteSpeed Cache, OneClick Chat to Order, Reno Quick View, Restaurant Owner Dashboard, Royal Elementor Addons et des composants d’interface WooCommerce. Food Menu - Restaurant Menu & Online Ordering for WooCommerce est installé mais inactif. Cette extension inactive peut être supprimée si elle n’est plus utilisée, après vérification des dépendances et sauvegarde.

## Catalogue, fiches produit et visibilité — constats

L’inventaire WooCommerce contient 22 produits publiés, tous indiqués « En stock ». Les catégories principales sont Tacos, Plats, Burgers et Pâtes. Les prix observés vont approximativement de 30 à 55 Dhs. Le catalogue est suffisamment renseigné pour une première mise en ligne, mais la page Menu ne restitue pas correctement la catégorie Pâtes, ce qui indique un problème de filtre, de widget Elementor ou de requête de catégorie plutôt qu’une absence de produits.

Une fiche produit de Tacos poulet présente deux listes d’options de sauce, un choix initial « Choisir une option », une quantité, un bouton d’ajout au panier et un bouton de commande. L’expérience permet la personnalisation, mais le visiteur doit comprendre quelle sauce est obligatoire et quelle est la différence entre « Sauce Tacos » et « Sauce Tacos 2 ». Les libellés et le prix sont visuellement difficiles à lire sur le fond sombre ; un test sur mobile et une vérification des contrastes sont indispensables.

Le thème actif est Royal Elementor Kit. Twenty Twenty-Five, Twenty Twenty-Four et Twenty Twenty-Three sont installés en plus du thème actif et disposent de mises à jour. Ils peuvent être conservés comme thèmes de secours, mais les thèmes inutiles devraient être supprimés après sauvegarde, conformément à la recommandation Santé du site.

Dans WooCommerce > Visibilité du site, l’option « En ligne » apparaît sélectionnée dans l’écran consulté. Le fait que les visiteurs déconnectés voient néanmoins la page de maintenance suggère un mode maintenance distinct de ce réglage WooCommerce, ou une mise en cache/page de maintenance à purger et à contrôler avant lancement.

## Structure, réglages et navigation — constats

Le contrôle structurel de l’accueil montre une page d’environ 1 265 px de largeur utile et 4 581 px de hauteur dans la session de test, avec 33 images, 152 liens et 6 images sans texte alternatif détecté. La page possède beaucoup de titres H2, parfois un titre par produit, ce qui rend la hiérarchie sémantique très répétitive. L’absence de texte alternatif sur certaines images doit être corrigée, notamment pour les logos, icônes ou images décoratives ; les images de produits déjà renseignées sont un bon point.

Les réglages généraux montrent le titre du site « Chef Anass » et un slogan non renseigné. Un slogan court et descriptif pourrait renforcer la compréhension immédiate et le référencement local. Les réglages de lecture utilisent une page statique « Chef Anass » comme accueil, ce qui est cohérent. Aucun réglage visible ne demande explicitement aux moteurs de recherche de ne pas indexer le site, mais il faudra vérifier ce point après la fin du mode maintenance.

Le menu principal est globalement court et lisible : Home, Notre Menu, Nos spécialités avec quatre sous-éléments Plats, Tacos, Pâtes et Burgers, puis Contact. « Nos spécialités » pointe vers « # », ce qui peut être acceptable comme menu parent mais doit être testé sur mobile ; un lien vers une section réelle ou une page dédiée serait plus clair. L’utilisation de « Home » au milieu de libellés français doit être harmonisée avec « Accueil ». Les liens de catégories et le menu mobile doivent être vérifiés après correction de la section Pâtes.

## Réglages WooCommerce — constats

L’adresse de la boutique est configurée sur 77 Rue Ishak, El Marouni, à côté de McDonald’s Maarif, Casablanca, Maroc, code postal 20000. La devise est bien le dirham marocain (MAD) et les prix sont affichés à droite, ce qui correspond au marché visé. Le réglage « vendre dans tous les pays » est plus large que le réglage « livrer dans des pays spécifiques : Maroc » ; il faudrait harmoniser ces paramètres pour éviter toute ambiguïté.

La page des zones de livraison ne contient pas de zone nationale configurée : seule la zone « Reste du monde » est présente, sans mode d’expédition offert. Comme le site vend actuellement via WhatsApp et non via le checkout WooCommerce, cette configuration peut ne pas bloquer le bouton WhatsApp, mais elle est incomplète et incohérente avec les textes qui promettent une livraison à Casablanca. Il faut soit formaliser les zones et tarifs WooCommerce, soit masquer clairement les fonctions de livraison WooCommerce non utilisées et gérer la règle de livraison dans le parcours WhatsApp.

## Commande par WhatsApp — constats

La page Commandes WooCommerce ne contient aucune commande enregistrée ; cela est cohérent avec un fonctionnement par WhatsApp plutôt qu’avec un checkout WooCommerce complet. L’extension OneClick Chat to Order affiche une notification recommandant de configurer plusieurs numéros WhatsApp, ce qui mérite vérification.

Un point critique de cohérence a été observé : le lien WhatsApp généré depuis le panier utilise un numéro différent de celui affiché dans la page Contact. Il faut vérifier lequel est le bon, le remplacer partout par le même numéro officiel et faire un test réel contrôlé avant publication. Aucun message n’a été envoyé.

## Confirmation page Pâtes

La page publique « Pâtes » confirme le défaut : le titre « Nos Pâtes » est suivi de deux blocs « No Products Found! », alors que six produits de catégorie Pâtes sont publiés et en stock dans WooCommerce. Le problème est donc clairement visible par un visiteur et doit être traité avant toute promotion du site.

Le footer est visuellement présent et reprend le logo, une courte phrase de marque, les réseaux sociaux, les liens de menu, l’adresse et les horaires. Sur la page Pâtes, le footer est relativement bas et lisible, mais certains liens restent génériques ou redondants ; la hiérarchie peut être simplifiée.

## URL et référencement technique — constats

La structure de permaliens générale est configurée sur `/%postname%/`, ce qui produit des URL lisibles. Les URL de produits observées restent propres, par exemple `/produit/tacos-poulet/`. Ce point est satisfaisant. Il reste à vérifier les balises SEO, les titres et descriptions, le sitemap, les données locales et l’indexation une fois le site sorti du mode maintenance.

## Contrôles HTTP et indexation — constats

Un contrôle externe renvoie HTTP 200 et indique que LiteSpeed Cache est actif. L’en-tête de sécurité `Content-Security-Policy: upgrade-insecure-requests` est présent. Le fichier `wp-sitemap.xml` répond correctement, tandis que `sitemap_index.xml` renvoie une page 404, ce qui peut être normal selon le plugin SEO utilisé mais doit être vérifié dans Search Console.

Le point le plus important est le fichier `robots.txt` : il contient `User-agent: Googlebot` puis `Disallow: /`, ce qui demande explicitement à Googlebot de ne pas explorer le site. Pour un site commercial qui doit être trouvé localement, ce blocage doit être retiré avant le lancement et vérifié dans Google Search Console. La règle générale `User-agent: * Allow: /` ne neutralise pas nécessairement la règle spécifique à Googlebot.
