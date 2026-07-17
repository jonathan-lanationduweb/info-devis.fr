<?php
/**
 * Info Devis Core — notifications in-app (cloche de l'en-tête).
 *
 * Table `{prefix}idc_notifications` (user_id, type, title, body, data, is_read,
 * created_at). Créées sur les événements métier (nouveau lead, RDV, avis, message,
 * devis signé…) ; lues via l'API AJAX de la cloche.
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDC_NOTIFS_DB_VERSION = '1.0';

function idc_notifs_install(): void
{
    global $wpdb;
    $table   = $wpdb->prefix . 'idc_notifications';
    $collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        type VARCHAR(100) NOT NULL,
        title VARCHAR(255) NULL,
        body TEXT NULL,
        data LONGTEXT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY user_unread (user_id, is_read)
    ) {$collate};");
    update_option('idc_notifs_db_version', IDC_NOTIFS_DB_VERSION);
}
add_action('init', static function (): void {
    if (get_option('idc_notifs_db_version') !== IDC_NOTIFS_DB_VERSION) {
        idc_notifs_install();
    }
}, 1);

/** Crée une notification pour un utilisateur. */
function idc_notify(int $user_id, string $type, string $title, string $body = '', array $data = []): void
{
    if ($user_id <= 0) {
        return;
    }
    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'idc_notifications', [
        'user_id'    => $user_id,
        'type'       => $type,
        'title'      => $title,
        'body'       => $body,
        'data'       => $data ? wp_json_encode($data) : null,
        'is_read'    => 0,
        'created_at' => current_time('mysql'),
    ], ['%d', '%s', '%s', '%s', '%s', '%d', '%s']);
}

/** Nombre de notifications non lues. */
function idc_notif_unread_count(int $user_id): int
{
    global $wpdb;
    $t = $wpdb->prefix . 'idc_notifications';
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$t} WHERE user_id = %d AND is_read = 0",
        $user_id
    ));
}

/** Formatage relatif « il y a … ». */
function idc_notif_time_ago(string $datetime): string
{
    $diff = current_time('timestamp') - strtotime($datetime);
    if ($diff < 60)    return "à l'instant";
    if ($diff < 3600)  return 'il y a ' . floor($diff / 60) . ' min';
    if ($diff < 86400) return 'il y a ' . floor($diff / 3600) . ' h';
    if ($diff < 604800) return 'il y a ' . floor($diff / 86400) . ' j';
    return date_i18n('d/m/Y', strtotime($datetime));
}

/* ── API : liste (AJAX) ────────────────────────────────────────────────── */
add_action('wp_ajax_idc_notifications_list', static function (): void {
    $user = wp_get_current_user();
    if (!$user->ID) {
        wp_send_json(['success' => false], 403);
    }
    global $wpdb;
    $t = $wpdb->prefix . 'idc_notifications';
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, type, title, body, data, is_read, created_at FROM {$t} WHERE user_id = %d ORDER BY created_at DESC LIMIT 10",
        $user->ID
    )) ?: [];
    $items = [];
    foreach ($rows as $r) {
        $items[] = [
            'id'      => (int) $r->id,
            'type'    => $r->type,
            'title'   => $r->title,
            'body'    => $r->body,
            'data'    => $r->data ? json_decode($r->data, true) : null,
            'is_read' => (int) $r->is_read,
            'ago'     => idc_notif_time_ago($r->created_at),
        ];
    }
    wp_send_json(['success' => true, 'unread' => idc_notif_unread_count((int) $user->ID), 'items' => $items]);
});

/* ── API : marquer lu (AJAX) ───────────────────────────────────────────── */
add_action('wp_ajax_idc_notifications_read', static function (): void {
    if (!isset($_POST['idc_notif_nonce']) || !wp_verify_nonce($_POST['idc_notif_nonce'], 'idc_notifications')) {
        wp_send_json(['success' => false], 403);
    }
    $user = wp_get_current_user();
    if (!$user->ID) {
        wp_send_json(['success' => false], 403);
    }
    global $wpdb;
    $t = $wpdb->prefix . 'idc_notifications';
    if (!empty($_POST['all'])) {
        $wpdb->query($wpdb->prepare("UPDATE {$t} SET is_read = 1 WHERE user_id = %d AND is_read = 0", $user->ID));
    } elseif (!empty($_POST['id'])) {
        $wpdb->query($wpdb->prepare("UPDATE {$t} SET is_read = 1 WHERE id = %d AND user_id = %d", (int) $_POST['id'], $user->ID));
    }
    wp_send_json(['success' => true]);
});

/* ── Déclencheurs : rattachés aux événements métier existants ──────────── */

// Nouveau message → notifier le destinataire.
add_action('idc_message_sent', static function (int $to_user_id, string $from_name, int $demande_id): void {
    idc_notify($to_user_id, 'message_new', 'Nouveau message', 'De ' . $from_name, ['demande_id' => $demande_id]);
}, 10, 3);

// Nouvelle demande de RDV → notifier l'artisan.
add_action('idc_rdv_created', static function (int $artisan_user_id, string $date, string $client): void {
    idc_notify($artisan_user_id, 'rdv_demande', 'Nouvelle demande de RDV', $client . ' — ' . $date);
}, 10, 3);

// RDV confirmé → notifier le client.
add_action('idc_rdv_confirmed', static function (int $client_user_id, string $date, string $entreprise): void {
    idc_notify($client_user_id, 'rdv_confirmed', 'Rendez-vous confirmé', $entreprise . ' — ' . $date);
}, 10, 3);

// Nouvel avis → notifier l'artisan.
add_action('idc_avis_created', static function (int $artisan_user_id, int $note): void {
    idc_notify($artisan_user_id, 'avis_new', 'Nouvel avis reçu', 'Note : ' . $note . '/5');
}, 10, 2);

// Devis signé → notifier l'artisan.
add_action('idc_devis_signed', static function (int $artisan_user_id, string $reference, string $client): void {
    idc_notify($artisan_user_id, 'devis_signe', 'Devis signé', $client . ' a signé ' . $reference);
}, 10, 3);

// Nouveau lead (demande matchée) → notifier l'artisan.
add_action('idc_lead_new', static function (int $artisan_user_id, string $metiers, string $ville): void {
    idc_notify($artisan_user_id, 'new_lead', 'Nouvelle opportunité', $metiers . ' à ' . $ville);
}, 10, 3);
