<?php
/**
 * Lecture / écriture d'une entité, création, duplication, actions.
 * Les clés meta autorisées reprennent exactement celles d'info-devis-core.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Clés meta _idc_ autorisées en écriture, par type d'écran. */
function ida_meta_keys(string $type): array
{
    return [
        'artisan'     => ['company_name', 'siret', 'phone', 'ville', 'code_postal', 'radius_km', 'annees_experience', 'plan', 'badge_level', 'verification_status', 'siret_verified', 'user_id'],
        'demande'     => ['reference', 'ville', 'code_postal', 'urgency', 'budget_min', 'budget_max', 'budget_range', 'status', 'client_user_id', 'contact_name', 'contact_email', 'contact_phone'],
        'avis'        => ['rating', 'status', 'artisan_post_id', 'client_user_id', 'verified'],
        'realisation' => ['artisan_post_id', 'ville', 'duree', 'budget'],
        'rdv'         => ['artisan_post_id', 'client_user_id', 'date_rdv', 'duree_min', 'adresse', 'statut'],
    ][$type] ?? [];
}

/** GET /item/{type}/{id} */
function ida_rest_item_get(WP_REST_Request $req)
{
    $type      = (string) $req['type'];
    $post_type = ida_post_type($type);
    $post      = get_post((int) $req['id']);
    if (!$post_type || !$post || $post->post_type !== $post_type) {
        return ida_error('Élément introuvable', 404);
    }

    $metas = [];
    foreach (ida_meta_keys($type) as $key) {
        $metas[$key] = ida_meta($post->ID, $key);
    }
    // Metas en lecture seule utiles à l'écran.
    foreach (['rating_avg', 'rating_count', 'siret_company', 'consent_at', 'budget_range'] as $ro) {
        $v = ida_meta($post->ID, $ro);
        if ($v !== '') {
            $metas[$ro] = $v;
        }
    }

    $terms = get_the_terms($post, 'metier');
    $cats  = $post->post_type === 'post' ? get_the_terms($post, 'category') : false;

    return [
        'id'        => $post->ID,
        'type'      => $type,
        'title'     => $post->post_title,
        'content'   => $post->post_content,
        'excerpt'   => $post->post_excerpt,
        'status'    => $post->post_status,
        'slug'      => $post->post_name,
        'date'      => get_the_date('d/m/Y H:i', $post),
        'thumbnail_id'  => (int) get_post_thumbnail_id($post),
        'thumbnail_url' => ida_thumb($post->ID, 'medium'),
        'metas'     => $metas,
        'metier'    => ($terms && !is_wp_error($terms)) ? wp_list_pluck($terms, 'term_id') : [],
        'categories' => ($cats && !is_wp_error($cats)) ? wp_list_pluck($cats, 'term_id') : [],
        'yoast'     => ida_yoast($post->ID),
        'view'      => get_permalink($post),
        'wp_edit'   => admin_url('post.php?post=' . $post->ID . '&action=edit'),
        'enums'     => ida_enums(),
        'row'       => ida_format_row($type, $post),
    ];
}

/** Corps commun de sauvegarde (create + update). */
function ida_apply_item_payload(string $type, int $post_id, array $body)
{
    $post_type = ida_post_type($type);
    $data = ['ID' => $post_id];

    if (array_key_exists('title', $body)) {
        $data['post_title'] = sanitize_text_field((string) $body['title']);
    }
    if (array_key_exists('content', $body)) {
        $data['post_content'] = wp_kses_post((string) $body['content']);
    }
    if (array_key_exists('excerpt', $body)) {
        $data['post_excerpt'] = sanitize_textarea_field((string) $body['excerpt']);
    }
    if (!empty($body['status']) && in_array($body['status'], ['publish', 'draft', 'pending', 'private'], true)) {
        $data['post_status'] = $body['status'];
    }
    if (isset($body['menu_order'])) {
        $data['menu_order'] = (int) $body['menu_order'];
    }

    $result = wp_update_post(wp_slash($data), true);
    if (is_wp_error($result)) {
        return $result;
    }

    if (array_key_exists('thumbnail_id', $body)) {
        $thumb = (int) $body['thumbnail_id'];
        $thumb > 0 ? set_post_thumbnail($post_id, $thumb) : delete_post_thumbnail($post_id);
    }

    // Metas _idc_ (liste blanche par type + validation des énumérations).
    $enums   = ida_enums();
    $checks  = [
        'plan' => 'plan', 'badge_level' => 'badge', 'verification_status' => 'verification',
        'urgency' => 'urgency', 'statut' => 'rdv_status',
    ];
    if (!empty($body['metas']) && is_array($body['metas'])) {
        foreach (ida_meta_keys($type) as $key) {
            if (!array_key_exists($key, $body['metas'])) {
                continue;
            }
            $value = sanitize_text_field((string) $body['metas'][$key]);
            if ($key === 'status') {
                $enum = $type === 'avis' ? 'avis_status' : 'demande_status';
                if (!isset($enums[$enum][$value])) {
                    continue;
                }
            } elseif (isset($checks[$key]) && $value !== '' && !isset($enums[$checks[$key]][$value])) {
                continue;
            }
            update_post_meta($post_id, '_idc_' . $key, $value);
        }
    }

    // Synchronisations héritées d'info-devis-core.
    if ($type === 'avis') {
        $status = ida_meta($post_id, 'status');
        update_post_meta($post_id, '_idc_approved', $status === 'approved' ? '1' : '0');
        $artisan_id = (int) ida_meta($post_id, 'artisan_post_id');
        if ($artisan_id && function_exists('idc_recalc_artisan_rating')) {
            idc_recalc_artisan_rating($artisan_id);
        }
    }

    // Taxonomies.
    if (array_key_exists('metier', $body) && is_array($body['metier'])) {
        wp_set_object_terms($post_id, array_map('intval', $body['metier']), 'metier');
    }
    if ($post_type === 'post' && array_key_exists('categories', $body) && is_array($body['categories'])) {
        wp_set_object_terms($post_id, array_map('intval', $body['categories']), 'category');
    }

    // SEO Yoast.
    if (!empty($body['yoast']) && is_array($body['yoast'])) {
        if (array_key_exists('title', $body['yoast'])) {
            update_post_meta($post_id, '_yoast_wpseo_title', sanitize_text_field((string) $body['yoast']['title']));
        }
        if (array_key_exists('description', $body['yoast'])) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_textarea_field((string) $body['yoast']['description']));
        }
    }

    return true;
}

/** POST /item/{type}/{id} */
function ida_rest_item_save(WP_REST_Request $req)
{
    $type      = (string) $req['type'];
    $post_type = ida_post_type($type);
    $post      = get_post((int) $req['id']);
    if (!$post_type || !$post || $post->post_type !== $post_type) {
        return ida_error('Élément introuvable', 404);
    }
    $result = ida_apply_item_payload($type, $post->ID, (array) $req->get_json_params());
    if (is_wp_error($result)) {
        return $result;
    }
    return ['ok' => true, 'id' => $post->ID, 'item' => ida_rest_item_get($req)];
}

/** POST /item/{type} — création. */
function ida_rest_item_create(WP_REST_Request $req)
{
    $type      = (string) $req['type'];
    $post_type = ida_post_type($type);
    if (!$post_type) {
        return ida_error('Type inconnu', 404);
    }
    $body = (array) $req->get_json_params();

    $post_id = wp_insert_post(wp_slash([
        'post_type'   => $post_type,
        'post_status' => 'draft',
        'post_title'  => sanitize_text_field((string) ($body['title'] ?? 'Nouveau')),
    ]), true);
    if (is_wp_error($post_id)) {
        return $post_id;
    }

    // Référence automatique pour les demandes créées à la main (format info-devis-core).
    if ($type === 'demande' && empty($body['metas']['reference'])) {
        $body['metas']['reference'] = 'DV' . date_i18n('ymd') . '-' . strtoupper(wp_generate_password(4, false, false));
        $body['metas']['status'] = $body['metas']['status'] ?? 'pending';
    }

    $result = ida_apply_item_payload($type, $post_id, $body);
    if (is_wp_error($result)) {
        return $result;
    }
    return ['ok' => true, 'id' => $post_id];
}

/** POST /item/{type}/{id}/action — trash, restore, duplicate, status, verify, suspend… */
function ida_rest_item_action(WP_REST_Request $req)
{
    $type      = (string) $req['type'];
    $post_type = ida_post_type($type);
    $post      = get_post((int) $req['id']);
    if (!$post_type || !$post || $post->post_type !== $post_type) {
        return ida_error('Élément introuvable', 404);
    }

    $body   = (array) $req->get_json_params();
    $action = (string) ($body['action'] ?? '');
    $value  = (string) ($body['value'] ?? '');
    $enums  = ida_enums();

    switch ($action) {
        case 'trash':
            wp_trash_post($post->ID);
            return ['ok' => true];

        case 'restore':
            wp_untrash_post($post->ID);
            return ['ok' => true];

        case 'publish':
            wp_update_post(['ID' => $post->ID, 'post_status' => 'publish']);
            return ['ok' => true];

        case 'draft':
        case 'suspend':
            wp_update_post(['ID' => $post->ID, 'post_status' => 'draft']);
            return ['ok' => true];

        case 'duplicate':
            $new_id = wp_insert_post(wp_slash([
                'post_type'    => $post->post_type,
                'post_status'  => 'draft',
                'post_title'   => $post->post_title . ' (copie)',
                'post_content' => $post->post_content,
                'post_excerpt' => $post->post_excerpt,
            ]), true);
            if (is_wp_error($new_id)) {
                return $new_id;
            }
            foreach (get_post_meta($post->ID) as $key => $values) {
                foreach ($values as $v) {
                    add_post_meta($new_id, $key, maybe_unserialize($v));
                }
            }
            foreach (get_object_taxonomies($post->post_type) as $tax) {
                $terms = wp_get_object_terms($post->ID, $tax, ['fields' => 'ids']);
                if ($terms && !is_wp_error($terms)) {
                    wp_set_object_terms($new_id, $terms, $tax);
                }
            }
            if ($thumb = get_post_thumbnail_id($post)) {
                set_post_thumbnail($new_id, $thumb);
            }
            return ['ok' => true, 'id' => $new_id];

        case 'demande_status': // Kanban
            if (!isset($enums['demande_status'][$value])) {
                return ida_error('Statut invalide');
            }
            update_post_meta($post->ID, '_idc_status', $value);
            return ['ok' => true, 'status' => $value];

        case 'avis_status':
            if (!isset($enums['avis_status'][$value])) {
                return ida_error('Statut invalide');
            }
            update_post_meta($post->ID, '_idc_status', $value);
            update_post_meta($post->ID, '_idc_approved', $value === 'approved' ? '1' : '0');
            $artisan_id = (int) ida_meta($post->ID, 'artisan_post_id');
            if ($artisan_id && function_exists('idc_recalc_artisan_rating')) {
                idc_recalc_artisan_rating($artisan_id);
            }
            return ['ok' => true, 'status' => $value];

        case 'verify': // Vérification artisan
            if (!isset($enums['verification'][$value])) {
                return ida_error('Statut invalide');
            }
            update_post_meta($post->ID, '_idc_verification_status', $value);
            if ($value === 'validated') {
                if (ida_meta($post->ID, 'badge_level') === 'referenced') {
                    update_post_meta($post->ID, '_idc_badge_level', 'verified');
                }
                if ($post->post_status === 'pending') {
                    wp_update_post(['ID' => $post->ID, 'post_status' => 'publish']);
                }
            }
            return ['ok' => true, 'status' => $value];

        case 'rdv_status':
            if (!isset($enums['rdv_status'][$value])) {
                return ida_error('Statut invalide');
            }
            update_post_meta($post->ID, '_idc_statut', $value);
            return ['ok' => true, 'status' => $value];
    }

    return ida_error('Action inconnue : ' . $action);
}
