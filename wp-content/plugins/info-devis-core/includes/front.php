<?php
/**
 * Info Devis Core — shortcodes front + traitement du formulaire de devis.
 */

if (!defined('ABSPATH')) {
    exit;
}

final class InfoDevisFront
{
    /** Émojis de secours par slug de métier. */
    private const ICONS = [
        'plomberie'               => '🔧',
        'electricite'             => '⚡',
        'peinture'                => '🎨',
        'toiture'                 => '🏠',
        'chauffage'               => '🔥',
        'menuiserie'              => '🪚',
        'climatisation'           => '❄️',
        'isolation'               => '🧱',
        'maconnerie'              => '🧱',
        'carrelage'               => '◼️',
        'jardinage'               => '🌿',
        'renovation'              => '🛠️',
        'securite-domotique'      => '🔒',
        'energies-renouvelables'  => '☀️',
        'amenagements-exterieurs' => '🌳',
        'services-b2b'            => '🏢',
        'demenagement-services'   => '📦',
        'traitement-protection'   => '🛡️',
    ];

    public static function init(): void
    {
        add_shortcode('idc_categories', [__CLASS__, 'shortcode_categories']);
        add_shortcode('idc_artisans', [__CLASS__, 'shortcode_artisans']);
        add_shortcode('idc_devis_form', [__CLASS__, 'shortcode_devis_form']);
        add_shortcode('idc_dashboard', [__CLASS__, 'shortcode_dashboard']);
        add_action('admin_post_idc_submit_devis', [__CLASS__, 'handle_devis_submission']);
        add_action('admin_post_nopriv_idc_submit_devis', [__CLASS__, 'handle_devis_submission']);
        add_action('admin_post_idc_submit_devis2', [__CLASS__, 'handle_devis_v2']);
        add_action('admin_post_nopriv_idc_submit_devis2', [__CLASS__, 'handle_devis_v2']);
        add_action('admin_post_idc_contact_send', [__CLASS__, 'handle_contact']);
        add_action('admin_post_nopriv_idc_contact_send', [__CLASS__, 'handle_contact']);
    }

    private static function icon_for(WP_Term $term): string
    {
        $icon = get_term_meta($term->term_id, '_idc_icon', true);
        if ($icon && preg_match('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $icon)) {
            return $icon;
        }
        return self::ICONS[$term->slug] ?? '🔧';
    }

    /* ------------------------------------------------------------------ */
    /*  [idc_categories] — grille des catégories de travaux                */
    /* ------------------------------------------------------------------ */

    public static function shortcode_categories(): string
    {
        $terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0]);
        if (is_wp_error($terms) || !$terms) {
            return '';
        }
        $devis_page = get_page_by_path('devis');
        $html = '<div class="idc-grid">';
        foreach ($terms as $term) {
            $count = (int) $term->count;
            $url   = $devis_page
                ? add_query_arg('metier', $term->slug, get_permalink($devis_page))
                : get_term_link($term);
            $html .= '<div class="idc-card"><a href="' . esc_url($url) . '">'
                . '<div class="idc-card__icon">' . self::icon_for($term) . '</div>'
                . '<div class="idc-card__title">' . esc_html($term->name) . '</div>'
                . '<div class="idc-card__meta">Demander un devis</div>'
                . '</a></div>';
        }
        return $html . '</div>';
    }

    /* ------------------------------------------------------------------ */
    /*  [idc_artisans] — annuaire filtrable                                */
    /* ------------------------------------------------------------------ */

    public static function shortcode_artisans(array $atts = []): string
    {
        $atts   = shortcode_atts(['metier' => ''], $atts);
        $metier = sanitize_title($_GET['metier'] ?? $atts['metier']);
        $ville  = sanitize_text_field(wp_unslash($_GET['ville'] ?? ''));

        $args = [
            'post_type'      => 'artisan',
            'post_status'    => 'publish',
            'posts_per_page' => 24,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];
        if ($metier) {
            $args['tax_query'] = [['taxonomy' => 'metier', 'field' => 'slug', 'terms' => $metier]];
        }
        if ($ville) {
            $args['meta_query'] = [['key' => '_idc_ville', 'value' => $ville, 'compare' => 'LIKE']];
        }
        $query = new WP_Query($args);

        $terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0]);
        $html  = '<form class="idc-filters" method="get" action="">';
        $html .= '<div><label for="idc-f-metier">Catégorie</label><select id="idc-f-metier" name="metier"><option value="">Toutes</option>';
        foreach ((array) $terms as $term) {
            $html .= '<option value="' . esc_attr($term->slug) . '"' . selected($metier, $term->slug, false) . '>' . esc_html($term->name) . '</option>';
        }
        $html .= '</select></div>';
        $html .= '<div><label for="idc-f-ville">Ville</label><input id="idc-f-ville" type="text" name="ville" value="' . esc_attr($ville) . '" placeholder="Paris, Lyon…" /></div>';
        $html .= '<button type="submit" class="idc-btn idc-btn--brand">Filtrer</button>';
        $html .= '</form>';

        if (!$query->have_posts()) {
            return $html . '<p>Aucun artisan ne correspond à cette recherche pour le moment.</p>';
        }

        $badge_labels = ['referenced' => 'Référencé', 'verified' => 'Vérifié', 'verified_pro' => 'Vérifié Pro', 'premium' => 'Premium'];
        $html .= '<div class="idc-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            $pid      = get_the_ID();
            $ville_a  = get_post_meta($pid, '_idc_ville', true);
            $siret_ok = get_post_meta($pid, '_idc_siret_verified', true) === '1';
            $rating   = get_post_meta($pid, '_idc_rating_avg', true);
            $count    = (int) get_post_meta($pid, '_idc_rating_count', true);
            $badge    = get_post_meta($pid, '_idc_badge_level', true);
            $metiers  = get_the_terms($pid, 'metier') ?: [];

            $html .= '<div class="idc-card idc-artisan-card">';
            $html .= '<h3 class="idc-card__title" style="text-align:left;margin:0;"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
            if ($ville_a) {
                $html .= '<span class="idc-card__meta">📍 ' . esc_html($ville_a) . '</span>';
            }
            $html .= '<span class="idc-artisan-card__badges">';
            if ($siret_ok) {
                $html .= '<span class="idc-badge idc-badge--verified">✔ SIRET vérifié</span>';
            }
            if ($badge && isset($badge_labels[$badge])) {
                $html .= '<span class="idc-badge">' . esc_html($badge_labels[$badge]) . '</span>';
            }
            if ($rating && $count > 0) {
                $html .= '<span class="idc-badge idc-badge--rating">★ ' . esc_html($rating) . '/5</span>';
            }
            foreach (array_slice($metiers, 0, 3) as $term) {
                $html .= '<span class="idc-badge">' . esc_html($term->name) . '</span>';
            }
            $html .= '</span>';
            $html .= '<a class="idc-btn" href="' . esc_url(get_permalink()) . '">Voir la fiche</a>';
            $html .= '</div>';
        }
        wp_reset_postdata();
        return $html . '</div>';
    }

    /* ------------------------------------------------------------------ */
    /*  [idc_devis_form] — formulaire de demande de devis                  */
    /* ------------------------------------------------------------------ */

    public static function shortcode_devis_form(): string
    {
        $html = '';
        if (isset($_GET['devis'])) {
            if ($_GET['devis'] === 'ok') {
                $html .= '<div class="idc-notice idc-notice--success">✅ Votre demande de devis a bien été envoyée ! Les artisans correspondant à votre projet vont vous recontacter rapidement.</div>';
            } else {
                $messages = [
                    'champs'  => 'Merci de remplir tous les champs obligatoires.',
                    'email'   => 'L’adresse e-mail saisie n’est pas valide.',
                    'trop'    => 'Vous venez déjà d’envoyer une demande. Merci de patienter quelques minutes.',
                    'erreur'  => 'Une erreur est survenue, merci de réessayer.',
                ];
                $html .= '<div class="idc-notice idc-notice--error">' . esc_html($messages[$_GET['devis']] ?? $messages['erreur']) . '</div>';
            }
        }

        $selected = sanitize_title($_GET['metier'] ?? '');
        $terms    = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0]);

        $current_user = wp_get_current_user();

        $html .= '<form class="idc-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        $html .= '<input type="hidden" name="action" value="idc_submit_devis" />';
        $html .= wp_nonce_field('idc_devis_form', 'idc_devis_nonce_front', true, false);
        $html .= '<p class="idc-hp-field" aria-hidden="true"><label>Ne pas remplir<input type="text" name="idc_website" tabindex="-1" autocomplete="off" /></label></p>';

        $html .= '<p><label for="idc-metier">Type de travaux *</label><select id="idc-metier" name="idc_metier" required><option value="">— Choisir une catégorie —</option>';
        foreach ((array) $terms as $term) {
            $html .= '<option value="' . esc_attr($term->slug) . '"' . selected($selected, $term->slug, false) . '>' . esc_html($term->name) . '</option>';
        }
        $html .= '</select></p>';

        $html .= '<p><label for="idc-description">Décrivez votre projet *</label><textarea id="idc-description" name="idc_description" rows="5" required placeholder="Ex : fuite sous l’évier de la cuisine, remplacement du siphon…"></textarea></p>';

        $html .= '<div class="idc-form__row">';
        $html .= '<p><label for="idc-ville">Ville *</label><input id="idc-ville" type="text" name="idc_ville" required /></p>';
        $html .= '<p><label for="idc-cp">Code postal *</label><input id="idc-cp" type="text" name="idc_code_postal" pattern="[0-9]{5}" maxlength="5" required /></p>';
        $html .= '</div>';

        $html .= '<div class="idc-form__row">';
        $html .= '<p><label for="idc-urgence">Urgence</label><select id="idc-urgence" name="idc_urgency"><option value="normal">Dans les prochaines semaines</option><option value="urgent">Urgent (cette semaine)</option><option value="tres_urgent">Très urgent (aujourd’hui / demain)</option></select></p>';
        $html .= '<p><label for="idc-budget">Budget indicatif (€)</label><input id="idc-budget" type="number" name="idc_budget_max" min="0" step="50" /></p>';
        $html .= '</div>';

        $html .= '<div class="idc-form__row">';
        $html .= '<p><label for="idc-nom">Votre nom *</label><input id="idc-nom" type="text" name="idc_nom" value="' . esc_attr($current_user->exists() ? $current_user->display_name : '') . '" required /></p>';
        $html .= '<p><label for="idc-email">Votre e-mail *</label><input id="idc-email" type="email" name="idc_email" value="' . esc_attr($current_user->exists() ? $current_user->user_email : '') . '" required /></p>';
        $html .= '</div>';

        $html .= '<p><label for="idc-tel">Téléphone</label><input id="idc-tel" type="tel" name="idc_phone" /></p>';

        $html .= '<p><label style="font-weight:400;"><input type="checkbox" name="idc_consent" value="1" required /> J’accepte que mes informations soient transmises aux artisans correspondant à ma demande, conformément à la <a href="' . esc_url(home_url('/confidentialite/')) . '">politique de confidentialité</a>. *</label></p>';

        $html .= '<p style="margin-top:8px;"><button type="submit" class="idc-btn">Recevoir mes devis gratuits</button></p>';
        $html .= '<p style="font-size:0.85rem;color:#6b7280;">Service gratuit et sans engagement.</p>';
        $html .= '</form>';

        return $html;
    }

    public static function handle_devis_submission(): void
    {
        $back = wp_get_referer() ?: home_url('/devis/');
        $back = remove_query_arg(['devis', 'metier'], $back);

        $fail = static function (string $code) use ($back): void {
            wp_safe_redirect(add_query_arg('devis', $code, $back));
            exit;
        };

        if (!isset($_POST['idc_devis_nonce_front']) || !wp_verify_nonce($_POST['idc_devis_nonce_front'], 'idc_devis_form')) {
            $fail('erreur');
        }
        if (!empty($_POST['idc_website'])) { // pot de miel anti-spam
            $fail('erreur');
        }

        // Limite : une demande par IP toutes les 2 minutes.
        $ip  = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        $key = 'idc_devis_rl_' . md5($ip);
        if ($ip && get_transient($key)) {
            $fail('trop');
        }

        $metier      = sanitize_title($_POST['idc_metier'] ?? '');
        $description = sanitize_textarea_field(wp_unslash($_POST['idc_description'] ?? ''));
        $ville       = sanitize_text_field(wp_unslash($_POST['idc_ville'] ?? ''));
        $cp          = preg_replace('/\D/', '', (string) ($_POST['idc_code_postal'] ?? ''));
        $urgency     = in_array($_POST['idc_urgency'] ?? '', ['normal', 'urgent', 'tres_urgent'], true) ? $_POST['idc_urgency'] : 'normal';
        $budget      = is_numeric($_POST['idc_budget_max'] ?? '') ? (float) $_POST['idc_budget_max'] : '';
        $nom         = sanitize_text_field(wp_unslash($_POST['idc_nom'] ?? ''));
        $email       = sanitize_email($_POST['idc_email'] ?? '');
        $phone       = sanitize_text_field(wp_unslash($_POST['idc_phone'] ?? ''));

        $term = $metier ? get_term_by('slug', $metier, 'metier') : false;
        if (!$term || !$description || !$ville || strlen($cp) !== 5 || !$nom) {
            $fail('champs');
        }
        if (!$email || !is_email($email)) {
            $fail('email');
        }
        if (empty($_POST['idc_consent'])) {
            $fail('champs');
        }

        $reference = 'DV' . date_i18n('ymd') . '-' . strtoupper(wp_generate_password(4, false, false));

        $post_id = wp_insert_post([
            'post_type'    => 'demande_devis',
            'post_status'  => 'publish',
            'post_title'   => $reference . ' — ' . $term->name . ' à ' . $ville,
            'post_content' => $description,
        ], true);
        if (is_wp_error($post_id)) {
            $fail('erreur');
        }

        $user = get_user_by('email', $email);
        $meta = [
            'reference'      => $reference,
            'ville'          => $ville,
            'code_postal'    => $cp,
            'urgency'        => $urgency,
            'budget_max'     => $budget,
            'status'         => 'pending',
            'contact_name'   => $nom,
            'contact_email'  => $email,
            'contact_phone'  => $phone,
            'client_user_id' => $user ? $user->ID : '',
            'consent_at'     => current_time('mysql'),
        ];
        foreach ($meta as $meta_key => $value) {
            if ($value !== '' && $value !== null) {
                update_post_meta($post_id, '_idc_' . $meta_key, (string) $value);
            }
        }
        wp_set_object_terms($post_id, [(int) $term->term_id], 'metier');

        set_transient($key, 1, 2 * MINUTE_IN_SECONDS);

        self::send_devis_emails($post_id, $reference, $term->name, $ville, $nom, $email);

        wp_safe_redirect(add_query_arg('devis', 'ok', $back));
        exit;
    }

    /**
     * Traitement du formulaire de devis original (multi-métiers, AJAX JSON).
     * Champs identiques à l'application historique : categories[] (slugs),
     * description, urgency, budget (tranche), first_name, last_name, email,
     * phone, ville, code_postal, consent_privacy, consent_marketing.
     */
    public static function handle_devis_v2(): void
    {
        $is_ajax = !empty($_POST['idc_ajax']);
        $fail    = static function (array $errors) use ($is_ajax): void {
            if ($is_ajax) {
                wp_send_json(['success' => false, 'errors' => $errors]);
            }
            wp_safe_redirect(add_query_arg('devis', 'champs', wp_get_referer() ?: home_url('/devis/')));
            exit;
        };

        if (!isset($_POST['idc_devis_nonce_front'])
            || !wp_verify_nonce($_POST['idc_devis_nonce_front'], 'idc_devis_form')
            || !empty($_POST['idc_website'])) {
            $fail(['Session expirée, merci de recharger la page.']);
        }

        // Limite : une demande par IP toutes les 2 minutes.
        $ip  = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        $key = 'idc_devis_rl_' . md5($ip);
        if ($ip && get_transient($key)) {
            $fail(['Vous venez déjà d’envoyer une demande. Merci de patienter quelques minutes.']);
        }

        $slugs       = array_filter(array_map('sanitize_title', (array) ($_POST['categories'] ?? [])));
        $description = sanitize_textarea_field(wp_unslash($_POST['description'] ?? ''));
        $urgency     = in_array($_POST['urgency'] ?? '', ['normal', 'urgent', 'tres_urgent'], true) ? $_POST['urgency'] : 'normal';
        $budgets     = ['non_defini', 'moins_500', '500_1000', '1000_5000', 'plus_5000'];
        $budget      = in_array($_POST['budget'] ?? '', $budgets, true) ? $_POST['budget'] : 'non_defini';
        $first_name  = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
        $last_name   = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));
        $email       = sanitize_email($_POST['email'] ?? '');
        $phone       = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
        $ville       = sanitize_text_field(wp_unslash($_POST['ville'] ?? ''));
        $cp          = preg_replace('/\D/', '', (string) ($_POST['code_postal'] ?? ''));

        $errors = [];
        $terms  = [];
        foreach ($slugs as $slug) {
            $term = get_term_by('slug', $slug, 'metier');
            if ($term) {
                $terms[] = $term;
            }
        }
        if (!$terms) {
            $errors[] = 'Sélectionnez au moins une catégorie de travaux.';
        }
        if (mb_strlen($description) < 10) {
            $errors[] = 'Décrivez votre projet (10 caractères minimum).';
        }
        if (!$first_name) {
            $errors[] = 'Le prénom est obligatoire.';
        }
        if (!$email || !is_email($email)) {
            $errors[] = 'L’adresse e-mail est invalide.';
        }
        if (!$ville) {
            $errors[] = 'La ville est obligatoire.';
        }
        if ($cp !== '' && strlen($cp) !== 5) {
            $errors[] = 'Le code postal doit comporter 5 chiffres.';
        }
        if (empty($_POST['consent_privacy'])) {
            $errors[] = 'Vous devez accepter la transmission de vos données aux artisans.';
        }
        if ($errors) {
            $fail($errors);
        }

        $nom        = trim($first_name . ' ' . $last_name);
        $cat_names  = array_map(static fn($t) => $t->name, $terms);
        $cats_label = implode(' · ', $cat_names);
        $reference  = 'DV' . date_i18n('ymd') . '-' . strtoupper(wp_generate_password(4, false, false));

        $post_id = wp_insert_post([
            'post_type'    => 'demande_devis',
            'post_status'  => 'publish',
            'post_title'   => $reference . ' — ' . $cats_label . ' à ' . $ville,
            'post_content' => $description,
        ], true);
        if (is_wp_error($post_id)) {
            $fail(['Une erreur technique est survenue, merci de réessayer.']);
        }

        $meta = [
            'reference'         => $reference,
            'ville'             => $ville,
            'code_postal'       => $cp,
            'urgency'           => $urgency,
            'budget_range'      => $budget,
            'status'            => 'pending',
            'contact_name'      => $nom,
            'contact_email'     => $email,
            'contact_phone'     => $phone,
            'consent_at'        => current_time('mysql'),
            'consent_marketing' => empty($_POST['consent_marketing']) ? '0' : '1',
        ];
        $user = get_user_by('email', $email);
        if ($user) {
            $meta['client_user_id'] = $user->ID;
        }
        foreach ($meta as $meta_key => $value) {
            if ($value !== '' && $value !== null) {
                update_post_meta($post_id, '_idc_' . $meta_key, (string) $value);
            }
        }
        wp_set_object_terms($post_id, array_map(static fn($t) => (int) $t->term_id, $terms), 'metier');

        set_transient($key, 1, 2 * MINUTE_IN_SECONDS);
        // Données affichées sur la page de confirmation (référence + métiers).
        set_transient('idc_confirm_' . $reference, ['cats' => $cat_names], HOUR_IN_SECONDS);

        self::send_devis_emails($post_id, $reference, $cats_label, $ville, $nom, $email);

        $redirect = add_query_arg('ref', rawurlencode($reference), home_url('/devis-confirmation/'));
        if ($is_ajax) {
            wp_send_json(['success' => true, 'redirect' => $redirect]);
        }
        wp_safe_redirect($redirect);
        exit;
    }

    private static function send_devis_emails(int $post_id, string $reference, string $metier, string $ville, string $nom, string $email): void
    {
        $vars = [
            '{reference}'  => $reference,
            '{metier}'     => $metier,
            '{ville}'      => $ville,
            '{nom}'        => $nom,
            '{email}'      => $email,
            '{lien_admin}' => admin_url('post.php?post=' . $post_id . '&action=edit'),
        ];

        idc_send_mail('devis_admin', get_option('admin_email'), $vars);
        idc_send_mail('devis_client', $email, $vars);

        if (function_exists('idc_match_artisans')) {
            foreach (idc_match_artisans($post_id) as $artisan_post_id) {
                $user_id = (int) get_post_meta($artisan_post_id, '_idc_user_id', true);
                $user    = $user_id ? get_userdata($user_id) : false;
                if ($user) {
                    idc_send_mail('devis_artisan', $user->user_email, $vars);
                    do_action('idc_lead_new', $user_id, $metier, $ville);
                }
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Formulaire de contact (page /contact/)                             */
    /* ------------------------------------------------------------------ */

    public static function handle_contact(): void
    {
        $back = wp_get_referer() ?: home_url('/contact/');
        $back = remove_query_arg('contact', $back);

        $fail = static function (string $code) use ($back): void {
            wp_safe_redirect(add_query_arg('contact', $code, $back));
            exit;
        };

        if (!isset($_POST['idc_contact_nonce'])
            || !wp_verify_nonce($_POST['idc_contact_nonce'], 'idc_contact_send')
            || !empty($_POST['idc_website'])) {
            $fail('erreur');
        }

        // Limite : un message par IP toutes les 2 minutes.
        $ip  = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        $key = 'idc_contact_rl_' . md5($ip);
        if ($ip && get_transient($key)) {
            $fail('erreur');
        }

        $first   = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
        $last    = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));
        $email   = sanitize_email($_POST['email'] ?? '');
        $subjects = ['info' => 'Demande d\'information', 'tech' => 'Problème technique', 'artisan' => 'Partenariat artisan', 'signalement' => 'Signalement', 'autre' => 'Autre'];
        $subject = $subjects[$_POST['subject'] ?? 'info'] ?? $subjects['info'];
        $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

        if (!$first || !$last || !$email || !is_email($email) || mb_strlen($message) < 5) {
            $fail('champs');
        }

        $sent = wp_mail(
            get_option('admin_email'),
            '[InfoDevis Contact] ' . $subject . ' — ' . $first . ' ' . $last,
            "Message reçu via le formulaire de contact :\n\nDe : $first $last <$email>\nSujet : $subject\n\n$message",
            ['Reply-To: ' . $first . ' ' . $last . ' <' . $email . '>']
        );
        set_transient($key, 1, 2 * MINUTE_IN_SECONDS);

        $log   = get_option('idc_email_log', []);
        $log[] = ['date' => current_time('mysql'), 'template' => 'contact_admin', 'to' => get_option('admin_email'), 'status' => $sent ? 'envoye' : 'echec'];
        update_option('idc_email_log', array_slice($log, -50), false);

        $fail('ok');
    }

    /* ------------------------------------------------------------------ */
    /*  [idc_dashboard] — espace membre (artisan / client)                 */
    /* ------------------------------------------------------------------ */

    public static function shortcode_dashboard(): string
    {
        if (!is_user_logged_in()) {
            $html  = '<div class="idc-form" style="max-width:420px;">';
            $html .= '<h2 style="margin-top:0;">Connexion à votre espace</h2>';
            $html .= wp_login_form(['echo' => false, 'redirect' => get_permalink()]);
            $html .= '<p style="font-size:0.9rem;"><a href="' . esc_url(wp_lostpassword_url(get_permalink())) . '">Mot de passe oublié ?</a></p>';
            $html .= '</div>';
            return $html;
        }

        $user  = wp_get_current_user();
        $roles = (array) $user->roles;

        if (in_array('administrator', $roles, true)) {
            return '<p>Vous êtes connecté en tant qu’administrateur. <a href="' . esc_url(admin_url()) . '">Accéder au tableau de bord WordPress</a>.</p>';
        }

        if (in_array('artisan', $roles, true)) {
            return '<p>Bonjour ' . esc_html($user->display_name) . ' 👋</p>'
                . '<p><a class="idc-btn idc-btn--brand" href="' . esc_url(home_url('/dashboard/artisan/')) . '">Accéder à mon espace artisan</a></p>';
        }

        // Les clients disposent désormais de leur véritable espace.
        return '<p>Bonjour ' . esc_html($user->display_name) . ' 👋</p>'
            . '<p><a class="idc-btn idc-btn--brand" href="' . esc_url(home_url('/dashboard/client/')) . '">Accéder à mon espace client</a></p>';
    }

    private static function dashboard_artisan(WP_User $user): string
    {
        $html = '<h2>Bonjour ' . esc_html($user->display_name) . ' 👋</h2>';

        if (isset($_GET['abo']) && $_GET['abo'] === 'ok') {
            $html .= '<div class="idc-notice idc-notice--success">✅ Merci ! Votre abonnement est en cours d’activation (quelques secondes). Vous recevrez un email de confirmation.</div>';
        }
        $plan = get_user_meta($user->ID, '_idc_plan', true) ?: 'gratuit';
        $html .= '<p>Votre abonnement : <strong>' . esc_html(ucfirst($plan)) . '</strong>'
            . ($plan === 'gratuit' ? ' — <a href="' . esc_url(home_url('/tarifs-pro/')) . '">découvrir les offres Silver et Gold</a>' : '')
            . '</p>';

        $fiche = get_posts([
            'post_type'   => 'artisan',
            'post_status' => 'any',
            'numberposts' => 1,
            'meta_key'    => '_idc_user_id',
            'meta_value'  => $user->ID,
        ]);
        if ($fiche) {
            $html .= '<p>Votre fiche : <a href="' . esc_url(get_permalink($fiche[0])) . '"><strong>' . esc_html(get_the_title($fiche[0])) . '</strong></a></p>';
        }

        $demandes = function_exists('idc_get_demandes_for_artisan') ? idc_get_demandes_for_artisan($user->ID) : [];
        $html .= '<h3>Projets correspondant à votre activité</h3>';
        if (!$demandes) {
            return $html . '<p>Aucun projet correspondant pour le moment.</p>';
        }
        $html .= '<table class="idc-dash-table"><tr><th>Référence</th><th>Projet</th><th>Ville</th><th>Urgence</th><th>Date</th></tr>';
        $urgences = ['normal' => 'Normale', 'urgent' => 'Urgent', 'tres_urgent' => 'Très urgent'];
        foreach ($demandes as $demande) {
            $html .= '<tr><td>' . esc_html(get_post_meta($demande->ID, '_idc_reference', true)) . '</td>'
                . '<td>' . esc_html(wp_trim_words($demande->post_content, 14)) . '</td>'
                . '<td>' . esc_html(get_post_meta($demande->ID, '_idc_ville', true)) . '</td>'
                . '<td>' . esc_html($urgences[get_post_meta($demande->ID, '_idc_urgency', true)] ?? '—') . '</td>'
                . '<td>' . esc_html(get_the_date('', $demande)) . '</td></tr>';
        }
        return $html . '</table>';
    }

    private static function dashboard_client(WP_User $user): string
    {
        $html = '<h2>Bonjour ' . esc_html($user->display_name) . ' 👋</h2><h3>Vos demandes de devis</h3>';

        $demandes = get_posts([
            'post_type'   => 'demande_devis',
            'post_status' => 'publish',
            'numberposts' => 50,
            'meta_query'  => [
                'relation' => 'OR',
                ['key' => '_idc_client_user_id', 'value' => $user->ID],
                ['key' => '_idc_contact_email', 'value' => $user->user_email],
            ],
        ]);
        if (!$demandes) {
            $devis_page = get_page_by_path('devis');
            $html .= '<p>Vous n’avez pas encore de demande. '
                . ($devis_page ? '<a class="idc-btn idc-btn--brand" href="' . esc_url(get_permalink($devis_page)) . '">Demander un devis</a>' : '')
                . '</p>';
            if (function_exists('idc_render_avis_form')) {
                $html .= idc_render_avis_form();
            }
            return $html;
        }

        $statuts = [
            'pending' => 'En attente', 'sent' => 'Envoyée aux artisans', 'accepted' => 'Acceptée',
            'in_progress' => 'En cours', 'completed' => 'Terminée', 'cancelled' => 'Annulée', 'refused' => 'Refusée',
        ];
        $html .= '<table class="idc-dash-table"><tr><th>Référence</th><th>Projet</th><th>Ville</th><th>Statut</th><th>Date</th></tr>';
        foreach ($demandes as $demande) {
            $status = get_post_meta($demande->ID, '_idc_status', true);
            $html .= '<tr><td>' . esc_html(get_post_meta($demande->ID, '_idc_reference', true)) . '</td>'
                . '<td>' . esc_html(wp_trim_words($demande->post_content, 14)) . '</td>'
                . '<td>' . esc_html(get_post_meta($demande->ID, '_idc_ville', true)) . '</td>'
                . '<td>' . esc_html($statuts[$status] ?? $status) . '</td>'
                . '<td>' . esc_html(get_the_date('', $demande)) . '</td></tr>';
        }
        $html .= '</table>';

        if (function_exists('idc_render_avis_form')) {
            $html .= idc_render_avis_form();
        }
        return $html;
    }
}

InfoDevisFront::init();
