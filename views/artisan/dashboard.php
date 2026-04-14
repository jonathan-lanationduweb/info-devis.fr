<?php /* views/artisan/dashboard.php */ ?>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24
  }
</style>

<aside class="fixed left-0 top-20 h-[calc(100vh-5rem)] w-64 border-r border-[#aeb3b2]/15 bg-[#faf9f8] flex flex-col py-8 px-4 gap-2 z-40">
  <div class="mb-8 px-4">
    <p class="font-label uppercase tracking-widest text-[10px] text-stone-400">Espace Membre</p>
    <h3 class="font-headline italic text-lg text-primary">Espace Artisan</h3>
  </div>
  <nav class="flex-1 flex flex-col gap-1">
    <a href="<?= APP_URL ?>/dashboard/artisan" class="flex items-center gap-3 bg-primary/5 text-primary rounded-lg px-4 py-3 font-bold">
      <span class="material-symbols-outlined">dashboard</span>
      <span class="font-label uppercase tracking-widest text-xs">Tableau de bord</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard/artisan/leads" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">format_list_bulleted</span>
      <span class="font-label uppercase tracking-widest text-xs">Mes Leads</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard/artisan/messages" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">chat_bubble</span>
      <span class="font-label uppercase tracking-widest text-xs">Messages</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard/artisan/calendar" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">calendar_today</span>
      <span class="font-label uppercase tracking-widest text-xs">Calendrier</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard/artisan/profile" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">account_circle</span>
      <span class="font-label uppercase tracking-widest text-xs">Profil Public</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard/artisan/stats" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">query_stats</span>
      <span class="font-label uppercase tracking-widest text-xs">Statistiques</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard/artisan/abonnement" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">card_membership</span>
      <span class="font-label uppercase tracking-widest text-xs">Abonnement</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard/artisan/documents" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">description</span>
      <span class="font-label uppercase tracking-widest text-xs">Documents</span>
    </a>
  </nav>
  <div class="p-4 bg-surface-container-low rounded-xl">
    <p class="text-xs font-bold text-on-surface-variant mb-2">Besoin d'aide ?</p>
    <p class="text-[10px] text-outline leading-relaxed">Support disponible lundi au vendredi.</p>
  </div>
</aside>

<main class="ml-64 pt-20 min-h-screen">
  <div class="max-w-6xl mx-auto px-8 py-12">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
      <div class="space-y-2">
        <div class="flex items-center gap-3">
          <h1 class="text-4xl font-headline font-bold tracking-tight"><?= Security::e($artisan['company_name'] ?? $_SESSION['user_name'] ?? 'Mon entreprise') ?></h1>
          <?php if (!empty($artisan['badge_verified']) || ($artisan['verification_status'] ?? '') === 'validated'): ?>
            <span class="flex items-center gap-1 bg-primary-container text-on-primary-container px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
              <span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1,'wght' 300,'GRAD' 0,'opsz' 24">verified</span>
              Vérifié
            </span>
          <?php endif; ?>
        </div>
        <p class="text-on-surface-variant font-medium">Plan <?= ucfirst($artisan['plan'] ?? 'Gratuit') ?></p>
      </div>
      <span class="inline-flex items-center gap-2 bg-surface-container-highest border border-outline-variant/20 px-4 py-2 rounded text-xs font-bold uppercase tracking-tighter text-secondary">
        <span class="material-symbols-outlined text-sm">star</span>
        Plan <?= ucfirst($artisan['plan'] ?? 'Gratuit') ?>
      </span>
    </div>

    <!-- Alerte vérification -->
    <?php if (($artisan['verification_status'] ?? '') === 'pending'): ?>
      <div class="mb-12 bg-tertiary-container text-on-tertiary-container p-4 rounded-xl flex items-center gap-4 border border-tertiary/10">
        <span class="material-symbols-outlined text-tertiary">info</span>
        <p class="text-sm font-medium">Votre dossier est en cours de validation par nos experts. Certaines fonctionnalités peuvent être limitées.</p>
        <a href="<?= APP_URL ?>/dashboard/artisan/documents" class="ml-auto text-xs font-bold uppercase tracking-widest text-tertiary hover:underline flex-shrink-0">Uploader mes docs →</a>
      </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-12">
      <?php
      $kpis = [
        ['Total Leads',   $stats['total_leads'],     null],
        ['En attente',    $stats['pending_leads'],    'text-primary'],
        ['Acceptés',      $stats['accepted_leads'],   null],
        ['Note moyenne',  number_format($stats['avg_rating'], 1) . '/5', null],
        ['Avis',          $stats['total_avis'],       null],
        ['Messages',      $stats['unread_messages'],  'primary'], // special bg
      ];
      foreach ($kpis as $i => [$label, $val, $color]):
        $isLast = $i === 5;
      ?>
        <div class="<?= $isLast ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest border border-outline-variant/10' ?> p-6 rounded-xl shadow-sm">
          <p class="text-[10px] font-bold uppercase tracking-widest <?= $isLast ? 'opacity-70' : 'text-outline-variant' ?> mb-4"><?= $label ?></p>
          <div class="flex items-baseline gap-2">
            <span class="text-3xl font-headline font-bold <?= (!$isLast && $color) ? $color : '' ?>"><?= $val ?></span>
            <?php if ($isLast): ?><span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1,'wght' 300,'GRAD' 0,'opsz' 24">mark_as_unread</span><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Navigation Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <!-- Grande carte leads -->
      <a href="<?= APP_URL ?>/dashboard/artisan/leads"
        class="md:col-span-2 group relative overflow-hidden bg-surface-container-high rounded-2xl aspect-[2/1] p-8 flex flex-col justify-end hover:bg-surface-container-highest transition-colors">
        <div class="absolute top-0 right-0 p-8 opacity-20 group-hover:opacity-30 transition-opacity">
          <span class="material-symbols-outlined text-8xl">format_list_bulleted</span>
        </div>
        <div class="relative z-10">
          <h3 class="text-2xl font-headline font-bold mb-2">Gestion des Leads</h3>
          <p class="text-on-surface-variant text-sm max-w-xs">Consultez et répondez aux nouvelles demandes de devis qualifiées.</p>
          <?php if ($stats['pending_leads'] > 0): ?>
            <span class="mt-3 inline-block bg-primary text-on-primary text-xs font-bold px-3 py-1 rounded-full"><?= $stats['pending_leads'] ?> en attente</span>
          <?php endif; ?>
        </div>
      </a>

      <?php
      $navCards = [
        ['/dashboard/artisan/messages',   'mail',           'Messagerie',    'Échanges avec vos clients'],
        ['/dashboard/artisan/calendar',   'calendar_today', 'Calendrier',    'Rendez-vous et chantiers'],
        ['/dashboard/artisan/profile',    'account_circle', 'Profil Public', 'Portfolio et vitrine'],
        ['/dashboard/artisan/stats',      'query_stats',    'Statistiques',  'Performance et visibilité'],
        ['/dashboard/artisan/abonnement', 'card_membership', 'Abonnement',    'Plan ' . ucfirst($artisan['plan'] ?? 'Gratuit')],
        ['/dashboard/artisan/documents',  'description',    'Documents',     'Assurances et KBIS'],
      ];
      foreach ($navCards as [$url, $icon, $title, $sub]):
      ?>
        <a href="<?= APP_URL . $url ?>" class="group bg-surface-container-low rounded-2xl p-8 flex flex-col justify-between hover:shadow-lg transition-all border border-outline-variant/5">
          <span class="material-symbols-outlined text-primary text-3xl"><?= $icon ?></span>
          <div>
            <h3 class="text-lg font-headline font-bold"><?= $title ?></h3>
            <p class="text-outline text-xs mt-1"><?= $sub ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Citation éditoriale -->
    <div class="mt-20 p-12 bg-surface-container-highest rounded-3xl relative overflow-hidden">
      <span class="absolute top-0 left-4 text-primary/10 text-[120px] font-headline select-none">"</span>
      <div class="relative z-10 max-w-2xl">
        <p class="text-xl font-headline italic text-on-surface mb-6 leading-relaxed">
          Le succès d'un artisan sur Info-Devis repose sur la réactivité. Les clients qui reçoivent une réponse dans les 2 heures ont un taux de conversion 3 fois plus élevé.
        </p>
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
            <span class="material-symbols-outlined text-primary">person</span>
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-widest text-primary">Conseil de l'Expert</p>
            <p class="text-sm font-headline font-medium">L'équipe InfoDevis</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</main>