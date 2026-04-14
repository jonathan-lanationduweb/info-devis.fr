<?php /* views/admin/artisans.php */ ?>
<?php include BASE_PATH . '/views/admin/_sidebar.php'; ?>

<main class="lg:ml-72 pt-28 px-8 pb-12">
  <div class="max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
      <div>
        <h1 class="font-headline text-5xl font-bold text-on-surface mb-2">Gestion artisans</h1>
        <p class="text-on-surface-variant font-body">Supervisez et validez les professionnels rejoignant le réseau InfoDevis.</p>
      </div>
      <div class="flex items-center gap-3 bg-surface-container-low p-1 rounded-xl">
        <?php foreach (['pending' => '⏳ En attente', 'validated' => '✅ Validés', 'refused' => '❌ Refusés'] as $s => $l): ?>
          <a href="?status=<?= $s ?>"
            class="px-6 py-2.5 rounded-lg text-sm font-semibold tracking-wide transition-all duration-300 <?= $status === $s ? 'bg-surface-container-lowest shadow-sm text-primary' : 'text-on-surface-variant hover:text-primary' ?>">
            <?= $l ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
      <div class="bg-surface-container-low p-6 rounded-xl border-l-4 border-primary">
        <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Total artisans</p>
        <p class="text-3xl font-headline font-bold text-primary"><?= number_format($total) ?></p>
      </div>
      <div class="bg-surface-container-low p-6 rounded-xl border-l-4 border-tertiary">
        <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">En attente</p>
        <p class="text-3xl font-headline font-bold text-tertiary"><?= $status === 'pending' ? $total : 0 ?></p>
      </div>
      <div class="bg-surface-container-low p-6 rounded-xl border-l-4 border-outline-variant">
        <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1">Page</p>
        <p class="text-3xl font-headline font-bold text-on-surface"><?= $page ?> / <?= $pages ?></p>
      </div>
    </div>

    <!-- Liste artisans -->
    <?php if (empty($artisans)): ?>
      <div class="text-center py-20">
        <span class="material-symbols-outlined text-6xl text-outline-variant mb-4 block">check_circle</span>
        <p class="text-on-surface-variant">Aucun artisan dans ce statut</p>
      </div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($artisans as $a): ?>
          <div class="bg-surface-container-lowest p-6 rounded-xl flex flex-col md:flex-row items-center gap-8 group hover:bg-surface-container-low transition-colors shadow-sm border border-transparent hover:border-outline-variant/10" id="artisan-<?= $a['id'] ?>">

            <!-- Avatar -->
            <div class="flex-shrink-0 relative">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center font-bold text-primary text-2xl">
                <?= strtoupper(substr($a['company_name'] ?? $a['first_name'], 0, 1)) ?>
              </div>
              <?php if ($a['verification_status'] === 'validated'): ?>
                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-primary border-2 border-white rounded-full flex items-center justify-center">
                  <span class="material-symbols-outlined text-white text-[10px]" style="font-variation-settings:'FILL' 1">verified</span>
                </div>
              <?php endif; ?>
            </div>

            <!-- Infos -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-1">
                <h3 class="text-lg font-bold text-on-surface truncate"><?= Security::e($a['company_name']) ?></h3>
                <?php if ($a['siret_verified']): ?>
                  <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-tighter bg-primary/10 text-primary border border-primary/20">SIRET ✓</span>
                <?php endif; ?>
                <?php
                $planColors = ['pro' => 'bg-tertiary/10 text-tertiary', 'starter' => 'bg-blue-100 text-blue-700', 'illimite' => 'bg-yellow-100 text-yellow-700', 'gratuit' => 'bg-stone-200 text-stone-600'];
                $pc = $planColors[$a['plan'] ?? 'gratuit'] ?? 'bg-stone-200 text-stone-600';
                ?>
                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-tighter <?= $pc ?>"><?= ucfirst($a['plan'] ?? 'Gratuit') ?></span>
              </div>
              <div class="flex flex-wrap items-center gap-x-6 gap-y-1 text-sm text-on-surface-variant font-medium">
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">person</span> <?= Security::e($a['first_name'] . ' ' . $a['last_name']) ?></span>
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">email</span> <?= Security::e($a['email']) ?></span>
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[18px]">pin_drop</span> <?= Security::e(($a['ville'] ?? '') . ' ' . ($a['code_postal'] ?? '')) ?></span>
                <span class="flex items-center gap-1.5 text-xs font-mono bg-surface-container px-2 py-0.5 rounded">SIRET: <?= Security::e($a['siret'] ?? '') ?></span>
                <span class="text-xs text-outline-variant">Inscrit <?= !empty($a['reg_date']) ? date('d/m/Y', strtotime($a['reg_date'])) : '' ?></span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 flex-shrink-0">
              <!-- Voir dossier — toujours visible -->
              <a href="<?= APP_URL ?>/admin/artisan/<?= $a['id'] ?>"
                class="flex items-center gap-2 px-4 py-2.5 border border-outline-variant/30 text-on-surface rounded-full text-xs font-bold uppercase tracking-widest hover:border-primary hover:text-primary transition-all">
                <span class="material-symbols-outlined text-base">folder_open</span>
                Voir dossier
              </a>
              <?php if ($status === 'pending'): ?>
                <button onclick="validateArtisan(<?= $a['id'] ?>, 'refuse')"
                  class="flex items-center justify-center w-10 h-10 rounded-full border border-error/20 text-error hover:bg-error hover:text-white transition-all duration-300">
                  <span class="material-symbols-outlined">close</span>
                </button>
                <button onclick="validateArtisan(<?= $a['id'] ?>, 'validate')"
                  class="flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-on-primary font-bold text-xs tracking-wide hover:opacity-90 transition-all shadow-md shadow-primary/10">
                  ✅ Valider
                </button>
              <?php else: ?>
                <span class="text-xs font-label uppercase tracking-widest text-on-surface-variant px-3"><?= ucfirst($a['verification_status']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pages > 1): ?>
        <div class="mt-12 flex items-center justify-between border-t border-outline-variant/10 pt-8">
          <?php if ($page > 1): ?>
            <a href="?status=<?= $status ?>&page=<?= $page - 1 ?>" class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-on-surface-variant hover:text-primary transition-colors">
              <span class="material-symbols-outlined">arrow_back</span> Précédent
            </a>
          <?php else: ?><span></span><?php endif; ?>
          <div class="flex items-center gap-4">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
              <a href="?status=<?= $status ?>&page=<?= $i ?>"
                class="text-sm font-bold <?= $i === $page ? 'text-primary underline underline-offset-8' : 'text-on-surface-variant hover:text-primary cursor-pointer transition-colors' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>
          </div>
          <?php if ($page < $pages): ?>
            <a href="?status=<?= $status ?>&page=<?= $page + 1 ?>" class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-on-surface-variant hover:text-primary transition-colors">
              Suivant <span class="material-symbols-outlined">arrow_forward</span>
            </a>
          <?php else: ?><span></span><?php endif; ?>
        </div>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</main>

<script>
  async function validateArtisan(id, action) {
    const note = action === 'refuse' ? prompt('Motif du refus (optionnel):') : '';
    if (action === 'refuse' && note === null) return;
    const form = new FormData();
    form.append('artisan_id', id);
    form.append('action', action);
    form.append('note', note || '');
    form.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    const res = await fetch('<?= APP_URL ?>/admin/artisan/validate', {
      method: 'POST',
      body: form
    });
    const data = await res.json();
    if (data.success) document.getElementById('artisan-' + id).remove();
    else alert('Erreur');
  }
</script>