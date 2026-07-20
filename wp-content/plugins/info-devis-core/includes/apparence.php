<?php
/**
 * Info Devis Core — personnalisation du dashboard artisan (page « Apparence »).
 *
 * Réservé au plan Gold : 7 thèmes prédéfinis + couleur d'accent personnalisée.
 * La préférence est stockée sur la fiche (_idc_dashboard_theme / _idc_dashboard_color)
 * et appliquée en surchargeant la variable CSS --tw-primary sur les pages du
 * dashboard artisan.
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDC_APPARENCE_THEMES = [
    'forest'  => '#2D5F4E',
    'ocean'   => '#1E40AF',
    'rose'    => '#EC4899',
    'sunset'  => '#F97316',
    'elegant' => '#1F2937',
    'purple'  => '#7C3AED',
    'solar'   => '#EAB308',
];

/** L'artisan a-t-il accès à la personnalisation (plan Gold) ? */
function idc_apparence_is_gold(?WP_Post $fiche): bool
{
    if (!$fiche) {
        return false;
    }
    $plan = get_post_meta($fiche->ID, '_idc_plan', true) ?: 'gratuit';
    return in_array($plan, ['gold', 'illimite', 'pro'], true);
}

/** #RRGGBB → "R G B" (triplet pour rgb(var(--tw-primary))). */
function idc_hex_to_rgb_triplet(string $hex): ?string
{
    $hex = ltrim(trim($hex), '#');
    if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        return null;
    }
    return hexdec(substr($hex, 0, 2)) . ' ' . hexdec(substr($hex, 2, 2)) . ' ' . hexdec(substr($hex, 4, 2));
}

/** Assombrit un hex d'un facteur (0-1) et renvoie un triplet "R G B". */
function idc_darken_triplet(string $hex, float $factor = 0.8): ?string
{
    $hex = ltrim(trim($hex), '#');
    if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
        return null;
    }
    $r = (int) round(hexdec(substr($hex, 0, 2)) * $factor);
    $g = (int) round(hexdec(substr($hex, 2, 2)) * $factor);
    $b = (int) round(hexdec(substr($hex, 4, 2)) * $factor);
    return "$r $g $b";
}

/** Couleur d'accent effective d'une fiche (custom > thème > défaut). */
function idc_apparence_color(?WP_Post $fiche): string
{
    if (!$fiche || !idc_apparence_is_gold($fiche)) {
        return '#207752';
    }
    $custom = (string) get_post_meta($fiche->ID, '_idc_dashboard_color', true);
    if ($custom && preg_match('/^#[0-9a-fA-F]{6}$/', $custom)) {
        return $custom;
    }
    $theme = (string) get_post_meta($fiche->ID, '_idc_dashboard_theme', true);
    return IDC_APPARENCE_THEMES[$theme] ?? '#207752';
}

/* ── Enregistrement des préférences (AJAX) ─────────────────────────────── */
add_action('wp_ajax_idc_artisan_apparence_save', static function (): void {
    if (!isset($_POST['idc_apparence_nonce']) || !wp_verify_nonce($_POST['idc_apparence_nonce'], 'idc_apparence')) {
        wp_send_json(['success' => false, 'error' => 'Session expirée, rechargez la page.'], 403);
    }
    $fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;
    if (!$fiche) {
        wp_send_json(['success' => false, 'error' => 'Fiche artisan introuvable.'], 403);
    }
    if (!idc_apparence_is_gold($fiche)) {
        wp_send_json(['success' => false, 'error' => 'La personnalisation est réservée au plan Gold.'], 403);
    }

    $theme = sanitize_key($_POST['theme'] ?? '');
    if ($theme && !isset(IDC_APPARENCE_THEMES[$theme])) {
        wp_send_json(['success' => false, 'error' => 'Thème inconnu.'], 400);
    }
    update_post_meta($fiche->ID, '_idc_dashboard_theme', $theme);

    $color = sanitize_text_field($_POST['custom_color'] ?? '');
    if ($color && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
        $color = '';
    }
    // Une couleur strictement égale au défaut = pas de surcharge.
    update_post_meta($fiche->ID, '_idc_dashboard_color', strtolower($color) === '#207752' ? '' : $color);

    wp_send_json(['success' => true, 'message' => 'Préférences enregistrées.']);
});

/* ── Application de l'accent sur le dashboard artisan ───────────────────── */
add_action('wp_head', static function (): void {
    if (!is_user_logged_in()) {
        return;
    }
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    if (strpos($uri, '/dashboard/artisan') === false) {
        return;
    }
    $fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;
    $color = idc_apparence_color($fiche);
    if ($color === '#207752') {
        return; // défaut : rien à surcharger.
    }
    $base = idc_hex_to_rgb_triplet($color);
    $dk   = idc_darken_triplet($color, 0.78);
    $lt   = idc_hex_to_rgb_triplet($color);
    if (!$base) {
        return;
    }
    echo "\n<style id=\"idc-apparence\">:root{--tw-primary:{$base};--tw-primary-lt:{$lt};--tw-primary-dk:{$dk};}</style>\n";
}, 99);
