<?php
/**
 * Info Devis Core — vérification SIRET via l'API publique
 * « Recherche d'entreprises » (recherche-entreprises.api.gouv.fr, sans clé).
 *
 * À l'enregistrement d'une fiche artisan, si le SIRET a changé, on interroge
 * l'API : SIRET trouvé et actif -> _idc_siret_verified = 1 + raison sociale
 * officielle stockée dans _idc_siret_company.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('save_post_artisan', static function (int $post_id): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }

    $siret = preg_replace('/\D/', '', (string) get_post_meta($post_id, '_idc_siret', true));
    if (strlen($siret) !== 14) {
        return;
    }
    // Déjà vérifié pour ce même SIRET : on ne rappelle pas l'API.
    if (get_post_meta($post_id, '_idc_siret_checked', true) === $siret) {
        return;
    }

    $response = wp_remote_get(
        'https://recherche-entreprises.api.gouv.fr/search?q=' . rawurlencode($siret) . '&page=1&per_page=1',
        ['timeout' => 8]
    );
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return; // API indisponible : on ne change rien, on retentera au prochain enregistrement.
    }

    $data     = json_decode(wp_remote_retrieve_body($response), true);
    $verified = false;
    $company  = '';

    foreach ((array) ($data['results'] ?? []) as $result) {
        $siren = $result['siren'] ?? '';
        if ($siren && str_starts_with($siret, $siren)) {
            $verified = true;
            $company  = (string) ($result['nom_complet'] ?? $result['nom_raison_sociale'] ?? '');
            break;
        }
    }

    update_post_meta($post_id, '_idc_siret_checked', $siret);
    update_post_meta($post_id, '_idc_siret_verified', $verified ? '1' : '0');
    if ($company) {
        update_post_meta($post_id, '_idc_siret_company', $company);
    }
}, 20);
