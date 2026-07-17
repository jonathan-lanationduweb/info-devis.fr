<?php
/**
 * InfoDevis Admin — journalisation des emails.
 *
 * Table {prefix}idv_email_logs alimentée par les hooks natifs :
 *  - filter `wp_mail`        → création de la ligne (statut pending)
 *  - action `wp_mail_succeeded` (WP ≥ 5.9) → statut sent + sent_at
 *  - action `wp_mail_failed` → statut failed + code/message d'erreur
 * Couvre donc AUSSI les emails natifs WordPress (mot de passe oublié…).
 *
 * Le type de message est posé par info-devis-core (idc_send_mail) via le
 * global `idc_current_mail_template` ; défaut : `wordpress`.
 * Rétention configurable (option idv_email_retention_days, défaut 90 j),
 * purge quotidienne WP-Cron. Le corps complet n'est PAS conservé (extrait
 * de 200 caractères maximum, sans données sensibles inutiles).
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDA_EMAIL_LOGS_DB_VERSION = '1';

function ida_email_logs_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'idv_email_logs';
}

/** Création / mise à jour du schéma (une seule fois par version). */
function ida_email_logs_install(): void
{
    if (get_option('idv_email_logs_db_version') === IDA_EMAIL_LOGS_DB_VERSION) {
        return;
    }
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table   = ida_email_logs_table();
    $charset = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        message_type VARCHAR(64) NOT NULL DEFAULT 'wordpress',
        recipient_email VARCHAR(255) NOT NULL DEFAULT '',
        subject VARCHAR(255) NOT NULL DEFAULT '',
        excerpt VARCHAR(200) NOT NULL DEFAULT '',
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        attempts SMALLINT UNSIGNED NOT NULL DEFAULT 1,
        error_code VARCHAR(64) NOT NULL DEFAULT '',
        error_message TEXT NULL,
        failed_body MEDIUMTEXT NULL,
        failed_headers TEXT NULL,
        provider VARCHAR(64) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL,
        sent_at DATETIME NULL,
        resolved_at DATETIME NULL,
        related_object_type VARCHAR(32) NOT NULL DEFAULT '',
        related_object_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        KEY status (status),
        KEY message_type (message_type),
        KEY recipient_email (recipient_email(80)),
        KEY created_at (created_at)
    ) {$charset};");
    update_option('idv_email_logs_db_version', IDA_EMAIL_LOGS_DB_VERSION);
}
add_action('init', 'ida_email_logs_install', 5);

/** Fournisseur d'envoi actuel (sans secret). */
function ida_email_provider(): string
{
    if (defined('WPMS_ON') && WPMS_ON && defined('WPMS_SMTP_HOST')) {
        return 'smtp:' . WPMS_SMTP_HOST;
    }
    return 'php_mail';
}

/* ── Capture des envois ─────────────────────────────────────────────────── */

/** filter wp_mail : enregistre la ligne et mémorise son id pour les hooks suivants. */
add_filter('wp_mail', static function (array $atts): array {
    global $wpdb, $ida_current_log_id;

    $to = $atts['to'] ?? '';
    if (is_array($to)) {
        $to = implode(', ', $to);
    }
    $type    = (string) ($GLOBALS['idc_current_mail_template'] ?? 'wordpress');
    $related = (array) ($GLOBALS['idc_current_mail_related'] ?? []);

    $wpdb->insert(ida_email_logs_table(), [
        'message_type'        => substr(sanitize_key($type) ?: 'wordpress', 0, 64),
        'recipient_email'     => substr(sanitize_text_field((string) $to), 0, 255),
        'subject'             => substr(sanitize_text_field((string) ($atts['subject'] ?? '')), 0, 255),
        'excerpt'             => substr(sanitize_text_field(wp_strip_all_tags((string) ($atts['message'] ?? ''))), 0, 200),
        'status'              => 'pending',
        'attempts'            => 1,
        'provider'            => ida_email_provider(),
        'created_at'          => current_time('mysql'),
        'related_object_type' => substr(sanitize_key((string) ($related['type'] ?? '')), 0, 32),
        'related_object_id'   => (int) ($related['id'] ?? 0),
    ]);
    $ida_current_log_id = (int) $wpdb->insert_id;

    return $atts;
}, 999);

/** Succès (WP ≥ 5.9). */
add_action('wp_mail_succeeded', static function (): void {
    global $wpdb, $ida_current_log_id;
    if (!empty($ida_current_log_id)) {
        $wpdb->update(ida_email_logs_table(), [
            'status'  => 'sent',
            'sent_at' => current_time('mysql'),
        ], ['id' => $ida_current_log_id]);
        $ida_current_log_id = 0;
    }
});

/**
 * Échec : code + message d'erreur PHPMailer. Le corps complet n'est conservé
 * QUE pour les échecs (données du WP_Error), afin de permettre un renvoi
 * fidèle ; il est purgé avec la rétention.
 */
add_action('wp_mail_failed', static function (WP_Error $error): void {
    global $wpdb, $ida_current_log_id;
    if (!empty($ida_current_log_id)) {
        $data    = (array) $error->get_error_data();
        $headers = $data['headers'] ?? [];
        $wpdb->update(ida_email_logs_table(), [
            'status'         => 'failed',
            'error_code'     => substr((string) $error->get_error_code(), 0, 64),
            'error_message'  => substr((string) $error->get_error_message(), 0, 2000),
            'failed_body'    => (string) ($data['message'] ?? ''),
            'failed_headers' => is_array($headers) ? implode("\n", $headers) : (string) $headers,
        ], ['id' => $ida_current_log_id]);
        $ida_current_log_id = 0;
    }
});

/* ── Requêtes ───────────────────────────────────────────────────────────── */

/** Statistiques globales (30 derniers jours + totaux). */
function ida_email_stats(): array
{
    global $wpdb;
    $table = ida_email_logs_table();
    $total  = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    $sent   = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'sent'");
    $failed = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'failed'");
    $pending = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'pending'");
    $last_error = $wpdb->get_row("SELECT id, subject, recipient_email, error_message, provider, created_at FROM {$table} WHERE status = 'failed' ORDER BY id DESC LIMIT 1", ARRAY_A);
    $recent_errors = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE status = 'failed' AND resolved_at IS NULL AND created_at >= %s",
        gmdate('Y-m-d H:i:s', strtotime('-7 days'))
    ));
    return [
        'total'         => $total,
        'sent'          => $sent,
        'failed'        => $failed,
        'pending'       => $pending,
        'success_rate'  => $total > 0 ? round($sent / $total * 100) : null,
        'recent_errors' => $recent_errors,
        'last_error'    => $last_error ?: null,
    ];
}

/** Liste filtrée + paginée. */
function ida_email_logs_query(array $args): array
{
    global $wpdb;
    $table = ida_email_logs_table();

    $where  = ['1=1'];
    $params = [];
    if (!empty($args['status'])) {
        $where[]  = 'status = %s';
        $params[] = sanitize_key($args['status']);
    }
    if (!empty($args['type'])) {
        $where[]  = 'message_type = %s';
        $params[] = sanitize_key($args['type']);
    }
    if (!empty($args['recipient'])) {
        $where[]  = 'recipient_email LIKE %s';
        $params[] = '%' . $wpdb->esc_like(sanitize_text_field($args['recipient'])) . '%';
    }
    if (!empty($args['search'])) {
        $where[]  = 'subject LIKE %s';
        $params[] = '%' . $wpdb->esc_like(sanitize_text_field($args['search'])) . '%';
    }
    if (!empty($args['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $args['date_from'])) {
        $where[]  = 'created_at >= %s';
        $params[] = $args['date_from'] . ' 00:00:00';
    }
    if (!empty($args['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $args['date_to'])) {
        $where[]  = 'created_at <= %s';
        $params[] = $args['date_to'] . ' 23:59:59';
    }
    if (!empty($args['only_errors'])) {
        $where[] = "status = 'failed'";
    }

    $where_sql = implode(' AND ', $where);
    $per_page  = max(1, min(100, (int) ($args['per_page'] ?? 20)));
    $page      = max(1, (int) ($args['page'] ?? 1));
    $offset    = ($page - 1) * $per_page;

    $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
    $list_sql  = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d";

    $total = (int) ($params ? $wpdb->get_var($wpdb->prepare($count_sql, $params)) : $wpdb->get_var($count_sql));
    $rows  = $wpdb->get_results($wpdb->prepare($list_sql, array_merge($params, [$per_page, $offset])), ARRAY_A);

    return ['rows' => $rows ?: [], 'total' => $total, 'page' => $page, 'pages' => (int) ceil($total / $per_page)];
}

/* ── Rétention ──────────────────────────────────────────────────────────── */

function ida_email_retention_days(): int
{
    return max(7, min(730, (int) get_option('idv_email_retention_days', 90)));
}

add_action('idv_email_logs_purge', static function (): void {
    global $wpdb;
    $wpdb->query($wpdb->prepare(
        'DELETE FROM ' . ida_email_logs_table() . ' WHERE created_at < %s',
        gmdate('Y-m-d H:i:s', strtotime('-' . ida_email_retention_days() . ' days'))
    ));
});

add_action('init', static function (): void {
    if (!wp_next_scheduled('idv_email_logs_purge')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'idv_email_logs_purge');
    }
});
