<?php
/**
 * Info Devis Core — signature électronique d'un devis par le client.
 *
 * Reproduit le circuit de l'original (views/client/signature.php +
 * DashboardClientController::sign) : le client signe au doigt/souris (canvas →
 * image PNG), on enregistre une preuve (empreinte SHA-256 + IP + user-agent +
 * horodatage) et la demande passe au statut « in_progress ». Table
 * `{prefix}idc_signatures`.
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDC_SIGNATURES_DB_VERSION = '1.0';

function idc_signatures_install(): void
{
    global $wpdb;
    $table   = $wpdb->prefix . 'idc_signatures';
    $collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        demande_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        signature_data LONGTEXT NOT NULL,
        ip_address VARCHAR(64) NULL,
        user_agent VARCHAR(500) NULL,
        document_hash CHAR(64) NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY demande (demande_id)
    ) {$collate};");
    update_option('idc_signatures_db_version', IDC_SIGNATURES_DB_VERSION);
}
add_action('init', static function (): void {
    if (get_option('idc_signatures_db_version') !== IDC_SIGNATURES_DB_VERSION) {
        idc_signatures_install();
    }
}, 1);

/** La demande a-t-elle déjà été signée ? Retourne la ligne ou null. */
function idc_signature_get(int $demande_id): ?object
{
    global $wpdb;
    $t = $wpdb->prefix . 'idc_signatures';
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$t} WHERE demande_id = %d ORDER BY id DESC LIMIT 1",
        $demande_id
    )) ?: null;
}

/** Le client courant peut-il signer cette demande ? (propriété + statut). */
function idc_signature_can_sign(int $demande_id, WP_User $user): bool
{
    if (get_post_type($demande_id) !== 'demande_devis') {
        return false;
    }
    $owner = (int) get_post_meta($demande_id, '_idc_client_user_id', true) === (int) $user->ID
        || strtolower((string) get_post_meta($demande_id, '_idc_contact_email', true)) === strtolower($user->user_email);
    if (!$owner) {
        return false;
    }
    // Signable une fois la demande acceptée par un artisan (ou déjà en cours).
    return in_array((string) get_post_meta($demande_id, '_idc_status', true), ['accepted', 'in_progress'], true);
}

/* ── Signer un devis (AJAX) ────────────────────────────────────────────── */
add_action('wp_ajax_idc_devis_sign', static function (): void {
    if (!isset($_POST['idc_sign_nonce']) || !wp_verify_nonce($_POST['idc_sign_nonce'], 'idc_devis_sign')) {
        wp_send_json(['success' => false, 'error' => 'Session expirée, rechargez la page.'], 403);
    }
    $user       = wp_get_current_user();
    $demande_id = (int) ($_POST['demande_id'] ?? 0);
    $data       = (string) wp_unslash($_POST['signature_data'] ?? '');

    if (!idc_signature_can_sign($demande_id, $user)) {
        wp_send_json(['success' => false, 'error' => 'Devis introuvable ou non signable.'], 403);
    }
    // Le champ doit être une image PNG encodée en data URL.
    if (!preg_match('#^data:image/png;base64,[A-Za-z0-9+/=]+$#', $data) || strlen($data) > 2_000_000) {
        wp_send_json(['success' => false, 'error' => 'Signature manquante ou invalide.'], 400);
    }
    if (idc_signature_get($demande_id)) {
        wp_send_json(['success' => false, 'error' => 'Ce devis a déjà été signé.'], 409);
    }

    $now  = current_time('mysql');
    $hash = hash('sha256', $demande_id . '|' . $user->ID . '|' . $data . '|' . $now);

    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'idc_signatures', [
        'demande_id'     => $demande_id,
        'user_id'        => $user->ID,
        'signature_data' => $data,
        'ip_address'     => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
        'user_agent'     => substr(sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        'document_hash'  => $hash,
        'created_at'     => $now,
    ], ['%d', '%d', '%s', '%s', '%s', '%s', '%s']);

    update_post_meta($demande_id, '_idc_status', 'in_progress');
    update_post_meta($demande_id, '_idc_signed_at', $now);
    update_post_meta($demande_id, '_idc_signature_hash', $hash);

    // Prévenir les artisans ayant accepté la demande.
    if (function_exists('idc_msg_fiches_of_demande') && function_exists('idc_send_mail')) {
        foreach (idc_msg_fiches_of_demande($demande_id) as $fiche_id) {
            if (get_post_meta($demande_id, '_idc_lead_status_' . $fiche_id, true) === 'accepted') {
                $art = get_userdata((int) get_post_meta($fiche_id, '_idc_user_id', true));
                if ($art) {
                    idc_send_mail('devis_signe_artisan', $art->user_email, [
                        '{reference}' => (string) get_post_meta($demande_id, '_idc_reference', true),
                        '{nom}'       => $user->display_name,
                    ]);
                    do_action('idc_devis_signed', (int) $art->ID, (string) get_post_meta($demande_id, '_idc_reference', true), $user->display_name);
                }
            }
        }
    }

    wp_send_json(['success' => true, 'hash' => $hash]);
});
