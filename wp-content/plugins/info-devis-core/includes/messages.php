<?php
/**
 * Info Devis Core — messagerie client ↔ artisan.
 *
 * Un « fil » de discussion est la paire (demande_devis, fiche artisan) : dès qu'un
 * artisan a répondu à une demande (meta `_idc_lead_status_{fiche}`), le client et cet
 * artisan peuvent échanger. Table `{prefix}idc_messages`.
 *
 * Accès : le client propriétaire de la demande (par user_id ou email de contact) et
 * l'artisan propriétaire de la fiche liée à cette demande.
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDC_MESSAGES_DB_VERSION = '1.0';

/** Création / mise à jour de la table des messages. */
function idc_messages_install(): void
{
    global $wpdb;
    $table   = $wpdb->prefix . 'idc_messages';
    $collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        demande_id BIGINT UNSIGNED NOT NULL,
        fiche_id BIGINT UNSIGNED NOT NULL,
        sender_user_id BIGINT UNSIGNED NOT NULL,
        body TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        read_at DATETIME NULL,
        PRIMARY KEY (id),
        KEY thread (demande_id, fiche_id),
        KEY recipient_unread (read_at)
    ) {$collate};");

    update_option('idc_messages_db_version', IDC_MESSAGES_DB_VERSION);
}
// Filet de sécurité : crée la table si absente (activation déjà passée).
add_action('init', static function (): void {
    if (get_option('idc_messages_db_version') !== IDC_MESSAGES_DB_VERSION) {
        idc_messages_install();
    }
}, 1);

/** L'utilisateur est-il le client propriétaire de la demande ? */
function idc_msg_user_is_client(int $demande_id, WP_User $user): bool
{
    if (get_post_type($demande_id) !== 'demande_devis') {
        return false;
    }
    if ((int) get_post_meta($demande_id, '_idc_client_user_id', true) === (int) $user->ID) {
        return true;
    }
    $email = (string) get_post_meta($demande_id, '_idc_contact_email', true);
    return $email !== '' && strtolower($email) === strtolower($user->user_email);
}

/** L'utilisateur (via sa fiche) est-il l'artisan lié à cette demande ? */
function idc_msg_fiche_linked(int $demande_id, int $fiche_id): bool
{
    if (get_post_type($fiche_id) !== 'artisan') {
        return false;
    }
    if (get_post_meta($demande_id, '_idc_lead_status_' . $fiche_id, true) !== '') {
        return true;
    }
    // Un fil déjà entamé compte aussi.
    global $wpdb;
    $t = $wpdb->prefix . 'idc_messages';
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$t} WHERE demande_id = %d AND fiche_id = %d",
        $demande_id,
        $fiche_id
    )) > 0;
}

/** Contrôle d'accès complet à un fil. Retourne 'client' | 'artisan' | ''. */
function idc_msg_role_in_thread(int $demande_id, int $fiche_id, WP_User $user): string
{
    if (idc_msg_user_is_client($demande_id, $user) && idc_msg_fiche_linked($demande_id, $fiche_id)) {
        return 'client';
    }
    $fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;
    if ($fiche && (int) $fiche->ID === $fiche_id && idc_msg_fiche_linked($demande_id, $fiche_id)) {
        return 'artisan';
    }
    return '';
}

/** Messages d'un fil (ordre chronologique). */
function idc_msg_get(int $demande_id, int $fiche_id): array
{
    global $wpdb;
    $t = $wpdb->prefix . 'idc_messages';
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$t} WHERE demande_id = %d AND fiche_id = %d ORDER BY id ASC",
        $demande_id,
        $fiche_id
    )) ?: [];
}

/** Marque comme lus les messages d'un fil non envoyés par l'utilisateur. */
function idc_msg_mark_read(int $demande_id, int $fiche_id, int $user_id): void
{
    global $wpdb;
    $t = $wpdb->prefix . 'idc_messages';
    $wpdb->query($wpdb->prepare(
        "UPDATE {$t} SET read_at = %s WHERE demande_id = %d AND fiche_id = %d AND sender_user_id <> %d AND read_at IS NULL",
        current_time('mysql'),
        $demande_id,
        $fiche_id,
        $user_id
    ));
}

/**
 * Nombre de messages non lus adressés à l'utilisateur, restreint à SES fils
 * (client → ses demandes ; artisan → sa fiche). Évite de compter les
 * conversations d'autrui et la fuite du volume d'activité global.
 */
function idc_msg_unread_count(int $user_id): int
{
    global $wpdb;
    $t    = $wpdb->prefix . 'idc_messages';
    $user = get_userdata($user_id);
    if (!$user) {
        return 0;
    }

    // Artisan : uniquement les fils de sa fiche.
    if (in_array('artisan', (array) $user->roles, true)) {
        $fiche = get_posts([
            'post_type'   => 'artisan',
            'post_status' => 'publish',
            'numberposts' => 1,
            'fields'      => 'ids',
            'meta_key'    => '_idc_user_id',
            'meta_value'  => $user_id,
        ]);
        if (!$fiche) {
            return 0;
        }
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$t} WHERE fiche_id = %d AND read_at IS NULL AND sender_user_id <> %d",
            (int) $fiche[0],
            $user_id
        ));
    }

    // Client : uniquement les fils de ses demandes de devis.
    $demandes = get_posts([
        'post_type'   => 'demande_devis',
        'post_status' => 'any',
        'numberposts' => 300,
        'fields'      => 'ids',
        'author'      => $user_id,
    ]);
    if (!$demandes) {
        return 0;
    }
    $in = implode(',', array_map('intval', $demandes));
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$t} WHERE demande_id IN ({$in}) AND read_at IS NULL AND sender_user_id <> %d",
        $user_id
    ));
}

/** Fils d'un client : [{demande, fiche_id, fiche_title, last, unread}]. */
function idc_msg_threads_for_client(WP_User $user): array
{
    $demandes = get_posts([
        'post_type'   => 'demande_devis',
        'post_status' => 'any',
        'numberposts' => 100,
        'meta_query'  => [
            'relation' => 'OR',
            ['key' => '_idc_client_user_id', 'value' => $user->ID],
            ['key' => '_idc_contact_email', 'value' => $user->user_email],
        ],
    ]);
    $threads = [];
    foreach ($demandes as $d) {
        foreach (idc_msg_fiches_of_demande($d->ID) as $fiche_id) {
            $threads[] = idc_msg_thread_summary($d->ID, $fiche_id, $user->ID);
        }
    }
    return idc_msg_sort_threads($threads);
}

/** Fils d'un artisan : demandes liées à sa fiche. */
function idc_msg_threads_for_artisan(WP_Post $fiche, int $user_id): array
{
    global $wpdb;
    // Demandes où cette fiche a un statut de lead.
    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value <> ''",
        '_idc_lead_status_' . $fiche->ID
    ));
    // + demandes avec un fil déjà entamé.
    $t = $wpdb->prefix . 'idc_messages';
    $ids = array_unique(array_merge($ids, $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT demande_id FROM {$t} WHERE fiche_id = %d",
        $fiche->ID
    ))));
    $threads = [];
    foreach ($ids as $demande_id) {
        if (get_post_type((int) $demande_id) === 'demande_devis') {
            $threads[] = idc_msg_thread_summary((int) $demande_id, $fiche->ID, $user_id);
        }
    }
    return idc_msg_sort_threads($threads);
}

/** Fiches artisans liées à une demande (ayant un statut de lead ou un fil). */
function idc_msg_fiches_of_demande(int $demande_id): array
{
    global $wpdb;
    $rows = $wpdb->get_col($wpdb->prepare(
        "SELECT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s AND meta_value <> ''",
        $demande_id,
        '_idc_lead_status_%'
    ));
    $ids = [];
    foreach ($rows as $key) {
        $ids[] = (int) substr($key, strlen('_idc_lead_status_'));
    }
    $t = $wpdb->prefix . 'idc_messages';
    $ids = array_unique(array_merge($ids, array_map('intval', $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT fiche_id FROM {$t} WHERE demande_id = %d",
        $demande_id
    )))));
    return array_values(array_filter($ids, static fn($id) => get_post_type($id) === 'artisan'));
}

/** Résumé d'un fil pour l'affichage. */
function idc_msg_thread_summary(int $demande_id, int $fiche_id, int $user_id): array
{
    global $wpdb;
    $t    = $wpdb->prefix . 'idc_messages';
    $last = $wpdb->get_row($wpdb->prepare(
        "SELECT body, created_at FROM {$t} WHERE demande_id = %d AND fiche_id = %d ORDER BY id DESC LIMIT 1",
        $demande_id,
        $fiche_id
    ));
    $unread = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$t} WHERE demande_id = %d AND fiche_id = %d AND sender_user_id <> %d AND read_at IS NULL",
        $demande_id,
        $fiche_id,
        $user_id
    ));
    return [
        'demande_id'  => $demande_id,
        'fiche_id'    => $fiche_id,
        'reference'   => (string) get_post_meta($demande_id, '_idc_reference', true),
        'demande'     => get_the_title($demande_id),
        'fiche_title' => get_the_title($fiche_id),
        'last_body'   => $last->body ?? '',
        'last_at'     => $last->created_at ?? '',
        'unread'      => $unread,
    ];
}

function idc_msg_sort_threads(array $threads): array
{
    usort($threads, static fn($a, $b) => strcmp((string) $b['last_at'], (string) $a['last_at']));
    return $threads;
}

/* ── Envoi d'un message (AJAX) ─────────────────────────────────────────── */
add_action('wp_ajax_idc_message_send', static function (): void {
    if (!isset($_POST['idc_msg_nonce']) || !wp_verify_nonce($_POST['idc_msg_nonce'], 'idc_message_send')) {
        wp_send_json(['success' => false, 'error' => 'Session expirée, rechargez la page.'], 403);
    }
    $user       = wp_get_current_user();
    $demande_id = (int) ($_POST['demande_id'] ?? 0);
    $fiche_id   = (int) ($_POST['fiche_id'] ?? 0);
    $body       = trim(sanitize_textarea_field(wp_unslash($_POST['body'] ?? '')));

    $role = idc_msg_role_in_thread($demande_id, $fiche_id, $user);
    if ($role === '') {
        wp_send_json(['success' => false, 'error' => 'Conversation non autorisée.'], 403);
    }
    if ($body === '') {
        wp_send_json(['success' => false, 'error' => 'Le message est vide.'], 400);
    }
    if (mb_strlen($body) > 4000) {
        $body = mb_substr($body, 0, 4000);
    }

    global $wpdb;
    $t = $wpdb->prefix . 'idc_messages';
    $now = current_time('mysql');
    $wpdb->insert($t, [
        'demande_id'     => $demande_id,
        'fiche_id'       => $fiche_id,
        'sender_user_id' => $user->ID,
        'body'           => $body,
        'created_at'     => $now,
    ], ['%d', '%d', '%d', '%s', '%s']);
    $id = (int) $wpdb->insert_id;

    // Notifier l'autre partie par email (best effort).
    if ($role === 'client') {
        $to = get_userdata((int) get_post_meta($fiche_id, '_idc_user_id', true));
    } else {
        $client_id = (int) get_post_meta($demande_id, '_idc_client_user_id', true);
        $to = $client_id ? get_userdata($client_id) : null;
        if (!$to) {
            $to = (object) ['user_email' => (string) get_post_meta($demande_id, '_idc_contact_email', true)];
        }
    }
    if (!empty($to->user_email) && function_exists('idc_send_mail')) {
        idc_send_mail('message_nouveau', $to->user_email, [
            '{nom}'        => $user->display_name,
            '{reference}'  => (string) get_post_meta($demande_id, '_idc_reference', true),
            '{entreprise}' => get_the_title($fiche_id),
        ]);
    }
    // Notification in-app pour le destinataire.
    $to_id = ($role === 'client')
        ? (int) get_post_meta($fiche_id, '_idc_user_id', true)
        : (int) get_post_meta($demande_id, '_idc_client_user_id', true);
    do_action('idc_message_sent', $to_id, $user->display_name, $demande_id);

    wp_send_json(['success' => true, 'message' => [
        'id'    => $id,
        'body'  => $body,
        'mine'  => true,
        'at'    => date_i18n('d/m/Y H:i', strtotime($now)),
    ]]);
});

/* ── Marquer un fil comme lu (AJAX) ────────────────────────────────────── */
add_action('wp_ajax_idc_messages_read', static function (): void {
    if (!isset($_POST['idc_msg_nonce']) || !wp_verify_nonce($_POST['idc_msg_nonce'], 'idc_message_send')) {
        wp_send_json(['success' => false], 403);
    }
    $user       = wp_get_current_user();
    $demande_id = (int) ($_POST['demande_id'] ?? 0);
    $fiche_id   = (int) ($_POST['fiche_id'] ?? 0);
    if (idc_msg_role_in_thread($demande_id, $fiche_id, $user) === '') {
        wp_send_json(['success' => false], 403);
    }
    idc_msg_mark_read($demande_id, $fiche_id, $user->ID);
    wp_send_json(['success' => true]);
});
