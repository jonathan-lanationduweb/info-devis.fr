# Matrice de migration — InfoDevis (appli MVC) → WordPress

Légende état : ✅ fait · 🟡 partiel · ⬜ à faire

| Fonctionnalité | Fichiers historiques | Tables historiques | Route historique | Destination WordPress | Contenu / taxonomie | Rôle | Rendu | Méthode migration | État | Tests |
|---|---|---|---|---|---|---|---|---|---|---|
| Catégories métiers | models/Category*, views métiers | `categories` (18) | `/metiers`, `/metier/{slug}` | Taxonomie `metier` (hiérarchique, term meta icône/image/prix/SEO) | `metier` | — | `taxonomy-metier.php` | Script idempotent (terms + term meta) | ✅ | HTTP 200, 18 termes |
| Annuaire artisans | controllers/Artisan*, views/artisans | `artisans` (3), `artisan_categories` | `/artisans`, `/artisan/{id}` | CPT `artisan` public + fiche user liée | CPT `artisan` + tax `metier` | `artisan` | `archive-artisan.php`, `single-artisan.php`, `[idc_artisans]` | Script (posts + meta + terms + lien user) | ✅ | HTTP 200, 3 fiches |
| Comptes utilisateurs | models/User, auth | `users` (6) | `/login`, `/register` | Utilisateurs WP natifs, rôles `client`/`artisan`/`gestionnaire`/admin | — | tous | `[idc_dashboard]`, wp-login | Script (hash bcrypt préservés) | ✅ | Connexion OK |
| Demandes de devis | controllers/Devis*, chatbot | `devis` (2), `devis_categories` | `/devis` | CPT `demande_devis` (privé, admin + dashboards) | CPT + tax `metier` | client | `[idc_devis_form]` | Script + formulaire front | ✅ | Soumission E2E OK |
| Matching artisans | services/Matching | `artisan_zones`, `zones` | interne | `idc_match_artisans()` — métier + département, metabox admin, emails | — | — | metabox + dashboard artisan | Réécriture V1 (rayon géoloc = V2) | 🟡 | Unitaire manuel |
| Avis clients | controllers/Avis | `avis` (0) | `/avis` | CPT `avis` : statuts, anti-doublon, recalcul note, dépôt client | CPT `avis` | client/artisan | dashboard + fiche artisan | Workflow implémenté (base vide) | ✅ | E2E dépôt+approbation |
| Vérification SIRET | services/Siret | `artisans.siret*` | interne | API publique recherche-entreprises (sans clé), auto à l'enregistrement | — | — | badge fiche | Réécriture | ✅ | Save fiche |
| Guides & prix | controllers/Guide | `guides` (18) | `/guides`, `/guide/{slug}` | CPT `guide` (converti depuis articles migrés) | CPT `guide` | — | archive + single natifs | Script + conversion post_type | ✅ | HTTP 200 |
| Blog | controllers/Blog | `blog_posts` (7) | `/blog` | Articles natifs, catégorie « Blog » | `post` | auteur | natif + Yoast | Script (statuts mappés) | ✅ | HTTP 200 |
| Réalisations / portfolio | uploads, projets | `projets` (1), `projet_photos` | dashboards | CPT `realisation` (artisan, ville, budget, validation) | CPT `realisation` + tax `metier` | artisan | archive + single | CPT créé ; 1 projet test migrable au besoin | 🟡 | Admin OK |
| RDV & disponibilités | controllers/Rdv | `rdv`, `artisan_creneaux`, `availability`, `artisan_indispos` | dashboards | CPT `rdv` administrable (workflow front = phase suivante) | CPT `rdv` | client/artisan | admin | CPT + meta ; front à faire | 🟡 | Admin OK |
| Emails | services/Mail, queue_worker | `queue_jobs`, `relances` | CRON | `wp_mail` + WP Mail SMTP + modèles administrables (Info Devis → Emails) | options | admin | réglages | Réécriture (file d'attente WP-Cron = à faire) | 🟡 | Envoi local Mailtrap |
| Abonnements Stripe | services/Stripe | `abonnements` (2), `paiements` (0) | `/abonnement`, webhook | Checkout Sessions (API REST Stripe), webhook signé `/wp-json/idc/v1/stripe-webhook`, clés en wp-config | meta user/artisan | artisan | page Tarifs pro | Réécriture mode test | 🟡 | Session test + webhook signature |
| Google OAuth | config OAuth | — | `/oauth` | Non repris en V1 — documenté (hooks `authenticate` WP) | — | — | — | ⬜ reporté | ⬜ | — |
| Chatbot | chatbot/ | `chatbot_messages` | widget | Non repris en V1 — le formulaire multi-champs le remplace | — | — | — | ⬜ reporté | ⬜ | — |
| Signatures électroniques | services | `signatures` (0) | dashboards | Non repris en V1 (table vide) | — | — | — | ⬜ reporté | ⬜ | — |
| Documents artisans | uploads/ | `documents` (3) | admin | Statuts conservés en meta fiche artisan ; upload sécurisé front = phase suivante | meta | artisan/admin | metabox | Meta migrées | 🟡 | Admin OK |
| SEO / sitemap | cron_seo.php | `seo_pages` (0) | `/sitemap.xml` | Yoast (titres, meta, sitemap, canonicals) + données structurées maison (ProfessionalService, AggregateRating) | — | — | wp_head | Config + code | ✅ | sitemap_index.xml 200 |
| Consentements RGPD | consents | `consents` (29) | formulaires | Case de consentement + horodatage en meta demande ; outils natifs export/effacement | meta | — | formulaires | Réécriture (historique non migré — données de test) | 🟡 | Formulaire |
| Notifications internes | notifications | `notifications*` | dashboards | Emails (V1) ; centre de notifications = V2 | — | — | — | ⬜ reporté | ⬜ | — |
| File d'attente / CRON | queue_worker, cron_seo | `queue_jobs` | cron système | WP-Cron (relances/recalculs) — à compléter | — | — | — | ⬜ à faire | ⬜ | — |

## Correspondance des URL (base du plan de redirections)

Voir `plan-redirections.csv`. Principe : les slugs publics conservés (`/metier/{slug}`, `/guide/{slug}`, articles) ; les routes applicatives (`/login`, `/dashboard`…) redirigées vers leurs équivalents WP (`/espace-membre/`…).
