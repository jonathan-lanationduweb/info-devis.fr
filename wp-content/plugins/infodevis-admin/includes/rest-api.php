<?php
/**
 * API REST InfoDevis Admin — enregistrement des routes + helpers partagés.
 * Namespace : idc/v1 (déjà utilisé par info-devis-core pour le webhook Stripe).
 * Toutes les routes exigent la capacité idc_manage.
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDA_NS = 'idc/v1';

/** permission_callback commun. */
function ida_rest_permission(): bool
{
    return ida_can_manage();
}

/** Raccourci d'enregistrement de route. */
function ida_route(string $route, $methods, callable $cb): void
{
    register_rest_route(IDA_NS, '/admin' . $route, [
        'methods'             => $methods,
        'callback'            => $cb,
        'permission_callback' => 'ida_rest_permission',
    ]);
}

add_action('rest_api_init', static function (): void {
    /* Boot + recherche */
    ida_route('/bootstrap', 'GET', 'ida_rest_bootstrap');
    ida_route('/search', 'GET', 'ida_rest_search');

    /* Dashboard */
    ida_route('/dashboard', 'GET', 'ida_rest_dashboard');

    /* Listes + entités génériques */
    ida_route('/list/(?P<type>[a-z_]+)', 'GET', 'ida_rest_list');
    ida_route('/item/(?P<type>[a-z_]+)/(?P<id>\d+)', 'GET', 'ida_rest_item_get');
    ida_route('/item/(?P<type>[a-z_]+)/(?P<id>\d+)', 'POST', 'ida_rest_item_save');
    ida_route('/item/(?P<type>[a-z_]+)', 'POST', 'ida_rest_item_create');
    ida_route('/item/(?P<type>[a-z_]+)/(?P<id>\d+)/action', 'POST', 'ida_rest_item_action');

    /* Métiers */
    ida_route('/metiers', 'GET', 'ida_rest_metiers');
    ida_route('/metiers', 'POST', 'ida_rest_metier_create');
    ida_route('/metiers/reorder', 'POST', 'ida_rest_metiers_reorder');
    ida_route('/metiers/(?P<id>\d+)', 'POST', 'ida_rest_metier_save');
    ida_route('/metiers/(?P<id>\d+)', 'DELETE', 'ida_rest_metier_delete');

    /* Clients */
    ida_route('/clients/(?P<id>\d+)', 'GET', 'ida_rest_client_get');

    /* RDV (calendrier) */
    ida_route('/rdv/calendar', 'GET', 'ida_rest_rdv_calendar');

    /* Accueil, réglages, emails, SEO, paiements, sauvegardes */
    ida_route('/home', 'GET', 'ida_rest_home_get');
    ida_route('/home', 'POST', 'ida_rest_home_save');
    ida_route('/settings', 'GET', 'ida_rest_settings_get');
    ida_route('/settings', 'POST', 'ida_rest_settings_save');
    ida_route('/emails', 'GET', 'ida_rest_emails_get');
    ida_route('/emails', 'POST', 'ida_rest_emails_save');
    ida_route('/seo', 'GET', 'ida_rest_seo');
    ida_route('/payments', 'GET', 'ida_rest_payments');
    ida_route('/backups', 'GET', 'ida_rest_backups');
    ida_route('/backups', 'POST', 'ida_rest_backup_create');
});

/* ---------------------------------------------------------------------- */
/*  Référentiels partagés                                                  */
/* ---------------------------------------------------------------------- */

/** Types gérés par les écrans génériques → post_type WordPress. */
function ida_types(): array
{
    return [
        'article'     => 'post',
        'guide'       => 'guide',
        'page'        => 'page',
        'artisan'     => 'artisan',
        'demande'     => 'demande_devis',
        'avis'        => 'avis',
        'realisation' => 'realisation',
        'rdv'         => 'rdv',
        'faq'         => 'faq',
    ];
}

function ida_post_type(string $type): ?string
{
    return ida_types()[$type] ?? null;
}

/** Énumérations (reprises d'info-devis-core, à ne pas modifier). */
function ida_enums(): array
{
    return [
        'demande_status' => [
            'pending' => 'En attente', 'sent' => 'Envoyée', 'accepted' => 'Acceptée',
            'in_progress' => 'En cours', 'completed' => 'Terminée',
            'cancelled' => 'Annulée', 'refused' => 'Refusée',
        ],
        'avis_status' => [
            'pending' => 'En attente', 'approved' => 'Approuvé', 'refused' => 'Refusé',
            'reported' => 'Signalé', 'hidden' => 'Masqué',
        ],
        'plan' => [
            'gratuit' => 'Gratuit', 'starter' => 'Starter', 'pro' => 'Pro',
            'illimite' => 'Illimité', 'silver' => 'Silver', 'gold' => 'Gold',
        ],
        'badge' => [
            'referenced' => 'Référencé', 'verified' => 'Vérifié',
            'verified_pro' => 'Vérifié Pro', 'premium' => 'Premium',
        ],
        'verification' => [
            'pending' => 'En attente', 'validated' => 'Validé', 'refused' => 'Refusé',
        ],
        'rdv_status' => [
            'propose' => 'Proposé', 'confirme' => 'Confirmé',
            'annule' => 'Annulé', 'termine' => 'Terminé',
        ],
        'urgency' => [
            'normal' => 'Normale', 'urgent' => 'Urgent', 'tres_urgent' => 'Très urgent',
        ],
    ];
}

/* ---------------------------------------------------------------------- */
/*  Helpers                                                                */
/* ---------------------------------------------------------------------- */

function ida_meta(int $post_id, string $key): string
{
    return (string) get_post_meta($post_id, '_idc_' . $key, true);
}

/** Texte brut pour l'API : décode les entités stockées par WordPress (&amp;…). */
function ida_text(string $value): string
{
    return wp_specialchars_decode($value, ENT_QUOTES);
}

/** Miniature (ou vide). */
function ida_thumb(int $post_id, string $size = 'thumbnail'): string
{
    return (string) (get_the_post_thumbnail_url($post_id, $size) ?: '');
}

/** Temps de lecture estimé. */
function ida_reading_time(string $content): string
{
    $words = str_word_count(wp_strip_all_tags($content));
    return max(1, (int) ceil($words / 200)) . ' min';
}

/** Date relative en français. */
function ida_ago(string $mysql_date): string
{
    return sprintf('il y a %s', human_time_diff(strtotime($mysql_date), current_time('timestamp')));
}

/** Statut de publication → libellé. */
function ida_post_status_label(string $status): string
{
    return [
        'publish' => 'Publié', 'draft' => 'Brouillon', 'pending' => 'En attente',
        'future' => 'Planifié', 'private' => 'Privé', 'trash' => 'Corbeille',
        'auto-draft' => 'Brouillon',
    ][$status] ?? $status;
}

/** SEO Yoast d'un post (title/description + verdict rempli ou non). */
function ida_yoast(int $post_id): array
{
    $title = (string) get_post_meta($post_id, '_yoast_wpseo_title', true);
    $desc  = (string) get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
    return ['title' => $title, 'description' => $desc, 'ok' => $desc !== ''];
}

/** Nombre de posts d'un type (statuts utiles). */
function ida_count(string $post_type, string $status = 'publish'): int
{
    $counts = wp_count_posts($post_type);
    return (int) ($counts->{$status} ?? 0);
}

/** Réponse d'erreur standard. */
function ida_error(string $message, int $code = 400): WP_Error
{
    return new WP_Error('ida_error', $message, ['status' => $code]);
}
