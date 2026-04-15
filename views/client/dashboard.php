<?php /* views/client/dashboard.php */ ?>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24
  }

  #mob-cl-sidebar {
    transform: translateX(-100%);
    transition: transform .28s cubic-bezier(.4, 0, .2, 1)
  }

  #mob-cl-sidebar.open {
    transform: translateX(0)
  }

  #mob-cl-overlay {
    opacity: 0;
    pointer-events: none;
    transition: opacity .28s
  }

  #mob-cl-overlay.open {
    opacity: 1;
    pointer-events: auto
  }
</style>

<?php $current = '/' . trim(str_replace('/info-devis', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/'); ?>

<!-- ── Navbar top mobile ─────────────────────────────────────── -->
<header class="fixed top-0 left-0 right-0 z-40 h-16 bg-[#faf9f8]/90 backdrop-blur-xl border-b border-outline-variant/10 flex items-center justify-between px-4 md:hidden">
  <a href="<?= APP_URL ?>" class="font-headline italic text-xl text-primary">Info-Devis</a>
  <button onclick="toggleClMenu()" class="p-2 rounded-xl hover:bg-surface-container transition-colors">
    <span class="material-symbols-outlined">menu</span>
  </button>
</header>

<!-- ── Overlay mobile ────────────────────────────────────────── -->
<div id="mob-cl-overlay" class="fixed inset-0 z-40 bg-on-surface/50 md:hidden" onclick="toggleClMenu()"></div>

<!-- ── Sidebar mobile ────────────────────────────────────────── -->
<aside id="mob-cl-sidebar" class="fixed left-0 top-0 h-full w-64 z-50 bg-[#faf9f8] border-r border-outline-variant/10 flex flex-col py-6 px-4 md:hidden overflow-y-auto">
  <div class="flex items-center justify-between mb-6 px-2">
    <span class="font-headline italic text-primary text-xl">Espace Client</span>
    <button onclick="toggleClMenu()" class="p-1.5 rounded-lg hover:bg-surface-container">
      <span class="material-symbols-outlined">close</span>
    </button>
  </div>
  <nav class="flex flex-col gap-0.5 flex-1">
    <?php foreach (
      [
        ['/dashboard/client',           'dashboard',      'Tableau de bord'],
        ['/dashboard/client/devis',     'architecture',   'Mes Projets'],
        ['/dashboard/client/messages',  'chat_bubble',    'Messages'],
        ['/dashboard/client/calendrier', 'calendar_today', 'Calendriers artisans'],
        ['/dashboard/client/avis',      'star_rate',      'Mes Avis'],
      ] as [$url, $icon, $label]
    ):
      $active = $current === $url;
    ?>
      <a href="<?= APP_URL . $url ?>"
        class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors
              <?= $active ? 'bg-primary/5 text-primary font-bold border-l-2 border-primary' : 'text-on-surface-variant hover:bg-primary/5 hover:text-primary' ?>">
        <span class="material-symbols-outlined text-[20px]"><?= $icon ?></span>
        <span class="font-label text-[11px] uppercase tracking-widest font-bold"><?= $label ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="mt-4 pt-4 border-t border-outline-variant/10">
    <a href="<?= APP_URL ?>/devis"
      class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary text-on-primary font-bold text-xs uppercase tracking-widest hover:opacity-90">
      <span class="material-symbols-outlined text-[20px]">add_circle</span> Nouveau devis
    </a>
  </div>
</aside>

<!-- ── Sidebar desktop ───────────────────────────────────────── -->
<aside class="hidden md:flex fixed left-0 top-20 h-[calc(100vh-5rem)] w-64 bg-[#faf9f8] border-r border-outline-variant/10 flex-col py-8 px-4 gap-2 z-40">
  <div class="mb-4 px-4">
    <h3 class="font-label font-bold uppercase tracking-widest text-xs text-stone-500">Espace Membre</h3>
  </div>
  <nav class="flex-1 flex flex-col gap-1">
    <?php foreach (
      [
        ['/dashboard/client',           'dashboard',      'Tableau de bord'],
        ['/dashboard/client/devis',     'architecture',   'Mes Projets'],
        ['/dashboard/client/messages',  'chat_bubble',    'Messages'],
        ['/dashboard/client/calendrier', 'calendar_today', 'Calendriers artisans'],
        ['/dashboard/client/avis',      'star_rate',      'Mes Avis'],
      ] as [$url, $icon, $label]
    ):
      $active = $current === $url;
    ?>
      <a href="<?= APP_URL . $url ?>"
        class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors
              <?= $active ? 'bg-primary/5 text-primary font-bold' : 'text-stone-500 hover:bg-stone-100' ?>">
        <span class="material-symbols-outlined"><?= $icon ?></span>
        <span class="font-label uppercase tracking-widest text-xs"><?= $label ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="p-4 bg-surface-container-low rounded-xl border border-outline-variant/10">
    <p class="text-xs font-semibold text-on-surface mb-1">Besoin d'aide ?</p>
    <a href="<?= APP_URL ?>/contact" class="text-primary text-xs font-bold flex items-center gap-1">
      Contactez-nous <span class="material-symbols-outlined text-sm">arrow_forward</span>
    </a>
  </div>
</aside>

<!-- ── Bottom nav mobile ─────────────────────────────────────── -->
<nav class="fixed bottom-0 left-0 right-0 z-30 bg-[#faf9f8]/95 backdrop-blur-xl border-t border-outline-variant/10 flex items-center justify-around h-16 md:hidden">
  <?php foreach (
    [
      ['/dashboard/client',           'home',          'Accueil'],
      ['/dashboard/client/devis',     'architecture',  'Projets'],
      ['/dashboard/client/calendrier', 'calendar_today', 'Calendrier'],
      ['/dashboard/client/messages',  'chat_bubble',   'Messages'],
      ['/devis',                      'add_circle',    'Nouveau'],
    ] as [$url, $icon, $label]
  ):
    $active = $current === $url;
  ?>
    <a href="<?= APP_URL . $url ?>"
      class="flex flex-col items-center justify-center gap-0.5 flex-1 py-2 <?= $active ? 'text-primary' : 'text-on-surface-variant' ?> transition-colors">
      <span class="material-symbols-outlined text-[22px]" style="<?= $active ? "font-variation-settings:'FILL' 1,'wght' 300,'GRAD' 0,'opsz' 24" : '' ?>"><?= $icon ?></span>
      <span class="text-[9px] font-bold uppercase tracking-widest"><?= $label ?></span>
    </a>
  <?php endforeach; ?>
</nav>

<!-- ── Contenu principal ─────────────────────────────────────── -->
<main class="md:ml-64 pt-20 md:pt-32 px-4 md:px-12 pb-24 md:pb-20">
  <header class="mb-8 md:mb-12">
    <h1 class="font-headline text-3xl md:text-5xl font-medium tracking-tight italic mb-2">
      Bienvenue, <?= Security::e(explode(' ', $_SESSION['user_name'] ?? 'Client')[0]) ?>
    </h1>
    <p class="text-on-surface-variant text-sm md:text-base opacity-80">
      Aperçu de vos demandes et des artisans disponibles près de chez vous.
    </p>
  </header>

  <?php
  $totalDevis = count($devis ?? []);
  $enCours    = count(array_filter($devis ?? [], fn($d) => in_array($d['status'], ['sent', 'in_progress'])));
  $termines   = count(array_filter($devis ?? [], fn($d) => $d['status'] === 'completed'));
  $nbNotifs   = count($notifs ?? []);
  ?>

  <!-- Stats -->
  <section class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
    <div class="bg-surface-container-lowest p-5 md:p-8 rounded-2xl border border-outline-variant/10 shadow-sm hover:shadow-md transition-all">
      <span class="material-symbols-outlined text-primary mb-3 block text-2xl md:text-[32px]">description</span>
      <p class="text-2xl md:text-3xl font-headline italic font-bold"><?= str_pad($totalDevis, 2, '0', STR_PAD_LEFT) ?></p>
      <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">Total demandes</p>
    </div>
    <div class="bg-surface-container-lowest p-5 md:p-8 rounded-2xl border border-outline-variant/10 shadow-sm hover:shadow-md transition-all">
      <span class="material-symbols-outlined text-tertiary mb-3 block text-2xl md:text-[32px]">pending_actions</span>
      <p class="text-2xl md:text-3xl font-headline italic font-bold"><?= str_pad($enCours, 2, '0', STR_PAD_LEFT) ?></p>
      <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">En cours</p>
    </div>
    <div class="bg-surface-container-lowest p-5 md:p-8 rounded-2xl border border-outline-variant/10 shadow-sm hover:shadow-md transition-all">
      <span class="material-symbols-outlined text-secondary mb-3 block text-2xl md:text-[32px]">task_alt</span>
      <p class="text-2xl md:text-3xl font-headline italic font-bold"><?= str_pad($termines, 2, '0', STR_PAD_LEFT) ?></p>
      <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">Terminées</p>
    </div>
    <div class="bg-primary-container p-5 md:p-8 rounded-2xl border border-primary/10 shadow-sm relative overflow-hidden">
      <div class="relative z-10">
        <span class="material-symbols-outlined text-on-primary-container mb-3 block text-2xl md:text-[32px]">notifications_active</span>
        <p class="text-2xl md:text-3xl font-headline italic font-bold text-on-primary-container"><?= str_pad($nbNotifs, 2, '0', STR_PAD_LEFT) ?></p>
        <p class="text-[10px] uppercase tracking-widest text-on-primary-container mt-1 font-bold">Notifications</p>
      </div>
    </div>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Dernières demandes -->
    <div class="lg:col-span-2 space-y-6">
      <div class="flex justify-between items-baseline">
        <h2 class="font-headline text-xl md:text-2xl font-bold">Dernières Demandes</h2>
        <a href="<?= APP_URL ?>/dashboard/client/devis" class="text-xs font-bold uppercase tracking-widest text-primary border-b border-primary/30 hover:border-primary transition-colors">Tout voir</a>
      </div>

      <?php if (empty($devis)): ?>
        <div class="p-8 rounded-2xl bg-surface-container-low/50 text-center">
          <span class="material-symbols-outlined text-4xl text-outline-variant mb-4 block">inbox</span>
          <p class="text-on-surface-variant mb-4 text-sm">Aucune demande pour le moment</p>
          <a href="<?= APP_URL ?>/devis" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label text-xs uppercase tracking-widest font-bold hover:opacity-90 inline-block">
            Faire ma première demande
          </a>
        </div>
      <?php else: ?>
        <?php
        $sLabels = [
          'sent'        => ['en attente', 'bg-surface-container-high text-on-surface-variant'],
          'in_progress' => ['en cours',  'bg-tertiary-container text-on-tertiary-container'],
          'completed'   => ['terminé',   'bg-primary/10 text-primary'],
          'cancelled'   => ['annulé',    'bg-error-container text-on-error-container'],
        ];
        foreach (array_slice($devis, 0, 5) as $d):
          [$sl, $sc] = $sLabels[$d['status']] ?? ['?', 'bg-stone-100 text-stone-500'];
        ?>
          <div class="flex items-start gap-4 p-4 md:p-5 rounded-2xl bg-surface-container-low/50 hover:bg-surface-container-lowest transition-all border border-transparent hover:border-outline-variant/10">
            <div class="w-12 h-12 rounded-xl bg-surface-container-high flex items-center justify-center flex-shrink-0">
              <span class="material-symbols-outlined text-2xl text-outline-variant">home_repair_service</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap justify-between items-start gap-1 mb-1">
                <span class="<?= $sc ?> text-[9px] uppercase font-bold tracking-widest px-2 py-0.5 rounded"><?= $sl ?></span>
                <span class="text-[10px] text-on-surface-variant"><?= !empty($d['created_at']) ? date('d M Y', strtotime($d['created_at'])) : '' ?></span>
              </div>
              <h4 class="font-headline text-base font-bold truncate"><?= Security::e($d['title'] ?? 'Demande sans titre') ?></h4>
              <p class="text-xs text-on-surface-variant line-clamp-1 mt-0.5"><?= Security::e(substr($d['description'] ?? '', 0, 80)) ?></p>
              <div class="mt-2 flex flex-wrap gap-3">
                <span class="text-[10px] font-bold flex items-center gap-0.5">
                  <span class="material-symbols-outlined text-sm">location_on</span><?= Security::e($d['ville'] ?? '') ?>
                </span>
                <span class="text-[10px] font-bold flex items-center gap-0.5">
                  <span class="material-symbols-outlined text-sm">tag</span><?= Security::e($d['reference'] ?? '') ?>
                </span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- ── Section calendrier artisans disponibles ─────────── -->
      <?php
      // Récupérer les artisans disponibles aujourd'hui ou cette semaine
      $today      = date('Y-m-d');
      $weekEnd    = date('Y-m-d', strtotime('+7 days'));
      $artisansDispos = Database::fetchAll(
        "SELECT DISTINCT a.id, a.company_name, a.ville, a.calendar_description,
                  u.first_name, u.last_name,
                  GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ', ') as categories,
                  MIN(av.date) as next_dispo
           FROM artisans a
           JOIN users u ON u.id = a.user_id
           JOIN availability av ON av.artisan_id = a.id
           LEFT JOIN artisan_categories ac ON ac.artisan_id = a.id
           LEFT JOIN categories c ON c.id = ac.category_id
           WHERE av.status = 'available'
             AND av.date BETWEEN ? AND ?
             AND a.is_verified = 1
           GROUP BY a.id
           ORDER BY next_dispo ASC
           LIMIT 4",
        [$today, $weekEnd]
      );
      ?>
      <?php if (!empty($artisansDispos)): ?>
        <div class="mt-6">
          <div class="flex justify-between items-baseline mb-4">
            <h2 class="font-headline text-xl font-bold flex items-center gap-2">
              <span class="material-symbols-outlined text-primary">calendar_today</span>
              Artisans disponibles cette semaine
            </h2>
            <a href="<?= APP_URL ?>/dashboard/client/calendrier" class="text-xs font-bold uppercase tracking-widest text-primary border-b border-primary/30 hover:border-primary transition-colors">Voir tout</a>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php foreach ($artisansDispos as $art): ?>
              <div class="bg-white rounded-xl border border-outline-variant/15 p-4 hover:border-primary/30 transition-all">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">
                    <?= strtoupper(substr($art['company_name'] ?? 'A', 0, 1)) ?>
                  </div>
                  <div class="min-w-0">
                    <p class="font-semibold text-sm truncate"><?= Security::e($art['company_name']) ?></p>
                    <p class="text-xs text-on-surface-variant flex items-center gap-1">
                      <span class="material-symbols-outlined text-sm">location_on</span>
                      <?= Security::e($art['ville'] ?? '') ?>
                    </p>
                  </div>
                  <span class="ml-auto flex-shrink-0 bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                    ✓ Dispo
                  </span>
                </div>
                <?php if (!empty($art['categories'])): ?>
                  <p class="text-[10px] text-on-surface-variant mb-2 truncate">
                    <?= Security::e($art['categories']) ?>
                  </p>
                <?php endif; ?>
                <?php if (!empty($art['calendar_description'])): ?>
                  <p class="text-xs text-on-surface-variant/70 italic mb-2 line-clamp-2">
                    "<?= Security::e($art['calendar_description']) ?>"
                  </p>
                <?php endif; ?>
                <div class="flex items-center justify-between mt-2">
                  <span class="text-[10px] text-primary font-bold">
                    Prochain dispo : <?= !empty($art['next_dispo']) ? date('d/m', strtotime($art['next_dispo'])) : '—' ?>
                  </span>
                  <a href="<?= APP_URL ?>/dashboard/client/calendrier/<?= (int)$art['id'] ?>"
                    class="text-[10px] font-bold text-primary flex items-center gap-0.5 hover:gap-1 transition-all">
                    Voir calendrier <span class="material-symbols-outlined text-sm">arrow_forward</span>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="mt-6 p-5 bg-surface-container-low/50 rounded-xl border border-outline-variant/10 flex items-center gap-4">
          <span class="material-symbols-outlined text-3xl text-outline-variant">calendar_today</span>
          <div>
            <p class="text-sm font-semibold">Calendriers des artisans</p>
            <p class="text-xs text-on-surface-variant">Consultez les disponibilités des artisans vérifiés de votre région.</p>
            <a href="<?= APP_URL ?>/dashboard/client/calendrier" class="text-xs text-primary font-bold mt-1 inline-flex items-center gap-1">
              Voir les disponibilités <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
      <div class="bg-on-surface text-surface p-6 rounded-2xl shadow-xl relative overflow-hidden">
        <div class="relative z-10">
          <h3 class="font-headline text-xl font-bold mb-3 leading-tight">Un nouveau projet ?</h3>
          <p class="text-sm text-surface/70 mb-5">Laissez nos artisans s'occuper du reste.</p>
          <a href="<?= APP_URL ?>/devis" class="w-full bg-primary text-on-primary py-3 rounded-lg font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all flex justify-center items-center gap-2">
            Nouvelle demande <span class="material-symbols-outlined text-sm">add_circle</span>
          </a>
        </div>
        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent opacity-50 pointer-events-none"></div>
      </div>

      <!-- Accès rapide -->
      <div class="space-y-2">
        <h3 class="font-label uppercase tracking-widest text-[10px] text-stone-500 mb-2">Accès Rapide</h3>
        <?php foreach (
          [
            ['/dashboard/client/messages',  'forum',        'Messages',          'Vos artisans vous attendent',  'text-primary'],
            ['/dashboard/client/calendrier', 'calendar_today', 'Calendriers',       'Disponibilités artisans',      'text-green-600'],
            ['/dashboard/client/avis',      'star_rate',     'Mes Avis',         'Partagez votre expérience',    'text-tertiary'],
            ['/dashboard/client/devis',     'receipt_long',  'Mes demandes',     'Suivre mes devis',             'text-secondary'],
          ] as [$url, $icon, $title, $sub, $color]
        ): ?>
          <a href="<?= APP_URL . $url ?>" class="group flex items-center justify-between p-4 bg-surface-container-low rounded-xl border border-outline-variant/10 hover:border-primary/30 transition-all">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-surface-container-highest flex items-center justify-center <?= $color ?> group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-base"><?= $icon ?></span>
              </div>
              <div>
                <p class="text-sm font-bold text-on-surface"><?= $title ?></p>
                <p class="text-[10px] text-on-surface-variant"><?= $sub ?></p>
              </div>
            </div>
            <span class="material-symbols-outlined text-outline group-hover:text-primary transition-colors">chevron_right</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</main>

<script>
  function toggleClMenu() {
    document.getElementById('mob-cl-sidebar').classList.toggle('open');
    document.getElementById('mob-cl-overlay').classList.toggle('open');
    document.body.style.overflow = document.getElementById('mob-cl-sidebar').classList.contains('open') ? 'hidden' : '';
  }
</script>