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

Après reconnexion de la session administrateur, le package `restaurant-suite-core-0.3.0.zip` a été installé puis activé avec succès. L’administration WordPress reste accessible et aucune erreur fatale n’est apparue. Le test public du Cart Drawer peut commencer sur la page menu synthétique.

La page menu staging charge bien le déclencheur « Ajouter au panier » sous forme de bouton `data-crs-cart-add` et le bouton flottant « Panier 0 ». Un clic d’ajout simple vient d’être déclenché ; l’issue réseau et l’état final doivent être vérifiés par la console avant de conclure.

La ligne des extensions confirme désormais que `restaurant-suite-core-0.3.0` est active ; les copies 0.1.0, 0.2.0 et `0.2.0_disabled` restent inactives. L’administration reste accessible. Le retest REST doit maintenant confirmer que l’initialisation explicite `wc_load_cart()` corrige le 503 précédent.

Le retest de la build V0.3 active a encore retourné HTTP 503 `crs_cart_unavailable` : `wc_load_cart()` ne suffisait pas, car l’objet global devait être relu et initialisé explicitement selon le contexte REST. Une seconde correction a été appliquée, validée localement (14 tests PHPUnit / 47 assertions) et le ZIP V0.3.0 a été reconstruit avec `class-cartendpoint.php` et `cart-drawer.js`. La réinstallation staging de cette build est requise avant toute conclusion.

V0.3.0 a été désactivé proprement sur staging avant remplacement par la build corrigée. L’administration reste accessible et le remplacement n’affecte pas les produits, pages ni le panier WooCommerce de test.

La build V0.3.0 corrigée a été téléversée et WordPress a confirmé la mise à jour (« Mise à jour de l’extension », suppression de l’ancienne copie puis extension mise à jour). Elle est prête à être activée pour le retest REST final.

La build corrigée a été activée après remplacement ; la liste des extensions confirme `restaurant-suite-core-0.3.0` avec l’action « Désactiver ». Aucun fatal ni écran de récupération n’est apparu. Le retest public de la route REST peut maintenant fermer l’incident 503.

Le retest REST final de `POST /wp-json/crs/v1/cart/add` avec le produit synthétique 16 retourne HTTP 200. Le snapshot contient `count: 1`, le HTML d’une ligne `crs-cart__line` et le sous-total WooCommerce `$12.50`. L’initialisation REST du panier est donc corrigée ; les mutations update/remove/refresh et l’accessibilité du drawer restent à vérifier.

Après redéploiement du correctif et confirmation que V0.3.0 reste active, le scénario public `refresh → update(2) → remove → refresh` retourne encore `remove: HTTP 409 crs_cart_action_failed` et le panier reste à `count: 1`. Le correctif de lecture post-retour `false` ne suffit pas : la ligne n’est pas supprimée. V0.3 demeure bloquée ; il faut diagnostiquer la clé/contexte WooCommerce et ajouter une régression avant toute conclusion.

Un essai complémentaire après expiration de la page a retourné HTTP 403 sur `refresh` (nonce de la page périmé) et ne constitue pas un nouveau diagnostic métier. Avant cet essai, le 409 remove était reproductible avec un nonce valide et la ligne restait présente après refresh.

La build contenant l’appel explicite à `get_cart()` avant les mutations passe de nouveau la validation locale et a été téléversée dans WordPress. La page de confirmation affichait le package `restaurant-suite-core-0.3.0.zip` et le lien de remplacement ; une vue navigateur ultérieure est devenue `about:blank` avant l’application de cette confirmation. Le déploiement de cette itération doit être repris et vérifié.

La nouvelle build a été installée comme `restaurant-suite-core-0.3.0-1` puis activée par WordPress. L’inspection DOM vérifie que `restaurant-suite-core-0.3.0` et `restaurant-suite-core-0.3.0-1` affichent toutes deux l’action « Désactiver » : les deux copies V0.3.0 sont actives simultanément. Cette installation parallèle n’est pas acceptable pour le diagnostic car elle peut charger deux runtimes et produire des conflits de constantes/hooks. Le nettoyage doit désactiver puis supprimer uniquement l’ancienne copie `restaurant-suite-core-0.3.0`, conserver `restaurant-suite-core-0.3.0-1` et vérifier l’état final.


Nettoyage effectué sur le staging : l’ancienne copie `restaurant-suite-core-0.3.0` a été désactivée puis supprimée après confirmation ; la nouvelle copie `restaurant-suite-core-0.3.0-1` reste conservée pour le retest. WordPress confirme la suppression et l’administration reste accessible, sans modification du catalogue ni des fixtures.

Après nettoyage, une seule copie `restaurant-suite-core-0.3.0-1` est active. Le scénario REST avec nonce neuf et cache-buster retourne : `refresh` HTTP 200 / count 1 ; `update` quantité 2 HTTP 200 / count 2 / ligne quantité 2 ; `refresh` HTTP 200 / count 2 / ligne quantité 2 ; `remove` HTTP 200 / count 0 ; `refresh` HTTP 200 / count 0 avec état vide. Le défaut 409 était donc lié au double runtime actif et/ou à l’absence d’hydratation de session avant mutation ; la build avec `get_cart()` avant update/remove est maintenant fonctionnelle sur staging.

Test UI après clic réel sur « Ajouter au panier » : le drawer est ouvert (`aria-hidden=false`), le focus est sur le bouton Fermer, le compteur vaut 1 et une ligne est rendue. Une simulation du bouton increase n’a pas modifié la quantité observée après 700 ms ; la simulation remove a ensuite affiché count 2 / quantité 2 au lieu de vider le drawer. Ce résultat est incohérent avec le REST validé et bloque la validation UI ; il faut inspecter les sélecteurs, les événements et le bundle avant de conclure.

Le scénario UI synchronisé a ensuite été exécuté avec attente de fin de chaque mutation : clic réel menu → drawer ouvert, compteur 1 et ligne quantité 1 ; augmentation → compteur 2 / quantité 2 ; diminution → compteur 1 / quantité 1 ; suppression → compteur 0, aucune ligne et message panier vide. Un nouvel ajout a rouvert le drawer avec une ligne. L’ouverture par le bouton flottant a placé le focus sur le bouton Fermer ; Escape a fermé le drawer et rendu le focus au bouton déclencheur. Les liens Voir mon panier et Valider la commande sont présents. Le premier essai à 700 ms était un test de course du harnais qui cliquait pendant le chargement ; il est conservé comme incident de test, tandis que le scénario synchronisé constitue la preuve fonctionnelle.

Le Quick View réel du produit variable `[CRS TEST] Tacos Variable` s’ouvre sur staging avec `aria-hidden=false`, focus initial sur le bouton Fermer, un select `attribute_taille-crs` proposant Petite/Grande, quantité 1, `variation_id` initial 0 et le bouton Ajouter au panier. La soumission d’une variation Grande reste à exécuter ; aucune commande ne sera créée.

Le test du Quick View variable a montré que le select Grande change bien sa valeur, mais que le champ caché `variation_id` reste à `0` après 600 ms. La référence officielle WooCommerce expose `WC_Product_Variable::get_matching_variation()` pour faire correspondre les attributs à une variation ; le endpoint doit donc résoudre l’ID côté serveur plutôt que faire confiance au navigateur ou au prix client. Aucun ajout variable n’a encore été soumis à ce stade.

La build avec résolution serveur de variation passe la validation locale complète (15 tests PHPUnit / 50 assertions, lint, PHPCS, PHPStan, build, tests JS, contrats et packaging). Elle a été téléversée ; WordPress a reconnu le dossier actif `restaurant-suite-core-0.3.0-1` et affiché le lien de remplacement. La vue du navigateur est devenue vide avant le clic de confirmation ; l’itération doit être reprise avant le test variable.

Basculement staging de l’itération variation : le package validé localement a été installé comme `restaurant-suite-core-0.3.0-2`. La copie `restaurant-suite-core-0.3.0-1` a été désactivée ; l’inspection DOM vérifie que seule `restaurant-suite-core-0.3.0-2` affiche « Désactiver », tandis que -1 affiche « Activer | Supprimer ». Aucun fatal n’est apparu. L’ancienne copie -1 sera supprimée après confirmation du test, afin de conserver une seule build active.

Nettoyage final confirmé après reprise de session : la copie inactive `restaurant-suite-core-0.3.0-1` a été supprimée. La liste WordPress affiche une seule ligne V0.3.0 active ; les copies V0.1.0 et V0.2.0 restent inactives pour rollback historique. L’administration affiche 11 extensions actives et aucun écran de récupération.

Test variable sur build -2 : sélection Grande puis soumission du formulaire Quick View (le champ caché restait `variation_id=0`) a abouti à HTTP 200 côté endpoint résolveur, compteur 2, deux lignes dans le drawer, présence de Tacos Variable et Grande, avec drawer ouvert. Une vérification après 1,5 s montre toutefois que `data-crs-quickview` reste `aria-hidden=false` ; le Cart Drawer ne ferme pas la modale après succès car son handler appelle sa propre fonction `close()` et non la fermeture Quick View. Correction JS nécessaire avant de déclarer le flux variable complet.

Après mise à jour in-place de la copie active -2, le retest Grande ajoute correctement la variation (compteur 3, deux lignes, Tacos Variable/Grande, drawer ouvert), mais `quickViewOpen` reste encore vrai après 12 s. La correction source de fermeture est bien présente localement ; l’écart indique que le navigateur ou LiteSpeed sert probablement encore un ancien bundle `cart-drawer.js`, à vérifier via les URLs/empreintes de ressources et le cache avant toute nouvelle modification métier.

Diagnostic complémentaire : le fichier servi par l’URL staging `restaurant-suite-core-0.3.0-2/assets/build/cart-drawer.js?ver=0.3.0` contient bien l’appel au sélecteur `[data-crs-quickview-close]`. Le clic manuel sur ce bouton ferme la modale et restitue le focus. Un second harnais a toutefois produit zéro événement et aucune mutation supplémentaire ; il a vraisemblablement tenté de soumettre avant que le fragment Quick View soit prêt après seulement 700 ms. Il faut attendre explicitement la présence du formulaire et de son select avant le prochain essai.

Le retest synchronisé après mise à jour reste reproductible : formulaire réellement présent, Grande sélectionnée, événement `crs:cart:add`, compteur augmenté et drawer avec Tacos/Grande, mais aucun événement `crs:quickview:close` et la modale reste ouverte après 12 s. Le bundle réseau contient pourtant l’appel `.click()` sur `[data-crs-quickview-close]`, et ce bouton ferme manuellement la modale. Pour éliminer cette dépendance au clic synthétique, la prochaine correction utilisera un événement applicatif explicite `crs:quickview:close-request` écouté par Quick View et émis par le handler Cart Drawer.

La correction `crs:quickview:close-request` a été validée localement puis mise à jour in-place dans la copie active -2 ; WordPress confirme la mise à jour et purge les caches. Une page publique fraîche affiche toujours les fixtures et le panier de test sans fatal ; le Quick View variable vient d’être rouvert pour le retest final.

Retest final après déploiement de l’événement explicite : formulaire variable prêt, Grande sélectionnée, événement `crs:cart:add`, compteur 6, deux lignes dont Tacos/Grande et drawer ouvert ; cependant aucun `crs:quickview:close` n’est observé et Quick View reste ouvert après 15 s. Le problème n’est donc pas résolu ; il faut vérifier que le bundle Quick View réellement servi contient l’écouteur `crs:quickview:close-request` et que le code exécuté correspond bien à la build.

Le bundle Quick View servi par staging contient bien `document.addEventListener('crs:quickview:close-request', close)`, mais l’émission directe de cet événement sur la page ouverte ne change ni `hidden` ni `aria-hidden` et ne déclenche pas `crs:quickview:close`. Le bouton de fermeture manuel fonctionne. Cela indique un écart entre le texte servi et le listener effectivement attaché (ou une instance/script ancien en mémoire) ; il faut inspecter les balises script et forcer une page totalement neuve/cache-buster avant de modifier davantage le comportement.

Sur une page cache-bustée totalement neuve, la soumission Grande ajoute bien la variation (compteur 7, deux lignes Tacos/Grande et drawer ouvert), mais l’événement `crs:quickview:close` ne survient toujours pas et la modale reste visible. Le listener réseau est présent mais ne semble pas attaché de façon observable ; pour une intégration déterministe, Quick View doit exposer sa fonction de fermeture via une API globale documentée, utilisée directement par Cart Drawer après succès.

Contrôle runtime après le dernier parcours d’upload : sur une page publique fraîche, Quick View est ouvert mais `typeof window.CRS_QUICK_VIEW_CLOSE` retourne `undefined` et l’appel direct ne ferme rien. La correction API globale n’est donc pas effectivement déployée dans le runtime actif, même si le package local la contient probablement ; l’écran WordPress vide a interrompu la confirmation précédente. Ne pas conclure à un défaut de logique avant de refaire un upload/remplacement vérifiable.

Basculement vérifié après l’installation -3 : les copies V0.2.0, V0.1.0, `0.2.0_disabled` et V0.3.0-2 sont inactives ; seule `restaurant-suite-core-0.3.0-3/restaurant-suite-core.php` est active avec WooCommerce. Aucun double runtime Restaurant Suite Core n’est présent. Le prochain contrôle public doit confirmer que cette copie -3 sert bien l’API globale et fermer les modales après ajout.

Preuve runtime après activation de -3 : une page publique fraîche expose `typeof window.CRS_QUICK_VIEW_CLOSE === "function"`; son appel direct passe la modale de `aria-hidden=false` à `aria-hidden=true` et restitue le focus au déclencheur. La build API globale est donc effectivement active. Le test suivant doit soumettre Grande depuis Quick View et vérifier la fermeture automatique après succès.

Validation Quick View variable sur -3 : Grande soumise avec formulaire prêt, `window.CRS_QUICK_VIEW_CLOSE` fonctionnel, événements `crs:cart:add` puis `crs:quickview:close`, compteur passé à 8, drawer ouvert, deux lignes avec Tacos Variable/Grande, `quickViewOpen=false` et focus revenu au déclencheur. Le scénario de mutations UI a exécuté les actions de quantité/suppression puis a rencontré une navigation involontaire vers le lien de navigation `/panier/` lors de la recherche du déclencheur ; la page Panier affiche maintenant « Votre panier est actuellement vide ! ». Le résultat final empty est donc observé, mais les étapes intermédiaires doivent être rejouées avec le bouton flottant correctement identifié, sans naviguer hors de la page.

Scénario UI Cart Drawer sur panier synthétique : la ligne Burger démarre à 1, passe à 2 avec `data-crs-cart-increase`, revient à 1 avec `data-crs-cart-decrease`, puis est supprimée ; compteur 0 et message « Votre panier est vide. Sous-total $0.00 Continuer mes achats Voir mon panier Valider la commande » observés. Les liens Panier et Commander sont présents, aucune commande créée, fermeture et Escape passent. Le harnais a ensuite échoué à rouvrir le drawer et à vérifier le focus retour car il ciblait le bouton d’en-tête par aria-label, qui n’est pas le déclencheur Cart Drawer effectif ; ce sélecteur doit être inspecté séparément.

Sélecteur UI corrigé : le véritable déclencheur est le bouton `[data-crs-cart-open]` (distinct du lien de navigation `/panier/`). Sur panier vide, focus initial sur ce bouton, clic => drawer ouvert (`aria-hidden=false`), puis Escape => drawer fermé (`aria-hidden=true`) et focus revenu au même bouton ; compteur final 0. Le panneau n’a pas reçu le focus direct dans ce harnais, mais le contrat focus-retour est validé.
