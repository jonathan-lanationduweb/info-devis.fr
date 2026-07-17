<?php
/**
 * Plugin Name: InfoDevis Admin
 * Description: Administration SaaS d'InfoDevis — remplace l'expérience wp-admin par une interface moderne. WordPress reste le moteur ; désactiver ce plugin restitue l'admin classique.
 * Version: 1.1.0
 * Author: Jonathan — La Nation du Web
 * Text Domain: infodevis-admin
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

define('IDA_VERSION', '1.1.0');
define('IDA_PATH', plugin_dir_path(__FILE__));
define('IDA_URL', plugin_dir_url(__FILE__));
define('IDA_APP_SLUG', 'infodevis-admin'); // URL : /infodevis-admin/

/** Contrôle d'accès unique de toute la nouvelle admin. */
function ida_can_manage(): bool
{
    return current_user_can('idc_manage');
}

/** URL de l'application. */
function ida_app_url(string $hash = ''): string
{
    return home_url('/' . IDA_APP_SLUG . '/' . ($hash !== '' ? '#' . $hash : ''));
}

/* FAQ : CPT léger géré uniquement par InfoDevis Admin. */
add_action('init', static function (): void {
    register_post_type('faq', [
        'labels' => [
            'name'          => 'FAQ',
            'singular_name' => 'Question FAQ',
        ],
        'public'          => false,
        'show_ui'         => false,
        'supports'        => ['title', 'editor', 'page-attributes'],
        'capability_type' => 'post',
    ]);
});

require_once IDA_PATH . 'includes/home-options.php';
require_once IDA_PATH . 'includes/app-shell.php';
require_once IDA_PATH . 'includes/rest-api.php';
require_once IDA_PATH . 'includes/rest-lists.php';
require_once IDA_PATH . 'includes/rest-entities.php';
require_once IDA_PATH . 'includes/rest-dashboard.php';
require_once IDA_PATH . 'includes/rest-settings.php';
require_once IDA_PATH . 'includes/email-logs.php';
require_once IDA_PATH . 'includes/rest-emails.php';
require_once IDA_PATH . 'includes/wp-admin-integration.php';
