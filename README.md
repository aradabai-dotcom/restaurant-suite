# Restaurant Suite

Base de conception et de développement d’une suite WordPress duplicable pour les restaurants.

## Statut du projet

Le dépôt contient actuellement la **roadmap**, les **cahiers des charges**, l’architecture, les audits des extensions de référence, la stratégie de matérialisation du dépôt et le pack CDC v3. Le plugin et le thème métier ne sont pas encore implémentés : la prochaine étape est de matérialiser le dépôt technique, puis de valider la phase 0.0 avant de commencer la V0.1.

## Objectif produit

Créer une solution propriétaire légère basée sur WooCommerce, avec un plugin Restaurant Suite Core et un thème Restaurant Base Theme compatible Elementor. La solution doit reprendre les meilleures expériences de Quick View, Side Cart, live preview, commande WhatsApp et dashboard restaurant, tout en réduisant les dépendances, le JavaScript et les conflits entre extensions.

WooCommerce reste la source unique de vérité pour les produits, variations, prix, stock, panier et commandes. Elementor est une couche de personnalisation optionnelle ; il ne doit pas être nécessaire au fonctionnement du menu, du panier ou de la commande.

## Organisation

| Dossier | Contenu |
|---|---|
| `docs/roadmap/` | Roadmap validée et versions 0.0 à 1.0 |
| `docs/cdc/` | CDC maître et CDC détaillés par phase |
| `docs/architecture/` | Architecture du plugin, du thème et contrat de matérialisation |
| `docs/audits/` | Audits des extensions, du dashboard et des archives de référence |
| `docs/tooling/` | DDEV, outillage, matrice d’outils et installation locale |
| `docs/staging/` | Checklist et règles du WordPress vierge réel |
| `releases/` | Archives CDC et checksums validés |
| `decisions/` | Décisions d’architecture et validations |

## Environnements

Le projet utilise trois environnements distincts. La sandbox sert à préparer le code, analyser les archives, lancer le tooling Node et empaqueter. DDEV est l’environnement local canonique et réinitialisable pour PHP, WordPress, WooCommerce, HPOS, panier, commandes, fixtures et migrations. Le WordPress vierge réel est un staging technique pour vérifier HTTPS, cache, cron, emails, permissions et différences d’hébergement.

Le staging réel ne doit jamais recevoir de données client, commandes réelles, mots de passe de production ou secrets. Les scans WPScan, OWASP ZAP et les tests k6 ne ciblent que les URLs explicitement autorisées.

## Ordre de travail obligatoire

```text
1. Matérialiser l’arborescence et les scripts du dépôt
2. Configurer DDEV et les dépendances verrouillées
3. Implémenter les fixtures et le reset idempotent
4. Corriger et exécuter la CI
5. Faire passer make doctor, make install, make reset et make validate
6. Installer le package sur le WordPress vierge réel
7. Approuver le gate de matérialisation
8. Finaliser et approuver les contrats métier phase 0.0
9. Commencer la V0.1
```

## Règles de contribution

Aucun code métier V0.1 ne doit être fusionné avant l’approbation écrite de la phase 0.0. Chaque modification doit indiquer la phase concernée, le test ajouté ou mis à jour, le comportement de rollback et les impacts Elementor/WooCommerce.

Les branches et commits doivent être explicites. Les dépendances doivent être verrouillées. Les logs, screenshots et rapports de CI doivent être nettoyés de toute donnée personnelle ou secret avant publication.

## Documents de départ

Commencer par lire :

1. `docs/roadmap/roadmap-restaurant-suite-finale.md`.
2. `docs/architecture/specification-materialisation-depot.md`.
3. `docs/cdc/restaurant-suite-cdc-v3/README.md`.
4. `docs/cdc/restaurant-suite-cdc-v3/docs/REPOSITORY-MATERIALIZATION-GATE.md`.
5. `docs/tooling/CDC-OUTILS-PAR-VERSION.md`.

## Licence et provenance

Les extensions Quick View et Side Cart fournies servent uniquement de références fonctionnelles et UX pour une réimplémentation indépendante. Le code ne doit pas être copié sans vérification de licence et de compatibilité juridique. Les nouveaux composants du projet devront avoir une licence clairement déclarée avant toute distribution client.
