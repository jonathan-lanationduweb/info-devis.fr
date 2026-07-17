<?php
/**
 * Info Devis Core — abonnements Stripe (mode test).
 *
 * Les clés sont définies dans wp-config.php (jamais dans le code ni côté
 * client) : IDC_STRIPE_PUBLIC, IDC_STRIPE_SECRET, IDC_STRIPE_PRICE_SILVER,
 * IDC_STRIPE_PRICE_GOLD, IDC_STRIPE_WEBHOOK_SECRET.
 *
 * Flux : page Tarifs pro -> Checkout Session (API REST Stripe) -> retour
 * espace membre -> webhook /wp-json/idc/v1/stripe-webhook (signature vérifiée,
 * idempotent) qui met à jour le plan de l'artisan.
 */

if (!defined('ABSPATH')) {
    exit;
}

function idc_stripe_ready(): bool
{
    return defined('IDC_STRIPE_SECRET') && IDC_STRIPE_SECRET !== ''
        && defined('IDC_STRIPE_PRICE_SILVER') && defined('IDC_STRIPE_PRICE_GOLD');
}

/** Appel API Stripe (form-encoded). Retourne le JSON décodé ou WP_Error. */
function idc_stripe_request(string $method, string $endpoint, array $body = [])
{
    if (!idc_stripe_ready()) {
        return new WP_Error('idc_stripe_off', 'Stripe non configuré.');
    }
    $args = [
        'method'  => $method,
        'timeout' => 15,
        'headers' => [
            'Authorization' => 'Bearer ' . IDC_STRIPE_SECRET,
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ],
    ];
    if ($body) {
        $args['body'] = http_build_query($body);
    }
    $response = wp_remote_request('https://api.stripe.com/v1/' . ltrim($endpoint, '/'), $args);
    if (is_wp_error($response)) {
        return $response;
    }
    $data = json_decode(wp_remote_retrieve_body($response), true);
    $code = wp_remote_retrieve_response_code($response);
    if ($code >= 400) {
        return new WP_Error('idc_stripe_api', $data['error']['message'] ?? ('Erreur Stripe HTTP ' . $code));
    }
    return $data;
}

/* ---------------------------------------------------------------------- */
/*  [idc_tarifs] — page Tarifs professionnels                               */
/* ---------------------------------------------------------------------- */

add_shortcode('idc_tarifs', static function (): string {
    // Offres et prix identiques à la page Tarifs Pro originale (views/home/tarifs.php).
    $plans = [
        'gratuit' => [
            'label' => 'Gratuit', 'prix' => '0€ /mois',
            'points' => ['Profil professionnel basique', '1 réalisation maximum (1 photo)', 'Badge « Référencé » (SIRET vérifié)', 'Système de prise de RDV en ligne'],
        ],
        'silver' => [
            'label' => 'Silver', 'prix' => '10€ /mois',
            'points' => ['5 réalisations (3 photos chacune)', 'Badge « Vérifié »', 'Visibilité renforcée', 'Articles blog (3/mois)'],
        ],
        'gold' => [
            'label' => 'Gold', 'prix' => '14€ /mois',
            'points' => ['Réalisations illimitées', 'Badge « Vérifié Pro »', 'Visibilité PRIORITAIRE (Top 5 garanti)', 'Articles blog illimités', 'Thème personnalisé'],
        ],
    ];

    $html = '';
    if (isset($_GET['abo']) && $_GET['abo'] === 'annule') {
        $html .= '<div class="idc-notice idc-notice--error">Le paiement a été annulé. Vous pouvez réessayer quand vous le souhaitez.</div>';
    }
    if (!idc_stripe_ready()) {
        $html .= '<div class="idc-notice idc-notice--error">Le paiement en ligne n’est pas encore configuré sur cet environnement.</div>';
    }

    $is_artisan = is_user_logged_in() && in_array('artisan', (array) wp_get_current_user()->roles, true);

    $html .= '<div class="idc-grid" style="grid-template-columns:repeat(auto-fit,minmax(250px,1fr));">';
    foreach ($plans as $key => $plan) {
        $html .= '<div class="idc-card" style="text-align:left;">';
        $html .= '<h3 class="idc-card__title" style="font-size:1.3rem;">' . esc_html($plan['label']) . '</h3>';
        $html .= '<p style="font-size:1.6rem;font-weight:700;color:var(--idc-brand,#207752);margin:4px 0 14px;">' . esc_html($plan['prix']) . '</p>';
        $html .= '<ul style="margin:0 0 18px;padding-left:18px;">';
        foreach ($plan['points'] as $point) {
            $html .= '<li>' . esc_html($point) . '</li>';
        }
        $html .= '</ul>';
        if ($key === 'gratuit') {
            $html .= '<a class="idc-btn idc-btn--brand" href="' . esc_url(home_url('/inscription/')) . '">Créer ma fiche</a>';
        } elseif ($is_artisan && idc_stripe_ready()) {
            $html .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">'
                . '<input type="hidden" name="action" value="idc_stripe_checkout" />'
                . '<input type="hidden" name="idc_plan" value="' . esc_attr($key) . '" />'
                . wp_nonce_field('idc_stripe_checkout', 'idc_stripe_nonce', true, false)
                . '<button type="submit" class="idc-btn">Choisir ' . esc_html($plan['label']) . '</button></form>';
        } else {
            $html .= '<a class="idc-btn" href="' . esc_url(home_url('/espace-membre/')) . '">Se connecter pour s’abonner</a>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '<p style="font-size:0.85rem;color:#6b7280;">Paiement sécurisé par Stripe. Abonnement résiliable à tout moment.</p>';

    return $html;
});

/* ---------------------------------------------------------------------- */
/*  Création de la session Checkout                                        */
/* ---------------------------------------------------------------------- */

add_action('admin_post_idc_stripe_checkout', static function (): void {
    $back = home_url('/tarifs-pro/');

    if (!is_user_logged_in()
        || !isset($_POST['idc_stripe_nonce'])
        || !wp_verify_nonce($_POST['idc_stripe_nonce'], 'idc_stripe_checkout')) {
        wp_safe_redirect($back);
        exit;
    }
    $user = wp_get_current_user();
    if (!in_array('artisan', (array) $user->roles, true)) {
        wp_safe_redirect($back);
        exit;
    }

    $plan  = $_POST['idc_plan'] ?? '';
    $price = $plan === 'gold' ? IDC_STRIPE_PRICE_GOLD : ($plan === 'silver' ? IDC_STRIPE_PRICE_SILVER : '');
    if (!$price) {
        wp_safe_redirect($back);
        exit;
    }

    $session = idc_stripe_request('POST', 'checkout/sessions', [
        'mode'                            => 'subscription',
        'line_items[0][price]'            => $price,
        'line_items[0][quantity]'         => 1,
        'success_url'                     => home_url('/dashboard/artisan/abonnement/?abo=ok'),
        'cancel_url'                      => home_url('/tarifs-pro/?abo=annule'),
        'client_reference_id'             => $user->ID,
        'customer_email'                  => $user->user_email,
        'metadata[user_id]'               => $user->ID,
        'metadata[plan]'                  => $plan,
        'subscription_data[metadata][user_id]' => $user->ID,
        'subscription_data[metadata][plan]'    => $plan,
    ]);

    if (is_wp_error($session) || empty($session['url'])) {
        wp_safe_redirect(add_query_arg('abo', 'annule', $back));
        exit;
    }
    wp_redirect($session['url']); // Domaine Stripe : redirection externe volontaire.
    exit;
});

/* ---------------------------------------------------------------------- */
/*  Webhook Stripe — signature vérifiée + idempotence                      */
/* ---------------------------------------------------------------------- */

add_action('rest_api_init', static function (): void {
    register_rest_route('idc/v1', '/stripe-webhook', [
        'methods'             => 'POST',
        'permission_callback' => '__return_true', // La signature Stripe fait office d'authentification.
        'callback'            => 'idc_stripe_webhook',
    ]);
});

function idc_stripe_verify_signature(string $payload, string $header): bool
{
    if (!defined('IDC_STRIPE_WEBHOOK_SECRET') || IDC_STRIPE_WEBHOOK_SECRET === '') {
        return false;
    }
    $timestamp  = '';
    $signatures = [];
    foreach (explode(',', $header) as $part) {
        [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
        if ($key === 't') {
            $timestamp = $value;
        } elseif ($key === 'v1') {
            $signatures[] = $value;
        }
    }
    if (!$timestamp || !$signatures) {
        return false;
    }
    // Tolérance de 5 minutes contre le rejeu.
    if (abs(time() - (int) $timestamp) > 300) {
        return false;
    }
    $expected = hash_hmac('sha256', $timestamp . '.' . $payload, IDC_STRIPE_WEBHOOK_SECRET);
    foreach ($signatures as $signature) {
        if (hash_equals($expected, $signature)) {
            return true;
        }
    }
    return false;
}

function idc_stripe_webhook(WP_REST_Request $request): WP_REST_Response
{
    $payload = $request->get_body();
    $header  = $request->get_header('stripe-signature') ?: '';

    if (!idc_stripe_verify_signature($payload, $header)) {
        return new WP_REST_Response(['error' => 'invalid signature'], 400);
    }

    $event = json_decode($payload, true);
    if (!is_array($event) || empty($event['id']) || empty($event['type'])) {
        return new WP_REST_Response(['error' => 'invalid payload'], 400);
    }

    // Idempotence : un événement déjà traité est accepté sans effet.
    $processed = get_option('idc_stripe_events', []);
    if (in_array($event['id'], $processed, true)) {
        return new WP_REST_Response(['status' => 'already processed'], 200);
    }

    $object = $event['data']['object'] ?? [];

    switch ($event['type']) {
        case 'checkout.session.completed':
            $user_id = (int) ($object['metadata']['user_id'] ?? $object['client_reference_id'] ?? 0);
            $plan    = sanitize_key($object['metadata']['plan'] ?? '');
            if ($user_id && in_array($plan, ['silver', 'gold'], true)) {
                idc_stripe_set_plan($user_id, $plan, [
                    'customer'     => (string) ($object['customer'] ?? ''),
                    'subscription' => (string) ($object['subscription'] ?? ''),
                ]);
                $user = get_userdata($user_id);
                if ($user) {
                    idc_send_mail('abonnement_active', $user->user_email, ['{plan}' => ucfirst($plan)]);
                }
            }
            break;

        case 'invoice.payment_failed':
            $user_id = idc_stripe_user_from_customer((string) ($object['customer'] ?? ''));
            if ($user_id && ($user = get_userdata($user_id))) {
                idc_send_mail('paiement_echec', $user->user_email, ['{plan}' => '']);
            }
            break;

        case 'customer.subscription.deleted':
            $user_id = (int) ($object['metadata']['user_id'] ?? 0);
            if (!$user_id) {
                $user_id = idc_stripe_user_from_customer((string) ($object['customer'] ?? ''));
            }
            if ($user_id) {
                $ancien_plan = (string) get_user_meta($user_id, '_idc_plan', true);
                idc_stripe_set_plan($user_id, 'gratuit', []);
                if ($user = get_userdata($user_id)) {
                    idc_send_mail('abonnement_annule', $user->user_email, [
                        '{plan}' => ucfirst($ancien_plan ?: 'payante'),
                    ], ['type' => 'user', 'id' => $user_id]);
                }
            }
            break;
    }

    $processed[] = $event['id'];
    update_option('idc_stripe_events', array_slice($processed, -200), false);

    return new WP_REST_Response(['status' => 'ok'], 200);
}

/** Applique un plan à l'utilisateur ET à sa fiche artisan. */
function idc_stripe_set_plan(int $user_id, string $plan, array $stripe_ids): void
{
    update_user_meta($user_id, '_idc_plan', $plan);
    if (!empty($stripe_ids['customer'])) {
        update_user_meta($user_id, '_idc_stripe_customer_id', $stripe_ids['customer']);
    }
    if (!empty($stripe_ids['subscription'])) {
        update_user_meta($user_id, '_idc_stripe_subscription_id', $stripe_ids['subscription']);
    }
    $fiche = get_posts([
        'post_type'   => 'artisan',
        'post_status' => 'any',
        'numberposts' => 1,
        'fields'      => 'ids',
        'meta_key'    => '_idc_user_id',
        'meta_value'  => $user_id,
    ]);
    if ($fiche) {
        update_post_meta((int) $fiche[0], '_idc_plan', $plan);
    }
}

function idc_stripe_user_from_customer(string $customer_id): int
{
    if (!$customer_id) {
        return 0;
    }
    $users = get_users([
        'meta_key'   => '_idc_stripe_customer_id',
        'meta_value' => $customer_id,
        'number'     => 1,
        'fields'     => 'ID',
    ]);
    return $users ? (int) $users[0] : 0;
}
