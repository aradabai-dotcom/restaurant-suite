# Contrat des événements JavaScript — phase 0.0

Restaurant Suite possède un seul store public et un seul système d’événements. Les événements de référence sont `crs:cart:add`, `crs:cart:update`, `crs:cart:remove`, `crs:cart:refresh`, `crs:quickview:open`, `crs:quickview:close` et `crs:order:created`.

Aucun payload navigateur ne contient un prix considéré comme fiable. Le serveur reste l’autorité. Le contrat machine est dans `docs/contracts/events.json`.
