<?php
/**
 * Point d'entrée de l'application : http://site/infodevis-admin/
 * Intercepté sur `init` (aucune règle de réécriture, aucun flush nécessaire).
 * Ne charge ni le thème ni wp-admin : uniquement le shell HTML de la SPA.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', static function (): void {
    $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $home_path = rtrim((string) parse_url(home_url('/'), PHP_URL_PATH), '/');
    $app_path  = $home_path . '/' . IDA_APP_SLUG;

    if ($path !== $app_path && !str_starts_with($path, $app_path . '/')) {
        return;
    }

    if (!is_user_logged_in()) {
        auth_redirect(); // redirige vers wp-login puis revient ici
    }
    if (!ida_can_manage()) {
        wp_die(
            'Accès réservé à l\'équipe InfoDevis. <a href="' . esc_url(home_url('/')) . '">Retour au site</a>',
            'InfoDevis Admin',
            403
        );
    }

    nocache_headers();
    ida_render_app_shell();
    exit;
}, 1);

/**
 * Groupe « Administration WordPress » de la sidebar (administrateurs).
 * Liens directs vers les écrans natifs — rien n'est supprimé côté wp-admin,
 * WordPress applique ses propres capacités sur chaque écran.
 */
function ida_wp_admin_menu(): array
{
    if (!current_user_can('administrator')) {
        return [];
    }

    $updates = 0;
    if (function_exists('wp_get_update_data')) {
        $data = wp_get_update_data();
        $updates = (int) ($data['counts']['total'] ?? 0);
    }

    $items = [
        ['label' => 'Médias',          'icon' => 'image',      'url' => admin_url('upload.php')],
        ['label' => 'Apparence',       'icon' => 'palette',    'url' => admin_url('themes.php')],
        ['label' => 'Extensions',      'icon' => 'plug',       'url' => admin_url('plugins.php')],
        ['label' => 'Utilisateurs',    'icon' => 'users',      'url' => admin_url('users.php')],
        ['label' => 'Outils',          'icon' => 'wrench',     'url' => admin_url('tools.php')],
        ['label' => 'Réglages',        'icon' => 'settings',   'url' => admin_url('options-general.php')],
        ['label' => 'Santé du site',   'icon' => 'heart-pulse', 'url' => admin_url('site-health.php')],
        ['label' => 'Mises à jour',    'icon' => 'refresh',    'url' => admin_url('update-core.php'), 'badge' => $updates],
    ];
    if (defined('WPSEO_VERSION')) {
        $items[] = ['label' => 'Yoast SEO', 'icon' => 'trending-up', 'url' => admin_url('admin.php?page=wpseo_dashboard')];
    }
    if (defined('WPMS_PLUGIN_VER') || is_plugin_active_for_ida('wp-mail-smtp/wp_mail_smtp.php')) {
        $items[] = ['label' => 'WP Mail SMTP', 'icon' => 'mail', 'url' => admin_url('admin.php?page=wp-mail-smtp')];
    }
    return $items;
}

/** is_plugin_active sans dépendre du chargement de wp-admin/includes/plugin.php. */
function is_plugin_active_for_ida(string $plugin): bool
{
    return in_array($plugin, (array) get_option('active_plugins', []), true);
}

/** Rendu du shell HTML de la SPA. */
function ida_render_app_shell(): void
{
    $user = wp_get_current_user();

    $boot = [
        'wpAdminMenu' => ida_wp_admin_menu(),
        'restRoot' => esc_url_raw(rest_url('idc/v1/admin')),
        'wpRest'   => esc_url_raw(rest_url()),
        'nonce'    => wp_create_nonce('wp_rest'),
        'appUrl'   => ida_app_url(),
        'homeUrl'  => home_url('/'),
        'adminUrl' => admin_url(),
        'logoutUrl' => wp_logout_url(home_url('/')),
        'isAdmin'  => current_user_can('administrator'),
        'user'     => [
            'name'   => $user->display_name,
            'first'  => get_user_meta($user->ID, 'first_name', true) ?: $user->display_name,
            'email'  => $user->user_email,
            'avatar' => get_avatar_url($user->ID, ['size' => 64]),
        ],
        'site'     => ['name' => get_bloginfo('name')],
        'version'  => IDA_VERSION,
    ];

    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>InfoDevis Admin</title>
<link rel="icon" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/favicon.svg'); ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo esc_url(IDA_URL . 'assets/css/admin.css?v=' . IDA_VERSION); ?>">
<script>window.IDA = <?php echo wp_json_encode($boot); ?>;</script>
</head>
<body>
<div id="app" class="ida-app">
  <div class="ida-boot-loader" aria-label="Chargement">
    <div class="ida-boot-logo">Info<span>Devis</span></div>
    <div class="ida-boot-spinner"></div>
  </div>
</div>
<script type="module" src="<?php echo esc_url(IDA_URL . 'assets/js/app.js?v=' . IDA_VERSION); ?>"></script>
</body>
</html>
    <?php
}
