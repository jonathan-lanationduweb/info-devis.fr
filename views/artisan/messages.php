<?php /* views/artisan/messages.php */ ?>
<?php include __DIR__ . '/_sidebar.php'; ?>

<main class="md:ml-72 pt-28 px-6 md:px-12 pb-20 max-w-6xl">

  <header class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
    <div>
      <h1 class="text-5xl font-headline font-medium tracking-tight mb-2">Messages</h1>
      <p class="font-body text-on-surface-variant max-w-md">Répondez rapidement pour améliorer votre taux de conversion.</p>
    </div>
    <a href="<?= APP_URL ?>/dashboard/artisan"
      class="flex items-center gap-2 bg-surface-container border border-outline-variant/20 text-on-surface px-5 py-2.5 rounded-xl font-label text-xs font-bold uppercase tracking-widest hover:border-primary hover:text-primary transition-all">
      <span class="material-symbols-outlined text-[18px]">home</span>
      Dashboard
    </a>
  </header>

  <?php if (empty($conversations)): ?>
    <div class="text-center py-24 bg-surface-container-lowest rounded-2xl border border-outline-variant/10">
      <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">chat_bubble</span>
      <h2 class="font-headline text-3xl mb-4">Aucune conversation</h2>
      <p class="text-on-surface-variant mb-8">Les messages apparaissent quand vous acceptez un lead</p>
      <a href="<?= APP_URL ?>/dashboard/artisan/leads"
        class="bg-primary text-on-primary px-8 py-4 rounded-xl font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 inline-block">
        Voir mes leads
      </a>
    </div>
  <?php else: ?>

    <div class="flex flex-col gap-3">
      <?php foreach ($conversations as $conv):
        $hasUnread = !empty($conv['unread']) && $conv['unread'] > 0;
        $initiale  = strtoupper(substr($conv['first_name'] ?? 'C', 0, 1));
      ?>
        <a href="<?= APP_URL ?>/dashboard/artisan/messages/<?= $conv['lead_id'] ?>"
          class="group flex items-center gap-6 p-6 <?= $hasUnread ? 'bg-surface-container-lowest border-l-4 border-primary shadow-sm' : 'bg-surface-container-low hover:bg-surface-container' ?> rounded-xl transition-all hover:translate-x-1">
          <div class="relative flex-shrink-0">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center font-bold text-primary text-lg border border-primary/10">
              <?= $initiale ?>
            </div>
            <?php if ($hasUnread): ?>
              <div class="absolute -top-1 -right-1 w-5 h-5 bg-primary text-on-primary text-[10px] font-bold flex items-center justify-center rounded-full border-2 border-white">
                <?= min($conv['unread'], 9) ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="flex-grow min-w-0">
            <div class="flex items-center justify-between mb-1">
              <h3 class="text-lg font-semibold truncate"><?= Security::e(trim(($conv['first_name'] ?? '') . ' ' . ($conv['last_name'] ?? ''))) ?></h3>
              <span class="text-[11px] font-bold text-stone-400 uppercase tracking-widest flex-shrink-0 ml-3">
                <?= !empty($conv['last_at']) ? date('d/m H:i', strtotime($conv['last_at'])) : '' ?>
              </span>
            </div>
            <p class="<?= $hasUnread ? 'text-on-surface font-semibold' : 'text-on-surface-variant' ?> truncate font-body text-sm mb-1">
              <?= Security::e($conv['last_message'] ?? 'Pas encore de message') ?>
            </p>
            <span class="text-[11px] text-outline-variant font-mono">Réf. <?= Security::e($conv['reference'] ?? '') ?></span>
          </div>
          <span class="material-symbols-outlined text-outline-variant group-hover:text-primary transition-colors flex-shrink-0">chevron_right</span>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="mt-12 bg-primary rounded-2xl p-10 text-on-primary">
      <span class="material-symbols-outlined text-3xl mb-3 opacity-40 block">format_quote</span>
      <p class="text-2xl font-headline italic mb-4 leading-tight max-w-xl">
        "Le succès d'un artisan sur InfoDevis repose sur la réactivité. Les clients qui reçoivent une réponse dans les 2 heures ont un taux de conversion 3 fois plus élevé."
      </p>
      <p class="font-body text-xs uppercase tracking-widest font-bold opacity-70">Conseil de l'expert</p>
    </div>

  <?php endif; ?>
</main>