<?php
/**
 * Modèle de configuration InfoDevis — À COPIER en wp-config.php sur le serveur.
 * Renseigner les vraies valeurs (idéalement via variables d'environnement).
 * NE JAMAIS committer le wp-config.php rempli.
 */

// ── Base de données ──────────────────────────────────────────────
define('DB_NAME',     getenv('DB_NAME')     ?: 'wordpress');
define('DB_USER',     getenv('DB_USER')     ?: 'utilisateur_dedie');   // pas 'root' en prod
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'mot_de_passe_fort');
define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATE',  '');

$table_prefix = 'wp_';

// ── URLs (adapter au domaine de production) ──────────────────────
define('WP_HOME',    getenv('WP_HOME')    ?: 'https://info-devis.fr');
define('WP_SITEURL', getenv('WP_SITEURL') ?: 'https://info-devis.fr');

// ── Clés de sécurité (RÉGÉNÉRER : https://api.wordpress.org/secret-key/1.1/salt/) ──
define('AUTH_KEY',         getenv('AUTH_KEY')         ?: 'à-générer');
define('SECURE_AUTH_KEY',  getenv('SECURE_AUTH_KEY')  ?: 'à-générer');
define('LOGGED_IN_KEY',    getenv('LOGGED_IN_KEY')    ?: 'à-générer');
define('NONCE_KEY',        getenv('NONCE_KEY')        ?: 'à-générer');
define('AUTH_SALT',        getenv('AUTH_SALT')        ?: 'à-générer');
define('SECURE_AUTH_SALT', getenv('SECURE_AUTH_SALT') ?: 'à-générer');
define('LOGGED_IN_SALT',   getenv('LOGGED_IN_SALT')   ?: 'à-générer');
define('NONCE_SALT',       getenv('NONCE_SALT')       ?: 'à-générer');

// ── Durcissement ─────────────────────────────────────────────────
define('DISALLOW_FILE_EDIT', true);   // désactive l'éditeur de code de l'admin
define('FORCE_SSL_ADMIN',    true);   // admin en HTTPS
define('WP_DEBUG',           false);
define('FS_METHOD',          'direct');

// ── Emails (WP Mail SMTP — Brevo en production) ──────────────────
define('WPMS_ON',      true);
define('WPMS_MAILER',  'smtp');
define('WPMS_SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp-relay.brevo.com');
define('WPMS_SMTP_PORT', 587);
define('WPMS_SSL',       'tls');
define('WPMS_SMTP_AUTH', true);
define('WPMS_SMTP_USER', getenv('SMTP_USER') ?: '');
define('WPMS_SMTP_PASS', getenv('SMTP_PASS') ?: '');   // clé SMTP — via variable d'env
define('WPMS_MAIL_FROM', 'contact@info-devis.fr');
define('WPMS_MAIL_FROM_NAME', 'InfoDevis');

// ── Stripe (clés LIVE en production — via variables d'env, jamais en clair) ──
define('IDC_STRIPE_PUBLIC',         getenv('STRIPE_PUBLIC')         ?: '');
define('IDC_STRIPE_SECRET',         getenv('STRIPE_SECRET')         ?: '');
define('IDC_STRIPE_PRICE_SILVER',   getenv('STRIPE_PRICE_SILVER')   ?: '');
define('IDC_STRIPE_PRICE_GOLD',     getenv('STRIPE_PRICE_GOLD')     ?: '');
define('IDC_STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET') ?: '');

// ── Fin ──────────────────────────────────────────────────────────
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
require_once ABSPATH . 'wp-settings.php';
