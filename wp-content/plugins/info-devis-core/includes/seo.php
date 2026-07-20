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

    // Description (bio de la fiche, nettoyée et tronquée).
    $bio = trim(wp_strip_all_tags((string) get_post($post_id)->post_content));
    if ($bio !== '') {
        $schema['description'] = mb_substr($bio, 0, 300);
    }

    // Image (cover puis vignette) — améliore l'affichage en résultats enrichis.
    $image = (string) get_post_meta($post_id, '_idc_cover_url', true);
    if ($image === '' && has_post_thumbnail($post_id)) {
        $image = (string) get_the_post_thumbnail_url($post_id, 'large');
    }
    if ($image !== '') {
        $schema['image'] = $image;
    }

    if ($ville || $cp) {
        $schema['address'] = array_filter([
            '@type'           => 'PostalAddress',
            'addressLocality' => $ville ?: null,
            'postalCode'      => $cp ?: null,
            'addressCountry'  => 'FR',
        ]);
    }
    // Zone desservie (utile pour le SEO local).
    if ($ville) {
        $schema['areaServed'] = ['@type' => 'City', 'name' => $ville];
    }
    if ($phone) {
        $schema['telephone'] = $phone;
    }
    if ($metiers) {
        $schema['knowsAbout'] = array_map(static fn($t) => $t->name, $metiers);
        // Catalogue de services (les métiers de l'artisan).
        $schema['hasOfferCatalog'] = [
            '@type' => 'OfferCatalog',
            'name'  => 'Prestations',
            'itemListElement' => array_map(static fn($t) => [
                '@type' => 'OfferCatalog',
                'name'  => $t->name,
            ], $metiers),
        ];
    }
    // Réseaux sociaux (sameAs).
    $social = array_values(array_filter([
        (string) get_post_meta($post_id, '_idc_linkedin_url', true),
        (string) get_post_meta($post_id, '_idc_instagram_url', true),
    ]));
    if ($social) {
        $schema['sameAs'] = $social;
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
