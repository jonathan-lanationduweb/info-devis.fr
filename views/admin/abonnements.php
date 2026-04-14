<?php /* views/admin/abonnements.php */ ?>
<?php include BASE_PATH . '/views/admin/_sidebar.php'; ?>

<main class="lg:ml-72 min-h-screen">
  <header class="fixed top-0 right-0 left-0 lg:left-72 z-40 h-20 bg-[#faf9f8]/80 backdrop-blur-xl flex items-center justify-between px-12 shadow-sm shadow-primary/5">
    <div class="flex items-center gap-2">
      <a href="<?= APP_URL ?>/admin" class="font-headline italic text-primary/60 hover:text-primary text-sm transition-colors">Admin</a>
      <span class="material-symbols-outlined text-xs text-outline-variant">chevron_right</span>
      <span class="font-headline italic text-on-surface">Abonnements</span>
    </div>
  </header>

  <div class="pt-32 px-12 pb-20 max-w-7xl mx-auto">
    <div class="mb-10">
      <h1 class="font-headline text-5xl font-light tracking-tighter text-on-background mb-2">Abonnements</h1>
      <p class="text-on-surface-variant text-sm">Plans actifs sur la plateforme</p>
    </div>

    <?php
    $planCfg = [
      'starter'  => ['bg-blue-100 text-blue-800',   'Starter'],
      'pro'      => ['bg-purple-100 text-purple-800','Pro'],
      'illimite' => ['bg-primary/10 text-primary',  'Illimité'],
      'gratuit'  => ['bg-stone-100 text-stone-600',  'Gratuit'],
    ];
    $statusCfg = [
      'active'    => ['bg-green-100 text-green-700', 'Actif'],
      'cancelled' => ['bg-red-100 text-red-700',     'Annulé'],
      'expired'   => ['bg-stone-100 text-stone-500', 'Expiré'],
    ];
    ?>

    <?php if (empty($abonnements)): ?>
    <div class="bg-white rounded-2xl border border-outline-variant/20 text-center py-24">
      <span class="material-symbols-outlined text-5xl text-outline-variant mb-4 block">card_membership</span>
      <p class="font-headline italic text-xl text-on-surface-variant">Aucun abonnement pour le moment</p>
    </div>
    <?php else: ?>

    <!-- Stats rapides -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
      <?php
      $totals = ['starter'=>0,'pro'=>0,'illimite'=>0,'total'=>count($abonnements)];
      foreach ($abonnements as $ab) { $totals[$ab['plan'] ?? 'starter'] = ($totals[$ab['plan'] ?? 'starter'] ?? 0) + 1; }
      ?>
      <div class="bg-white rounded-xl border border-outline-variant/20 p-5">
        <p class="text-xs font-label uppercase tracking-widest text-on-surface-variant mb-2">Total</p>
        <p class="text-3xl font-headline font-bold text-primary"><?= $totals['total'] ?></p>
      </div>
      <div class="bg-white rounded-xl border border-outline-variant/20 p-5">
        <p class="text-xs font-label uppercase tracking-widest text-on-surface-variant mb-2">Starter</p>
        <p class="text-3xl font-headline font-bold text-blue-600"><?= $totals['starter'] ?? 0 ?></p>
      </div>
      <div class="bg-white rounded-xl border border-outline-variant/20 p-5">
        <p class="text-xs font-label uppercase tracking-widest text-on-surface-variant mb-2">Pro</p>
        <p class="text-3xl font-headline font-bold text-purple-600"><?= $totals['pro'] ?? 0 ?></p>
      </div>
      <div class="bg-white rounded-xl border border-outline-variant/20 p-5">
        <p class="text-xs font-label uppercase tracking-widest text-on-surface-variant mb-2">Illimité</p>
        <p class="text-3xl font-headline font-bold text-primary"><?= $totals['illimite'] ?? 0 ?></p>
      </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low/50">
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70">Artisan</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70">Entreprise</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70">Plan</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70">Statut</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70">Début</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70">Fin</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70">Montant</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/5">
          <?php foreach ($abonnements as $ab):
            [$planCls, $planLabel] = $planCfg[$ab['plan'] ?? 'starter'] ?? ['bg-stone-100 text-stone-600', ucfirst($ab['plan'] ?? '?')];
            [$sCls, $sLabel] = $statusCfg[$ab['status'] ?? 'active'] ?? ['bg-stone-100 text-stone-500', ucfirst($ab['status'] ?? '?')];
          ?>
          <tr class="hover:bg-surface-container-low transition-colors">
            <td class="px-8 py-5">
              <div>
                <p class="font-semibold text-sm"><?= Security::e(($ab['first_name']??'').' '.($ab['last_name']??'')) ?></p>
                <p class="text-xs text-on-surface-variant"><?= Security::e($ab['email'] ?? '') ?></p>
              </div>
            </td>
            <td class="px-8 py-5 text-sm font-headline italic"><?= Security::e($ab['company_name'] ?? '—') ?></td>
            <td class="px-8 py-5">
              <span class="text-[10px] font-bold px-3 py-1 rounded-full <?= $planCls ?> uppercase tracking-wider"><?= $planLabel ?></span>
            </td>
            <td class="px-8 py-5">
              <span class="text-[10px] font-bold px-3 py-1 rounded-full <?= $sCls ?> uppercase tracking-wider"><?= $sLabel ?></span>
            </td>
            <td class="px-8 py-5 text-sm text-on-surface-variant/70">
              <?= !empty($ab['started_at']) ? date('d/m/Y', strtotime($ab['started_at'])) : '—' ?>
            </td>
            <td class="px-8 py-5 text-sm text-on-surface-variant/70">
              <?= !empty($ab['expires_at']) ? date('d/m/Y', strtotime($ab['expires_at'])) : '—' ?>
            </td>
            <td class="px-8 py-5 text-sm font-bold text-primary">
              <?= isset($ab['amount']) ? number_format($ab['amount']/100, 2, ',', ' ').' €' : '—' ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- Citation -->
    <div class="mt-20 relative bg-surface-container-highest/40 p-12 rounded-2xl overflow-hidden">
      <span class="absolute -top-10 -left-5 text-[180px] font-headline italic opacity-[0.03] select-none text-primary">"</span>
      <p class="font-headline italic text-xl text-on-surface relative z-10 max-w-2xl">
        "Les abonnements pro sont le socle de la confiance entre InfoDevis et ses artisans partenaires."
      </p>
      <p class="mt-4 font-label text-xs uppercase tracking-widest text-primary font-bold">— Note de la Rédaction</p>
    </div>

  </div>
</main>
