# Contrat des statuts — phase 0.0

Les identifiants internes sont stables ; les libellés sont personnalisables. Les transitions sont contrôlées par capacité et ne modifient jamais le montant de la commande.

Le graphe, les états terminaux et les transitions autorisées sont dans `docs/contracts/statuses.json`. Toute transition non déclarée est refusée côté serveur et côté interface.
