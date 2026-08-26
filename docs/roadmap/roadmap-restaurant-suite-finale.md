# Roadmap finale — Restaurant Suite et Restaurant Base Theme

**Objectif produit :** créer une solution WordPress duplicable pour plusieurs restaurants, sans plugins payants, en reprenant les meilleures expériences de Reno Quick View et Side Cart WooCommerce, avec une technologie plus légère, un seul panier, un dashboard simple et un thème compatible Elementor.

## Décision d’architecture

La solution finale sera composée de **WooCommerce**, d’un plugin propriétaire **Restaurant Suite Core** et d’un thème **Restaurant Base Theme**. WooCommerce restera la source unique de vérité pour les produits, catégories, variations, prix, stock, panier et commandes. Le plugin propriétaire ajoutera les fonctions restaurant. Le thème gérera la présentation, les templates et le design system.

Elementor restera compatible et pourra personnaliser les zones prévues, mais il ne sera pas obligatoire pour utiliser le menu, le Quick View, le panier ou WhatsApp. Cette séparation permettra de proposer la même base à différents clients, avec ou sans Elementor.

| Élément | Responsabilité | Décision |
|---|---|---|
| WooCommerce | Catalogue, panier et commandes | Dépendance centrale conservée |
| Restaurant Suite Core | Menu, Quick View, Cart Drawer, WhatsApp, règles restaurant et dashboard | Plugin propriétaire unique et modulaire |
| Restaurant Base Theme | Templates, design system, responsive et SEO technique | Thème léger compatible Elementor |
| Elementor | Personnalisation visuelle et widgets prévus | Compatible, mais optionnel pour le fonctionnement |
| LiteSpeed Cache | Cache et optimisation serveur | Conservé séparément si l’hébergement l’utilise |
| Hostinger Tools | Fonctions propres à Hostinger | Hors périmètre du produit restaurant |

## Règle de progression

Chaque version doit être **testée, validée et réversible** avant le début de la suivante. Nous ne supprimerons pas les extensions actuelles avant la validation du remplacement correspondant sur un environnement de staging.

Il ne faudra jamais activer sur la même page les deux Quick View, les deux paniers latéraux ou les deux systèmes WhatsApp. Cette situation provoquerait des boutons dupliqués, des événements concurrents, des fragments incohérents et des commandes difficiles à diagnostiquer.

---

# Phase 0.0 — Contrats de conception obligatoires

Cette phase vient avant tout développement métier. Elle transforme la vision en décisions techniques vérifiables.

## 0.0.1 — Contrat de données

| Donnée | Source de vérité | Stockage initial recommandé | Règle |
|---|---|---|---|
| Nom, prix, image, stock | WooCommerce | Produit WooCommerce | Ne jamais dupliquer dans une table parallèle |
| Catégories | WooCommerce | Taxonomie `product_cat` | Signaler les catégories vides |
| Variations et attributs | WooCommerce | Variations natives WooCommerce | Limiter la V0.1 aux variations natives |
| Option tarifée | WooCommerce | Variation ou produit associé | Variation si les combinaisons restent maîtrisables |
| Remarque libre | Restaurant Suite | Champ de formulaire contrôlé | Ne peut jamais modifier le prix |
| Allergène et information descriptive | Restaurant Suite | Métadonnée produit validée | Affichage côté serveur obligatoire |
| Supplément complexe | À définir plus tard | Pas de logique en V0.1 | Reporter après validation du modèle simple |
| Disponibilité | Restaurant Suite | Métadonnée/règle versionnée | Une bascule disponible/indisponible en premier |
| Horaires | Restaurant Suite | Option versionnée `crs_settings` | Format documenté et migrable |
| Livraison et minimum | Restaurant Suite + WooCommerce | Réglages versionnés, frais WooCommerce si possible | Règles simples d’abord |
| Commande | WooCommerce | Objets CRUD WooCommerce et HPOS | Pas de requêtes directes sur les tables de commandes |

**Décision obligatoire :** la V0.1 ne gère pas de système d’options tarifées propriétaire. Elle supporte les produits simples et les variations WooCommerce natives. Les options complexes seront ajoutées seulement après validation de la structure produit.

Le code commande utilisera les APIs WooCommerce et préparera la compatibilité HPOS. HPOS utilise des tables dédiées pour les commandes et WooCommerce indique qu’une extension incompatible peut empêcher son activation [1].

## 0.0.2 — Contrat des statuts de commande

Le statut WhatsApp **En attente de confirmation** doit être défini avant de construire le dashboard. Il pourra être un statut personnalisé enregistré par le plugin, à condition de préciser ses transitions et ses droits.

| Statut | Signification | Transition suivante |
|---|---|---|
| En attente de confirmation | La demande a été créée, mais le restaurant ne l’a pas encore acceptée | Confirmée ou Refusée |
| Confirmée | Le restaurant accepte la commande | En préparation |
| En préparation | La cuisine prépare la commande | Prête |
| Prête | La commande attend le retrait ou la livraison | Terminée ou En livraison |
| En livraison | La commande est confiée au livreur | Terminée |
| Terminée | La commande est remise au client | État final |
| Refusée | La commande ne sera pas préparée | État final |

Les transitions devront être contrôlées par rôle. Un changement de statut ne devra pas modifier le prix. Les noms affichés pourront être personnalisés, mais les identifiants internes devront rester stables pour les migrations et les intégrations.

## 0.0.3 — Contrat d’idempotence WhatsApp

Une tentative de commande recevra un identifiant unique. Le serveur vérifiera cet identifiant avant de créer une nouvelle commande. Si la même requête est répétée, il renverra le récapitulatif de la commande existante au lieu d’en créer une seconde.

Le flux prévu est : validation du formulaire, verrouillage temporaire du bouton, recalcul serveur, contrôle du stock, création unique de la commande, affichage du récapitulatif, puis ouverture de WhatsApp. Si WhatsApp ne s’ouvre pas, le client pourra relancer uniquement le lien sans recréer la commande.

Les cas d’erreur à traiter sont le panier modifié, le produit devenu indisponible, la variation supprimée, le numéro WhatsApp mal configuré, l’échec de création, le double clic, la perte réseau et l’abandon avant l’ouverture de WhatsApp.

## 0.0.4 — Contrat de compatibilité

La compatibilité complète avec les blocs Panier et Checkout ne sera pas annoncée avant les tests spécifiques. Les fragments classiques WooCommerce ne suffisent pas automatiquement à garantir le fonctionnement des blocs.

| Compatibilité | Décision |
|---|---|
| Produits simples et variables | V0.1 obligatoire |
| WooCommerce CRUD | V0.1 obligatoire |
| HPOS | Testé avant V1.0, déclaré si le code est compatible |
| Panier/Checkout classiques | V0.2 obligatoire si utilisés par le site |
| Blocs Panier/Checkout | Reportés après le Cart Drawer, sauf besoin immédiat du site |
| Elementor actif | Testé à partir de V0.1 |
| Elementor désactivé | Menu, produit et panier doivent rester utilisables |
| Thème sans Elementor | Testé avant V1.0 |

## 0.0.5 — Matrice de permissions

| Rôle | Produits | Disponibilité | Commandes | Coordonnées client | WhatsApp | Réglages généraux |
|---|---|---|---|---|---|---|
| Propriétaire | Lecture/écriture | Oui | Tous droits | Oui | Oui | Oui |
| Manager | Lecture/écriture | Oui | Tous les statuts opérationnels | Oui | Optionnel | Limités |
| Cuisine | Non | Optionnel | Voir et changer les statuts cuisine | Minimum nécessaire | Non | Non |
| Livreur | Non | Non | Voir les commandes assignées et statut livraison | Adresse nécessaire | Non | Non |

Chaque action AJAX ou REST devra vérifier l’identité, la capacité et le nonce. Le Plugin Developer Handbook recommande de s’appuyer sur les hooks, les contrôles de capacité, la validation des entrées, les nonces et l’échappement des sorties [2].

## 0.0.6 — Contrat des événements JavaScript

Le plugin aura un seul store public et des événements stables : `crs:cart:add`, `crs:cart:update`, `crs:cart:remove`, `crs:cart:refresh`, `crs:quickview:open`, `crs:quickview:close` et `crs:order:created`.

Le Quick View, le menu, le Cart Drawer et WhatsApp ne doivent jamais créer chacun leur compteur, leur panier ou leur système de synchronisation. Le dashboard possédera un bundle séparé et ne chargera pas le JavaScript public complet.

## 0.0.7 — Livrables de sortie

La phase 0.0 est terminée uniquement lorsque les six livrables suivants existent : contrat de données, matrice de permissions, contrat des statuts, contrat d’idempotence, contrat d’événements JavaScript et matrice de tests.

---

# Version 0.1 — Fondation et menu public

## Périmètre

La V0.1 contient l’initialisation du plugin, les réglages de base, les variables CSS, la carte produit, les catégories, le menu rendu côté serveur, le bloc Gutenberg, le shortcode de secours et le widget Elementor de menu.

Elle ne contient pas encore le Cart Drawer complet, WhatsApp, le Kanban, les règles avancées de livraison, les rôles complexes ni un système propriétaire d’options tarifées.

## Expérience utilisateur

Le menu doit afficher les vrais produits WooCommerce avec image, nom, description, prix, disponibilité et lien vers la fiche produit. Le widget Elementor doit seulement contrôler la présentation : catégorie, colonnes, image, prix, description et bouton.

Le menu doit fonctionner avec Gutenberg, avec Elementor ou avec le shortcode. Si Elementor est désactivé, le contenu ne doit pas disparaître.

## Critères de sortie

| Test | Résultat exigé |
|---|---|
| Produit simple | Lisible et ajoutable au panier |
| Produit variable | Variation sélectionnable ou lien clair vers la fiche produit |
| Produit hors stock | Indisponible et non ajoutable |
| Catégorie vide | Message propre, jamais une page cassée |
| Sans JavaScript | Contenu et liens lisibles |
| Mobile | Cartes et actions utilisables au doigt |
| Elementor | Widget fonctionnel sans créer une copie des produits |

---

# Version 0.2 — Quick View et live preview

## Fonctions reprises de Reno Quick View

Nous reprendrons le choix de position du bouton, le déclenchement depuis l’image ou le nom, la modale, le panneau latéral, la largeur, la hauteur, les couleurs, le padding, l’animation, le bouton de fermeture, la galerie, le zoom optionnel et le choix des informations affichées.

Pour le restaurant, le Quick View affichera image, nom, prix, description courte, variations natives, disponibilité, allergènes si disponibles, remarque libre et bouton d’ajout au panier. Le bouton principal sera configurable entre **Ajouter au panier** et **Ajouter et commander**.

## Accessibilité et fallback

La fenêtre doit gérer le focus clavier, Échap, le retour du focus au bouton d’origine, `aria-modal`, le titre accessible, le verrouillage du scroll, le chargement, les erreurs et la fermeture mobile. Un lien vers la fiche produit restera disponible si l’interaction dynamique échoue.

## Live preview

Le back-office proposera un panneau de réglages à gauche et une preview isolée à droite, avec un switch desktop/tablette/mobile. Les modifications visuelles seront appliquées par variables CSS, sans recharger toute la page.

La V0.2 exposera cinq presets : Classique, Moderne, Compact, Sombre et Minimal mobile. Elle limitera l’interface à environ quinze réglages sûrs : layout, largeur, hauteur, couleurs, texte, rayon, ombre, position, libellé, image, prix, description et bouton. Les réglages CSS avancés et comportements expérimentaux resteront masqués.

## Critères de sortie

Le Quick View doit fonctionner sur produits simples et variables, ne pas dupliquer les boutons Elementor ou thème, ne pas charger Reno sur la page test et ne pas bloquer le parcours si JavaScript échoue.

---

# Version 0.3 — Cart Drawer

## Fonctions reprises de Side Cart

Nous reprendrons le panneau coulissant, le bouton panier flottant, le compteur, l’ajout AJAX, les quantités, la suppression, les notices, les sous-totaux, le panier vide, le bouton continuer les achats, les liens de commande, les layouts lignes/cartes, la position gauche/droite, la largeur, le responsive, les couleurs et les boutons.

Nous n’emporterons pas les dépendances lourdes observées dans l’archive : jQuery obligatoire, Font Awesome complet, police d’icônes globale, Masonry, Magic Animations ou plusieurs frameworks d’administration. Le nouveau système utilisera un seul bundle public, des SVG contrôlés et des animations CSS respectant `prefers-reduced-motion`.

## Critères de sortie

Le panier doit rester correct après ajout depuis le menu, ajout depuis Quick View, changement de quantité, suppression, actualisation, retour arrière, utilisateur invité, utilisateur connecté, produit variable et session mobile. Les pages panier, checkout, compte et dashboard seront exclues du cache dynamique.

Les coupons, cadeaux et récompenses ne font pas partie de la V0.3. Ils ne seront ajoutés qu’après stabilisation du panier principal.

---

# Version 0.4 — WhatsApp et règles restaurant simples

## Flux de commande

Le client remplit son nom, téléphone, adresse, mode de réception et remarque. Le serveur valide, recharge les produits et variations, vérifie la disponibilité, recalcule les taxes/frais et crée une commande WooCommerce unique. La commande reçoit le statut configuré, puis le client voit le récapitulatif et peut ouvrir WhatsApp.

Le prix, les taxes et le total ne seront jamais acceptés directement depuis le navigateur. Le numéro WhatsApp sera validé et protégé contre l’accès non autorisé, mais ne sera pas traité comme une clé API secrète. Il ne sera pas inclus dans les exports publics.

## Règles simples

La V0.4 gérera l’ouverture/fermeture du restaurant, le minimum de commande, le retrait ou la livraison, les frais simples et les zones manuelles. Les tarifs complexes par distance, créneaux multiples, jours fériés et règles combinatoires viendront plus tard.

## Critères de sortie

Une commande de test doit être créée une seule fois, avec les bons produits, les bonnes quantités, le bon total et le bon statut. Une répétition de requête doit renvoyer la commande existante. Si WhatsApp échoue, l’utilisateur doit pouvoir relancer uniquement le lien.

---

# Version 0.5 — Dashboard restaurant

Le dashboard n’arrive qu’après validation du modèle de commande. Il sera conçu pour un restaurateur peu familier avec WordPress.

| Écran | Fonction V0.5 |
|---|---|
| Aujourd’hui | Nouvelles commandes, confirmées, préparation, prêtes, terminées et restaurant ouvert/fermé |
| Commandes | Liste mobile puis Kanban simple avec transitions autorisées |
| Produits | Recherche, photo, nom, prix, catégorie et disponibilité |
| Horaires | Ouverture, fermeture et message visible au client |
| WhatsApp | Numéro, message, mode et test contrôlé |
| Utilisateurs | Rôles propriétaire, manager, cuisine et livreur |
| Aide | Instructions en français simple |

Le dashboard doit permettre de rendre un plat indisponible et de changer un statut sans entrer dans les menus techniques de wp-admin. La cuisine ne devra pas voir les réglages généraux ni modifier les montants.

## Critères de sortie

Un restaurant doit pouvoir gérer une journée de commandes depuis un mobile, sans créer de doublon, sans voir les données d’un autre restaurant et sans pouvoir modifier des informations hors de ses permissions.

---

# Version 0.6 — Thème et intégrations Elementor

Le thème fournira les templates de l’accueil, du menu, des catégories, du produit, du panier, de la confirmation, du contact, des horaires et de la livraison. Il utilisera du HTML serveur et les zones sémantiques `header`, `nav`, `main`, `section`, `article`, `aside` et `footer`.

Les zones Elementor prévues seront le header, le hero, l’avant-menu, l’après-menu, la sidebar, l’avant-footer et le footer. Les widgets seront Menu restaurant, onglets catégories, carte produit, déclencheur Quick View, déclencheur panier, appel WhatsApp, horaires et adresse.

Elementor pourra modifier les sections et les styles, mais ne sera jamais responsable des prix, produits, panier ou commandes. Le thème doit rester fonctionnel avec Elementor désactivé.

Google indique que la visibilité dans les fonctions génératives repose sur les bases du SEO : contenu utile, pages crawlables, structure claire, HTML compréhensible, bonne expérience mobile et réduction des doublons [3]. Le thème n’aura donc pas besoin de `llms.txt` ni d’un balisage spécial pour les LLM. Les données structurées seront cohérentes avec le contenu visible : Restaurant/LocalBusiness, Product, Offer et BreadcrumbList lorsque les données sont disponibles.

Les objectifs de performance seront mesurés avec les Core Web Vitals : LCP ≤ 2,5 secondes, INP < 200 ms et CLS < 0,1 [4].

---

# Version 0.7 — Duplication client

L’installation d’un nouveau restaurant passera par un assistant qui demande le nom, logo, couleurs, adresse, téléphone, horaires, zones de livraison, numéro WhatsApp, devise et mode de commande.

Le plugin exportera les réglages et le design dans un JSON versionné. Le catalogue sera importé par CSV ou par les outils WooCommerce. L’assistant contrôlera uniquement les dépendances présentes sur le site. Il ne contiendra pas de vérification Dokan héritée si Dokan n’est pas installé.

Avant publication, il contrôlera qu’aucun produit n’est sans prix, qu’aucune catégorie active n’est vide, qu’aucun bouton principal ne manque, qu’un numéro WhatsApp est configuré si le mode WhatsApp est choisi et qu’aucune page publique importante n’est accidentellement en `noindex`.

Chaque mise à jour devra migrer les réglages sans écraser le catalogue ou les commandes. Un export, une sauvegarde et une procédure de rollback seront documentés.

---

# Version 1.0 — Tests, migration et livraison

## Matrice de tests

| Domaine | Tests obligatoires |
|---|---|
| Catalogue | Produits simples, variables, hors stock, variations avec prix différents |
| Panier | Ajout, quantité, suppression, session invité, session connectée, mobile |
| Commande | Taxes, frais, minimum, statut, double clic, erreur réseau et idempotence |
| WhatsApp | Numéro valide, numéro invalide, commande créée, lien relançable |
| Compatibilité | HPOS, WooCommerce, Elementor actif/désactivé, thème sans Elementor |
| Accessibilité | Clavier, focus, Échap, contraste, lecteurs d’écran et motion réduite |
| Performance | Assets conditionnels, absence de JS inutile, cache et Core Web Vitals |
| Sécurité | Capacités, nonces, échappement, validation des entrées et accès croisés |
| Déploiement | Installation, mise à jour, import/export, sauvegarde et rollback |

## Retrait progressif des extensions actuelles

| Extension | Retrait prévu |
|---|---|
| Reno Quick View | Après la validation de la V0.2 |
| Side Cart WooCommerce | Après la validation de la V0.3 |
| OneClick Chat to Order | Après la validation de la V0.4 |
| Restaurant Owner Dashboard | Après la validation de la V0.5 |
| Essential Addons | Après confirmation qu’aucun modèle ne l’utilise |
| WPCode | Après migration et test de chaque snippet utile |
| Elementor/Royal Addons | Après migration du design, si le client accepte le thème natif |
| WooCommerce | Ne jamais retirer |
| LiteSpeed Cache | Conserver séparément si nécessaire |
| Hostinger Tools | Conserver seulement pour les fonctions Hostinger |

La migration se fera une extension à la fois sur staging, avec test de régression après chaque retrait. Le site actuel ne sera pas migré brutalement en production.

## Definition of Done pour la V1.0

La V1.0 est prête lorsque le package s’installe sur un nouveau site WooCommerce, affiche le menu, gère le Quick View, maintient un panier cohérent, crée une commande WhatsApp unique, permet la gestion restaurant selon les rôles, fonctionne avec ou sans Elementor, passe la matrice de tests et peut être restauré sans perte de données.

## Prochaine action

La prochaine action n’est pas de coder directement le dashboard. Il faut commencer par produire les livrables de la phase 0.0 : **contrat de données, statuts, permissions, idempotence, événements JavaScript, hooks WooCommerce et tests**. Une fois ces documents validés, nous pourrons construire Restaurant Suite V0.1 sur une copie de Chef Anass.

> **Décision finale :** cette roadmap est approuvée pour exécution. Elle reprend les meilleures idées des extensions fournies, conserve Elementor comme outil de personnalisation, réduit les dépendances et protège le projet contre un périmètre trop large dès la première version.

## Références

[1]: https://developer.woocommerce.com/docs/features/orders/high-performance-order-storage/ "WooCommerce Developer Documentation — High-Performance Order Storage"

[2]: https://developer.wordpress.org/plugins/ "WordPress Plugin Developer Handbook"

[3]: https://developers.google.com/search/docs/fundamentals/ai-optimization-guide "Google Search Central — Optimizing your website for generative AI features on Google Search"

[4]: https://developers.google.com/search/docs/appearance/core-web-vitals "Google Search Central — Understanding Core Web Vitals and Google search results"
