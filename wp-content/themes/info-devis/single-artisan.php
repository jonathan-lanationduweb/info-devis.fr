<?php
/**
 * Fiche publique artisan — reproduction fidèle de views/pages/artisan_profil.php :
 * bannière de couverture, avatar superposé, identité + badges + plan, réseaux,
 * bloc stats/bio/CTA, onglets Réalisations / À propos / Avis (?tab=).
 */

get_header();

the_post();
$idv_id      = get_the_ID();
$idv_name    = get_the_title();
$idv_m       = static fn(string $k): string => (string) get_post_meta($idv_id, '_idc_' . $k, true);
$idv_user_id = (int) $idv_m('user_id');
$idv_owner   = get_userdata($idv_user_id);

$idv_fallback = IDV_THEME_URI . '/assets/images/metier.png';
$idv_cover    = $idv_m('cover_url') ?: $idv_fallback;
$idv_avatar   = get_the_post_thumbnail_url($idv_id, 'medium') ?: IDV_THEME_URI . '/assets/images/avatar-default.png';

$idv_gerant = $idv_owner ? trim($idv_owner->display_name) : '';
$idv_show_gerant = $idv_gerant !== '' && mb_strtolower($idv_gerant) !== mb_strtolower($idv_name);

$idv_types      = array_filter(array_map('trim', explode(',', $idv_m('types_projets'))));
$idv_specialite = $idv_types[0] ?? 'Artisan qualifié';
$idv_ville      = $idv_m('ville');
$idv_exp        = (int) $idv_m('annees_experience');
$idv_linkedin   = $idv_m('linkedin_url');
$idv_instagram  = $idv_m('instagram_url');
$idv_metiers    = get_the_terms($idv_id, 'metier') ?: [];

// Badge de niveau (plan) — même logique que l'annuaire.
$idv_plan  = strtolower($idv_m('plan') ?: 'gratuit');
$idv_level = in_array($idv_plan, ['gold', 'illimite', 'pro'], true) ? 'verified_pro' : ($idv_plan === 'silver' || $idv_plan === 'starter' ? 'verified' : 'referenced');
$idv_badges = [
    'referenced'   => ['fa-solid fa-clipboard-check', 'Référencé', 'idv-badge--referenced'],
    'verified'     => ['fa-solid fa-circle-check', 'Vérifié', 'idv-badge--verified'],
    'verified_pro' => ['fa-solid fa-medal', 'Vérifié Pro', 'idv-badge--verified-pro'],
];
[$idv_b_icon, $idv_b_label, $idv_b_class] = $idv_badges[$idv_level];
$idv_public_plan = match ($idv_plan) {
    'gold', 'pro', 'illimite' => ['Gold', 'fa-medal', 'bg-amber-100 text-amber-800 border-amber-200'],
    'silver', 'starter'       => ['Silver', 'fa-award', 'bg-stone-100 text-stone-700 border-stone-200'],
    default                   => null,
};

// Réalisations publiées de la fiche.
$idv_projets = get_posts([
    'post_type'      => 'realisation',
    'post_status'    => 'publish',
    'posts_per_page' => 30,
    'meta_key'       => '_idc_artisan_post_id',
    'meta_value'     => $idv_id,
]);
$idv_nb_projets = count($idv_projets);

// Avis approuvés.
$idv_avis = get_posts([
    'post_type'      => 'avis',
    'post_status'    => 'publish',
    'posts_per_page' => 50,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => [
        ['key' => '_idc_artisan_post_id', 'value' => $idv_id],
        ['key' => '_idc_status', 'value' => 'approved'],
    ],
]);
$idv_nb_avis = (int) ($idv_m('rating_count') ?: count($idv_avis));
$idv_rating  = (float) $idv_m('rating_avg');
$idv_distrib = json_decode($idv_m('rating_distribution'), true) ?: [];

// Onglet actif (?tab=) — contenu accessible sans JavaScript.
$idv_tab = in_array($_GET['tab'] ?? '', ['apropos', 'avis'], true) ? $_GET['tab'] : 'realisations';
$idv_tab_url = static fn(string $t): string => $t === 'realisations' ? get_permalink() : add_query_arg('tab', $t, get_permalink());

// État de session pour le CTA.
$idv_logged   = is_user_logged_in();
$idv_current  = wp_get_current_user();
$idv_is_owner = $idv_logged && $idv_user_id === (int) $idv_current->ID;
$idv_is_client = $idv_logged && in_array('client', (array) $idv_current->roles, true);
?>

<!-- Cover banner (mobile 5/3, desktop 8/3) -->
<div class="ad-cover relative w-full mt-20" style="background:#0c1a2c;overflow:hidden;">
  <img src="<?php echo esc_url($idv_cover); ?>" alt="Cover <?php echo esc_attr($idv_name); ?>"
       class="w-full h-full object-cover object-center" loading="eager"
       onerror="this.src='<?php echo esc_url($idv_fallback); ?>'">
</div>
<style>
  .ad-cover { aspect-ratio: 5/3; min-height: 200px; }
  @media (min-width: 768px) {
    .ad-cover { aspect-ratio: 8/3; min-height: 300px; max-height: 520px; }
  }
</style>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 md:-mt-12 relative z-10 pb-20">

  <!-- Header profil -->
  <section class="flex flex-col md:flex-row items-start justify-between gap-6">
    <div class="flex flex-row items-start md:items-end gap-4 md:gap-6 w-full md:w-auto">
      <div class="relative flex-shrink-0">
        <div class="w-20 h-20 md:w-32 md:h-32 rounded-full border-4 border-white overflow-hidden bg-white shadow-lg">
          <img src="<?php echo esc_url($idv_avatar); ?>" alt="<?php echo esc_attr($idv_name); ?>"
               class="w-full h-full object-cover"
               onerror="this.src='<?php echo esc_url(IDV_THEME_URI . '/assets/images/avatar-default.png'); ?>'">
        </div>
      </div>

      <div class="mb-2 pt-0 md:pt-20 min-w-0 flex-1">
        <h1 class="text-2xl md:text-4xl font-semibold tracking-tight leading-tight mb-1" style="font-family:'Newsreader',serif;">
          <?php echo esc_html($idv_name); ?>
        </h1>
        <?php if ($idv_show_gerant) : ?>
          <p class="text-sm text-on-surface-variant mb-2 inline-flex items-center gap-1.5">
            <i class="fa-solid fa-user-tie text-primary" style="font-size:12px;" aria-hidden="true"></i>
            <span>Gérant : <strong><?php echo esc_html($idv_gerant); ?></strong></span>
          </p>
        <?php endif; ?>
        <p class="text-lg text-on-surface-variant italic mb-3" style="font-family:'Newsreader',serif;">
          <?php echo esc_html($idv_specialite); ?>
        </p>
        <?php if ($idv_nb_avis > 0) :
            $idv_round = (int) round($idv_rating);
        ?>
          <a href="<?php echo esc_url($idv_tab_url('avis')); ?>" class="inline-flex items-center gap-2 mb-3 group" title="Voir les avis">
            <span class="text-base leading-none tracking-tight">
              <span class="text-amber-500"><?php echo str_repeat('★', $idv_round); ?></span><span class="text-stone-300"><?php echo str_repeat('★', 5 - $idv_round); ?></span>
            </span>
            <span class="text-sm font-bold text-on-surface"><?php echo esc_html(number_format($idv_rating, 1, ',', '')); ?></span>
            <span class="text-sm text-on-surface-variant group-hover:text-primary transition-colors">(<?php echo (int) $idv_nb_avis; ?> avis)</span>
          </a>
        <?php endif; ?>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="idv-badge <?php echo esc_attr($idv_b_class); ?> idv-badge--md">
            <i class="<?php echo esc_attr($idv_b_icon); ?> idv-badge__icon" aria-hidden="true"></i>
            <span class="idv-badge__label"><?php echo esc_html($idv_b_label); ?></span>
          </span>
          <?php if ($idv_public_plan) : ?>
            <span class="text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 border <?php echo esc_attr($idv_public_plan[2]); ?>" title="Abonnement <?php echo esc_attr($idv_public_plan[0]); ?>">
              <i class="fa-solid <?php echo esc_attr($idv_public_plan[1]); ?>" aria-hidden="true"></i>
              <?php echo esc_html($idv_public_plan[0]); ?>
            </span>
          <?php endif; ?>
          <a href="<?php echo esc_url(home_url('/nos-niveaux-de-confiance/')); ?>"
             class="text-xs text-on-surface-variant hover:text-primary inline-flex items-center"
             title="En savoir plus sur les niveaux de confiance">
            <span class="material-symbols-outlined" style="font-size:14px;">help_outline</span>
          </a>
        </div>
      </div>
    </div>

    <?php if ($idv_linkedin || $idv_instagram) : ?>
      <div class="flex gap-3 pt-4 md:pt-20">
        <?php if ($idv_linkedin) : ?>
          <a href="<?php echo esc_url($idv_linkedin); ?>" target="_blank" rel="noopener"
             class="w-11 h-11 rounded-full flex items-center justify-center bg-white border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary hover:shadow-md transition-all"
             aria-label="LinkedIn" title="LinkedIn">
            <i class="fa-brands fa-linkedin-in" style="font-size:16px;" aria-hidden="true"></i>
          </a>
        <?php endif; ?>
        <?php if ($idv_instagram) : ?>
          <a href="<?php echo esc_url($idv_instagram); ?>" target="_blank" rel="noopener"
             class="w-11 h-11 rounded-full flex items-center justify-center bg-white border border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary hover:shadow-md transition-all"
             aria-label="Instagram" title="Instagram">
            <i class="fa-brands fa-instagram" style="font-size:16px;" aria-hidden="true"></i>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>

  <hr class="my-8 border-outline-variant/30">

  <!-- Stats + Bio + CTA -->
  <section class="grid grid-cols-1 md:grid-cols-12 gap-12">
    <div class="md:col-span-4 space-y-6">
      <div class="space-y-4">
        <?php if ($idv_exp > 0) : ?>
          <div class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant/15 bg-white">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#f8f8f8;">
              <span class="material-symbols-outlined text-primary" style="font-size:20px;">work_history</span>
            </div>
            <div>
              <p class="text-xs uppercase tracking-wider text-on-surface-variant font-bold">Expérience</p>
              <p class="font-semibold text-sm"><?php echo $idv_exp; ?> an<?php echo $idv_exp > 1 ? 's' : ''; ?></p>
            </div>
          </div>
        <?php endif; ?>

        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded flex items-center justify-center" style="background:#f8f8f8;">
            <span class="material-symbols-outlined text-primary" style="font-size:20px;">apartment</span>
          </div>
          <div>
            <p class="text-xs uppercase tracking-wider text-on-surface-variant font-bold">Réalisations</p>
            <p class="font-semibold text-sm"><?php echo $idv_nb_projets; ?> réalisé<?php echo $idv_nb_projets > 1 ? 's' : ''; ?></p>
          </div>
        </div>

        <?php if ($idv_ville) : ?>
          <div class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant/15 bg-white">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#f8f8f8;">
              <span class="material-symbols-outlined text-primary" style="font-size:20px;">location_on</span>
            </div>
            <div>
              <p class="text-xs uppercase tracking-wider text-on-surface-variant font-bold">Localisation</p>
              <p class="font-semibold text-sm"><?php echo esc_html($idv_ville); ?>, France</p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($idv_types) : ?>
        <div class="pt-4" style="border-top:1px solid #f3f4f6;">
          <p class="text-[10px] uppercase font-bold text-on-surface-variant mb-3 tracking-widest">Types de projets</p>
          <div class="flex flex-wrap gap-2">
            <?php foreach ($idv_types as $idv_tag) : ?>
              <span class="px-3 py-1 text-[11px] font-medium rounded text-on-surface-variant" style="background:#f8f8f8;">
                <?php echo esc_html($idv_tag); ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="md:col-span-8 flex flex-col justify-between">
      <div class="max-w-xl">
        <?php $idv_bio = trim(wp_strip_all_tags(get_the_content())); ?>
        <p class="text-on-surface-variant text-sm leading-relaxed mb-8">
          <?php if ($idv_bio !== '') : ?>
            <?php echo esc_html(wp_trim_words($idv_bio, 32, '…')); ?>
            <?php if (str_word_count($idv_bio) > 32) : ?>
              <a href="<?php echo esc_url($idv_tab_url('apropos')); ?>" class="text-primary font-semibold hover:underline whitespace-nowrap">Lire la suite</a>
            <?php endif; ?>
          <?php else : ?>
            Cet artisan n'a pas encore rédigé sa biographie.
          <?php endif; ?>
        </p>
      </div>

      <div class="flex items-center gap-3 flex-wrap">
        <?php if ($idv_is_owner) : ?>
          <a href="<?php echo esc_url(home_url('/dashboard/artisan/profile/')); ?>" class="btn-outline-pill">
            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
            Modifier ma fiche
          </a>
        <?php elseif ($idv_logged) : ?>
          <a href="<?php echo esc_url(add_query_arg('pro', $idv_id, home_url('/prendre-rdv/'))); ?>" class="btn-primary-pill">
            <span class="material-symbols-outlined" style="font-size:18px;">event_available</span>
            Prendre rendez-vous
          </a>
          <a href="<?php echo esc_url(home_url('/devis/' . ($idv_metiers ? '?metier=' . $idv_metiers[0]->slug : ''))); ?>" class="btn-outline-pill">
            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
            Demander un devis
          </a>
        <?php else : ?>
          <a href="<?php echo esc_url(add_query_arg('redirect_to', rawurlencode(get_permalink()), home_url('/connexion/'))); ?>" class="btn-primary-pill">
            <span class="material-symbols-outlined" style="font-size:18px;">login</span>
            Se connecter pour demander
          </a>
        <?php endif; ?>
        <?php if (!$idv_is_owner) : ?>
          <button type="button" class="idv-tap w-12 h-12 border border-outline-variant rounded-full flex items-center justify-center hover:bg-gray-50 transition-colors"
                  data-fav
                  data-fav-id="<?php echo (int) $idv_id; ?>"
                  data-fav-type="artisan"
                  data-fav-title="<?php echo esc_attr($idv_name); ?>"
                  data-fav-url="<?php echo esc_url(get_permalink()); ?>"
                  data-fav-img="<?php echo esc_url($idv_cover); ?>"
                  aria-pressed="false" aria-label="Ajouter aux favoris">
            <span class="material-symbols-outlined" style="font-size:18px;color:#9CA3AF;">favorite</span>
          </button>
        <?php endif; ?>
        <button type="button" class="idv-tap w-12 h-12 border border-outline-variant rounded-full items-center justify-center hover:bg-gray-50 transition-colors"
                data-share
                data-share-title="<?php echo esc_attr($idv_name); ?>"
                data-share-text="<?php echo esc_attr('Découvrez ' . $idv_name . ' sur InfoDevis'); ?>"
                data-share-url="<?php echo esc_url(get_permalink()); ?>"
                aria-label="Partager">
          <span class="material-symbols-outlined" style="font-size:18px;color:#9CA3AF;">ios_share</span>
        </button>
      </div>
    </div>
  </section>

  <!-- Onglets -->
  <nav class="tabs-artisan" data-purpose="portfolio-tabs">
    <?php foreach ([
        'realisations' => ['Réalisations', $idv_nb_projets],
        'apropos'      => ['À propos', null],
        'avis'         => ['Avis', $idv_nb_avis],
    ] as $idv_key => [$idv_label, $idv_count]) : ?>
      <a href="<?php echo esc_url($idv_tab_url($idv_key)); ?>" class="tabs-artisan__tab<?php echo $idv_key === $idv_tab ? ' active-tab' : ''; ?>">
        <?php echo esc_html($idv_label); ?>
        <?php if ($idv_count !== null && $idv_count > 0) : ?>
          <span style="opacity:0.6;">(<?php echo (int) $idv_count; ?>)</span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <?php if ($idv_tab === 'realisations') : ?>
    <?php if (!$idv_projets) : ?>
      <div class="text-center py-16 text-on-surface-variant">
        <span class="material-symbols-outlined" style="font-size:48px;opacity:0.4;">apartment</span>
        <p class="mt-4">Cet artisan n'a pas encore publié de réalisation.</p>
      </div>
    <?php else : ?>
      <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($idv_projets as $idv_p) :
            $idv_p_cover  = get_the_post_thumbnail_url($idv_p, 'large') ?: $idv_fallback;
            $idv_p_budget = (float) get_post_meta($idv_p->ID, '_idc_budget', true);
            $idv_p_duree  = (string) get_post_meta($idv_p->ID, '_idc_duree', true);
            $idv_p_surface = (int) get_post_meta($idv_p->ID, '_idc_surface_m2', true);
        ?>
          <article class="projet-card group">
            <div class="projet-card__visual">
              <img src="<?php echo esc_url($idv_p_cover); ?>" alt="<?php echo esc_attr(get_the_title($idv_p)); ?>"
                   loading="lazy" onerror="this.src='<?php echo esc_url($idv_fallback); ?>'">
            </div>
            <div class="projet-card__body">
              <h3 class="projet-card__title"><?php echo esc_html(get_the_title($idv_p)); ?></h3>
              <div class="projet-card__stats">
                <?php if ($idv_p_surface > 0) : ?>
                  <div class="projet-card__stat"><span class="material-symbols-outlined">straighten</span><span><?php echo $idv_p_surface; ?> m²</span></div>
                <?php endif; ?>
                <?php if ($idv_p_budget > 0) : ?>
                  <div class="projet-card__stat"><span class="material-symbols-outlined">payments</span><span><?php echo esc_html(number_format($idv_p_budget, 0, ',', ' ')); ?>€</span></div>
                <?php endif; ?>
                <?php if ($idv_p_duree !== '') : ?>
                  <div class="projet-card__stat"><span class="material-symbols-outlined">schedule</span><span><?php echo esc_html($idv_p_duree); ?></span></div>
                <?php endif; ?>
              </div>
              <p class="projet-card__resume"><?php echo esc_html(wp_trim_words($idv_p->post_content, 18)); ?></p>
              <a href="<?php echo esc_url(get_permalink($idv_p)); ?>" class="projet-card__cta">
                Voir la réalisation
                <span class="material-symbols-outlined" style="font-size:12px;">arrow_forward</span>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

  <?php elseif ($idv_tab === 'apropos') : ?>
    <section class="max-w-3xl space-y-10">
      <?php $idv_presentation = wp_strip_all_tags(get_the_content()); ?>
      <?php if ($idv_presentation !== '') : ?>
        <div>
          <h2 class="text-2xl font-semibold mb-4 inline-flex items-center gap-2" style="font-family:'Newsreader',serif;">
            <i class="fa-solid fa-building text-primary" style="font-size:18px;" aria-hidden="true"></i>
            Présentation
          </h2>
          <p class="text-on-surface-variant leading-relaxed"><?php echo nl2br(esc_html($idv_presentation)); ?></p>
        </div>
      <?php endif; ?>

      <?php if ($idv_m('mission')) : ?>
        <div>
          <h3 class="text-xl font-semibold mb-3 inline-flex items-center gap-2" style="font-family:'Newsreader',serif;">
            <i class="fa-solid fa-bullseye text-primary" style="font-size:16px;" aria-hidden="true"></i>
            Notre mission
          </h3>
          <p class="text-on-surface-variant leading-relaxed"><?php echo nl2br(esc_html($idv_m('mission'))); ?></p>
        </div>
      <?php endif; ?>

      <?php if ($idv_m('expertises_detail') || $idv_metiers) : ?>
        <div>
          <h3 class="text-xl font-semibold mb-3 inline-flex items-center gap-2" style="font-family:'Newsreader',serif;">
            <i class="fa-solid fa-screwdriver-wrench text-primary" style="font-size:16px;" aria-hidden="true"></i>
            Nos domaines d'expertise
          </h3>
          <?php if ($idv_m('expertises_detail')) :
              $idv_expertises = array_filter(array_map('trim', preg_split('/\r?\n+/', trim($idv_m('expertises_detail')))));
          ?>
            <div class="flex flex-wrap gap-2 mb-5">
              <?php foreach ($idv_expertises as $idv_exp) : ?>
                <span class="inline-flex items-center gap-1.5 max-w-full px-3.5 py-1.5 rounded-2xl text-sm font-medium bg-primary/5 text-primary border border-primary/15">
                  <i class="fa-solid fa-check text-[10px] opacity-70 shrink-0" aria-hidden="true"></i>
                  <span class="min-w-0 break-words leading-snug"><?php echo esc_html($idv_exp); ?></span>
                </span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php if ($idv_metiers) : ?>
            <div class="flex flex-wrap gap-2 pt-1">
              <?php foreach ($idv_metiers as $idv_t) : ?>
                <span class="inline-block max-w-full break-words px-3 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wide bg-surface-container text-on-surface-variant"><?php echo esc_html($idv_t->name); ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($idv_m('pourquoi_nous_choisir')) :
          $idv_reasons = array_filter(array_map('trim', preg_split('/\r?\n+/', trim($idv_m('pourquoi_nous_choisir')))));
      ?>
        <div class="bg-primary/5 border-l-4 border-primary p-6 rounded-r-2xl">
          <h3 class="text-xl font-semibold mb-3 inline-flex items-center gap-2" style="font-family:'Newsreader',serif;">
            <i class="fa-solid fa-award text-primary" style="font-size:16px;" aria-hidden="true"></i>
            Pourquoi nous choisir ?
          </h3>
          <div class="text-on-surface-variant leading-relaxed space-y-2">
            <?php if (count($idv_reasons) > 1) : ?>
              <ul class="space-y-2">
                <?php foreach ($idv_reasons as $idv_r) : ?>
                  <li class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0" style="font-size:14px;" aria-hidden="true"></i>
                    <span><?php echo esc_html($idv_r); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else : ?>
              <p><?php echo nl2br(esc_html($idv_m('pourquoi_nous_choisir'))); ?></p>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Contact (selon les préférences de confidentialité) -->
      <?php
      $idv_show_contact = $idv_m('show_contact') !== '0';
      $idv_email_public = $idv_m('email_public');
      $idv_phone        = $idv_m('phone');
      ?>
      <?php if ($idv_show_contact && ($idv_email_public || $idv_phone || $idv_ville || $idv_linkedin)) : ?>
        <div class="border-t border-outline-variant/15 pt-8">
          <h3 class="text-xl font-semibold mb-4 inline-flex items-center gap-2" style="font-family:'Newsreader',serif;">
            <i class="fa-solid fa-envelope-open-text text-primary" style="font-size:16px;" aria-hidden="true"></i>
            Contact
          </h3>
          <?php if ($idv_m('contact_message')) : ?>
            <p class="text-on-surface-variant italic mb-5" style="font-family:'Newsreader',serif;">
              "<?php echo esc_html($idv_m('contact_message')); ?>"
            </p>
          <?php endif; ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php if ($idv_email_public) : ?>
              <a href="mailto:<?php echo esc_attr($idv_email_public); ?>"
                 class="flex items-center gap-3 p-3 bg-white border border-outline-variant/15 rounded-xl hover:border-primary hover:bg-primary/5 transition-colors">
                <i class="fa-solid fa-envelope text-primary" aria-hidden="true"></i>
                <span class="text-sm break-all"><?php echo esc_html($idv_email_public); ?></span>
              </a>
            <?php endif; ?>
            <?php if ($idv_phone) : ?>
              <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $idv_phone)); ?>"
                 class="flex items-center gap-3 p-3 bg-white border border-outline-variant/15 rounded-xl hover:border-primary hover:bg-primary/5 transition-colors">
                <i class="fa-solid fa-phone text-primary" aria-hidden="true"></i>
                <span class="text-sm"><?php echo esc_html($idv_phone); ?></span>
              </a>
            <?php endif; ?>
            <?php if ($idv_ville) : ?>
              <div class="flex items-center gap-3 p-3 bg-white border border-outline-variant/15 rounded-xl">
                <i class="fa-solid fa-location-dot text-primary" aria-hidden="true"></i>
                <span class="text-sm"><?php echo esc_html($idv_ville); ?><?php echo $idv_m('code_postal') ? ' · ' . esc_html($idv_m('code_postal')) : ''; ?></span>
              </div>
            <?php endif; ?>
            <?php if ($idv_linkedin) : ?>
              <a href="<?php echo esc_url($idv_linkedin); ?>" target="_blank" rel="noopener"
                 class="flex items-center gap-3 p-3 bg-white border border-outline-variant/15 rounded-xl hover:border-primary hover:bg-primary/5 transition-colors">
                <i class="fa-brands fa-linkedin text-[#0a66c2]" aria-hidden="true"></i>
                <span class="text-sm">LinkedIn</span>
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if (!$idv_presentation && !$idv_m('mission') && !$idv_m('expertises_detail') && !$idv_m('pourquoi_nous_choisir') && !$idv_metiers) : ?>
        <p class="text-on-surface-variant italic">Cet artisan n'a pas encore complété sa présentation.</p>
      <?php endif; ?>
    </section>

  <?php else : /* Avis */ ?>
    <section class="max-w-3xl">

      <?php if (isset($_GET['avis'])) : ?>
        <div class="mb-6 p-4 rounded-xl text-sm <?php echo $_GET['avis'] === 'ok' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
          <?php echo $_GET['avis'] === 'ok' ? '✓ Merci ! Votre avis sera visible après validation par notre équipe.' : ($_GET['avis'] === 'doublon' ? 'Vous avez déjà déposé un avis pour cet artisan.' : 'Une erreur est survenue, merci de réessayer.'); ?>
        </div>
      <?php endif; ?>

      <!-- Synthèse des notes -->
      <?php if ($idv_nb_avis > 0) : ?>
        <div class="bg-white p-8 rounded-2xl border border-outline-variant/15 mb-8 flex flex-col sm:flex-row gap-8 items-center">
          <div class="text-center">
            <p class="text-5xl font-semibold" style="font-family:'Newsreader',serif;"><?php echo esc_html(number_format($idv_rating, 1, ',', '')); ?></p>
            <p class="text-amber-500 text-lg"><?php echo esc_html(str_repeat('★', (int) round($idv_rating))); ?></p>
            <p class="text-xs text-on-surface-variant"><?php echo $idv_nb_avis; ?> avis</p>
          </div>
          <div class="flex-1 w-full space-y-1.5">
            <?php for ($idv_n = 5; $idv_n >= 1; $idv_n--) :
                $idv_c = (int) ($idv_distrib[$idv_n] ?? 0);
                $idv_pct = $idv_nb_avis > 0 ? round($idv_c / $idv_nb_avis * 100) : 0;
            ?>
              <div class="flex items-center gap-3 text-xs">
                <span class="w-8 text-on-surface-variant"><?php echo $idv_n; ?> ★</span>
                <div class="flex-1 h-2 rounded-full" style="background:#f3f4f6;">
                  <div class="h-2 rounded-full bg-primary" style="width:<?php echo $idv_pct; ?>%;"></div>
                </div>
                <span class="w-8 text-right text-on-surface-variant"><?php echo $idv_c; ?></span>
              </div>
            <?php endfor; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Dépôt d'avis : clients connectés uniquement (anti-abus côté extension) -->
      <?php if ($idv_is_client && !$idv_is_owner) : ?>
        <div class="bg-white p-8 rounded-2xl border border-outline-variant/15 mb-8">
          <h3 class="text-xl font-semibold mb-1" style="font-family:'Newsreader',serif;">Laissez votre avis</h3>
          <p class="text-sm text-on-surface-variant mb-6">Partagez votre expérience avec cet artisan.</p>
          <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="space-y-6">
            <input type="hidden" name="action" value="idc_submit_avis">
            <input type="hidden" name="idc_artisan" value="<?php echo (int) $idv_id; ?>">
            <input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr(add_query_arg('tab', 'avis', get_permalink())); ?>">
            <?php wp_nonce_field('idc_avis_form', 'idc_avis_nonce_front'); ?>

            <div class="flex items-center gap-2" id="avis-stars">
              <?php for ($idv_i = 1; $idv_i <= 5; $idv_i++) : ?>
                <button type="button" data-star="<?php echo $idv_i; ?>" class="avis-star transition-transform hover:scale-110">
                  <span class="material-symbols-outlined text-stone-300" style="font-size:36px;">star</span>
                </button>
              <?php endfor; ?>
              <span class="ml-3 text-sm text-on-surface-variant" id="avis-rating-label">Sélectionnez une note</span>
            </div>
            <input type="hidden" name="idc_note" id="avis-rating" value="0">

            <textarea name="idc_comment" rows="4" required placeholder="Décrivez votre expérience avec cet artisan..."
                      class="w-full p-4 rounded-xl border border-outline-variant/30 focus:border-primary focus:ring-2 focus:ring-primary/10 focus:outline-none text-base"></textarea>

            <button type="submit" class="btn-primary-pill">
              <span class="material-symbols-outlined" style="font-size:18px;">send</span>
              Publier mon avis
            </button>
          </form>
        </div>
        <script>
          (function() {
            const stars = document.querySelectorAll('#avis-stars .avis-star');
            const ratingInput = document.getElementById('avis-rating');
            const label = document.getElementById('avis-rating-label');
            const labels = ['', 'Très insatisfait', 'Insatisfait', 'Correct', 'Bien', 'Excellent'];
            stars.forEach(s => {
              s.addEventListener('click', () => {
                const current = parseInt(s.dataset.star);
                ratingInput.value = current;
                label.textContent = labels[current];
                stars.forEach(st => {
                  const ic = st.querySelector('.material-symbols-outlined');
                  if (parseInt(st.dataset.star) <= current) { ic.style.color = '#fbbf24'; ic.style.fontVariationSettings = "'FILL' 1"; }
                  else { ic.style.color = ''; ic.style.fontVariationSettings = "'FILL' 0"; }
                });
              });
            });
          })();
        </script>
      <?php elseif (!$idv_logged) : ?>
        <div class="bg-primary/5 p-6 rounded-2xl mb-8 flex items-center justify-between gap-4 flex-wrap">
          <p class="text-sm">Vous avez fait appel à cet artisan ? Connectez-vous pour laisser un avis.</p>
          <a href="<?php echo esc_url(add_query_arg('redirect_to', rawurlencode(add_query_arg('tab', 'avis', get_permalink())), home_url('/connexion/'))); ?>" class="btn-primary-pill">Se connecter</a>
        </div>
      <?php endif; ?>

      <!-- Liste des avis -->
      <?php if (!$idv_avis) : ?>
        <div class="text-center py-16 text-on-surface-variant">
          <span class="material-symbols-outlined" style="font-size:48px;opacity:0.4;">rate_review</span>
          <p class="mt-4">Aucun avis publié pour le moment.</p>
        </div>
      <?php else : ?>
        <div class="space-y-6">
          <?php foreach ($idv_avis as $idv_a) :
              $idv_a_note   = max(1, min(5, (int) get_post_meta($idv_a->ID, '_idc_rating', true)));
              $idv_a_client = get_userdata((int) get_post_meta($idv_a->ID, '_idc_client_user_id', true));
              // Confidentialité : prénom + initiale du nom uniquement.
              $idv_a_name   = 'Client';
              if ($idv_a_client) {
                  $idv_parts  = explode(' ', trim($idv_a_client->display_name));
                  $idv_a_name = $idv_parts[0] . (isset($idv_parts[1]) ? ' ' . mb_substr($idv_parts[1], 0, 1) . '.' : '');
              }
              $idv_a_reply    = (string) get_post_meta($idv_a->ID, '_idc_artisan_reply', true);
              $idv_a_verified = get_post_meta($idv_a->ID, '_idc_verified', true) === '1';
          ?>
            <article class="p-6 rounded-2xl card-hover bg-white">
              <div class="flex items-center justify-between mb-3 gap-3 flex-wrap">
                <div class="font-semibold inline-flex items-center gap-2">
                  <?php echo esc_html($idv_a_name); ?>
                  <?php if ($idv_a_verified) : ?>
                    <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Vérifié</span>
                  <?php endif; ?>
                </div>
                <div class="text-amber-500 text-sm"><?php echo esc_html(str_repeat('★', $idv_a_note)); ?></div>
              </div>
              <p class="text-on-surface-variant text-sm leading-relaxed"><?php echo esc_html(wp_strip_all_tags($idv_a->post_content)); ?></p>
              <p class="text-xs text-on-surface-variant/60 mt-3"><?php echo esc_html(get_the_date('d/m/Y', $idv_a)); ?></p>
              <?php if ($idv_a_reply) : ?>
                <div class="mt-4 pl-4 border-l-2 border-primary/30 bg-primary/5 rounded-r-lg p-4">
                  <p class="text-[10px] uppercase tracking-widest text-primary font-bold mb-1">Réponse de l'artisan</p>
                  <p class="text-sm text-on-surface-variant"><?php echo esc_html($idv_a_reply); ?></p>
                </div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>

</div>

<?php if (!$idv_is_owner) : ?>
<!-- Barre d'action fixe mobile (section 21.6) : Favori + Prendre RDV -->
<div class="idv-actionbar" role="group" aria-label="Actions rapides">
  <button type="button" class="idv-actionbar__fav idv-tap"
          data-fav
          data-fav-id="<?php echo (int) $idv_id; ?>"
          data-fav-type="artisan"
          data-fav-title="<?php echo esc_attr($idv_name); ?>"
          data-fav-url="<?php echo esc_url(get_permalink()); ?>"
          data-fav-img="<?php echo esc_url($idv_cover); ?>"
          aria-pressed="false" aria-label="Ajouter aux favoris">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
  </button>
  <?php if ($idv_logged) : ?>
    <a href="<?php echo esc_url(add_query_arg('pro', $idv_id, home_url('/prendre-rdv/'))); ?>" class="idv-actionbar__cta">
      <span class="material-symbols-outlined" style="font-size:20px;">event_available</span> Prendre rendez-vous
    </a>
  <?php else : ?>
    <a href="<?php echo esc_url(add_query_arg('redirect_to', rawurlencode(get_permalink()), home_url('/connexion/'))); ?>" class="idv-actionbar__cta">
      <span class="material-symbols-outlined" style="font-size:20px;">event_available</span> Prendre rendez-vous
    </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php get_footer(); ?>
