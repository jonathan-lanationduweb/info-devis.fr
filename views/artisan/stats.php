<?php /* views/artisan/stats.php */ ?>
<?php include __DIR__ . '/_sidebar.php'; ?>

<main class="md:ml-72 pt-24 pb-16 min-h-screen bg-surface-container-low">
  <div class="max-w-5xl mx-auto px-6">

    <!-- Header avec bouton home -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="font-headline text-4xl text-on-surface mb-2">Mes statistiques</h1>
        <p class="text-on-surface-variant text-sm">Suivez vos performances sur InfoDevis</p>
      </div>
      <a href="<?= APP_URL ?>/dashboard/artisan"
        class="flex items-center gap-2 bg-white border border-outline-variant/20 text-on-surface px-5 py-2.5 rounded-xl font-label text-xs font-bold uppercase tracking-widest hover:border-primary hover:text-primary transition-all shadow-sm">
        <span class="material-symbols-outlined text-[18px]">home</span>
        Dashboard
      </a>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
      <?php
      $kpis = [
        ['Total leads',       $stats['total_leads'] ?? 0,                        '📋', 'text-on-surface'],
        ['Taux acceptation',  ($stats['total_leads'] > 0 ? round(($stats['accepted_leads'] ?? 0) / ($stats['total_leads']) * 100) : 0) . '%', '✅', 'text-primary'],
        ['Note moyenne',      number_format((float)($stats['avg_rating'] ?? 0), 1) . '/5', '⭐', 'text-yellow-600'],
        ['Avis reçus',        $stats['total_avis'] ?? 0,                         '💬', 'text-primary'],
      ];
      foreach ($kpis as [$label, $val, $ico, $color]):
      ?>
        <div class="bg-white rounded-2xl border border-outline-variant/20 p-6 text-center shadow-sm">
          <div class="text-2xl mb-2"><?= $ico ?></div>
          <p class="text-3xl font-headline font-bold <?= $color ?>"><?= $val ?></p>
          <p class="text-xs font-label uppercase tracking-widest text-on-surface-variant mt-1"><?= $label ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Graphique mensuel -->
    <div class="bg-white rounded-2xl border border-outline-variant/20 p-8 mb-8 shadow-sm">
      <h2 class="font-headline text-2xl mb-6">Évolution mensuelle des leads</h2>
      <?php if (empty($monthly)): ?>
        <div class="text-center py-12 text-on-surface-variant">
          <span class="material-symbols-outlined text-5xl mb-3 block">bar_chart</span>
          <p>Pas encore de données mensuelles.</p>
        </div>
      <?php else:
        $maxLeads = max(array_column($monthly, 'leads')) ?: 1;
      ?>
        <div class="flex items-end justify-between gap-2 h-48 mb-4">
          <?php foreach ($monthly as $m):
            $h = max(5, round(($m['leads'] / $maxLeads) * 100));
            $isMax = $m['leads'] == max(array_column($monthly, 'leads'));
          ?>
            <div class="flex-1 flex flex-col items-center gap-1">
              <span class="text-xs font-bold text-on-surface-variant"><?= $m['leads'] ?></span>
              <div class="w-full <?= $isMax ? 'bg-primary' : 'bg-primary/30' ?> rounded-t-lg hover:bg-primary transition-colors" style="height:<?= $h ?>%"></div>
              <span class="text-[10px] text-on-surface-variant font-label"><?= substr($m['month'], 5, 2) ?>/<?= substr($m['month'], 2, 2) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Tableau mensuel -->
    <?php if (!empty($monthly)): ?>
      <div class="bg-white rounded-2xl border border-outline-variant/20 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-outline-variant/10">
          <h2 class="font-headline text-xl">Détail par mois</h2>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-surface-container-low border-b border-outline-variant/10">
            <tr>
              <th class="text-left px-5 py-3 font-label text-xs uppercase tracking-widest text-on-surface-variant">Mois</th>
              <th class="text-left px-5 py-3 font-label text-xs uppercase tracking-widest text-on-surface-variant">Leads reçus</th>
              <th class="text-left px-5 py-3 font-label text-xs uppercase tracking-widest text-on-surface-variant">Acceptés</th>
              <th class="text-left px-5 py-3 font-label text-xs uppercase tracking-widest text-on-surface-variant">Taux</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <?php foreach ($monthly as $m):
              $taux = ($m['leads'] ?? 0) > 0 ? round(($m['accepted'] ?? 0) / ($m['leads']) * 100) : 0;
            ?>
              <tr class="hover:bg-surface-container-low transition-colors">
                <td class="px-5 py-3 font-semibold text-on-surface"><?= Security::e($m['month']) ?></td>
                <td class="px-5 py-3 text-on-surface-variant"><?= $m['leads'] ?></td>
                <td class="px-5 py-3 text-primary font-bold"><?= $m['accepted'] ?? 0 ?></td>
                <td class="px-5 py-3">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 bg-surface-container-high rounded-full h-1.5">
                      <div class="bg-primary h-1.5 rounded-full" style="width:<?= $taux ?>%"></div>
                    </div>
                    <span class="text-xs font-semibold text-on-surface-variant w-8"><?= $taux ?>%</span>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</main>