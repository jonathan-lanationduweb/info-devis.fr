<?php
/**
 * Article de blog — reproduction fidèle de views/blog/show.php :
 * en-tête éditorial (kicker + titre serif italique + badge + date),
 * hero avec extrait en encart, sidebar (sommaire, citation, CTA),
 * corps .article-body, temps de lecture + auteur, avis de l'expert, CTA final.
 * NB : la section « Articles similaires » de la vue d'origine ne s'affichait
 * jamais (variable jamais fournie par le contrôleur) — non portée.
 * Les autres types de contenus gardent la bannière générique.
 */

get_header();

while (have_posts()) :
    the_post();

    if ('post' !== get_post_type()) :
        ?>
        <section class="page-banner page-banner--blog">
          <div class="container max-w-screen-xl mx-auto px-8">
            <span class="page-banner__eyebrow"><?php echo esc_html(get_post_type() === 'guide' ? 'Guide travaux' : 'Blog'); ?></span>
            <h1 class="page-banner__title"><?php the_title(); ?></h1>
            <p class="page-banner__subtitle"><?php echo esc_html(get_the_date()); ?></p>
          </div>
        </section>

        <section class="py-16 bg-surface">
          <div class="max-w-3xl mx-auto px-8">
            <article class="entry-content font-body leading-relaxed">
              <?php the_content(); ?>
            </article>
          </div>
        </section>
        <?php
        continue;
    endif;

    // Mapping catégorie (nom français) → icône Font Awesome 7 (identique à l'original).
    $idv_cat_fa = [
        'Toiture'       => 'fa-house-chimney-crack',
        'Chauffage'     => 'fa-fire',
        'Maçonnerie'    => 'fa-trowel-bricks',
        'Plomberie'     => 'fa-faucet-drip',
        'Isolation'     => 'fa-shield',
        'Rénovation'    => 'fa-house-chimney',
        'Électricité'   => 'fa-bolt',
        'Peinture'      => 'fa-paint-roller',
        'Jardinage'     => 'fa-seedling',
        'Carrelage'     => 'fa-grip',
        'Climatisation' => 'fa-snowflake',
        'Menuiserie'    => 'fa-hammer',
    ];

    $idv_terms = get_the_terms(get_the_ID(), 'metier');
    $idv_cat   = ($idv_terms && !is_wp_error($idv_terms)) ? $idv_terms[0]->name : 'Conseils';
    $idv_icon  = 'fa-solid ' . ($idv_cat_fa[$idv_cat] ?? 'fa-newspaper');

    $idv_excerpt = get_post_field('post_excerpt', get_the_ID());

    // Temps de lecture (≈ 200 mots/min) — identique au calcul original.
    $idv_words = str_word_count(wp_strip_all_tags(get_post_field('post_content', get_the_ID())));
    $idv_read  = max(1, (int) ceil($idv_words / 200));

    $idv_author_id = (int) get_post_field('post_author', get_the_ID());
    $idv_author    = trim(get_user_meta($idv_author_id, 'first_name', true) . ' ' . get_user_meta($idv_author_id, 'last_name', true));
    if ('' === $idv_author) {
        $idv_author = get_the_author_meta('display_name', $idv_author_id);
    }

    $idv_devis_url = home_url('/devis/');
    ?>
<main class="pt-32">

  <!-- En-tête éditorial -->
  <header class="max-w-5xl mx-auto px-8 mb-16">
    <div class="flex flex-col md:flex-row items-end gap-12 mb-16">
      <div class="md:w-2/3">
        <p class="font-label uppercase tracking-[0.2em] text-primary mb-6 text-sm font-semibold">
          Le Curateur de l'Habitat — Guide Expert
        </p>
        <h1 class="font-headline text-5xl md:text-7xl leading-tight text-on-surface italic">
          <?php the_title(); ?>
        </h1>
      </div>
      <div class="md:w-1/3 pb-4">
        <div class="flex items-center gap-3 mb-4">
          <span class="font-label text-xs font-bold uppercase tracking-widest text-primary bg-primary/10 px-3 py-1 rounded-full inline-flex items-center gap-1.5">
            <i class="<?php echo esc_attr($idv_icon); ?>" style="font-size:11px;" aria-hidden="true"></i>
            <?php echo esc_html($idv_cat); ?>
          </span>
        </div>
        <p class="text-on-surface-variant font-label uppercase tracking-widest text-xs">
          Publié le <?php echo esc_html(get_the_date('d/m/Y')); ?>
        </p>
        <div class="h-px w-24 bg-primary mt-4"></div>
      </div>
    </div>

    <!-- Image hero -->
    <?php if (has_post_thumbnail()) : ?>
    <div class="relative h-[400px] md:h-[500px] overflow-hidden rounded-2xl mb-8">
      <?php the_post_thumbnail('full', ['class' => 'w-full h-full object-cover']); ?>
      <div class="absolute inset-0 bg-gradient-to-t from-on-surface/40 to-transparent"></div>

      <?php if ($idv_excerpt) : ?>
      <div class="absolute bottom-8 left-8 bg-surface-container-lowest/85 backdrop-blur-xl p-6 rounded-lg max-w-md shadow-lg">
        <p class="font-headline italic text-lg text-on-surface leading-snug">
          "<?php echo esc_html(mb_substr($idv_excerpt, 0, 140)); ?><?php echo mb_strlen($idv_excerpt) > 140 ? '…' : ''; ?>"
        </p>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </header>

  <!-- Corps de l'article -->
  <div class="max-w-7xl mx-auto px-8 grid grid-cols-1 md:grid-cols-12 gap-16 mb-24">

    <!-- Sidebar -->
    <aside class="md:col-span-3 order-2 md:order-1">
      <div class="sticky top-32 space-y-10">

        <!-- Sommaire -->
        <div class="bg-surface-container-low p-8 rounded-xl">
          <h3 class="font-label uppercase tracking-widest text-xs font-bold text-primary mb-6">Sommaire</h3>
          <ul class="space-y-4 font-label text-xs uppercase tracking-wider text-on-surface-variant">
            <li><a href="#contenu" class="hover:text-primary transition-colors">→ Lire l'article</a></li>
            <li><a href="#devis" class="hover:text-primary transition-colors">→ Obtenir un devis</a></li>
          </ul>
        </div>

        <!-- Citation -->
        <div class="border-t border-outline-variant/20 pt-8">
          <p class="font-headline italic text-lg text-on-surface mb-4">
            "Un devis gratuit, c'est la première étape vers un projet réussi."
          </p>
          <p class="font-label text-xs uppercase tracking-widest text-on-surface-variant">— L'équipe InfoDevis</p>
        </div>

        <!-- CTA sidebar -->
        <div class="bg-primary p-8 rounded-xl text-on-primary">
          <h4 class="font-headline text-xl italic mb-3">Besoin d'un artisan ?</h4>
          <p class="text-on-primary/80 text-sm mb-6">Comparez jusqu'à 5 devis gratuits sous 24h.</p>
          <a href="<?php echo esc_url($idv_devis_url); ?>"
             class="block w-full text-center bg-white text-primary py-3 rounded-lg font-label text-xs uppercase tracking-widest font-bold hover:bg-primary-container transition-all">
            Demander un devis
          </a>
        </div>

      </div>
    </aside>

    <!-- Contenu principal -->
    <article class="md:col-span-9 order-1 md:order-2" id="contenu">

      <!-- Intro -->
      <?php if ($idv_excerpt) : ?>
      <p class="font-body text-xl text-on-surface-variant leading-relaxed mb-12 border-l-2 border-primary-container pl-6">
        <?php echo esc_html($idv_excerpt); ?>
      </p>
      <?php endif; ?>

      <!-- Contenu HTML de l'article -->
      <div class="article-body">
        <?php the_content(); ?>
      </div>
      <style>
        .article-body { font-family: 'Manrope', sans-serif; color: var(--color-text-default, #4B5563); font-size: 17px; line-height: 1.75; }
        .article-body p { margin: 0 0 1.4em; }
        .article-body h2 { font-family: 'Newsreader', serif; font-size: 2rem; line-height: 1.2; margin: 2.5em 0 1em; color: var(--color-text, #111); font-weight: 600; }
        .article-body h3 { font-family: 'Newsreader', serif; font-size: 1.5rem; line-height: 1.3; margin: 2em 0 .8em; color: var(--color-text, #111); font-weight: 600; }
        .article-body h4 { font-size: 1.15rem; margin: 1.6em 0 .6em; color: var(--color-text-strong, #2f3333); font-weight: 700; }
        .article-body strong { color: var(--color-text, #111); font-weight: 700; }
        .article-body em { color: var(--color-text-strong, #2f3333); font-style: italic; }
        .article-body a { color: var(--color-primary, #207752); text-decoration: none; border-bottom: 1px solid rgba(32,119,82,0.25); transition: border-color .15s; }
        .article-body a:hover { border-bottom-color: var(--color-primary, #207752); }
        .article-body ul, .article-body ol { margin: 1em 0 1.4em; padding-left: 1.5em; }
        .article-body ul li, .article-body ol li { margin: .5em 0; padding-left: .25em; }
        .article-body ul { list-style: none; padding-left: 0; }
        .article-body ul li { position: relative; padding-left: 1.6em; }
        .article-body ul li::before { content: ''; position: absolute; left: 0; top: 0.7em; width: 6px; height: 6px; border-radius: 50%; background: var(--color-primary, #207752); }
        .article-body blockquote { border-left: 4px solid var(--color-primary, #207752); padding: .25em 0 .25em 1.5em; font-family: 'Newsreader', serif; font-style: italic; font-size: 1.3em; line-height: 1.4; color: var(--color-text-strong, #2f3333); margin: 2em 0; }
        .article-body img { max-width: 100%; height: auto; border-radius: 12px; margin: 1.5em 0; box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
        .article-body code { background: #f3f4f6; padding: .1em .4em; border-radius: 4px; font-size: .9em; color: #be185d; }
        .article-body pre { background: #1f2937; color: #e5e7eb; padding: 1.2em; border-radius: 12px; overflow-x: auto; margin: 1.5em 0; font-size: 0.9em; }
        .article-body hr { border: 0; height: 1px; background: var(--color-border, #e5e7eb); margin: 2.5em 0; }
        @media (max-width: 768px) {
          .article-body { font-size: 16px; }
          .article-body h2 { font-size: 1.6rem; }
          .article-body h3 { font-size: 1.25rem; }
        }
      </style>

      <!-- Temps de lecture estimé + auteur -->
      <div class="flex items-center justify-between gap-4 mt-12 pt-8 border-t border-outline-variant/15 flex-wrap">
        <div class="flex items-center gap-2 text-sm text-on-surface-variant">
          <i class="fa-solid fa-clock text-primary" aria-hidden="true"></i>
          <span><?php echo (int) $idv_read; ?> min de lecture · <?php echo esc_html(number_format($idv_words, 0, ',', ' ')); ?> mots</span>
        </div>
        <?php if ($idv_author) : ?>
          <div class="flex items-center gap-2 text-sm text-on-surface-variant">
            <i class="fa-solid fa-user-pen text-primary" aria-hidden="true"></i>
            <span>Par <strong><?php echo esc_html($idv_author); ?></strong></span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Citation éditoriale -->
      <div class="bg-surface-container-highest p-12 rounded-xl relative overflow-hidden my-16">
        <span class="absolute -top-10 -left-4 text-[10rem] font-headline text-primary opacity-5 leading-none select-none">"</span>
        <div class="relative z-10">
          <h4 class="font-label uppercase tracking-widest text-primary text-xs font-bold mb-6">L'avis de l'expert</h4>
          <blockquote class="font-headline text-2xl md:text-3xl italic text-on-surface leading-snug mb-6">
            "Un projet bien préparé, avec des artisans qualifiés et certifiés, c'est la garantie d'un résultat durable et d'un investissement rentable."
          </blockquote>
          <p class="font-label uppercase tracking-widest text-on-surface-variant text-xs">— L'équipe éditoriale InfoDevis</p>
        </div>
      </div>

      <!-- CTA inline -->
      <div class="bg-primary p-12 rounded-xl flex flex-col md:flex-row items-center justify-between gap-8" id="devis">
        <div>
          <h3 class="font-headline text-3xl text-on-primary mb-2 italic">
            Besoin d'un artisan pour vos travaux de <?php echo esc_html(mb_strtolower($idv_cat)); ?> ?
          </h3>
          <p class="text-on-primary/80 font-body text-sm">Mise en relation gratuite avec nos artisans certifiés InfoDevis.</p>
        </div>
        <a href="<?php echo esc_url($idv_devis_url); ?>"
           class="bg-white text-primary px-10 py-4 rounded-lg font-label uppercase tracking-widest text-xs font-extrabold hover:bg-surface transition-all shadow-xl flex-shrink-0">
          Trouver un artisan
        </a>
      </div>

    </article>
  </div>

</main>
    <?php
endwhile;

get_footer();
