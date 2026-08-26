# Matrice de tests — phase 0.0

| Contrat | Test prévu | Bloquant |
|---|---|---|
| Données | JSON valide, propriétaire unique, commandes CRUD, aucun prix navigateur fiable | Oui |
| Statuts | Identifiants stables, transitions déclarées, terminalité | Oui |
| Idempotence | Même clé = même commande, concurrence et relance WhatsApp | Oui dès V0.4 |
| Permissions | Chaque endpoint possède une capacité et un cas de refus | Oui |
| Événements | Noms stables, producteur/consommateur uniques, pas de prix fiable | Oui |
| Compatibilité | PHP/WP/WC/HPOS/Elementor selon matrice | Oui avant release |
| Qualité | Composer validate, lint, PHPCS, PHPStan, PHPUnit et validation contrats | Oui |

Les tests de menu, Quick View, panier et dashboard commencent dans leurs phases respectives ; la phase 0.0 ne développe pas encore ces modules.
