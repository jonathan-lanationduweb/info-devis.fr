<?php
/**
 * Info Devis Core — workflow des avis clients.
 *
 * Statuts (meta _idc_status) : pending / approved / refused / reported / hidden.
 * Un client connecté ne peut déposer qu'UN avis par artisan.
 * La note moyenne et le nombre d'avis de la fiche artisan sont recalculés
 * automatiquement à chaque changement.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Recalcule note moyenne, nombre d'avis et répartition par étoiles d'un artisan.
 */
function idc_recalc_artisan_rating(int $artisan_post_id): void
{
    if (!$artisan_post_id || get_post_type($artisan_post_id) !== 'artisan') {
        return;
    }
    $avis = get_posts([
        'post_type'      => 'avis',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => [
            ['key' => '_idc_artisan_post_id', 'value' => $artisan_post_id],
            ['key' => '_idc_status', 'value' => 'approved'],
        ],
    ]);

    $count        = count($avis);
    $sum          = 0;
    $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    foreach ($avis as $avis_id) {
        $note = max(1, min(5, (int) get_post_meta($avis_id, '_idc_rating', true)));
        $sum += $note;
        $distribution[$note]++;
    }

    update_post_meta($artisan_post_id, '_idc_rating_count', (string) $count);
    update_post_meta($artisan_post_id, '_idc_rating_avg', $count ? number_format($sum / $count, 2, '.', '') : '0');
    update_post_meta($artisan_post_id, '_idc_rating_distribution', wp_json_encode($distribution));
}

/** Recalcul à chaque enregistrement d'un avis (après la sauvegarde des meta). */
add_action('save_post_avis', static function (int $post_id): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    $artisan_id = (int) get_post_meta($post_id, '_idc_artisan_post_id', true);
    if ($artisan_id) {
        idc_recalc_artisan_rating($artisan_id);
        // Notifie l'artisan lors de la publication.
        if (get_post_meta($post_id, '_idc_status', true) === 'approved'
            && !get_post_meta($post_id, '_idc_artisan_notified', true)) {
            $user_id = (int) get_post_meta($artisan_id, '_idc_user_id', true);
            $user    = $user_id ? get_userdata($user_id) : false;
            if ($user) {
                idc_send_mail('avis_publie_artisan', $user->user_email, [
                    '{entreprise}' => get_the_title($artisan_id),
                    '{note}'       => (string) get_post_meta($post_id, '_idc_rating', true),
                ]);
                do_action('idc_avis_created', $user_id, (int) get_post_meta($post_id, '_idc_rating', true));
                update_post_meta($post_id, '_idc_artisan_notified', '1');
            }
        }
    }
}, 30);

/** L'avis existe-t-il déjà pour ce couple client / artisan ? */
function idc_client_has_review(int $client_user_id, int $artisan_post_id): bool
{
    $existing = get_posts([
        'post_type'      => 'avis',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            ['key' => '_idc_client_user_id', 'value' => $client_user_id],
            ['key' => '_idc_artisan_post_id', 'value' => $artisan_post_id],
        ],
    ]);
    return !empty($existing);
}

/**
 * Formulaire de dépôt d'avis (affiché dans l'espace client).
 */
function idc_render_avis_form(): string
{
    if (!is_user_logged_in()) {
        return '';
    }
    $user = wp_get_current_user();

    $html = '';
    if (isset($_GET['avis'])) {
        if ($_GET['avis'] === 'ok') {
            $html .= '<div class="idc-notice idc-notice--success">✅ Merci ! Votre avis a été envoyé et sera visible après validation par notre équipe.</div>';
        } else {
            $messages = [
                'doublon' => 'Vous avez déjà déposé un avis pour cet artisan.',
                'champs'  => 'Merci de choisir un artisan, une note et d’écrire un commentaire.',
                'erreur'  => 'Une erreur est survenue, merci de réessayer.',
            ];
            $html .= '<div class="idc-notice idc-notice--error">' . esc_html($messages[$_GET['avis']] ?? $messages['erreur']) . '</div>';
        }
    }

    $artisans = get_posts([
        'post_type'      => 'artisan',
        'post_status'    => 'publish',
        'posts_per_page' => 200,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);
    if (!$artisans) {
        return $html;
    }

    $html .= '<h3>Laisser un avis</h3>';
    $html .= '<form class="idc-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="max-width:560px;">';
    $html .= '<input type="hidden" name="action" value="idc_submit_avis" />';
    $html .= wp_nonce_field('idc_avis_form', 'idc_avis_nonce_front', true, false);

    $html .= '<p><label for="idc-avis-artisan">Artisan concerné *</label><select id="idc-avis-artisan" name="idc_artisan" required><option value="">— Choisir —</option>';
    foreach ($artisans as $artisan) {
        $html .= '<option value="' . (int) $artisan->ID . '">' . esc_html($artisan->post_title) . '</option>';
    }
    $html .= '</select></p>';

    $html .= '<p><label for="idc-avis-note">Votre note *</label><select id="idc-avis-note" name="idc_note" required>';
    foreach ([5 => '★★★★★ Excellent', 4 => '★★★★ Très bien', 3 => '★★★ Bien', 2 => '★★ Moyen', 1 => '★ Décevant'] as $value => $label) {
        $html .= '<option value="' . $value . '">' . esc_html($label) . '</option>';
    }
    $html .= '</select></p>';

    $html .= '<p><label for="idc-avis-comment">Votre commentaire *</label><textarea id="idc-avis-comment" name="idc_comment" rows="4" required placeholder="Décrivez votre expérience avec cet artisan…"></textarea></p>';
    $html .= '<p><button type="submit" class="idc-btn idc-btn--brand">Envoyer mon avis</button></p>';
    $html .= '<p style="font-size:0.85rem;color:#6b7280;">Votre avis sera publié après validation. Nom affiché : ' . esc_html($user->display_name) . '.</p>';
    $html .= '</form>';

    return $html;
}

/** Traitement du dépôt d'avis (client connecté uniquement). */
add_action('admin_post_idc_submit_avis', static function (): void {
    $back = wp_get_referer() ?: home_url('/espace-membre/');
    $back = remove_query_arg('avis', $back);

    $fail = static function (string $code) use ($back): void {
        wp_safe_redirect(add_query_arg('avis', $code, $back));
        exit;
    };

    if (!is_user_logged_in()
        || !isset($_POST['idc_avis_nonce_front'])
        || !wp_verify_nonce($_POST['idc_avis_nonce_front'], 'idc_avis_form')) {
        $fail('erreur');
    }

    $user       = wp_get_current_user();
    $artisan_id = (int) ($_POST['idc_artisan'] ?? 0);
    $note       = max(1, min(5, (int) ($_POST['idc_note'] ?? 0)));
    $comment    = sanitize_textarea_field(wp_unslash($_POST['idc_comment'] ?? ''));

    if (!$artisan_id || get_post_type($artisan_id) !== 'artisan' || !$comment || !$note) {
        $fail('champs');
    }
    if (idc_client_has_review($user->ID, $artisan_id)) {
        $fail('doublon');
    }

    $post_id = wp_insert_post([
        'post_type'    => 'avis',
        'post_status'  => 'publish',
        'post_title'   => 'Avis de ' . $user->display_name . ' — ' . get_the_title($artisan_id),
        'post_content' => $comment,
    ], true);
    if (is_wp_error($post_id)) {
        $fail('erreur');
    }

    update_post_meta($post_id, '_idc_artisan_post_id', (string) $artisan_id);
    update_post_meta($post_id, '_idc_client_user_id', (string) $user->ID);
    update_post_meta($post_id, '_idc_rating', (string) $note);
    update_post_meta($post_id, '_idc_status', 'pending');
    update_post_meta($post_id, '_idc_approved', '0');
    update_post_meta($post_id, '_idc_verified', '0');

    idc_send_mail('avis_recu_admin', get_option('admin_email'), [
        '{entreprise}' => get_the_title($artisan_id),
        '{note}'       => (string) $note,
        '{nom}'        => $user->display_name,
        '{lien_admin}' => admin_url('post.php?post=' . $post_id . '&action=edit'),
    ]);

    wp_safe_redirect(add_query_arg('avis', 'ok', $back));
    exit;
});
