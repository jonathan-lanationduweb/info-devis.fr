<?php
/**
 * Bootstrap de l'app, tableau de bord et recherche globale.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** GET /bootstrap — infos transverses (badges sidebar, référentiels). */
function ida_rest_bootstrap(): array
{
    return [
        'badges' => ida_pending_badges(),
        'enums'  => ida_enums(),
        'metiers' => array_map(static fn($t) => [
            'id' => $t->term_id, 'name' => ida_text($t->name), 'slug' => $t->slug,
        ], get_terms(['taxonomy' => 'metier', 'hide_empty' => false]) ?: []),
    ];
}

/** Compteurs « à traiter » (badges de la sidebar). */
function ida_pending_badges(): array
{
    global $wpdb;
    $pending_meta = static function (string $post_type) use ($wpdb): int {
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
             JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_idc_status'
             WHERE p.post_type = %s AND p.post_status = 'publish' AND m.meta_value = 'pending'",
            $post_type
        ));
    };
    return [
        'demandes' => $pending_meta('demande_devis'),
        'avis'     => $pending_meta('avis'),
        'artisans' => ida_count('artisan', 'pending'),
    ];
}

/** GET /dashboard */
function ida_rest_dashboard(): array
{
    global $wpdb;
    $user = wp_get_current_user();

    /* KPI */
    $today_demandes = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'demande_devis'
         AND post_status = 'publish' AND DATE(post_date) = %s",
        current_time('Y-m-d')
    ));
    $upcoming_rdv = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p
         JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_idc_date_rdv'
         WHERE p.post_type = 'rdv' AND p.post_status = 'publish' AND m.meta_value >= %s",
        current_time('Y-m-d')
    ));

    $badges = ida_pending_badges();

    $stats = [
        ['key' => 'demandes', 'label' => 'Demandes de devis', 'value' => ida_count('demande_devis'), 'sub' => $today_demandes . " aujourd'hui", 'icon' => 'file-text', 'link' => '#/demandes'],
        ['key' => 'artisans', 'label' => 'Artisans', 'value' => ida_count('artisan') + ida_count('artisan', 'pending'), 'sub' => $badges['artisans'] . ' à valider', 'icon' => 'hard-hat', 'link' => '#/artisans'],
        ['key' => 'articles', 'label' => 'Articles', 'value' => ida_count('post'), 'sub' => ida_count('post', 'draft') . ' brouillons', 'icon' => 'newspaper', 'link' => '#/articles'],
        ['key' => 'guides', 'label' => 'Guides', 'value' => ida_count('guide'), 'sub' => 'publiés', 'icon' => 'book-open', 'link' => '#/guides'],
        ['key' => 'avis', 'label' => 'Avis', 'value' => ida_count('avis'), 'sub' => $badges['avis'] . ' à modérer', 'icon' => 'star', 'link' => '#/avis'],
        ['key' => 'rdv', 'label' => 'Rendez-vous', 'value' => $upcoming_rdv, 'sub' => 'à venir', 'icon' => 'calendar', 'link' => '#/rdv'],
    ];

    /* Graphique : demandes par mois (6 derniers mois) */
    $chart = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = new DateTimeImmutable(current_time('Y-m-01'));
        $month = $month->modify("-{$i} months");
        $count = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'demande_devis'
             AND post_status IN ('publish','draft') AND post_date >= %s AND post_date < %s",
            $month->format('Y-m-01 00:00:00'),
            $month->modify('+1 month')->format('Y-m-01 00:00:00')
        ));
        $chart[] = ['label' => date_i18n('M', $month->getTimestamp()), 'value' => $count];
    }

    /* Activité récente : derniers contenus tous types confondus */
    $recent = get_posts([
        'post_type'      => ['demande_devis', 'artisan', 'avis', 'post', 'guide', 'rdv', 'realisation'],
        'post_status'    => ['publish', 'pending', 'draft'],
        'posts_per_page' => 8,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    ]);
    $type_labels = [
        'demande_devis' => ['Demande', 'file-text', '#/demandes'],
        'artisan'       => ['Artisan', 'hard-hat', '#/artisans'],
        'avis'          => ['Avis', 'star', '#/avis'],
        'post'          => ['Article', 'newspaper', '#/articles'],
        'guide'         => ['Guide', 'book-open', '#/guides'],
        'rdv'           => ['RDV', 'calendar', '#/rdv'],
        'realisation'   => ['Réalisation', 'image', '#/realisations'],
    ];
    $activity = array_map(static function ($p) use ($type_labels) {
        [$label, $icon, $link] = $type_labels[$p->post_type];
        return [
            'type'  => $label,
            'icon'  => $icon,
            'title' => $p->post_title !== '' ? ida_text($p->post_title) : '(sans titre)',
            'ago'   => ida_ago($p->post_modified),
            'link'  => $link . '/' . $p->ID,
            'status' => ida_post_status_label($p->post_status),
        ];
    }, $recent);

    /* Santé système (repris de l'écran « État du système ») */
    $smtp_on   = defined('WPMS_ON') && WPMS_ON && defined('WPMS_SMTP_HOST');
    $stripe_on = function_exists('idc_stripe_ready') && idc_stripe_ready();
    $required  = ['accueil', 'trouver-un-artisan', 'devis', 'espace-membre', 'inscription', 'tarifs-pro', 'contact', 'mentions-legales', 'confidentialite', 'cgv'];
    $missing   = array_filter($required, static fn($slug) => !get_page_by_path($slug));
    $health = [
        ['label' => 'SMTP', 'ok' => $smtp_on],
        ['label' => 'Stripe', 'ok' => $stripe_on],
        ['label' => 'Pages obligatoires', 'ok' => empty($missing)],
        ['label' => 'Permaliens', 'ok' => (bool) get_option('permalink_structure')],
    ];

    return [
        'greeting' => [
            'name' => get_user_meta($user->ID, 'first_name', true) ?: $user->display_name,
            'date' => date_i18n('l j F Y'),
        ],
        'stats'    => $stats,
        'chart'    => ['title' => 'Demandes de devis — 6 derniers mois', 'data' => $chart],
        'todo'     => [
            ['label' => 'demandes en attente', 'count' => $badges['demandes'], 'link' => '#/demandes?statut=pending', 'icon' => 'file-text'],
            ['label' => 'artisans à vérifier', 'count' => $badges['artisans'], 'link' => '#/artisans/verifications', 'icon' => 'shield-check'],
            ['label' => 'avis à modérer', 'count' => $badges['avis'], 'link' => '#/avis?statut=pending', 'icon' => 'star'],
        ],
        'activity' => $activity,
        'health'   => $health,
    ];
}

/** GET /search?q= — recherche globale (Ctrl+K). */
function ida_rest_search(WP_REST_Request $req): array
{
    $q = trim((string) $req->get_param('q'));
    if (mb_strlen($q) < 2) {
        return ['groups' => []];
    }

    $groups = [];
    $configs = [
        ['label' => 'Pages',        'type' => 'page',        'route' => 'pages'],
        ['label' => 'Articles',     'type' => 'post',        'route' => 'articles'],
        ['label' => 'Guides',       'type' => 'guide',       'route' => 'guides'],
        ['label' => 'Artisans',     'type' => 'artisan',     'route' => 'artisans'],
        ['label' => 'Demandes',     'type' => 'demande_devis', 'route' => 'demandes'],
        ['label' => 'Rendez-vous',  'type' => 'rdv',         'route' => 'rdv'],
        ['label' => 'Avis',         'type' => 'avis',        'route' => 'avis'],
        ['label' => 'Réalisations', 'type' => 'realisation', 'route' => 'realisations'],
    ];

    foreach ($configs as $cfg) {
        $posts = get_posts([
            'post_type'      => $cfg['type'],
            'post_status'    => ['publish', 'draft', 'pending'],
            's'              => $q,
            'posts_per_page' => 5,
        ]);
        // Demandes : chercher aussi par référence / email.
        if ($cfg['type'] === 'demande_devis') {
            $by_meta = get_posts([
                'post_type' => 'demande_devis', 'post_status' => 'any', 'posts_per_page' => 5,
                'meta_query' => [
                    'relation' => 'OR',
                    ['key' => '_idc_reference', 'value' => $q, 'compare' => 'LIKE'],
                    ['key' => '_idc_contact_email', 'value' => $q, 'compare' => 'LIKE'],
                    ['key' => '_idc_contact_name', 'value' => $q, 'compare' => 'LIKE'],
                ],
            ]);
            $seen = wp_list_pluck($posts, 'ID');
            foreach ($by_meta as $p) {
                if (!in_array($p->ID, $seen, true)) {
                    $posts[] = $p;
                }
            }
        }
        if (!$posts) {
            continue;
        }
        $groups[] = [
            'label' => $cfg['label'],
            'items' => array_map(static function ($p) use ($cfg) {
                $sub = '';
                if ($cfg['type'] === 'artisan') {
                    $sub = ida_meta($p->ID, 'ville');
                } elseif ($cfg['type'] === 'demande_devis') {
                    $sub = trim(ida_meta($p->ID, 'reference') . ' · ' . ida_meta($p->ID, 'ville'), ' ·');
                } elseif ($cfg['type'] === 'rdv') {
                    $sub = ida_meta($p->ID, 'date_rdv');
                }
                return [
                    'title' => $p->post_title !== '' ? ida_text($p->post_title) : '(sans titre)',
                    'sub'   => $sub,
                    'link'  => '#/' . $cfg['route'] . '/' . $p->ID,
                ];
            }, array_slice($posts, 0, 5)),
        ];
    }

    /* Utilisateurs (clients, artisans, équipe) */
    $users = (new WP_User_Query([
        'search'         => '*' . $q . '*',
        'search_columns' => ['user_login', 'user_email', 'display_name'],
        'number'         => 5,
    ]))->get_results();
    if ($users) {
        $groups[] = [
            'label' => 'Utilisateurs',
            'items' => array_map(static fn($u) => [
                'title' => $u->display_name,
                'sub'   => $u->user_email . ' · ' . implode(', ', $u->roles),
                'link'  => in_array('client', (array) $u->roles, true) ? '#/clients/' . $u->ID : '#/clients',
            ], $users),
        ];
    }

    return ['groups' => $groups];
}
