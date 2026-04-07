<?php
/**
 * Configuration principale de l'application
 * InfoDevis.fr
 */

// ── Environnement ─────────────────────────────────────────────
define('APP_ENV',     'development'); // 'production' en prod
define('APP_NAME',    'InfoDevis');
define('APP_URL',     'http://localhost/info-devis');
define('APP_VERSION', '1.0.0');

// ── Email ─────────────────────────────────────────────────────
define('MAIL_FROM',    'contact@info-devis.fr');
define('MAIL_NAME',    'InfoDevis');
define('MAIL_HOST',    'smtp.example.com');
define('MAIL_PORT',    587);
define('MAIL_USER',    '');
define('MAIL_PASS',    '');
define('MAIL_SECURE',  'tls');

// ── Stripe ────────────────────────────────────────────────────
define('STRIPE_PUBLIC_KEY',  'pk_test_VOTRE_CLE_PUBLIQUE');
define('STRIPE_SECRET_KEY',  'sk_test_VOTRE_CLE_SECRETE');
define('STRIPE_WEBHOOK_SEC', 'whsec_VOTRE_SECRET');

// ── Plans abonnement ──────────────────────────────────────────
define('PLAN_PRICES', [
    'gratuit'  => 0,
    'starter'  => 49,
    'pro'      => 99,
    'illimite' => 199,
]);
define('LEAD_PRICE', 9.90);

// ── Limites leads par plan ────────────────────────────────────
define('PLAN_LEAD_LIMITS', [
    'gratuit'  => 3,
    'starter'  => 20,
    'pro'      => 50,
    'illimite' => PHP_INT_MAX,
]);

// ── Matching ──────────────────────────────────────────────────
define('MATCH_MAX_PER_CATEGORY', 5);
define('MATCH_RADIUS_DEFAULT_KM', 50);

// ── Upload ────────────────────────────────────────────────────
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10 Mo
define('UPLOAD_ALLOWED_MIME', [
    'image/jpeg', 'image/png', 'image/webp',
    'application/pdf',
]);

// ── Anti-doublon leads ────────────────────────────────────────
define('ANTIDUPLICATE_HOURS', 24);

// ── Chemins ───────────────────────────────────────────────────
define('BASE_PATH',  __DIR__ . '/..');
define('VIEWS_PATH', __DIR__ . '/../views');
define('LOGS_PATH',  __DIR__ . '/../logs');

// ── Contact ───────────────────────────────────────────────────
define('CONTACT_EMAIL',   'contact@info-devis.fr');
define('CONTACT_PHONE',   '06 61 48 62 67');
define('CONTACT_ADDRESS', '45 Rue des Boulets, 75011 Paris, France');
define('CONTACT_HOURS',   'Lundi - Vendredi : 9h - 18h');

// ── KPI ───────────────────────────────────────────────────────
define('KPI_ARTISANS', '12 000+');

// ── Google Maps ───────────────────────────────────────────────
define('GOOGLE_MAPS_KEY', 'VOTRE_CLE_GOOGLE_MAPS');
define('GOOGLE_MAPS_LAT', 48.8566);
define('GOOGLE_MAPS_LNG', 2.3522);

// ── Débogage ──────────────────────────────────────────────────
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ── Timezone ──────────────────────────────────────────────────
date_default_timezone_set('Europe/Paris');
