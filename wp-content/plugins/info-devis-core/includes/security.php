<?php
/**
 * Info Devis Core — durcissement de session.
 *
 * Durée de session limitée à 1 heure : l'utilisateur connecté est
 * automatiquement déconnecté au bout d'une heure (y compris « se souvenir de
 * moi »), ce qui réduit la fenêtre d'exploitation d'un cookie volé.
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDC_SESSION_TTL = HOUR_IN_SECONDS; // 3600 s = 1 h

/** Durée de validité du cookie d'authentification. */
add_filter('auth_cookie_expiration', static function (int $length, int $user_id, bool $remember): int {
    return IDC_SESSION_TTL;
}, 10, 3);
