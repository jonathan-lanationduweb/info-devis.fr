# Architecture WordPress — InfoDevis

## Principe directeur

Séparation stricte affichage / métier :

- **Thème `info-devis`** (enfant Astra) → design, templates, CSS. Peut être changé sans perte de données ni de fonctionnalités.
- **Extension `info-devis-core`** → tout le métier : contenus, rôles, formulaires, matching, SIRET, avis, emails, Stripe, REST, réglages, diagnostics.

Préfixe technique : `idc_` (hooks, options, shortcodes) et `_idc_` (métadonnées). Choisi et déjà présent en base avant la consigne `idv_` ; conservé pour éviter une double convention.

## Thème — `wp-content/themes/info-devis/`

```
style.css                 charte (vert #207752, orange #f97316) + composants idc-*
functions.php             enqueue + presets couleurs Astra
single-artisan.php        fiche artisan (badges, métiers, avis approuvés, CTA devis)
archive-artisan.php       annuaire filtrable (délègue à [idc_artisans])
taxonomy-metier.php       page catégorie de travaux (annuaire filtré)
```

Le rendu des listes/formulaires vit dans l'extension (shortcodes) : le thème ne fait que les envelopper. Un changement de thème conserve 100 % du métier.

## Extension — `wp-content/plugins/info-devis-core/`

```
info-devis-core.php       bootstrap : CPT, taxonomie, rôles, metaboxes, colonnes admin
uninstall.php             désinstallation prudente (les contenus métier ne sont PAS supprimés)
includes/front.php        shortcodes publics + traitement des formulaires (admin-post)
includes/matching.php     idc_match_artisans(), idc_get_demandes_for_artisan(), metabox
includes/siret.php        vérification SIRET (API recherche-entreprises, sans clé)
includes/avis.php         workflow avis : statuts, anti-doublon, recalcul notes, dépôt client
includes/inscription.php  inscription artisan front (compte + fiche en attente de validation)
includes/emails.php       modèles d'emails administrables (options, variables {xxx})
includes/stripe.php       Checkout abonnements + webhook REST signé + écran diagnostic
includes/seo.php          données structurées, noindex des espaces privés
includes/admin-pages.php  menu « Info Devis » : réglages, emails, état du système
```

## Modèle de données

| Donnée | Support WP | Détail |
|---|---|---|
| Catégorie de travaux | Taxonomie `metier` (hiérarchique) | term meta `_idc_icon/image/prix_min/prix_max/meta_title/meta_description` ; services = termes enfants |
| Artisan | CPT `artisan` public + utilisateur WP lié (`_idc_user_id`) | SIRET, ville/CP, rayon, plan, badge, statuts docs, note moyenne… (~20 meta `_idc_*`) |
| Client | Utilisateur WP rôle `client` | téléphone/adresse en user meta `_idc_*` |
| Demande de devis | CPT `demande_devis` (non public) | référence, contact, urgence, budget, statut, consentement horodaté |
| Avis | CPT `avis` (non public) | note 1-5, statut (pending/approved/refused/reported/hidden), lien artisan + client, réponse artisan |
| Réalisation | CPT `realisation` public | artisan lié, ville, budget, validation admin |
| Guide | CPT `guide` public (`/guide/{slug}`) | contenu HTML depuis JSON historiques, métier lié |
| RDV | CPT `rdv` (non public) | artisan + client, date/heure, durée, statut |
| Abonnement | user meta + meta fiche artisan | plan, statut Stripe, IDs client/subscription Stripe |

## Rôles et capacités

- `administrator` : tout.
- `gestionnaire` : gestion des contenus métier (edit/publish sur CPT métier + listes) sans réglages ni extensions.
- `artisan` : `read`, `upload_files` ; agit sur SES données via les écrans front (jamais wp-admin).
- `client` : `read` ; idem via front.

Contrôles d'accès : `is_user_logged_in` + rôle + propriété de l'objet (`_idc_user_id` / `_idc_client_user_id`) + nonce sur chaque action.

## Flux clés

1. **Demande de devis** : form front → `admin_post_idc_submit_devis` → validation/nonce/honeypot/rate-limit → CPT + meta + terme → emails (admin, client, artisans matchés via modèles administrables).
2. **Matching V1** : intersection catégorie(s) ∩ même département (2 premiers chiffres CP), artisans sans CP inclus (profil incomplet). Documenté dans `logique-matching.md`.
3. **Avis** : dépôt par client connecté (1 avis max par couple client/artisan) → statut `pending` → validation admin → recalcul automatique `_idc_rating_avg/_idc_rating_count` sur la fiche → affichage fiche artisan.
4. **Inscription artisan** : form front → user rôle `artisan` (mdp choisi) + fiche CPT `pending` → email admin pour validation (demande du TODO historique).
5. **Stripe** : page Tarifs pro → Checkout Session (API REST, clés en wp-config) → retour → webhook `/wp-json/idc/v1/stripe-webhook` (signature vérifiée, idempotent par event id) → meta abonnement.

## Ce que le cahier des charges prévoit et qui reste en V2

Google OAuth, chatbot, signatures, centre de notifications, file d'attente d'emails avec relances (WP-Cron), calendrier de réservation front (façon Doctolib), matching géolocalisé par rayon, taxonomies `zone_intervention`/`specialite` (le couple département + rayon suffit en V1). Chaque report est tracé dans `matrice-migration.md`.
