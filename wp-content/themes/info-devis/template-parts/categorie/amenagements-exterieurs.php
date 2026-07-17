<?php
/**
 * Page catégorie Aménagements Extérieurs — port 1:1 de
 * views/categories/slugs/amenagements-exterieurs.php (mise en page spécifique).
 */

$idv_term    = $args['term'];
$idv_devis   = $args['devis_url'];
$idv_catalog = $args['categories_url'];
$idv_top     = $args['top_artisans'];
$idv_related = $args['related'];

$idv_desc = $idv_term->description !== '' ? $idv_term->description : 'Des solutions architecturales pour sublimer vos espaces de vie exterieurs en havres de paix.';

$idv_cover = file_exists(get_template_directory() . '/assets/images/categories/amenagements-exterieurs/cover.jpg')
    ? IDV_THEME_URI . '/assets/images/categories/amenagements-exterieurs/cover.jpg'
    : IDV_THEME_URI . '/assets/images/metier.png';
?>
<main class="pt-20">

<!-- Hero -->
<section class="relative min-h-[870px] flex items-center overflow-hidden bg-surface-container-low">
  <div class="absolute inset-0 z-0">
    <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1400&q=80" alt="Amenagements exterieurs">
    <div class="absolute inset-0 bg-gradient-to-r from-background via-background/40 to-transparent"></div>
  </div>
  <div class="relative z-10 max-w-[1440px] mx-auto px-8 w-full">
    <div class="max-w-2xl">
      <span class="font-label tracking-[0.2em] uppercase text-xs text-primary mb-6 block font-semibold">Art de Vivre &amp; Habitat</span>
      <h1 class="font-headline text-6xl md:text-8xl text-on-surface leading-tight mb-8 italic">Amenagements<br><span class="not-italic">Exterieurs</span></h1>
      <p class="text-xl font-body text-on-surface-variant mb-10 max-w-lg leading-relaxed">
        <?php echo esc_html($idv_desc); ?>
      </p>
      <div class="flex flex-wrap gap-4 mt-6">
        <a href="<?php echo esc_url($idv_devis); ?>" class="bg-primary text-on-primary px-10 py-5 rounded-xl font-label tracking-widest uppercase text-xs hover:opacity-90 transition-all shadow-2xl shadow-primary/20">Explorer les solutions</a>
        <a href="<?php echo esc_url(home_url('/tarifs-pro/')); ?>" class="border border-outline-variant/30 text-on-surface px-10 py-5 rounded-xl font-label tracking-widest uppercase text-xs hover:bg-surface-container-low transition-all">Consulter les tarifs</a>
      </div>
    </div>
  </div>
</section>

<!-- Hero image catégorie -->
<section class="py-16 px-8 max-w-[1440px] mx-auto">
  <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-8">
    <div class="max-w-xl">
      <h2 class="font-headline text-5xl mb-6">L'Excellence du Plein Air</h2>
      <p class="text-on-surface-variant leading-relaxed text-lg">
        Découvrez nos réalisations architecturales : de la terrasse en bois noble à la véranda bioclimatique,
        en passant par les pergolas et bassins.
      </p>
    </div>
    <a href="<?php echo esc_url($idv_catalog); ?>" class="font-label text-xs uppercase tracking-widest text-primary border-b border-primary/20 pb-1 hover:border-primary transition-all">Voir tout le catalogue</a>
  </div>
  <div class="relative overflow-hidden rounded-2xl" style="aspect-ratio:21/9;">
    <img src="<?php echo esc_url($idv_cover); ?>" alt="Aménagements extérieurs"
         class="w-full h-full object-cover" loading="lazy">
    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
  </div>
</section>

<!-- Services -->
<section class="bg-surface-container-low py-24 px-8">
  <div class="max-w-[1440px] mx-auto">
    <div class="text-center mb-16">
      <span class="font-label text-primary tracking-[0.2em] uppercase text-xs mb-4 block">Nos Expertises</span>
      <h2 class="font-headline text-5xl">Des prestations <span class="italic">sur-mesure</span></h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-px bg-outline-variant/10 overflow-hidden rounded-3xl border border-outline-variant/10 shadow-sm">
      <?php foreach (['Terrasses de Prestige', 'Jardins et Verandas', 'Pergolas Innovantes', 'Bassins et Piscines'] as $idv_svc) : ?>
      <div class="bg-surface p-10 hover:bg-surface-container-high transition-colors group">
        <div class="w-12 h-12 rounded-full border border-primary/20 flex items-center justify-center mb-8 group-hover:bg-primary transition-all">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-primary group-hover:text-on-primary transition-colors"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h3 class="text-xl font-headline mb-4"><?php echo esc_html($idv_svc); ?></h3>
        <p class="text-on-surface-variant font-body text-sm leading-relaxed mb-6">Nos artisans interviennent avec la plus grande precision.</p>
        <a href="<?php echo esc_url($idv_devis); ?>" class="text-primary font-label text-xs tracking-widest uppercase flex items-center gap-2 group-hover:gap-4 transition-all">En savoir plus &rarr;</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Expertise 360 -->
<section class="py-24 px-8">
  <div class="max-w-[1440px] mx-auto grid grid-cols-1 lg:grid-cols-2 gap-24 items-center">
    <div class="relative">
      <h2 class="font-headline text-5xl mb-12 leading-tight italic">Une expertise <br><span class="not-italic">a 360 degres</span></h2>
      <div class="space-y-8">
        <?php foreach ([
            ['Conception Architecturale', 'Plans personnalises et modelisation 3D pour visualiser votre futur espace.'],
            ['Materiaux Durables', 'Selection rigoureuse de bois certifies, pierres naturelles et revetements ecologiques.'],
            ['Installation Certifiee', 'Mise en oeuvre par nos artisans hautement qualifies, garanties decennales incluses.'],
        ] as $idv_av) : ?>
        <div class="flex gap-6 items-start">
          <span class="p-3 bg-white rounded-full shadow-md flex-shrink-0">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-primary"><polyline points="20 6 9 17 4 12"/></svg>
          </span>
          <div>
            <h4 class="font-headline text-xl mb-2"><?php echo esc_html($idv_av[0]); ?></h4>
            <p class="text-on-surface-variant text-sm leading-relaxed"><?php echo esc_html($idv_av[1]); ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80" alt="Construction" class="w-full aspect-[3/4] object-cover rounded-xl mt-12">
      <img src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=600&q=80" alt="Materiaux" class="w-full aspect-[3/4] object-cover rounded-xl">
    </div>
  </div>
</section>

<!-- Processus -->
<section class="py-24 px-8 max-w-[1440px] mx-auto text-center">
  <div class="max-w-3xl mx-auto mb-20">
    <span class="font-label tracking-widest uppercase text-xs text-primary mb-6 block">Notre Methode</span>
    <h2 class="font-headline text-5xl mb-8">Votre projet, notre engagement</h2>
    <p class="text-on-surface-variant text-lg">Nous simplifions la complexite pour ne laisser place qu&apos;a l&apos;emotion.</p>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-16 relative">
    <div class="hidden md:block absolute top-12 left-1/4 right-1/4 h-px bg-outline-variant/30"></div>
    <?php foreach ([
        ['Diagnostic et Vision', 'Analyse de votre terrain et de vos besoins pour definir une direction coherente.'],
        ['Selection et Devis', 'Mise en relation avec les experts et remise de devis detailles et transparents.'],
        ['Suivi et Livraison', 'Accompagnement tout au long des travaux pour une livraison cle en main.'],
    ] as $idv_i => $idv_s) : ?>
    <div class="relative">
      <div class="w-16 h-16 bg-primary text-on-primary rounded-full flex items-center justify-center mx-auto mb-8 font-headline text-2xl shadow-lg relative z-10"><?php echo $idv_i + 1; ?></div>
      <h3 class="font-headline text-2xl mb-4"><?php echo esc_html($idv_s[0]); ?></h3>
      <p class="text-on-surface-variant text-sm leading-relaxed"><?php echo esc_html($idv_s[1]); ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Top artisans -->
<?php if (!empty($idv_top)) : ?>
<section class="py-16 px-8 bg-surface-container-low">
  <div class="max-w-[1440px] mx-auto">
    <h2 class="font-headline text-3xl text-center mb-10">Artisans recommandes</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ($idv_top as $idv_a) :
          $idv_first  = get_user_meta($idv_a->post_author, 'first_name', true) ?: 'A';
          $idv_ville  = get_post_meta($idv_a->ID, '_idc_ville', true);
          $idv_rating = (float) get_post_meta($idv_a->ID, '_idc_rating_avg', true);
          $idv_full   = (int) round($idv_rating);
      ?>
      <div class="bg-white rounded-2xl p-6 border border-outline-variant/20 hover:-translate-y-1 hover:shadow-lg transition-all">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#0d2b33] to-primary flex items-center justify-center text-white font-bold text-lg">
            <?php echo esc_html(mb_strtoupper(mb_substr($idv_first, 0, 1))); ?>
          </div>
          <div>
            <div class="font-semibold text-sm"><?php echo esc_html(get_the_title($idv_a)); ?></div>
            <div class="text-xs text-on-surface-variant"><?php echo esc_html($idv_ville); ?></div>
          </div>
        </div>
        <div class="flex items-center gap-2 mb-3">
          <span class="text-yellow-400"><?php echo str_repeat('&#9733;', $idv_full) . str_repeat('&#9734;', 5 - $idv_full); ?></span>
          <span class="text-sm font-semibold"><?php echo number_format($idv_rating, 1); ?></span>
        </div>
        <a href="<?php echo esc_url($idv_devis); ?>" class="block w-full text-center bg-primary text-on-primary py-2.5 rounded-full text-sm font-semibold hover:opacity-90 transition-all">Demander un devis</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Categories liees -->
<?php if (!empty($idv_related)) : ?>
<section class="py-16 px-8">
  <div class="max-w-[1440px] mx-auto">
    <h2 class="font-headline text-3xl mb-8 text-center">Autres corps de metiers</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <?php foreach ($idv_related as $idv_rel) : ?>
      <a href="<?php echo esc_url(get_term_link($idv_rel)); ?>" class="group bg-surface-container-low border border-outline-variant/20 rounded-xl p-6 text-center hover:border-primary hover:-translate-y-1 hover:shadow-md transition-all">
        <h3 class="font-headline text-lg mb-1 group-hover:text-primary transition-colors"><?php echo esc_html($idv_rel->name); ?></h3>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA final -->
<section class="pb-24 px-8">
  <div class="max-w-[1440px] mx-auto bg-primary rounded-[2rem] p-16 md:p-24 relative overflow-hidden text-center text-on-primary">
    <div class="absolute inset-0 opacity-10 pointer-events-none">
      <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-[120px]"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-primary-fixed rounded-full blur-[120px]"></div>
    </div>
    <div class="relative z-10 max-w-2xl mx-auto">
      <h2 class="font-headline text-5xl md:text-6xl mb-10 italic">Pret a transformer votre exterieur ?</h2>
      <p class="font-body text-on-primary/80 text-lg mb-12 leading-relaxed">Rejoignez les milliers de proprietaires qui ont fait confiance a InfoDevis pour creer leur espace de vie exterieur.</p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="<?php echo esc_url($idv_devis); ?>" class="bg-white text-primary px-12 py-5 rounded-lg font-label tracking-widest uppercase text-sm hover:bg-primary-container transition-all shadow-2xl shadow-black/10">Obtenir une etude gratuite</a>
        <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="border border-white/30 text-white px-12 py-5 rounded-lg font-label tracking-widest uppercase text-sm hover:bg-white/10 transition-all">Contacter un conseiller</a>
      </div>
    </div>
  </div>
</section>

</main>
