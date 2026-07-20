<?php
/**
 * Carte artisan — reproduction fidèle de views/partials/card_pro.php
 * + views/partials/artisan_badge.php (badges Font Awesome, 3 niveaux).
 *
 * Args : ['artisan' => WP_Post]
 */

$idv_artisan = $args['artisan'] ?? null;
if (!$idv_artisan instanceof WP_Post) {
    return;
}

$idv_id      = $idv_artisan->ID;
$idv_name    = get_the_title($idv_artisan);
$idv_cover   = get_post_meta($idv_id, '_idc_cover_url', true) ?: IDV_THEME_URI . '/assets/images/metier.png';
$idv_avatar  = get_the_post_thumbnail_url($idv_id, 'medium') ?: IDV_THEME_URI . '/assets/images/avatar-default.png';
$idv_ville   = get_post_meta($idv_id, '_idc_ville', true);
$idv_exp     = (int) get_post_meta($idv_id, '_idc_annees_experience', true);
$idv_bio     = wp_strip_all_tags($idv_artisan->post_content);
$idv_projets = count(get_posts([
    'post_type'   => 'realisation',
    'post_status' => 'publish',
    'numberposts' => -1,
    'fields'      => 'ids',
    'meta_key'    => '_idc_artisan_post_id',
    'meta_value'  => $idv_id,
]));

// Badge selon le plan (3 niveaux alignés Gratuit / Silver / Gold — logique originale).
$idv_plan  = get_post_meta($idv_id, '_idc_plan', true);
$idv_level = in_array($idv_plan, ['gold', 'illimite', 'pro'], true) ? 'verified_pro'
    : (in_array($idv_plan, ['silver'], true) ? 'verified' : 'referenced');
$idv_badges = [
    'referenced'   => ['icon' => 'fa-solid fa-clipboard-check', 'label' => 'Référencé', 'tooltip' => 'Professionnel inscrit avec un SIRET vérifié (plan Gratuit)', 'class' => 'idv-badge--referenced'],
    'verified'     => ['icon' => 'fa-solid fa-circle-check', 'label' => 'Vérifié', 'tooltip' => 'KBIS, identité et assurance RC Pro vérifiés (plan Silver)', 'class' => 'idv-badge--verified'],
    'verified_pro' => ['icon' => 'fa-solid fa-medal', 'label' => 'Vérifié Pro', 'tooltip' => 'Décennale, qualifications et certifications vérifiées (plan Gold)', 'class' => 'idv-badge--verified-pro'],
];
$idv_badge = $idv_badges[$idv_level];

// Spécialités (max 3 + compteur) avec icônes Font Awesome.
$idv_metiers = get_the_terms($idv_id, 'metier') ?: [];
?>
<article class="expert-card group">
  <div class="expert-card__cover">
    <img src="<?php echo esc_url($idv_cover); ?>" alt="Cover <?php echo esc_attr($idv_name); ?>"
      onerror="this.src='<?php echo esc_url(IDV_THEME_URI . '/assets/images/metier.png'); ?>'">

    <div class="expert-card__count-badge">
      <?php echo (int) $idv_projets; ?> réalisation<?php echo $idv_projets > 1 ? 's' : ''; ?>
    </div>

    <button class="expert-card__fav idv-tap" type="button"
      data-fav
      data-fav-id="<?php echo (int) $idv_id; ?>"
      data-fav-type="artisan"
      data-fav-title="<?php echo esc_attr($idv_name); ?>"
      data-fav-url="<?php echo esc_url(get_permalink($idv_artisan)); ?>"
      data-fav-img="<?php echo esc_url($idv_cover); ?>"
      aria-pressed="false" aria-label="Ajouter aux favoris">
      <span class="material-symbols-outlined" style="font-size:18px;">favorite</span>
    </button>
  </div>

  <div class="expert-card__avatar">
    <img src="<?php echo esc_url($idv_avatar); ?>" alt="<?php echo esc_attr($idv_name); ?>"
      onerror="this.src='<?php echo esc_url(IDV_THEME_URI . '/assets/images/avatar-default.png'); ?>'">
  </div>

  <div class="expert-card__body">
    <h2 class="font-headline text-2xl font-semibold mb-1 transition-colors group-hover:text-primary">
      <?php echo esc_html($idv_name); ?>
    </h2>
    <p class="text-on-surface-variant text-sm mb-4">Artisan qualifié</p>

    <?php if ($idv_metiers) : ?>
      <div class="card-specialites">
        <?php foreach (array_slice($idv_metiers, 0, 3) as $idv_term) : ?>
          <span class="card-specialite-tag">
            <?php echo idv_cat_icon_html($idv_term->slug); ?>
            <?php echo esc_html($idv_term->name); ?>
          </span>
        <?php endforeach; ?>
        <?php if (count($idv_metiers) > 3) : ?>
          <span class="card-specialite-tag card-specialite-tag--more">+<?php echo count($idv_metiers) - 3; ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="flex flex-wrap justify-center gap-2 mb-6">
      <span class="idv-badge <?php echo esc_attr($idv_badge['class']); ?> idv-badge--md" title="<?php echo esc_attr($idv_badge['tooltip']); ?>">
        <i class="<?php echo esc_attr($idv_badge['icon']); ?> idv-badge__icon"></i>
        <span class="idv-badge__label"><?php echo esc_html($idv_badge['label']); ?></span>
      </span>
    </div>

    <div class="flex justify-center gap-6 text-sm text-on-surface-variant mb-6 py-4"
      style="border-top:1px solid #f3f4f6;border-bottom:1px solid #f3f4f6;">
      <?php if ($idv_ville) : ?>
        <span class="flex items-center gap-1.5 font-medium">
          <span class="material-symbols-outlined text-primary" style="font-size:18px;">location_on</span>
          <?php echo esc_html($idv_ville); ?>
        </span>
      <?php endif; ?>
      <?php if ($idv_exp > 0) : ?>
        <span class="flex items-center gap-1.5 font-medium">
          <span class="material-symbols-outlined text-primary" style="font-size:18px;">work_history</span>
          <?php echo $idv_exp; ?> an<?php echo $idv_exp > 1 ? 's' : ''; ?>
        </span>
      <?php endif; ?>
    </div>

    <?php if ($idv_bio) : ?>
      <p class="text-sm text-on-surface-variant mb-8 leading-relaxed"
        style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
        <?php echo esc_html($idv_bio); ?>
      </p>
    <?php endif; ?>

    <a href="<?php echo esc_url(get_permalink($idv_artisan)); ?>" class="btn-primary-pill mt-auto" style="width:100%;">
      Voir le profil
      <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
    </a>
  </div>
</article>
