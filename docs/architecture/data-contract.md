# Contrat de données — phase 0.0

WooCommerce est la source de vérité pour produits, catégories, variations, prix, stock, panier et commandes. Restaurant Suite ne duplique pas le catalogue dans une table parallèle. Les commandes sont lues et écrites via les objets CRUD WooCommerce ; les requêtes SQL directes sur les tables de commandes sont interdites.

La V0.1 supporte les produits simples et les variations natives WooCommerce. Une remarque libre ne peut pas modifier le prix. Les allergènes sont des métadonnées validées et rendues côté serveur. Les horaires et réglages restaurant résident dans l’option versionnée `crs_settings`.

Le contrat machine est dans `docs/contracts/data-contract.json`.
