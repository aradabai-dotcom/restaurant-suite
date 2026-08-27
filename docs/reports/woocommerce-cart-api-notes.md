# Notes de référence WooCommerce — Cart API

Date de consultation : 27 août 2026.

La référence officielle de `WC_Cart` documente `remove_cart_item()` comme la méthode de suppression d’une ligne et `set_quantity()` comme la méthode de changement de quantité. La documentation officielle du Store API précise que les mutations POST du panier nécessitent un nonce ou un cart token et renvoient l’état complet du panier. Elle définit `POST /wc/store/v1/cart/remove-item` avec `key`, ainsi que `POST /wc/store/v1/cart/update-item` avec `key` et `quantity`. Les erreurs 409 du Store API indiquent normalement une clé absente/invalide et peuvent inclure l’état courant du panier.

Références :

1. [WooCommerce Code Reference — WC_Cart](https://woocommerce.github.io/code-reference/classes/WC-Cart.html)
2. [WooCommerce Developer Docs — Cart API](https://developer.woocommerce.com/docs/apis/store-api/resources-endpoints/cart/)

Constat pour V0.3 : l’endpoint propriétaire appelle correctement les méthodes classiques, mais le staging montre que les paramètres de mutation semblent ignorés ou que la ligne n’est pas retrouvée dans le contexte REST (`update` HTTP 200 mais quantité inchangée ; `remove` HTTP 409). Il faut inspecter le contexte de session et envisager un adaptateur Store API officiellement documenté, sans maintenir de panier parallèle.
