<?php
/**
 * Info Devis Core — SEO : données structurées + noindex des espaces privés.
 * Yoast SEO gère titres, meta descriptions, canonicals, sitemap, Open Graph.
 * Ici : uniquement ce que Yoast ne couvre pas pour notre métier.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Pages privées / transactionnelles à ne pas indexer. */
add_filter('wp_robots', static function (array $robots): array {
    $path    = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $private = is_page(['espace-membre', 'inscription', 'connexion', 'prendre-rdv', 'mes-rdv', 'devis-confirmation'])
        || str_starts_with($path, '/dashboard/')
        || is_search();
    if ($private) {
        $robots['noindex'] = true;
        $robots['follow']  = true;
    }
    return $robots;
});

/** Données structurées ProfessionalService sur les fiches artisans. */
add_action('wp_head', static function (): void {
    if (!is_singular('artisan')) {
        return;
    }
    $post_id = get_queried_object_id();
    $ville   = get_post_meta($post_id, '_idc_ville', true);
    $cp      = get_post_meta($post_id, '_idc_code_postal', true);
    $phone   = get_post_meta($post_id, '_idc_phone', true);
    $rating  = (float) get_post_meta($post_id, '_idc_rating_avg', true);
    $count   = (int) get_post_meta($post_id, '_idc_rating_count', true);
    $metiers = get_the_terms($post_id, 'metier') ?: [];

    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'ProfessionalService',
        'name'     => get_the_title($post_id),
        'url'      => get_permalink($post_id),
    ];
    if ($ville || $cp) {
        $schema['address'] = array_filter([
            '@type'           => 'PostalAddress',
            'addressLocality' => $ville ?: null,
            'postalCode'      => $cp ?: null,
            'addressCountry'  => 'FR',
        ]);
    }
    if ($phone) {
        $schema['telephone'] = $phone;
    }
    if ($metiers) {
        $schema['knowsAbout'] = array_map(static fn($t) => $t->name, $metiers);
    }
    // AggregateRating uniquement si des avis approuvés existent réellement.
    if ($count > 0 && $rating > 0) {
        $schema['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format($rating, 1, '.', ''),
            'reviewCount' => $count,
            'bestRating'  => 5,
            'worstRating' => 1,
        ];
    }
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
});
