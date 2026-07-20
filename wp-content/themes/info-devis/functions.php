<?php
/**
 * Thème Info Devis — autonome, conversion fidèle du site original.
 * Le rendu reproduit views/layout/main.php + includes/navbar.php + includes/footer.php.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('IDV_THEME_VERSION', '1.2.0');
define('IDV_THEME_URI', get_template_directory_uri());

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus(['primary' => 'Menu principal (navbar originale)']);
});

add_action('wp_enqueue_scripts', static function (): void {
    // Google Fonts — identiques à l'original (Newsreader + Manrope + Material Symbols).
    wp_enqueue_style(
        'idv-fonts',
        'https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Manrope:wght@300;400;500;600;700&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'idv-material-symbols',
        'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap',
        [],
        null
    );
    // Font Awesome 7 — icônes originales (pas d'emojis).
    wp_enqueue_style(
        'idv-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css',
        [],
        null
    );

    // CSS originaux copiés tels quels + layout extrait de main.php.
    wp_enqueue_style('idv-theme-tokens', IDV_THEME_URI . '/assets/css/theme.css', [], IDV_THEME_VERSION);
    wp_enqueue_style('idv-portfolio', IDV_THEME_URI . '/assets/css/portfolio.css', ['idv-theme-tokens'], IDV_THEME_VERSION);
    wp_enqueue_style('idv-rdv', IDV_THEME_URI . '/assets/css/rdv.css', ['idv-theme-tokens'], IDV_THEME_VERSION);
    wp_enqueue_style('idv-layout', IDV_THEME_URI . '/assets/css/layout.css', ['idv-theme-tokens'], IDV_THEME_VERSION);
    wp_enqueue_style('idv-transitional', IDV_THEME_URI . '/assets/css/transitional.css', [], IDV_THEME_VERSION);
    wp_enqueue_style('idv-responsive', IDV_THEME_URI . '/assets/css/responsive.css', ['idv-layout', 'idv-transitional'], IDV_THEME_VERSION);
    wp_enqueue_style('idv-chatbot', IDV_THEME_URI . '/assets/css/chatbot.css', ['idv-theme-tokens'], IDV_THEME_VERSION);
    if (is_user_logged_in()) {
        wp_enqueue_style('idv-notifications', IDV_THEME_URI . '/assets/css/notifications.css', ['idv-theme-tokens'], IDV_THEME_VERSION);
    }

    // Tailwind CDN + configuration inline d'origine (views/layout/main.php).
    wp_enqueue_script('idv-tailwind', 'https://cdn.tailwindcss.com?plugins=forms,container-queries', [], null, false);
    wp_add_inline_script('idv-tailwind', idv_tailwind_config(), 'after');

    // Expérience mobile « type application » (sections 21→24).
    wp_enqueue_style('idv-mobile', IDV_THEME_URI . '/assets/css/mobile.css', ['idv-theme-tokens'], IDV_THEME_VERSION);

    // JS originaux.
    wp_enqueue_script('idv-main', IDV_THEME_URI . '/assets/js/main.js', [], IDV_THEME_VERSION, true);
    wp_enqueue_script('idv-portfolio', IDV_THEME_URI . '/assets/js/portfolio.js', [], IDV_THEME_VERSION, true);
    wp_enqueue_script('idv-chatbot', IDV_THEME_URI . '/assets/js/chatbot.js', [], IDV_THEME_VERSION, true);

    // Mobile + PWA.
    wp_enqueue_script('idv-mobile', IDV_THEME_URI . '/assets/js/mobile.js', [], IDV_THEME_VERSION, true);
    wp_enqueue_script('idv-mobile-enhance', IDV_THEME_URI . '/assets/js/mobile-enhance.js', ['idv-mobile'], IDV_THEME_VERSION, true);
    wp_enqueue_script('idv-pwa', IDV_THEME_URI . '/assets/js/pwa.js', [], IDV_THEME_VERSION, true);
    wp_localize_script('idv-pwa', 'IDV_PWA', [
        'swUrl' => home_url('/service-worker.js'),
    ]);
});

/* PWA : manifest, couleur de thème, méta application dans le <head>. */
add_action('wp_head', static function (): void {
    $theme_color = '#207752';
    echo '<link rel="manifest" href="' . esc_url(home_url('/manifest.webmanifest')) . '">' . "\n";
    echo '<meta name="theme-color" content="' . esc_attr($theme_color) . '">' . "\n";
    echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
    echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
    echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
    echo '<meta name="apple-mobile-web-app-title" content="InfoDevis">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . esc_url(home_url('/assets/icons/apple-touch-icon.png')) . '">' . "\n";
}, 1);

/* Assistant guidé (chatbot) : meta base-url + widget en pied de page, sur tout le site. */
add_action('wp_head', static function (): void {
    echo '<meta name="base-url" content="' . esc_url(home_url('')) . '">' . "\n";
});
add_action('wp_footer', static function (): void {
    if (!is_admin()) {
        get_template_part('template-parts/chatbot');
    }
});

/** Config Tailwind — copie conforme de l'original. */
function idv_tailwind_config(): string
{
    return <<<'JS'
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        'primary':             'rgb(var(--tw-primary, 32 119 82) / <alpha-value>)',
        'primary-dk':          'rgb(var(--tw-primary-dk, 22 90 60) / <alpha-value>)',
        'primary-lt':          'rgb(var(--tw-primary-lt, 40 147 106) / <alpha-value>)',
        'primary-container':   'rgb(var(--tw-primary-container, 160 244 198) / <alpha-value>)',
        'on-primary':          'rgb(var(--tw-on-primary, 225 255 235) / <alpha-value>)',
        'on-primary-container':'rgb(var(--tw-on-primary-container, 0 94 61) / <alpha-value>)',
        'secondary': '#57615c',
        'secondary-container': '#dae5de',
        'on-secondary-container': '#4a544f',
        'teal': '#157e90',
        'teal-dk': '#0f6474',
        'surface': '#faf9f8',
        'surface-container-low': '#f3f4f3',
        'surface-container': '#edeeed',
        'surface-container-high': '#e6e9e8',
        'surface-dim': '#d6dbda',
        'on-surface': '#2f3333',
        'on-surface-variant': '#5b605f',
        'outline': '#777c7b',
        'outline-variant': '#aeb3b2',
        'background': '#faf9f8',
        'on-background': '#2f3333',
        'error': '#9f403d',
        'gold': '#f0b429',
      },
      fontFamily: {
        headline: ['Newsreader', 'serif'],
        body: ['Manrope', 'sans-serif'],
        label: ['Manrope', 'sans-serif'],
      },
      borderRadius: {
        DEFAULT: '0.125rem',
        lg: '0.5rem',
        xl: '1rem',
        '2xl': '1.5rem',
        full: '9999px',
      },
    },
  },
};
JS;
}

/** Icônes Font Awesome par slug de métier (fichier original data/category_icons.php). */
function idv_category_icons(): array
{
    static $icons = null;
    if ($icons === null) {
        $file  = get_template_directory() . '/inc/category_icons.php';
        $icons = file_exists($file) ? require $file : [];
    }
    return is_array($icons) ? $icons : [];
}

function idv_cat_icon_html(string $slug, string $extra_class = ''): string
{
    $icons = idv_category_icons();
    $cls   = $icons[$slug] ?? 'fa-solid fa-screwdriver-wrench';
    return '<i class="' . esc_attr($cls . ($extra_class !== '' ? ' ' . $extra_class : '')) . '" aria-hidden="true"></i>';
}

/** Images de couverture par métier (fichier original data/category_images.php). */
function idv_category_images(): array
{
    static $map = null;
    if ($map === null) {
        $file = get_template_directory() . '/inc/category_images.php';
        $raw  = file_exists($file) ? require $file : [];
        $map  = [];
        foreach ((array) $raw as $slug => $info) {
            $local_dir = get_template_directory() . '/assets/images/categories/' . $slug . '/';
            foreach (['cover.jpg', 'cover.webp', 'cover.png'] as $ext) {
                if (file_exists($local_dir . $ext)) {
                    $map[$slug] = IDV_THEME_URI . '/assets/images/categories/' . $slug . '/' . $ext;
                    continue 2;
                }
            }
            if (!empty($info['remote'])) {
                $map[$slug] = $info['remote'];
            }
        }
    }
    return $map;
}

/** URL du tableau de bord selon le rôle (équivalent navbar originale). */
function idv_dashboard_url(): string
{
    $roles = (array) wp_get_current_user()->roles;
    if (in_array('administrator', $roles, true) || in_array('gestionnaire', $roles, true)) {
        return admin_url();
    }
    if (in_array('artisan', $roles, true)) {
        return home_url('/dashboard/artisan/');
    }
    return home_url('/dashboard/client/');
}

/** Liens de navigation : menu WordPress s'il existe, sinon liens originaux. */
function idv_nav_links(): array
{
    $locations = get_nav_menu_locations();
    if (!empty($locations['primary'])) {
        $items = wp_get_nav_menu_items($locations['primary']);
        if ($items) {
            $links = [];
            foreach ($items as $item) {
                if (empty($item->menu_item_parent)) {
                    $links[] = [$item->url, $item->title];
                }
            }
            if ($links) {
                return $links;
            }
        }
    }
    // Fallback : navigation originale (includes/navbar.php).
    return [
        [home_url('/categories/'),     'Métiers'],
        [home_url('/professionnels/'), 'Professionnels'],
        [home_url('/guides/'),         'Guides'],
        [home_url('/tarifs-pro/'),     'Tarifs Pro'],
        [home_url('/blog/'),           'Blog'],
        [home_url('/contact/'),        'Contact'],
    ];
}

/** Lien navbar avec état actif (équivalent navLink() original). */
function idv_nav_link(string $href, string $label): string
{
    $current = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $path    = '/' . trim((string) parse_url($href, PHP_URL_PATH), '/');
    $active  = ($path !== '/' && str_starts_with($current, $path));
    $classes = $active ? 'nav-link nav-link--active' : 'nav-link';
    return '<a href="' . esc_url($href) . '" class="' . $classes . '">' . esc_html($label) . '</a>';
}

/**
 * Garde d'accès des espaces membres : connexion requise + rôle attendu.
 * Un artisan qui tente l'espace client est renvoyé vers son espace (et
 * inversement) ; l'administrateur peut tout voir.
 */
function idv_require_role(string $role): WP_User
{
    if (!is_user_logged_in()) {
        wp_safe_redirect(add_query_arg('redirect_to', rawurlencode($_SERVER['REQUEST_URI'] ?? '/'), home_url('/connexion/')));
        exit;
    }
    $user  = wp_get_current_user();
    $roles = (array) $user->roles;
    if (in_array('administrator', $roles, true) || in_array($role, $roles, true)) {
        return $user;
    }
    // Mauvais espace : redirection selon le rôle réel.
    wp_safe_redirect(in_array('artisan', $roles, true) ? home_url('/dashboard/artisan/') : home_url('/dashboard/client/'));
    exit;
}

/** Demandes de devis du client courant (par compte lié ou email de contact). */
function idv_client_demandes(WP_User $user): array
{
    return get_posts([
        'post_type'      => 'demande_devis',
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => [
            'relation' => 'OR',
            ['key' => '_idc_client_user_id', 'value' => $user->ID],
            ['key' => '_idc_contact_email', 'value' => $user->user_email],
        ],
    ]);
}

/** Libellé + classes de statut d'une demande (mapping original conservé). */
function idv_demande_status(string $status): array
{
    $map = [
        'pending'     => ['En attente', 'bg-secondary-container text-on-secondary-container'],
        'sent'        => ['En attente', 'bg-secondary-container text-on-secondary-container'],
        'accepted'    => ['En cours', 'bg-primary-container text-on-primary-container'],
        'in_progress' => ['En cours', 'bg-primary-container text-on-primary-container'],
        'completed'   => ['Terminé', 'bg-emerald-100 text-emerald-800'],
        'cancelled'   => ['Annulé', 'bg-stone-200 text-stone-500'],
        'refused'     => ['Annulé', 'bg-stone-200 text-stone-500'],
    ];
    return $map[$status] ?? ['Inconnu', 'bg-gray-100 text-gray-600'];
}

/** Fiche artisan liée au compte courant (tout statut, y compris en validation). */
function idv_artisan_fiche(WP_User $user): ?WP_Post
{
    $fiche = get_posts([
        'post_type'   => 'artisan',
        'post_status' => 'any',
        'numberposts' => 1,
        'meta_key'    => '_idc_user_id',
        'meta_value'  => $user->ID,
    ]);
    return $fiche ? $fiche[0] : null;
}

/**
 * Opportunités (leads) d'un artisan : demandes matchées (métier + département)
 * + historique des demandes déjà acceptées/refusées par lui.
 * Retour : [['demande' => WP_Post, 'status' => 'pending'|'accepted'|'refused'], …]
 */
function idv_artisan_leads(WP_User $user): array
{
    $fiche = idv_artisan_fiche($user);
    if (!$fiche) {
        return [];
    }
    $key   = '_idc_lead_status_' . $fiche->ID;
    $leads = [];

    // Historique (déjà répondu).
    foreach (get_posts([
        'post_type'      => 'demande_devis',
        'post_status'    => 'publish',
        'posts_per_page' => 50,
        'meta_key'       => $key,
    ]) as $demande) {
        $leads[$demande->ID] = ['demande' => $demande, 'status' => (string) get_post_meta($demande->ID, $key, true)];
    }
    // Matching courant (en attente de réponse).
    if (function_exists('idc_get_demandes_for_artisan')) {
        foreach (idc_get_demandes_for_artisan($user->ID) as $demande) {
            if (!isset($leads[$demande->ID])) {
                $leads[$demande->ID] = ['demande' => $demande, 'status' => 'pending'];
            }
        }
    }
    // Plus récentes d'abord.
    usort($leads, static fn($a, $b) => strcmp($b['demande']->post_date, $a['demande']->post_date));
    return array_values($leads);
}

/** Échec de connexion : retour sur NOTRE page de connexion (pas wp-login). */
add_action('wp_login_failed', static function (): void {
    $referer = wp_get_referer();
    if ($referer && str_contains($referer, '/connexion')) {
        wp_safe_redirect(add_query_arg('erreur', '1', home_url('/connexion/')));
        exit;
    }
});
add_filter('authenticate', static function ($user, $username, $password) {
    // Champs vides soumis depuis notre page → même traitement que l'échec.
    if (did_action('login_form_login') && ($username === '' || $password === '')
        && wp_get_referer() && str_contains((string) wp_get_referer(), '/connexion')) {
        wp_safe_redirect(add_query_arg('erreur', '1', home_url('/connexion/')));
        exit;
    }
    return $user;
}, 30, 3);

/** Coordonnées administrables (valeurs originales par défaut). */
function idv_contact(string $key): string
{
    $defaults = [
        'phone'   => '06 61 48 62 67',
        'email'   => 'contact@info-devis.fr',
        'address' => '45 Rue des Boulets, 75011 Paris, France',
    ];
    return (string) get_option('idv_contact_' . $key, $defaults[$key] ?? '');
}

/**
 * Barre d'admin masquée sur le front pour les clients et artisans
 * (le site original n'en a pas ; l'équipe garde son propre réglage
 * via infodevis-admin).
 */
add_filter('show_admin_bar', static function ($show) {
    $idv_user = wp_get_current_user();
    if ($idv_user->exists() && array_intersect(['client', 'artisan'], (array) $idv_user->roles)) {
        return false;
    }
    return $show;
});
