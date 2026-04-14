<?php /* views/client/messages.php */ ?>
<?php include BASE_PATH . '/views/client/_sidebar.php'; ?>

<main class="md:ml-72 pt-28 px-6 md:px-12 pb-20 max-w-6xl">

  <header class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
    <div>
      <h1 class="text-5xl md:text-6xl font-headline font-medium tracking-tight mb-2">Mes messages</h1>
      <p class="font-body text-on-surface-variant max-w-md">Gérez vos échanges avec les artisans certifiés pour vos projets de rénovation.</p>
    </div>
  </header>

  <?php if (empty($conversations)): ?>
    <div class="text-center py-24">
      <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">chat_bubble</span>
      <h2 class="font-headline text-3xl mb-4">Aucune conversation</h2>
      <p class="text-on-surface-variant mb-8">Les messages apparaissent quand un artisan accepte votre devis</p>
      <a href="<?= APP_URL ?>/devis" class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all inline-block">
        Faire une demande
      </a>
    </div>
  <?php else: ?>
    <div class="flex flex-col gap-4">

      <?php foreach ($conversations as $conv):
        $hasUnread = !empty($conv['unread']) && $conv['unread'] > 0;
        $initiale  = strtoupper(substr($conv['first_name'] ?? 'A', 0, 1));
      ?>
        <a href="<?= APP_URL ?>/dashboard/client/messages/<?= $conv['lead_id'] ?>"
          class="group relative flex items-center gap-6 p-6 <?= $hasUnread ? 'bg-surface-container-lowest border-l-4 border-primary' : 'bg-surface-container-low' ?> rounded-xl transition-all hover:translate-x-1 shadow-sm">

          <!-- Avatar -->
          <div class="relative flex-shrink-0">
            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center border-2 border-primary/10 font-bold text-primary text-xl">
              <?= $initiale ?>
            </div>
            <?php if ($hasUnread): ?>
              <div class="absolute bottom-0 right-0 w-4 h-4 bg-primary border-2 border-white rounded-full"></div>
            <?php endif; ?>
          </div>

          <!-- Contenu -->
          <div class="flex-grow min-w-0">
            <div class="flex items-center gap-3 mb-1">
              <h3 class="text-xl font-semibold leading-none"><?= Security::e($conv['company_name'] ?: ($conv['first_name'] . ' ' . $conv['last_name'])) ?></h3>
              <?php if (!empty($conv['badge_verified'])): ?>
                <span class="flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-primary text-[10px] font-bold uppercase tracking-wider rounded border border-emerald-100">
                  <span class="material-symbols-outlined text-[12px]" style="font-variation-settings:'FILL' 1">verified</span>
                  Certifié
                </span>
              <?php endif; ?>
            </div>
            <p class="<?= $hasUnread ? 'text-on-surface font-semibold' : 'text-on-surface-variant' ?> truncate font-body text-sm">
              <?= Security::e($conv['last_message'] ?? 'Aucun message') ?>
            </p>
            <div class="flex items-center gap-4 mt-2">
              <span class="text-[11px] font-bold text-stone-400 uppercase tracking-widest">
                <?= !empty($conv['last_at']) ? date('d/m H:i', strtotime($conv['last_at'])) : '' ?>
              </span>
              <span class="text-[11px] text-on-surface-variant">Réf. <?= Security::e($conv['reference'] ?? '') ?></span>
            </div>
          </div>

          <!-- Badge non lus -->
          <div class="flex flex-col items-end gap-3 flex-shrink-0">
            <?php if ($hasUnread): ?>
              <span class="w-6 h-6 bg-primary text-on-primary text-[10px] font-bold flex items-center justify-center rounded-full"><?= $conv['unread'] ?></span>
            <?php endif; ?>
            <span class="material-symbols-outlined text-outline group-hover:text-primary transition-colors">chevron_right</span>
          </div>
        </a>
      <?php endforeach; ?>

      <!-- Citation éditoriale -->
      <div class="mt-12 bg-primary rounded-xl overflow-hidden relative">
        <div class="p-12 md:p-16 z-10 relative text-on-primary">
          <span class="material-symbols-outlined text-4xl mb-4 opacity-40">format_quote</span>
          <h4 class="text-3xl font-headline italic mb-6 leading-tight max-w-xl">
            "La communication fluide entre client et artisan est le premier secret d'un chantier réussi."
          </h4>
          <p class="font-body text-xs uppercase tracking-widest font-bold opacity-80">Conseil d'expert InfoDevis</p>
        </div>
      </div>

    </div>
  <?php endif; ?>

</main>