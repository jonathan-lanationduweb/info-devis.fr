<?php
/**
 * Listes génériques : GET /idc/v1/admin/list/{type}
 * Paramètres : s, status, page, per_page, orderby, order, metier,
 *              plan, badge, verification, urgency, avis_status, rdv_status
 */

if (!defined('ABSPATH')) {
    exit;
}

function ida_rest_list(WP_REST_Request $req)
{
    $type = (string) $req['type'];

    if ($type === 'client') {
        return ida_list_clients($req);
    }

    $post_type = ida_post_type($type);
    if (!$post_type) {
        return ida_error('Type inconnu : ' . $type, 404);
    }

    $page     = max(1, (int) $req->get_param('page'));
    $per_page = min(100, max(1, (int) ($req->get_param('per_page') ?: 20)));
    $status   = (string) ($req->get_param('status') ?: 'any');

    $args = [
        'post_type'      => $post_type,
        'post_status'    => $status === 'any' ? ['publish', 'draft', 'pending', 'future', 'private'] : $status,
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => (string) ($req->get_param('orderby') ?: 'date'),
        'order'          => strtoupper((string) $req->get_param('order')) === 'ASC' ? 'ASC' : 'DESC',
    ];

    if ($s = (string) $req->get_param('s')) {
        $args['s'] = $s;
    }
    if ($metier = (string) $req->get_param('metier')) {
        $args['tax_query'] = [['taxonomy' => 'metier', 'field' => 'slug', 'terms' => $metier]];
    }

    // Filtres meta par type.
    $meta_query = [];
    foreach ([
        'plan'         => '_idc_plan',
        'badge'        => '_idc_badge_level',
        'verification' => '_idc_verification_status',
        'urgency'      => '_idc_urgency',
        'avis_status'  => '_idc_status',
        'rdv_status'   => '_idc_statut',
        'demande_status' => '_idc_status',
    ] as $param => $meta_key) {
        if ($v = (string) $req->get_param($param)) {
            $meta_query[] = ['key' => $meta_key, 'value' => $v];
        }
    }
    if ($meta_query) {
        $args['meta_query'] = $meta_query;
    }

    // Recherche élargie aux metas (référence, ville, SIRET…) pour les types métier.
    if (!empty($args['s']) && in_array($type, ['artisan', 'demande', 'avis', 'rdv'], true)) {
        $ids_by_meta = get_posts([
            'post_type'      => $post_type,
            'post_status'    => 'any',
            'posts_per_page' => 200,
            'fields'         => 'ids',
            'meta_query'     => [
                'relation' => 'OR',
                ['key' => '_idc_ville', 'value' => $args['s'], 'compare' => 'LIKE'],
                ['key' => '_idc_siret', 'value' => $args['s'], 'compare' => 'LIKE'],
                ['key' => '_idc_reference', 'value' => $args['s'], 'compare' => 'LIKE'],
                ['key' => '_idc_contact_email', 'value' => $args['s'], 'compare' => 'LIKE'],
            ],
        ]);
        if ($ids_by_meta) {
            $ids_by_title = get_posts(array_merge($args, ['fields' => 'ids', 'posts_per_page' => 200, 'paged' => 1]));
            $args['post__in'] = array_unique(array_merge($ids_by_title, $ids_by_meta));
            unset($args['s']);
        }
    }

    $q = new WP_Query($args);
    $rows = array_map(static fn($p) => ida_format_row($type, $p), $q->posts);

    return [
        'rows'  => $rows,
        'total' => (int) $q->found_posts,
        'pages' => (int) $q->max_num_pages,
        'page'  => $page,
    ];
}

/** Ligne de liste selon le type. */
function ida_format_row(string $type, WP_Post $p): array
{
    $base = [
        'id'     => $p->ID,
        'title'  => $p->post_title !== '' ? ida_text($p->post_title) : '(sans titre)',
        'status' => $p->post_status,
        'status_label' => ida_post_status_label($p->post_status),
        'date'   => get_the_date('d/m/Y', $p),
        'ago'    => ida_ago($p->post_date),
        'edit'   => '#/' . ($type === 'article' ? 'articles' : $type . 's') . '/' . $p->ID,
    ];

    switch ($type) {
        case 'article':
        case 'guide':
            $terms = get_the_terms($p, $type === 'article' ? 'category' : 'metier');
            if ($type === 'article') {
                $metiers = get_the_terms($p, 'metier');
                if ($metiers && !is_wp_error($metiers)) {
                    $terms = $metiers;
                }
            }
            return $base + [
                'thumb'    => ida_thumb($p->ID),
                'category' => ($terms && !is_wp_error($terms)) ? ida_text($terms[0]->name) : '—',
                'reading'  => ida_reading_time($p->post_content),
                'seo_ok'   => ida_yoast($p->ID)['ok'],
                'view'     => get_permalink($p),
            ];

        case 'page':
            return $base + [
                'slug'   => $p->post_name,
                'seo_ok' => ida_yoast($p->ID)['ok'],
                'view'   => get_permalink($p),
                'wp_edit' => admin_url('post.php?post=' . $p->ID . '&action=edit'),
            ];

        case 'artisan':
            return $base + [
                'thumb'        => ida_thumb($p->ID),
                'company'      => ida_meta($p->ID, 'company_name'),
                'ville'        => ida_meta($p->ID, 'ville'),
                'experience'   => ida_meta($p->ID, 'annees_experience'),
                'plan'         => ida_meta($p->ID, 'plan') ?: 'gratuit',
                'badge'        => ida_meta($p->ID, 'badge_level') ?: 'referenced',
                'verification' => ida_meta($p->ID, 'verification_status') ?: 'pending',
                'siret_ok'     => ida_meta($p->ID, 'siret_verified') === '1',
                'rating'       => ida_meta($p->ID, 'rating_avg'),
                'rating_count' => ida_meta($p->ID, 'rating_count'),
                'view'         => get_permalink($p),
            ];

        case 'demande':
            $metier = get_the_terms($p, 'metier');
            return $base + [
                'reference' => ida_meta($p->ID, 'reference'),
                'ville'     => ida_meta($p->ID, 'ville'),
                'cp'        => ida_meta($p->ID, 'code_postal'),
                'urgency'   => ida_meta($p->ID, 'urgency') ?: 'normal',
                'dstatus'   => ida_meta($p->ID, 'status') ?: 'pending',
                'contact'   => ida_meta($p->ID, 'contact_name'),
                'email'     => ida_meta($p->ID, 'contact_email'),
                'metier'    => ($metier && !is_wp_error($metier)) ? ida_text($metier[0]->name) : '—',
                'budget'    => ida_meta($p->ID, 'budget_range'),
            ];

        case 'avis':
            $artisan_id = (int) ida_meta($p->ID, 'artisan_post_id');
            return $base + [
                'rating'   => (int) ida_meta($p->ID, 'rating'),
                'astatus'  => ida_meta($p->ID, 'status') ?: 'pending',
                'verified' => ida_meta($p->ID, 'verified') === '1',
                'artisan'  => $artisan_id ? get_the_title($artisan_id) : '—',
                'artisan_id' => $artisan_id,
                'excerpt'  => wp_trim_words(wp_strip_all_tags($p->post_content), 18),
            ];

        case 'realisation':
            $artisan_id = (int) ida_meta($p->ID, 'artisan_post_id');
            return $base + [
                'thumb'   => ida_thumb($p->ID),
                'ville'   => ida_meta($p->ID, 'ville'),
                'budget'  => ida_meta($p->ID, 'budget'),
                'artisan' => $artisan_id ? get_the_title($artisan_id) : '—',
                'view'    => get_permalink($p),
            ];

        case 'rdv':
            $artisan_id = (int) ida_meta($p->ID, 'artisan_post_id');
            $client_id  = (int) ida_meta($p->ID, 'client_user_id');
            $client     = $client_id ? get_userdata($client_id) : null;
            return $base + [
                'date_rdv' => ida_meta($p->ID, 'date_rdv'),
                'duree'    => ida_meta($p->ID, 'duree_min'),
                'adresse'  => ida_meta($p->ID, 'adresse'),
                'rstatus'  => ida_meta($p->ID, 'statut') ?: 'propose',
                'artisan'  => $artisan_id ? get_the_title($artisan_id) : '—',
                'client'   => $client ? $client->display_name : '—',
            ];

        case 'faq':
            return $base + [
                'excerpt' => wp_trim_words(wp_strip_all_tags($p->post_content), 20),
                'order'   => (int) $p->menu_order,
            ];
    }

    return $base;
}

/* ---------------------------------------------------------------------- */
/*  Clients (utilisateurs rôle client)                                     */
/* ---------------------------------------------------------------------- */

function ida_list_clients(WP_REST_Request $req): array
{
    $page     = max(1, (int) $req->get_param('page'));
    $per_page = min(100, max(1, (int) ($req->get_param('per_page') ?: 20)));

    $args = [
        'role'    => 'client',
        'number'  => $per_page,
        'paged'   => $page,
        'orderby' => 'registered',
        'order'   => 'DESC',
        'count_total' => true,
    ];
    if ($s = (string) $req->get_param('s')) {
        $args['search'] = '*' . $s . '*';
        $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
    }

    $query = new WP_User_Query($args);
    $rows  = [];
    foreach ($query->get_results() as $u) {
        $demandes = get_posts([
            'post_type' => 'demande_devis', 'post_status' => 'any', 'fields' => 'ids',
            'posts_per_page' => 50,
            'meta_query' => [
                'relation' => 'OR',
                ['key' => '_idc_client_user_id', 'value' => $u->ID],
                ['key' => '_idc_contact_email', 'value' => $u->user_email],
            ],
        ]);
        $rows[] = [
            'id'        => $u->ID,
            'title'     => $u->display_name,
            'email'     => $u->user_email,
            'phone'     => (string) get_user_meta($u->ID, '_idc_phone', true),
            'ville'     => (string) get_user_meta($u->ID, '_idc_ville', true),
            'registered' => date_i18n('d/m/Y', strtotime($u->user_registered)),
            'demandes'  => count($demandes),
            'avatar'    => get_avatar_url($u->ID, ['size' => 48]),
        ];
    }

    $total = (int) $query->get_total();
    return [
        'rows'  => $rows,
        'total' => $total,
        'pages' => (int) ceil($total / $per_page),
        'page'  => $page,
    ];
}

/** GET /clients/{id} : détail d'un client + demandes + RDV + avis. */
function ida_rest_client_get(WP_REST_Request $req)
{
    $u = get_userdata((int) $req['id']);
    if (!$u) {
        return ida_error('Client introuvable', 404);
    }
    $demandes = get_posts([
        'post_type' => 'demande_devis', 'post_status' => 'any', 'posts_per_page' => 50,
        'meta_query' => [
            'relation' => 'OR',
            ['key' => '_idc_client_user_id', 'value' => $u->ID],
            ['key' => '_idc_contact_email', 'value' => $u->user_email],
        ],
    ]);
    return [
        'id'        => $u->ID,
        'name'      => $u->display_name,
        'email'     => $u->user_email,
        'phone'     => (string) get_user_meta($u->ID, '_idc_phone', true),
        'ville'     => (string) get_user_meta($u->ID, '_idc_ville', true),
        'cp'        => (string) get_user_meta($u->ID, '_idc_code_postal', true),
        'registered' => date_i18n('d F Y', strtotime($u->user_registered)),
        'avatar'    => get_avatar_url($u->ID, ['size' => 96]),
        'demandes'  => array_map(static fn($p) => ida_format_row('demande', $p), $demandes),
    ];
}
