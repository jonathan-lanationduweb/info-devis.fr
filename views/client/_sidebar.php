<?php /* views/client/_sidebar.php */ ?>
<style>
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; }
  #cli-mob-sidebar {
    transform: translateX(-100%);
    transition: transform .28s cubic-bezier(.4, 0, .2, 1);
  }
  #cli-mob-sidebar.open { transform: translateX(0); }
  #cli-mob-overlay { opacity: 0; pointer-events: none; transition: opacity .28s; }
  #cli-mob-overlay.open { opacity: 1; pointer-events: auto; }
</style>

<?php
$current = '/' . trim(str_replace('/info-devis', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');
$links = [
  ['/dashboard/client',          'dashboard',   'Tableau de bord'],
  ['/dashboard/client/devis',    'architecture','Mes Projets'],
  ['/dashboard/client/messages', 'chat_bubble', 'Messages'],
  ['/dashboard/client/avis',     'star_rate',   'Mes Avis'],
];
$clientName = $_SESSION['user_name'] ?? 'Mon espace';
?>

<!-- ── Navbar top mobile ──────────────────────────────────────────────────── -->
<header class="fixed top-0 left-0 right-0 z-40 h-16 bg-[#faf9f8]/90 backdrop-blur-xl border-b border-outline-variant/10 flex items-center justify-between px-4 md:hidden">
  <a href="<?= APP_URL ?>" class="font-headline italic text-xl text-primary">Info-Devis</a>
  <button onclick="cliToggle()" class="p-2 rounded-xl hover:bg-surface-container transition-colors" aria-label="Menu">
    <span class="material-symbols-outlined text-on-surface">menu</span>
  </button>
</header>

<!-- ── Overlay mobile ────────────────────────────────────────────────────── -->
<div id="cli-mob-overlay"
  class="fixed inset-0 z-40 bg-on-surface/50 md:hidden"
  onclick="cliToggle()"></div>

<!-- ── Sidebar mobile (drawer) ───────────────────────────────────────────── -->
<aside id="cli-mob-sidebar"
  class="fixed left-0 top-0 h-full w-72 z-50 bg-[#faf9f8] border-r border-outline-variant/10 flex flex-col py-6 px-4 gap-1 md:hidden overflow-y-auto">

  <div class="flex items-center justify-between mb-6 px-2">
    <span class="font-headline italic text-primary text-xl">Espace Client</span>
    <button onclick="cliToggle()" class="p-1.5 rounded-lg hover:bg-surface-container">
      <span class="material-symbols-outlined">close</span>
    </button>
  </div>

  <div class="px-2 mb-4">
    <p class="font-headline italic text-base text-on-surface font-semibold"><?= htmlspecialchars($clientName) ?></p>
    <p class="font-label text-[10px] font-semibold uppercase tracking-widest text-stone-500 mt-1">Espace Client</p>
  </div>

  <nav class="flex flex-col gap-0.5 flex-1">
    <?php foreach ($links as [$url, $icon, $label]):
      $active = $current === $url;
      $cls = $active
        ? 'flex items-center gap-3 bg-primary/5 text-primary px-4 py-3 rounded-xl font-bold border-l-2 border-primary'
        : 'flex items-center gap-3 text-on-surface-variant px-4 py-3 rounded-xl hover:bg-primary/5 hover:text-primary transition-colors';
    ?>
      <a href="<?= APP_URL . $url ?>" class="<?= $cls ?>">
        <span class="material-symbols-outlined text-[20px]"><?= $icon ?></span>
        <span class="font-label text-[11px] uppercase tracking-widest font-bold"><?= $label ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="mt-4 pt-4 border-t border-outline-variant/10 space-y-1">
    <a href="<?= APP_URL ?>" class="flex items-center gap-3 text-on-surface-variant px-4 py-3 rounded-xl hover:bg-surface-container transition-colors text-xs">
      <span class="material-symbols-outlined text-base">home</span>
      <span class="font-label text-[11px] uppercase tracking-widest">Site principal</span>
    </a>
    <a href="<?= APP_URL ?>/deconnexion" class="flex items-center gap-3 text-red-500 px-4 py-3 rounded-xl hover:bg-red-50 transition-colors">
      <span class="material-symbols-outlined text-[20px]">logout</span>
      <span class="font-label text-[11px] uppercase tracking-widest font-bold">Déconnexion</span>
    </a>
  </div>
</aside>

<!-- ── Sidebar desktop ────────────────────────────────────────────────────── -->
<aside class="hidden md:flex fixed left-0 top-0 h-full w-72 flex-col py-8 px-6 gap-y-4 bg-[#faf9f8] border-r border-outline-variant/10 z-30 pt-28">
  <div class="mb-8 px-4">
    <h2 class="text-primary font-headline text-2xl italic">Tableau de Bord</h2>
    <p class="font-label text-[10px] font-semibold uppercase tracking-widest text-stone-500 opacity-70 mt-1">Espace Client</p>
  </div>
  <nav class="flex flex-col gap-1 flex-1">
    <?php foreach ($links as [$url, $icon, $label]):
      $active = str_starts_with($current, str_replace(APP_URL, '', APP_URL . $url));
    ?>
      <a href="<?= APP_URL . $url ?>"
        class="flex items-center gap-4 px-4 py-3 <?= $active ? 'text-primary border-r-2 border-primary bg-emerald-50/50 translate-x-1' : 'text-stone-500 hover:text-primary hover:bg-stone-100' ?> transition-all duration-200 font-label text-sm font-semibold uppercase tracking-wider rounded-lg">
        <span class="material-symbols-outlined"><?= $icon ?></span>
        <?= $label ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="mt-auto flex flex-col gap-1 pt-8 border-t border-outline-variant/10">
    <a href="<?= APP_URL ?>" class="flex items-center gap-4 px-4 py-3 text-stone-500 hover:text-primary hover:bg-stone-100 transition-all font-label text-xs font-semibold uppercase tracking-wider rounded-lg">
      <span class="material-symbols-outlined">home</span> Site principal
    </a>
    <a href="<?= APP_URL ?>/deconnexion" class="flex items-center gap-4 px-4 py-3 text-stone-500 hover:text-error hover:bg-stone-100 transition-all font-label text-xs font-semibold uppercase tracking-wider rounded-lg">
      <span class="material-symbols-outlined">logout</span> Déconnexion
    </a>
  </div>
</aside>

<!-- ── Bottom nav mobile ──────────────────────────────────────────────────── -->
<nav class="fixed bottom-0 left-0 right-0 z-30 bg-[#faf9f8]/95 backdrop-blur-xl border-t border-outline-variant/10 flex items-center justify-around h-16 md:hidden">
  <?php foreach ($links as [$url, $icon, $label]):
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
  function cliToggle() {
    document.getElementById('cli-mob-sidebar').classList.toggle('open');
    document.getElementById('cli-mob-overlay').classList.toggle('open');
    document.body.style.overflow = document.getElementById('cli-mob-sidebar').classList.contains('open') ? 'hidden' : '';
  }
</script>
