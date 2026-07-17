# InfoDevis Admin — Architecture

> Refonte complète du back-office. WordPress reste le moteur (contenus, utilisateurs,
> médias, REST), mais l'expérience d'administration est une application « InfoDevis Admin »
> autonome, au rendu SaaS (Notion / Stripe / Linear / Shopify Admin).

## 1. Principes

1. **WordPress = moteur, pas interface.** Aucun écran wp-admin n'est utilisé au quotidien.
2. **Aucune suppression.** L'existant (plugin `info-devis-core`, thème, wp-admin) reste
   intact et fonctionnel. La nouvelle admin est un plugin **additionnel** : le désactiver
   restitue l'admin WordPress classique.
3. **Réutilisation maximale.** Toute la couche métier existante est appelée telle quelle :
   `idc_match_artisans()`, `idc_send_mail()`, `idc_email_templates_catalog()`,
   `idc_recalc_artisan_rating()`, `idc_stripe_*`, vérification SIRET, etc.
4. **Léger et rapide.** Pas de framework JS, pas de build : ES modules natifs, CSS custom
   propriétés, icônes Lucide inlinées en SVG. L'app ne charge ni le thème ni wp-admin.

## 2. Vue d'ensemble

```
┌────────────────────────────────────────────────────────────────┐
│  Navigateur — http://info-devis.local/infodevis-admin/         │
│  SPA vanilla JS (hash routing #/dashboard, #/artisans, …)      │
│  assets: admin.css + app.js (+ modules ES)                     │
└──────────────┬─────────────────────────────────────────────────┘
               │ fetch() + X-WP-Nonce (cookie auth WordPress)
┌──────────────▼─────────────────────────────────────────────────┐
│  API REST  /wp-json/idc/v1/admin/*                             │
│  permission_callback : current_user_can('idc_manage')          │
│  (namespace idc/v1 déjà utilisé par le webhook Stripe)         │
└──────────────┬─────────────────────────────────────────────────┘
┌──────────────▼─────────────────────────────────────────────────┐
│  WordPress (moteur)                                            │
│  CPT : artisan, demande_devis, avis, realisation, guide, rdv   │
│  Taxonomie : metier · Rôles : gestionnaire/artisan/client      │
│  Couche métier : info-devis-core (matching, emails, Stripe…)   │
└────────────────────────────────────────────────────────────────┘
```

## 3. Le plugin `infodevis-admin`

```
wp-content/plugins/infodevis-admin/
├── infodevis-admin.php            Bootstrap : constantes, requires, activation
├── includes/
│   ├── app-shell.php              Point d'entrée /infodevis-admin/ (intercepté sur
│   │                              `init`, sans règle de réécriture) : contrôle d'accès
│   │                              idc_manage puis rendu du shell HTML + exit.
│   ├── rest-api.php               Enregistrement des routes idc/v1/admin/* + helpers
│   ├── rest-lists.php             Listes génériques (posts, pages, users) : recherche,
│   │                              filtres, tri, pagination, formatage des lignes
│   ├── rest-entities.php          Lecture/écriture d'un élément (post + metas _idc_ +
│   │                              taxonomies + SEO Yoast), création, duplication,
│   │                              corbeille, changement de statut (Kanban)
│   ├── rest-dashboard.php         Statistiques, graphiques, activité récente,
│   │                              recherche globale
│   ├── rest-settings.php          Réglages société, emails, métiers (CRUD + ordre),
│   │                              sections de la page d'accueil, résumé SEO
│   ├── home-options.php           Défauts + accès aux options `idv_home_*`
│   │                              (partagé avec le thème)
│   └── wp-admin-integration.php   Redirections login/wp-admin, masquage admin bar,
│                                  échappatoire ?classic=1
└── assets/
    ├── css/admin.css              Design system + composants + layouts (1 fichier,
    │                              découpé en couches @layer)
    └── js/
        ├── app.js                 Bootstrap, routeur hash, layout, navigation
        ├── api.js                 Client REST (fetch + nonce + erreurs + toasts)
        ├── icons.js               Sous-ensemble Lucide en SVG inline
        ├── ui.js                  Composants : Table, Drawer, Modal, Toast, Badge,
        │                          Tabs, EmptyState, Menu, ImagePicker, RichText
        └── views/
            ├── dashboard.js       Tableau de bord (stats, graphes, activité, accès rapides)
            ├── site.js            Gestion du site (cartes) + éditeur page d'accueil
            ├── content.js         Articles, guides, catégories, FAQ, pages (listes + édition)
            ├── artisans.js        Artisans, vérifications, réalisations, avis
            ├── crm.js             Clients, demandes (table + Kanban), rendez-vous (calendrier)
            ├── payments.js        Stripe, abonnements, factures
            ├── seo.js             Résumé SEO, redirections, sitemap
            └── settings.js        Paramètres, emails/SMTP, API, sauvegardes
```

### Point d'entrée sans réécriture

`app-shell.php` s'accroche sur `init` : si `REQUEST_URI` commence par `/infodevis-admin`,
il vérifie la connexion (`auth_redirect()`) puis `current_user_can('idc_manage')`, rend le
shell HTML (une seule page, `<div id="app">`) et `exit`. Ni le thème ni wp-admin ne sont
chargés — seulement le core WordPress nécessaire à l'authentification et à l'API.

### Authentification & sécurité

- Session : cookies WordPress standard (aucun système parallèle).
- API : nonce REST (`wp_create_nonce('wp_rest')`) injecté dans le shell, envoyé en
  `X-WP-Nonce` sur chaque requête.
- Toutes les routes `idc/v1/admin/*` exigent `idc_manage` (administrateur + gestionnaire).
- Écriture : sanitisation systématique (`sanitize_text_field`, `wp_kses_post`,
  listes blanches d'énumérations reprises d'`info-devis-core`).
- Aucun secret affiché (clés Stripe/SMTP restent dans `wp-config.php`, l'écran Paramètres
  n'affiche que leur état).

## 4. Données

Aucune table personnalisée. Tout s'appuie sur l'existant :

| Donnée | Stockage |
|---|---|
| Contenus (articles, guides, pages, artisans, demandes, avis, réalisations, RDV) | `wp_posts` + `wp_postmeta` (`_idc_*`) |
| Métiers | taxonomie `metier` + term meta `_idc_*` |
| Clients / artisans (comptes) | `wp_users` + user meta `_idc_*` |
| Réglages société / réseaux sociaux | options `idv_contact_*` (existantes) + `ida_settings` (nouvelle) |
| Page d'accueil administrable | options `idv_home_*` (nouvelles, défauts = contenu actuel) |
| Modèles d'emails | option `idc_email_templates` (existante) |
| SEO | metas Yoast `_yoast_wpseo_*` (lues/écrites) |

## 5. Compatibilité & réversibilité

- wp-admin reste accessible : `wp-login.php?classic=1` puis `/wp-admin/?classic=1`
  (l'administrateur y a toujours accès ; le paramètre pose un cookie de session).
- Gutenberg reste disponible en « éditeur avancé » via un lien d'échappement depuis
  les écrans d'édition InfoDevis.
- Désactiver le plugin `infodevis-admin` = retour immédiat à l'admin WordPress
  classique, sans perte de données.

## 6. Ce qui est réutilisé (inventaire)

- **Capacité** `idc_manage` (déjà portée par administrateur + gestionnaire).
- **Namespace REST** `idc/v1` (webhook Stripe déjà dessus).
- **Fonctions métier** : `idc_match_artisans`, `idc_get_demandes_for_artisan`,
  `idc_send_mail`, `idc_get_email_template`, `idc_email_templates_catalog`,
  `idc_recalc_artisan_rating`, `idc_stripe_ready`, `idc_stripe_request`,
  `idc_stripe_set_plan`, vérification SIRET (`save_post_artisan`).
- **Énumérations** : statuts demandes (`pending/sent/accepted/in_progress/completed/
  cancelled/refused`), avis (`pending/approved/refused/reported/hidden`), plans
  (`gratuit/starter/pro/illimite/silver/gold`), badges, statuts RDV
  (`propose/confirme/annule/termine`).
- **Helpers thème** : `idv_contact()`, `idv_category_icons()`, `idv_category_images()`.
- **Options existantes** : `idc_email_templates`, `idc_email_log`, `idv_contact_*`.
