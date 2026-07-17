<?php
/**
 * Header — reproduction fidèle de includes/navbar.php (site original).
 */

$idv_logged   = is_user_logged_in();
$idv_dash_url = $idv_logged ? idv_dashboard_url() : '';
$idv_links    = idv_nav_links();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="<?php echo esc_url(IDV_THEME_URI); ?>/assets/images/favicon.svg" type="image/svg+xml">
  <link rel="alternate icon" href="<?php echo esc_url(IDV_THEME_URI); ?>/assets/images/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="<?php echo esc_url(IDV_THEME_URI); ?>/assets/images/favicon.svg">
  <?php wp_head(); ?>
</head>

<body <?php body_class('bg-background text-on-background font-body'); ?> data-theme="forest">

  <nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-gray-100" id="site-header">
    <div class="flex justify-between items-center px-8 py-4 w-full max-w-screen-2xl mx-auto">

      <div class="flex items-center gap-10">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="font-headline text-2xl font-bold italic text-primary">
          Info<span class="text-on-background">-Devis</span>
        </a>
        <div class="hidden md:flex items-center gap-7">
          <?php foreach ($idv_links as [$href, $label]) {
              echo idv_nav_link($href, $label);
          } ?>
        </div>
      </div>

      <div class="hidden md:flex items-center gap-4">
        <?php if ($idv_logged) : ?>
          <?php
          if (array_intersect(['client', 'artisan'], (array) wp_get_current_user()->roles)) {
              get_template_part('template-parts/notifications-bell');
          }
          ?>
          <a href="<?php echo esc_url($idv_dash_url); ?>" class="nav-link">Mon espace</a>
          <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="nav-link">Déconnexion</a>
        <?php else : ?>
          <a href="<?php echo esc_url(home_url('/connexion/')); ?>" class="nav-link">Se connecter</a>
          <a href="<?php echo esc_url(home_url('/inscription/')); ?>" class="nav-link">S'inscrire</a>
          <a href="<?php echo esc_url(home_url('/inscription/?type=artisan')); ?>" class="nav-link">Espace Pro</a>
        <?php endif; ?>
        <a href="<?php echo esc_url(home_url('/devis/')); ?>"
          class="bg-primary text-white px-5 py-2.5 rounded font-semibold text-sm hover:opacity-90 transition-all">
          Demander un devis
        </a>
      </div>

      <div class="md:hidden flex items-center gap-3">
        <button id="hamburger" aria-label="Menu">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="3" y1="6" x2="21" y2="6" />
            <line x1="3" y1="12" x2="21" y2="12" />
            <line x1="3" y1="18" x2="21" y2="18" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile -->
    <div class="md:hidden hidden flex-col gap-1 px-6 pb-5 bg-white border-t border-gray-100" id="mobile-nav">
      <?php
      $idv_current = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
      foreach ($idv_links as [$href, $label]) :
          $idv_path   = '/' . trim((string) parse_url($href, PHP_URL_PATH), '/');
          $idv_active = ($idv_path !== '/' && str_starts_with($idv_current, $idv_path));
      ?>
        <a href="<?php echo esc_url($href); ?>"
          class="py-3 border-b border-gray-100 font-medium text-sm <?php echo $idv_active ? 'text-primary font-semibold' : 'text-on-background'; ?>">
          <?php echo esc_html($label); ?>
        </a>
      <?php endforeach; ?>
      <?php if ($idv_logged) : ?>
        <a href="<?php echo esc_url($idv_dash_url); ?>" class="text-on-background py-3 border-b border-gray-100 font-medium text-sm">Mon espace</a>
        <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="text-on-background py-3 border-b border-gray-100 font-medium text-sm">Déconnexion</a>
      <?php else : ?>
        <a href="<?php echo esc_url(home_url('/connexion/')); ?>" class="text-on-background py-3 border-b border-gray-100 font-medium text-sm">Se connecter</a>
        <a href="<?php echo esc_url(home_url('/inscription/')); ?>" class="text-on-background py-3 border-b border-gray-100 font-medium text-sm">S'inscrire</a>
        <a href="<?php echo esc_url(home_url('/inscription/?type=artisan')); ?>" class="text-on-background py-3 border-b border-gray-100 font-medium text-sm">Espace Pro</a>
      <?php endif; ?>
      <a href="<?php echo esc_url(home_url('/devis/')); ?>" class="mt-3 bg-primary text-white text-center py-3 rounded font-semibold text-sm block">
        Demander un devis
      </a>
    </div>
  </nav>

  <main id="main-content">
