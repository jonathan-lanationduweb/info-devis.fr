<?php
/**
 * Index des guides — reproduction fidèle de views/home/guides.php :
 * hero éditorial, bloc 3 piliers, grille de cards, CTA final.
 */

get_header();

$idv_guides = get_posts([
    'post_type'      => 'guide',
    'post_status'    => 'publish',
    'posts_per_page' => 50,
    'orderby'        => 'title',
    'order'          => 'ASC',
]);
$idv_images   = idv_category_images();
$idv_icons    = idv_category_icons();
$idv_fallback = IDV_THEME_URI . '/assets/images/metier.png';
?>

<style>
  .pill-black {
    display:inline-flex; align-items:center; gap:6px;
    background:#0a0e23; color:#fff;
    font-family:'Manrope',sans-serif;
    font-size:10px; font-weight:700; letter-spacing:.2em; text-transform:uppercase;
    padding:6px 14px; border-radius:9999px;
  }
  .guide-card2 {
    background:#fff; border:1px solid rgba(229,231,235,0.7); border-radius:20px;
    overflow:hidden; display:flex; flex-direction:column;
    transition: box-shadow .3s ease, border-color .3s ease, transform .3s ease;
    text-decoration:none; color:inherit;
  }
  .guide-card2:hover {
    box-shadow:0 20px 50px rgba(32,119,82,0.10);
    border-color:rgba(32,119,82,0.30);
    transform: translateY(-3px);
  }
  .guide-card2__img {
    aspect-ratio: 5/3; overflow:hidden; background:#f1f5f4; position:relative;
  }
  .guide-card2__img img {
    width:100%; height:100%; object-fit:cover;
    transition: transform .9s ease;
  }
  .guide-card2:hover .guide-card2__img img { transform: scale(1.05); }
  .guide-card2__icon {
    position:absolute; top:14px; right:14px;
    width:42px; height:42px; border-radius:12px;
    background: rgba(10,14,35,0.92); color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-size:16px;
  }
  .guide-card2__body { padding: 22px 22px 24px; display:flex; flex-direction:column; flex:1; }
  .guide-card2__title {
    font-family:'Newsreader',serif; font-size:22px; line-height:1.25;
    color:#1f1f1f; margin-bottom:10px;
  }
  .guide-card2__title em { color:#207752; font-style:italic; }
  .guide-card2__intro {
    font-family:'Manrope',sans-serif; font-size:13.5px; color:#5b605f;
    line-height:1.55; flex:1;
    display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical;
    overflow:hidden;
  }
  .guide-card2__cta {
    margin-top:18px;
    font-family:'Manrope',sans-serif; font-size:11px; font-weight:700;
    text-transform:uppercase; letter-spacing:.18em; color:#207752;
    display:inline-flex; align-items:center; gap:6px;
  }
  .guide-card2__cta i { transition: transform .25s ease; }
  .guide-card2:hover .guide-card2__cta i { transform: translateX(3px); }
</style>

<div class="pt-32 pb-24 bg-background">

  <!-- HERO ÉDITORIAL -->
  <section class="max-w-7xl mx-auto px-6 sm:px-8 mb-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-end mb-14">
      <div class="lg:col-span-8">
        <span class="pill-black mb-8"><i class="fa-solid fa-book-open" style="font-size:11px;" aria-hidden="true"></i>Guides & Conseils</span>
        <h1 class="text-4xl sm:text-5xl md:text-7xl font-headline leading-[0.95] text-on-background mb-6" style="font-family:'Newsreader',serif;">
          Comprendre <span class="italic text-primary">ce qui se cache</span><br class="hidden sm:block">
          derrière chaque métier.
        </h1>
      </div>
      <div class="lg:col-span-4 pb-3">
        <p class="font-body text-base sm:text-lg text-on-surface-variant leading-relaxed">
          Le bon réflexe, le bon pro, au bon moment — un guide pratique pour anticiper, comparer et choisir en toute sérénité.
        </p>
      </div>
    </div>

    <!-- Bloc 3 piliers -->
    <div class="bg-surface-container-low rounded-3xl p-8 sm:p-10 lg:p-14">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-10">
        <div>
          <div class="w-12 h-12 rounded-2xl bg-primary text-white flex items-center justify-center mb-5">
            <i class="fa-solid fa-shield-halved" style="font-size:18px;" aria-hidden="true"></i>
          </div>
          <h3 class="font-headline text-xl mb-2" style="font-family:'Newsreader',serif;">Artisans <span class="italic text-primary">vérifiés</span></h3>
          <p class="text-sm text-on-surface-variant leading-relaxed">SIRET, assurance professionnelle et garantie décennale contrôlés. Trois niveaux de confiance : <em>Référencé</em>, <em>Vérifié</em>, <em>Vérifié Pro</em>.</p>
        </div>
        <div>
          <div class="w-12 h-12 rounded-2xl bg-primary text-white flex items-center justify-center mb-5">
            <i class="fa-solid fa-handshake" style="font-size:18px;" aria-hidden="true"></i>
          </div>
          <h3 class="font-headline text-xl mb-2" style="font-family:'Newsreader',serif;">Mise en relation <span class="italic text-primary">sans engagement</span></h3>
          <p class="text-sm text-on-surface-variant leading-relaxed">Vous décrivez votre projet, des artisans qualifiés de votre zone vous proposent un rendez-vous. Vous restez maître de la décision à chaque étape.</p>
        </div>
        <div>
          <div class="w-12 h-12 rounded-2xl bg-primary text-white flex items-center justify-center mb-5">
            <i class="fa-solid fa-star-half-stroke" style="font-size:18px;" aria-hidden="true"></i>
          </div>
          <h3 class="font-headline text-xl mb-2" style="font-family:'Newsreader',serif;">Suivi & <span class="italic text-primary">avis transparents</span></h3>
          <p class="text-sm text-on-surface-variant leading-relaxed">Notes et témoignages vérifiés après chantier. Vous savez exactement ce que les clients précédents ont vécu avec chaque artisan.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- GRILLE DES GUIDES -->
  <section class="max-w-7xl mx-auto px-6 sm:px-8 mb-24">
    <div class="flex items-end justify-between flex-wrap gap-6 mb-10">
      <div>
        <span class="pill-black mb-5"><i class="fa-solid fa-grip" style="font-size:11px;" aria-hidden="true"></i>Tous les métiers</span>
        <h2 class="text-3xl sm:text-4xl md:text-5xl font-headline leading-tight" style="font-family:'Newsreader',serif;">
          <?php echo count($idv_guides); ?> guides <span class="italic text-primary">à explorer</span>.
        </h2>
      </div>
      <p class="text-sm text-on-surface-variant max-w-md">
        Cliquez sur un métier pour découvrir quand y faire appel, ce qu'un bon pro apporte et les questions à poser avant signature.
      </p>
    </div>

    <?php if (!$idv_guides) : ?>
      <div class="bg-white p-12 text-center rounded-2xl border border-outline-variant/10">
        <i class="fa-solid fa-book-open text-stone-300 mb-4" style="font-size:42px;" aria-hidden="true"></i>
        <p class="text-on-surface-variant">Aucun guide publié pour le moment.</p>
      </div>
    <?php else : ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        <?php foreach ($idv_guides as $idv_g) :
            $idv_terms_g = get_the_terms($idv_g->ID, 'metier') ?: [];
            $idv_slug_g  = $idv_terms_g ? $idv_terms_g[0]->slug : '';
            $idv_img_g   = get_the_post_thumbnail_url($idv_g, 'large') ?: ($idv_images[$idv_slug_g] ?? $idv_fallback);
            $idv_icon_g  = $idv_slug_g && isset($idv_icons[$idv_slug_g]) ? $idv_icons[$idv_slug_g] : '';
        ?>
          <a href="<?php echo esc_url(get_permalink($idv_g)); ?>" class="guide-card2">
            <div class="guide-card2__img">
              <img src="<?php echo esc_url($idv_img_g); ?>"
                   alt="<?php echo esc_attr(get_the_title($idv_g)); ?>"
                   loading="lazy"
                   onerror="this.src='<?php echo esc_url($idv_fallback); ?>'">
              <?php if ($idv_icon_g) : ?>
                <span class="guide-card2__icon"><i class="<?php echo esc_attr($idv_icon_g); ?>" aria-hidden="true"></i></span>
              <?php endif; ?>
            </div>
            <div class="guide-card2__body">
              <h3 class="guide-card2__title"><?php echo esc_html(get_the_title($idv_g)); ?></h3>
              <p class="guide-card2__intro"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($idv_g->post_content), 24)); ?></p>
              <span class="guide-card2__cta">
                Lire le guide
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
              </span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- CTA FINAL -->
  <section class="max-w-7xl mx-auto px-6 sm:px-8">
    <div class="bg-primary rounded-3xl p-10 sm:p-12 lg:p-16 text-center relative overflow-hidden">
      <h2 class="font-headline text-2xl sm:text-3xl md:text-5xl text-white mb-5 leading-tight" style="font-family:'Newsreader',serif;">
        Votre projet <span class="italic">mérite</span> les bons artisans.
      </h2>
      <p class="text-white/85 mb-10 max-w-xl mx-auto font-body text-sm sm:text-base">
        Décrivez votre besoin en 2 minutes, recevez jusqu'à 5 propositions d'artisans certifiés près de chez vous.
      </p>
      <div class="flex flex-wrap gap-3 justify-center">
        <a href="<?php echo esc_url(home_url('/devis/')); ?>"
           class="inline-flex items-center gap-2 bg-white text-primary px-6 sm:px-8 py-4 rounded-xl text-sm font-bold uppercase tracking-widest hover:opacity-95 transition-all shadow-lg">
          <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
          Demander un devis gratuit
        </a>
        <a href="<?php echo esc_url(home_url('/professionnels/')); ?>"
           class="inline-flex items-center gap-2 bg-transparent text-white border border-white/40 px-6 sm:px-8 py-4 rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-white/10 transition-all">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
          Parcourir les artisans
        </a>
      </div>
    </div>
  </section>

</div>

<?php get_footer(); ?>
