<?php
/**
 * Détail d'un guide métier — reproduction 1:1 de views/pages/guide_detail.php :
 * fil d'ariane, pill « Métier » + vues, titre serif (mot-clé en italique vert),
 * intro, image hero, blocs éditoriaux (Quand faire appel / Ce qu'un pro apporte /
 * Questions), CTA « Prêt à passer à l'action ? », guides connexes.
 *
 * Le contenu migré est du HTML (h2 + ul) : on le découpe en sections stylées.
 */

require_once get_template_directory() . '/inc/categorie-config.php';

get_header();

while (have_posts()) :
    the_post();
    $idv_id   = get_the_ID();
    $idv_slug = get_post_field('post_name', $idv_id);

    if (!current_user_can('manage_options')) {
        $idv_views = (int) get_post_meta($idv_id, '_idc_vues', true) + 1;
        update_post_meta($idv_id, '_idc_vues', $idv_views);
    } else {
        $idv_views = (int) get_post_meta($idv_id, '_idc_vues', true);
    }

    // Image hero : cover locale du métier, sinon photo de la config catégorie, sinon défaut.
    $idv_cfgs = idv_categorie_slug_config();
    $idv_hero = IDV_THEME_URI . '/assets/images/metier.png';
    if (file_exists(get_template_directory() . '/assets/images/categories/' . $idv_slug . '/cover.jpg')) {
        $idv_hero = IDV_THEME_URI . '/assets/images/categories/' . $idv_slug . '/cover.jpg';
    } elseif (isset($idv_cfgs[$idv_slug]['hero'])) {
        $idv_hero = $idv_cfgs[$idv_slug]['hero'];
    }

    // Titre : mot après « & » (ou dernier mot) en italique vert.
    $idv_title = get_the_title();
    if (str_contains($idv_title, ' & ')) {
        [$idv_t1, $idv_t2] = explode(' & ', $idv_title, 2);
        $idv_title_html = esc_html($idv_t1) . ' &amp; <span class="italic text-primary">' . esc_html($idv_t2) . '</span>';
    } else {
        $idv_title_html = preg_replace('/(\S+)\s*$/u', '<span class="italic text-primary">$1</span>', esc_html($idv_title), 1);
    }

    // Découpe du contenu HTML en : intro (1er <p>) + sections (h2 → contenu).
    $idv_intro = '';
    $idv_sections = [];
    $idv_content = get_post_field('post_content', $idv_id);
    if (trim($idv_content) !== '') {
        $idv_dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $idv_dom->loadHTML('<?xml encoding="utf-8"?><div id="idv-root">' . $idv_content . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        $idv_root = $idv_dom->getElementById('idv-root');
        if ($idv_root) {
            $idv_has_section = false;
            foreach (iterator_to_array($idv_root->childNodes) as $idv_node) {
                if ($idv_node->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }
                $idv_tag = strtolower($idv_node->nodeName);
                if ($idv_tag === 'h2' || $idv_tag === 'h3') {
                    $idv_sections[] = ['title' => trim($idv_node->textContent), 'html' => ''];
                    $idv_has_section = true;
                } elseif (!$idv_has_section && $idv_tag === 'p' && $idv_intro === '') {
                    $idv_intro = trim($idv_node->textContent);
                } elseif ($idv_has_section) {
                    $idv_last = count($idv_sections) - 1;
                    $idv_sections[$idv_last]['html'] .= $idv_dom->saveHTML($idv_node);
                }
            }
        }
    }
    if ($idv_intro === '') {
        $idv_intro = get_the_excerpt();
    }

    // Icône par intitulé de section.
    $idv_icon_for = static function (string $t): string {
        $t = mb_strtolower($t);
        if (str_contains($t, 'quand')) return 'fa-bullseye';
        if (str_contains($t, 'question')) return 'fa-circle-question';
        if (str_contains($t, 'pro') || str_contains($t, 'apport') || str_contains($t, 'bon')) return 'fa-medal';
        return 'fa-book-open';
    };

    // Liens CTA : métier lié = slug du guide s'il correspond à une taxonomie metier.
    $idv_metier = get_term_by('slug', $idv_slug, 'metier') ? $idv_slug : '';
    $idv_url_devis = $idv_metier ? add_query_arg('categories', $idv_metier, home_url('/devis/')) : home_url('/devis/');
    $idv_url_pros  = $idv_metier ? add_query_arg('categorie', $idv_metier, home_url('/professionnels/')) : home_url('/professionnels/');
    ?>

<style>
  .idv-pill-black { display:inline-flex; align-items:center; gap:6px; background:#0a0e23; color:#fff; font-family:'Manrope',sans-serif; font-size:10px; font-weight:700; letter-spacing:.2em; text-transform:uppercase; padding:6px 14px; border-radius:9999px; }
  .idv-guide-hero__img { border-radius:24px; overflow:hidden; aspect-ratio:16/9; box-shadow:0 30px 80px rgba(0,0,0,0.12); }
  .idv-guide-hero__img img { width:100%; height:100%; object-fit:cover; }
  .idv-info-block { background:#faf9f8; border-radius:18px; padding:24px 26px; }
  .idv-info-block__title { font-family:'Manrope',sans-serif; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.2em; color:#207752; display:inline-flex; align-items:center; gap:8px; margin-bottom:16px; }
  .idv-info-block ul { display:flex; flex-direction:column; gap:12px; list-style:none; padding:0; margin:0; }
  .idv-info-block li { display:flex; gap:12px; align-items:flex-start; font-size:15px; line-height:1.6; color:#3f4441; }
  .idv-info-block li::before { content:""; flex:none; width:7px; height:7px; border-radius:9999px; background:#207752; margin-top:9px; }
  .idv-info-block p { font-size:15px; line-height:1.6; color:#3f4441; }
  .idv-related-card { background:#fff; border:1px solid rgba(229,231,235,0.7); border-radius:16px; overflow:hidden; text-decoration:none; color:inherit; transition:all .25s ease; display:block; }
  .idv-related-card:hover { transform:translateY(-3px); box-shadow:0 14px 30px rgba(0,0,0,0.08); border-color:rgba(32,119,82,0.25); }
  .idv-related-card__img { aspect-ratio:16/10; overflow:hidden; background:#f1f5f4; }
  .idv-related-card__img img { width:100%; height:100%; object-fit:cover; }
</style>

<main class="pt-32 pb-24 bg-background">

  <!-- Fil d'ariane -->
  <nav class="max-w-5xl mx-auto px-6 sm:px-8 mb-8 text-xs text-on-surface-variant">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-primary">Accueil</a>
    <span class="mx-2">/</span>
    <a href="<?php echo esc_url(home_url('/guides/')); ?>" class="hover:text-primary">Guides</a>
    <span class="mx-2">/</span>
    <span class="text-on-surface font-semibold"><?php the_title(); ?></span>
  </nav>

  <!-- Hero -->
  <section class="max-w-5xl mx-auto px-6 sm:px-8 mb-16">
    <div class="flex items-center gap-3 mb-6">
      <span class="idv-pill-black"><i class="fa-solid fa-book-open" style="font-size:11px;"></i>Métier</span>
      <span class="text-xs text-on-surface-variant"><i class="fa-solid fa-eye mr-1"></i><?php echo esc_html($idv_views); ?> vues</span>
    </div>
    <h1 class="text-4xl sm:text-5xl md:text-7xl font-headline leading-[0.95] text-on-background mb-8"><?php echo $idv_title_html; ?></h1>
    <?php if ($idv_intro) : ?>
      <p class="text-base sm:text-lg md:text-xl text-on-surface-variant leading-relaxed mb-10 max-w-3xl font-body"><?php echo esc_html($idv_intro); ?></p>
    <?php endif; ?>
    <div class="idv-guide-hero__img mb-12">
      <img src="<?php echo esc_url($idv_hero); ?>" alt="<?php echo esc_attr(get_the_title()); ?>"
           onerror="this.src='<?php echo esc_url(IDV_THEME_URI . '/assets/images/metier.png'); ?>'">
    </div>
  </section>

  <!-- Contenu éditorial -->
  <section class="max-w-4xl mx-auto px-6 sm:px-8 mb-20">
    <div class="space-y-6">
      <?php if ($idv_sections) : ?>
        <?php foreach ($idv_sections as $idv_sec) : if (trim($idv_sec['html']) === '') continue; ?>
          <div class="idv-info-block">
            <p class="idv-info-block__title"><i class="fa-solid <?php echo esc_attr($idv_icon_for($idv_sec['title'])); ?>" style="font-size:11px;"></i><?php echo esc_html($idv_sec['title']); ?></p>
            <?php echo wp_kses_post($idv_sec['html']); ?>
          </div>
        <?php endforeach; ?>
      <?php else : ?>
        <div class="idv-info-block"><?php the_content(); ?></div>
      <?php endif; ?>
    </div>
  </section>

  <!-- CTA -->
  <section class="max-w-5xl mx-auto px-6 sm:px-8 mb-24">
    <div class="bg-primary rounded-3xl p-8 sm:p-10 lg:p-14 relative overflow-hidden">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center relative z-10">
        <div>
          <h2 class="text-2xl sm:text-3xl md:text-4xl font-headline text-white mb-3 leading-tight">Prêt à <span class="italic">passer à l'action</span> ?</h2>
          <p class="text-white/85 text-sm sm:text-base leading-relaxed">Découvrez les artisans certifiés ou recevez plusieurs propositions adaptées à votre projet — c'est gratuit et sans engagement.</p>
        </div>
        <div class="flex flex-col gap-3">
          <a href="<?php echo esc_url($idv_url_pros); ?>" class="inline-flex items-center justify-center gap-2 bg-white text-primary px-6 py-4 rounded-xl text-sm font-bold uppercase tracking-widest hover:opacity-95 transition-all shadow-lg">
            <i class="fa-solid fa-user-tie"></i> Voir les artisans certifiés
          </a>
          <a href="<?php echo esc_url($idv_url_devis); ?>" class="inline-flex items-center justify-center gap-2 bg-transparent text-white border border-white/50 px-6 py-4 rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-white/10 transition-all">
            <i class="fa-solid fa-file-lines"></i> Demander un devis
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Guides connexes -->
  <?php
  $idv_related = get_posts([
      'post_type'    => 'guide',
      'post_status'  => 'publish',
      'numberposts'  => 3,
      'post__not_in' => [$idv_id],
      'orderby'      => 'rand',
  ]);
  if ($idv_related) : ?>
    <section class="max-w-7xl mx-auto px-6 sm:px-8 mb-20">
      <div class="flex items-end justify-between flex-wrap gap-4 mb-8">
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-headline">Découvrez <span class="italic text-primary">d'autres guides</span></h2>
        <a href="<?php echo esc_url(home_url('/guides/')); ?>" class="text-sm font-bold text-primary hover:underline inline-flex items-center gap-1">Tous les guides <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
        <?php foreach ($idv_related as $idv_r) :
            $idv_rslug = $idv_r->post_name;
            $idv_rimg = IDV_THEME_URI . '/assets/images/metier.png';
            if (file_exists(get_template_directory() . '/assets/images/categories/' . $idv_rslug . '/cover.jpg')) {
                $idv_rimg = IDV_THEME_URI . '/assets/images/categories/' . $idv_rslug . '/cover.jpg';
            } elseif (isset($idv_cfgs[$idv_rslug]['hero'])) {
                $idv_rimg = $idv_cfgs[$idv_rslug]['hero'];
            }
        ?>
          <a href="<?php echo esc_url(get_permalink($idv_r)); ?>" class="idv-related-card">
            <div class="idv-related-card__img">
              <img src="<?php echo esc_url($idv_rimg); ?>" alt="<?php echo esc_attr(get_the_title($idv_r)); ?>" loading="lazy"
                   onerror="this.src='<?php echo esc_url(IDV_THEME_URI . '/assets/images/metier.png'); ?>'">
            </div>
            <div class="p-5">
              <h3 class="font-headline text-lg mb-2"><?php echo esc_html(get_the_title($idv_r)); ?></h3>
              <p class="text-xs text-on-surface-variant line-clamp-2"><?php echo esc_html(wp_trim_words(get_the_excerpt($idv_r), 20)); ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</main>

    <?php
endwhile;

get_footer();
