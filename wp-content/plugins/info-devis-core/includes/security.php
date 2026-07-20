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

/* ── En-têtes de sécurité HTTP (front) ─────────────────────────────────── */
add_action('send_headers', static function (): void {
    if (is_admin()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(self), camera=(), microphone=(), interest-cohort=()');
    header('Cross-Origin-Opener-Policy: same-origin');
});

/* ── Masquer la version de WordPress (réduction de surface) ────────────── */
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');

/* ── Désactiver XML-RPC et les pingbacks (vecteurs d'attaque classiques) ── */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_headers', static function (array $headers): array {
    unset($headers['X-Pingback']);
    return $headers;
});
add_filter('xmlrpc_methods', static function (array $methods): array {
    unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);
    return $methods;
});

/* ── Bloquer l'énumération des utilisateurs ────────────────────────────── */
// ?author=N sur le front (redirige vers l'accueil au lieu de révéler le login).
add_action('template_redirect', static function (): void {
    if (!is_admin() && isset($_GET['author']) && !is_author()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
});
// Endpoints REST d'utilisateurs fermés aux visiteurs non connectés.
add_filter('rest_endpoints', static function (array $endpoints): array {
    if (!is_user_logged_in()) {
        unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});
