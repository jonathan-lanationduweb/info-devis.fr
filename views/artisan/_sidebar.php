<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function sidebarActive(string $path): string {
    global $uri;
    return str_contains($uri, $path) ? 'active' : '';
}
?>
<aside class="dashboard-sidebar">
  <div class="sidebar-logo">
    <a href="<?= APP_URL ?>">Info<span>Devis</span></a>
  </div>
  <ul class="sidebar-menu">
    <li class="sidebar-section-title">Principal</li>
    <li><a href="<?= APP_URL ?>/dashboard/artisan"          class="<?= sidebarActive('/artisan') && !sidebarActive('/artisan/') ? 'active' : '' ?>">📊 Tableau de bord</a></li>
    <li><a href="<?= APP_URL ?>/dashboard/artisan/leads"    class="<?= sidebarActive('/leads') ?>">🎯 Mes leads</a></li>
    <li><a href="<?= APP_URL ?>/dashboard/artisan/messages" class="<?= sidebarActive('/messages') ?>">💬 Messages</a></li>
    <li class="sidebar-section-title">Mon compte</li>
    <li><a href="<?= APP_URL ?>/dashboard/artisan/profile"  class="<?= sidebarActive('/profile') ?>">👤 Mon profil</a></li>
    <li><a href="<?= APP_URL ?>/dashboard/artisan/calendar" class="<?= sidebarActive('/calendar') ?>">📅 Disponibilités</a></li>
    <li><a href="<?= APP_URL ?>/dashboard/artisan/documents"class="<?= sidebarActive('/documents') ?>">📄 Documents</a></li>
    <li><a href="<?= APP_URL ?>/dashboard/artisan/stats"    class="<?= sidebarActive('/stats') ?>">📈 Statistiques</a></li>
    <li><a href="<?= APP_URL ?>/dashboard/artisan/abonnement" class="<?= sidebarActive('/abonnement') ?>">💳 Abonnement</a></li>
    <li class="sidebar-section-title">Autre</li>
    <li><a href="<?= APP_URL ?>">🏠 Site principal</a></li>
    <li><a href="<?= APP_URL ?>/deconnexion" style="color:rgba(255,100,100,.7)">🚪 Déconnexion</a></li>
  </ul>
</aside>
