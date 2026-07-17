<?php
/**
 * Page d'accueil — reproduction fidèle de views/home/index.php (site original).
 * Textes, structure, classes et SVG identiques ; données depuis WordPress.
 *
 * Depuis la refonte InfoDevis Admin : chaque section lit ses contenus dans les
 * options `idv_home_*` (plugin infodevis-admin). Les valeurs par défaut
 * ci-dessous sont l'exact contenu original : sans le plugin, ou tant que rien
 * n'est modifié dans l'admin, le rendu est identique au commit précédent.
 */

get_header();

/* Sections administrables (fallback autonome si le plugin est désactivé). */
$idv_sections = function_exists('ida_home_all') ? ida_home_all() : [];
$idv_s = static fn(string $key, array $defaults): array => array_merge($defaults, $idv_sections[$key] ?? []);

$idv_hero = $idv_s('hero', [
    'visible' => true,
    'title' => "Trouvez l'artisan parfait pour vos",
    'title_accent' => 'travaux',
    'subtitle' => '',
    'image' => IDV_THEME_URI . '/assets/images/artisan.png',
    'image_mobile' => '',
    'overlay' => 50,
    'height' => '870',
    'show_search' => true,
    'btn1_label' => 'Chercher',
]);
$idv_reassurance = $idv_s('reassurance', [
    'visible' => true,
    'items' => ['100% GRATUIT', 'RÉPONSE RAPIDE', 'ARTISANS CERTIFIÉS'],
]);
$idv_expertises = $idv_s('expertises', [
    'visible' => true, 'kicker' => 'Services Professionnels',
    'title' => 'Nos expertises', 'link' => 'Voir tous les métiers',
]);
$idv_etapes = $idv_s('etapes', [
    'visible' => true,
    'title' => 'Comment ça marche',
    'intro' => 'Trois étapes simples pour concrétiser vos projets de rénovation avec sérénité.',
    'steps' => [
        ['title' => 'Décrivez votre projet', 'desc' => 'Remplissez notre formulaire en 2 minutes pour détailler vos besoins spécifiques.'],
        ['title' => 'Recevez des devis', 'desc' => "Jusqu'à 5 artisans qualifiés vous contactent pour proposer leurs services."],
        ['title' => 'Choisissez & Réalisez', 'desc' => "Comparez les offres et sélectionnez l'artisan qui vous correspond le mieux."],
    ],
]);
$idv_pros_s = $idv_s('professionnels', [
    'visible' => true, 'title' => 'Découvrez nos', 'accent' => 'professionnels',
    'intro' => 'Des artisans certifiés, vérifiés et notés. Consultez leur portfolio et faites votre choix.',
]);
$idv_real_s = $idv_s('realisations', [
    'visible' => true, 'title' => 'Dernières', 'accent' => 'réalisations',
    'intro' => "L'inspiration au quotidien. Découvrez les projets récents de nos artisans.",
]);
$idv_citation = $idv_s('citation', [
    'visible' => true,
    'texte' => "La qualité d'un ouvrage ne réside pas seulement dans les matériaux utilisés, mais dans l'intention et la précision de la main qui les façonne.",
    'auteur' => "L'équipe InfoDevis",
]);
$idv_cta = $idv_s('cta', [
    'visible' => true,
    'title' => 'Prêt à lancer vos travaux ?',
    'intro' => "Rejoignez des milliers de particuliers qui font confiance à notre réseau d'artisans certifiés.",
    'btn1_label' => 'Demander mon devis gratuit', 'btn1_url' => '/devis/',
    'btn2_label' => 'Consulter les tarifs', 'btn2_url' => '/tarifs-pro/',
]);
/* URL interne ou absolue. */
$idv_url = static fn(string $u): string => str_starts_with($u, 'http') ? $u : home_url($u);

// Catégories : ordre défini dans InfoDevis Admin (repli : alphabétique, comme l'original).
if (function_exists('ida_get_metiers_ordered')) {
    $idv_terms = ida_get_metiers_ordered();
} else {
    $idv_terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0, 'orderby' => 'name', 'order' => 'ASC']);
    if (is_wp_error($idv_terms)) {
        $idv_terms = [];
    }
}
$idv_images = idv_category_images();
$idv_cat_url = static fn($slug) => home_url('/categorie/' . $slug . '/');
/* Image d'un métier : term meta (admin) prioritaire, sinon mapping original. */
$idv_cat_img = static function ($term, string $fallback) use ($idv_images): string {
    $meta = get_term_meta($term->term_id, '_idc_image', true);
    return $meta ?: ($idv_images[$term->slug] ?? $fallback);
};

$idv_first  = $idv_terms[0] ?? null;
$idv_second = $idv_terms[1] ?? null;
$idv_rest   = array_slice($idv_terms, 2, 3);

// Artisans mis en avant (équivalent $featuredPros).
$idv_pros = get_posts([
    'post_type'      => 'artisan',
    'post_status'    => 'publish',
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

// Réalisations mises en avant (équivalent $featuredProjets).
$idv_projets = get_posts([
    'post_type'      => 'realisation',
    'post_status'    => 'publish',
    'posts_per_page' => 3,
]);
?>

<?php if (!empty($idv_hero['visible'])) : ?>
<!-- ── Hero ── -->
<section class="relative flex items-center pt-20 overflow-hidden" style="min-height:<?php echo (int) $idv_hero['height']; ?>px;">
  <div class="absolute inset-0 z-0">
    <div id="idv-hero-bg" style="position:absolute;inset:0;background-image:url('<?php echo esc_url($idv_hero['image']); ?>');background-size:cover;background-position:center center;"></div>
    <?php if (!empty($idv_hero['image_mobile'])) : ?>
      <style>@media (max-width: 767px) { #idv-hero-bg { background-image: url('<?php echo esc_url($idv_hero['image_mobile']); ?>') !important; } }</style>
    <?php endif; ?>
    <div class="absolute inset-0" style="background:rgba(0,0,0,<?php echo max(0, min(90, (int) $idv_hero['overlay'])) / 100; ?>);"></div>
  </div>

  <div class="relative z-10 w-full max-w-screen-2xl mx-auto px-8">
    <div class="max-w-3xl">
      <h1 class="font-headline text-6xl md:text-8xl text-white mb-8 leading-[1.1] tracking-tight drop-shadow-lg">
        <?php echo esc_html($idv_hero['title']); ?>
        <span class="text-primary italic"> <?php echo esc_html($idv_hero['title_accent']); ?></span>
      </h1>
      <?php if (!empty($idv_hero['subtitle'])) : ?>
        <p class="text-white/85 text-xl mb-8 drop-shadow"><?php echo esc_html($idv_hero['subtitle']); ?></p>
      <?php endif; ?>

      <?php if (!empty($idv_hero['show_search'])) : ?>
      <!-- Barre de recherche -->
      <div class="bg-white/95 backdrop-blur-md rounded-xl p-2 flex flex-col md:flex-row items-stretch gap-2 max-w-2xl shadow-2xl">
        <div class="flex-1 flex items-center px-4 py-3 border-r border-gray-200">
          <svg class="text-gray-400 mr-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z" />
          </svg>
          <select name="category" id="hero-category" class="w-full bg-transparent border-none focus:ring-0 text-on-background text-sm font-body">
            <option value="">Type de métier</option>
            <?php foreach ($idv_terms as $idv_term) : ?>
              <option value="<?php echo esc_attr($idv_term->slug); ?>"><?php echo esc_html($idv_term->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex-1 flex items-center px-4 py-3 border-r border-gray-200">
          <svg class="text-gray-400 mr-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
            <circle cx="12" cy="10" r="3" />
          </svg>
          <input type="text" id="hero-ville" placeholder="Code postal / Ville"
            class="w-full bg-transparent border-none focus:ring-0 text-on-background placeholder-gray-400 text-sm font-body">
        </div>
        <button id="hero-search-btn" class="bg-primary text-white px-8 py-3 rounded-lg font-semibold text-sm hover:opacity-90 transition-all">
          <?php echo esc_html($idv_hero['btn1_label']); ?>
        </button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($idv_reassurance['visible']) && !empty($idv_reassurance['items'])) : ?>
<!-- ── Barre réassurance ── -->
<div class="bg-primary py-5">
  <div class="max-w-screen-2xl mx-auto px-8 flex flex-col md:flex-row justify-around items-center gap-6 text-white font-label text-xs tracking-[0.15em] font-bold">
    <?php
    $idv_reassurance_icons = [
        '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" /><polyline points="12 6 12 12 16 14" />',
        '<circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />',
        '<path d="M22 11.08V12a10 10 0 11-5.93-9.14" /><polyline points="22 4 12 14.01 9 11.01" />',
    ];
    foreach (array_values($idv_reassurance['items']) as $idv_i => $idv_item) : ?>
      <div class="flex items-center gap-3">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <?php echo $idv_reassurance_icons[$idv_i % 3]; ?>
        </svg>
        <?php echo esc_html($idv_item); ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($idv_expertises['visible'])) : ?>
<!-- ── Nos expertises ── -->
<section class="pt-24 pb-16 bg-surface-container-low">
  <div class="max-w-screen-2xl mx-auto px-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
      <div>
        <span class="font-label text-xs uppercase tracking-widest text-primary font-bold mb-4 block"><?php echo esc_html($idv_expertises['kicker']); ?></span>
        <h2 class="font-headline text-5xl text-on-background"><?php echo esc_html($idv_expertises['title']); ?></h2>
      </div>
      <a href="<?php echo esc_url(home_url('/categories/')); ?>" class="text-primary font-medium flex items-center gap-2 hover:gap-4 transition-all pb-2 text-sm">
        <?php echo esc_html($idv_expertises['link']); ?>
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <line x1="5" y1="12" x2="19" y2="12" />
          <polyline points="12 5 19 12 12 19" />
        </svg>
      </a>
    </div>

    <!-- Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

      <!-- Grande carte gauche -->
      <?php if ($idv_first) : ?>
        <a href="<?php echo esc_url($idv_cat_url($idv_first->slug)); ?>"
          class="md:col-span-8 group relative overflow-hidden rounded-xl bg-surface-container-low aspect-[16/9] md:aspect-auto md:h-[500px]">
          <img src="<?php echo esc_url($idv_cat_img($idv_first, 'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80')); ?>"
            alt="<?php echo esc_attr($idv_first->name); ?>"
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
          <div class="absolute bottom-0 p-10 w-full">
            <span class="bg-primary-container/90 text-on-primary-container px-3 py-1 text-xs font-label uppercase tracking-widest mb-4 inline-block rounded">
              <?php echo idv_cat_icon_html($idv_first->slug, 'mr-1'); ?> <?php echo esc_html($idv_first->name); ?>
            </span>
            <h3 class="text-3xl font-headline text-white mb-3"><?php echo esc_html($idv_first->name); ?></h3>
            <p class="text-white/80 max-w-md text-sm leading-relaxed mb-5">
              <?php echo esc_html(mb_substr($idv_first->description ?: 'Artisans qualifiés disponibles près de chez vous.', 0, 120)); ?>...
            </p>
            <span class="inline-flex items-center gap-2 text-white font-label text-sm group-hover:gap-4 transition-all">
              Découvrir le métier →
            </span>
          </div>
        </a>
      <?php endif; ?>

      <!-- Carte portrait droite -->
      <?php if ($idv_second) : ?>
        <a href="<?php echo esc_url($idv_cat_url($idv_second->slug)); ?>"
          class="md:col-span-4 group relative overflow-hidden rounded-xl bg-surface-container-low h-[500px]">
          <img src="<?php echo esc_url($idv_cat_img($idv_second, 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=800&q=80')); ?>"
            alt="<?php echo esc_attr($idv_second->name); ?>"
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
          <div class="absolute bottom-0 p-8 w-full">
            <span class="bg-primary-container/90 text-on-primary-container px-3 py-1 text-xs font-label uppercase tracking-widest mb-4 inline-block rounded">
              <?php echo idv_cat_icon_html($idv_second->slug, 'mr-1'); ?> <?php echo esc_html($idv_second->name); ?>
            </span>
            <h3 class="text-2xl font-headline text-white mb-3"><?php echo esc_html($idv_second->name); ?></h3>
            <p class="text-white/80 text-sm leading-relaxed mb-5">
              <?php echo esc_html(mb_substr($idv_second->description ?: 'Artisans qualifiés disponibles près de chez vous.', 0, 100)); ?>...
            </p>
            <span class="inline-flex items-center gap-2 text-white font-label text-sm group-hover:gap-4 transition-all">
              Voir les réalisations →
            </span>
          </div>
        </a>
      <?php endif; ?>

      <!-- 3 petites cartes carrées -->
      <?php foreach ($idv_rest as $idv_cat) : ?>
        <a href="<?php echo esc_url($idv_cat_url($idv_cat->slug)); ?>"
          class="md:col-span-4 group relative overflow-hidden rounded-xl bg-surface-container-low aspect-square">
          <img src="<?php echo esc_url($idv_cat_img($idv_cat, 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800&q=80')); ?>"
            alt="<?php echo esc_attr($idv_cat->name); ?>"
            class="absolute inset-0 w-full h-full object-cover transition-all duration-700 group-hover:scale-105">
          <div class="absolute inset-0 bg-black/40 group-hover:bg-black/20 transition-colors duration-500"></div>
          <div class="absolute inset-0 flex flex-col justify-end p-8 bg-gradient-to-t from-black/80 to-transparent">
            <h3 class="text-xl font-headline text-white mb-2">
              <?php echo idv_cat_icon_html($idv_cat->slug, 'mr-2 text-primary-light'); ?> <?php echo esc_html($idv_cat->name); ?>
            </h3>
            <p class="text-white/70 text-xs leading-relaxed">
              <?php echo esc_html(mb_substr($idv_cat->description ?: 'Artisans qualifiés disponibles près de chez vous.', 0, 80)); ?>...
            </p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($idv_etapes['visible'])) : ?>
<!-- ── Comment ça marche ── -->
<section class="py-24 bg-surface">
  <div class="max-w-screen-2xl mx-auto px-8">
    <div class="max-w-2xl mx-auto text-center mb-20">
      <h2 class="font-headline text-5xl mb-6"><?php echo esc_html($idv_etapes['title']); ?></h2>
      <p class="text-on-surface-variant text-lg"><?php echo esc_html($idv_etapes['intro']); ?></p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-16">
      <?php
      // Icônes SVG originales, associées aux étapes par position.
      $idv_step_icons = [
          'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
          'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
          'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
      ];
      foreach (array_values($idv_etapes['steps']) as $idv_i => $idv_step) : ?>
        <div class="text-center group">
          <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-8 shadow-lg group-hover:bg-primary transition-colors duration-300">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" class="text-primary group-hover:text-white transition-colors">
              <path d="<?php echo esc_attr($idv_step_icons[$idv_i % 3]); ?>" />
            </svg>
          </div>
          <h3 class="font-headline text-2xl mb-4"><?php echo esc_html($idv_step['title']); ?></h3>
          <p class="text-on-surface-variant font-light px-4 text-sm leading-relaxed"><?php echo esc_html($idv_step['desc']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($idv_pros_s['visible']) && $idv_pros) : ?>
<!-- ── Découvrez nos professionnels ── -->
<section class="py-24 bg-background">
  <div class="max-w-7xl mx-auto px-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
      <div>
        <h2 class="text-4xl md:text-5xl font-bold leading-tight" style="font-family:'Newsreader',serif;">
          <?php echo esc_html($idv_pros_s['title']); ?> <span class="pill-title"><?php echo esc_html($idv_pros_s['accent']); ?></span>
        </h2>
        <p class="mt-4 text-on-surface-variant max-w-2xl">
          <?php echo esc_html($idv_pros_s['intro']); ?>
        </p>
      </div>
      <a href="<?php echo esc_url(home_url('/professionnels/')); ?>" class="text-sm font-semibold text-primary hover:underline flex items-center gap-1 whitespace-nowrap">
        Voir tous les professionnels
        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
      </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
      <?php foreach ($idv_pros as $idv_pro) {
          get_template_part('template-parts/card', 'pro', ['artisan' => $idv_pro]);
      } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($idv_real_s['visible']) && $idv_projets) : ?>
<!-- ── Dernières réalisations ── -->
<section class="py-24" style="background:#faf9f8;">
  <div class="max-w-7xl mx-auto px-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
      <div>
        <h2 class="text-4xl md:text-5xl font-bold leading-tight" style="font-family:'Newsreader',serif;">
          <?php echo esc_html($idv_real_s['title']); ?> <span class="pill-title"><?php echo esc_html($idv_real_s['accent']); ?></span>
        </h2>
        <p class="mt-4 text-on-surface-variant max-w-2xl">
          <?php echo esc_html($idv_real_s['intro']); ?>
        </p>
      </div>
      <a href="<?php echo esc_url(home_url('/professionnels/')); ?>" class="text-sm font-semibold text-primary hover:underline flex items-center gap-1 whitespace-nowrap">
        Voir toutes les réalisations
        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
      </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <?php foreach ($idv_projets as $idv_projet) {
          get_template_part('template-parts/card', 'projet', ['projet' => $idv_projet]);
      } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($idv_citation['visible'])) : ?>
<!-- ── Citation ── -->
<section class="py-24 bg-surface-container-highest/40">
  <div class="max-w-4xl mx-auto px-8">
    <div class="bg-surface-container-highest p-16 rounded-xl relative overflow-hidden">
      <span class="absolute top-0 left-0 text-[10rem] font-headline opacity-5 leading-none select-none">"</span>
      <div class="relative z-10 text-center">
        <p class="text-2xl font-headline italic text-on-surface leading-snug mb-8">
          "<?php echo esc_html($idv_citation['texte']); ?>"
        </p>
        <span class="font-label font-bold text-primary tracking-widest text-xs uppercase block"><?php echo esc_html($idv_citation['auteur']); ?></span>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($idv_cta['visible'])) : ?>
<!-- ── CTA ── -->
<section class="py-20 bg-surface-container-low">
  <div class="max-w-screen-xl mx-auto px-8">
    <div class="bg-primary rounded-2xl p-12 md:p-20 text-center relative overflow-hidden">
      <div class="absolute top-0 right-0 w-1/3 h-full bg-white/5 skew-x-12 translate-x-1/2"></div>
      <h2 class="font-headline text-4xl md:text-6xl text-white mb-8 relative z-10"><?php echo esc_html($idv_cta['title']); ?></h2>
      <p class="text-white/80 text-xl mb-12 max-w-2xl mx-auto relative z-10 font-light">
        <?php echo esc_html($idv_cta['intro']); ?>
      </p>
      <div class="flex flex-col md:flex-row gap-6 justify-center relative z-10">
        <a href="<?php echo esc_url($idv_url($idv_cta['btn1_url'])); ?>" class="bg-white text-primary px-10 py-4 rounded font-bold text-lg hover:bg-green-50 transition-colors shadow-lg">
          <?php echo esc_html($idv_cta['btn1_label']); ?>
        </a>
        <a href="<?php echo esc_url($idv_url($idv_cta['btn2_url'])); ?>" class="border border-white/30 text-white px-10 py-4 rounded font-bold text-lg hover:bg-white/10 transition-colors">
          <?php echo esc_html($idv_cta['btn2_label']); ?>
        </a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($idv_hero['visible']) && !empty($idv_hero['show_search'])) : ?>
<script>
  document.getElementById('hero-search-btn').addEventListener('click', function() {
    var cat = document.getElementById('hero-category').value;
    var ville = encodeURIComponent(document.getElementById('hero-ville').value);
    window.location.href = '<?php echo esc_url(home_url('/devis/')); ?>?metier=' + cat + '&ville=' + ville;
  });
</script>
<?php endif; ?>

<?php get_footer(); ?>
