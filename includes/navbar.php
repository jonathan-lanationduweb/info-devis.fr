<?php
$isLogged = !empty($_SESSION['user_id']);
$role     = $_SESSION['user_role'] ?? '';
$dashUrl  = match($role) {
    'admin'   => APP_URL . '/admin',
    'artisan' => APP_URL . '/dashboard/artisan',
    default   => APP_URL . '/dashboard/client',
};
?>
<header class="site-header" id="site-header">
  <div class="container">
    <div class="header-inner">
      <a href="<?= APP_URL ?>" class="logo">Info<span>Devis</span></a>
      <span class="header-badge">Service 100% gratuit — Sans engagement</span>

      <nav class="nav" aria-label="Navigation principale">
        <a href="<?= APP_URL ?>/categories">Métiers</a>
        <a href="<?= APP_URL ?>/guides-prix">Guides & Prix</a>
        <a href="<?= APP_URL ?>/tarifs-pro">Tarifs Pro</a>
        <a href="<?= APP_URL ?>/blog">Blog</a>
        <a href="<?= APP_URL ?>/contact">Contact</a>
      </nav>

      <div class="nav-actions">
        <?php if ($isLogged): ?>
          <a href="<?= $dashUrl ?>" class="btn-espace-pro">Mon espace</a>
          <a href="<?= APP_URL ?>/deconnexion" class="btn-espace-pro">Déconnexion</a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/connexion" class="btn-espace-pro">Connexion</a>
          <a href="<?= APP_URL ?>/inscription?type=artisan" class="btn-espace-pro">Espace Pro</a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/devis" class="btn-devis-nav">Demander un devis</a>
      </div>

      <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<nav class="mobile-nav" id="mobile-nav" aria-label="Navigation mobile">
  <a href="<?= APP_URL ?>/categories">Métiers</a>
  <a href="<?= APP_URL ?>/guides-prix">Guides & Prix</a>
  <a href="<?= APP_URL ?>/tarifs-pro">Tarifs Pro</a>
  <a href="<?= APP_URL ?>/blog">Blog</a>
  <a href="<?= APP_URL ?>/contact">Contact</a>
  <?php if ($isLogged): ?>
    <a href="<?= $dashUrl ?>">Mon espace</a>
    <a href="<?= APP_URL ?>/deconnexion">Déconnexion</a>
  <?php else: ?>
    <a href="<?= APP_URL ?>/connexion">Connexion</a>
    <a href="<?= APP_URL ?>/inscription?type=artisan">Espace Pro</a>
  <?php endif; ?>
  <a href="<?= APP_URL ?>/devis" class="btn btn-primary w-full mt-8">Demander un devis</a>
</nav>
