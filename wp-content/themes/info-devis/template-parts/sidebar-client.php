<?php
/**
 * Sidebar espace client — reproduction de views/client/_sidebar.php :
 * navbar top mobile + drawer + sidebar desktop + bottom nav mobile.
 * Args : ['user' => WP_User]
 */

$GLOBALS['idv_has_sidebar'] = true;
$idv_user = $args['user'] ?? wp_get_current_user();
$idv_name = $idv_user->display_name ?: 'Mon espace';

$idv_links = [
    [home_url('/dashboard/client/'), 'dashboard', 'Tableau de bord'],
    [home_url('/dashboard/client/devis/'), 'architecture', 'Mes Projets'],
    [home_url('/dashboard/client/disponibilites/'), 'schedule', 'Dispos artisans'],
    [home_url('/dashboard/client/avis/'), 'star_rate', 'Mes Avis'],
    [home_url('/mes-rdv/'), 'event_available', 'Mes RDV'],
    [home_url('/dashboard/client/profile/'), 'account_circle', 'Mon profil'],
];
$idv_current = trailingslashit((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$idv_is_active = static function (string $url) use ($idv_current): bool {
    return trailingslashit((string) parse_url($url, PHP_URL_PATH)) === $idv_current;
};
?>
<style>
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; }
  #cli-mob-sidebar { transform: translateX(100%); transition: transform .28s cubic-bezier(.4, 0, .2, 1); }
  #cli-mob-sidebar.open { transform: translateX(0); }
  #cli-mob-overlay { opacity: 0; pointer-events: none; transition: opacity .28s; }
  #cli-mob-overlay.open { opacity: 1; pointer-events: auto; }
</style>

<!-- ── Navbar top mobile ──────────────────────────────────────── -->
<header class="fixed top-0 left-0 right-0 z-40 h-16 bg-[#faf9f8]/90 backdrop-blur-xl border-b border-outline-variant/10 flex items-center justify-between px-4 md:hidden">
  <a href="<?php echo esc_url(home_url('/')); ?>" class="font-headline italic text-xl text-primary">Info-Devis</a>
  <div class="flex items-center gap-1">
    <?php get_template_part('template-parts/notifications-bell'); ?>
    <button onclick="cliToggle()" class="idv-tap p-2 rounded-xl hover:bg-surface-container transition-colors" aria-label="Menu">
      <span class="material-symbols-outlined text-on-surface">menu</span>
    </button>
  </div>
</header>

<!-- ── Overlay mobile ─────────────────────────────────────────── -->
<div id="cli-mob-overlay" class="fixed inset-0 z-40 bg-on-surface/50 md:hidden" onclick="cliToggle()"></div>

<!-- ── Sidebar mobile (drawer) ────────────────────────────────── -->
<aside id="cli-mob-sidebar" class="fixed right-0 top-0 h-full w-72 z-50 bg-[#faf9f8] border-l border-outline-variant/10 flex flex-col py-6 px-4 gap-1 md:hidden overflow-y-auto">
  <div class="flex items-center justify-between mb-6 px-2">
    <span class="font-headline italic text-primary text-xl">Espace Client</span>
    <button onclick="cliToggle()" class="p-1.5 rounded-lg hover:bg-surface-container" aria-label="Fermer">
      <span class="material-symbols-outlined">close</span>
    </button>
  </div>

  <div class="px-2 mb-4">
    <p class="font-headline italic text-base text-on-surface font-semibold"><?php echo esc_html($idv_name); ?></p>
    <p class="font-label text-[10px] font-semibold uppercase tracking-widest text-stone-500 mt-1">Espace Client</p>
  </div>

  <nav class="flex flex-col gap-0.5 flex-1">
    <?php foreach ($idv_links as [$idv_url, $idv_icon, $idv_label]) :
        $idv_cls = $idv_is_active($idv_url)
            ? 'flex items-center gap-3 bg-primary/5 text-primary px-4 py-3 rounded-xl font-bold border-l-2 border-primary'
            : 'flex items-center gap-3 text-on-surface-variant px-4 py-3 rounded-xl hover:bg-primary/5 hover:text-primary transition-colors';
    ?>
      <a href="<?php echo esc_url($idv_url); ?>" class="<?php echo $idv_cls; ?>">
        <span class="material-symbols-outlined text-[20px]"><?php echo esc_html($idv_icon); ?></span>
        <span class="font-label text-[11px] uppercase tracking-widest font-bold"><?php echo esc_html($idv_label); ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="mt-4 pt-4 border-t border-outline-variant/10 space-y-1">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-3 text-on-surface-variant px-4 py-3 rounded-xl hover:bg-surface-container transition-colors text-xs">
      <span class="material-symbols-outlined text-base">home</span>
      <span class="font-label text-[11px] uppercase tracking-widest">Site principal</span>
    </a>
    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="flex items-center gap-3 text-red-500 px-4 py-3 rounded-xl hover:bg-red-50 transition-colors">
      <span class="material-symbols-outlined text-[20px]">logout</span>
      <span class="font-label text-[11px] uppercase tracking-widest font-bold">Déconnexion</span>
    </a>
  </div>
</aside>

<!-- ── Sidebar desktop ────────────────────────────────────────── -->
<aside class="hidden md:flex fixed left-0 top-0 h-full w-72 flex-col py-8 px-6 gap-y-4 bg-[#faf9f8] border-r border-outline-variant/10 z-30 pt-28">
  <div class="mb-8 px-4">
    <h2 class="text-primary font-headline text-2xl italic">Tableau de Bord</h2>
    <p class="font-label text-[10px] font-semibold uppercase tracking-widest text-stone-500 opacity-70 mt-1">Espace Client</p>
  </div>
  <nav class="flex flex-col gap-1 flex-1">
    <?php foreach ($idv_links as [$idv_url, $idv_icon, $idv_label]) : ?>
      <a href="<?php echo esc_url($idv_url); ?>"
        class="flex items-center gap-4 px-4 py-3 <?php echo $idv_is_active($idv_url) ? 'text-primary border-r-2 border-primary bg-emerald-50/50 translate-x-1' : 'text-stone-500 hover:text-primary hover:bg-stone-100'; ?> transition-all duration-200 font-label text-sm font-semibold uppercase tracking-wider rounded-lg">
        <span class="material-symbols-outlined"><?php echo esc_html($idv_icon); ?></span>
        <?php echo esc_html($idv_label); ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="mt-auto flex flex-col gap-1 pt-8 border-t border-outline-variant/10">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-4 px-4 py-3 text-stone-500 hover:text-primary hover:bg-stone-100 transition-all font-label text-xs font-semibold uppercase tracking-wider rounded-lg">
      <span class="material-symbols-outlined">home</span> Site principal
    </a>
    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="flex items-center gap-4 px-4 py-3 text-stone-500 hover:text-error hover:bg-stone-100 transition-all font-label text-xs font-semibold uppercase tracking-wider rounded-lg">
      <span class="material-symbols-outlined">logout</span> Déconnexion
    </a>
  </div>
</aside>

<!-- ── Bottom nav mobile ──────────────────────────────────────── -->
<nav class="fixed bottom-0 left-0 right-0 z-30 bg-[#faf9f8]/95 backdrop-blur-xl border-t border-outline-variant/10 flex items-center justify-around h-16 md:hidden">
  <?php foreach ($idv_links as [$idv_url, $idv_icon, $idv_label]) :
      $idv_on = $idv_is_active($idv_url);
  ?>
    <a href="<?php echo esc_url($idv_url); ?>"
      class="flex flex-col items-center justify-center gap-0.5 flex-1 py-2 <?php echo $idv_on ? 'text-primary' : 'text-on-surface-variant'; ?> transition-colors">
      <span class="material-symbols-outlined text-[22px]" style="<?php echo $idv_on ? "font-variation-settings:'FILL' 1,'wght' 300,'GRAD' 0,'opsz' 24" : ''; ?>"><?php echo esc_html($idv_icon); ?></span>
      <span class="text-[9px] font-bold uppercase tracking-widest"><?php echo esc_html($idv_label); ?></span>
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
