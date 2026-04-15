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
define('MAIL_FORCE_SMTP', true);

// ── Email (PHPMailer + SMTP) ──────────────────────────────────
//
// CONFIGURATION GMAIL SMTP
// ─────────────────────────
// Pour obtenir un mot de passe d'application Gmail :
//   1. Connectez-vous à votre compte Google
//   2. Allez sur : https://myaccount.google.com/security
//   3. Activez la "Validation en deux étapes" (obligatoire)
//   4. Cherchez "Mots de passe des applications" (en bas de la page Sécurité)
//      ou allez directement sur : https://myaccount.google.com/apppasswords
//   5. Sélectionnez "Autre (nom personnalisé)" → tapez "InfoDevis"
//   6. Cliquez "Générer" → copiez le mot de passe à 16 caractères (ex: abcd efgh ijkl mnop)
//   7. Collez-le dans MAIL_PASS ci-dessous (sans les espaces)
//   8. Dans MAIL_USER, mettez votre adresse Gmail complète (ex: votre.adresse@gmail.com)
//   9. Dans MAIL_FROM, mettez la même adresse Gmail (Gmail exige From = compte authentifié)
//
// NOTE : Le compte Gmail doit être le même que celui utilisé pour générer le mot de passe.
//
define('MAIL_FROM',   'noreply@info-devis.fr'); // Mailtrap accepte n'importe quelle adresse ; Gmail exige que ce soit = MAIL_USER
define('MAIL_NAME',   'InfoDevis');
define('ADMIN_EMAIL', 'jonathan@lanationduweb.fr');

// define('MAIL_HOST',   'smtp.gmail.com'); // Serveur Gmail
// define('MAIL_PORT',   587);              // TLS (recommandé)
// define('MAIL_USER',   '');               // Votre adresse Gmail complète (ex: votre.adresse@gmail.com)
// define('MAIL_PASS',   '');               // Mot de passe d'application Gmail (16 car. sans espaces)
// define('MAIL_SECURE', 'tls');            // Ne pas modifier

// ── Mailtrap sandbox (tests sans livraison réelle) ────────────────────────
// Pour passer en Gmail : décommenter le bloc ci-dessus et commenter celui-ci
//
define('MAIL_HOST',   'sandbox.smtp.mailtrap.io');
define('MAIL_PORT',   2525);
define('MAIL_USER',   '48ca78bab706c9');
define('MAIL_PASS',   'a15ced477683da');
define('MAIL_SECURE', 'tls');

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
    'image/jpeg',
    'image/png',
    'image/webp',
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
