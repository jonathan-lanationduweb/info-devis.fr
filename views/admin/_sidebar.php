<?php /* views/admin/_sidebar.php */ ?>
<style>.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}</style>
<aside class="fixed left-0 top-0 h-full w-72 bg-[#faf9f8] flex flex-col py-8 px-6 gap-y-4 pt-28 hidden lg:flex z-30 border-r border-outline-variant/10">
  <div class="mb-8 px-4">
    <h1 class="font-headline italic font-medium text-2xl text-primary">Administration</h1>
    <p class="text-xs uppercase tracking-[0.15em] font-semibold text-stone-400 mt-1">InfoDevis Admin</p>
  </div>
  <nav class="flex-1 space-y-1">
    <?php
    $current = '/' . trim(str_replace('/info-devis','', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');
    $links = [
      ['/admin',              'dashboard',   'Tableau de bord'],
      ['/admin/artisans',     'handyman',    'Artisans'],
      ['/admin/devis',        'description', 'Devis'],
      ['/admin/users',        'group',       'Utilisateurs'],
      ['/admin/blog',         'edit_note',   'Blog'],
      ['/admin/paiements',    'payments',    'Paiements'],
      ['/admin/abonnements',  'card_membership','Abonnements'],
      ['/admin/metrics',      'query_stats', 'Métriques'],
      ['/admin/categories',   'category',    'Catégories'],
    ];
    foreach ($links as [$url, $icon, $label]):
      $active = ($current === $url) || ($url !== '/admin' && str_starts_with($current, $url));
    ?>
    <a href="<?= APP_URL . $url ?>"
       class="flex items-center px-4 py-3 gap-4 <?= $active ? 'text-primary border-r-2 border-primary bg-emerald-50/50 translate-x-1' : 'text-stone-500 hover:bg-stone-100 hover:text-primary' ?> transition-all duration-200 rounded-lg group font-label text-sm font-semibold uppercase tracking-wider">
      <span class="material-symbols-outlined"><?= $icon ?></span>
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="mt-auto pt-8 border-t border-stone-100 space-y-1">
    <a href="<?= APP_URL ?>" class="flex items-center px-4 py-3 gap-4 text-stone-500 hover:bg-stone-100 transition-all rounded-lg font-label text-xs font-semibold uppercase tracking-wider">
      <span class="material-symbols-outlined">home</span> Site principal
    </a>
    <a href="<?= APP_URL ?>/deconnexion" class="flex items-center px-4 py-3 gap-4 text-stone-500 hover:text-error hover:bg-stone-100 transition-all rounded-lg font-label text-xs font-semibold uppercase tracking-wider">
      <span class="material-symbols-outlined">logout</span> Déconnexion
    </a>
  </div>
</aside>
