# Changelog

## [0.3.0] — 15 juillet 2026 — Refonte « migration complète »

### Extension info-devis-core
- Nouveaux CPT : `realisation` (portfolio), `guide` (avec conversion des guides migrés), `rdv` (metabox artisan/client/date/durée/statut)
- Rôle `gestionnaire` + capacité `idc_manage` (pages d'admin Info Devis)
- Avis : statuts pending/approved/refused/reported/hidden, dépôt par client connecté (nonce, anti-doublon), recalcul automatique note moyenne + répartition par étoiles, notification artisan à la publication
- Inscription artisan front : compte + fiche `pending`, consentement horodaté, notification admin
- Emails : 9 modèles administrables (Info Devis → Emails), variables dynamiques, activation/désactivation, journal des 50 derniers envois
- Stripe : Checkout abonnements Silver/Gold (API REST), webhook signé + idempotent (`/wp-json/idc/v1/stripe-webhook`), plan appliqué compte + fiche, écran de diagnostic
- SEO : JSON-LD ProfessionalService + AggregateRating conditionnel sur les fiches, noindex des pages privées
- Page « État du système » (diagnostic complet sans secrets)
- `uninstall.php` prudent (les contenus métier ne sont jamais supprimés)
- Formulaire de devis : consentement RGPD obligatoire horodaté

### Site
- Pages : Devenir artisan, Tarifs professionnels, Mentions légales, Politique de confidentialité (publiée + déclarée), Conditions générales
- Menu : ajout Devenir artisan, Guides & Prix → archive du CPT
- wp-config : constantes Stripe (test) + SMTP

### Documentation
- 17 documents dans `/docs` (audit, matrice, architecture, migration, tests, sécurité, RGPD, SEO, Stripe, emails, matching, maintenance, redirections…)
- Sauvegarde complète horodatée dans `C:\wamp64\backups\info-devis\`

## [0.2.0] — 15 juillet 2026 — Socle métier
- Extension : taxonomie `metier`, CPT `artisan`/`demande_devis`/`avis`, rôles artisan/client, formulaire de devis (nonce, honeypot, rate-limit), matching V1 (métier + département), vérification SIRET automatique (API recherche-entreprises), dashboards front client/artisan
- Thème enfant Astra « Info Devis » (charte verte/orange, templates artisan + métier)
- Migration des données : 18 catégories, 6 utilisateurs (mots de passe conservés), 3 artisans, 2 devis, 18 guides, 7 articles
- Pages : Accueil, Trouver un artisan, Demande de devis, Espace membre, Contact, Actualités + menu principal

## [0.1.0] — 15 juillet 2026 — Installation
- WordPress fr_FR sur vhost `info-devis.local`, base `wordpress` (MySQL 8.4.7)
- Yoast SEO + WP Mail SMTP, permaliens `/%postname%/`, SMTP local Mailtrap
