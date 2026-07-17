<?php
/**
 * Template Name: Espace client — Dispos artisans
 * Artisans vérifiés et disponibles, avec lien vers leur fiche.
 */

$idv_user = idv_require_role('client');

get_header();
get_template_part('template-parts/sidebar', 'client', ['user' => $idv_user]);

$idv_pros = get_posts([
    'post_type'      => 'artisan',
    'post_status'    => 'publish',
    'posts_per_page' => 24,
    'orderby'        => 'title',
    'order'          => 'ASC',
]);
?>

<div class="md:ml-72 pt-32 pb-24 md:pb-20 px-8 md:px-16 min-h-screen">

  <header class="mb-16 border-b border-outline-variant/10 pb-12">
    <div class="flex items-center gap-4 mb-4">
      <span class="bg-primary/10 text-primary px-3 py-1 text-[10px] font-bold tracking-[0.2em] uppercase rounded-full">Disponibilités</span>
      <span class="h-px w-12 bg-outline-variant/30"></span>
    </div>
    <h1 class="text-5xl md:text-6xl font-headline italic font-bold text-on-surface leading-tight">Artisans disponibles</h1>
    <p class="mt-4 text-on-surface-variant font-body text-lg leading-relaxed max-w-lg">
      Les artisans vérifiés de votre région, prêts à intervenir sur vos projets.
    </p>
  </header>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-4xl">
    <?php foreach ($idv_pros as $idv_pro) :
        $idv_dispo   = get_post_meta($idv_pro->ID, '_idc_is_available', true) !== '0';
        $idv_ville   = get_post_meta($idv_pro->ID, '_idc_ville', true);
        $idv_metiers = get_the_terms($idv_pro->ID, 'metier') ?: [];
    ?>
      <div class="bg-white rounded-xl border border-outline-variant/15 p-4 hover:border-primary/30 transition-all">
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">
            <?php echo esc_html(mb_strtoupper(mb_substr(get_the_title($idv_pro), 0, 1))); ?>
          </div>
          <div class="min-w-0">
            <p class="font-semibold text-sm truncate"><?php echo esc_html(get_the_title($idv_pro)); ?></p>
            <?php if ($idv_ville) : ?>
              <p class="text-xs text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">location_on</span>
                <?php echo esc_html($idv_ville); ?>
              </p>
            <?php endif; ?>
          </div>
          <?php if ($idv_dispo) : ?>
            <span class="ml-auto flex-shrink-0 bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full">✓ Dispo</span>
          <?php endif; ?>
        </div>
        <?php if ($idv_metiers) : ?>
          <p class="text-[10px] text-on-surface-variant mb-2 truncate">
            <?php echo esc_html(implode(', ', array_map(static fn($t) => $t->name, $idv_metiers))); ?>
          </p>
        <?php endif; ?>
        <div class="flex items-center justify-between mt-2">
          <a href="<?php echo esc_url(add_query_arg('pro', $idv_pro->ID, home_url('/prendre-rdv/'))); ?>"
            class="text-[10px] font-bold bg-primary text-on-primary px-3 py-1.5 rounded-lg flex items-center gap-1 hover:opacity-90 transition-all">
            <span class="material-symbols-outlined text-sm">event</span> Prendre RDV
          </a>
          <a href="<?php echo esc_url(get_permalink($idv_pro)); ?>"
            class="text-[10px] font-bold text-primary flex items-center gap-0.5 hover:gap-1 transition-all">
            Voir la fiche <span class="material-symbols-outlined text-sm">arrow_forward</span>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php get_footer(); ?>
