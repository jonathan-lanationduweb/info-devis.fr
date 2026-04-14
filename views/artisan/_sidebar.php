<?php /* views/artisan/_sidebar.php  */ ?>

<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
  }

  #mob-sidebar {
    transform: translateX(-100%);
    transition: transform .28s cubic-bezier(.4, 0, .2, 1);
  }

  #mob-sidebar.open {
    transform: translateX(0);
  }

  #mob-overlay {
    opacity: 0;
    pointer-events: none;
    transition: opacity .28s;
  }

  #mob-overlay.open {
    opacity: 1;
    pointer-events: auto;
  }
</style>

<?php
$current    = '/' . trim(str_replace('/info-devis', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');
$allLinks = [
  ['/dashboard/artisan',           'dashboard',            'Tableau de bord'],
  ['/dashboard/artisan/leads',     'format_list_bulleted', 'Mes leads'],
  ['/dashboard/artisan/messages',  'chat_bubble',          'Messages'],
  ['/dashboard/artisan/calendar',  'calendar_today',       'Disponibilités'],
  ['/dashboard/artisan/profile',   'account_circle',       'Mon profil'],
  ['/dashboard/artisan/stats',     'query_stats',          'Statistiques'],
  ['/dashboard/artisan/documents', 'description',          'Documents'],
  ['/dashboard/artisan/abonnement', 'card_membership',      'Abonnement'],
];

function sidebarLink(string $url, string $icon, string $label, bool $active, string $extra = ''): string
{
  $cls = $active
    ? 'flex items-center gap-3 bg-primary/5 text-primary px-4 py-3 rounded-xl font-bold border-l-2 border-primary'
    : 'flex items-center gap-3 text-on-surface-variant px-4 py-3 rounded-xl hover:bg-primary/5 hover:text-primary transition-colors';
  return '<a href="' . APP_URL . $url . '" class="' . $cls . ' ' . $extra . '">'
    . '<span class="material-symbols-outlined text-[20px]">' . $icon . '</span>'
    . '<span class="font-label text-[11px] uppercase tracking-widest font-bold">' . $label . '</span>'
    . '</a>';
}
?>

<!-- ── Navbar top (mobile) ─────────────────────────────────────────────── -->
<header class="fixed top-0 left-0 right-0 z-40 h-16 bg-[#faf9f8]/90 backdrop-blur-xl border-b border-outline-variant/10 flex items-center justify-between px-4 md:hidden">
  <a href="<?= APP_URL ?>" class="font-headline italic text-xl text-primary">Info-Devis</a>
  <button id="mob-menu-btn" onclick="toggleMobMenu()"
    class="p-2 rounded-xl hover:bg-surface-container transition-colors"
    aria-label="Menu">
    <span class="material-symbols-outlined text-on-surface">menu</span>
  </button>
</header>

<!-- ── Overlay mobile ─────────────────────────────────────────────────── -->
<div id="mob-overlay"
  class="fixed inset-0 z-40 bg-on-surface/50 md:hidden"
  onclick="toggleMobMenu()"></div>

<!-- ── Sidebar mobile (drawer) ──────────────────────────────────────────── -->
<aside id="mob-sidebar"
  class="fixed left-0 top-0 h-full w-72 z-50 bg-[#faf9f8] border-r border-outline-variant/10 flex flex-col py-6 px-4 gap-1 md:hidden overflow-y-auto">

  <div class="flex items-center justify-between mb-6 px-2">
    <span class="font-headline italic text-primary text-xl">Espace Artisan</span>
    <button onclick="toggleMobMenu()" class="p-1.5 rounded-lg hover:bg-surface-container">
      <span class="material-symbols-outlined">close</span>
    </button>
  </div>

  <div class="px-2 mb-4">
    <p class="font-headline italic text-base text-on-surface font-semibold">
      <?= Security::e($artisan['company_name'] ?? $_SESSION['user_name'] ?? 'Mon espace') ?>
    </p>
    <div class="flex items-center gap-2 mt-1.5">
      <?php if (($artisan['verification_status'] ?? '') === 'validated'): ?>
        <span class="bg-emerald-50 text-primary border border-emerald-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">✓ Vérifié</span>
      <?php else: ?>
        <span class="bg-yellow-50 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">⏳ En validation</span>
      <?php endif; ?>
      <span class="bg-surface-container text-on-surface-variant px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase"><?= ucfirst($artisan['plan'] ?? 'Gratuit') ?></span>
    </div>
  </div>

  <nav class="flex flex-col gap-0.5 flex-1">
    <?php foreach ($allLinks as [$url, $icon, $label]): ?>
      <?= sidebarLink($url, $icon, $label, $current === $url) ?>
    <?php endforeach; ?>
  </nav>

  <div class="mt-4 pt-4 border-t border-outline-variant/10">
    <a href="<?= APP_URL ?>/deconnexion"
      class="flex items-center gap-3 text-red-500 px-4 py-3 rounded-xl hover:bg-red-50 transition-colors">
      <span class="material-symbols-outlined text-[20px]">logout</span>
      <span class="font-label text-[11px] uppercase tracking-widest font-bold">Déconnexion</span>
    </a>
  </div>
</aside>

<!-- ── Sidebar desktop ──────────────────────────────────────────────────── -->
<aside class="hidden md:flex fixed left-0 top-0 h-full w-72 flex-col py-8 px-6 gap-y-1 bg-[#faf9f8] border-r border-outline-variant/10 z-30 pt-20 overflow-y-auto">

  <div class="mb-6 px-2">
    <h2 class="font-headline italic text-xl text-primary font-semibold">
      <?= Security::e($artisan['company_name'] ?? $_SESSION['user_name'] ?? 'Mon espace') ?>
    </h2>
    <div class="flex flex-wrap items-center gap-2 mt-2">
      <?php if (($artisan['verification_status'] ?? '') === 'validated'): ?>
        <span class="flex items-center gap-1 bg-emerald-50 text-primary border border-emerald-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
          <span class="material-symbols-outlined text-[11px]" style="font-variation-settings:'FILL' 1,'wght' 300,'GRAD' 0,'opsz' 24">verified</span> Vérifié
        </span>
      <?php else: ?>
        <span class="bg-yellow-50 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">⏳ En validation</span>
      <?php endif; ?>
      <span class="bg-surface-container text-on-surface-variant px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase"><?= ucfirst($artisan['plan'] ?? 'Gratuit') ?></span>
    </div>
  </div>

  <nav class="flex flex-col gap-0.5 flex-1">
    <?php
    $mainLinks = array_slice($allLinks, 0, 3);
    $compteLinks = array_slice($allLinks, 3);
    ?>
    <p class="px-4 text-[9px] font-bold uppercase tracking-[.2em] text-outline-variant mb-1">Principal</p>
    <?php foreach ($mainLinks as [$url, $icon, $label]): ?>
      <?= sidebarLink($url, $icon, $label, $current === $url) ?>
    <?php endforeach; ?>

    <p class="px-4 text-[9px] font-bold uppercase tracking-[.2em] text-outline-variant mb-1 mt-4">Mon compte</p>
    <?php foreach ($compteLinks as [$url, $icon, $label]): ?>
      <?= sidebarLink($url, $icon, $label, $current === $url) ?>
    <?php endforeach; ?>
  </nav>

  <div class="mt-4 pt-4 border-t border-outline-variant/10 px-2 space-y-1">
    <a href="<?= APP_URL ?>" class="flex items-center gap-3 text-on-surface-variant px-4 py-2 rounded-xl hover:bg-surface-container transition-colors text-xs">
      <span class="material-symbols-outlined text-base">home</span>
      <span class="font-label text-[11px] uppercase tracking-widest">Site principal</span>
    </a>
    <a href="<?= APP_URL ?>/deconnexion" class="flex items-center gap-3 text-red-500 px-4 py-2 rounded-xl hover:bg-red-50 transition-colors text-xs">
      <span class="material-symbols-outlined text-base">logout</span>
      <span class="font-label text-[11px] uppercase tracking-widest">Déconnexion</span>
    </a>
  </div>
</aside>

<!-- ── Bottom nav mobile (5 raccourcis) ─────────────────────────────────── -->
<nav class="fixed bottom-0 left-0 right-0 z-30 bg-[#faf9f8]/95 backdrop-blur-xl border-t border-outline-variant/10 flex items-center justify-around h-16 md:hidden">
  <?php
  $mobileNav = [
    ['/dashboard/artisan',          'home',                 'Accueil'],
    ['/dashboard/artisan/leads',    'format_list_bulleted', 'Leads'],
    ['/dashboard/artisan/messages', 'chat_bubble',          'Messages'],
    ['/dashboard/artisan/calendar', 'calendar_today',       'Planning'],
    ['/dashboard/artisan/profile',  'account_circle',       'Profil'],
  ];
  foreach ($mobileNav as [$url, $icon, $label]):
    $isActive = $current === $url;
  ?>
    <a href="<?= APP_URL . $url ?>"
      class="flex flex-col items-center justify-center gap-0.5 flex-1 py-2 <?= $isActive ? 'text-primary' : 'text-on-surface-variant' ?> transition-colors">
      <span class="material-symbols-outlined text-[22px]" style="<?= $isActive ? "font-variation-settings:'FILL' 1,'wght' 300,'GRAD' 0,'opsz' 24" : '' ?>"><?= $icon ?></span>
      <span class="text-[9px] font-bold uppercase tracking-widest"><?= $label ?></span>
    </a>
  <?php endforeach; ?>
</nav>

<script>
  function toggleMobMenu() {
    document.getElementById('mob-sidebar').classList.toggle('open');
    document.getElementById('mob-overlay').classList.toggle('open');
    document.body.style.overflow = document.getElementById('mob-sidebar').classList.contains('open') ? 'hidden' : '';
  }
</script>