<?php
/**
 * Info Devis Core — actions front de l'espace artisan :
 * réponse aux opportunités (leads), réponse aux avis, ajout de réalisation,
 * mise à jour de la fiche. Chaque action vérifie : connexion, rôle,
 * nonce et PROPRIÉTÉ (l'artisan n'agit que sur SA fiche / SES avis).
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Fiche artisan du compte courant (ou null). */
function idc_current_artisan_fiche(): ?WP_Post
{
    if (!is_user_logged_in()) {
        return null;
    }
    $user = wp_get_current_user();
    if (!in_array('artisan', (array) $user->roles, true) && !in_array('administrator', (array) $user->roles, true)) {
        return null;
    }
    $fiche = get_posts([
        'post_type'   => 'artisan',
        'post_status' => 'any',
        'numberposts' => 1,
        'meta_key'    => '_idc_user_id',
        'meta_value'  => $user->ID,
    ]);
    return $fiche ? $fiche[0] : null;
}

/* ── Répondre à une opportunité (accepter / refuser, JSON) ─────────────── */
add_action('admin_post_idc_lead_respond', static function (): void {
    $fiche = idc_current_artisan_fiche();
    if (!$fiche
        || !isset($_POST['idc_lead_nonce'])
        || !wp_verify_nonce($_POST['idc_lead_nonce'], 'idc_lead_respond')) {
        wp_send_json(['success' => false, 'error' => 'Session expirée, rechargez la page.']);
    }

    $demande_id = (int) ($_POST['lead_id'] ?? 0);
    $status     = $_POST['status'] ?? '';
    if (!in_array($status, ['accepted', 'refused'], true)
        || get_post_type($demande_id) !== 'demande_devis') {
        wp_send_json(['success' => false, 'error' => 'Demande invalide.']);
    }

    // La demande doit correspondre à l'artisan (matching) ou avoir déjà un statut de sa part.
    $key     = '_idc_lead_status_' . $fiche->ID;
    $matched = in_array($fiche->ID, idc_match_artisans($demande_id), true)
        || get_post_meta($demande_id, $key, true) !== '';
    if (!$matched) {
        wp_send_json(['success' => false, 'error' => 'Cette demande ne vous est pas adressée.']);
    }

    update_post_meta($demande_id, $key, $status);

    if ($status === 'accepted') {
        // Première acceptation : la demande passe « acceptée » et le client est prévenu.
        if (in_array(get_post_meta($demande_id, '_idc_status', true), ['pending', 'sent'], true)) {
            update_post_meta($demande_id, '_idc_status', 'accepted');
        }
        $client_email = (string) get_post_meta($demande_id, '_idc_contact_email', true);
        if ($client_email) {
            idc_send_mail('devis_client_accepte', $client_email, [
                '{reference}'  => (string) get_post_meta($demande_id, '_idc_reference', true),
                '{entreprise}' => get_the_title($fiche),
                '{nom}'        => (string) get_post_meta($demande_id, '_idc_contact_name', true),
            ]);
        }
    }

    wp_send_json(['success' => true]);
});

/* ── Répondre à un avis (uniquement un avis de SA fiche) ───────────────── */
add_action('admin_post_idc_avis_reply', static function (): void {
    $back  = wp_get_referer() ?: home_url('/dashboard/artisan/avis/');
    $fiche = idc_current_artisan_fiche();

    if (!$fiche
        || !isset($_POST['idc_reply_nonce'])
        || !wp_verify_nonce($_POST['idc_reply_nonce'], 'idc_avis_reply')) {
        wp_safe_redirect(add_query_arg('reponse', 'erreur', $back));
        exit;
    }

    $avis_id = (int) ($_POST['avis_id'] ?? 0);
    $reply   = sanitize_textarea_field(wp_unslash($_POST['reply'] ?? ''));

    if (get_post_type($avis_id) !== 'avis'
        || (int) get_post_meta($avis_id, '_idc_artisan_post_id', true) !== $fiche->ID
        || $reply === '') {
        wp_safe_redirect(add_query_arg('reponse', 'erreur', $back));
        exit;
    }

    update_post_meta($avis_id, '_idc_artisan_reply', $reply);
    update_post_meta($avis_id, '_idc_artisan_reply_at', current_time('mysql'));

    wp_safe_redirect(add_query_arg('reponse', 'ok', $back));
    exit;
});

/* ── Ajouter une réalisation (statut pending, validation admin) ────────── */
add_action('admin_post_idc_artisan_projet', static function (): void {
    $back  = wp_get_referer() ?: home_url('/dashboard/artisan/projets/');
    $back  = remove_query_arg('projet', $back);
    $fiche = idc_current_artisan_fiche();

    $fail = static function (string $code) use ($back): void {
        wp_safe_redirect(add_query_arg('projet', $code, $back));
        exit;
    };

    if (!$fiche
        || !isset($_POST['idc_projet_nonce'])
        || !wp_verify_nonce($_POST['idc_projet_nonce'], 'idc_artisan_projet')) {
        $fail('erreur');
    }

    $titre = sanitize_text_field(wp_unslash($_POST['titre'] ?? ''));
    $desc  = sanitize_textarea_field(wp_unslash($_POST['description'] ?? ''));
    $ville = sanitize_text_field(wp_unslash($_POST['ville'] ?? ''));
    if ($titre === '' || mb_strlen($desc) < 10) {
        $fail('champs');
    }

    $post_id = wp_insert_post([
        'post_type'    => 'realisation',
        'post_status'  => 'pending', // publication après validation admin (comme l'original)
        'post_title'   => $titre,
        'post_content' => $desc,
    ], true);
    if (is_wp_error($post_id)) {
        $fail('erreur');
    }
    update_post_meta($post_id, '_idc_artisan_post_id', (string) $fiche->ID);
    if ($ville) {
        update_post_meta($post_id, '_idc_ville', $ville);
    }
    $slug = sanitize_title($_POST['metier'] ?? '');
    if ($slug && ($term = get_term_by('slug', $slug, 'metier'))) {
        wp_set_object_terms($post_id, [(int) $term->term_id], 'metier');
    }

    $fail('ok');
});

/* ── Mettre à jour SA fiche artisan ─────────────────────────────────────── */
add_action('admin_post_idc_artisan_fiche', static function (): void {
    $back  = wp_get_referer() ?: home_url('/dashboard/artisan/profile/');
    $back  = remove_query_arg('fiche', $back);
    $fiche = idc_current_artisan_fiche();

    $fail = static function (string $code) use ($back): void {
        wp_safe_redirect(add_query_arg('fiche', $code, $back));
        exit;
    };

    if (!$fiche
        || !isset($_POST['idc_fiche_nonce'])
        || !wp_verify_nonce($_POST['idc_fiche_nonce'], 'idc_artisan_fiche')) {
        $fail('erreur');
    }

    $cp = preg_replace('/\D/', '', (string) ($_POST['code_postal'] ?? ''));
    if ($cp !== '' && strlen($cp) !== 5) {
        $fail('champs');
    }

    wp_update_post([
        'ID'           => $fiche->ID,
        'post_content' => sanitize_textarea_field(wp_unslash($_POST['description'] ?? $fiche->post_content)),
    ]);
    foreach ([
        'phone'       => sanitize_text_field(wp_unslash($_POST['phone'] ?? '')),
        'ville'       => sanitize_text_field(wp_unslash($_POST['ville'] ?? '')),
        'code_postal' => $cp,
        'radius_km'   => (string) max(0, min(200, (int) ($_POST['radius_km'] ?? 30))),
        'is_available' => empty($_POST['is_available']) ? '0' : '1',
    ] as $meta_key => $value) {
        update_post_meta($fiche->ID, '_idc_' . $meta_key, $value);
    }

    $term_ids = [];
    foreach (array_map('sanitize_title', (array) ($_POST['metiers'] ?? [])) as $slug) {
        if ($term = get_term_by('slug', $slug, 'metier')) {
            $term_ids[] = (int) $term->term_id;
        }
    }
    if ($term_ids) {
        wp_set_object_terms($fiche->ID, $term_ids, 'metier');
    }

    $fail('ok');
});
