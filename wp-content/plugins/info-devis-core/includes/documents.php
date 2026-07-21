<?php
/**
 * Info Devis Core — documents de vérification des artisans.
 *
 * Upload réel (PDF/JPG/PNG, 10 Mo) depuis l'espace artisan, liste avec statut,
 * visualisation via un lien sécurisé (contrôle de propriété). Table
 * `{prefix}idc_documents`. La validation est faite par l'admin (statut).
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDC_DOCUMENTS_DB_VERSION = '1.0';
const IDC_DOCUMENT_TYPES = [
    // Page « Documents » (justificatifs généraux)
    'kbis'           => 'Extrait Kbis',
    'assurance'      => 'Assurance Décennale',
    'carte_identite' => "Pièce d'identité",
    'rib'            => 'RIB',
    'autre'          => 'Autre document',
    // Page « Vérification » (docs typés → badge de confiance)
    'rc_pro'         => 'Assurance RC Pro',
    'identite'       => "Pièce d'identité",
    'decennale'      => 'Assurance Décennale',
    'qualification'  => 'Qualifications professionnelles',
    'certification'  => 'Certifications',
];

function idc_documents_install(): void
{
    global $wpdb;
    $table   = $wpdb->prefix . 'idc_documents';
    $collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        fiche_id BIGINT UNSIGNED NOT NULL,
        type VARCHAR(40) NOT NULL,
        attachment_id BIGINT UNSIGNED NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        note VARCHAR(500) NULL,
        uploaded_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY fiche (fiche_id)
    ) {$collate};");
    update_option('idc_documents_db_version', IDC_DOCUMENTS_DB_VERSION);
}
add_action('init', static function (): void {
    if (get_option('idc_documents_db_version') !== IDC_DOCUMENTS_DB_VERSION) {
        idc_documents_install();
    }
}, 1);

/** Dossier privé des justificatifs KYC (dans uploads, verrouillé par .htaccess). */
function idc_documents_private_dir(): array
{
    $up  = wp_get_upload_dir();
    $dir = $up['basedir'] . '/idc-verification';
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }
    $ht = $dir . '/.htaccess';
    if (is_dir($dir) && !file_exists($ht)) {
        @file_put_contents(
            $ht,
            "# Documents de vérification (KYC) — accès HTTP direct interdit\n"
            . "Options -Indexes\n"
            . "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n"
            . "<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n"
        );
        @file_put_contents($dir . '/index.php', "<?php // Silence is golden.\n");
    }
    return ['path' => $dir, 'url' => $up['baseurl'] . '/idc-verification'];
}

/** Filtre `upload_dir` temporaire : dépose le document dans le dossier privé. */
function idc_documents_upload_dir(array $dirs): array
{
    $sub = '/idc-verification';
    $dirs['subdir'] = $sub;
    $dirs['path']   = $dirs['basedir'] . $sub;
    $dirs['url']    = $dirs['baseurl'] . $sub;
    return $dirs;
}

/** Documents d'une fiche, indexés par type (le plus récent par type). */
function idc_artisan_documents(int $fiche_id): array
{
    global $wpdb;
    $t = $wpdb->prefix . 'idc_documents';
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$t} WHERE fiche_id = %d ORDER BY id DESC",
        $fiche_id
    )) ?: [];
    $by_type = [];
    foreach ($rows as $r) {
        if (!isset($by_type[$r->type])) {
            $by_type[$r->type] = $r;
        }
    }
    return $by_type;
}

/* ── Upload d'un document (AJAX) ────────────────────────────────────────── */
add_action('wp_ajax_idc_artisan_document_upload', static function (): void {
    if (!isset($_POST['idc_doc_nonce']) || !wp_verify_nonce($_POST['idc_doc_nonce'], 'idc_artisan_document')) {
        wp_send_json(['success' => false, 'error' => 'Session expirée, rechargez la page.'], 403);
    }
    $fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;
    if (!$fiche) {
        wp_send_json(['success' => false, 'error' => 'Fiche artisan introuvable.'], 403);
    }
    $type = sanitize_key($_POST['type'] ?? '');
    if (!isset(IDC_DOCUMENT_TYPES[$type])) {
        wp_send_json(['success' => false, 'error' => 'Type de document invalide.'], 400);
    }
    if (empty($_FILES['document']['name'])) {
        wp_send_json(['success' => false, 'error' => 'Aucun fichier sélectionné.'], 400);
    }
    if (($_FILES['document']['size'] ?? 0) > 10 * 1024 * 1024) {
        wp_send_json(['success' => false, 'error' => 'Fichier trop volumineux (max 10 Mo).'], 400);
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Les justificatifs KYC (Kbis, pièce d'identité, RIB…) sont déposés dans un
    // dossier PRIVÉ, protégé de l'accès HTTP direct. Le visualiseur contrôlé
    // (idc_document_view) lit le fichier côté serveur, il continue de fonctionner.
    idc_documents_private_dir(); // s'assure que le dossier + le .htaccess existent
    add_filter('upload_dir', 'idc_documents_upload_dir');
    $aid = media_handle_upload('document', 0, [], [
        'test_form' => false,
        'mimes'     => ['pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png'],
    ]);
    remove_filter('upload_dir', 'idc_documents_upload_dir');
    if (is_wp_error($aid)) {
        wp_send_json(['success' => false, 'error' => 'Format non accepté (PDF, JPG ou PNG uniquement).'], 400);
    }
    // Marque l'attachement comme non attaché à une URL publique visible.
    update_post_meta($aid, '_idc_private_document', 1);

    // Marque l'attachement comme privé et rattaché à la fiche (contrôle d'accès).
    update_post_meta($aid, '_idc_document_fiche', $fiche->ID);

    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'idc_documents', [
        'fiche_id'      => $fiche->ID,
        'type'          => $type,
        'attachment_id' => $aid,
        'status'        => 'pending',
        'uploaded_at'   => current_time('mysql'),
    ], ['%d', '%s', '%d', '%s', '%s']);

    // Marque la fiche « en vérification » si elle ne l'est pas déjà.
    if (get_post_meta($fiche->ID, '_idc_verification_status', true) === '') {
        update_post_meta($fiche->ID, '_idc_verification_status', 'pending');
    }

    // Prévenir l'admin.
    if (function_exists('idc_send_mail')) {
        idc_send_mail('inscription_artisan_admin', get_option('admin_email'), [
            '{entreprise}' => get_the_title($fiche),
            '{nom}'        => wp_get_current_user()->display_name,
        ]);
    }

    wp_send_json(['success' => true, 'message' => 'Document transmis ! Il sera vérifié sous 48 h ouvrées.']);
});

/* ── Visualiser un document (AJAX, accès contrôlé) ──────────────────────── */
add_action('wp_ajax_idc_document_view', static function (): void {
    $doc_id = (int) ($_GET['id'] ?? 0);
    global $wpdb;
    $t   = $wpdb->prefix . 'idc_documents';
    $doc = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t} WHERE id = %d", $doc_id));
    if (!$doc) {
        wp_die('Document introuvable.', '', ['response' => 404]);
    }
    // Seuls le propriétaire de la fiche ou un admin peuvent voir le document.
    $fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;
    $is_owner = $fiche && (int) $fiche->ID === (int) $doc->fiche_id;
    if (!$is_owner && !current_user_can('manage_options') && !current_user_can('idc_manage')) {
        wp_die('Accès refusé.', '', ['response' => 403]);
    }
    $path = get_attached_file((int) $doc->attachment_id);
    if (!$path || !file_exists($path)) {
        wp_die('Fichier introuvable.', '', ['response' => 404]);
    }
    $mime = get_post_mime_type((int) $doc->attachment_id) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="' . basename($path) . '"');
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
});

/* ── Supprimer un document (AJAX, propriétaire, tant que non validé) ─────── */
add_action('wp_ajax_idc_artisan_document_delete', static function (): void {
    if (!isset($_POST['idc_doc_nonce']) || !wp_verify_nonce($_POST['idc_doc_nonce'], 'idc_artisan_document')) {
        wp_send_json(['success' => false, 'error' => 'Session expirée, rechargez la page.'], 403);
    }
    $fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;
    if (!$fiche) {
        wp_send_json(['success' => false, 'error' => 'Fiche artisan introuvable.'], 403);
    }
    $doc_id = (int) ($_POST['id'] ?? 0);
    global $wpdb;
    $t   = $wpdb->prefix . 'idc_documents';
    $doc = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t} WHERE id = %d", $doc_id));
    if (!$doc || (int) $doc->fiche_id !== (int) $fiche->ID) {
        wp_send_json(['success' => false, 'error' => 'Document introuvable.'], 404);
    }
    // Un document déjà validé ne peut plus être supprimé par l'artisan.
    if ($doc->status === 'validated') {
        wp_send_json(['success' => false, 'error' => 'Ce document est déjà validé et ne peut plus être supprimé.'], 403);
    }
    // Supprime le fichier joint puis la ligne.
    if ($doc->attachment_id) {
        wp_delete_attachment((int) $doc->attachment_id, true);
    }
    $wpdb->delete($t, ['id' => $doc_id], ['%d']);
    wp_send_json(['success' => true, 'message' => 'Document supprimé.']);
});
