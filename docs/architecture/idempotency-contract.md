# Contrat d’idempotence WhatsApp — phase 0.0

Chaque tentative de création reçoit une clé unique. Le serveur vérifie la clé avant toute création, recalcule les prix depuis WooCommerce et renvoie la commande existante si la requête est répétée. Si la commande existe mais que WhatsApp ne s’ouvre pas, seul le lien est relançable ; une nouvelle commande ne doit pas être créée.

Les scénarios obligatoires sont le double clic, deux requêtes concurrentes, la perte réseau, le panier modifié, le produit indisponible, la variation supprimée et l’échec d’ouverture de WhatsApp.
