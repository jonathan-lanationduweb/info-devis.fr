<?php
/**
 * Sidebar espace artisan — d'après views/artisan/_sidebar.php :
 * navbar top mobile + drawer + sidebar desktop (groupes Principal /
 * Mon compte) + bottom nav mobile + badge de validation.
 *
 * Args : ['user' => WP_User, 'fiche' => WP_Post|null]
 * Les liens pointent uniquement vers les écrans déjà portés (les écrans
 * restants — agenda, stats, apparence, blog, vérification, RDV — sont
 * ajoutés à mesure du portage ; suivi dans docs/audit-visuel-original.md).
 */

$GLOBALS['idv_has_sidebar'] = true;
$idv_user  = $args['user'] ?? wp_get_current_user();
$idv_fiche = $args['fiche'] ?? null;
$idv_name  = $idv_fiche ? get_the_title($idv_fiche) : ($idv_user->display_name ?: 'Mon espace');

$idv_validated = $idv_fiche && get_post_meta($idv_fiche->ID, '_idc_verification_status', true) === 'validated';
$idv_plan      = $idv_fiche ? get_post_meta($idv_fiche->ID, '_idc_plan', true) : 'gratuit';
$idv_level     = in_array($idv_plan, ['gold', 'illimite', 'pro'], true) ? 'verified_pro' : ($idv_plan === 'silver' ? 'verified' : 'referenced');
$idv_badges    = [
    'referenced'   => ['fa-solid fa-clipboard-check', 'Référencé', 'idv-badge--referenced'],
    'verified'     => ['fa-solid fa-circle-check', 'Vérifié', 'idv-badge--verified'],
    'verified_pro' => ['fa-solid fa-medal', 'Vérifié Pro', 'idv-badge--verified-pro'],
];
[$idv_b_icon, $idv_b_label, $idv_b_class] = $idv_badges[$idv_level];

$idv_main = [
    [home_url('/dashboard/artisan/'), 'dashboard', 'Tableau de bord'],
    [home_url('/dashboard/artisan/leads/'), 'lightbulb', 'Mes opportunités'],
    [home_url('/dashboard/artisan/rdv/'), 'engineering', 'Mes interventions'],
    [home_url('/dashboard/artisan/projets/'), 'apartment', 'Mes réalisations'],
    [home_url('/dashboard/artisan/disponibilites/'), 'event_available', 'Disponibilités'],
    [home_url('/dashboard/artisan/agenda/'), 'calendar_month', 'Mon agenda'],
];
$idv_compte = [
    [home_url('/dashboard/artisan/profile/'), 'account_circle', 'Mon profil'],
    [home_url('/dashboard/artisan/verification/'), 'verified', 'Vérification'],
    [home_url('/dashboard/artisan/stats/'), 'insights', 'Statistiques'],
    [home_url('/dashboard/artisan/avis/'), 'rate_review', 'Mes avis'],
    [home_url('/dashboard/artisan/apparence/'), 'palette', 'Apparence'],
    [home_url('/dashboard/artisan/blog/'), 'edit_note', 'Mes articles'],
    [home_url('/dashboard/artisan/documents/'), 'description', 'Documents'],
    [home_url('/dashboard/artisan/abonnement/'), 'card_membership', 'Abonnement'],
];
$idv_all = array_merge($idv_main, $idv_compte);

$idv_current = trailingslashit((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$idv_active  = static fn(string $url): bool => trailingslashit((string) parse_url($url, PHP_URL_PATH)) === $idv_current;

$idv_link = static function (string $url, string $icon, string $label) use ($idv_active): string {
    $cls = $idv_active($url)
        ? 'flex items-center gap-3 bg-primary/5 text-primary px-4 py-3 rounded-xl font-bold border-l-2 border-primary'
        : 'flex items-center gap-3 text-on-surface-variant px-4 py-3 rounded-xl hover:bg-primary/5 hover:text-primary transition-colors';
    return '<a href="' . esc_url($url) . '" class="' . $cls . '">'
        . '<span class="material-symbols-outlined text-[20px]">' . esc_html($icon) . '</span>'
        . '<span class="font-label text-[11px] uppercase tracking-widest font-bold">' . esc_html($label) . '</span>'
        . '</a>';
};

$idv_badge_html = $idv_validated
    ? '<span class="idv-badge ' . esc_attr($idv_b_class) . ' idv-badge--sm"><i class="' . esc_attr($idv_b_icon) . ' idv-badge__icon"></i><span class="idv-badge__label">' . esc_html($idv_b_label) . '</span></span>'
    : '<span class="bg-yellow-50 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">⏳ En validation</span>';
?>
<style>
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; }
  #mob-sidebar { transform: translateX(100%); transition: transform .28s cubic-bezier(.4, 0, .2, 1); }
  #mob-sidebar.open { transform: translateX(0); }
  #mob-overlay { opacity: 0; pointer-events: none; transition: opacity .28s; }
  #mob-overlay.open { opacity: 1; pointer-events: auto; }
</style>

<!-- ── Navbar top (mobile) ─────────────────────────────────────── -->
<header class="fixed top-0 left-0 right-0 z-40 h-16 bg-[#faf9f8]/90 backdrop-blur-xl border-b border-outline-variant/10 flex items-center justify-between px-4 md:hidden">
  <a href="<?php echo esc_url(home_url('/')); ?>" class="font-headline italic text-xl text-primary">Info-Devis</a>
  <div class="flex items-center gap-1">
    <?php get_template_part('template-parts/notifications-bell'); ?>
    <?php get_template_part('template-parts/user-avatar'); ?>
    <button onclick="toggleMobMenu()" class="idv-tap p-2 rounded-xl hover:bg-surface-container transition-colors" aria-label="Menu">
    <span class="material-symbols-outlined text-on-surface">menu</span>
    </button>
  </div>
</header>

<!-- ── Overlay mobile ──────────────────────────────────────────── -->
<div id="mob-overlay" class="fixed inset-0 z-40 bg-on-surface/50 md:hidden" onclick="toggleMobMenu()"></div>

<!-- ── Sidebar mobile (drawer) ─────────────────────────────────── -->
<aside id="mob-sidebar" class="fixed right-0 top-0 h-full w-72 z-50 bg-[#faf9f8] border-l border-outline-variant/10 flex flex-col py-6 px-4 gap-1 md:hidden overflow-y-auto">
  <div class="flex items-center justify-between mb-6 px-2">
    <span class="font-headline italic text-primary text-xl">Espace Artisan</span>
    <button onclick="toggleMobMenu()" class="p-1.5 rounded-lg hover:bg-surface-container" aria-label="Fermer">
      <span class="material-symbols-outlined">close</span>
    </button>
  </div>

  <div class="px-2 mb-4">
    <p class="font-headline italic text-base text-on-surface font-semibold"><?php echo esc_html($idv_name); ?></p>
    <div class="flex items-center gap-2 mt-1.5 flex-wrap"><?php echo $idv_badge_html; ?></div>
  </div>

  <nav class="flex flex-col gap-0.5 flex-1">
    <?php foreach ($idv_all as [$idv_u, $idv_i, $idv_l]) {
        echo $idv_link($idv_u, $idv_i, $idv_l);
    } ?>
  </nav>

  <div class="mt-4 pt-4 border-t border-outline-variant/10">
    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="flex items-center gap-3 text-red-500 px-4 py-3 rounded-xl hover:bg-red-50 transition-colors">
      <span class="material-symbols-outlined text-[20px]">logout</span>
      <span class="font-label text-[11px] uppercase tracking-widest font-bold">Déconnexion</span>
    </a>
  </div>
</aside>

<!-- ── Sidebar desktop ─────────────────────────────────────────── -->
<aside class="hidden md:flex fixed left-0 top-0 h-full w-72 flex-col py-8 px-6 gap-y-1 bg-[#faf9f8] border-r border-outline-variant/10 z-30 pt-20 overflow-y-auto">

  <div class="mb-6 px-2">
    <h2 class="font-headline italic text-xl text-primary font-semibold min-w-0 truncate mb-2" title="<?php echo esc_attr($idv_name); ?>">
      <?php echo esc_html($idv_name); ?>
    </h2>
    <div class="flex flex-wrap items-center gap-2 mt-2"><?php echo $idv_badge_html; ?></div>
  </div>

  <nav class="flex flex-col gap-0.5 flex-1">
    <p class="px-4 text-[9px] font-bold uppercase tracking-[.2em] text-outline-variant mb-1">Principal</p>
    <?php foreach ($idv_main as [$idv_u, $idv_i, $idv_l]) {
        echo $idv_link($idv_u, $idv_i, $idv_l);
    } ?>

    <p class="px-4 text-[9px] font-bold uppercase tracking-[.2em] text-outline-variant mb-1 mt-4">Mon compte</p>
    <?php foreach ($idv_compte as [$idv_u, $idv_i, $idv_l]) {
        echo $idv_link($idv_u, $idv_i, $idv_l);
    } ?>
  </nav>

  <div class="mt-4 pt-4 border-t border-outline-variant/10 px-2 space-y-1">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-3 text-on-surface-variant px-4 py-2 rounded-xl hover:bg-surface-container transition-colors text-xs">
      <span class="material-symbols-outlined text-base">home</span>
      <span class="font-label text-[11px] uppercase tracking-widest">Site principal</span>
    </a>
    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="flex items-center gap-3 text-red-500 px-4 py-2 rounded-xl hover:bg-red-50 transition-colors text-xs">
      <span class="material-symbols-outlined text-base">logout</span>
      <span class="font-label text-[11px] uppercase tracking-widest">Déconnexion</span>
    </a>
  </div>
</aside>

<!-- ── Bottom nav mobile ───────────────────────────────────────── -->
<nav class="fixed bottom-0 left-0 right-0 z-30 bg-[#faf9f8]/95 backdrop-blur-xl border-t border-outline-variant/10 flex items-center justify-around h-16 md:hidden">
  <?php foreach ([
      [home_url('/dashboard/artisan/'), 'home', 'Accueil'],
      [home_url('/dashboard/artisan/leads/'), 'lightbulb', 'Opportunités'],
      [home_url('/dashboard/artisan/projets/'), 'apartment', 'Projets'],
      [home_url('/dashboard/artisan/avis/'), 'rate_review', 'Avis'],
      [home_url('/dashboard/artisan/profile/'), 'account_circle', 'Profil'],
  ] as [$idv_u, $idv_i, $idv_l]) :
      $idv_on = $idv_active($idv_u);
  ?>
    <a href="<?php echo esc_url($idv_u); ?>"
      class="flex flex-col items-center justify-center gap-0.5 flex-1 py-2 <?php echo $idv_on ? 'text-primary' : 'text-on-surface-variant'; ?> transition-colors">
      <span class="material-symbols-outlined text-[22px]" style="<?php echo $idv_on ? "font-variation-settings:'FILL' 1,'wght' 300,'GRAD' 0,'opsz' 24" : ''; ?>"><?php echo esc_html($idv_i); ?></span>
      <span class="text-[9px] font-bold uppercase tracking-widest"><?php echo esc_html($idv_l); ?></span>
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
