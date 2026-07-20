<?php
/**
 * Info Devis Core — enregistrement des disponibilités (page « Mes disponibilités »).
 *
 * Auto-save façon original : plages hebdo multi-créneaux, absences datées (avec
 * motif), options avancées RDV (Gold), et liste d'attente pour la synchro agenda.
 * Toutes les actions sont limitées au propriétaire de la fiche (nonce idc_dispos).
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Fiche de l'artisan courant + garde nonce commune. Renvoie la fiche ou coupe. */
function idc_dispos_guard(): WP_Post
{
    $nonce = $_POST['idc_dispos_nonce'] ?? '';
    if (!$nonce || !wp_verify_nonce($nonce, 'idc_dispos')) {
        wp_send_json(['success' => false, 'error' => 'Session expirée, rechargez la page.'], 403);
    }
    $fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;
    if (!$fiche) {
        wp_send_json(['success' => false, 'error' => 'Fiche artisan introuvable.'], 403);
    }
    return $fiche;
}

/* ── Sauvegarde des plages hebdomadaires ───────────────────────────────── */
add_action('wp_ajax_idc_dispos_save', static function (): void {
    $fiche = idc_dispos_guard();
    $keys  = array_keys(IDC_RDV_JOURS); // mon..sun
    $week  = ['mon' => [], 'tue' => [], 'wed' => [], 'thu' => [], 'fri' => [], 'sat' => [], 'sun' => []];

    $creneaux = $_POST['creneaux'] ?? [];
    if (is_array($creneaux)) {
        foreach ($creneaux as $c) {
            $parts = explode('|', (string) $c);
            if (count($parts) !== 3) {
                continue;
            }
            [$day, $start, $end] = $parts;
            $day = (int) $day;
            if ($day < 1 || $day > 7) {
                continue;
            }
            if (!preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end) || $start >= $end) {
                continue;
            }
            $week[$keys[$day - 1]][] = [$start, $end];
        }
    }
    update_post_meta($fiche->ID, '_idc_availability', wp_json_encode($week));
    wp_send_json(['success' => true]);
});

/* ── Ajouter une absence ───────────────────────────────────────────────── */
add_action('wp_ajax_idc_indispo_add', static function (): void {
    $fiche = idc_dispos_guard();

    $normalize = static function (string $v): ?string {
        $v = str_replace('T', ' ', trim($v));
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $v)) {
            return substr($v, 0, 16);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            return $v . ' 00:00';
        }
        return null;
    };
    $start = $normalize(sanitize_text_field(wp_unslash($_POST['date_debut'] ?? '')));
    $end   = $normalize(sanitize_text_field(wp_unslash($_POST['date_fin'] ?? '')));
    $motif = sanitize_text_field(wp_unslash($_POST['motif'] ?? ''));
    if (!$start || !$end) {
        wp_send_json(['success' => false, 'error' => 'Dates invalides.'], 400);
    }
    if ($end < $start) {
        wp_send_json(['success' => false, 'error' => 'La date de fin doit suivre la date de début.'], 400);
    }

    $list   = idc_rdv_indispos_list($fiche->ID);
    $nextId = 1;
    foreach ($list as $a) {
        $nextId = max($nextId, (int) $a['id'] + 1);
    }
    $list[] = ['id' => $nextId, 'start' => $start, 'end' => $end, 'motif' => mb_substr($motif, 0, 100)];
    update_post_meta($fiche->ID, '_idc_indispos', wp_json_encode($list));
    wp_send_json(['success' => true]);
});

/* ── Supprimer une absence ─────────────────────────────────────────────── */
add_action('wp_ajax_idc_indispo_delete', static function (): void {
    $fiche = idc_dispos_guard();
    $id    = (int) ($_POST['id'] ?? 0);
    $list  = array_values(array_filter(idc_rdv_indispos_list($fiche->ID), static fn($a) => (int) $a['id'] !== $id));
    update_post_meta($fiche->ID, '_idc_indispos', wp_json_encode($list));
    wp_send_json(['success' => true]);
});

/* ── Options avancées RDV (Gold uniquement) ────────────────────────────── */
add_action('wp_ajax_idc_params_save', static function (): void {
    $fiche = idc_dispos_guard();
    $plan  = get_post_meta($fiche->ID, '_idc_plan', true) ?: 'gratuit';
    if (!in_array($plan, ['gold', 'illimite', 'pro'], true)) {
        wp_send_json(['success' => false, 'error' => 'Options réservées au plan Gold.'], 403);
    }
    $in = static fn(string $k, array $allowed, int $def): int => in_array((int) ($_POST[$k] ?? $def), $allowed, true) ? (int) $_POST[$k] : $def;

    update_post_meta($fiche->ID, '_idc_duree_rdv_default_min', $in('duree_rdv_default_min', [15, 30, 45, 60, 90, 120, 180], 90));
    update_post_meta($fiche->ID, '_idc_pause_entre_rdv_min', $in('pause_entre_rdv_min', [0, 15, 30, 45, 60], 0));
    update_post_meta($fiche->ID, '_idc_delai_prevenance_h', $in('delai_prevenance_h', [0, 2, 6, 12, 24, 48, 72], 24));
    update_post_meta($fiche->ID, '_idc_max_rdv_jour', $in('max_rdv_jour', [0, 3, 5, 8, 10, 15], 0));
    update_post_meta($fiche->ID, '_idc_accepter_jour_meme', empty($_POST['accepter_jour_meme']) ? 0 : 1);
    update_post_meta($fiche->ID, '_idc_pause_dejeuner', empty($_POST['pause_dejeuner']) ? 0 : 1);
    wp_send_json(['success' => true]);
});

/* ── Liste d'attente : prévenir au lancement d'une fonctionnalité ───────── */
add_action('wp_ajax_idc_notify_launch', static function (): void {
    $fiche   = idc_dispos_guard();
    $email   = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $feature = sanitize_key($_POST['feature'] ?? 'calendar_integration');
    if (!is_email($email)) {
        wp_send_json(['success' => false, 'error' => 'Email invalide.'], 400);
    }
    $list = get_option('idc_launch_waitlist', []);
    if (!is_array($list)) {
        $list = [];
    }
    $list[] = ['email' => $email, 'feature' => $feature, 'fiche' => $fiche->ID, 'date' => current_time('mysql')];
    update_option('idc_launch_waitlist', $list, false);
    wp_send_json(['success' => true, 'message' => 'Vous serez prévenu dès le lancement !']);
});
