<?php
/**
 * wp-config.php de PRODUCTION — info-devis.fr
 * ------------------------------------------------------------------
 * Les SECRETS (base de données, sels, Stripe, SMTP) ne sont PLUS dans ce
 * fichier : ils sont lus depuis un fichier « .env » placé à côté, que seul
 * le propriétaire connaît et qui n'est jamais committé (voir .env.example).
 *
 * Déploiement :
 *   1. Copier .env.example en .env, remplir les valeurs, l'envoyer dans /www.
 *   2. Renommer ce fichier en wp-config.php et l'envoyer dans /www.
 *   3. .env est protégé de l'accès HTTP par le .htaccess.
 */

// ── Chargement du .env (secrets hors dépôt) ──────────────────────────────
(static function (): void {
    $envFile = __DIR__ . '/.env';
    if (!is_readable($envFile)) {
        return; // pas de .env : les define() ci-dessous seront vides → erreur explicite
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Retirer d'éventuels guillemets entourants.
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[strlen($value) - 1] === $value[0]) {
            $value = substr($value, 1, -1);
        }
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
})();

/** Lit une variable d'environnement (.env) avec valeur par défaut. */
function idv_env(string $key, string $default = ''): string
{
    $v = getenv($key);
    return $v === false ? $default : $v;
}

// ── Base de données (valeurs dans .env) ──────────────────────────────────
define('DB_NAME',     idv_env('DB_NAME'));
define('DB_USER',     idv_env('DB_USER'));
define('DB_PASSWORD', idv_env('DB_PASSWORD'));
define('DB_HOST',     idv_env('DB_HOST', 'localhost'));
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATE',  '');

$table_prefix = 'wp_';

// ── URLs de production ───────────────────────────────────────────────────
define('WP_HOME',    'https://info-devis.fr');
define('WP_SITEURL', 'https://info-devis.fr');

// ── Clés & sels de sécurité (dans .env) ──────────────────────────────────
define('AUTH_KEY',         idv_env('AUTH_KEY'));
define('SECURE_AUTH_KEY',  idv_env('SECURE_AUTH_KEY'));
define('LOGGED_IN_KEY',    idv_env('LOGGED_IN_KEY'));
define('NONCE_KEY',        idv_env('NONCE_KEY'));
define('AUTH_SALT',        idv_env('AUTH_SALT'));
define('SECURE_AUTH_SALT', idv_env('SECURE_AUTH_SALT'));
define('LOGGED_IN_SALT',   idv_env('LOGGED_IN_SALT'));
define('NONCE_SALT',       idv_env('NONCE_SALT'));

// ── Derrière le reverse-proxy HTTPS d'online.net ─────────────────────────
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// ── Durcissement ─────────────────────────────────────────────────────────
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN',    true);
define('WP_DEBUG',           false);
define('WP_DEBUG_DISPLAY',   false);
define('FS_METHOD',          'direct');
define('WP_AUTO_UPDATE_CORE', 'minor');

// ── Emails (WP Mail SMTP — clés dans .env) ───────────────────────────────
if (idv_env('SMTP_USER') !== '') {
    define('WPMS_ON',      true);
    define('WPMS_MAILER',  'smtp');
    define('WPMS_SMTP_HOST', idv_env('SMTP_HOST', 'smtp-relay.brevo.com'));
    define('WPMS_SMTP_PORT', (int) idv_env('SMTP_PORT', '587'));
    define('WPMS_SSL',       'tls');
    define('WPMS_SMTP_AUTH', true);
    define('WPMS_SMTP_USER', idv_env('SMTP_USER'));
    define('WPMS_SMTP_PASS', idv_env('SMTP_PASS'));
    define('WPMS_MAIL_FROM', 'contact@info-devis.fr');
    define('WPMS_MAIL_FROM_NAME', 'InfoDevis');
}

// ── Stripe (clés LIVE dans .env) ─────────────────────────────────────────
define('IDC_STRIPE_PUBLIC',         idv_env('STRIPE_PUBLIC'));
define('IDC_STRIPE_SECRET',         idv_env('STRIPE_SECRET'));
define('IDC_STRIPE_PRICE_SILVER',   idv_env('STRIPE_PRICE_SILVER'));
define('IDC_STRIPE_PRICE_GOLD',     idv_env('STRIPE_PRICE_GOLD'));
define('IDC_STRIPE_WEBHOOK_SECRET', idv_env('STRIPE_WEBHOOK_SECRET'));

// ── Fin ──────────────────────────────────────────────────────────────────
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
require_once ABSPATH . 'wp-settings.php';
