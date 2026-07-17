<?php
/**
 * Intégration wp-admin : l'équipe InfoDevis n'utilise plus l'admin WordPress.
 * - Après connexion, les utilisateurs idc_manage arrivent sur /infodevis-admin/
 * - Le tableau de bord wp-admin et les listes natives remplacées redirigent
 *   vers l'écran InfoDevis correspondant
 * - Échappatoire : ?classic=1 (administrateurs) pose un cookie de session
 * - L'admin bar est retirée pour les gestionnaires
 * Rien n'est supprimé : désactiver ce plugin restitue tout.
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDA_CLASSIC_COOKIE = 'ida_classic';

/** Mode classique actif ? (cookie de session, administrateurs uniquement) */
function ida_classic_mode(): bool
{
    if (isset($_GET['classic'])) {
        return $_GET['classic'] === '1';
    }
    return !empty($_COOKIE[IDA_CLASSIC_COOKIE]);
}

/* Pose / retire le cookie quand ?classic=0|1 est passé. */
add_action('admin_init', static function (): void {
    if (!isset($_GET['classic']) || headers_sent()) {
        return;
    }
    if ($_GET['classic'] === '1' && current_user_can('administrator')) {
        setcookie(IDA_CLASSIC_COOKIE, '1', 0, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
    } else {
        setcookie(IDA_CLASSIC_COOKIE, '', time() - 3600, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true);
    }
}, 0);

/* Redirection après connexion : l'équipe arrive sur InfoDevis Admin. */
add_filter('login_redirect', static function ($redirect_to, $requested, $user) {
    if ($user instanceof WP_User && user_can($user, 'idc_manage')) {
        // Respecter une destination explicite vers l'app ou le mode classique.
        if (is_string($requested) && (str_contains($requested, IDA_APP_SLUG) || str_contains($requested, 'classic=1'))) {
            return $redirect_to;
        }
        return ida_app_url();
    }
    return $redirect_to;
}, 10, 3);

/* Remplacement des écrans wp-admin par les écrans InfoDevis. */
add_action('admin_init', static function (): void {
    if (wp_doing_ajax() || wp_doing_cron() || !ida_can_manage() || ida_classic_mode()) {
        return;
    }

    global $pagenow;

    // Tableau de bord wp-admin → dashboard InfoDevis.
    if ($pagenow === 'index.php' && empty($_GET['page'])) {
        wp_safe_redirect(ida_app_url('/dashboard'));
        exit;
    }

    // Listes natives remplacées → écran InfoDevis correspondant.
    if ($pagenow === 'edit.php') {
        $post_type = (string) ($_GET['post_type'] ?? 'post');
        $map = [
            'post'          => '/articles',
            'page'          => '/pages',
            'guide'         => '/guides',
            'artisan'       => '/artisans',
            'avis'          => '/avis',
            'demande_devis' => '/demandes',
            'realisation'   => '/realisations',
            'rdv'           => '/rdv',
        ];
        if (isset($map[$post_type])) {
            wp_safe_redirect(ida_app_url($map[$post_type]));
            exit;
        }
    }

    // Taxonomie métiers → écran Métiers.
    if ($pagenow === 'edit-tags.php' && ($_GET['taxonomy'] ?? '') === 'metier') {
        wp_safe_redirect(ida_app_url('/metiers'));
        exit;
    }

    // Anciennes pages Info Devis → nouveaux écrans.
    if ($pagenow === 'admin.php') {
        $page = (string) ($_GET['page'] ?? '');
        if ($page === 'idc-systeme') {
            wp_safe_redirect(ida_app_url('/parametres'));
            exit;
        }
        if ($page === 'idc-emails') {
            wp_safe_redirect(ida_app_url('/parametres/emails'));
            exit;
        }
    }
});

/*
 * Les menus natifs de wp-admin ne sont PAS supprimés : ils restent indispensables
 * pour la maintenance (extensions, mises à jour, réglages avancés). L'application
 * InfoDevis Admin expose un groupe « Administration WordPress » (administrateurs)
 * qui pointe directement vers ces écrans ; WordPress gère lui-même les capacités.
 */

/* Admin bar : masquée sur le front pour l'équipe (l'app a sa propre navigation). */
add_filter('show_admin_bar', static function ($show) {
    if (is_user_logged_in() && ida_can_manage() && !ida_classic_mode()) {
        return false;
    }
    return $show;
});

/* Lien « InfoDevis Admin » dans l'admin bar quand elle est visible (mode classique). */
add_action('admin_bar_menu', static function (WP_Admin_Bar $bar): void {
    if (!ida_can_manage()) {
        return;
    }
    $bar->add_node([
        'id'    => 'infodevis-admin',
        'title' => '⬢ InfoDevis Admin',
        'href'  => ida_app_url(),
        'meta'  => ['title' => 'Ouvrir InfoDevis Admin'],
    ]);
}, 15);

/* Page de connexion aux couleurs InfoDevis (léger, sans refonte du formulaire). */
add_action('login_enqueue_scripts', static function (): void {
    ?>
    <style>
        body.login { background: #f6f7f9; font-family: 'Manrope', -apple-system, sans-serif; }
        body.login h1 a {
            background: none; width: auto; height: auto; font-size: 0; text-decoration: none;
        }
        body.login h1 a::after {
            content: 'Info-Devis'; font-size: 30px; font-weight: 800;
            color: #0f1f33; letter-spacing: -0.5px;
        }
        .login form {
            border: 1px solid #e5e8ec; border-radius: 16px;
            box-shadow: 0 4px 24px rgba(15, 31, 51, .07); padding: 32px;
        }
        .login form .input, .login input[type=text], .login input[type=password] {
            border-radius: 8px; border-color: #e5e8ec; padding: 8px 12px;
        }
        .wp-core-ui .button-primary {
            background: #207752; border-color: #207752; border-radius: 8px;
            padding: 4px 20px; text-shadow: none; box-shadow: none;
        }
        .wp-core-ui .button-primary:hover { background: #165a3c; border-color: #165a3c; }
        .login #backtoblog a, .login #nav a { color: #5c6675; }
        .login .privacy-policy-page-link { display: none; }
    </style>
    <?php
});
add_filter('login_headerurl', static fn() => home_url('/'));
add_filter('login_headertext', static fn() => 'Info-Devis');
