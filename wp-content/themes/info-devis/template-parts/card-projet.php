<?php
/**
 * Carte réalisation — d'après views/partials/card_projet.php (classes portfolio.css).
 * Args : ['projet' => WP_Post]
 */

$idv_projet = $args['projet'] ?? null;
if (!$idv_projet instanceof WP_Post) {
    return;
}
$idv_photo   = get_the_post_thumbnail_url($idv_projet, 'large') ?: IDV_THEME_URI . '/assets/images/metier.png';
$idv_ville   = get_post_meta($idv_projet->ID, '_idc_ville', true);
$idv_artisan = (int) get_post_meta($idv_projet->ID, '_idc_artisan_post_id', true);
$idv_metiers = get_the_terms($idv_projet->ID, 'metier') ?: [];
?>
<a href="<?php echo esc_url(get_permalink($idv_projet)); ?>" class="projet-card group">
  <div class="projet-card__visual">
    <img src="<?php echo esc_url($idv_photo); ?>" alt="<?php echo esc_attr(get_the_title($idv_projet)); ?>"
      class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
  </div>
  <div class="projet-card__body">
    <?php if ($idv_metiers) : ?>
      <span class="card-specialite-tag"><?php echo idv_cat_icon_html($idv_metiers[0]->slug); ?> <?php echo esc_html($idv_metiers[0]->name); ?></span>
    <?php endif; ?>
    <h3 class="font-headline text-xl font-semibold mt-2 mb-1"><?php echo esc_html(get_the_title($idv_projet)); ?></h3>
    <p class="text-on-surface-variant text-sm">
      <?php if ($idv_ville) : ?>
        <span class="material-symbols-outlined text-primary" style="font-size:16px;vertical-align:-3px;">location_on</span>
        <?php echo esc_html($idv_ville); ?>
      <?php endif; ?>
      <?php if ($idv_artisan && get_post($idv_artisan)) : ?>
        — <?php echo esc_html(get_the_title($idv_artisan)); ?>
      <?php endif; ?>
    </p>
  </div>
</a>
