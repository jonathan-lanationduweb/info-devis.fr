<?php
/**
 * Liste des articles du blog — reproduction fidèle de views/blog/index.php :
 * en-tête éditorial, grille de cards avec icône Font Awesome par catégorie,
 * temps de lecture, bandeau CTA final.
 */

get_header();

// Mapping catégorie (nom français) → icône Font Awesome 7 (identique à l'original).
$idv_cat_fa = [
    'Toiture'                 => 'fa-house-chimney-crack',
    'Chauffage'               => 'fa-fire',
    'Maçonnerie'              => 'fa-trowel-bricks',
    'Plomberie'               => 'fa-faucet-drip',
    'Isolation'               => 'fa-shield',
    'Rénovation'              => 'fa-house-chimney',
    'Électricité'             => 'fa-bolt',
    'Peinture'                => 'fa-paint-roller',
    'Jardinage'               => 'fa-seedling',
    'Carrelage'               => 'fa-grip',
    'Climatisation'           => 'fa-snowflake',
    'Menuiserie'              => 'fa-hammer',
    'Aménagements Extérieurs' => 'fa-tree',
    'Énergies Renouvelables'  => 'fa-solar-panel',
    'Sécurité & Domotique'    => 'fa-shield-halved',
    'Déménagement & Services' => 'fa-truck',
    'Traitement & Protection' => 'fa-spray-can-sparkles',
];

/** Catégorie d'un article : terme métier lié, sinon « Conseils ». */
$idv_post_cat = static function (int $post_id): string {
    $terms = get_the_terms($post_id, 'metier');
    return ($terms && !is_wp_error($terms)) ? $terms[0]->name : 'Conseils';
};
$idv_post_icon = static function (string $cat) use ($idv_cat_fa): string {
    return 'fa-solid ' . ($idv_cat_fa[$cat] ?? 'fa-newspaper');
};
// Temps de lecture : l'original affiche toujours le fallback « 5 min » sur la
// liste (read_time n'existe pas en base, seul l'article calcule le temps réel).
?>

<div class="pt-32 pb-24">

  <!-- En-tête -->
  <header class="max-w-screen-2xl mx-auto px-8 mb-20 text-center">
    <span class="inline-block mb-4 text-primary font-bold tracking-wide uppercase text-sm">
      <i class="fa-solid fa-pen-nib mr-1" aria-hidden="true"></i> Conseils &amp; guides travaux
    </span>
    <h1 class="font-headline text-5xl md:text-7xl text-on-surface font-bold mb-6 tracking-tight">
      Blog InfoDevis
    </h1>
    <p class="font-body text-lg md:text-xl text-on-surface-variant max-w-3xl mx-auto leading-relaxed">
      Guides de prix, conseils de pros et astuces pour réussir vos travaux en toute sérénité.
    </p>
  </header>

  <!-- Grille articles -->
  <section class="max-w-screen-2xl mx-auto px-8">
    <?php if (!have_posts()) : ?>
      <div class="text-center py-24">
        <i class="fa-solid fa-newspaper text-primary mb-6" style="font-size:64px;opacity:0.6" aria-hidden="true"></i>
        <h2 class="font-headline text-3xl mb-4">Aucun article pour le moment</h2>
        <p class="text-on-surface-variant">Revenez bientôt, nos experts préparent du contenu pour vous.</p>
      </div>
    <?php else : ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-16">
        <?php while (have_posts()) : the_post();
            $idv_cat  = $idv_post_cat(get_the_ID());
            $idv_icon = $idv_post_icon($idv_cat);
        ?>
          <article class="group">

            <!-- Image -->
            <a href="<?php the_permalink(); ?>"
              class="relative aspect-[16/10] overflow-hidden rounded-xl mb-6 bg-surface-container shadow-sm block">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('large', ['class' => 'w-full h-full object-cover transition-transform duration-700 group-hover:scale-105', 'loading' => 'lazy']); ?>
              <?php else : ?>
                <div class="w-full h-full bg-gradient-to-br from-primary/10 to-surface-container-high flex items-center justify-center">
                  <i class="<?php echo esc_attr($idv_icon); ?> text-primary" style="font-size:48px;opacity:0.65;" aria-hidden="true"></i>
                </div>
              <?php endif; ?>
            </a>

            <!-- Meta -->
            <div class="flex items-center justify-between mb-4">
              <span class="font-label text-xs font-bold uppercase tracking-widest text-primary bg-primary/10 px-3 py-1 rounded-full inline-flex items-center gap-1.5">
                <i class="<?php echo esc_attr($idv_icon); ?>" style="font-size:11px;" aria-hidden="true"></i>
                <?php echo esc_html($idv_cat); ?>
              </span>
              <span class="text-on-surface-variant text-xs font-medium">
                <?php echo esc_html(get_the_date('d/m/Y')); ?>
              </span>
            </div>

            <!-- Titre -->
            <h3 class="font-headline text-2xl font-bold mb-3 text-on-surface group-hover:text-primary transition-colors leading-snug">
              <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>

            <!-- Extrait -->
            <p class="text-on-surface-variant text-sm mb-4 line-clamp-2 leading-relaxed">
              <?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?>
            </p>

            <!-- Pied -->
            <div class="flex items-center justify-between border-t border-outline-variant/10 pt-4">
              <div class="flex items-center gap-2 text-on-surface-variant/80">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="flex-shrink-0">
                  <circle cx="12" cy="12" r="10" />
                  <polyline points="12 6 12 12 16 14" />
                </svg>
                <span class="text-xs font-medium">5 min de lecture</span>
              </div>
              <a href="<?php the_permalink(); ?>"
                class="text-primary text-xs font-label font-bold uppercase tracking-widest hover:gap-2 flex items-center gap-1 transition-all">
                Lire →
              </a>
            </div>

          </article>
        <?php endwhile; ?>
      </div>
      <div class="mt-16 text-center"><?php the_posts_pagination(['prev_text' => '←', 'next_text' => '→']); ?></div>
    <?php endif; ?>
  </section>

  <!-- CTA Banner -->
  <section class="max-w-screen-2xl mx-auto px-8 mt-32">
    <div class="relative overflow-hidden bg-primary py-20 px-12 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-12 text-on-primary">
      <div class="relative z-10 max-w-2xl">
        <h2 class="font-headline text-4xl md:text-5xl font-bold mb-6">Prêt à lancer vos travaux ?</h2>
        <p class="text-lg opacity-90 leading-relaxed font-body">
          Obtenez jusqu'à 5 devis gratuits d'artisans qualifiés près de chez vous.
        </p>
      </div>
      <div class="relative z-10">
        <a href="<?php echo esc_url(home_url('/devis/')); ?>"
          class="bg-white text-primary px-10 py-5 rounded-lg text-lg font-bold shadow-xl hover:scale-105 transition-transform duration-300 inline-block">
          Demander un devis gratuit
        </a>
      </div>
      <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-black/10 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
    </div>
  </section>

</div>

<?php get_footer(); ?>
