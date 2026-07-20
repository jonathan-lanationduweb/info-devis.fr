<?php
/**
 * Info Devis Core — rédaction d'articles par les artisans (page « Mes articles »).
 *
 * Les articles sont des posts WordPress natifs (post_type `post`) rédigés par
 * l'artisan connecté. Soumission → statut `pending` (validation admin avant
 * publication). Réservé aux plans payants ; quota 3 / 30 jours en Silver, illimité
 * en Gold. Un rejet = repassage en `draft` + note admin (_idc_admin_note).
 */

if (!defined('ABSPATH')) {
    exit;
}

const IDC_BLOG_SILVER_QUOTA = 3; // articles / 30 jours

/** Plan normalisé de la fiche. */
function idc_blog_plan(?WP_Post $fiche): string
{
    if (!$fiche) {
        return 'gratuit';
    }
    $plan = get_post_meta($fiche->ID, '_idc_plan', true) ?: 'gratuit';
    if (in_array($plan, ['gold', 'illimite', 'pro'], true)) {
        return 'gold';
    }
    return $plan === 'silver' ? 'silver' : 'gratuit';
}

/** L'artisan peut-il rédiger (Silver ou Gold) ? */
function idc_blog_allowed(?WP_Post $fiche): bool
{
    return in_array(idc_blog_plan($fiche), ['silver', 'gold'], true);
}

/** Nombre d'articles (publiés ou en attente) sur les 30 derniers jours. */
function idc_blog_quota_used(int $author_id): int
{
    $q = new WP_Query([
        'post_type'      => 'post',
        'author'         => $author_id,
        'post_status'    => ['publish', 'pending'],
        'date_query'     => [['after' => '30 days ago']],
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);
    return (int) $q->post_count;
}

/** Articles de l'artisan (tous statuts), plus récents d'abord. */
function idc_blog_articles(int $author_id): array
{
    return get_posts([
        'post_type'      => 'post',
        'author'         => $author_id,
        'post_status'    => ['publish', 'pending', 'draft'],
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}

/** HTML simple autorisé dans le contenu d'un article. */
function idc_blog_allowed_html(): array
{
    return [
        'h2' => [], 'h3' => [], 'h4' => [],
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [],
        'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [],
        'a' => ['href' => [], 'title' => [], 'target' => [], 'rel' => []],
    ];
}

/* ── Enregistrer / mettre à jour un article ────────────────────────────── */
add_action('admin_post_idc_blog_save', static function (): void {
    $list = home_url('/dashboard/artisan/blog/');
    $fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;

    if (!$fiche || !isset($_POST['idc_blog_nonce']) || !wp_verify_nonce($_POST['idc_blog_nonce'], 'idc_blog_save')) {
        wp_safe_redirect(add_query_arg('err', 'nonce', $list));
        exit;
    }
    if (!idc_blog_allowed($fiche)) {
        wp_safe_redirect(add_query_arg('err', 'plan', $list));
        exit;
    }

    $user_id = get_current_user_id();
    $post_id = (int) ($_POST['id'] ?? 0);
    $editing = false;
    if ($post_id) {
        $existing = get_post($post_id);
        if (!$existing || $existing->post_type !== 'post' || (int) $existing->post_author !== $user_id) {
            wp_safe_redirect(add_query_arg('err', 'introuvable', $list));
            exit;
        }
        $editing = true;
    }

    // Quota Silver : uniquement à la création (pas sur une ré-édition).
    if (!$editing && idc_blog_plan($fiche) === 'silver' && idc_blog_quota_used($user_id) >= IDC_BLOG_SILVER_QUOTA) {
        wp_safe_redirect(add_query_arg(['err' => 'limit', 'max' => IDC_BLOG_SILVER_QUOTA], $list));
        exit;
    }

    $title   = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
    $excerpt = sanitize_textarea_field(wp_unslash($_POST['excerpt'] ?? ''));
    $content = wp_kses(wp_unslash($_POST['content'] ?? ''), idc_blog_allowed_html());
    if ('' === $title || '' === trim(wp_strip_all_tags($content))) {
        wp_safe_redirect(add_query_arg('err', 'vide', $list));
        exit;
    }

    $data = [
        'post_title'   => $title,
        'post_excerpt' => $excerpt,
        'post_content' => $content,
        'post_type'    => 'post',
        'post_status'  => 'pending', // toujours re-soumis pour validation
        'post_author'  => $user_id,
    ];
    if ($editing) {
        $data['ID'] = $post_id;
        $post_id = wp_update_post($data, true);
    } else {
        $post_id = wp_insert_post($data, true);
    }
    if (is_wp_error($post_id)) {
        wp_safe_redirect(add_query_arg('err', 'interne', $list));
        exit;
    }

    // Un nouvel envoi efface la note de rejet précédente.
    delete_post_meta($post_id, '_idc_admin_note');

    // Image de couverture : upload prioritaire, sinon URL externe (sideload).
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    if (!empty($_FILES['cover_file']['name'])) {
        if (($_FILES['cover_file']['size'] ?? 0) <= 5 * 1024 * 1024) {
            $aid = media_handle_upload('cover_file', $post_id, [], [
                'test_form' => false,
                'mimes'     => ['jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'],
            ]);
            if (!is_wp_error($aid)) {
                set_post_thumbnail($post_id, $aid);
                delete_post_meta($post_id, '_idc_cover_url');
            }
        }
    } else {
        $cover_url = esc_url_raw(wp_unslash($_POST['cover_image'] ?? ''));
        if ($cover_url) {
            $aid = media_sideload_image($cover_url, $post_id, null, 'id');
            if (!is_wp_error($aid)) {
                set_post_thumbnail($post_id, $aid);
                delete_post_meta($post_id, '_idc_cover_url');
            } else {
                // Sideload impossible (hors-ligne) : on garde l'URL en secours.
                update_post_meta($post_id, '_idc_cover_url', $cover_url);
            }
        }
    }

    // Prévenir l'admin qu'un article attend validation.
    if (function_exists('idc_send_mail')) {
        idc_send_mail('article_pending_admin', get_option('admin_email'), [
            '{titre}' => $title,
            '{nom}'   => wp_get_current_user()->display_name,
        ]);
    }

    wp_safe_redirect(add_query_arg('saved', '1', $list));
    exit;
});

/* ── Supprimer un article (AJAX, propriétaire uniquement) ───────────────── */
add_action('wp_ajax_idc_blog_delete', static function (): void {
    if (!isset($_POST['idc_blog_nonce']) || !wp_verify_nonce($_POST['idc_blog_nonce'], 'idc_blog_delete')) {
        wp_send_json(['success' => false, 'error' => 'Session expirée.'], 403);
    }
    $post_id = (int) ($_POST['id'] ?? 0);
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'post' || (int) $post->post_author !== get_current_user_id()) {
        wp_send_json(['success' => false, 'error' => 'Article introuvable.'], 403);
    }
    wp_trash_post($post_id);
    wp_send_json(['success' => true]);
});
