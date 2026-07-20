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

WordPress · PHP 8.3 · MySQL · thème Tailwind **compilé** (build statique, plus de CDN) ·
Stripe (abonnements artisans) · SMTP (Brevo en production) · Yoast SEO.

## Build du CSS (Tailwind)

Le thème n'utilise plus le CDN Tailwind (runtime) : le CSS est **compilé** et
versionné (`assets/css/tailwind.build.css`), donc le site fonctionne tel quel
après un simple `git pull` (aucun build requis sur le serveur).

Pour régénérer le CSS après avoir ajouté des classes Tailwind dans les templates :

```bash
cd wp-content/themes/info-devis
npm install        # une seule fois (installe Tailwind + plugins, hors dépôt)
npm run build:css  # régénère assets/css/tailwind.build.css (minifié)
# ou en continu pendant le dev :
npm run watch:css
```

Les classes construites dynamiquement en PHP (ex. `bg-<?= $col ?>-100`) sont
préservées via la `safelist` de `tailwind.config.js` — l'étendre si de nouvelles
couleurs de statut sont introduites.

---

## Version mobile & Progressive Web App (PWA)

Le site propose une **expérience mobile « type application »** et est **installable**
comme une PWA sur l'écran d'accueil.

### Fichiers concernés

```
manifest.webmanifest              → manifest PWA (racine du domaine)
service-worker.js                 → service worker (scope "/", versionné)
offline.html                      → page hors connexion (autonome, sans dépendance)
assets/icons/                     → icônes 192/512, maskable, apple-touch, favicon.ico
wp-content/themes/info-devis/
├── assets/css/mobile.css         → composants mobiles (menu, barre inférieure, 44px,
│                                    safe-areas, accordéons, galerie swipe, actionbar)
├── assets/js/mobile.js           → menu plein écran a11y, accordéons, swipe,
│                                    favoris (localStorage), toasts
├── assets/js/pwa.js              → enregistrement SW, invite d'installation, iOS, standalone
├── template-parts/mobile-menu.php   → menu plein écran (Échap, focus-trap, scroll-lock)
├── template-parts/bottom-nav.php    → barre inférieure (Accueil/Pros/Favoris/RDV/Menu)
└── page-favoris.php                 → page « Mes favoris » (rendu depuis localStorage)
```

> Les favoris sont stockés **côté client** (localStorage) : ils fonctionnent sans
> compte et hors connexion.

### Contexte sécurisé requis (important)

Un service worker ne s'enregistre **que dans un contexte sécurisé** : `https://…`
ou `http://localhost`. En développement via `http://info-devis.local` (HTTP + nom
d'hôte personnalisé), le SW est **désactivé silencieusement** (le site reste
100 % fonctionnel). **En production HTTPS, la PWA s'active automatiquement.**

### Contraintes WordPress (§22.7)

Le service worker **n'intercepte jamais** : `/wp-admin/`, `/wp-login.php`,
`/wp-json/`, `/wp-cron.php`, les aperçus, les espaces privés (`/dashboard/`),
les requêtes non-GET, et toute URL contenant un nonce — ainsi que d'éventuels
chemins WooCommerce (`/cart`, `/checkout`, `/mon-compte`) prévus pour le futur.
Le SW est servi depuis la **racine** (Apache sert les fichiers physiques avant le
rewrite WordPress) ; `.htaccess` ajoute le type MIME `application/manifest+json`
et l'en-tête `Service-Worker-Allowed: /`.

### Différence entre les niveaux (§23)

| Niveau | Ce que c'est | État |
|--------|--------------|------|
| **Site responsive** | Le site s'adapte à toutes les tailles d'écran. | ✅ En place |
| **PWA installable** | Ajout à l'écran d'accueil, icône, splash, mode standalone, cache hors ligne. | ✅ En place (HTTPS) |
| **Application hybride** | La PWA empaquetée dans une coque native (WebView) via **Capacitor**, publiable sur les stores. | 🔜 Compatible |
| **Application native** | App iOS/Android développée séparément (Swift/Kotlin ou React Native), connectée à WordPress en **headless** (API REST). | 🔜 Architecture compatible |

L'architecture reste compatible avec une future app **Capacitor** ou une **API
WordPress headless** ; aucune dépendance ne bloque cette évolution.

### Publier plus tard sur l'App Store / Google Play

1. Installer **Capacitor** dans un projet dédié : `npm i @capacitor/core @capacitor/cli`.
2. `npx cap init` puis pointer le `server.url` vers le site (ou empaqueter les assets).
3. Ajouter les plateformes : `npx cap add ios` / `npx cap add android`.
4. Générer les icônes/splash natifs, renseigner identifiants et permissions.
5. **Android** : ouvrir dans Android Studio, générer un **AAB signé**, publier sur la
   Google Play Console (fiche, captures, politique de confidentialité).
6. **iOS** : ouvrir dans Xcode, compte Apple Developer, archive → **App Store Connect**
   (fiche, captures, revue Apple).
7. Alternative Android sans coque : **TWA** (Trusted Web Activity) via Bubblewrap,
   qui embarque directement la PWA.

### Critères d'acceptation mobile (§24) — statut

Fonctionne dès 320 px · pas de débordement horizontal · navigation mobile complète
(menu plein écran + barre inférieure) · cibles ≥ 44 px · galeries au doigt ·
formulaires adaptés (types, `inputmode`, `autocomplete`, police ≥ 16 px) · safe-areas
respectées · animations allégées + `prefers-reduced-motion` · manifest valide ·
service worker fonctionnel (HTTPS) · page hors connexion · mode standalone géré.
