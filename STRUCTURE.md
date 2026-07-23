# InfoDevis — architecture du code

Le site repose sur **3 composants maison** (le reste = cœur WordPress + extensions tierces).
Chacun a une responsabilité claire. Rien de « métier » n'est dans le cœur WordPress.

```
wp-content/
├── themes/info-devis/          ← présentation (front public + espaces client/artisan)
├── plugins/info-devis-core/    ← logique métier (devis, RDV, messagerie, paiements…)
└── plugins/infodevis-admin/    ← back-office SaaS custom (/infodevis-admin/)
```

## Conventions de préfixes

| Préfixe | Domaine | Exemple |
|---|---|---|
| `idc_` | fonctions du plugin cœur | `idc_current_artisan_fiche()` |
| `_idc_` | métadonnées (post meta) | `_idc_date_rdv` |
| `idv_` | thème (fonctions, options) | `idv_require_role()` |
| `ida_` | plugin admin | `ida_app_url()` |

## `info-devis-core` — un fichier = une responsabilité

Tous les modules sont chargés depuis `info-devis-core.php` via `require_once`.
`includes/` :

| Fichier | Rôle |
|---|---|
| `front.php` | formulaire de devis, recherche, pages publiques |
| `matching.php` / `siret.php` | mise en relation artisans, vérif SIRET |
| `profil.php` / `inscription.php` | comptes, inscription |
| `artisan-front.php` / `apparence.php` / `artisan-blog.php` | espace artisan |
| `rdv.php` / `dispos.php` | rendez-vous et disponibilités |
| `messages.php` / `notifications.php` | messagerie, notifications |
| `avis.php` | avis clients |
| `signature.php` / `stripe.php` | signature électronique, paiements |
| `documents.php` | justificatifs KYC (dossier privé) |
| `emails.php` | e-mails transactionnels administrables |
| `seo.php` / `tracking.php` | SEO, RGPD + GA4 |
| **`security.php`** | **durcissement** : session 1 h, en-têtes HTTP, XML-RPC off, anti-énumération |
| **`access-control.php`** | **contrôle d'accès** (isolé) : admin bar, wp-admin réservé aux admins, connexion via `/connexion/` uniquement |
| `admin-pages.php` | pages de réglages historiques |

> **Sécurité = 2 fichiers dédiés.** `security.php` (durcissement passif) et
> `access-control.php` (règles d'accès actives). Pour changer une règle d'accès,
> on sait exactement où aller.

## Secrets : le fichier `.env`

Aucun secret n'est écrit en dur dans le code.

- `deploy/wp-config-prod.php` lit un fichier **`.env`** (à côté de lui) au démarrage.
- `deploy/.env.example` = modèle **versionné** (sans valeurs).
- `.env` = fichier réel **jamais committé** (dans `.gitignore`) et **bloqué en HTTP**
  par le `.htaccess`. Contient : identifiants base, 8 sels WordPress, clés Stripe, SMTP.
- Pour changer un secret : on édite `.env`, rien d'autre.

## Front : le thème `info-devis`

- `functions.php` : enqueue des assets, helpers, rôles.
- `templates/` : gabarits des espaces (tpl-artisan-*, tpl-client-*).
- `template-parts/` : morceaux réutilisables (sidebars, cartes, cloche de notifs).
- `page-*.php` : pages spécifiques (connexion, inscription…).
- `assets/css/tailwind.build.css` : **CSS Tailwind compilé** (versionné).
  Sources de build (`tailwind.config.js`, `package.json`) exclues de la prod.

## Build & déploiement (rappels)

- CSS : `npm run build:css` (Tailwind) → régénère `tailwind.build.css`.
- Déploiement : voir `deploy/DEPLOY-WINSCP.md`.
- Base de prod prête : `backups/info-devis-prod-*.sql` (URLs déjà en https).
- Application mobile : projet séparé `info-devis-app` (Capacitor iOS/Android).
