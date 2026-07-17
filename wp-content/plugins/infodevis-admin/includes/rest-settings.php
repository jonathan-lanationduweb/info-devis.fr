<?php
/**
 * Métiers, page d'accueil, réglages, emails, SEO, paiements, RDV, sauvegardes.
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ---------------------------------------------------------------------- */
/*  Métiers                                                                */
/* ---------------------------------------------------------------------- */

function ida_format_metier(WP_Term $t): array
{
    $artisans = get_posts([
        'post_type' => 'artisan', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 200,
        'tax_query' => [['taxonomy' => 'metier', 'field' => 'term_id', 'terms' => $t->term_id]],
    ]);
    $guides = get_posts([
        'post_type' => 'guide', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 200,
        'tax_query' => [['taxonomy' => 'metier', 'field' => 'term_id', 'terms' => $t->term_id]],
    ]);
    $image = (string) get_term_meta($t->term_id, '_idc_image', true);
    if ($image === '' && function_exists('idv_category_images')) {
        $image = idv_category_images()[$t->slug] ?? '';
    }
    return [
        'id'          => $t->term_id,
        'name'        => ida_text($t->name),
        'slug'        => $t->slug,
        'description' => $t->description,
        'image'       => $image,
        'icon'        => (string) get_term_meta($t->term_id, '_idc_icon', true),
        'prix_min'    => (string) get_term_meta($t->term_id, '_idc_prix_min', true),
        'prix_max'    => (string) get_term_meta($t->term_id, '_idc_prix_max', true),
        'meta_title'  => (string) get_term_meta($t->term_id, '_idc_meta_title', true),
        'meta_description' => (string) get_term_meta($t->term_id, '_idc_meta_description', true),
        'order'       => (string) get_term_meta($t->term_id, '_idc_order', true),
        'hidden'      => get_term_meta($t->term_id, '_idc_hidden', true) === '1',
        'artisans'    => count($artisans),
        'guides'      => count($guides),
        'view'        => get_term_link($t),
        'seo_ok'      => (string) get_term_meta($t->term_id, '_idc_meta_description', true) !== '',
    ];
}

function ida_rest_metiers(): array
{
    $terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0]);
    if (is_wp_error($terms)) {
        return ['rows' => []];
    }
    usort($terms, static function ($a, $b) {
        $oa = get_term_meta($a->term_id, '_idc_order', true);
        $ob = get_term_meta($b->term_id, '_idc_order', true);
        $oa = $oa === '' ? 9999 : (int) $oa;
        $ob = $ob === '' ? 9999 : (int) $ob;
        return $oa === $ob ? strcasecmp($a->name, $b->name) : $oa <=> $ob;
    });
    return ['rows' => array_map('ida_format_metier', $terms)];
}

function ida_rest_metier_create(WP_REST_Request $req)
{
    $body = (array) $req->get_json_params();
    $name = sanitize_text_field((string) ($body['name'] ?? ''));
    if ($name === '') {
        return ida_error('Le nom est obligatoire');
    }
    $result = wp_insert_term($name, 'metier');
    if (is_wp_error($result)) {
        return $result;
    }
    $req['id'] = $result['term_id'];
    return ida_rest_metier_save($req);
}

function ida_rest_metier_save(WP_REST_Request $req)
{
    $term = get_term((int) $req['id'], 'metier');
    if (!$term || is_wp_error($term)) {
        return ida_error('Métier introuvable', 404);
    }
    $body = (array) $req->get_json_params();

    $update = [];
    if (!empty($body['name'])) {
        $update['name'] = sanitize_text_field((string) $body['name']);
    }
    if (array_key_exists('description', $body)) {
        $update['description'] = sanitize_textarea_field((string) $body['description']);
    }
    if ($update) {
        $result = wp_update_term($term->term_id, 'metier', $update);
        if (is_wp_error($result)) {
            return $result;
        }
    }
    foreach (['image', 'icon', 'prix_min', 'prix_max', 'meta_title', 'meta_description'] as $key) {
        if (array_key_exists($key, $body)) {
            update_term_meta($term->term_id, '_idc_' . $key, sanitize_text_field((string) $body[$key]));
        }
    }
    if (array_key_exists('hidden', $body)) {
        update_term_meta($term->term_id, '_idc_hidden', $body['hidden'] ? '1' : '0');
    }
    return ['ok' => true, 'row' => ida_format_metier(get_term($term->term_id, 'metier'))];
}

function ida_rest_metiers_reorder(WP_REST_Request $req)
{
    $ids = (array) ($req->get_json_params()['ids'] ?? []);
    foreach (array_values($ids) as $index => $term_id) {
        update_term_meta((int) $term_id, '_idc_order', (string) ($index + 1));
    }
    return ['ok' => true];
}

function ida_rest_metier_delete(WP_REST_Request $req)
{
    $result = wp_delete_term((int) $req['id'], 'metier');
    return is_wp_error($result) ? $result : ['ok' => true];
}

/* ---------------------------------------------------------------------- */
/*  Page d'accueil                                                         */
/* ---------------------------------------------------------------------- */

function ida_rest_home_get(): array
{
    return ['sections' => ida_home_all(), 'defaults' => ida_home_defaults()];
}

function ida_rest_home_save(WP_REST_Request $req)
{
    $body     = (array) $req->get_json_params();
    $sections = (array) ($body['sections'] ?? []);
    $defaults = ida_home_defaults();

    foreach ($sections as $key => $values) {
        if (!isset($defaults[$key]) || !is_array($values)) {
            continue;
        }
        $clean = [];
        foreach ($defaults[$key] as $field => $default) {
            if (!array_key_exists($field, $values)) {
                continue;
            }
            $v = $values[$field];
            if (is_bool($default)) {
                $clean[$field] = (bool) $v;
            } elseif (is_int($default)) {
                $clean[$field] = (int) $v;
            } elseif (is_array($default)) {
                $clean[$field] = map_deep((array) $v, 'sanitize_text_field');
            } elseif (in_array($field, ['image', 'image_mobile'], true)) {
                $clean[$field] = esc_url_raw((string) $v);
            } else {
                $clean[$field] = sanitize_textarea_field((string) $v);
            }
        }
        update_option('idv_home_' . $key, $clean, false);
    }
    return ['ok' => true, 'sections' => ida_home_all()];
}

/* ---------------------------------------------------------------------- */
/*  Réglages                                                               */
/* ---------------------------------------------------------------------- */

function ida_settings_defaults(): array
{
    return [
        'company_name' => get_bloginfo('name'),
        'logo'         => '',
        'color_primary' => '#207752',
        'color_navy'   => '#0f1f33',
        'google_business' => '',
        'facebook'     => '',
        'instagram'    => '',
        'linkedin'     => '',
        'twitter'      => '',
        'footer_text'  => '',
    ];
}

function ida_rest_settings_get(): array
{
    $saved = get_option('ida_settings', []);
    $settings = array_merge(ida_settings_defaults(), is_array($saved) ? $saved : []);

    // Coordonnées : options idv_contact_* existantes (partagées avec le thème).
    $contact = [
        'phone'   => function_exists('idv_contact') ? idv_contact('phone') : (string) get_option('idv_contact_phone', ''),
        'email'   => function_exists('idv_contact') ? idv_contact('email') : (string) get_option('idv_contact_email', ''),
        'address' => function_exists('idv_contact') ? idv_contact('address') : (string) get_option('idv_contact_address', ''),
    ];

    // Intégrations : uniquement des états, jamais de secrets.
    $stripe_on = function_exists('idc_stripe_ready') && idc_stripe_ready();
    $integrations = [
        ['key' => 'stripe', 'label' => 'Stripe', 'ok' => $stripe_on,
         'detail' => $stripe_on
            ? 'Clés présentes — mode ' . (str_starts_with((string) (defined('IDC_STRIPE_SECRET') ? IDC_STRIPE_SECRET : ''), 'sk_test_') ? 'TEST' : 'LIVE')
            : 'Clés absentes de wp-config.php'],
        ['key' => 'stripe_webhook', 'label' => 'Webhook Stripe', 'ok' => defined('IDC_STRIPE_WEBHOOK_SECRET') && IDC_STRIPE_WEBHOOK_SECRET !== '',
         'detail' => 'Endpoint : /wp-json/idc/v1/stripe-webhook'],
        ['key' => 'smtp', 'label' => 'SMTP (WP Mail SMTP)', 'ok' => defined('WPMS_ON') && WPMS_ON && defined('WPMS_SMTP_HOST'),
         'detail' => defined('WPMS_SMTP_HOST') ? 'Hôte : ' . WPMS_SMTP_HOST : 'Non configuré (wp-config.php)'],
        ['key' => 'siret', 'label' => 'Vérification SIRET', 'ok' => true,
         'detail' => 'API publique recherche-entreprises.api.gouv.fr'],
        ['key' => 'yoast', 'label' => 'Yoast SEO', 'ok' => defined('WPSEO_VERSION'),
         'detail' => defined('WPSEO_VERSION') ? 'Version ' . WPSEO_VERSION : 'Plugin inactif'],
    ];

    /* Santé système (reprise de idc-systeme). */
    global $wp_version, $wpdb;
    $uploads  = wp_upload_dir();
    $required = ['accueil', 'trouver-un-artisan', 'devis', 'espace-membre', 'inscription', 'tarifs-pro', 'contact', 'mentions-legales', 'confidentialite', 'cgv'];
    $missing  = array_filter($required, static fn($slug) => !get_page_by_path($slug));
    $health = [
        ['label' => 'WordPress', 'value' => $wp_version, 'ok' => true],
        ['label' => 'PHP', 'value' => PHP_VERSION, 'ok' => version_compare(PHP_VERSION, '8.1', '>=')],
        ['label' => 'Base de données', 'value' => $wpdb->db_version(), 'ok' => (bool) $wpdb->get_var('SELECT 1')],
        ['label' => 'Thème actif', 'value' => wp_get_theme()->get('Name'), 'ok' => wp_get_theme()->get_stylesheet() === 'info-devis'],
        ['label' => 'Permaliens', 'value' => get_option('permalink_structure') ?: 'par défaut', 'ok' => (bool) get_option('permalink_structure')],
        ['label' => 'Dossier uploads', 'value' => wp_is_writable($uploads['basedir']) ? 'inscriptible' : 'NON inscriptible', 'ok' => wp_is_writable($uploads['basedir'])],
        ['label' => 'WP-Cron', 'value' => (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) ? 'désactivé' : 'actif', 'ok' => !(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON)],
        ['label' => 'Pages obligatoires', 'value' => $missing ? 'manquantes : ' . implode(', ', $missing) : 'toutes présentes', 'ok' => empty($missing)],
    ];

    return [
        'settings'     => $settings,
        'contact'      => $contact,
        'integrations' => $integrations,
        'health'       => $health,
    ];
}

function ida_rest_settings_save(WP_REST_Request $req)
{
    $body = (array) $req->get_json_params();

    if (!empty($body['contact']) && is_array($body['contact'])) {
        foreach (['phone', 'email', 'address'] as $key) {
            if (array_key_exists($key, $body['contact'])) {
                update_option('idv_contact_' . $key, sanitize_text_field((string) $body['contact'][$key]));
            }
        }
    }
    if (!empty($body['settings']) && is_array($body['settings'])) {
        $defaults = ida_settings_defaults();
        $saved    = array_merge($defaults, (array) get_option('ida_settings', []));
        foreach ($defaults as $key => $unused) {
            if (array_key_exists($key, $body['settings'])) {
                $v = (string) $body['settings'][$key];
                $saved[$key] = $key === 'logo' ? esc_url_raw($v) : sanitize_text_field($v);
            }
        }
        update_option('ida_settings', $saved, false);
    }
    return ['ok' => true];
}

/* ---------------------------------------------------------------------- */
/*  Emails                                                                 */
/* ---------------------------------------------------------------------- */

function ida_rest_emails_get()
{
    if (!function_exists('idc_email_templates_catalog')) {
        return ida_error('Le plugin info-devis-core est requis', 500);
    }
    $catalog = idc_email_templates_catalog();
    $templates = [];
    foreach ($catalog as $key => $default) {
        $current = idc_get_email_template($key);
        $templates[] = [
            'key'     => $key,
            'label'   => $default['label'],
            'vars'    => $default['vars'],
            'subject' => $current['subject'],
            'body'    => $current['body'],
            'enabled' => (bool) $current['enabled'],
        ];
    }
    $log = array_reverse(array_slice((array) get_option('idc_email_log', []), -20));
    return ['templates' => $templates, 'log' => $log];
}

function ida_rest_emails_save(WP_REST_Request $req)
{
    if (!function_exists('idc_email_templates_catalog')) {
        return ida_error('Le plugin info-devis-core est requis', 500);
    }
    $body    = (array) $req->get_json_params();
    $input   = (array) ($body['templates'] ?? []);
    $catalog = idc_email_templates_catalog();

    $saved = [];
    foreach ($catalog as $key => $default) {
        $t = null;
        foreach ($input as $candidate) {
            if (($candidate['key'] ?? '') === $key) {
                $t = $candidate;
                break;
            }
        }
        $current = idc_get_email_template($key);
        $saved[$key] = [
            'subject' => sanitize_text_field((string) ($t['subject'] ?? $current['subject'])),
            'body'    => sanitize_textarea_field((string) ($t['body'] ?? $current['body'])),
            'enabled' => $t !== null ? !empty($t['enabled']) : (bool) $current['enabled'],
        ];
    }
    update_option('idc_email_templates', $saved, false);
    return ['ok' => true];
}

/* ---------------------------------------------------------------------- */
/*  SEO                                                                    */
/* ---------------------------------------------------------------------- */

function ida_rest_seo(): array
{
    $types = ['page' => 'Page', 'post' => 'Article', 'guide' => 'Guide', 'artisan' => 'Artisan'];
    $issues = [];
    $total = 0;
    $ok = 0;

    foreach ($types as $post_type => $label) {
        $posts = get_posts(['post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => 300]);
        foreach ($posts as $p) {
            $total++;
            $yoast = ida_yoast($p->ID);
            $problems = [];
            if ($yoast['description'] === '') {
                $problems[] = 'Meta description manquante';
            }
            if (mb_strlen($p->post_title) > 65) {
                $problems[] = 'Titre long (' . mb_strlen($p->post_title) . ' car.)';
            }
            if (!$problems) {
                $ok++;
                continue;
            }
            $issues[] = [
                'id'      => $p->ID,
                'type'    => $label,
                'title'   => $p->post_title,
                'problem' => implode(' · ', $problems),
                'link'    => '#/' . ($post_type === 'post' ? 'articles' : $post_type . 's') . '/' . $p->ID,
            ];
        }
    }

    // Métiers sans meta description.
    $terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false]);
    $terms_missing = 0;
    if (!is_wp_error($terms)) {
        foreach ($terms as $t) {
            $total++;
            if ((string) get_term_meta($t->term_id, '_idc_meta_description', true) === '') {
                $terms_missing++;
                $issues[] = [
                    'id' => $t->term_id, 'type' => 'Métier', 'title' => $t->name,
                    'problem' => 'Meta description manquante', 'link' => '#/metiers',
                ];
            } else {
                $ok++;
            }
        }
    }

    return [
        'score'       => $total > 0 ? (int) round($ok / $total * 100) : 100,
        'indexables'  => $total,
        'missing'     => count($issues),
        'issues'      => array_slice($issues, 0, 100),
        'sitemap'     => home_url('/sitemap_index.xml'),
        'yoast_admin' => admin_url('admin.php?page=wpseo_dashboard'),
        'yoast_on'    => defined('WPSEO_VERSION'),
    ];
}

/* ---------------------------------------------------------------------- */
/*  Paiements                                                              */
/* ---------------------------------------------------------------------- */

function ida_rest_payments(): array
{
    $stripe_on = function_exists('idc_stripe_ready') && idc_stripe_ready();

    $artisans = get_posts(['post_type' => 'artisan', 'post_status' => ['publish', 'pending', 'draft'], 'posts_per_page' => 200]);
    $rows = [];
    $paying = 0;
    foreach ($artisans as $a) {
        $plan    = ida_meta($a->ID, 'plan') ?: 'gratuit';
        $user_id = (int) ida_meta($a->ID, 'user_id');
        $sub_id  = $user_id ? (string) get_user_meta($user_id, '_idc_stripe_subscription_id', true) : '';
        $cus_id  = $user_id ? (string) get_user_meta($user_id, '_idc_stripe_customer_id', true) : '';
        if (!in_array($plan, ['gratuit', ''], true)) {
            $paying++;
        }
        $rows[] = [
            'id'      => $a->ID,
            'title'   => $a->post_title,
            'plan'    => $plan,
            'subscription' => $sub_id,
            'customer'     => $cus_id,
            'stripe_url'   => $cus_id ? 'https://dashboard.stripe.com/customers/' . $cus_id : '',
            'email'   => $user_id && ($u = get_userdata($user_id)) ? $u->user_email : '',
        ];
    }

    return [
        'stripe_on'  => $stripe_on,
        'mode'       => $stripe_on && str_starts_with((string) (defined('IDC_STRIPE_SECRET') ? IDC_STRIPE_SECRET : ''), 'sk_test_') ? 'TEST' : 'LIVE',
        'webhook_on' => defined('IDC_STRIPE_WEBHOOK_SECRET') && IDC_STRIPE_WEBHOOK_SECRET !== '',
        'paying'     => $paying,
        'total'      => count($rows),
        'rows'       => $rows,
        'events'     => count((array) get_option('idc_stripe_events', [])),
        'dashboard'  => 'https://dashboard.stripe.com/',
    ];
}

/* ---------------------------------------------------------------------- */
/*  Rendez-vous (calendrier)                                               */
/* ---------------------------------------------------------------------- */

/** GET /rdv/calendar?month=2026-07 */
function ida_rest_rdv_calendar(WP_REST_Request $req): array
{
    $month = (string) ($req->get_param('month') ?: current_time('Y-m'));
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        $month = current_time('Y-m');
    }
    $rdvs = get_posts([
        'post_type' => 'rdv', 'post_status' => ['publish', 'draft', 'pending'], 'posts_per_page' => 300,
        'meta_query' => [['key' => '_idc_date_rdv', 'value' => $month, 'compare' => 'LIKE']],
    ]);
    $events = array_map(static function ($p) {
        $artisan_id = (int) ida_meta($p->ID, 'artisan_post_id');
        $client_id  = (int) ida_meta($p->ID, 'client_user_id');
        $client     = $client_id ? get_userdata($client_id) : null;
        return [
            'id'      => $p->ID,
            'title'   => $p->post_title !== '' ? $p->post_title : 'Rendez-vous',
            'date'    => ida_meta($p->ID, 'date_rdv'),
            'duree'   => ida_meta($p->ID, 'duree_min'),
            'adresse' => ida_meta($p->ID, 'adresse'),
            'status'  => ida_meta($p->ID, 'statut') ?: 'propose',
            'artisan' => $artisan_id ? get_the_title($artisan_id) : '',
            'client'  => $client ? $client->display_name : '',
        ];
    }, $rdvs);
    return ['month' => $month, 'events' => $events];
}

/* ---------------------------------------------------------------------- */
/*  Sauvegardes                                                            */
/* ---------------------------------------------------------------------- */

function ida_backups_dir(): string
{
    return trailingslashit(ABSPATH) . 'backups';
}

function ida_rest_backups(): array
{
    $dir = ida_backups_dir();
    $items = [];
    if (is_dir($dir)) {
        foreach (scandir($dir, SCANDIR_SORT_DESCENDING) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . '/' . $entry;
            $size = 0;
            if (is_dir($path)) {
                foreach (glob($path . '/*') ?: [] as $f) {
                    $size += (int) @filesize($f);
                }
            } else {
                $size = (int) @filesize($path);
            }
            $items[] = [
                'name' => $entry,
                'date' => date_i18n('d/m/Y H:i', (int) @filemtime($path)),
                'size' => size_format($size),
            ];
        }
    }
    return ['backups' => $items, 'dir' => $dir];
}

/** POST /backups — dump SQL natif PHP (base légère, pas de dépendance mysqldump). */
function ida_rest_backup_create()
{
    global $wpdb;

    $dir = ida_backups_dir() . '/auto-' . current_time('Y-m-d-His');
    if (!wp_mkdir_p($dir)) {
        return ida_error('Impossible de créer le dossier de sauvegarde', 500);
    }

    $file = $dir . '/db-' . DB_NAME . '.sql';
    $fh = fopen($file, 'w');
    if (!$fh) {
        return ida_error('Impossible d\'écrire le fichier', 500);
    }

    fwrite($fh, "-- Sauvegarde InfoDevis Admin — " . current_time('mysql') . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
    $tables = $wpdb->get_col('SHOW TABLES');
    foreach ($tables as $table) {
        $create = $wpdb->get_row('SHOW CREATE TABLE `' . $table . '`', ARRAY_N);
        fwrite($fh, "DROP TABLE IF EXISTS `$table`;\n" . $create[1] . ";\n\n");
        $offset = 0;
        while (true) {
            $rows = $wpdb->get_results("SELECT * FROM `$table` LIMIT 500 OFFSET $offset", ARRAY_A);
            if (!$rows) {
                break;
            }
            foreach ($rows as $row) {
                $values = array_map(static function ($v) use ($wpdb) {
                    if ($v === null) {
                        return 'NULL';
                    }
                    return "'" . esc_sql((string) $v) . "'";
                }, array_values($row));
                fwrite($fh, "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n");
            }
            $offset += 500;
        }
        fwrite($fh, "\n");
    }
    fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($fh);

    return ['ok' => true, 'file' => basename($dir) . '/' . basename($file), 'size' => size_format((int) filesize($file))];
}
