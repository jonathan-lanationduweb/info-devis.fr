# Déploiement — InfoDevis

Ce dépôt contient le **code métier** (thème + plugins custom). Pour un site
fonctionnel en production, il faut l'assembler avec WordPress, une base de données
et une configuration. Voici la procédure.

## Prérequis serveur
- PHP 8.1+ (8.3 recommandé), MySQL 8 / MariaDB 10.5+
- HTTPS (certificat Let's Encrypt), accès SSH ou FTP

## 1. Installer WordPress
Télécharger WordPress (https://fr.wordpress.org/telecharger/) et le déployer à la
racine web. Créer une base MySQL + un **utilisateur dédié** (pas `root`).

## 2. Déposer le code de ce dépôt
Cloner ce dépôt et copier dans l'installation WordPress :
- `wp-content/themes/info-devis/`
- `wp-content/plugins/info-devis-core/`
- `wp-content/plugins/infodevis-admin/`

## 3. Configurer `wp-config.php`
Copier `deploy/wp-config-sample.php` en `wp-config.php` à la racine, puis renseigner :
- accès base de données (utilisateur dédié + mot de passe fort) ;
- `WP_HOME` / `WP_SITEURL` = `https://info-devis.fr` ;
- **clés de sécurité** régénérées (https://api.wordpress.org/secret-key/1.1/salt/) ;
- **clés Stripe LIVE** et **SMTP Brevo** — via variables d'environnement de préférence.

> ⚠️ Les secrets du site de développement **ne doivent pas** être réutilisés :
> ils ont potentiellement fuité et doivent être **régénérés / tournés**.

## 4. Base de données
Importer un export de la base (structure + contenu) sur le serveur. Lors du changement
de domaine, faire un **search-replace** propre (données sérialisées) :
`wp search-replace 'http://info-devis.local' 'https://info-devis.fr' --all-tables`

## 5. Extensions tierces (depuis l'admin WordPress)
Installer et activer : **Yoast SEO**, **WP Mail SMTP**, (Akismet optionnel).

## 6. Contenus & médias
Copier `wp-content/uploads/` depuis le site source (images des fiches, réalisations, blog).

## 7. Finitions
- Recompiler **Tailwind** en CSS statique local (remplacer le CDN — voir `docs/audit-securite-complet.md`).
- Rapatrier les images Unsplash en local.
- Durcir `.htaccess` (protection wp-config, `-Indexes`, no-PHP dans `uploads/`, en-têtes).
- Configurer le **webhook Stripe** de production : `https://info-devis.fr/wp-json/idc/v1/stripe-webhook`.
- Régler les permaliens (Réglages → Permaliens → Enregistrer).

## 8. Vérifications
Inscription, connexion, demande de devis, prise de RDV, messagerie, signature,
paiement d'abonnement Stripe (test puis live), emails, cloche de notifications.

Détails de sécurité et points à durcir : voir `docs/audit-securite-complet.md`.
