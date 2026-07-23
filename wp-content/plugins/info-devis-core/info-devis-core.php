<?php
/**
 * Plugin Name: Info Devis Core
 * Description: Cœur métier Info Devis — fiches artisans, demandes de devis, avis clients, catégories de travaux et rôles utilisateur (artisan / client).
 * Version: 0.3.0
 * Author: Jonathan — La Nation du Web
 * Text Domain: info-devis-core
 */

if (!defined('ABSPATH')) {
    exit;
}

final class InfoDevisCore
{
    const META_PREFIX = '_idc_';

    public static function init(): void
    {
        add_action('init', [__CLASS__, 'register_taxonomies']);
        add_action('init', [__CLASS__, 'register_post_types']);
        add_action('init', [__CLASS__, 'register_roles']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_artisan', [__CLASS__, 'save_artisan_meta']);
        add_action('save_post_avis', [__CLASS__, 'save_avis_meta']);
        add_action('save_post_demande_devis', [__CLASS__, 'save_devis_meta']);
        add_action('save_post_realisation', [__CLASS__, 'save_realisation_meta']);
        add_action('save_post_rdv', [__CLASS__, 'save_rdv_meta']);

        add_filter('manage_artisan_posts_columns', [__CLASS__, 'artisan_columns']);
        add_action('manage_artisan_posts_custom_column', [__CLASS__, 'artisan_column_content'], 10, 2);
        add_filter('manage_avis_posts_columns', [__CLASS__, 'avis_columns']);
        add_action('manage_avis_posts_custom_column', [__CLASS__, 'avis_column_content'], 10, 2);
        add_filter('manage_demande_devis_posts_columns', [__CLASS__, 'devis_columns']);
        add_action('manage_demande_devis_posts_custom_column', [__CLASS__, 'devis_column_content'], 10, 2);
    }

    /* ------------------------------------------------------------------ */
    /*  Taxonomie : catégories de travaux (métiers)                        */
    /* ------------------------------------------------------------------ */

    public static function register_taxonomies(): void
    {
        register_taxonomy('metier', ['artisan', 'demande_devis', 'post'], [
            'labels' => [
                'name'          => 'Catégories de travaux',
                'singular_name' => 'Catégorie de travaux',
                'menu_name'     => 'Catégories travaux',
                'add_new_item'  => 'Ajouter une catégorie de travaux',
                'edit_item'     => 'Modifier la catégorie',
                'search_items'  => 'Rechercher une catégorie',
            ],
            'hierarchical'      => true,
            'public'            => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'categorie', 'hierarchical' => true], // URL originale : /categorie/{slug}
        ]);

        foreach (['icon', 'image', 'prix_min', 'prix_max', 'meta_title', 'meta_description'] as $key) {
            register_term_meta('metier', self::META_PREFIX . $key, [
                'type'         => 'string',
                'single'       => true,
                'show_in_rest' => true,
            ]);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Types de contenu                                                   */
    /* ------------------------------------------------------------------ */

    public static function register_post_types(): void
    {
        register_post_type('artisan', [
            'labels' => [
                'name'          => 'Artisans',
                'singular_name' => 'Artisan',
                'add_new_item'  => 'Ajouter un artisan',
                'edit_item'     => 'Modifier l’artisan',
                'search_items'  => 'Rechercher un artisan',
                'not_found'     => 'Aucun artisan trouvé',
            ],
            'public'       => true,
            'has_archive'  => 'artisans',
            'rewrite'      => ['slug' => 'artisan'],
            'menu_icon'    => 'dashicons-admin-tools',
            'menu_position' => 21,
            'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest' => true,
        ]);

        register_post_type('demande_devis', [
            'labels' => [
                'name'          => 'Demandes de devis',
                'singular_name' => 'Demande de devis',
                'add_new_item'  => 'Ajouter une demande',
                'edit_item'     => 'Modifier la demande',
                'not_found'     => 'Aucune demande trouvée',
            ],
            'public'          => false,
            'show_ui'         => true,
            'menu_icon'       => 'dashicons-media-document',
            'menu_position'   => 22,
            'supports'        => ['title', 'editor'],
            'capability_type' => 'post',
        ]);

        register_post_type('avis', [
            'labels' => [
                'name'          => 'Avis clients',
                'singular_name' => 'Avis client',
                'add_new_item'  => 'Ajouter un avis',
                'edit_item'     => 'Modifier l’avis',
                'not_found'     => 'Aucun avis trouvé',
            ],
            'public'          => false,
            'show_ui'         => true,
            'menu_icon'       => 'dashicons-star-filled',
            'menu_position'   => 23,
            'supports'        => ['title', 'editor'],
            'capability_type' => 'post',
        ]);

        register_post_type('realisation', [
            'labels' => [
                'name'          => 'Réalisations',
                'singular_name' => 'Réalisation',
                'add_new_item'  => 'Ajouter une réalisation',
                'edit_item'     => 'Modifier la réalisation',
                'not_found'     => 'Aucune réalisation trouvée',
            ],
            'public'        => true,
            'has_archive'   => 'realisations',
            'rewrite'       => ['slug' => 'realisation'],
            'menu_icon'     => 'dashicons-portfolio',
            'menu_position' => 24,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest'  => true,
            'taxonomies'    => ['metier'],
        ]);

        register_post_type('guide', [
            'labels' => [
                'name'          => 'Guides & Prix',
                'singular_name' => 'Guide',
                'add_new_item'  => 'Ajouter un guide',
                'edit_item'     => 'Modifier le guide',
                'not_found'     => 'Aucun guide trouvé',
            ],
            'public'        => true,
            'has_archive'   => 'guides',
            'rewrite'       => ['slug' => 'guides', 'with_front' => false], // URL originale : /guides/{slug}
            'menu_icon'     => 'dashicons-book',
            'menu_position' => 25,
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'author'],
            'show_in_rest'  => true,
            'taxonomies'    => ['metier'],
        ]);

        register_post_type('rdv', [
            'labels' => [
                'name'          => 'Rendez-vous',
                'singular_name' => 'Rendez-vous',
                'add_new_item'  => 'Ajouter un rendez-vous',
                'edit_item'     => 'Modifier le rendez-vous',
                'not_found'     => 'Aucun rendez-vous trouvé',
            ],
            'public'          => false,
            'show_ui'         => true,
            'menu_icon'       => 'dashicons-calendar-alt',
            'menu_position'   => 26,
            'supports'        => ['title', 'editor'],
            'capability_type' => 'post',
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Rôles utilisateur                                                  */
    /* ------------------------------------------------------------------ */

    public static function register_roles(): void
    {
        if (!get_role('artisan')) {
            add_role('artisan', 'Artisan', [
                'read'         => true,
                'upload_files' => true,
            ]);
        }
        if (!get_role('client')) {
            add_role('client', 'Client', [
                'read' => true,
            ]);
        }
        if (!get_role('gestionnaire')) {
            add_role('gestionnaire', 'Gestionnaire', [
                'read'                   => true,
                'upload_files'           => true,
                'edit_posts'             => true,
                'edit_others_posts'      => true,
                'edit_published_posts'   => true,
                'publish_posts'          => true,
                'delete_posts'           => true,
                'delete_others_posts'    => true,
                'delete_published_posts' => true,
                'edit_pages'             => true,
                'edit_published_pages'   => true,
                'manage_categories'      => true,
                'moderate_comments'      => true,
                'idc_manage'             => true,
            ]);
        }
        // Capacité de gestion Info Devis pour l'administrateur.
        $admin = get_role('administrator');
        if ($admin && !$admin->has_cap('idc_manage')) {
            $admin->add_cap('idc_manage');
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Meta boxes                                                         */
    /* ------------------------------------------------------------------ */

    public static function add_meta_boxes(): void
    {
        add_meta_box('idc_artisan', 'Informations artisan', [__CLASS__, 'render_artisan_box'], 'artisan', 'normal', 'high');
        add_meta_box('idc_avis', 'Détails de l’avis', [__CLASS__, 'render_avis_box'], 'avis', 'side', 'high');
        add_meta_box('idc_devis', 'Détails de la demande', [__CLASS__, 'render_devis_box'], 'demande_devis', 'normal', 'high');
        add_meta_box('idc_realisation', 'Détails de la réalisation', [__CLASS__, 'render_realisation_box'], 'realisation', 'side', 'high');
        add_meta_box('idc_rdv', 'Détails du rendez-vous', [__CLASS__, 'render_rdv_box'], 'rdv', 'normal', 'high');
    }

    private static function field(string $post_meta_key, int $post_id, string $label, string $type = 'text', array $options = []): void
    {
        $key   = self::META_PREFIX . $post_meta_key;
        $value = get_post_meta($post_id, $key, true);
        echo '<p style="margin:8px 0;"><label style="display:inline-block;min-width:180px;font-weight:600;" for="' . esc_attr($key) . '">' . esc_html($label) . '</label>';
        if ($type === 'select') {
            echo '<select id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">';
            foreach ($options as $opt_value => $opt_label) {
                echo '<option value="' . esc_attr($opt_value) . '"' . selected($value, $opt_value, false) . '>' . esc_html($opt_label) . '</option>';
            }
            echo '</select>';
        } elseif ($type === 'checkbox') {
            echo '<input type="checkbox" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="1"' . checked($value, '1', false) . ' />';
        } else {
            echo '<input type="' . esc_attr($type) . '" class="regular-text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
        }
        echo '</p>';
    }

    public static function render_artisan_box(WP_Post $post): void
    {
        wp_nonce_field('idc_save_artisan', 'idc_artisan_nonce');
        self::field('company_name', $post->ID, 'Raison sociale');
        self::field('siret', $post->ID, 'SIRET');
        self::field('siret_verified', $post->ID, 'SIRET vérifié', 'checkbox');
        self::field('phone', $post->ID, 'Téléphone');
        self::field('ville', $post->ID, 'Ville');
        self::field('code_postal', $post->ID, 'Code postal');
        self::field('radius_km', $post->ID, 'Rayon d’intervention (km)', 'number');
        self::field('annees_experience', $post->ID, 'Années d’expérience', 'number');
        self::field('plan', $post->ID, 'Abonnement', 'select', [
            'gratuit' => 'Gratuit', 'starter' => 'Starter', 'pro' => 'Pro',
            'illimite' => 'Illimité', 'silver' => 'Silver', 'gold' => 'Gold',
        ]);
        self::field('badge_level', $post->ID, 'Badge', 'select', [
            'referenced' => 'Référencé', 'verified' => 'Vérifié',
            'verified_pro' => 'Vérifié Pro', 'premium' => 'Premium',
        ]);
        self::field('verification_status', $post->ID, 'Statut de vérification', 'select', [
            'pending' => 'En attente', 'validated' => 'Validé', 'refused' => 'Refusé',
        ]);

        $user_id = get_post_meta($post->ID, self::META_PREFIX . 'user_id', true);
        if ($user_id && ($user = get_userdata((int) $user_id))) {
            echo '<p><strong>Compte utilisateur lié :</strong> <a href="' . esc_url(get_edit_user_link((int) $user_id)) . '">' . esc_html($user->user_email) . '</a></p>';
        }
        $rating = get_post_meta($post->ID, self::META_PREFIX . 'rating_avg', true);
        $count  = get_post_meta($post->ID, self::META_PREFIX . 'rating_count', true);
        if ($rating !== '') {
            echo '<p><strong>Note moyenne :</strong> ' . esc_html($rating) . ' / 5 (' . (int) $count . ' avis)</p>';
        }
    }

    public static function render_avis_box(WP_Post $post): void
    {
        wp_nonce_field('idc_save_avis', 'idc_avis_nonce');
        self::field('rating', $post->ID, 'Note', 'select', [
            '5' => '★★★★★', '4' => '★★★★', '3' => '★★★', '2' => '★★', '1' => '★',
        ]);
        self::field('status', $post->ID, 'Statut', 'select', [
            'pending'  => 'En attente',
            'approved' => 'Approuvé (visible)',
            'refused'  => 'Refusé',
            'reported' => 'Signalé',
            'hidden'   => 'Masqué',
        ]);
        self::field('verified', $post->ID, 'Avis vérifié', 'checkbox');
        self::field('artisan_post_id', $post->ID, 'ID fiche artisan', 'number');
        self::field('client_user_id', $post->ID, 'ID utilisateur client', 'number');
    }

    public static function render_devis_box(WP_Post $post): void
    {
        wp_nonce_field('idc_save_devis', 'idc_devis_nonce');
        self::field('reference', $post->ID, 'Référence');
        self::field('ville', $post->ID, 'Ville');
        self::field('code_postal', $post->ID, 'Code postal');
        self::field('urgency', $post->ID, 'Urgence', 'select', [
            'normal' => 'Normale', 'urgent' => 'Urgent', 'tres_urgent' => 'Très urgent',
        ]);
        self::field('budget_min', $post->ID, 'Budget min (€)', 'number');
        self::field('budget_max', $post->ID, 'Budget max (€)', 'number');
        self::field('status', $post->ID, 'Statut', 'select', [
            'pending' => 'En attente', 'sent' => 'Envoyée aux artisans', 'accepted' => 'Acceptée',
            'in_progress' => 'En cours', 'completed' => 'Terminée',
            'cancelled' => 'Annulée', 'refused' => 'Refusée',
        ]);
        self::field('client_user_id', $post->ID, 'ID utilisateur client', 'number');
    }

    public static function render_realisation_box(WP_Post $post): void
    {
        wp_nonce_field('idc_save_realisation', 'idc_realisation_nonce');
        self::field('artisan_post_id', $post->ID, 'ID fiche artisan', 'number');
        self::field('ville', $post->ID, 'Ville');
        self::field('duree', $post->ID, 'Durée des travaux');
        self::field('budget', $post->ID, 'Budget indicatif (€)', 'number');

        $artisan_id = (int) get_post_meta($post->ID, self::META_PREFIX . 'artisan_post_id', true);
        if ($artisan_id && get_post($artisan_id)) {
            echo '<p><strong>Artisan :</strong> <a href="' . esc_url(get_edit_post_link($artisan_id)) . '">' . esc_html(get_the_title($artisan_id)) . '</a></p>';
        }
        echo '<p style="color:#6b7280;font-size:12px;">Validation : utiliser le statut de publication (En attente de relecture = non visible, Publié = visible).</p>';
    }

    public static function render_rdv_box(WP_Post $post): void
    {
        wp_nonce_field('idc_save_rdv', 'idc_rdv_nonce');
        self::field('artisan_post_id', $post->ID, 'ID fiche artisan', 'number');
        self::field('client_user_id', $post->ID, 'ID utilisateur client', 'number');
        self::field('date_rdv', $post->ID, 'Date et heure (AAAA-MM-JJ HH:MM)');
        self::field('duree_min', $post->ID, 'Durée (minutes)', 'number');
        self::field('adresse', $post->ID, 'Adresse du rendez-vous');
        self::field('statut', $post->ID, 'Statut', 'select', [
            'propose'  => 'Proposé',
            'confirme' => 'Confirmé',
            'annule'   => 'Annulé',
            'termine'  => 'Terminé',
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Sauvegarde des meta                                                */
    /* ------------------------------------------------------------------ */

    private static function save_fields(int $post_id, string $nonce_field, string $nonce_action, array $text_keys, array $checkbox_keys): void
    {
        if (!isset($_POST[$nonce_field]) || !wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        foreach ($text_keys as $key) {
            $meta_key = self::META_PREFIX . $key;
            if (isset($_POST[$meta_key])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$meta_key])));
            }
        }
        foreach ($checkbox_keys as $key) {
            $meta_key = self::META_PREFIX . $key;
            update_post_meta($post_id, $meta_key, isset($_POST[$meta_key]) ? '1' : '0');
        }
    }

    public static function save_artisan_meta(int $post_id): void
    {
        self::save_fields(
            $post_id,
            'idc_artisan_nonce',
            'idc_save_artisan',
            ['company_name', 'siret', 'phone', 'ville', 'code_postal', 'radius_km', 'annees_experience', 'plan', 'badge_level', 'verification_status'],
            ['siret_verified']
        );
    }

    public static function save_avis_meta(int $post_id): void
    {
        self::save_fields(
            $post_id,
            'idc_avis_nonce',
            'idc_save_avis',
            ['rating', 'status', 'artisan_post_id', 'client_user_id'],
            ['verified']
        );
        // _idc_approved reste synchronisé pour compatibilité avec les requêtes existantes.
        if (isset($_POST['idc_avis_nonce'])) {
            $status = get_post_meta($post_id, self::META_PREFIX . 'status', true);
            update_post_meta($post_id, self::META_PREFIX . 'approved', $status === 'approved' ? '1' : '0');
        }
    }

    public static function save_devis_meta(int $post_id): void
    {
        self::save_fields(
            $post_id,
            'idc_devis_nonce',
            'idc_save_devis',
            ['reference', 'ville', 'code_postal', 'urgency', 'budget_min', 'budget_max', 'status', 'client_user_id'],
            []
        );
    }

    public static function save_realisation_meta(int $post_id): void
    {
        self::save_fields(
            $post_id,
            'idc_realisation_nonce',
            'idc_save_realisation',
            ['artisan_post_id', 'ville', 'duree', 'budget'],
            []
        );
    }

    public static function save_rdv_meta(int $post_id): void
    {
        self::save_fields(
            $post_id,
            'idc_rdv_nonce',
            'idc_save_rdv',
            ['artisan_post_id', 'client_user_id', 'date_rdv', 'duree_min', 'adresse', 'statut'],
            []
        );
    }

    /* ------------------------------------------------------------------ */
    /*  Colonnes admin                                                     */
    /* ------------------------------------------------------------------ */

    public static function artisan_columns(array $columns): array
    {
        $date = $columns['date'] ?? '';
        unset($columns['date']);
        $columns['idc_ville']  = 'Ville';
        $columns['idc_siret']  = 'SIRET';
        $columns['idc_plan']   = 'Abonnement';
        $columns['idc_rating'] = 'Note';
        $columns['date']       = $date;
        return $columns;
    }

    public static function artisan_column_content(string $column, int $post_id): void
    {
        switch ($column) {
            case 'idc_ville':
                echo esc_html(get_post_meta($post_id, self::META_PREFIX . 'ville', true));
                break;
            case 'idc_siret':
                $siret = get_post_meta($post_id, self::META_PREFIX . 'siret', true);
                echo esc_html($siret);
                if (get_post_meta($post_id, self::META_PREFIX . 'siret_verified', true) === '1') {
                    echo ' <span style="color:#00a32a;" title="SIRET vérifié">✔</span>';
                }
                break;
            case 'idc_plan':
                echo esc_html(ucfirst(get_post_meta($post_id, self::META_PREFIX . 'plan', true)));
                break;
            case 'idc_rating':
                $rating = get_post_meta($post_id, self::META_PREFIX . 'rating_avg', true);
                $count  = get_post_meta($post_id, self::META_PREFIX . 'rating_count', true);
                echo $rating !== '' ? esc_html($rating . ' / 5 (' . (int) $count . ')') : '—';
                break;
        }
    }

    public static function avis_columns(array $columns): array
    {
        $date = $columns['date'] ?? '';
        unset($columns['date']);
        $columns['idc_rating']   = 'Note';
        $columns['idc_approved'] = 'Approuvé';
        $columns['date']         = $date;
        return $columns;
    }

    public static function avis_column_content(string $column, int $post_id): void
    {
        if ($column === 'idc_rating') {
            $rating = (int) get_post_meta($post_id, self::META_PREFIX . 'rating', true);
            echo esc_html($rating ? str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) : '—');
        } elseif ($column === 'idc_approved') {
            echo get_post_meta($post_id, self::META_PREFIX . 'approved', true) === '1'
                ? '<span style="color:#00a32a;">✔ Oui</span>'
                : '<span style="color:#d63638;">✘ Non</span>';
        }
    }

    public static function devis_columns(array $columns): array
    {
        $date = $columns['date'] ?? '';
        unset($columns['date']);
        $columns['idc_reference'] = 'Référence';
        $columns['idc_ville']     = 'Ville';
        $columns['idc_status']    = 'Statut';
        $columns['date']          = $date;
        return $columns;
    }

    public static function devis_column_content(string $column, int $post_id): void
    {
        $labels = [
            'pending' => 'En attente', 'sent' => 'Envoyée', 'accepted' => 'Acceptée',
            'in_progress' => 'En cours', 'completed' => 'Terminée',
            'cancelled' => 'Annulée', 'refused' => 'Refusée',
        ];
        if ($column === 'idc_reference') {
            echo esc_html(get_post_meta($post_id, self::META_PREFIX . 'reference', true));
        } elseif ($column === 'idc_ville') {
            echo esc_html(get_post_meta($post_id, self::META_PREFIX . 'ville', true));
        } elseif ($column === 'idc_status') {
            $status = get_post_meta($post_id, self::META_PREFIX . 'status', true);
            echo esc_html($labels[$status] ?? $status);
        }
    }
}

InfoDevisCore::init();

require_once __DIR__ . '/includes/front.php';
require_once __DIR__ . '/includes/matching.php';
require_once __DIR__ . '/includes/siret.php';
require_once __DIR__ . '/includes/emails.php';
require_once __DIR__ . '/includes/profil.php';
require_once __DIR__ . '/includes/artisan-front.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/access-control.php';
require_once __DIR__ . '/includes/tracking.php';
require_once __DIR__ . '/includes/rdv.php';
require_once __DIR__ . '/includes/dispos.php';
require_once __DIR__ . '/includes/avis.php';
require_once __DIR__ . '/includes/inscription.php';
require_once __DIR__ . '/includes/messages.php';
require_once __DIR__ . '/includes/signature.php';
require_once __DIR__ . '/includes/notifications.php';
require_once __DIR__ . '/includes/documents.php';
require_once __DIR__ . '/includes/apparence.php';
require_once __DIR__ . '/includes/artisan-blog.php';
require_once __DIR__ . '/includes/stripe.php';
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/admin-pages.php';

register_activation_hook(__FILE__, static function (): void {
    InfoDevisCore::register_taxonomies();
    InfoDevisCore::register_post_types();
    InfoDevisCore::register_roles();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
