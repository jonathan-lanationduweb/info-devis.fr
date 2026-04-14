<?php /* views/artisan/leads.php */ ?>
<?php include __DIR__ . '/_sidebar.php'; ?>

<main class="md:ml-72 pt-28 px-8 pb-12">

  <div class="mb-12 max-w-5xl">
    <h1 class="text-4xl md:text-5xl font-headline italic text-on-surface mb-4">Mes opportunités</h1>
    <p class="text-on-surface-variant max-w-2xl font-body leading-relaxed">
      Gérez vos demandes entrantes. Chaque lead est qualifié par nos soins pour garantir la pertinence de votre futur chantier.
    </p>
  </div>

  <!-- Filtres -->
  <div class="flex items-center gap-4 mb-8 overflow-x-auto pb-2">
    <span class="text-xs font-label font-bold uppercase tracking-widest text-outline flex-shrink-0">Filtrer par:</span>
    <a href="?" class="<?= !isset($_GET['f']) ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest' ?> px-6 py-2 rounded-full text-xs font-label font-bold uppercase tracking-widest transition-colors whitespace-nowrap">
      Tous (<?= count($leads ?? []) ?>)
    </a>
    <?php
    $urgent  = array_filter($leads ?? [], fn($l) => ($l['urgency'] ?? '') === 'urgent');
    $pending = array_filter($leads ?? [], fn($l) => $l['status'] === 'pending');
    ?>
    <a href="?f=urgent" class="<?= ($_GET['f'] ?? '') == 'urgent' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest' ?> px-6 py-2 rounded-full text-xs font-label font-bold uppercase tracking-widest transition-colors whitespace-nowrap">
      Urgent (<?= count($urgent) ?>)
    </a>
    <a href="?f=pending" class="<?= ($_GET['f'] ?? '') == 'pending' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest' ?> px-6 py-2 rounded-full text-xs font-label font-bold uppercase tracking-widest transition-colors whitespace-nowrap">
      En attente (<?= count($pending) ?>)
    </a>
  </div>

  <?php if (empty($leads)): ?>
    <div class="text-center py-24">
      <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">format_list_bulleted</span>
      <h2 class="font-headline text-3xl mb-4">Aucun lead pour le moment</h2>
      <p class="text-on-surface-variant mb-8">Les leads apparaissent selon votre zone et vos catégories</p>
      <a href="<?= APP_URL ?>/dashboard/artisan/profile" class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 inline-block">
        Compléter mon profil
      </a>
    </div>
  <?php else: ?>

    <!-- Grille bento leads -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

      <?php
      $filteredLeads = $leads;
      if (($_GET['f'] ?? '') === 'urgent') $filteredLeads = $urgent;
      elseif (($_GET['f'] ?? '') === 'pending') $filteredLeads = $pending;

      $first = true;
      foreach ($filteredLeads as $lead):
        $isUrgent = ($lead['urgency'] ?? '') === 'urgent';
        $isPending = $lead['status'] === 'pending';
      ?>

        <?php if ($first): // Grande carte pour le premier lead 
        ?>
          <div class="lg:col-span-8 bg-surface-container-lowest p-8 rounded-xl relative overflow-hidden group shadow-sm border border-outline-variant/10" id="lead-<?= $lead['id'] ?>">
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                  <span class="font-mono text-[10px] tracking-widest text-outline uppercase"><?= Security::e($lead['reference'] ?? '') ?></span>
                  <?php if ($isUrgent): ?>
                    <span class="bg-error/10 text-error px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tighter flex items-center gap-1">
                      <span class="material-symbols-outlined text-[12px]" style="font-variation-settings:'FILL' 1">warning</span> Urgent
                    </span>
                  <?php endif; ?>
                </div>
                <h3 class="text-3xl font-headline font-medium text-on-surface mb-2"><?= Security::e($lead['title'] ?? '') ?></h3>
                <p class="text-on-surface-variant font-body line-clamp-2 max-w-xl"><?= Security::e(substr($lead['description'] ?? '', 0, 200)) ?></p>
              </div>
              <div class="flex flex-col items-end">
                <span class="text-xs font-label font-bold uppercase tracking-widest text-outline mb-1">Localisation</span>
                <span class="text-xl font-headline italic font-bold text-primary"><?= Security::e($lead['ville'] ?? '') ?></span>
              </div>
            </div>
            <?php if ($isPending): ?>
              <div class="flex flex-wrap items-center gap-4 mt-auto">
                <button onclick="respondLead(<?= $lead['id'] ?>, 'accepted')"
                  class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-sm hover:opacity-90 transition-opacity flex items-center gap-2">
                  Accepter le lead <span class="material-symbols-outlined">arrow_forward</span>
                </button>
                <button onclick="respondLead(<?= $lead['id'] ?>, 'refused')"
                  class="border border-outline-variant/30 text-outline px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-sm hover:bg-surface-container transition-colors">
                  Refuser
                </button>
                <div class="ml-auto hidden sm:flex items-center gap-4 text-outline-variant text-sm">
                  <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">location_on</span> <?= Security::e($lead['ville'] ?? '') ?></div>
                  <?php if (!empty($lead['notified_at'])): ?>
                    <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">calendar_today</span> <?= date('d/m', strtotime($lead['notified_at'])) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php else: ?>
              <div class="flex items-center gap-3">
                <span class="<?= $lead['status'] === 'accepted' ? 'text-primary' : 'text-outline-variant' ?> font-label text-xs uppercase tracking-widest font-bold">
                  <?= ucfirst($lead['status']) ?>
                </span>
              </div>
            <?php endif; ?>
          </div>
          <?php $first = false; ?>

        <?php else: // Petites cartes pour les suivants 
        ?>
          <div class="lg:col-span-4 bg-surface-container-low p-6 rounded-xl border border-outline-variant/10 flex flex-col" id="lead-<?= $lead['id'] ?>">
            <div class="flex items-center justify-between mb-4">
              <span class="font-mono text-[10px] tracking-widest text-outline uppercase"><?= Security::e($lead['reference'] ?? '') ?></span>
              <?php if ($isUrgent): ?>
                <span class="bg-error/10 text-error px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tighter">Urgent</span>
              <?php else: ?>
                <span class="text-[10px] font-label font-bold uppercase tracking-widest text-on-surface-variant/60">Standard</span>
              <?php endif; ?>
            </div>
            <h3 class="text-xl font-headline font-medium text-on-surface mb-3"><?= Security::e($lead['title'] ?? '') ?></h3>
            <p class="text-on-surface-variant text-sm font-body line-clamp-2 mb-6"><?= Security::e(substr($lead['description'] ?? '', 0, 100)) ?></p>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-4">
              <span class="material-symbols-outlined text-sm">location_on</span>
              <?= Security::e($lead['ville'] ?? '') ?>
            </div>
            <?php if ($isPending): ?>
              <div class="mt-auto space-y-3">
                <button onclick="respondLead(<?= $lead['id'] ?>, 'accepted')"
                  class="w-full bg-primary text-on-primary py-3 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-opacity">
                  Accepter
                </button>
                <button onclick="respondLead(<?= $lead['id'] ?>, 'refused')"
                  class="w-full border border-outline-variant/30 text-outline py-3 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:bg-surface-container transition-colors">
                  Refuser
                </button>
              </div>
            <?php else: ?>
              <span class="mt-auto text-xs font-label uppercase tracking-widest font-bold <?= $lead['status'] === 'accepted' ? 'text-primary' : 'text-outline-variant' ?>">
                <?= ucfirst($lead['status']) ?>
              </span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php endforeach; ?>
    </div>

    <!-- Stats performance -->
    <section class="mt-20 border-t border-outline-variant/20 pt-16">
      <div class="bg-surface-container-high rounded-2xl p-12 flex flex-col md:flex-row items-center gap-12">
        <div class="flex-1">
          <span class="material-symbols-outlined text-4xl text-primary mb-4 block" style="font-variation-settings:'FILL' 1">analytics</span>
          <h2 class="text-3xl font-headline italic text-on-surface mb-4">Performance ce mois-ci</h2>
          <p class="text-on-surface-variant font-body leading-relaxed max-w-md">
            Restez réactif sur les leads urgents pour améliorer votre taux de conversion.
          </p>
        </div>
        <div class="grid grid-cols-2 gap-8 md:border-l border-outline-variant/30 md:pl-12">
          <div>
            <span class="block text-4xl font-headline font-bold text-primary">
              <?= $stats['total_leads'] > 0 ? round($stats['accepted_leads'] / $stats['total_leads'] * 100) : 0 ?>%
            </span>
            <span class="text-[10px] font-label font-bold uppercase tracking-widest text-outline">Taux d'acceptation</span>
          </div>
          <div>
            <span class="block text-4xl font-headline font-bold text-primary"><?= $stats['total_leads'] ?></span>
            <span class="text-[10px] font-label font-bold uppercase tracking-widest text-outline">Leads total</span>
          </div>
        </div>
      </div>
    </section>

  <?php endif; ?>
</main>

<script>
  async function respondLead(leadId, status) {
    if (!confirm(status === 'accepted' ? 'Accepter ce lead ?' : 'Refuser ce lead ?')) return;
    const form = new FormData();
    form.append('lead_id', leadId);
    form.append('status', status);
    form.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    const res = await fetch('<?= APP_URL ?>/dashboard/artisan/lead/respond', {
      method: 'POST',
      body: form
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.error || 'Erreur');
  }
</script>