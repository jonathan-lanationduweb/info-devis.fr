<?php
/**
 * Info Devis Core — matching artisans <-> demandes de devis.
 *
 * V1 : correspondance par catégorie de travaux + même département
 * (2 premiers chiffres du code postal). L'ancienne appli utilisait un
 * rayon en km avec géolocalisation — à porter en V2 si besoin.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fiches artisans correspondant à une demande de devis.
 *
 * @return int[] IDs de posts artisan.
 */
function idc_match_artisans(int $demande_id): array
{
    $terms = wp_get_object_terms($demande_id, 'metier', ['fields' => 'ids']);
    if (is_wp_error($terms) || !$terms) {
        return [];
    }
    $dept = substr((string) get_post_meta($demande_id, '_idc_code_postal', true), 0, 2);

    $artisans = get_posts([
        'post_type'      => 'artisan',
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'fields'         => 'ids',
        'tax_query'      => [['taxonomy' => 'metier', 'field' => 'term_id', 'terms' => $terms]],
    ]);

    if (!$dept) {
        return $artisans;
    }

    $matched = [];
    foreach ($artisans as $artisan_id) {
        $artisan_cp = (string) get_post_meta($artisan_id, '_idc_code_postal', true);
        // Artisan sans code postal : inclus par défaut (profil incomplet).
        if ($artisan_cp === '' || substr($artisan_cp, 0, 2) === $dept) {
            $matched[] = (int) $artisan_id;
        }
    }
    return $matched;
}

/**
 * Demandes de devis correspondant à un artisan (via son compte utilisateur).
 *
 * @return WP_Post[]
 */
function idc_get_demandes_for_artisan(int $user_id): array
{
    // Seules les fiches publiées (validées par l'admin) reçoivent des leads,
    // comme dans l'application d'origine (is_verified = 1).
    $fiche = get_posts([
        'post_type'   => 'artisan',
        'post_status' => 'publish',
        'numberposts' => 1,
        'fields'      => 'ids',
        'meta_key'    => '_idc_user_id',
        'meta_value'  => $user_id,
    ]);
    if (!$fiche) {
        return [];
    }
    $artisan_id = (int) $fiche[0];

    $terms = wp_get_object_terms($artisan_id, 'metier', ['fields' => 'ids']);
    if (is_wp_error($terms) || !$terms) {
        return [];
    }
    $dept = substr((string) get_post_meta($artisan_id, '_idc_code_postal', true), 0, 2);

    $demandes = get_posts([
        'post_type'      => 'demande_devis',
        'post_status'    => 'publish',
        'posts_per_page' => 50,
        'tax_query'      => [['taxonomy' => 'metier', 'field' => 'term_id', 'terms' => $terms]],
        'meta_query'     => [['key' => '_idc_status', 'value' => ['pending', 'sent'], 'compare' => 'IN']],
    ]);

    if (!$dept) {
        return $demandes;
    }
    return array_values(array_filter($demandes, static function (WP_Post $demande) use ($dept): bool {
        $cp = (string) get_post_meta($demande->ID, '_idc_code_postal', true);
        return $cp === '' || substr($cp, 0, 2) === $dept;
    }));
}

/**
 * Metabox admin : artisans correspondant à la demande affichée.
 */
add_action('add_meta_boxes', static function (): void {
    add_meta_box(
        'idc_matching',
        'Artisans correspondants',
        static function (WP_Post $post): void {
            $matched = idc_match_artisans($post->ID);
            if (!$matched) {
                echo '<p>Aucun artisan ne correspond (catégorie + département).</p>';
                return;
            }
            echo '<ul style="margin:0;">';
            foreach ($matched as $artisan_id) {
                $ville = get_post_meta($artisan_id, '_idc_ville', true);
                echo '<li>• <a href="' . esc_url(get_edit_post_link($artisan_id)) . '">'
                    . esc_html(get_the_title($artisan_id)) . '</a>'
                    . ($ville ? ' — ' . esc_html($ville) : '') . '</li>';
            }
            echo '</ul>';
        },
        'demande_devis',
        'side',
        'default'
    );
});
