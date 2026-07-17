<?php
/**
 * InfoDevis Admin — API du centre d'emails.
 * Routes idc/v1/admin/emails/* (capacité idc_manage via ida_route).
 * Sécurité : nonce REST géré par api.js, validation serveur, aucun secret
 * SMTP retourné, limitation d'envoi des tests (5 / 10 min).
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', static function (): void {
    ida_route('/emails/overview', 'GET', 'ida_rest_emails_overview');
    ida_route('/emails/test', 'POST', 'ida_rest_emails_test');
    ida_route('/emails/deliverability', 'POST', 'ida_rest_emails_deliverability');
    ida_route('/emails/logs', 'GET', 'ida_rest_emails_logs');
    ida_route('/emails/logs/(?P<id>\d+)/resend', 'POST', 'ida_rest_emails_resend');
    ida_route('/emails/logs/(?P<id>\d+)/resolve', 'POST', 'ida_rest_emails_resolve');
    ida_route('/emails/template-test', 'POST', 'ida_rest_emails_template_test');
    ida_route('/emails/settings', 'POST', 'ida_rest_emails_settings');
});

/** Configuration SMTP visible (JAMAIS de mot de passe / clé). */
function ida_smtp_public_config(): array
{
    $on = defined('WPMS_ON') && WPMS_ON;
    return [
        'method'     => $on ? 'SMTP (WP Mail SMTP, constantes wp-config)' : 'PHP mail()',
        'host'       => $on && defined('WPMS_SMTP_HOST') ? WPMS_SMTP_HOST : '',
        'port'       => $on && defined('WPMS_SMTP_PORT') ? (int) WPMS_SMTP_PORT : 0,
        'encryption' => $on && defined('WPMS_SSL') ? strtoupper((string) WPMS_SSL) : '',
        'from_email' => defined('WPMS_MAIL_FROM') ? WPMS_MAIL_FROM : get_option('admin_email'),
        'from_name'  => defined('WPMS_MAIL_FROM_NAME') ? WPMS_MAIL_FROM_NAME : get_bloginfo('name'),
        'configured' => $on,
        'plugin'     => is_plugin_active('wp-mail-smtp/wp_mail_smtp.php') ? 'WP Mail SMTP' : '',
    ];
}

/* ── Vue d'ensemble (cartes) ────────────────────────────────────────────── */

function ida_rest_emails_overview()
{
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $smtp = ida_smtp_public_config();

    // État de connexion SMTP réel (socket, sans authentification, 4 s max).
    $connection = ['state' => 'non_configure', 'detail' => 'Aucun serveur SMTP configuré'];
    if ($smtp['configured'] && $smtp['host']) {
        $errno = 0;
        $errstr = '';
        $start = microtime(true);
        $fp = @fsockopen($smtp['host'], $smtp['port'] ?: 25, $errno, $errstr, 4);
        if ($fp) {
            $banner = trim((string) fgets($fp, 256));
            fclose($fp);
            $connection = [
                'state'  => 'ok',
                'detail' => sprintf('Connecté en %d ms — %s', (int) round((microtime(true) - $start) * 1000), $banner ?: 'bannière vide'),
            ];
        } else {
            $connection = ['state' => 'erreur', 'detail' => sprintf('Injoignable (%s:%d) : %s', $smtp['host'], $smtp['port'], $errstr ?: ('errno ' . $errno))];
        }
    }

    $templates_count = function_exists('idc_email_templates_catalog') ? count(idc_email_templates_catalog()) : 0;

    return [
        'stats'          => ida_email_stats(),
        'smtp'           => $smtp,
        'connection'     => $connection,
        'last_test'      => get_option('idv_email_last_test', null),
        'deliverability' => get_option('idv_email_deliverability', null),
        'templates'      => $templates_count,
        'retention_days' => ida_email_retention_days(),
    ];
}

/* ── Test d'envoi ───────────────────────────────────────────────────────── */

function ida_rest_emails_test(WP_REST_Request $req)
{
    // Anti-abus : 5 tests max par 10 minutes.
    $count = (int) get_transient('idv_email_test_count');
    if ($count >= 5) {
        return ida_error('Limite atteinte : 5 tests maximum par 10 minutes.', 429);
    }
    set_transient('idv_email_test_count', $count + 1, 10 * MINUTE_IN_SECONDS);

    $body    = (array) $req->get_json_params();
    $to      = sanitize_email((string) ($body['to'] ?? ''));
    $subject = sanitize_text_field((string) ($body['subject'] ?? '')) ?: '[Test] InfoDevis — email de test';
    $message = sanitize_textarea_field((string) ($body['message'] ?? '')) ?: 'Ceci est un email de test envoyé depuis InfoDevis Admin.';
    $html    = !empty($body['html']);

    if (!$to || !is_email($to)) {
        return ida_error('Adresse destinataire invalide.');
    }

    $headers = $html ? ['Content-Type: text/html; charset=UTF-8'] : [];
    if ($html) {
        $message = wpautop(esc_html($message));
    }

    // Capture de l'erreur PHPMailer exacte.
    $captured = null;
    $capture  = static function (WP_Error $e) use (&$captured): void {
        $captured = ['code' => (string) $e->get_error_code(), 'message' => $e->get_error_message()];
    };
    add_action('wp_mail_failed', $capture, 5);

    $GLOBALS['idc_current_mail_template'] = 'test_admin';
    $start = microtime(true);
    $sent  = wp_mail($to, $subject, $message, $headers);
    $ms    = (int) round((microtime(true) - $start) * 1000);
    unset($GLOBALS['idc_current_mail_template']);
    remove_action('wp_mail_failed', $capture, 5);

    $result = [
        'sent'        => (bool) $sent,
        'duration_ms' => $ms,
        'to'          => $to,
        'provider'    => ida_email_provider(),
        'error'       => $captured,
        'date'        => current_time('mysql'),
    ];
    update_option('idv_email_last_test', $result, false);

    return $result;
}

/* ── Analyse de délivrabilité (contrôles DNS réels) ─────────────────────── */

function ida_rest_emails_deliverability()
{
    $smtp   = ida_smtp_public_config();
    $domain = substr(strrchr((string) $smtp['from_email'], '@'), 1);
    if (!$domain) {
        return ida_error('Adresse d\'expédition invalide — impossible de déterminer le domaine.');
    }

    $lookup_txt = static function (string $host): array {
        $records = @dns_get_record($host, DNS_TXT);
        if (!is_array($records)) {
            return [];
        }
        return array_map(static fn($r) => (string) ($r['txt'] ?? ''), $records);
    };

    // SPF : TXT du domaine contenant v=spf1.
    $spf_records = array_values(array_filter($lookup_txt($domain), static fn($t) => str_starts_with($t, 'v=spf1')));
    $spf = [
        'state'  => $spf_records ? 'ok' : 'absent',
        'record' => $spf_records[0] ?? '',
    ];

    // DMARC : TXT sur _dmarc.domaine.
    $dmarc_records = array_values(array_filter($lookup_txt('_dmarc.' . $domain), static fn($t) => str_starts_with($t, 'v=DMARC1')));
    $dmarc = [
        'state'  => $dmarc_records ? 'ok' : 'absent',
        'record' => $dmarc_records[0] ?? '',
    ];

    // DKIM : sélecteurs courants testés — sans sélecteur connu, résultat honnête « non détecté ».
    $dkim = ['state' => 'inconnu', 'selector' => '', 'note' => 'Le sélecteur DKIM dépend de votre fournisseur — non détectable automatiquement sans lui.'];
    foreach (['default', 'mail', 'brevo', 'k1', 'google', 'selector1', 'mandrill'] as $selector) {
        $found = array_filter($lookup_txt($selector . '._domainkey.' . $domain), static fn($t) => str_contains($t, 'v=DKIM1') || str_contains($t, 'k=rsa'));
        if ($found) {
            $dkim = ['state' => 'ok', 'selector' => $selector, 'note' => ''];
            break;
        }
    }

    // MX du domaine (bonus diagnostic).
    $mx = @dns_get_record($domain, DNS_MX);
    $has_mx = is_array($mx) && count($mx) > 0;

    $score = 0;
    $score += $spf['state'] === 'ok' ? 35 : 0;
    $score += $dkim['state'] === 'ok' ? 35 : ($dkim['state'] === 'inconnu' ? 10 : 0);
    $score += $dmarc['state'] === 'ok' ? 20 : 0;
    $score += $has_mx ? 10 : 0;

    $recommendations = [];
    if ($spf['state'] !== 'ok') {
        $recommendations[] = "Ajoutez un enregistrement TXT SPF sur {$domain} (ex. « v=spf1 include:spf.brevo.com ~all » pour Brevo).";
    }
    if ($dkim['state'] !== 'ok') {
        $recommendations[] = 'Activez la signature DKIM chez votre fournisseur SMTP et publiez la clé fournie (TXT « selecteur._domainkey »).';
    }
    if ($dmarc['state'] !== 'ok') {
        $recommendations[] = "Publiez un enregistrement TXT sur _dmarc.{$domain} (ex. « v=DMARC1; p=none; rua=mailto:postmaster@{$domain} » pour commencer).";
    }
    if (!$has_mx) {
        $recommendations[] = "Le domaine {$domain} n'a pas d'enregistrement MX : les réponses à vos emails seront perdues.";
    }
    if (str_contains((string) $smtp['host'], 'mailtrap')) {
        $recommendations[] = 'Mailtrap est un bac à sable : les emails ne partent pas réellement. Passez sur Brevo (ou équivalent) avant la mise en production.';
    }

    $result = [
        'domain'          => $domain,
        'spf'             => $spf,
        'dkim'            => $dkim,
        'dmarc'           => $dmarc,
        'mx'              => $has_mx,
        'score'           => $score,
        'recommendations' => $recommendations,
        'checked_at'      => current_time('mysql'),
    ];
    update_option('idv_email_deliverability', $result, false);

    return $result;
}

/* ── Journaux ───────────────────────────────────────────────────────────── */

function ida_rest_emails_logs(WP_REST_Request $req)
{
    $result = ida_email_logs_query([
        'status'      => (string) $req->get_param('status'),
        'type'        => (string) $req->get_param('type'),
        'recipient'   => (string) $req->get_param('recipient'),
        'search'      => (string) $req->get_param('search'),
        'date_from'   => (string) $req->get_param('date_from'),
        'date_to'     => (string) $req->get_param('date_to'),
        'only_errors' => (bool) $req->get_param('only_errors'),
        'page'        => (int) ($req->get_param('page') ?: 1),
        'per_page'    => (int) ($req->get_param('per_page') ?: 20),
    ]);

    // Types présents (pour le filtre).
    global $wpdb;
    $types = $wpdb->get_col('SELECT DISTINCT message_type FROM ' . ida_email_logs_table() . ' ORDER BY message_type');

    $result['rows'] = array_map(static function (array $r): array {
        unset($r['failed_headers']); // inutile côté client
        $r['has_body'] = !empty($r['failed_body']);
        $r['failed_body'] = $r['failed_body'] ? substr((string) $r['failed_body'], 0, 1500) : '';
        return $r;
    }, $result['rows']);
    $result['types'] = $types ?: [];

    return $result;
}

/** Renvoi d'un email échoué (corps conservé pour les échecs uniquement). */
function ida_rest_emails_resend(WP_REST_Request $req)
{
    global $wpdb;
    $id  = (int) $req['id'];
    $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . ida_email_logs_table() . ' WHERE id = %d', $id), ARRAY_A);
    if (!$row) {
        return ida_error('Entrée introuvable.', 404);
    }
    if ($row['status'] !== 'failed') {
        return ida_error('Seuls les emails échoués peuvent être renvoyés.');
    }
    if (empty($row['failed_body'])) {
        return ida_error('Le contenu de cet email n\'a pas été conservé — renvoi impossible.');
    }

    $headers = $row['failed_headers'] ? explode("\n", (string) $row['failed_headers']) : [];
    $GLOBALS['idc_current_mail_template'] = $row['message_type'];

    $captured = null;
    $capture  = static function (WP_Error $e) use (&$captured): void {
        $captured = $e->get_error_message();
    };
    add_action('wp_mail_failed', $capture, 5);
    $sent = wp_mail($row['recipient_email'], $row['subject'], (string) $row['failed_body'], $headers);
    remove_action('wp_mail_failed', $capture, 5);
    unset($GLOBALS['idc_current_mail_template']);

    // Incrémente le compteur de tentatives sur la ligne d'origine ; si le
    // renvoi réussit, la ligne passe « sent » (le nouvel envoi a aussi créé
    // sa propre ligne de journal).
    $wpdb->update(ida_email_logs_table(), array_merge(
        ['attempts' => (int) $row['attempts'] + 1],
        $sent ? ['status' => 'sent', 'sent_at' => current_time('mysql'), 'resolved_at' => current_time('mysql')] : []
    ), ['id' => $id]);

    return ['sent' => (bool) $sent, 'error' => $captured];
}

/** Marquer une erreur comme résolue. */
function ida_rest_emails_resolve(WP_REST_Request $req)
{
    global $wpdb;
    $updated = $wpdb->update(ida_email_logs_table(), ['resolved_at' => current_time('mysql')], ['id' => (int) $req['id']]);
    return ['ok' => $updated !== false];
}

/* ── Test d'un modèle avec variables d'exemple ──────────────────────────── */

function ida_rest_emails_template_test(WP_REST_Request $req)
{
    if (!function_exists('idc_send_mail')) {
        return ida_error('Le plugin info-devis-core est requis', 500);
    }
    $body = (array) $req->get_json_params();
    $key  = sanitize_key((string) ($body['key'] ?? ''));
    $to   = sanitize_email((string) ($body['to'] ?? ''));
    if (!$to || !is_email($to)) {
        return ida_error('Adresse destinataire invalide.');
    }
    if (!array_key_exists($key, idc_email_templates_catalog())) {
        return ida_error('Modèle inconnu.');
    }

    $count = (int) get_transient('idv_email_test_count');
    if ($count >= 5) {
        return ida_error('Limite atteinte : 5 tests maximum par 10 minutes.', 429);
    }
    set_transient('idv_email_test_count', $count + 1, 10 * MINUTE_IN_SECONDS);

    // Variables d'exemple clairement factices (jamais de vraies données client).
    $sent = idc_send_mail($key, $to, [
        '{nom}'        => 'Jean Exemple',
        '{email}'      => 'jean.exemple@example.com',
        '{entreprise}' => 'Entreprise Exemple',
        '{reference}'  => 'DV000000-TEST',
        '{metier}'     => 'Plomberie',
        '{ville}'      => 'Paris',
        '{date}'       => current_time('Y-m-d H:i'),
        '{note}'       => '5',
        '{plan}'       => 'Gold',
        '{montant}'    => '14,00 €',
        '{lien_admin}' => home_url('/infodevis-admin/'),
        '{lien_reset}' => home_url('/connexion/'),
    ]);

    return ['sent' => (bool) $sent];
}

/* ── Réglages (rétention) ───────────────────────────────────────────────── */

function ida_rest_emails_settings(WP_REST_Request $req)
{
    $body = (array) $req->get_json_params();
    if (isset($body['retention_days'])) {
        update_option('idv_email_retention_days', max(7, min(730, (int) $body['retention_days'])), false);
    }
    return ['ok' => true, 'retention_days' => ida_email_retention_days()];
}
