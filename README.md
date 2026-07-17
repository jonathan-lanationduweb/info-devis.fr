# info-devis

Plateforme de mise en relation entre particuliers et artisans qualifiés
(demande de devis, annuaire, rendez-vous, avis, messagerie), construite sur **WordPress**.

## Contenu de ce dépôt

Ce dépôt versionne **le code métier** du projet (pas le cœur WordPress ni les extensions tierces) :

```
wp-content/
├── themes/
│   └── info-devis/            → thème principal (front + espaces client/artisan)
└── plugins/
    ├── info-devis-core/       → logique métier (devis, matching, RDV, avis, Stripe,
    │                            emails, messagerie, signature, notifications, chatbot…)
    └── infodevis-admin/       → back-office SPA (REST idc/v1/admin/*)
docs/                          → documentation (audit, tests, architecture…)
deploy/wp-config-sample.php    → modèle de configuration (sans secrets)
DEPLOY.md                      → procédure de déploiement pas à pas
```

## Ce qui n'est PAS dans le dépôt (volontairement)

- **Cœur WordPress** (`wp-admin/`, `wp-includes/`, `wp-*.php`) — installé séparément.
- **Extensions tierces** (Yoast SEO, WP Mail SMTP, Akismet) — réinstallées depuis l'admin.
- **`wp-config.php`** et les **secrets** (clés Stripe, SMTP, sel de sécurité).
- **`backups/`**, **`wp-content/uploads/`** et la **base de données**.

> ⚠️ Un site WordPress ne se déploie pas uniquement depuis ce dépôt : il faut aussi
> WordPress, une base de données et un `wp-config.php` renseigné. Voir **[DEPLOY.md](DEPLOY.md)**.

## Stack

WordPress · PHP 8.3 · MySQL · thème Tailwind (build à prévoir pour la prod) ·
Stripe (abonnements artisans) · SMTP (Brevo en production).
