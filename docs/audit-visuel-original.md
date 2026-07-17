# Audit visuel — Site original InfoDevis (référence absolue)

**Original :** `C:\wamp64\www\info-devis` — servi sur `http://localhost/info-devis` (fonctionnel, vérifié le 15/07/2026)
**ZIP de secours :** `C:\wamp64\backups\info-devis\20260715-1509\ancienne-application.zip`
**Captures de référence :** `docs/captures/original/` (16 pages publiques, 1440px) · WP actuel : `docs/captures/wordpress/`

## 1. Pile front du site original (à reproduire)

| Élément | Détail |
|---|---|
| Layout maître | `views/layout/main.php` (~780 lignes : head, styles inline, navbar via `includes/navbar.php`, footer via `includes/footer.php`, chatbot, scripts) |
| CSS | `assets/css/theme.css` (design tokens — source de vérité couleurs), `portfolio.css`, `rdv.css` + **Tailwind CDN** (config inline : couleurs primaires via variables CSS, thèmes artisan) + styles inline (`glass-nav`, `editorial-shadow`, animations `reveal`) |
| `main.css` | Legacy — utilisé uniquement par `layout/404.php` |
| Typographies | **Newsreader** (titres, serif, italiques d'accent) + **Manrope** (corps) — Google Fonts |
| Icônes | **Font Awesome 7** (CDN) + **Material Symbols Outlined** — JAMAIS d'emojis |
| JS | `assets/js/main.js` (menus, reveal, notifications, formulaires), `portfolio.js` |
| Images | 23 fichiers dans `assets/img/` (favicon SVG, og-default, photos) + photos externes (Unsplash) dans les vues |
| Chatbot | Widget flottant bas-droite (bulle verte) |
| Identité | Logo texte « *Info-Devis* » serif italique ; navbar blanche épurée ; héro photo pleine largeur, titre serif 2 lignes + mot « travaux » en italique vert ; barre de recherche métier + code postal ; bandeau vert 3 garanties (100 % GRATUIT / RÉPONSE RAPIDE / ARTISANS CERTIFIÉS) ; cartes métiers photos avec badge catégorie ; fond `#faf9f8` |

## 2. Inventaire des routes (extrait de `index.php`, ~120 routes)

### Pages publiques

| Route | Contrôleur | Vue | WP cible | État reproduction |
|---|---|---|---|---|
| `GET /` | HomeController::index | `home/index.php` | front-page.php | ✅ **reproduite 1:1** (héro+recherche, bandeau, bento expertises, comment ça marche, pros, citation, CTA — comparaison captures OK, footer aligné au pixel) |
| `GET /categories` | CategoryController::index | `categories/index.php` | page-categories.php | ✅ reproduite 1:1 (hero éditorial, bento 18 métiers avec offsets/carte wide, citation, CTA) |
| `GET /categorie/{slug}` | CategoryController::show | `categories/show.php` + `categories/slugs/{slug}.php` (18 vues SEO dédiées !) | taxonomy-metier.php | ✅ vue universelle reproduite 1:1 (hero 5/7, services, trust + processus, CTA) — 🟡 contenus SEO longs des 18 vues `slugs/` à injecter (term description ou champs hero en term meta) |
| `GET /service/{slug}`, `GET /metiers/{cat}/{slug}` | ServiceController | `pages/prestation_detail.php` | à définir | ❌ non migrée |
| `GET /artisans` | ArtisanController::index | vue artisans | archive-artisan.php | 🟡 fonctionnel, visuel non fidèle |
| `GET /professionnels` | ArtisanController::annuaire | `pages/professionnels.php` | page-professionnels.php | ✅ reproduite 1:1 (titre pill, search-pill, pastilles filtres, tri, section Gold « recommandés », cartes expert-card, pagination) |
| `GET /artisan/{id}` | ArtisanController::show | `pages/artisan_profil.php` (+ `partials/tabs_artisan.php`, avis, portfolio) | single-artisan.php | 🟡 données OK, interface à onglets non reproduite |
| `GET /realisations/{slug}` | ProjetController::detail | `pages/projet_detail.php` | single realisation | ❌ non migrée |
| `GET /devis` + `POST` + `/confirmation` | DevisController | `home/devis.php`, `devis-contact.php`, `devis-confirmation.php` (+ `partials/devis-trust.php`) | page-devis.php + page-devis-confirmation.php | ✅ reproduits 1:1 : sidebar progression + garanties, tuiles multi-métiers (devis groupé), urgence/budget par tranches, consentements, soumission AJAX JSON (action `idc_submit_devis2`), confirmation étape 3/3 avec référence + liste des métiers — testé E2E (demande DV260715-XQJB, 2 métiers, budget 1000_5000). 🟡 `devis-contact.php` (variante) non porté |
| `GET /blog`, `/blog/{slug}` | BlogController | `blog/index.php`, `blog/show.php` | Actualités | 🟡 contenus migrés, visuel à refaire |
| `GET /guides`, `/guides/{slug}` | HomeController | `home/guides.php`, `pages/guide_detail.php` | CPT guide | 🟡 contenus migrés, visuel à refaire |
| `GET /tarifs-pro` | HomeController::tarifs | `home/tarifs.php` | Tarifs professionnels | 🟡 Stripe OK, visuel à refaire |
| `GET /nos-niveaux-de-confiance` | HomeController::niveaux | `pages/nos_niveaux_de_confiance.php` | à créer | ❌ non migrée |
| `GET /contact` + POST | ContactController | `home/contact.php` | page Contact | ❌ formulaire + design à reproduire |
| Pages légales (4) | LegalController | `legal/*.php` | pages créées | 🟡 textes génériques — reprendre les textes originaux |
| 404 | — | `layout/404.php` | 404.php | ❌ à reproduire |

### Authentification

| Route | Vue | WP cible | État |
|---|---|---|---|
| `/connexion`, `/inscription` (+ succès/vérif email), `/mot-de-passe-oublie`, `/reset-password` | `auth/login.php`, `auth/register.php` (choix client/artisan), `auth/forgot*.php`, `auth/reset.php` | pages front dédiées (pas wp-login brut) | ❌ V1 utilise wp_login_form générique |
| `/auth/google` + callback | AuthController | extension (hooks authenticate) | ⬜ reporté (documenté) |

### Espace client (sidebar `client/_sidebar.php`)

Dashboard, profil, devis, avis (dépôt/modif/suppression), signature, paiement, calendrier, disponibilités artisans → vues `client/*.php`. **État : ✅ reproduit** — sidebar 1:1 (drawer mobile + desktop + bottom nav, `template-parts/sidebar-client.php`), tableau de bord 1:1 (`/dashboard/client/` : stats, dernières demandes, accès rapides, encart calendriers), Mes Projets 1:1 (filtres par statut, cartes, citation), Mes Avis (liste + dépôt, statuts de modération, réponse artisan), Mon profil (coordonnées + mot de passe avec vérification de l'actuel), Mes RDV (CPT rdv), Dispos artisans. Garde d'accès `idv_require_role` : anonyme → 302 /connexion/?redirect_to, artisan → redirigé. Testé E2E (6 écrans + mise à jour profil + permissions). 🟡 Restent : signature (`signature.php`), paiement client, calendrier artisan détaillé (`calendrier.php`) — dépendent des modules signature/RDV V2 ; avis.php et profile.php à recomparer visuellement avec les vues originales (implémentés dans le design system, pas encore comparés capture à capture).

### Espace artisan (sidebar `artisan/_sidebar.php`)

Dashboard, leads, profil, stats, avis (+réponse), blog artisan (Silver/Gold), apparence (thèmes de couleur !), calendar, disponibilités, RDV (liste/détail/statuts/notes/montant/créneaux/documents), abonnement, documents, vérification, portfolio (projets CRUD + photos) → vues `artisan/*.php`. **État : 🟡 cœur reproduit** — sidebar 1:1 (groupes Principal / Mon compte, badge de niveau ou « ⏳ En validation », drawer + bottom nav — liens limités aux écrans portés), **Mes opportunités 1:1** (filtres, grande carte bento + petites cartes, accepter/refuser AJAX avec statut par artisan `_idc_lead_status_{fiche}`, bloc performance), tableau de bord (stats + dernières opportunités + accès rapides), Mes avis (+ réponse artisan, propriété vérifiée), Mes réalisations (liste + soumission → validation admin), Mon profil (description, zone, rayon, métiers, disponibilité), Documents (6 statuts migrés + envoi par email), Abonnement (plans + Stripe Checkout). Règle métier conservée : **seules les fiches validées (publiées) reçoivent des leads**. Testé E2E (7 écrans, acceptation de lead, mise à jour fiche multi-métiers). ⬜ Restent : stats détaillées, apparence (thèmes), blog artisan, calendar/disponibilités/RDV (dépend du module RDV), vérification (upload docs), messages ; dashboard/avis/projets/profile à recomparer capture à capture avec les vues originales.

### Admin métier (sidebar `admin/_sidebar.php`)

17 écrans : dashboard, artisans (+détail, validation, note, documents), devis, users, blog (+validation), guides (CRUD), projets (+validation photos), paiements, abonnements, chatbot, metrics, catégories (CRUD), migrations. **État : 🟡 remplacé par l'admin WordPress (CPT) — fonctionnel mais sans les écrans de validation dédiés (validation artisan/documents = metabox simple).**

### Module RDV / disponibilités (routes `/rdv/*`, `/mes-rdv`, `/dashboard/*/disponibilites|rdv|calendar`)

**État : ✅ V1 fonctionnelle testée E2E (15/07/2026)** — `includes/rdv.php` :
- Artisan : `/dashboard/artisan/disponibilites/` (semaine type jour par jour, indisponibilités ponctuelles, durée RDV, délai de prévenance, plafond/jour, message calendrier + aperçu des créneaux réels sur 7 jours) et `/dashboard/artisan/rdv/` (interventions : confirmer / annuler / terminer, client notifié).
- Client : bouton « Prendre RDV » sur `/dashboard/client/disponibilites/`, page `/prendre-rdv/?pro={id}` (créneaux recalculés côté serveur : passé/délai/indispos/plafond/créneaux pris), `/mes-rdv/` (suivi + annulation).
- Protections vérifiées : double réservation refusée (recalcul serveur au moment du POST), créneau passé impossible, client n'annule que SES RDV, artisan ne gère que SA fiche.
- Emails : rdv_nouveau_artisan, rdv_recap_client, rdv_confirme_client, rdv_annule (modèles administrables).
- ⚠️ Piège WordPress documenté : le paramètre s'appelle `pro` car `artisan` est un query var réservé (slug du CPT public) → 404 sinon.
- 🟡 Restent : vue `disponibilites.php` originale (922 lignes) et `calendar.php`/`rdv_detail.php` à recomparer visuellement (V1 = design system fidèle, pas encore de comparaison capture à capture) ; notes/montant/documents par RDV ; proposition de créneau alternatif par l'artisan.

### API historiques

Notifications (cloche header), checkout Stripe, devis, artisans, messages, chatbot, favoris, créneaux RDV. **État : 🟡 Stripe porté (REST idc/v1), le reste ⬜.**

## 3. Composants réutilisables identifiés (`views/partials/`)

`artisan_badge.php`, `badge.php`, `card_pro.php` (carte artisan), `card_projet.php`, `creneaux_picker.php` (RDV), `devis-trust.php`, `notifications_bell.php`, `pagination.php`, `tabs_artisan.php` → à porter en `template-parts/` du thème.

## 4. Écarts majeurs V1 → original (constat)

1. Thème enfant Astra générique au lieu du design éditorial Newsreader/Manrope/Tailwind.
2. Emojis à la place des icônes Font Awesome / Material Symbols.
3. Page d'accueil inventée (hero dégradé, cartes plates) au lieu du héro photo + recherche + bandeau garanties + cartes photos.
4. Header/footer Astra (mention « Propulsé par ») au lieu de la navbar glass + footer original.
5. Formulaire de devis simplifié en une étape au lieu du parcours original.
6. Dashboards = tableaux génériques au lieu des interfaces sidebar complètes.
7. Pages absentes : catégories (18 vues SEO), services/prestations, professionnels, niveaux de confiance, réalisation détail, auth stylée.
8. Textes réécrits au lieu des contenus originaux.

Mise à jour de ce document au fil de la reconstruction : colonne « État » page par page, avec captures avant/après dans `docs/captures/comparaisons/`.
