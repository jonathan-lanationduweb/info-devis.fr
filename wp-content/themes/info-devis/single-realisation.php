<?php
/**
 * Détail d'une réalisation — reproduction 1:1 de views/pages/projet_detail.php :
 * fil d'ariane, en-tête (badge métier + lieu + année + titre serif + résumé),
 * grande image, colonne « À propos de ce projet », sidebar (Caractéristiques,
 * L'artisan, favoris). Données : CPT realisation + metas _idc_*.
 */

get_header();

while (have_posts()) :
    the_post();
    $idv_id = get_the_ID();

    // Compteur de vues (hors admin).
    if (!current_user_can('manage_options')) {
        $idv_views = (int) get_post_meta($idv_id, '_idc_vues', true) + 1;
        update_post_meta($idv_id, '_idc_vues', $idv_views);
    } else {
        $idv_views = (int) get_post_meta($idv_id, '_idc_vues', true);
    }

    $idv_terms   = get_the_terms($idv_id, 'metier');
    $idv_cat     = ($idv_terms && !is_wp_error($idv_terms)) ? $idv_terms[0]->name : '';
    $idv_ville   = (string) get_post_meta($idv_id, '_idc_ville', true);
    $idv_surface = (int) get_post_meta($idv_id, '_idc_surface_m2', true);
    $idv_budget  = (float) get_post_meta($idv_id, '_idc_budget', true);
    $idv_duree   = (int) preg_replace('/\D/', '', (string) get_post_meta($idv_id, '_idc_duree', true));
    $idv_resume  = get_the_excerpt();

    // Fiche artisan liée.
    $idv_fiche_id = (int) get_post_meta($idv_id, '_idc_artisan_post_id', true);
    $idv_fiche    = $idv_fiche_id ? get_post($idv_fiche_id) : null;
    $idv_company  = $idv_fiche ? get_the_title($idv_fiche) : '';
    $idv_a_ville  = $idv_fiche ? (string) get_post_meta($idv_fiche_id, '_idc_ville', true) : '';
    $idv_avatar   = ($idv_fiche && has_post_thumbnail($idv_fiche_id))
        ? get_the_post_thumbnail_url($idv_fiche_id, 'thumbnail')
        : IDV_THEME_URI . '/assets/images/avatar-default.png';

    $idv_cover = has_post_thumbnail($idv_id)
        ? get_the_post_thumbnail_url($idv_id, 'large')
        : IDV_THEME_URI . '/assets/images/metier.png';
    ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-12">

  <!-- Fil d'ariane -->
  <nav class="mb-8 text-sm text-on-surface-variant flex items-center gap-2 flex-wrap">
    <a href="<?php echo esc_url(home_url('/professionnels/')); ?>" class="hover:text-primary">Professionnels</a>
    <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
    <?php if ($idv_fiche) : ?>
      <a href="<?php echo esc_url(get_permalink($idv_fiche)); ?>" class="hover:text-primary"><?php echo esc_html($idv_company); ?></a>
      <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
    <?php endif; ?>
    <span class="text-on-surface"><?php the_title(); ?></span>
  </nav>

  <!-- En-tête -->
  <header class="mb-12">
    <div class="flex items-center gap-3 mb-4 flex-wrap">
      <?php if ($idv_cat) : ?>
        <span class="bg-primary/10 text-primary px-3 py-1 text-[10px] font-bold tracking-[0.15em] uppercase rounded-full"><?php echo esc_html($idv_cat); ?></span>
      <?php endif; ?>
      <?php if ($idv_ville) : ?>
        <span class="text-sm text-on-surface-variant flex items-center gap-1">
          <span class="material-symbols-outlined" style="font-size:16px;">location_on</span><?php echo esc_html($idv_ville); ?>
        </span>
      <?php endif; ?>
      <span class="text-sm text-on-surface-variant">· <?php echo esc_html(get_the_date('Y')); ?></span>
    </div>
    <h1 class="text-4xl md:text-5xl font-bold leading-tight font-headline"><?php the_title(); ?></h1>
    <?php if ($idv_resume) : ?>
      <p class="mt-4 text-xl text-on-surface-variant italic font-headline"><?php echo esc_html($idv_resume); ?></p>
    <?php endif; ?>
  </header>

  <!-- Image de couverture -->
  <section class="mb-16">
    <div class="rounded-2xl overflow-hidden" style="background:#f8f8f8;aspect-ratio:16/10;max-height:640px;">
      <img src="<?php echo esc_url($idv_cover); ?>" alt="<?php echo esc_attr(get_the_title()); ?>"
           class="w-full h-full object-cover"
           onerror="this.src='<?php echo esc_url(IDV_THEME_URI . '/assets/images/metier.png'); ?>'">
    </div>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
    <!-- Description -->
    <div class="lg:col-span-2">
      <h2 class="text-2xl font-semibold mb-4 font-headline">À propos de ce projet</h2>
      <div class="text-on-surface-variant leading-relaxed space-y-4">
        <?php the_content(); ?>
      </div>
    </div>

    <!-- Sidebar -->
    <aside class="space-y-6">
      <!-- Caractéristiques -->
      <div class="bg-white p-6 rounded-2xl border border-outline-variant/10 shadow-sm">
        <h3 class="text-sm font-bold uppercase tracking-widest text-on-surface-variant mb-4">Caractéristiques</h3>
        <dl class="space-y-3">
          <?php if ($idv_surface) : ?>
            <div class="flex justify-between text-sm border-b border-outline-variant/10 pb-3"><dt class="text-on-surface-variant">Surface</dt><dd class="font-semibold"><?php echo esc_html($idv_surface); ?> m²</dd></div>
          <?php endif; ?>
          <?php if ($idv_budget) : ?>
            <div class="flex justify-between text-sm border-b border-outline-variant/10 pb-3"><dt class="text-on-surface-variant">Budget</dt><dd class="font-semibold"><?php echo esc_html(number_format($idv_budget, 0, ',', ' ')); ?> €</dd></div>
          <?php endif; ?>
          <?php if ($idv_duree) : ?>
            <div class="flex justify-between text-sm border-b border-outline-variant/10 pb-3"><dt class="text-on-surface-variant">Durée</dt><dd class="font-semibold"><?php echo esc_html($idv_duree); ?> jours</dd></div>
          <?php endif; ?>
          <div class="flex justify-between text-sm"><dt class="text-on-surface-variant">Vues</dt><dd class="font-semibold"><?php echo esc_html($idv_views); ?></dd></div>
        </dl>
      </div>

      <!-- L'artisan -->
      <?php if ($idv_fiche) : ?>
        <div class="bg-white p-6 rounded-2xl border border-outline-variant/10 shadow-sm">
          <h3 class="text-sm font-bold uppercase tracking-widest text-on-surface-variant mb-4">L'artisan</h3>
          <div class="flex items-center gap-3 mb-4">
            <img src="<?php echo esc_url($idv_avatar); ?>" class="w-12 h-12 rounded-full object-cover" alt="<?php echo esc_attr($idv_company); ?>"
                 onerror="this.src='<?php echo esc_url(IDV_THEME_URI . '/assets/images/avatar-default.png'); ?>'">
            <div>
              <p class="font-semibold text-sm"><?php echo esc_html($idv_company); ?></p>
              <p class="text-xs text-on-surface-variant"><?php echo esc_html($idv_a_ville); ?></p>
            </div>
          </div>
          <a href="<?php echo esc_url(get_permalink($idv_fiche)); ?>"
             class="w-full bg-primary text-on-primary rounded-full py-3 flex items-center justify-center gap-2 font-bold text-sm hover:opacity-90 transition-all">
            Voir le profil
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
          </a>
        </div>
      <?php endif; ?>

      <!-- Favori (localStorage, hors-ligne) -->
      <button type="button" class="idv-tap w-full p-4 rounded-2xl border border-outline-variant/20 flex items-center justify-center gap-2 hover:border-primary transition-colors"
              data-fav
              data-fav-id="<?php echo (int) $idv_id; ?>"
              data-fav-type="realisation"
              data-fav-title="<?php echo esc_attr(get_the_title()); ?>"
              data-fav-url="<?php echo esc_url(get_permalink()); ?>"
              data-fav-img="<?php echo esc_url($idv_cover); ?>"
              aria-pressed="false">
        <span class="material-symbols-outlined">favorite</span>
        <span class="font-semibold text-sm" data-fav-label>Ajouter aux favoris</span>
      </button>
    </aside>
  </div>

  <!-- Autres réalisations du même artisan -->
  <?php
  if ($idv_fiche_id) :
      $idv_autres = get_posts([
          'post_type'   => 'realisation',
          'post_status' => 'publish',
          'numberposts' => 3,
          'post__not_in' => [$idv_id],
          'meta_key'    => '_idc_artisan_post_id',
          'meta_value'  => $idv_fiche_id,
      ]);
      if ($idv_autres) : ?>
        <section class="mt-24">
          <h2 class="text-3xl font-semibold mb-8 font-headline">Autres réalisations de <?php echo esc_html($idv_company); ?></h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($idv_autres as $idv_p) : ?>
              <a href="<?php echo esc_url(get_permalink($idv_p)); ?>" class="group block rounded-2xl overflow-hidden bg-surface-container-low hover:shadow-lg transition-all">
                <div class="overflow-hidden" style="aspect-ratio:4/3;background:#f8f8f8;">
                  <img src="<?php echo esc_url(has_post_thumbnail($idv_p) ? get_the_post_thumbnail_url($idv_p, 'medium') : IDV_THEME_URI . '/assets/images/metier.png'); ?>"
                       class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="<?php echo esc_attr(get_the_title($idv_p)); ?>">
                </div>
                <div class="p-5">
                  <h3 class="font-headline text-lg font-semibold group-hover:text-primary transition-colors"><?php echo esc_html(get_the_title($idv_p)); ?></h3>
                  <p class="text-sm text-on-surface-variant mt-1 line-clamp-2"><?php echo esc_html(get_the_excerpt($idv_p)); ?></p>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif;
  endif;
  ?>

</main>

    <?php
endwhile;

get_footer();
