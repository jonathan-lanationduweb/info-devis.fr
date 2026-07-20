<?php
/**
 * Annuaire public des artisans — reproduction fidèle de views/pages/professionnels.php.
 * Filtres : q (recherche), ville, categorie (slug), sort, pg (pagination).
 */

get_header();

$idv_q     = sanitize_text_field(wp_unslash($_GET['q'] ?? ''));
$idv_ville = sanitize_text_field(wp_unslash($_GET['ville'] ?? ''));
$idv_categ = sanitize_title($_GET['categorie'] ?? '');
$idv_sort  = in_array($_GET['sort'] ?? '', ['recommended', 'rating', 'avis', 'distance'], true) ? $_GET['sort'] : 'recommended';
$idv_page  = max(1, (int) ($_GET['pg'] ?? 1));
$idv_per   = 12;

$idv_args = [
    'post_type'      => 'artisan',
    'post_status'    => 'publish',
    'posts_per_page' => $idv_per,
    'paged'          => $idv_page,
];
if ($idv_q) {
    $idv_args['s'] = $idv_q;
}
if ($idv_ville) {
    $idv_args['meta_query'][] = ['key' => '_idc_ville', 'value' => $idv_ville, 'compare' => 'LIKE'];
}
if ($idv_categ) {
    $idv_args['tax_query'] = [['taxonomy' => 'metier', 'field' => 'slug', 'terms' => $idv_categ]];
}
switch ($idv_sort) {
    case 'rating':
        $idv_args['meta_key'] = '_idc_rating_avg';
        $idv_args['orderby']  = 'meta_value_num';
        $idv_args['order']    = 'DESC';
        break;
    case 'avis':
        $idv_args['meta_key'] = '_idc_rating_count';
        $idv_args['orderby']  = 'meta_value_num';
        $idv_args['order']    = 'DESC';
        break;
    case 'distance':
        $idv_args['orderby'] = 'date';
        $idv_args['order']   = 'DESC';
        break;
}
$idv_query = new WP_Query($idv_args);
$idv_total = (int) $idv_query->found_posts;

// Artisans Gold (recommandés) — uniquement en tri par défaut sans recherche.
$idv_gold = [];
if ($idv_sort === 'recommended' && !$idv_q && !$idv_ville) {
    $idv_gold = get_posts([
        'post_type'      => 'artisan',
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'meta_query'     => [['key' => '_idc_plan', 'value' => ['gold', 'pro', 'illimite'], 'compare' => 'IN']],
    ]);
}

$idv_terms    = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0, 'orderby' => 'name']);
$idv_base_url = home_url('/professionnels/');
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-16">

    <!-- Header -->
    <header class="mb-16">
        <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold leading-[1.1] mb-8" style="font-family:'Newsreader',serif;">
            Découvrez nos <span class="pill-title">architectes</span> et <br class="hidden md:block">
            <span class="pill-title">professionnels</span> disponibles
        </h1>
        <p class="max-w-3xl text-on-surface-variant text-xl leading-relaxed">
            Explorez notre sélection d'artisans et de professionnels du bâtiment inscrits sur InfoDevis.
            Consultez leurs profils, découvrez leurs réalisations et contactez-les facilement pour donner vie à vos projets
            d'architecture ou de rénovation.
        </p>
    </header>

    <!-- Recherche + filtres -->
    <section class="mb-16">
        <form method="GET" action="<?php echo esc_url($idv_base_url); ?>" class="search-pill">
            <div class="search-pill__field">
                <span class="material-symbols-outlined">search</span>
                <input type="text" name="q" class="search-pill__input"
                       placeholder="Quel professionnel recherchez-vous ?"
                       value="<?php echo esc_attr($idv_q); ?>">
            </div>
            <div class="search-pill__divider"></div>
            <div class="search-pill__field">
                <span class="material-symbols-outlined">location_on</span>
                <input type="text" name="ville" class="search-pill__input"
                       placeholder="Où ? (Ville, code postal...)"
                       value="<?php echo esc_attr($idv_ville); ?>">
            </div>
            <button type="submit" class="search-pill__btn">
                Rechercher
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
            </button>
        </form>

        <div class="mt-6 flex flex-wrap gap-3 idv-filter-pills">
            <a href="<?php echo esc_url($idv_base_url); ?>" class="btn-outline-pill"
               style="padding:8px 20px;font-size:13px;">
                <span class="material-symbols-outlined" style="font-size:18px;">tune</span>
                Tous
            </a>
            <?php foreach ((array) $idv_terms as $idv_term) :
                $idv_active = ($idv_categ === $idv_term->slug);
            ?>
                <a href="<?php echo esc_url(add_query_arg('categorie', $idv_term->slug, $idv_base_url)); ?>"
                   class="btn-outline-pill"
                   style="padding:8px 20px;font-size:13px;<?php echo $idv_active ? 'border-color:#207752;color:#207752;background:rgba(32,119,82,0.06);' : ''; ?>">
                    <?php echo esc_html($idv_term->name); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php $idv_active_count = (int) (bool) $idv_categ + (int) (bool) $idv_q + (int) (bool) $idv_ville; ?>
        <button type="button" class="idv-filters-trigger" data-sheet-open="idv-filters-sheet" aria-haspopup="dialog">
            <span class="material-symbols-outlined" style="font-size:20px;">tune</span>
            Filtres &amp; tri
            <?php if ($idv_active_count) : ?><span class="idv-filters-trigger__count"><?php echo $idv_active_count; ?></span><?php endif; ?>
        </button>
    </section>

    <!-- Bottom-sheet filtres (mobile) -->
    <div id="idv-filters-sheet" class="idv-sheet" role="dialog" aria-modal="true" aria-label="Filtres" aria-hidden="true">
        <div class="idv-sheet__handle"></div>
        <div class="idv-sheet__head">
            <span class="idv-sheet__title">Filtres &amp; tri</span>
            <button type="button" class="idv-sheet__close" data-sheet-close aria-label="Fermer"><span class="material-symbols-outlined">close</span></button>
        </div>

        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2 mt-2">Métier</p>
        <div class="idv-sheet__filters">
            <a href="<?php echo esc_url($idv_base_url); ?>" class="idv-sheet__filter <?php echo $idv_categ === '' ? 'idv-sheet__filter--active' : ''; ?>">
                <span class="material-symbols-outlined">tune</span> Tous les métiers
            </a>
            <?php foreach ((array) $idv_terms as $idv_term) : ?>
                <a href="<?php echo esc_url(add_query_arg('categorie', $idv_term->slug, $idv_base_url)); ?>"
                   class="idv-sheet__filter <?php echo $idv_categ === $idv_term->slug ? 'idv-sheet__filter--active' : ''; ?>">
                    <?php echo idv_cat_icon_html($idv_term->slug); ?> <?php echo esc_html($idv_term->name); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2 mt-6">Trier par</p>
        <div class="idv-sheet__filters">
            <?php
            $idv_sorts = ['recommended' => 'Recommandés', 'rating' => 'Mieux notés', 'avis' => "Plus d'avis", 'distance' => 'Plus récents'];
            foreach ($idv_sorts as $idv_sk => $idv_slabel) :
                $idv_sort_url = add_query_arg(array_filter(['q' => $idv_q, 'ville' => $idv_ville, 'categorie' => $idv_categ, 'sort' => $idv_sk]), $idv_base_url);
            ?>
                <a href="<?php echo esc_url($idv_sort_url); ?>" class="idv-sheet__filter <?php echo $idv_sort === $idv_sk ? 'idv-sheet__filter--active' : ''; ?>">
                    <span class="material-symbols-outlined"><?php echo $idv_sort === $idv_sk ? 'radio_button_checked' : 'radio_button_unchecked'; ?></span>
                    <?php echo esc_html($idv_slabel); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Compteur résultats + tri -->
    <div class="flex items-center justify-between gap-4 mb-8 flex-wrap">
        <p class="text-sm text-on-surface-variant">
            <strong><?php echo $idv_total; ?></strong> professionnel<?php echo $idv_total > 1 ? 's' : ''; ?> trouvé<?php echo $idv_total > 1 ? 's' : ''; ?>
        </p>
        <form method="GET" action="<?php echo esc_url($idv_base_url); ?>" class="flex items-center gap-2">
            <?php foreach (['q' => $idv_q, 'ville' => $idv_ville, 'categorie' => $idv_categ] as $idv_k => $idv_v) :
                if ($idv_v) : ?>
                    <input type="hidden" name="<?php echo esc_attr($idv_k); ?>" value="<?php echo esc_attr($idv_v); ?>">
                <?php endif;
            endforeach; ?>
            <label class="text-xs uppercase tracking-widest text-on-surface-variant font-bold">Trier par</label>
            <select name="sort" onchange="this.form.submit()"
                    class="bg-surface-container border-none rounded-full px-4 py-2 text-sm font-semibold focus:ring-2 focus:ring-primary/20">
                <option value="recommended" <?php selected($idv_sort, 'recommended'); ?>>Recommandés</option>
                <option value="rating" <?php selected($idv_sort, 'rating'); ?>>Mieux notés</option>
                <option value="avis" <?php selected($idv_sort, 'avis'); ?>>Plus d'avis</option>
                <option value="distance" <?php selected($idv_sort, 'distance'); ?>>Plus récents</option>
            </select>
        </form>
    </div>

    <!-- Artisans recommandés (Top Gold) -->
    <?php if ($idv_gold) : ?>
        <section class="mb-12">
            <div class="flex items-center gap-3 mb-6">
                <i class="fa-solid fa-medal text-amber-500" style="font-size:24px" aria-hidden="true"></i>
                <h2 class="text-2xl font-bold" style="font-family:'Newsreader',serif;font-style:italic;">Artisans recommandés</h2>
                <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200">Top 5 Gold</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <?php foreach ($idv_gold as $idv_pro) {
                    get_template_part('template-parts/card', 'pro', ['artisan' => $idv_pro]);
                } ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Tous les pros -->
    <?php if (!$idv_query->have_posts()) : ?>
        <div class="text-center py-24 text-on-surface-variant">
            <span class="material-symbols-outlined" style="font-size:48px;opacity:0.4;">search_off</span>
            <p class="mt-4 text-lg">Aucun professionnel ne correspond à votre recherche.</p>
            <a href="<?php echo esc_url($idv_base_url); ?>" class="btn-primary-pill mt-6">Réinitialiser les filtres</a>
        </div>
    <?php else : ?>
        <?php if ($idv_gold) : ?>
            <h2 class="text-xl font-bold mb-6" style="font-family:'Newsreader',serif;">Tous les professionnels</h2>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php while ($idv_query->have_posts()) :
                $idv_query->the_post();
                get_template_part('template-parts/card', 'pro', ['artisan' => get_post()]);
            endwhile;
            wp_reset_postdata(); ?>
        </div>

        <?php if ($idv_query->max_num_pages > 1) : ?>
            <nav class="flex justify-center gap-2 mt-16" aria-label="Pagination">
                <?php echo paginate_links([
                    'base'      => esc_url(add_query_arg('pg', '%#%', $idv_base_url)),
                    'format'    => '',
                    'current'   => $idv_page,
                    'total'     => (int) $idv_query->max_num_pages,
                    'prev_text' => '←',
                    'next_text' => '→',
                    'type'      => 'plain',
                ]); ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
