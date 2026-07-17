# Audit initial — Projet InfoDevis

**Date :** 15 juillet 2026
**Auteur :** Claude (assistant de migration)
**Environnement :** WAMP local — Windows 11, Apache 2.4.65, PHP 8.3.28 (FCGI), MySQL 8.4.7 (port 3306), MariaDB 11.4.9 (non utilisée par le projet)

---

## 1. Topologie du projet

| Élément | Emplacement | Rôle |
|---|---|---|
| **Site WordPress** | `C:\wamp64\www\info-devis.local` | Nouveau site cible, vhost `http://info-devis.local` |
| **Application historique** | `C:\wamp64\www\info-devis` | Appli PHP MVC custom, `http://localhost/info-devis` |
| **Base WordPress** | MySQL `wordpress` (préfixe `wp_`) | Données du nouveau site |
| **Base historique** | MySQL `infodevis` (45 tables) | Données de l'ancienne appli — **non modifiée** |
| **Sauvegardes** | `C:\wamp64\backups\info-devis\<horodatage>` | Dumps SQL + zips des deux arborescences |

L'installation WordPress est saine : cœur intact (6.x fr_FR), pas de modification de `wp-admin`/`wp-includes`.

## 2. WordPress — état au moment de l'audit

- **Thème actif :** `info-devis` (thème enfant d'Astra), `wp-content/themes/info-devis/`
- **Extensions actives :** `info-devis-core` (extension métier maison), Yoast SEO, WP Mail SMTP
- **Extensions installées inactives :** Akismet, Hello Dolly
- **Permaliens :** `/%postname%/`
- **Page d'accueil statique** + page des articles « Actualités »
- **SMTP :** constantes `WPMS_*` dans `wp-config.php` (Mailtrap sandbox en local)

## 3. Application historique — inventaire fonctionnel

Architecture MVC maison : `index.php` (routeur) + `.htaccess` (réécriture), `controllers/`, `models/`, `views/`, `services/` (Mail, Matching, SIRET, SEO), `api/`, `chatbot/`, `config/` (app.php, database.php, security.php, database.sql), `migrations/`, `uploads/`, `queue_worker.php` (file d'emails), `cron_seo.php`, Composer (`vendor/`).

Fonctionnalités observées (README + code + TODO) : annuaire artisans, 18 catégories métiers, devis multi-catégories, matching par zone + score, dashboards client/artisan/admin, avis vérifiés, RDV et disponibilités, documents de vérification (Kbis, RC Pro, identité, décennale…), abonnements (gratuit→gold), paiements Stripe, signature électronique, chatbot, SIRET (API officielle), file d'emails asynchrone, relances, SEO auto + sitemap, RGPD/consentements, scoring anti-spam.

**Bugs connus (TODO de l'appli) :** emails non reçus (SMTP), ajout de catégories admin défaillant, responsive des dashboards, hamburger page contact, chatbot multi-métiers, double header page paiements.

## 4. Base historique — tables et volumes

45 tables. Données réelles à migrer : `categories` (18), `guides` (18), `blog_posts` (7). Données de test : `users` (6), `artisans` (3), `devis` (2), `rdv` (2), `abonnements` (2), `avis` (0). Tables techniques/vides : logs, queue, notifications, seo_pages, zones, signatures, etc.

Encodage : utf8mb4. Mots de passe : **bcrypt `$2y$`** (compatibles WordPress ≥ 6.8 — vérifié, importés tels quels, connexion préservée).

## 5. Secrets détectés (valeurs non reproduites ici — confidentielles)

| Emplacement | Contenu | Action |
|---|---|---|
| `info-devis/.env` | `STRIPE_PUBLIC`, `STRIPE_SECRET` (mode **test**), `STRIPE_ABONNEMENT_SILVER/GOLD` (price IDs), `STRIPE_WEBHOOK_SECRET`, `OAUTH_ID_CLIENT`, `OAUTH_ID_SECRET` (Google) | Reportés dans `wp-config.php` (hors webroot public en prod). **Rotation obligatoire avant production.** |
| `info-devis/config/app.php` | Identifiants SMTP Mailtrap sandbox (dev) | Repris en constantes `WPMS_*` dans `wp-config.php` |
| `info-devis/README.md` | Identifiants admin de démo en clair | ⚠️ Compte migré : mot de passe à changer, README à purger avant tout dépôt public |

## 6. Risques identifiés

1. **Compte admin historique** migré avec son mot de passe faible documenté → à réinitialiser.
2. **Clés Stripe test** dans `.env` versionnable → rotation avant prod, ne jamais commiter.
3. L'ancienne appli reste servie sur `localhost/info-devis` → à désactiver à la mise en prod pour éviter le contenu dupliqué et l'exposition de `.env`/logs.
4. Données majoritairement de test → la migration est rejouable (scripts idempotents) le jour où des données réelles existeront.
5. URLs de prod historiques inconnues (le site n'est pas encore en ligne) → le plan de redirection est préparé sur la base des routes locales.

## 7. Décisions d'architecture (résumé — détail dans architecture-wordpress.md)

- Séparation stricte : **thème** = affichage (enfant Astra), **extension `info-devis-core`** = métier.
- Préfixe technique : `idc_` / `_idc_` (métadonnées) — choisi avant la consigne `idv_`, conservé pour cohérence avec les données déjà en base ; documenté.
- Rôles : `artisan`, `client` (créés en amont, utilisateurs déjà affectés), + `gestionnaire` ajouté. Slugs courts conservés plutôt que `*_info_devis` : mêmes garanties d'unicité via capacités préfixées, et migration des 6 comptes déjà effectuée.
- Guides : d'abord migrés en articles, **convertis en CPT `guide`** conformément au cahier des charges.
