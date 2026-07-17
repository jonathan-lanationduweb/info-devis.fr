# Tests — campagne du 15 juillet 2026 (local)

Méthode : requêtes HTTP réelles (visiteur, client connecté, artisan), scripts CLI WordPress pour les vérifications serveur, `php -l` sur chaque fichier livré.

## Installation

| Test | Résultat |
|---|---|
| Activation thème enfant + extension, réactivation | ✔ sans erreur fatale |
| `php -l` sur les 11 fichiers de l'extension + 5 du thème | ✔ 0 erreur |
| État du système (diagnostic) | ✔ tous les voyants verts |

## Visiteur (HTTP)

| Page | Résultat |
|---|---|
| Accueil, annuaire, fiche artisan, catégorie métier, guides (archive), réalisation (archive), demande de devis, inscription artisan, tarifs pro, contact, actualités, mentions légales, confidentialité, CGU, sitemap | ✔ HTTP 200 (15/15) |
| Page inexistante | ✔ HTTP 404 |
| Menu, hero, grille catégories, cartes artisans | ✔ contenus présents |
| Aucune erreur/warning PHP visible | ✔ |

## Parcours devis (E2E, non connecté)

Nonce récupéré → soumission → redirection `?devis=ok` → demande `DV260715-913G` créée avec référence, statut `pending`, consentement horodaté ✔. Rate-limit et pot de miel actifs ✔.

## Parcours client (E2E)

Création compte test → connexion (`wp-login.php`) → dashboard « Bonjour Claire » ✔ → dépôt d'avis 5★ ✔ → 2e dépôt refusé (anti-doublon) ✔ → approbation (modération) → note fiche recalculée 5.00/5 (1 avis) + répartition ✔ → avis visible sur la fiche publique + `AggregateRating` dans le JSON-LD ✔.

## Parcours artisan (E2E)

Inscription front (nonce, consentement) → compte `marc.testeur` rôle artisan + fiche « Plomberie Test SARL » en `pending` ✔ → emails admin + bienvenue journalisés.

## Stripe (mode test)

| Test | Résultat |
|---|---|
| Création Checkout Session réelle | ✔ `cs_test_…`, URL checkout.stripe.com |
| Webhook signé `checkout.session.completed` | ✔ 200, plan silver appliqué (compte + fiche) |
| Rejeu du même événement | ✔ « already processed » |
| Signature invalide / absente | ✔ 400 |

## Sécurité

| Test | Résultat |
|---|---|
| Page admin Info Devis sans connexion | ✔ « Accès refusé » |
| Capacité `idc_manage` (gestionnaire) | ✔ accès accordé, puis refus après suppression du compte |
| Secrets dans les pages d'admin | ✔ aucun |
| noindex espace membre | ✔ |

## SEO

Sitemap 200 ✔ · JSON-LD ProfessionalService ✔ · AggregateRating conditionnel ✔ · 404 correcte ✔ · permaliens propres ✔.

## Tests non réalisés (à faire)

- Paiement test complet dans le navigateur (carte `4242 4242 4242 4242`) — la session est créée, il reste à dérouler l'écran Stripe à la main.
- Emails : vérifier la réception dans l'inbox Mailtrap (les envois sont journalisés côté site ; les identifiants sandbox datent de l'ancienne appli et peuvent être expirés).
- Responsive : le thème (Astra + grilles CSS) est responsive par construction — faire une passe visuelle mobile.
- Réinitialisation de mot de passe (dépend de la réception d'emails).
- Montée de version WordPress/extension.

## Données de test à supprimer avant production

- Utilisateurs : `client.test@example.com` (n'existe pas en compte — juste une demande), `marc.testeur@example.com` (ID 7), `claire.cliente@example.com` (ID 8)
- Demande `DV260715-913G` + demande du test E2E initial
- Avis de test (ID 58) et fiche « Plomberie Test SARL » (ID 57)
- Les 6 comptes migrés de l'ancienne base sont aussi des données de test (à confirmer avec le propriétaire)
