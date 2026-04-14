<?php /* views/admin/paiements.php */ ?>
<?php include BASE_PATH . '/views/admin/_sidebar.php'; ?>

<main class="md:ml-72 min-h-screen pt-4 pb-20 px-6 lg:px-12">

  <!-- Breadcrumb -->
  <nav class="mb-8 mt-24 flex items-center gap-2">
    <a href="<?= APP_URL ?>/admin" class="text-[11px] font-label uppercase tracking-[0.15em] text-stone-400 hover:text-primary transition-colors">Admin</a>
    <span class="material-symbols-outlined text-stone-300 text-sm">chevron_right</span>
    <span class="text-[11px] font-label uppercase tracking-[0.15em] text-primary font-bold">Paiements</span>
  </nav>

  <!-- Header -->
  <header class="mb-12">
    <h1 class="font-headline text-5xl md:text-6xl text-on-background tracking-tight">Paiements</h1>
    <p class="mt-4 font-body text-on-surface-variant max-w-2xl leading-relaxed">
      Suivi et gestion de l'ensemble des flux financiers de la plateforme. Visualisez les transactions en temps réel.
    </p>
  </header>

  <!-- Stats Bento -->
  <?php
  $totalEnc   = Database::fetch("SELECT COALESCE(SUM(amount),0) as s FROM paiements WHERE status='paid'")['s'] ?? 0;
  $totalTrans = Database::fetch("SELECT COUNT(*) as c FROM paiements")['c'] ?? 0;
  $totalPaids = Database::fetch("SELECT COUNT(*) as c FROM paiements WHERE status='paid'")['c'] ?? 0;
  ?>
  <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
    <div class="bg-surface-container-lowest p-8 rounded-xl flex flex-col justify-between min-h-[160px] group hover:bg-emerald-50 transition-colors duration-500">
      <span class="font-label uppercase tracking-widest text-[11px] font-semibold text-stone-500 mb-4 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-primary"></span> Total encaissé
      </span>
      <div class="flex items-baseline gap-2">
        <span class="font-headline text-5xl font-bold text-primary italic"><?= number_format($totalEnc / 100, 0, ',', ' ') ?></span>
        <span class="font-headline text-3xl text-primary italic">€</span>
      </div>
    </div>
    <div class="bg-surface-container-lowest p-8 rounded-xl flex flex-col justify-between min-h-[160px] group hover:bg-stone-100 transition-colors duration-500">
      <span class="font-label uppercase tracking-widest text-[11px] font-semibold text-stone-500 mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">receipt_long</span> Transactions
      </span>
      <span class="font-headline text-5xl font-bold text-on-background"><?= $totalTrans ?></span>
    </div>
    <div class="bg-surface-container-lowest p-8 rounded-xl flex flex-col justify-between min-h-[160px] group hover:bg-stone-100 transition-colors duration-500">
      <span class="font-label uppercase tracking-widest text-[11px] font-semibold text-stone-500 mb-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">verified</span> Payés
      </span>
      <span class="font-headline text-5xl font-bold text-on-background"><?= $totalPaids ?></span>
    </div>
  </section>

  <!-- Tableau transactions -->
  <section class="relative">
    <div class="mb-6 flex justify-between items-end">
      <h3 class="font-headline text-2xl italic text-on-background">Historique des transactions</h3>
      <div class="flex gap-4">
        <button class="px-4 py-2 bg-surface-container text-on-surface font-label text-[11px] font-bold uppercase tracking-widest rounded-lg flex items-center gap-2 hover:bg-surface-container-high transition-colors">
          <span class="material-symbols-outlined text-sm">filter_list</span> Filtrer
        </button>
      </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl overflow-hidden min-h-[400px] flex flex-col">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low">
            <th class="px-8 py-6 font-label uppercase tracking-widest text-[10px] font-bold text-stone-500">Utilisateur</th>
            <th class="px-8 py-6 font-label uppercase tracking-widest text-[10px] font-bold text-stone-500">Montant</th>
            <th class="px-8 py-6 font-label uppercase tracking-widest text-[10px] font-bold text-stone-500">Statut</th>
            <th class="px-8 py-6 font-label uppercase tracking-widest text-[10px] font-bold text-stone-500">Type</th>
            <th class="px-8 py-6 font-label uppercase tracking-widest text-[10px] font-bold text-stone-500">Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($paiements)): ?>
            <tr>
              <td class="py-32 text-center" colspan="5">
                <div class="flex flex-col items-center justify-center space-y-4">
                  <div class="w-20 h-20 bg-surface-container-low rounded-full flex items-center justify-center mb-2">
                    <span class="material-symbols-outlined text-4xl text-stone-300">payments</span>
                  </div>
                  <p class="font-headline italic text-2xl text-stone-400">Aucun paiement</p>
                  <p class="font-body text-sm text-stone-400 max-w-xs mx-auto">
                    Les transactions validées apparaîtront ici dès que les premières commandes seront finalisées.
                  </p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php
            $statusCfg = [
              'paid'    => ['bg-green-100 text-green-700', 'Payé'],
              'pending' => ['bg-yellow-100 text-yellow-700', 'En attente'],
              'failed'  => ['bg-red-100 text-red-700',     'Échoué'],
              'refunded' => ['bg-stone-100 text-stone-500', 'Remboursé'],
            ];
            foreach ($paiements as $p):
              [$scls, $slabel] = $statusCfg[$p['status'] ?? ''] ?? ['bg-stone-100 text-stone-500', ucfirst($p['status'] ?? '?')];
            ?>
              <tr class="hover:bg-surface-container-low transition-colors border-b border-outline-variant/5">
                <td class="px-8 py-5">
                  <div>
                    <p class="font-semibold text-sm"><?= Security::e(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?></p>
                    <p class="text-xs text-on-surface-variant"><?= Security::e($p['email'] ?? '') ?></p>
                  </div>
                </td>
                <td class="px-8 py-5">
                  <span class="font-headline text-lg font-bold text-primary italic">
                    <?= isset($p['amount']) ? number_format($p['amount'] / 100, 2, ',', ' ') . ' €' : '—' ?>
                  </span>
                </td>
                <td class="px-8 py-5">
                  <span class="text-[10px] font-bold px-3 py-1 rounded-full <?= $scls ?> uppercase tracking-wider"><?= $slabel ?></span>
                </td>
                <td class="px-8 py-5 text-sm text-on-surface-variant italic font-headline">
                  <?= Security::e($p['type'] ?? $p['plan'] ?? '—') ?>
                </td>
                <td class="px-8 py-5 text-sm text-stone-400">
                  <?= !empty($p['created_at']) ? date('d/m/Y H:i', strtotime($p['created_at'])) : '—' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Footer éditorial -->
  <footer class="mt-20 pt-10 border-t border-outline-variant/10 flex flex-col md:flex-row justify-between items-start gap-8">
    <div class="max-w-md">
      <h4 class="font-headline text-lg italic mb-2">Note de l'éditeur</h4>
      <p class="text-xs font-body text-stone-500 leading-relaxed uppercase tracking-wider">
        Toutes les transactions sont traitées via des protocoles de sécurité bancaire de niveau institutionnel. Les délais de versement standard sont de 3 à 5 jours ouvrés.
      </p>
    </div>
    <div class="flex gap-8">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-2">Support Technique</p>
        <a class="text-sm font-headline italic hover:text-primary transition-colors" href="mailto:support@info-devis.fr">support@info-devis.fr</a>
      </div>
    </div>
  </footer>

</main>

<!-- Citation flottante décorative -->
<div class="fixed bottom-12 right-12 hidden lg:block max-w-[280px]">
  <div class="bg-surface-container-highest p-6 rounded-xl relative overflow-hidden group hover:bg-primary transition-all duration-700">
    <span class="absolute -top-4 -left-2 text-8xl font-headline opacity-10 select-none group-hover:text-white transition-colors">"</span>
    <p class="relative z-10 font-headline italic text-sm text-on-tertiary-container group-hover:text-on-primary transition-colors leading-relaxed">
      La transparence financière est le socle de la confiance entre le maître d'ouvrage et l'artisan d'exception.
    </p>
    <p class="mt-4 font-label uppercase tracking-widest text-[9px] font-bold text-primary group-hover:text-on-primary opacity-60">Manifeste de Qualité</p>
  </div>
</div>