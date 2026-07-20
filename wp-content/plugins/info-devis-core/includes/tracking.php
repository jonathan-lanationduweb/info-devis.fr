<?php
/**
 * Info Devis Core — mesure d'audience & conversions, conforme RGPD.
 *
 * Principe (CNIL / Consent Mode v2) :
 *  - AUCUN script Google n'est chargé tant que le visiteur n'a pas consenti.
 *  - Consent Mode par défaut « refusé » ; passé à « accordé » après acceptation.
 *  - GA4 ne se charge que si un ID de mesure est configuré ET le consentement
 *    « mesure » est donné.
 *  - Événements de conversion : generate_lead (devis), rdv_booked (RDV),
 *    sign_up (inscription) — déclenchés seulement après consentement.
 *
 * Configuration de l'ID GA4 : Réglages → Général (accessible via /wp-admin/
 * options-general.php?classic=1), ou filtre `idc_ga4_id`, ou option `idc_ga4_id`.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** ID de mesure GA4 (ex. G-XXXXXXXXXX). Vide = aucune mesure. */
function idc_ga4_id(): string
{
    $id = (string) get_option('idc_ga4_id', '');
    $id = (string) apply_filters('idc_ga4_id', $id);
    return preg_match('/^(G|AW|GT)-[A-Z0-9]+$/i', $id) ? $id : '';
}

/** Conversions à déclencher sur la page courante (selon le contexte). */
function idc_tracking_conversions(): array
{
    $events = [];
    // Devis envoyé → page de confirmation.
    if (is_page('devis-confirmation') || is_page_template('page-devis-confirmation.php')) {
        $events[] = ['name' => 'generate_lead', 'params' => ['form' => 'devis']];
    }
    // Inscription réussie.
    if (isset($_GET['inscription']) && $_GET['inscription'] === 'ok') {
        $events[] = ['name' => 'sign_up', 'params' => ['method' => 'email']];
    }
    // RDV réservé.
    if (isset($_GET['rdv']) && $_GET['rdv'] === 'ok') {
        $events[] = ['name' => 'rdv_booked', 'params' => []];
    }
    return $events;
}

/* ── Consent Mode v2 : valeurs par défaut « refusé » (avant tout tag) ───── */
add_action('wp_head', static function (): void {
    if (idc_ga4_id() === '') {
        return; // pas de mesure configurée → rien du tout
    }
    ?>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('consent', 'default', {
    ad_storage: 'denied', analytics_storage: 'denied',
    ad_user_data: 'denied', ad_personalization: 'denied',
    functionality_storage: 'granted', security_storage: 'granted',
    wait_for_update: 500
  });
</script>
    <?php
}, 0);

/* ── Assets du bandeau de consentement + configuration ─────────────────── */
add_action('wp_enqueue_scripts', static function (): void {
    if (idc_ga4_id() === '') {
        return; // pas de mesure → pas de bandeau ni de JS
    }
    $base = plugin_dir_url(__FILE__) . 'assets/';
    wp_enqueue_style('idc-consent', $base . 'consent.css', [], '1.0.1');
    wp_enqueue_script('idc-consent', $base . 'consent.js', [], '1.0.1', true);
    wp_localize_script('idc-consent', 'IDC_CONSENT', [
        'ga4'         => idc_ga4_id(),
        'conversions' => idc_tracking_conversions(),
        'policyUrl'   => home_url('/confidentialite/'),
    ]);
});

/* ── Bandeau (rendu en pied de page) ───────────────────────────────────── */
add_action('wp_footer', static function (): void {
    if (is_admin() || idc_ga4_id() === '') {
        return;
    }
    $tpl = get_theme_file_path('template-parts/cookie-consent.php');
    if (file_exists($tpl)) {
        include $tpl;
    }
}, 5); // priorité 5 : le bandeau est dans le DOM AVANT les scripts de pied (priorité 20)

/* ── Réglage de l'ID GA4 dans les Réglages → Général de WordPress ──────── */
add_action('admin_init', static function (): void {
    register_setting('general', 'idc_ga4_id', [
        'type'              => 'string',
        'sanitize_callback' => static function ($v): string {
            $v = strtoupper(trim((string) $v));
            return preg_match('/^(G|AW|GT)-[A-Z0-9]+$/', $v) ? $v : '';
        },
        'default'           => '',
    ]);
    add_settings_field(
        'idc_ga4_id',
        'ID Google Analytics 4',
        static function (): void {
            printf(
                '<input type="text" name="idc_ga4_id" value="%s" class="regular-text" placeholder="G-XXXXXXXXXX"> '
                . '<p class="description">Laisser vide pour désactiver toute mesure. Le suivi ne démarre qu\'après consentement du visiteur (RGPD).</p>',
                esc_attr((string) get_option('idc_ga4_id', ''))
            );
        },
        'general'
    );
});
