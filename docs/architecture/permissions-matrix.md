# Matrice des permissions — phase 0.0

Chaque endpoint déclare une capacité, vérifie l’identité, le nonce, les entrées et le périmètre du restaurant. La cuisine ne voit que les informations nécessaires à la préparation ; le livreur ne peut pas modifier les montants ni les réglages.

La matrice machine se trouve dans `docs/contracts/permissions.json`. Les tests doivent inclure au moins un refus par endpoint et par rôle non autorisé.
