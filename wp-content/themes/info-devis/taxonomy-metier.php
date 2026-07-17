<?php
/**
 * Page catégorie de travaux — reproduction fidèle du comportement original :
 * chaque métier a sa vue slug (views/categories/slugs/{slug}.php).
 * - 16 métiers partagent la même structure (slug-standard + config par slug) ;
 * - amenagements-exterieurs et maconnerie ont leur mise en page propre ;
 * - un métier créé après coup retombe sur la vue universelle (show.php).
 */

require_once get_template_directory() . '/inc/categorie-config.php';

get_header();

$idv_term = get_queried_object();
$idv_ctx  = [
    'term'           => $idv_term,
    'devis_url'      => add_query_arg('metier', $idv_term->slug, home_url('/devis/')),
    'categories_url' => home_url('/categories/'),
    'top_artisans'   => idv_categorie_top_artisans((int) $idv_term->term_id),
    'related'        => idv_categorie_related((int) $idv_term->term_id),
];

$idv_configs = idv_categorie_slug_config();

if ('amenagements-exterieurs' === $idv_term->slug) {
    get_template_part('template-parts/categorie/amenagements-exterieurs', null, $idv_ctx);
} elseif ('maconnerie' === $idv_term->slug) {
    get_template_part('template-parts/categorie/maconnerie', null, $idv_ctx);
} elseif (isset($idv_configs[$idv_term->slug])) {
    $idv_ctx['cfg'] = $idv_configs[$idv_term->slug];
    get_template_part('template-parts/categorie/slug-standard', null, $idv_ctx);
} else {
    get_template_part('template-parts/categorie/universelle', null, $idv_ctx);
}

get_footer();
