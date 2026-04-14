<?php /* views/client/devis.php */ ?>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
    vertical-align: middle
  }
</style>

<?php include BASE_PATH . '/views/client/_sidebar.php'; ?>

<main class="md:ml-72 pt-32 pb-20 px-8 md:px-16 min-h-screen">

  <!-- Header -->
  <header class="flex flex-col md:flex-row justify-between items-end gap-6 mb-16 border-b border-outline-variant/10 pb-12">
    <div class="max-w-2xl">
      <div class="flex items-center gap-4 mb-4">
        <span class="bg-primary/10 text-primary px-3 py-1 text-[10px] font-bold tracking-[0.2em] uppercase rounded-full">Mes projets</span>
        <span class="h-px w-12 bg-outline-variant/30"></span>
      </div>
      <h1 class="text-5xl md:text-6xl font-headline italic font-bold text-on-surface leading-tight">
        Mes demandes
        <span class="text-outline-variant/40 not-italic font-light">(<?= count($devis ?? []) ?>)</span>
      </h1>
      <p class="mt-4 text-on-surface-variant font-body text-lg leading-relaxed max-w-lg">
        Gérez vos projets de rénovation et suivez l'avancement de vos devis.
      </p>
    </div>
    <a href="<?= APP_URL ?>/devis"
      class="flex items-center gap-3 bg-primary text-on-primary px-8 py-4 font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all shadow-xl shadow-primary/10 rounded-lg">
      <span class="material-symbols-outlined text-lg">add</span>
      Nouvelle demande
    </a>
  </header>

  <!-- Filtres -->
  <div class="flex gap-10 mb-12 overflow-x-auto pb-4">
    <?php
    $filters = ['all' => 'Tous', 'sent' => 'En attente', 'in_progress' => 'En cours', 'completed' => 'Terminé', 'cancelled' => 'Annulé'];
    $activeFilter = $_GET['status'] ?? 'all';
    foreach ($filters as $val => $label):
      $active = $activeFilter === $val;
    ?>
      <a href="?status=<?= $val ?>"
        class="<?= $active ? 'text-primary border-b-2 border-primary' : 'text-stone-400 hover:text-primary' ?> pb-2 text-xs font-bold uppercase tracking-widest transition-all whitespace-nowrap">
        <?= $label ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php
  $filteredDevis = $devis ?? [];
  if ($activeFilter !== 'all') {
    $filteredDevis = array_filter($filteredDevis, fn($d) => $d['status'] === $activeFilter);
  }
  ?>

  <?php if (empty($filteredDevis)): ?>
    <div class="text-center py-24">
      <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">inbox</span>
      <h2 class="font-headline text-3xl mb-4">Aucune demande</h2>
      <p class="text-on-surface-variant mb-8">Vous n'avez pas encore fait de demande de devis.</p>
      <a href="<?= APP_URL ?>/devis" class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all inline-block">
        Faire ma première demande
      </a>
    </div>
  <?php else: ?>

    <!-- Grille des demandes -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-x-12 gap-y-16">
      <?php
      $statusConfig = [
        'sent'        => ['En attente',  'bg-secondary-container text-on-secondary-container'],
        'in_progress' => ['En cours',    'bg-primary-container text-on-primary-container'],
        'completed'   => ['Terminé',     'bg-emerald-100 text-emerald-800'],
        'cancelled'   => ['Annulé',      'bg-stone-200 text-stone-500'],
      ];
      foreach ($filteredDevis as $d):
        [$statusLabel, $statusClass] = $statusConfig[$d['status']] ?? ['Inconnu', 'bg-gray-100 text-gray-600'];
        $isCancelled = $d['status'] === 'cancelled';
      ?>
        <div class="group relative <?= $isCancelled ? 'bg-stone-50/50 border border-dashed border-stone-200 grayscale' : 'bg-surface-container-low hover:bg-surface-container' ?> p-10 transition-all duration-500 rounded-xl">
          <?php if (!$isCancelled): ?>
            <div class="absolute -top-4 -left-4 w-24 h-24 bg-primary/5 -z-10 group-hover:scale-110 transition-transform rounded"></div>
          <?php endif; ?>

          <div class="flex justify-between items-start mb-10">
            <div class="flex flex-col gap-1">
              <span class="text-[10px] font-bold text-stone-400 uppercase tracking-tighter font-mono">Réf. <?= Security::e($d['reference'] ?? '') ?></span>
              <h3 class="text-3xl font-headline font-bold text-on-surface <?= !$isCancelled ? 'group-hover:text-primary transition-colors' : 'text-stone-400' ?>">
                <?= Security::e($d['title'] ?? 'Demande sans titre') ?>
              </h3>
            </div>
            <span class="<?= $statusClass ?> px-4 py-1.5 text-[10px] font-bold uppercase tracking-widest rounded-full flex-shrink-0"><?= $statusLabel ?></span>
          </div>

          <div class="flex gap-8 mb-10 <?= $isCancelled ? 'opacity-50' : '' ?>">
            <div class="w-24 h-24 rounded-xl bg-surface-container-high flex items-center justify-center flex-shrink-0">
              <span class="material-symbols-outlined text-3xl text-outline-variant">home_repair_service</span>
            </div>
            <div class="flex flex-col justify-center gap-3">
              <div class="flex items-center gap-3 text-on-surface-variant">
                <span class="material-symbols-outlined text-lg">location_on</span>
                <span class="text-sm"><?= Security::e($d['ville'] ?? 'Ville non précisée') ?></span>
              </div>
              <?php if (!empty($d['created_at'])): ?>
                <div class="flex items-center gap-3 text-on-surface-variant">
                  <span class="material-symbols-outlined text-lg">calendar_today</span>
                  <span class="text-xs"><?= date('d F Y', strtotime($d['created_at'])) ?></span>
                </div>
              <?php endif; ?>
              <p class="text-sm text-on-surface-variant line-clamp-2"><?= Security::e(substr($d['description'] ?? '', 0, 100)) ?></p>
            </div>
          </div>

          <div class="flex items-center justify-between pt-8 border-t <?= $isCancelled ? 'border-stone-100' : 'border-outline-variant/20' ?>">
            <div class="flex flex-col">
              <span class="text-[10px] text-stone-400 uppercase font-bold tracking-widest mb-1">Statut</span>
              <span class="text-sm font-semibold text-on-surface"><?= $statusLabel ?></span>
            </div>

            <?php if ($d['status'] === 'in_progress'): ?>
              <a href="<?= APP_URL ?>/dashboard/client/signature/<?= $d['id'] ?>"
                class="bg-on-surface text-surface px-6 py-3 text-[10px] font-bold uppercase tracking-[0.2em] hover:bg-primary transition-colors flex items-center gap-3 rounded-lg">
                Signer le devis
                <span class="material-symbols-outlined text-sm">edit_document</span>
              </a>
            <?php elseif ($d['status'] === 'completed'): ?>
              <a href="<?= APP_URL ?>/dashboard/client/avis"
                class="bg-primary/5 text-primary px-6 py-3 text-[10px] font-bold uppercase tracking-[0.2em] border border-primary/20 hover:bg-primary/10 transition-colors flex items-center gap-3 rounded-lg">
                Laisser un avis
                <span class="material-symbols-outlined text-sm">grade</span>
              </a>
            <?php elseif ($d['status'] === 'sent'): ?>
              <span class="text-on-surface px-6 py-3 text-[10px] font-bold uppercase tracking-[0.2em] border border-outline-variant/30 rounded-lg text-on-surface-variant">
                En attente d'artisans
              </span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

  <!-- Citation éditoriale -->
  <section class="mt-24">
    <div class="bg-surface-container-highest p-16 relative overflow-hidden rounded-xl">
      <span class="absolute -top-10 -left-10 text-[12rem] font-headline text-on-surface opacity-5 select-none">"</span>
      <div class="relative z-10 max-w-3xl mx-auto text-center">
        <p class="text-2xl md:text-3xl font-headline italic text-on-surface mb-8 leading-relaxed">
          La qualité d'un devis ne se mesure pas seulement au prix, mais à la précision des détails techniques fournis.
        </p>
        <div class="flex flex-col items-center gap-2">
          <span class="h-8 w-px bg-primary mb-2"></span>
          <p class="text-xs text-on-surface-variant italic">L'équipe InfoDevis</p>
        </div>
      </div>
    </div>
  </section>

</main>