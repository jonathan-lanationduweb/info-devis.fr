<?php
/**
 * Info Devis Core — pages d'administration : Emails, État du système.
 * Accès : capacité `idc_manage` (administrateur + gestionnaire).
 * Aucun secret n'est affiché sur ces écrans.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', static function (): void {
    add_menu_page(
        'Info Devis',
        'Info Devis',
        'idc_manage',
        'idc-systeme',
        'idc_render_system_page',
        'dashicons-hammer',
        58
    );
    add_submenu_page('idc-systeme', 'État du système', 'État du système', 'idc_manage', 'idc-systeme', 'idc_render_system_page');
    add_submenu_page('idc-systeme', 'Modèles d’emails', 'Emails', 'idc_manage', 'idc-emails', 'idc_render_emails_page');
});

/* ---------------------------------------------------------------------- */
/*  Modèles d'emails                                                       */
/* ---------------------------------------------------------------------- */

function idc_render_emails_page(): void
{
    if (!current_user_can('idc_manage')) {
        wp_die('Accès refusé.');
    }

    $catalog = idc_email_templates_catalog();

    if (isset($_POST['idc_emails_nonce']) && wp_verify_nonce($_POST['idc_emails_nonce'], 'idc_save_emails')) {
        $saved = [];
        foreach ($catalog as $key => $tpl) {
            $saved[$key] = [
                'subject' => sanitize_text_field(wp_unslash($_POST['subject_' . $key] ?? $tpl['subject'])),
                'body'    => sanitize_textarea_field(wp_unslash($_POST['body_' . $key] ?? $tpl['body'])),
                'enabled' => !empty($_POST['enabled_' . $key]),
            ];
        }
        update_option('idc_email_templates', $saved, false);
        echo '<div class="notice notice-success"><p>Modèles enregistrés.</p></div>';
    }

    echo '<div class="wrap"><h1>Modèles d’emails</h1>';
    echo '<p>Personnalisez chaque email envoyé par le site. Les variables entre accolades sont remplacées automatiquement à l’envoi.</p>';
    echo '<form method="post">';
    wp_nonce_field('idc_save_emails', 'idc_emails_nonce');

    foreach ($catalog as $key => $default) {
        $tpl = idc_get_email_template($key);
        echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:16px 20px;margin-bottom:16px;max-width:860px;">';
        echo '<h2 style="margin-top:0;">' . esc_html($default['label']) . '</h2>';
        echo '<p><label><input type="checkbox" name="enabled_' . esc_attr($key) . '" value="1" ' . checked($tpl['enabled'], true, false) . ' /> Email activé</label></p>';
        echo '<p><label style="display:block;font-weight:600;">Sujet</label><input type="text" name="subject_' . esc_attr($key) . '" value="' . esc_attr($tpl['subject']) . '" class="large-text" /></p>';
        echo '<p><label style="display:block;font-weight:600;">Message</label><textarea name="body_' . esc_attr($key) . '" rows="6" class="large-text">' . esc_textarea($tpl['body']) . '</textarea></p>';
        echo '<p style="color:#6b7280;font-size:12px;">Variables disponibles : <code>' . esc_html($default['vars']) . '</code></p>';
        echo '</div>';
    }

    echo '<p><button type="submit" class="button button-primary">Enregistrer les modèles</button></p>';
    echo '</form>';

    // Journal des 15 derniers envois.
    $log = array_reverse(array_slice(get_option('idc_email_log', []), -15));
    if ($log) {
        echo '<h2>Derniers envois</h2><table class="widefat striped" style="max-width:860px;"><thead><tr><th>Date</th><th>Modèle</th><th>Destinataire</th><th>Statut</th></tr></thead><tbody>';
        foreach ($log as $entry) {
            $ok = ($entry['status'] ?? '') === 'envoye';
            echo '<tr><td>' . esc_html($entry['date'] ?? '') . '</td><td>' . esc_html($entry['template'] ?? '') . '</td><td>' . esc_html($entry['to'] ?? '') . '</td><td>'
                . ($ok ? '<span style="color:#00a32a;">✔ envoyé</span>' : '<span style="color:#d63638;">✘ échec</span>') . '</td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div>';
}

/* ---------------------------------------------------------------------- */
/*  État du système                                                        */
/* ---------------------------------------------------------------------- */

function idc_render_system_page(): void
{
    if (!current_user_can('idc_manage')) {
        wp_die('Accès refusé.');
    }
    global $wpdb, $wp_version;

    $theme  = wp_get_theme();
    $plugin = get_file_data(WP_PLUGIN_DIR . '/info-devis-core/info-devis-core.php', ['Version' => 'Version']);

    $checks = [];

    $checks[] = ['Version WordPress', $wp_version, true];
    $checks[] = ['Version PHP', PHP_VERSION, version_compare(PHP_VERSION, '8.1', '>=')];
    $checks[] = ['Extension Info Devis Core', $plugin['Version'] ?: 'inconnue', true];
    $checks[] = ['Thème actif', $theme->get('Name') . ' ' . $theme->get('Version'), $theme->get_stylesheet() === 'info-devis'];

    $db_ok = (bool) $wpdb->get_var('SELECT 1');
    $checks[] = ['Base de données', $db_ok ? 'connectée (' . $wpdb->db_version() . ')' : 'erreur', $db_ok];

    $permalinks = get_option('permalink_structure');
    $checks[] = ['Permaliens', $permalinks ?: 'par défaut (à corriger)', !empty($permalinks)];

    $smtp_on = defined('WPMS_ON') && WPMS_ON && defined('WPMS_SMTP_HOST');
    $checks[] = ['SMTP (WP Mail SMTP)', $smtp_on ? 'configuré via wp-config (' . WPMS_SMTP_HOST . ')' : 'non configuré', $smtp_on];

    $stripe_on = function_exists('idc_stripe_ready') && idc_stripe_ready();
    $stripe_detail = $stripe_on ? 'clés présentes (mode ' . (str_starts_with((string) IDC_STRIPE_SECRET, 'sk_test_') ? 'TEST' : 'LIVE') . ')' : 'clés absentes de wp-config';
    $checks[] = ['Stripe', $stripe_detail, $stripe_on];

    $webhook_on = defined('IDC_STRIPE_WEBHOOK_SECRET') && IDC_STRIPE_WEBHOOK_SECRET !== '';
    $checks[] = ['Webhook Stripe', $webhook_on ? 'secret configuré — endpoint /wp-json/idc/v1/stripe-webhook' : 'secret manquant', $webhook_on];

    $cron_ok = !(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON);
    $checks[] = ['WP-Cron', $cron_ok ? 'actif' : 'désactivé', $cron_ok];

    $uploads = wp_upload_dir();
    $writable = wp_is_writable($uploads['basedir']);
    $checks[] = ['Dossier uploads', $writable ? 'accessible en écriture' : 'NON inscriptible', $writable];

    // Pages obligatoires.
    $required_pages = ['accueil', 'trouver-un-artisan', 'devis', 'espace-membre', 'inscription', 'tarifs-pro', 'contact', 'mentions-legales', 'confidentialite', 'cgv'];
    $missing = array_filter($required_pages, static fn($slug) => !get_page_by_path($slug));
    $checks[] = ['Pages obligatoires', $missing ? 'manquantes : ' . implode(', ', $missing) : 'toutes présentes', empty($missing)];

    // Activité.
    $pending_demandes = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_idc_status'
         WHERE p.post_type = %s AND p.post_status = 'publish' AND m.meta_value = 'pending'",
        'demande_devis'
    ));
    $checks[] = ['Demandes de devis en attente', (string) $pending_demandes, true];

    $pending_avis = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_idc_status'
         WHERE p.post_type = %s AND p.post_status = 'publish' AND m.meta_value = 'pending'",
        'avis'
    ));
    $checks[] = ['Avis en attente de modération', (string) $pending_avis, true];

    $pending_artisans = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'artisan' AND post_status = 'pending'"
    );
    $checks[] = ['Fiches artisans à valider', (string) $pending_artisans, true];

    echo '<div class="wrap"><h1>Info Devis — État du système</h1>';
    echo '<table class="widefat striped" style="max-width:860px;"><thead><tr><th>Élément</th><th>État</th><th></th></tr></thead><tbody>';
    foreach ($checks as [$label, $value, $ok]) {
        echo '<tr><td><strong>' . esc_html($label) . '</strong></td><td>' . esc_html($value) . '</td><td>'
            . ($ok ? '<span style="color:#00a32a;">✔</span>' : '<span style="color:#d63638;">✘</span>') . '</td></tr>';
    }
    echo '</tbody></table>';
    echo '<p style="color:#6b7280;">Aucune valeur secrète n’est affichée sur cette page.</p></div>';
}
