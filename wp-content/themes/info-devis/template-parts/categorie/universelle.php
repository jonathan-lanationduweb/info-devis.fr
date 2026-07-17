<?php
/**
 * Page catégorie — vue universelle (port de views/categories/show.php),
 * utilisée en secours pour un métier créé sans vue slug d'origine.
 */

$idv_term = $args['term'];
$idv_name = $idv_term->name;
$idv_slug = $idv_term->slug;

$idv_images     = idv_category_images();
$idv_hero_image = get_term_meta($idv_term->term_id, '_idc_image', true) ?: ($idv_images[$idv_slug] ?? IDV_THEME_URI . '/assets/images/metier.png');
$idv_subtitle   = $idv_term->description;

$idv_artisan_count = (int) $idv_term->count;

$idv_devis_url = $args['devis_url'];
$idv_pros_url  = add_query_arg('categorie', $idv_slug, home_url('/professionnels/'));

// Valeurs par défaut originales (services + confiance).
$idv_services = [
    ['title' => 'Conseil personnalisé', 'desc' => 'Une approche sur-mesure pour chaque projet.'],
    ['title' => 'Devis transparent', 'desc' => 'Une estimation détaillée et sans engagement.'],
    ['title' => 'Travaux soignés', 'desc' => 'Des artisans rigoureux et expérimentés.'],
    ['title' => 'Suivi de chantier', 'desc' => 'Un accompagnement de A à Z jusqu\'à la livraison.'],
];
$idv_trust = [
    ['title' => 'Artisans certifiés', 'desc' => 'Une sélection rigoureuse des meilleurs professionnels de votre région.'],
    ['title' => 'Devis gratuit', 'desc' => 'Une estimation transparente et détaillée, sans engagement de votre part.'],
    ['title' => 'Garantie décennale', 'desc' => 'Tous nos travaux sont couverts par une assurance responsabilité civile décennale.'],
];
?>

<div class="pt-24">

  <!-- ── HERO ──────────────────────────────────────────────────────────── -->
  <section class="relative px-8 mb-24 max-w-[1440px] mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
      <div class="lg:col-span-5 z-10">
        <span class="font-label text-primary tracking-[0.2em] uppercase text-xs mb-6 block">
          Nos artisans qualifiés
        </span>
        <h1 class="text-6xl lg:text-7xl font-headline font-light leading-tight mb-8 text-on-surface">
          <?php echo esc_html('Devis ' . $idv_name . ' gratuit'); ?>
        </h1>
        <?php if ($idv_subtitle) : ?>
          <p class="text-xl font-body text-on-surface-variant mb-10 max-w-md">
            <?php echo esc_html($idv_subtitle); ?>
          </p>
        <?php endif; ?>
        <div class="flex flex-wrap gap-4">
          <a href="<?php echo esc_url($idv_devis_url); ?>"
             class="bg-primary text-on-primary px-8 py-4 rounded-xl font-label tracking-widest uppercase text-xs hover:opacity-90 transition-all">
            Demander un devis
          </a>
          <a href="<?php echo esc_url($idv_pros_url); ?>"
             class="border border-outline-variant/30 text-on-surface px-8 py-4 rounded-xl font-label tracking-widest uppercase text-xs hover:bg-surface-container-low transition-all">
            Voir les artisans
          </a>
        </div>
        <?php if ($idv_artisan_count > 0) : ?>
          <p class="text-sm text-on-surface-variant mt-6">
            🛡️ <strong><?php echo $idv_artisan_count; ?>+ artisans vérifiés</strong> dans cette catégorie
          </p>
        <?php endif; ?>
      </div>
      <div class="lg:col-span-7 relative h-[500px] rounded-2xl overflow-hidden group">
        <img class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
             src="<?php echo esc_url($idv_hero_image); ?>"
             alt="<?php echo esc_attr($idv_name); ?>"
             onerror="this.src='<?php echo esc_url(IDV_THEME_URI . '/assets/images/metier.png'); ?>'">
        <div class="absolute inset-0 bg-gradient-to-r from-background/40 to-transparent"></div>
      </div>
    </div>
  </section>

  <!-- ── SERVICES ──────────────────────────────────────────────────────── -->
  <section class="bg-surface-container-low py-24 px-8">
    <div class="max-w-[1440px] mx-auto">
      <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-8">
        <div class="max-w-xl">
          <span class="font-label text-primary tracking-[0.2em] uppercase text-xs mb-4 block">Nos expertises</span>
          <h2 class="text-5xl font-headline leading-tight">
            Des prestations <span class="italic">sur-mesure</span> pour chaque projet.
          </h2>
        </div>
        <div class="text-on-surface-variant font-body italic border-l border-primary/30 pl-8 max-w-md">
          L'art de bâtir pour durer, avec des artisans qualifiés et engagés.
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-px bg-outline-variant/10 overflow-hidden rounded-3xl border border-outline-variant/10 shadow-sm">
        <?php foreach ($idv_services as $idv_svc) : ?>
          <div class="bg-surface p-10 hover:bg-surface-container-high transition-colors group">
            <div class="w-12 h-12 rounded-full border border-primary/20 flex items-center justify-center mb-8 group-hover:bg-primary transition-all">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-primary group-hover:text-on-primary transition-colors">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            </div>
            <h3 class="text-xl font-headline mb-4"><?php echo esc_html($idv_svc['title']); ?></h3>
            <p class="text-on-surface-variant font-body text-sm leading-relaxed mb-6">
              <?php echo esc_html($idv_svc['desc']); ?>
            </p>
            <a href="<?php echo esc_url($idv_devis_url); ?>"
               class="text-primary font-label text-xs tracking-widest uppercase flex items-center gap-2 group-hover:gap-4 transition-all">
              En savoir plus →
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ── TRUST + PROCESSUS ─────────────────────────────────────────────── -->
  <section class="py-24 px-8 max-w-[1440px] mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
      <div>
        <h2 class="text-4xl font-headline mb-12 italic">Pourquoi nous faire confiance ?</h2>
        <div class="space-y-10">
          <?php foreach ($idv_trust as $idv_t) : ?>
            <div class="flex gap-6 items-start">
              <div class="p-3 bg-tertiary-container rounded-lg flex-shrink-0">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-on-tertiary-container">
                  <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                  <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
              </div>
              <div>
                <h4 class="font-headline text-xl mb-2"><?php echo esc_html($idv_t['title']); ?></h4>
                <p class="text-on-surface-variant text-sm leading-relaxed"><?php echo esc_html($idv_t['desc']); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="bg-surface-container rounded-[2rem] p-12 relative overflow-hidden">
        <h3 class="text-3xl font-headline mb-10">Le Processus <span class="italic text-primary">InfoDevis</span></h3>
        <div class="space-y-8 relative">
          <div class="absolute left-6 top-8 bottom-8 w-px bg-primary/20"></div>
          <?php foreach ([
              ['Demande de devis', 'Décrivez votre projet en moins de 2 minutes.'],
              ['Mise en relation', "Jusqu'à 5 artisans locaux qualifiés vous contactent."],
              ['Réalisation', 'Choisissez et lancez vos travaux en toute sérénité.'],
          ] as $idv_i => $idv_step) : ?>
            <div class="flex gap-6 relative">
              <div class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center font-headline text-lg shrink-0 z-10 shadow-lg"><?php echo $idv_i + 1; ?></div>
              <div>
                <p class="font-label text-xs uppercase tracking-widest text-primary mb-1">Étape <?php echo $idv_i + 1; ?></p>
                <h4 class="text-xl font-headline mb-2"><?php echo esc_html($idv_step[0]); ?></h4>
                <p class="text-on-surface-variant text-sm"><?php echo esc_html($idv_step[1]); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ── CTA FINAL ─────────────────────────────────────────────────────── -->
  <section class="px-8 pb-24 max-w-[1440px] mx-auto">
    <div class="bg-primary text-on-primary rounded-3xl p-16 text-center">
      <h2 class="text-4xl md:text-5xl font-headline mb-6">Prêt à concrétiser votre <span class="italic">vision</span> ?</h2>
      <p class="text-on-primary/80 mb-8 max-w-xl mx-auto">
        Obtenez des offres comparatives gratuites de la part d'artisans <?php echo esc_html(mb_strtolower($idv_name)); ?> certifiés près de chez vous.
      </p>
      <a href="<?php echo esc_url($idv_devis_url); ?>"
         class="inline-block bg-white text-primary px-10 py-4 rounded-xl font-label tracking-widest uppercase text-xs font-bold hover:bg-on-primary/10 hover:text-on-primary transition-all">
        Obtenir mon devis
      </a>
    </div>
  </section>

</div>

