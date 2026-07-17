<?php
/**
 * Info Devis Core — mise à jour du profil client (coordonnées + mot de passe).
 * Contrôles : connexion, nonce, propriété du compte (l'utilisateur ne modifie
 * que SES données), vérification du mot de passe actuel avant changement.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_post_idc_client_profile', static function (): void {
    $back = wp_get_referer() ?: home_url('/dashboard/client/profile/');
    $back = remove_query_arg('profil', $back);

    $fail = static function (string $code) use ($back): void {
        wp_safe_redirect(add_query_arg('profil', $code, $back));
        exit;
    };

    if (!is_user_logged_in()
        || !isset($_POST['idc_profile_nonce'])
        || !wp_verify_nonce($_POST['idc_profile_nonce'], 'idc_client_profile')) {
        $fail('erreur');
    }

    $user    = wp_get_current_user();
    $section = sanitize_key($_POST['idc_section'] ?? '');

    if ($section === 'coordonnees') {
        $display = sanitize_text_field(wp_unslash($_POST['display_name'] ?? ''));
        if ($display === '') {
            $fail('champs');
        }
        $cp = preg_replace('/\D/', '', (string) ($_POST['code_postal'] ?? ''));
        if ($cp !== '' && strlen($cp) !== 5) {
            $fail('champs');
        }
        wp_update_user(['ID' => $user->ID, 'display_name' => $display]);
        update_user_meta($user->ID, '_idc_phone', sanitize_text_field(wp_unslash($_POST['phone'] ?? '')));
        update_user_meta($user->ID, '_idc_ville', sanitize_text_field(wp_unslash($_POST['ville'] ?? '')));
        update_user_meta($user->ID, '_idc_code_postal', $cp);
    } elseif ($section === 'password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        if (!wp_check_password($current, $user->user_pass, $user->ID)) {
            $fail('mdp_actuel');
        }
        if (strlen($new) < 8) {
            $fail('mdp');
        }
        wp_set_password($new, $user->ID);
        // wp_set_password déconnecte : reconnexion immédiate pour ne pas casser le parcours.
        wp_set_auth_cookie($user->ID);
    } else {
        $fail('erreur');
    }

    wp_safe_redirect(add_query_arg('profil', 'ok', $back));
    exit;
});
