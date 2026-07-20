<?php
/**
 * Info Devis Core — rendez-vous et disponibilités (façon Doctolib).
 *
 * Modèle :
 * - Fiche artisan : meta `_idc_availability` (JSON semaine : jour => {on, start, end}),
 *   `_idc_indispos` (JSON dates YYYY-MM-DD), `_idc_duree_rdv_default_min`,
 *   `_idc_delai_prevenance_h`, `_idc_max_rdv_jour` (0 = illimité) — mêmes
 *   réglages que l'application d'origine.
 * - CPT `rdv` : _idc_artisan_post_id, _idc_client_user_id, _idc_date_rdv
 *   (Y-m-d H:i), _idc_duree_min, _idc_statut (propose|confirme|annule|termine).
 *
 * Protections : créneaux passés refusés, délai de prévenance, double
 * réservation impossible (créneau déjà pris ou indisponibilité), actions
 * limitées au propriétaire (client → SES RDV, artisan → SA fiche).
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDC_RDV_JOURS = ['mon' => 'Lundi', 'tue' => 'Mardi', 'wed' => 'Mercredi', 'thu' => 'Jeudi', 'fri' => 'Vendredi', 'sat' => 'Samedi', 'sun' => 'Dimanche'];

/**
 * Plages hebdomadaires multi-créneaux d'une fiche.
 * Retour : [1..7 => [['HH:MM','HH:MM'], …]] (1 = lundi … 7 = dimanche).
 * Lit `_idc_availability` ; compatible avec l'ancien format {on,start,end}.
 */
function idc_rdv_ranges(int $fiche_id): array
{
    $saved = json_decode((string) get_post_meta($fiche_id, '_idc_availability', true), true);
    $keys  = array_keys(IDC_RDV_JOURS);
    $out   = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 7 => []];

    if (!is_array($saved)) {
        foreach ([1, 2, 3, 4, 5] as $n) {
            $out[$n] = [['08:00', '18:00']]; // défaut : lun-ven 08:00-18:00
        }
        return $out;
    }
    foreach ($keys as $i => $jour) {
        $n    = $i + 1;
        $cell = $saved[$jour] ?? null;
        if (is_array($cell) && array_key_exists('on', $cell)) {
            // Ancien format : une seule plage {on,start,end}.
            if (!empty($cell['on'])) {
                $s = preg_match('/^\d{2}:\d{2}$/', $cell['start'] ?? '') ? $cell['start'] : '08:00';
                $e = preg_match('/^\d{2}:\d{2}$/', $cell['end'] ?? '') ? $cell['end'] : '18:00';
                if ($s < $e) {
                    $out[$n] = [[$s, $e]];
                }
            }
        } elseif (is_array($cell)) {
            // Nouveau format : liste de plages [[s,e], …].
            foreach ($cell as $r) {
                if (is_array($r) && count($r) >= 2
                    && preg_match('/^\d{2}:\d{2}$/', (string) $r[0]) && preg_match('/^\d{2}:\d{2}$/', (string) $r[1])
                    && $r[0] < $r[1]) {
                    $out[$n][] = [(string) $r[0], (string) $r[1]];
                }
            }
        }
    }
    return $out;
}

/** Planning simple {on,start,end} dérivé des plages (compat agenda/aperçu). */
function idc_rdv_week(int $fiche_id): array
{
    $ranges = idc_rdv_ranges($fiche_id);
    $week   = [];
    foreach (array_keys(IDC_RDV_JOURS) as $i => $jour) {
        $r = $ranges[$i + 1];
        $week[$jour] = $r
            ? ['on' => true, 'start' => $r[0][0], 'end' => end($r)[1]]
            : ['on' => false, 'start' => '08:00', 'end' => '18:00'];
    }
    return $week;
}

/**
 * Absences riches : [ ['id'=>int,'start'=>'Y-m-d H:i','end'=>'Y-m-d H:i','motif'=>string], … ].
 * Compatible avec l'ancien format (tableau de dates 'Y-m-d' = journée entière).
 */
function idc_rdv_indispos_list(int $fiche_id): array
{
    $raw = json_decode((string) get_post_meta($fiche_id, '_idc_indispos', true), true);
    if (!is_array($raw)) {
        return [];
    }
    $out = [];
    foreach ($raw as $item) {
        if (is_string($item) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $item)) {
            $out[] = ['id' => count($out) + 1, 'start' => $item . ' 00:00', 'end' => $item . ' 23:59', 'motif' => ''];
        } elseif (is_array($item) && !empty($item['start']) && !empty($item['end'])) {
            $out[] = [
                'id'    => (int) ($item['id'] ?? count($out) + 1),
                'start' => (string) $item['start'],
                'end'   => (string) $item['end'],
                'motif' => (string) ($item['motif'] ?? ''),
            ];
        }
    }
    return $out;
}

/** Compat : dates 'Y-m-d' entièrement bloquées (une absence couvre toute la journée). */
function idc_rdv_indispos(int $fiche_id): array
{
    $dates = [];
    foreach (idc_rdv_indispos_list($fiche_id) as $a) {
        try {
            $s = new DateTimeImmutable($a['start']);
            $e = new DateTimeImmutable($a['end']);
        } catch (Exception $ex) {
            continue;
        }
        $cur = $s->setTime(0, 0);
        while ($cur <= $e) {
            if ($s <= $cur->setTime(0, 0) && $e >= $cur->setTime(23, 59)) {
                $dates[] = $cur->format('Y-m-d');
            }
            $cur = $cur->modify('+1 day');
        }
    }
    return array_values(array_unique($dates));
}

/** RDV actifs (proposés + confirmés) d'une fiche, indexés par 'Y-m-d H:i'. */
function idc_rdv_occupes(int $fiche_id): array
{
    $rdvs = get_posts([
        'post_type'      => 'rdv',
        'post_status'    => 'publish',
        'posts_per_page' => 200,
        'meta_query'     => [
            ['key' => '_idc_artisan_post_id', 'value' => $fiche_id],
            ['key' => '_idc_statut', 'value' => ['propose', 'confirme'], 'compare' => 'IN'],
        ],
    ]);
    $occupes = [];
    foreach ($rdvs as $rdv) {
        $occupes[(string) get_post_meta($rdv->ID, '_idc_date_rdv', true)] = true;
    }
    return $occupes;
}

/**
 * Générateur de créneaux réservables pour une liste de dates ('Y-m-d').
 * Règles : plages hebdo (multi-créneaux) − créneaux pris − délai de prévenance
 * − plafond/jour − absences datées − pause déjeuner − pause inter-RDV −
 * acceptation du jour même. Retour : ['Y-m-d' => ['H:i', …], …].
 */
function idc_rdv_generate_slots(int $fiche_id, array $dates): array
{
    $ranges   = idc_rdv_ranges($fiche_id);
    $absences = idc_rdv_indispos_list($fiche_id);
    $occupes  = idc_rdv_occupes($fiche_id);
    $duree    = max(15, (int) (get_post_meta($fiche_id, '_idc_duree_rdv_default_min', true) ?: 90));
    $pause    = max(0, (int) get_post_meta($fiche_id, '_idc_pause_entre_rdv_min', true));
    $delai_h  = max(0, (int) (get_post_meta($fiche_id, '_idc_delai_prevenance_h', true) ?: 24));
    $max_jour = max(0, (int) get_post_meta($fiche_id, '_idc_max_rdv_jour', true));
    $jour_meme = (int) get_post_meta($fiche_id, '_idc_accepter_jour_meme', true) === 1;
    $dejeuner  = (int) get_post_meta($fiche_id, '_idc_pause_dejeuner', true) === 1;

    $tz    = wp_timezone();
    $now   = new DateTimeImmutable('now', $tz);
    $limit = $now->modify('+' . $delai_h . ' hours');
    $today = $now->format('Y-m-d');
    $step  = $duree + $pause;

    // Absences en timestamps pour test de chevauchement rapide.
    $abs = [];
    foreach ($absences as $a) {
        try {
            $abs[] = [(new DateTimeImmutable($a['start'], $tz))->getTimestamp(), (new DateTimeImmutable($a['end'], $tz))->getTimestamp()];
        } catch (Exception $e) {
            continue;
        }
    }

    $slots = [];
    foreach ($dates as $date) {
        try {
            $day = new DateTimeImmutable($date, $tz);
        } catch (Exception $e) {
            continue;
        }
        if (!$jour_meme && $date === $today) {
            continue;
        }
        $dayRanges = $ranges[(int) $day->format('N')] ?? [];
        if (!$dayRanges) {
            continue;
        }
        if ($max_jour > 0) {
            $pris = count(array_filter(array_keys($occupes), static fn($k) => str_starts_with($k, $date)));
            if ($pris >= $max_jour) {
                continue;
            }
        }
        foreach ($dayRanges as [$s, $e]) {
            $cursor = new DateTimeImmutable($date . ' ' . $s, $tz);
            $fin    = new DateTimeImmutable($date . ' ' . $e, $tz);
            while ($cursor->modify('+' . $duree . ' minutes') <= $fin) {
                $slotEnd = $cursor->modify('+' . $duree . ' minutes');
                $key     = $cursor->format('Y-m-d H:i');
                $ok      = $cursor > $limit && empty($occupes[$key]);
                if ($ok && $dejeuner) {
                    $noon = new DateTimeImmutable($date . ' 12:00', $tz);
                    $two  = new DateTimeImmutable($date . ' 14:00', $tz);
                    if ($cursor < $two && $slotEnd > $noon) {
                        $ok = false;
                    }
                }
                if ($ok) {
                    $t0 = $cursor->getTimestamp();
                    $t1 = $slotEnd->getTimestamp();
                    foreach ($abs as [$as, $ae]) {
                        if ($t0 < $ae && $t1 > $as) {
                            $ok = false;
                            break;
                        }
                    }
                }
                if ($ok) {
                    $slots[$date][] = $cursor->format('H:i');
                }
                $cursor = $cursor->modify('+' . $step . ' minutes');
            }
        }
        if (isset($slots[$date])) {
            $slots[$date] = array_values(array_unique($slots[$date]));
            sort($slots[$date]);
        }
    }
    return $slots;
}

/** Créneaux réservables des N prochains jours (à partir d'aujourd'hui). */
function idc_rdv_slots(int $fiche_id, int $days = 14): array
{
    $tz    = wp_timezone();
    $now   = new DateTimeImmutable('now', $tz);
    $dates = [];
    for ($d = 0; $d < $days; $d++) {
        $dates[] = $now->modify('+' . $d . ' days')->format('Y-m-d');
    }
    return idc_rdv_generate_slots($fiche_id, $dates);
}

/** Créneaux réservables d'une semaine donnée (lundi → dimanche). */
function idc_rdv_slots_semaine(int $fiche_id, string $monday): array
{
    try {
        $start = new DateTimeImmutable($monday, wp_timezone());
    } catch (Exception $e) {
        return [];
    }
    $dates = [];
    for ($d = 0; $d < 7; $d++) {
        $dates[] = $start->modify('+' . $d . ' days')->format('Y-m-d');
    }
    return idc_rdv_generate_slots($fiche_id, $dates);
}

/** Demandes de RDV du client vers cet artisan sur les N derniers jours (anti-spam 3/7j). */
function idc_rdv_demandes_recentes(int $client_id, int $fiche_id, int $jours = 7): int
{
    return count(get_posts([
        'post_type'      => 'rdv',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'fields'         => 'ids',
        'date_query'     => [['after' => $jours . ' days ago']],
        'meta_query'     => [
            ['key' => '_idc_artisan_post_id', 'value' => $fiche_id],
            ['key' => '_idc_client_user_id', 'value' => $client_id],
        ],
    ]));
}

/* ── API créneaux d'une semaine (équivalent GET /api/creneaux/{id}) ─────── */
function idc_ajax_creneaux(): void
{
    $fiche_id = (int) ($_GET['fiche'] ?? 0);
    if (get_post_type($fiche_id) !== 'artisan' || get_post_status($fiche_id) !== 'publish') {
        wp_send_json(['success' => false, 'error' => 'Artisan introuvable'], 404);
    }
    $week_raw = sanitize_text_field(wp_unslash($_GET['week'] ?? ''));
    try {
        $monday = preg_match('/^\d{4}-\d{2}-\d{2}$/', $week_raw)
            ? (new DateTimeImmutable($week_raw, wp_timezone()))->modify('monday this week')
            : (new DateTimeImmutable('today', wp_timezone()))->modify('monday this week');
    } catch (Exception $e) {
        $monday = (new DateTimeImmutable('today', wp_timezone()))->modify('monday this week');
    }
    wp_send_json(['success' => true, 'slots' => (object) idc_rdv_slots_semaine($fiche_id, $monday->format('Y-m-d'))]);
}
add_action('wp_ajax_idc_creneaux', 'idc_ajax_creneaux');
add_action('wp_ajax_nopriv_idc_creneaux', 'idc_ajax_creneaux'); // créneaux publics, comme l'original

/* ── Client : réserver un créneau (AJAX JSON, page « Prendre rendez-vous ») ── */
add_action('wp_ajax_idc_rdv_creer', static function (): void {
    if (!isset($_POST['idc_rdv_nonce']) || !wp_verify_nonce($_POST['idc_rdv_nonce'], 'idc_rdv_create')) {
        wp_send_json(['success' => false, 'error' => 'Session expirée, rechargez la page.'], 403);
    }
    $user = wp_get_current_user();

    $fiche_id = (int) ($_POST['artisan'] ?? 0);
    $slot     = sanitize_text_field(wp_unslash($_POST['date_rdv'] ?? ''));

    if (get_post_type($fiche_id) !== 'artisan' || get_post_status($fiche_id) !== 'publish'
        || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $slot)) {
        wp_send_json(['success' => false, 'error' => 'Demande invalide.'], 400);
    }
    if (empty($_POST['consent_engagement'])) {
        wp_send_json(['success' => false, 'error' => 'Merci de cocher l\'engagement de présence.'], 400);
    }
    $adresse = sanitize_text_field(wp_unslash($_POST['adresse'] ?? ''));
    if ('' === $adresse) {
        wp_send_json(['success' => false, 'error' => 'L\'adresse du chantier est obligatoire.'], 400);
    }

    // Anti-spam : max 3 demandes / 7 jours / artisan (comme l'original).
    if (idc_rdv_demandes_recentes((int) $user->ID, $fiche_id) >= 3) {
        wp_send_json(['success' => false, 'error' => 'Vous avez déjà soumis 3 demandes à cet artisan ces 7 derniers jours.'], 429);
    }

    // Le créneau doit être réellement proposé (recalcul serveur sur SA semaine).
    [$slot_date, $slot_heure] = explode(' ', $slot);
    $monday = (new DateTimeImmutable($slot_date, wp_timezone()))->modify('monday this week')->format('Y-m-d');
    $slots  = idc_rdv_slots_semaine($fiche_id, $monday);
    if (!in_array($slot_heure, $slots[$slot_date] ?? [], true)) {
        wp_send_json(['success' => false, 'error' => 'Ce créneau vient d\'être réservé ou n\'est plus disponible — choisissez-en un autre.'], 409);
    }

    $duree   = max(15, (int) (get_post_meta($fiche_id, '_idc_duree_rdv_default_min', true) ?: 90));
    $post_id = wp_insert_post([
        'post_type'    => 'rdv',
        'post_status'  => 'publish',
        'post_title'   => 'RDV ' . $slot . ' — ' . get_the_title($fiche_id) . ' / ' . $user->display_name,
        'post_content' => sanitize_textarea_field(wp_unslash($_POST['description'] ?? '')),
    ], true);
    if (is_wp_error($post_id)) {
        wp_send_json(['success' => false, 'error' => 'Erreur interne, merci de réessayer.'], 500);
    }
    foreach ([
        'artisan_post_id' => (string) $fiche_id,
        'client_user_id'  => (string) $user->ID,
        'date_rdv'        => $slot,
        'duree_min'       => (string) $duree,
        'statut'          => 'propose',
        'adresse'         => $adresse,
        'code_postal'     => sanitize_text_field(wp_unslash($_POST['code_postal'] ?? '')),
        'ville'           => sanitize_text_field(wp_unslash($_POST['ville'] ?? '')),
    ] as $key => $value) {
        if ('' !== $value) {
            update_post_meta($post_id, '_idc_' . $key, $value);
        }
    }
    if (!empty($_POST['devis_id'])) {
        $devis_id = (int) $_POST['devis_id'];
        if (get_post_type($devis_id) === 'demande_devis' && (int) get_post_field('post_author', $devis_id) === (int) $user->ID) {
            update_post_meta($post_id, '_idc_devis_id', (string) $devis_id);
        }
    }

    // Photos du chantier (max 5, 10 Mo, JPG/PNG/WEBP — comme l'original).
    if (!empty($_FILES['photos']['name'][0])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $nb = min(5, count($_FILES['photos']['name']));
        for ($i = 0; $i < $nb; $i++) {
            if (!empty($_FILES['photos']['error'][$i]) || ($_FILES['photos']['size'][$i] ?? 0) > 10 * 1024 * 1024) {
                continue;
            }
            $_FILES['idc_rdv_photo'] = [
                'name'     => $_FILES['photos']['name'][$i],
                'type'     => $_FILES['photos']['type'][$i],
                'tmp_name' => $_FILES['photos']['tmp_name'][$i],
                'error'    => $_FILES['photos']['error'][$i],
                'size'     => $_FILES['photos']['size'][$i],
            ];
            $aid = media_handle_upload('idc_rdv_photo', $post_id, [], ['mimes' => ['jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp']]);
            if (!is_wp_error($aid) && !has_post_thumbnail($post_id)) {
                set_post_thumbnail($post_id, $aid);
            }
        }
        unset($_FILES['idc_rdv_photo']);
    }

    // Notifications : artisan (nouvelle demande de RDV) + client (récapitulatif).
    $artisan_user = get_userdata((int) get_post_meta($fiche_id, '_idc_user_id', true));
    if ($artisan_user) {
        idc_send_mail('rdv_nouveau_artisan', $artisan_user->user_email, [
            '{date}' => $slot, '{nom}' => $user->display_name, '{entreprise}' => get_the_title($fiche_id),
        ]);
        do_action('idc_rdv_created', (int) $artisan_user->ID, $slot, $user->display_name);
    }
    idc_send_mail('rdv_recap_client', $user->user_email, [
        '{date}' => $slot, '{nom}' => $user->display_name, '{entreprise}' => get_the_title($fiche_id),
    ]);

    wp_send_json(['success' => true, 'redirect' => add_query_arg('rdv', 'ok', home_url('/mes-rdv/'))]);
});

/* ── Artisan : enregistrer ses disponibilités ──────────────────────────── */
add_action('admin_post_idc_dispos_save', static function (): void {
    $back  = wp_get_referer() ?: home_url('/dashboard/artisan/disponibilites/');
    $back  = remove_query_arg('dispos', $back);
    $fiche = idc_current_artisan_fiche();

    if (!$fiche || !isset($_POST['idc_dispos_nonce']) || !wp_verify_nonce($_POST['idc_dispos_nonce'], 'idc_dispos_save')) {
        wp_safe_redirect(add_query_arg('dispos', 'erreur', $back));
        exit;
    }

    $week = [];
    foreach (array_keys(IDC_RDV_JOURS) as $jour) {
        $start = preg_match('/^\d{2}:\d{2}$/', $_POST['start_' . $jour] ?? '') ? $_POST['start_' . $jour] : '08:00';
        $end   = preg_match('/^\d{2}:\d{2}$/', $_POST['end_' . $jour] ?? '') ? $_POST['end_' . $jour] : '18:00';
        $week[$jour] = ['on' => !empty($_POST['on_' . $jour]), 'start' => $start, 'end' => $end];
    }
    update_post_meta($fiche->ID, '_idc_availability', wp_json_encode($week));

    $indispos = array_values(array_unique(array_filter(
        array_map('trim', explode(',', sanitize_text_field(wp_unslash($_POST['indispos'] ?? '')))),
        static fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)
    )));
    update_post_meta($fiche->ID, '_idc_indispos', wp_json_encode($indispos));

    update_post_meta($fiche->ID, '_idc_duree_rdv_default_min', (string) max(15, min(480, (int) ($_POST['duree'] ?? 90))));
    update_post_meta($fiche->ID, '_idc_delai_prevenance_h', (string) max(0, min(168, (int) ($_POST['delai'] ?? 24))));
    update_post_meta($fiche->ID, '_idc_max_rdv_jour', (string) max(0, min(20, (int) ($_POST['max_jour'] ?? 0))));
    update_post_meta($fiche->ID, '_idc_calendar_description', sanitize_text_field(wp_unslash($_POST['calendar_description'] ?? '')));

    wp_safe_redirect(add_query_arg('dispos', 'ok', $back));
    exit;
});

/* ── Client : réserver un créneau ──────────────────────────────────────── */
add_action('admin_post_idc_rdv_create', static function (): void {
    $back = wp_get_referer() ?: home_url('/mes-rdv/');
    $back = remove_query_arg('rdv', $back);

    $fail = static function (string $code) use ($back): void {
        wp_safe_redirect(add_query_arg('rdv', $code, $back));
        exit;
    };

    if (!is_user_logged_in() || !isset($_POST['idc_rdv_nonce']) || !wp_verify_nonce($_POST['idc_rdv_nonce'], 'idc_rdv_create')) {
        $fail('erreur');
    }
    $user = wp_get_current_user();

    $fiche_id = (int) ($_POST['artisan'] ?? 0);
    $slot     = sanitize_text_field(wp_unslash($_POST['slot'] ?? '')); // 'Y-m-d H:i'
    $motif    = sanitize_textarea_field(wp_unslash($_POST['motif'] ?? ''));

    if (get_post_type($fiche_id) !== 'artisan' || get_post_status($fiche_id) !== 'publish'
        || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $slot)) {
        $fail('erreur');
    }

    // Le créneau doit être réellement proposé (recalcul serveur : passé, délai,
    // indispos, double réservation et plafond sont re-vérifiés ici).
    [$slot_date, $slot_heure] = explode(' ', $slot);
    $slots = idc_rdv_slots($fiche_id);
    if (!in_array($slot_heure, $slots[$slot_date] ?? [], true)) {
        $fail('creneau');
    }

    $duree   = max(15, (int) (get_post_meta($fiche_id, '_idc_duree_rdv_default_min', true) ?: 90));
    $post_id = wp_insert_post([
        'post_type'    => 'rdv',
        'post_status'  => 'publish',
        'post_title'   => 'RDV ' . $slot . ' — ' . get_the_title($fiche_id) . ' / ' . $user->display_name,
        'post_content' => $motif,
    ], true);
    if (is_wp_error($post_id)) {
        $fail('erreur');
    }
    foreach ([
        'artisan_post_id' => (string) $fiche_id,
        'client_user_id'  => (string) $user->ID,
        'date_rdv'        => $slot,
        'duree_min'       => (string) $duree,
        'statut'          => 'propose',
    ] as $key => $value) {
        update_post_meta($post_id, '_idc_' . $key, $value);
    }

    // Notifications : artisan (nouvelle demande de RDV) + client (récapitulatif).
    $artisan_user = get_userdata((int) get_post_meta($fiche_id, '_idc_user_id', true));
    if ($artisan_user) {
        idc_send_mail('rdv_nouveau_artisan', $artisan_user->user_email, [
            '{date}' => $slot, '{nom}' => $user->display_name, '{entreprise}' => get_the_title($fiche_id),
        ]);
        do_action('idc_rdv_created', (int) $artisan_user->ID, $slot, $user->display_name);
    }
    idc_send_mail('rdv_recap_client', $user->user_email, [
        '{date}' => $slot, '{nom}' => $user->display_name, '{entreprise}' => get_the_title($fiche_id),
    ]);

    $fail('ok');
});

/* ── Changement de statut (artisan : confirmer/annuler/terminer ; client : annuler) ── */
add_action('admin_post_idc_rdv_status', static function (): void {
    $back = wp_get_referer() ?: home_url('/');
    $back = remove_query_arg('rdv', $back);

    $fail = static function (string $code) use ($back): void {
        wp_safe_redirect(add_query_arg('rdv', $code, $back));
        exit;
    };

    if (!is_user_logged_in() || !isset($_POST['idc_rdv_nonce']) || !wp_verify_nonce($_POST['idc_rdv_nonce'], 'idc_rdv_status')) {
        $fail('erreur');
    }
    $user    = wp_get_current_user();
    $rdv_id  = (int) ($_POST['rdv_id'] ?? 0);
    $statut  = $_POST['statut'] ?? '';
    if (get_post_type($rdv_id) !== 'rdv' || !in_array($statut, ['confirme', 'annule', 'termine'], true)) {
        $fail('erreur');
    }

    $fiche_id  = (int) get_post_meta($rdv_id, '_idc_artisan_post_id', true);
    $client_id = (int) get_post_meta($rdv_id, '_idc_client_user_id', true);
    $fiche     = idc_current_artisan_fiche();
    $is_owner_artisan = $fiche && $fiche->ID === $fiche_id;
    $is_owner_client  = $client_id === (int) $user->ID;

    // Le client ne peut qu'annuler SON RDV ; l'artisan gère les RDV de SA fiche.
    if (!($is_owner_artisan || ($is_owner_client && $statut === 'annule') || current_user_can('manage_options'))) {
        $fail('erreur');
    }

    update_post_meta($rdv_id, '_idc_statut', $statut);

    $date = (string) get_post_meta($rdv_id, '_idc_date_rdv', true);
    $vars = ['{date}' => $date, '{entreprise}' => get_the_title($fiche_id)];
    if ($statut === 'confirme' && ($client = get_userdata($client_id))) {
        idc_send_mail('rdv_confirme_client', $client->user_email, $vars + ['{nom}' => $client->display_name]);
        do_action('idc_rdv_confirmed', (int) $client->ID, $date, get_the_title($fiche_id));
    } elseif ($statut === 'annule') {
        // Prévenir l'autre partie.
        if ($is_owner_artisan && ($client = get_userdata($client_id))) {
            idc_send_mail('rdv_annule', $client->user_email, $vars + ['{nom}' => $client->display_name]);
        } elseif ($is_owner_client && ($artisan_user = get_userdata((int) get_post_meta($fiche_id, '_idc_user_id', true)))) {
            idc_send_mail('rdv_annule', $artisan_user->user_email, $vars + ['{nom}' => $artisan_user->display_name]);
        }
    }

    $fail('statut_ok');
});
