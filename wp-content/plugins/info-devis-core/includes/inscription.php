<?php
/**
 * Info Devis Core — inscription artisan côté public.
 *
 * Crée un utilisateur (rôle artisan) + une fiche artisan en statut
 * « pending » (en attente de validation admin), puis notifie l'admin
 * (demande historique du TODO : « recevoir un mail pour valider un
 * artisan en attente »).
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('idc_inscription_artisan', static function (): string {
    if (is_user_logged_in()) {
        return '<p>Vous êtes déjà connecté. <a href="' . esc_url(home_url('/espace-membre/')) . '">Accéder à votre espace</a>.</p>';
    }

    $html = '';
    if (isset($_GET['inscription'])) {
        if ($_GET['inscription'] === 'ok') {
            $html .= '<div class="idc-notice idc-notice--success">✅ Votre compte artisan a été créé ! Votre fiche est en cours de vérification — vous recevrez un email dès sa validation. Vous pouvez dès maintenant vous connecter à votre espace.</div>';
        } else {
            $messages = [
                'champs'   => 'Merci de remplir tous les champs obligatoires.',
                'email'    => 'Cette adresse e-mail est invalide ou déjà utilisée.',
                'mdp'      => 'Le mot de passe doit contenir au moins 8 caractères.',
                'consent'  => 'Vous devez accepter les conditions pour vous inscrire.',
                'erreur'   => 'Une erreur est survenue, merci de réessayer.',
            ];
            $html .= '<div class="idc-notice idc-notice--error">' . esc_html($messages[$_GET['inscription']] ?? $messages['erreur']) . '</div>';
        }
    }

    $terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0]);

    $html .= '<form class="idc-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    $html .= '<input type="hidden" name="action" value="idc_register_artisan" />';
    $html .= wp_nonce_field('idc_inscription_artisan', 'idc_inscription_nonce', true, false);
    $html .= '<p class="idc-hp-field" aria-hidden="true"><label>Ne pas remplir<input type="text" name="idc_website" tabindex="-1" autocomplete="off" /></label></p>';

    $html .= '<h3 style="margin-top:0;">Votre entreprise</h3>';
    $html .= '<div class="idc-form__row">';
    $html .= '<p><label for="idc-i-entreprise">Raison sociale *</label><input id="idc-i-entreprise" type="text" name="idc_entreprise" required /></p>';
    $html .= '<p><label for="idc-i-siret">SIRET (14 chiffres)</label><input id="idc-i-siret" type="text" name="idc_siret" pattern="[0-9]{14}" maxlength="14" placeholder="Vérifié automatiquement" /></p>';
    $html .= '</div>';
    $html .= '<div class="idc-form__row">';
    $html .= '<p><label for="idc-i-ville">Ville *</label><input id="idc-i-ville" type="text" name="idc_ville" required /></p>';
    $html .= '<p><label for="idc-i-cp">Code postal *</label><input id="idc-i-cp" type="text" name="idc_code_postal" pattern="[0-9]{5}" maxlength="5" required /></p>';
    $html .= '</div>';

    $html .= '<p><label>Vos métiers *</label>';
    foreach ((array) $terms as $term) {
        $html .= '<label style="display:inline-block;font-weight:400;margin:2px 14px 2px 0;"><input type="checkbox" name="idc_metiers[]" value="' . esc_attr($term->slug) . '" /> ' . esc_html($term->name) . '</label>';
    }
    $html .= '</p>';

    $html .= '<h3>Vos identifiants</h3>';
    $html .= '<div class="idc-form__row">';
    $html .= '<p><label for="idc-i-nom">Nom et prénom *</label><input id="idc-i-nom" type="text" name="idc_nom" required /></p>';
    $html .= '<p><label for="idc-i-tel">Téléphone *</label><input id="idc-i-tel" type="tel" name="idc_phone" required /></p>';
    $html .= '</div>';
    $html .= '<div class="idc-form__row">';
    $html .= '<p><label for="idc-i-email">E-mail *</label><input id="idc-i-email" type="email" name="idc_email" required /></p>';
    $html .= '<p><label for="idc-i-mdp">Mot de passe * (8 caractères min.)</label><input id="idc-i-mdp" type="password" name="idc_password" minlength="8" required /></p>';
    $html .= '</div>';

    $html .= '<p><label style="font-weight:400;"><input type="checkbox" name="idc_consent" value="1" required /> J’accepte les <a href="' . esc_url(home_url('/cgv/')) . '">conditions générales</a> et la <a href="' . esc_url(home_url('/confidentialite/')) . '">politique de confidentialité</a>. *</label></p>';

    $html .= '<p><button type="submit" class="idc-btn">Créer mon compte artisan</button></p>';
    $html .= '</form>';

    return $html;
});

add_action('admin_post_nopriv_idc_register_artisan', 'idc_handle_register_artisan');
add_action('admin_post_idc_register_artisan', 'idc_handle_register_artisan');

/**
 * Inscription unifiée (page /inscription/ fidèle à auth/register.php) :
 * rôle client ou artisan selon le champ `role`. Connexion automatique
 * après création, puis redirection vers l'espace correspondant.
 */
add_action('admin_post_nopriv_idc_register', 'idc_handle_register');
add_action('admin_post_idc_register', 'idc_handle_register');

function idc_handle_register(): void
{
    $role  = ($_POST['role'] ?? '') === 'artisan' ? 'artisan' : 'client';
    $back  = wp_get_referer() ?: home_url('/inscription/' . ($role === 'artisan' ? '?type=artisan' : ''));
    $back  = remove_query_arg('inscription', $back);

    $fail = static function (string $code) use ($back): void {
        wp_safe_redirect(add_query_arg('inscription', $code, $back));
        exit;
    };

    if (!isset($_POST['idc_register_nonce'])
        || !wp_verify_nonce($_POST['idc_register_nonce'], 'idc_register')
        || !empty($_POST['idc_website'])) {
        $fail('erreur');
    }

    $first    = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
    $last     = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));
    $email    = sanitize_email($_POST['email'] ?? '');
    $phone    = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!$first || !$last) {
        $fail('champs');
    }
    if (!$email || !is_email($email) || email_exists($email)) {
        $fail('email');
    }
    if (strlen($password) < 8) {
        $fail('mdp');
    }
    if (empty($_POST['consent_privacy'])) {
        $fail('consent');
    }

    $entreprise = '';
    $siret      = '';
    $ville      = '';
    $cp         = '';
    if ($role === 'artisan') {
        $entreprise = sanitize_text_field(wp_unslash($_POST['company_name'] ?? ''));
        $siret      = preg_replace('/\D/', '', (string) ($_POST['siret'] ?? ''));
        $ville      = sanitize_text_field(wp_unslash($_POST['ville'] ?? ''));
        $cp         = preg_replace('/\D/', '', (string) ($_POST['code_postal'] ?? ''));
        if (!$entreprise || !$ville) {
            $fail('champs');
        }
        if ($cp !== '' && strlen($cp) !== 5) {
            $fail('champs');
        }
    }

    $login = sanitize_user(strstr($email, '@', true), true) ?: $role;
    $base  = $login;
    $i     = 1;
    while (username_exists($login)) {
        $login = $base . $i++;
    }
    $user_id = wp_insert_user([
        'user_login'   => $login,
        'user_email'   => $email,
        'user_pass'    => $password,
        'first_name'   => $first,
        'last_name'    => $last,
        'display_name' => trim($first . ' ' . $last),
        'role'         => $role,
    ]);
    if (is_wp_error($user_id)) {
        $fail('erreur');
    }
    if ($phone) {
        update_user_meta($user_id, '_idc_phone', $phone);
    }
    update_user_meta($user_id, '_idc_consent_at', current_time('mysql'));
    update_user_meta($user_id, '_idc_consent_marketing', empty($_POST['consent_marketing']) ? '0' : '1');

    if ($role === 'artisan') {
        $post_id = wp_insert_post([
            'post_type'   => 'artisan',
            'post_status' => 'pending',
            'post_title'  => $entreprise,
        ], true);
        if (!is_wp_error($post_id)) {
            foreach ([
                'user_id' => $user_id, 'company_name' => $entreprise, 'siret' => $siret,
                'phone' => $phone, 'ville' => $ville, 'code_postal' => $cp,
                'plan' => 'gratuit', 'badge_level' => 'referenced', 'verification_status' => 'pending',
            ] as $key => $value) {
                if ($value !== '' && $value !== null) {
                    update_post_meta($post_id, '_idc_' . $key, (string) $value);
                }
            }
            idc_send_mail('inscription_artisan_admin', get_option('admin_email'), [
                '{entreprise}' => $entreprise, '{nom}' => trim($first . ' ' . $last),
                '{email}' => $email, '{ville}' => $ville,
                '{lien_admin}' => admin_url('post.php?post=' . $post_id . '&action=edit'),
            ]);
        }
        idc_send_mail('inscription_artisan_bienvenue', $email, [
            '{nom}' => $first, '{entreprise}' => $entreprise,
        ]);
    }

    // Connexion automatique puis redirection vers le bon espace.
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);
    wp_safe_redirect($role === 'artisan' ? home_url('/dashboard/artisan/') : home_url('/dashboard/client/'));
    exit;
}

function idc_handle_register_artisan(): void
{
    $back = wp_get_referer() ?: home_url('/inscription/');
    $back = remove_query_arg('inscription', $back);

    $fail = static function (string $code) use ($back): void {
        wp_safe_redirect(add_query_arg('inscription', $code, $back));
        exit;
    };

    if (!isset($_POST['idc_inscription_nonce'])
        || !wp_verify_nonce($_POST['idc_inscription_nonce'], 'idc_inscription_artisan')
        || !empty($_POST['idc_website'])) {
        $fail('erreur');
    }

    $entreprise = sanitize_text_field(wp_unslash($_POST['idc_entreprise'] ?? ''));
    $siret      = preg_replace('/\D/', '', (string) ($_POST['idc_siret'] ?? ''));
    $ville      = sanitize_text_field(wp_unslash($_POST['idc_ville'] ?? ''));
    $cp         = preg_replace('/\D/', '', (string) ($_POST['idc_code_postal'] ?? ''));
    $metiers    = array_map('sanitize_title', (array) ($_POST['idc_metiers'] ?? []));
    $nom        = sanitize_text_field(wp_unslash($_POST['idc_nom'] ?? ''));
    $phone      = sanitize_text_field(wp_unslash($_POST['idc_phone'] ?? ''));
    $email      = sanitize_email($_POST['idc_email'] ?? '');
    $password   = (string) ($_POST['idc_password'] ?? '');

    if (!$entreprise || !$ville || strlen($cp) !== 5 || !$metiers || !$nom || !$phone) {
        $fail('champs');
    }
    if (!$email || !is_email($email) || email_exists($email)) {
        $fail('email');
    }
    if (strlen($password) < 8) {
        $fail('mdp');
    }
    if (empty($_POST['idc_consent'])) {
        $fail('consent');
    }

    // Compte utilisateur.
    $login = sanitize_user(strstr($email, '@', true), true) ?: 'artisan';
    $base  = $login;
    $i     = 1;
    while (username_exists($login)) {
        $login = $base . $i++;
    }
    $user_id = wp_insert_user([
        'user_login'   => $login,
        'user_email'   => $email,
        'user_pass'    => $password,
        'display_name' => $nom,
        'role'         => 'artisan',
    ]);
    if (is_wp_error($user_id)) {
        $fail('erreur');
    }
    update_user_meta($user_id, '_idc_phone', $phone);
    update_user_meta($user_id, '_idc_ville', $ville);
    update_user_meta($user_id, '_idc_code_postal', $cp);
    update_user_meta($user_id, '_idc_consent_at', current_time('mysql'));

    // Fiche artisan en attente de validation.
    $post_id = wp_insert_post([
        'post_type'   => 'artisan',
        'post_status' => 'pending',
        'post_title'  => $entreprise,
    ], true);
    if (!is_wp_error($post_id)) {
        $meta = [
            'user_id'             => $user_id,
            'company_name'        => $entreprise,
            'siret'               => $siret,
            'phone'               => $phone,
            'ville'               => $ville,
            'code_postal'         => $cp,
            'plan'                => 'gratuit',
            'badge_level'         => 'referenced',
            'verification_status' => 'pending',
        ];
        foreach ($meta as $key => $value) {
            if ($value !== '' && $value !== null) {
                update_post_meta($post_id, '_idc_' . $key, (string) $value);
            }
        }
        $term_ids = [];
        foreach ($metiers as $slug) {
            $term = get_term_by('slug', $slug, 'metier');
            if ($term) {
                $term_ids[] = (int) $term->term_id;
            }
        }
        if ($term_ids) {
            wp_set_object_terms($post_id, $term_ids, 'metier');
        }
    }

    // Notifications.
    idc_send_mail('inscription_artisan_admin', get_option('admin_email'), [
        '{entreprise}' => $entreprise,
        '{nom}'        => $nom,
        '{email}'      => $email,
        '{ville}'      => $ville,
        '{lien_admin}' => is_wp_error($post_id) ? admin_url('edit.php?post_type=artisan') : admin_url('post.php?post=' . $post_id . '&action=edit'),
    ]);
    idc_send_mail('inscription_artisan_bienvenue', $email, [
        '{nom}'        => $nom,
        '{entreprise}' => $entreprise,
    ]);

    wp_safe_redirect(add_query_arg('inscription', 'ok', $back));
    exit;
}
