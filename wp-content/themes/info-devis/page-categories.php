<?php
/**
 * Page « Nos Métiers & Expertises » — reproduction fidèle de views/categories/index.php.
 */

get_header();

$idv_terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0, 'orderby' => 'name', 'order' => 'ASC']);
if (is_wp_error($idv_terms)) {
    $idv_terms = [];
}
$idv_images   = idv_category_images();
$idv_fallback = IDV_THEME_URI . '/assets/images/metier.png';
$idv_img      = static fn(string $slug): string => $idv_images[$slug] ?? $idv_fallback;

// Hauteurs bento alternées (identiques à l'original).
$idv_heights = [400, 450, 380, 420, 480, 360, 500, 380, 420, 480, 400, 350, 450, 400, 380, 500, 420];
?>

<div class="min-h-screen">

  <!-- ── Hero Section ────────────────────────────────────────── -->
  <section class="relative pt-24 pb-8 px-8 max-w-7xl mx-auto overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

      <div class="space-y-8 z-10">
        <span class="inline-block px-4 py-1.5 bg-tertiary-container text-on-tertiary-container rounded-full text-xs font-bold tracking-widest uppercase font-body">
          Le Curateur de l'Excellence
        </span>
        <h1 class="text-6xl md:text-8xl leading-[1.05] tracking-tighter text-on-surface">
          Nos Métiers <br>
          <span class="italic text-primary">&amp; Expertises</span>
        </h1>
        <p class="text-xl text-stone-500 max-w-md font-light leading-relaxed">
          Une sélection rigoureuse d'artisans d'exception pour sublimer votre patrimoine. Découvrez notre catalogue de services haut de gamme.
        </p>
      </div>

      <div class="relative group h-[500px] w-full rounded-2xl overflow-hidden bg-surface-container-high">
        <img class="w-full h-full object-cover grayscale-[20%] group-hover:scale-105 transition-transform duration-1000"
          src="<?php echo esc_url($idv_fallback); ?>"
          alt="Artisans InfoDevis">
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        <div class="absolute bottom-8 left-8 text-white">
          <p class="text-xs font-bold uppercase tracking-[0.2em] mb-2">Signature Quality</p>
          <h2 class="text-3xl font-serif">L'Art de Bien Bâtir</h2>
        </div>
      </div>

    </div>
  </section>

  <!-- ── Grille bento des métiers ────────────────────────────── -->
  <section class="pt-8 pb-16 px-8 max-w-[1400px] mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-10">

      <?php foreach (array_values($idv_terms) as $idv_i => $idv_cat) :
          $idv_slug   = $idv_cat->slug;
          $idv_h      = $idv_heights[$idv_i % count($idv_heights)];
          $idv_offset = '';
          if ($idv_i % 5 === 1) {
              $idv_offset = 'lg:mt-6';
          }
          if ($idv_i % 7 === 4) {
              $idv_offset = 'lg:-mt-8';
          }
          if ($idv_i === 6) {
              $idv_offset = 'lg:col-span-2 lg:h-[500px]'; // carte wide énergie
          }
          $idv_desc = $idv_cat->description ?: 'Artisans qualifiés disponibles près de chez vous pour tous vos travaux.';
      ?>

        <a href="<?php echo esc_url(home_url('/categorie/' . $idv_slug . '/')); ?>"
          class="group cursor-pointer <?php echo esc_attr($idv_offset); ?>">

          <!-- Image -->
          <div class="relative overflow-hidden rounded-xl bg-surface-container-low mb-5 <?php echo $idv_i === 6 ? 'h-full w-full' : ''; ?>"
            style="<?php echo $idv_i !== 6 ? 'height:' . (int) $idv_h . 'px' : ''; ?>">

            <img src="<?php echo esc_url($idv_img($idv_slug)); ?>"
              alt="<?php echo esc_attr($idv_cat->name); ?>"
              class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
              loading="lazy"
              onerror="if(!this.dataset.fb){this.dataset.fb='1';this.src='<?php echo esc_url($idv_fallback); ?>';}">

            <?php if ($idv_i === 6) : /* carte wide avec overlay */ ?>
              <div class="absolute inset-0 bg-black/20"></div>
              <div class="absolute top-8 left-8 text-white">
                <span class="px-3 py-1 border border-white/30 rounded text-[10px] uppercase tracking-widest font-bold">Innovation Durable</span>
              </div>
              <div class="absolute bottom-8 left-8 text-white max-w-sm">
                <h3 class="text-4xl font-serif mb-2"><?php echo esc_html($idv_cat->name); ?></h3>
                <p class="text-sm opacity-80 mb-4"><?php echo esc_html(mb_substr($idv_desc, 0, 100)); ?></p>
              </div>
            <?php endif; ?>
          </div>

          <?php if ($idv_i !== 6) : ?>
            <h3 class="text-2xl font-serif text-on-surface mb-2 group-hover:text-primary transition-colors">
              <?php echo esc_html($idv_cat->name); ?>
            </h3>
            <p class="text-stone-500 font-light text-sm leading-relaxed line-clamp-2">
              <?php echo esc_html(mb_substr($idv_desc, 0, 120)); ?>
            </p>
          <?php endif; ?>

        </a>

      <?php endforeach; ?>

    </div>
  </section>

  <!-- ── Citation éditoriale ──────────────────────────────────── -->
  <section class="py-24 bg-surface-container-high/30 overflow-hidden">
    <div class="max-w-5xl mx-auto px-8 relative">
      <span class="absolute -top-12 -left-4 text-[12rem] font-serif text-primary/5 select-none leading-none">"</span>
      <div class="relative z-10 text-center">
        <h2 class="text-4xl md:text-5xl font-serif leading-tight italic text-stone-800">
          « La qualité ne se négocie pas, elle se construit avec passion par les meilleurs artisans de France. »
        </h2>
        <p class="mt-8 text-xs font-bold tracking-[0.3em] text-primary uppercase">L'équipe InfoDevis</p>
      </div>
    </div>
  </section>

  <!-- ── CTA ──────────────────────────────────────────────────── -->
  <section class="max-w-7xl mx-auto px-8 mt-24 mb-24">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
      <div>
        <h2 class="text-4xl font-serif mb-6">Prêt à concrétiser votre vision ?</h2>
        <p class="text-on-surface-variant font-body text-lg">
          Nos experts vous accompagnent dans la définition de vos besoins et la sélection des meilleurs artisans locaux.
        </p>
      </div>
      <div class="flex flex-col sm:flex-row gap-4">
        <a href="<?php echo esc_url(home_url('/devis/')); ?>"
          class="bg-primary text-white px-8 py-4 rounded-lg font-label font-bold text-sm hover:opacity-90 transition-all text-center">
          Lancer un projet
        </a>
        <a href="<?php echo esc_url(home_url('/tarifs-pro/')); ?>"
          class="border border-primary text-primary px-8 py-4 rounded-lg font-label font-bold text-sm hover:bg-primary/5 transition-all text-center">
          Consulter les tarifs
        </a>
      </div>
    </div>
  </section>

</div>

<?php get_footer(); ?>
