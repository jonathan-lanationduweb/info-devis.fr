<?php /* views/admin/metrics.php */ ?>
<?php
$moisFr = [
  '01' => 'Janvier',
  '02' => 'Février',
  '03' => 'Mars',
  '04' => 'Avril',
  '05' => 'Mai',
  '06' => 'Juin',
  '07' => 'Juillet',
  '08' => 'Août',
  '09' => 'Septembre',
  '10' => 'Octobre',
  '11' => 'Novembre',
  '12' => 'Décembre'
];
?>
<?php include BASE_PATH . '/views/admin/_sidebar.php'; ?>

<main class="ml-72 min-h-screen">

  <!-- TopAppBar -->
  <header class="fixed top-0 left-72 right-0 z-40 h-20 bg-[#faf9f8]/80 backdrop-blur-xl flex justify-between items-center px-12 shadow-sm shadow-primary/5">
    <h2 class="font-headline italic tracking-tight text-2xl text-primary">Métriques business</h2>
    <div class="flex items-center space-x-8">
      <div class="flex items-center bg-surface-container-low px-4 py-2 rounded-full border border-outline-variant/10">
        <span class="material-symbols-outlined text-outline mr-2">search</span>
        <input class="bg-transparent border-none focus:ring-0 text-sm font-label placeholder:text-outline/50 w-48" placeholder="Rechercher..." type="text" />
      </div>
      <div class="flex items-center space-x-4 text-on-surface/60">
        <button class="hover:text-primary transition-colors"><span class="material-symbols-outlined">notifications</span></button>
        <button class="hover:text-primary transition-colors"><span class="material-symbols-outlined">settings</span></button>
      </div>
    </div>
  </header>

  <section class="pt-32 px-12 pb-20">

    <!-- KPIs Bento -->
    <div class="grid grid-cols-12 gap-6 mb-12">

      <!-- Revenus -->
      <div class="col-span-12 lg:col-span-5 bg-surface-container-lowest p-10 rounded-xl shadow-[0_40px_80px_-20px_rgba(14,108,72,0.08)] border border-primary/5 flex flex-col justify-between">
        <div>
          <span class="font-label uppercase tracking-widest text-[10px] text-outline mb-4 block">Revenus Totaux (Mensuels)</span>
          <h3 class="text-6xl font-headline italic text-primary"><?= number_format((float)($metrics['revenue_month'] ?? 0), 0, ',', ' ') ?> €</h3>
          <div class="mt-4 flex items-center text-primary">
            <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
            <span class="text-xs font-bold font-label">CA du mois en cours</span>
          </div>
        </div>
        <!-- Graphique barres CA 7 derniers mois -->
        <div class="mt-12 h-24 flex items-end space-x-1">
          <?php
          $mRevs = [];
          for ($m = 6; $m >= 0; $m--) {
            $mDate = date('Y-m', strtotime("-$m months"));
            $rev = (float)(Database::fetch("SELECT COALESCE(SUM(amount),0) as s FROM paiements WHERE status='paid' AND DATE_FORMAT(created_at,'%Y-%m')=?", [$mDate])['s'] ?? 0);
            $mRevs[] = $rev;
          }
          $maxR = max($mRevs) > 0 ? max($mRevs) : 1;
          foreach ($mRevs as $i => $rev):
            $h = max(10, round(($rev / $maxR) * 100));
            $isLast = $i === 6;
          ?>
            <div class="flex-1 <?= $isLast ? 'bg-primary' : 'bg-primary/10 hover:bg-primary' ?> transition-all rounded-t-sm" style="height:<?= $h ?>%"></div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Leads qualifiés -->
      <div class="col-span-12 lg:col-span-4 bg-surface-container-low p-10 rounded-xl flex flex-col justify-between">
        <div>
          <span class="font-label uppercase tracking-widest text-[10px] text-outline mb-4 block">Total Devis</span>
          <h3 class="text-5xl font-headline text-on-surface"><?= number_format((int)($metrics['total_devis'] ?? 0)) ?></h3>
          <p class="text-xs text-outline mt-2 font-label">Demandes transmises aux artisans partenaires</p>
        </div>
        <div class="mt-8 space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Artisans vérifiés</span>
            <span class="font-bold text-primary"><?= $metrics['total_artisans'] ?? 0 ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Clients inscrits</span>
            <span class="font-bold"><?= $metrics['total_clients'] ?? 0 ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Abonnements actifs</span>
            <span class="font-bold text-primary"><?= $metrics['active_subs'] ?? 0 ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Blog en attente</span>
            <span class="font-bold text-orange-600"><?= $metrics['pending_blog'] ?? 0 ?></span>
          </div>
        </div>
      </div>

      <!-- Taux conversion cercle SVG -->
      <?php
      $totalD = (int)($metrics['total_devis'] ?? 0);
      $totalL = (int)(Database::fetch('SELECT COUNT(*) as c FROM leads')['c'] ?? 0);
      $txConv = $totalD > 0 ? min(99, round($totalL / max($totalD, 1) * 100)) : 0;
      $circumference = 2 * M_PI * 58; // r=58
      $dashOffset = $circumference - ($txConv / 100) * $circumference;
      ?>
      <div class="col-span-12 lg:col-span-3 bg-primary p-10 rounded-xl text-on-primary flex flex-col items-center justify-center relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-container/10 rounded-full blur-3xl"></div>
        <span class="font-label uppercase tracking-widest text-[10px] text-on-primary/60 mb-6 block text-center">Taux de Conversion</span>
        <div class="relative w-32 h-32 flex items-center justify-center">
          <svg class="w-full h-full transform -rotate-90" viewBox="0 0 128 128">
            <circle class="text-white/10" cx="64" cy="64" fill="transparent" r="58" stroke="currentColor" stroke-width="4"></circle>
            <circle class="text-on-primary" cx="64" cy="64" fill="transparent" r="58" stroke="currentColor"
              stroke-dasharray="<?= round($circumference) ?>"
              stroke-dashoffset="<?= round($dashOffset) ?>"
              stroke-width="4" stroke-linecap="round"></circle>
          </svg>
          <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-3xl font-headline font-bold"><?= $txConv ?>%</span>
            <span class="text-[8px] uppercase tracking-tighter opacity-70">Leads/Devis</span>
          </div>
        </div>
        <p class="text-[10px] text-center mt-6 text-on-primary/70 font-label leading-relaxed">Ratio leads générés par devis</p>
      </div>
    </div>

    <!-- Graphique performance mensuelle -->
    <div class="mb-12 bg-surface-container-lowest p-12 rounded-xl shadow-[0_40px_80px_-20px_rgba(14,108,72,0.08)]">
      <div class="flex justify-between items-end mb-16">
        <div>
          <h4 class="font-headline italic text-3xl text-on-background mb-2">Performance mensuelle</h4>
          <p class="text-sm font-label text-outline max-w-md">Analyse de l'acquisition utilisateur et des devis sur les 6 derniers mois.</p>
        </div>
        <div class="flex space-x-4">
          <a href="<?= APP_URL ?>/admin" class="px-4 py-2 text-[10px] font-label font-bold uppercase tracking-widest bg-surface-container border border-outline-variant/20 rounded hover:bg-surface-container-high transition-colors">← Retour</a>
        </div>
      </div>

      <!-- Graphique SVG ligne -->
      <?php
      $months6   = [];
      $maxVal    = 1;
      for ($m = 5; $m >= 0; $m--) {
        $mDate = date('Y-m', strtotime("-$m months"));
        [$y, $mo] = explode('-', $mDate);
        $leads  = (int)(Database::fetch('SELECT COUNT(*) as c FROM leads WHERE DATE_FORMAT(created_at,"%Y-%m")=?', [$mDate])['c'] ?? 0);
        $devisM = (int)(Database::fetch('SELECT COUNT(*) as c FROM devis WHERE DATE_FORMAT(created_at,"%Y-%m")=?', [$mDate])['c'] ?? 0);
        $months6[] = ['label' => ($moisFr[$mo] ?? $mo), 'leads' => $leads, 'devis' => $devisM];
        $maxVal = max($maxVal, $leads, $devisM);
      }
      // Générer les points SVG pour le graphique ligne
      $svgW = 1000;
      $svgH = 100;
      $pointsLeads = [];
      $pointsDevis = [];
      foreach ($months6 as $i => $m) {
        $x = round($i * ($svgW / 5));
        $yL = round($svgH - ($m['leads'] / $maxVal) * $svgH * 0.9 - 5);
        $yD = round($svgH - ($m['devis'] / $maxVal) * $svgH * 0.9 - 5);
        $pointsLeads[] = "$x,$yL";
        $pointsDevis[] = "$x,$yD";
      }
      $pathLeads = 'M' . implode(' L', $pointsLeads);
      $pathDevis = 'M' . implode(' L', $pointsDevis);
      // Zone remplie leads
      $areaLeads = $pathLeads . " L{$svgW},{$svgH} L0,{$svgH} Z";
      ?>
      <div class="relative h-80 w-full">
        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
          <?php for ($i = 0; $i < 5; $i++): ?>
            <div class="w-full border-t border-outline-variant/10"></div>
          <?php endfor; ?>
        </div>
        <svg class="absolute inset-0 w-full h-full" preserveAspectRatio="none" viewBox="0 0 <?= $svgW ?> <?= $svgH ?>">
          <defs>
            <linearGradient id="areaGrad" x1="0" x2="0" y1="0" y2="1">
              <stop offset="0%" stop-color="#0e6c48" stop-opacity="0.12" />
              <stop offset="100%" stop-color="#0e6c48" stop-opacity="0" />
            </linearGradient>
          </defs>
          <path d="<?= $areaLeads ?>" fill="url(#areaGrad)" />
          <path d="<?= $pathLeads ?>" fill="none" stroke="#0e6c48" stroke-width="2.5" />
          <path d="<?= $pathDevis ?>" fill="none" stroke="#0e6c48" stroke-width="1.5" stroke-dasharray="8,4" opacity="0.5" />
          <?php foreach ($months6 as $i => $m):
            $x = round($i * ($svgW / 5));
            $yL = round($svgH - ($m['leads'] / $maxVal) * $svgH * 0.9 - 5);
          ?>
            <circle cx="<?= $x ?>" cy="<?= $yL ?>" r="5" fill="#0e6c48" />
          <?php endforeach; ?>
        </svg>
      </div>

      <!-- Labels X -->
      <div class="flex justify-between mt-8 text-outline font-label text-[10px] uppercase tracking-widest px-1">
        <?php foreach ($months6 as $m): ?>
          <span><?= $m['label'] ?></span>
        <?php endforeach; ?>
      </div>

      <!-- Légende -->
      <div class="flex items-center gap-8 mt-4 text-xs text-on-surface-variant">
        <div class="flex items-center gap-2">
          <div class="w-4 h-0.5 bg-primary"></div><span>Leads</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-4 h-0.5 bg-primary/50" style="border-top:1px dashed"></div><span>Devis</span>
        </div>
      </div>
    </div>

    <!-- Tableau détaillé indicateurs -->
    <div class="bg-surface-container-low rounded-xl overflow-hidden">
      <div class="px-10 py-8 border-b border-outline-variant/10 bg-surface-container-low/50">
        <h5 class="font-headline text-xl italic text-primary">Tableau détaillé des indicateurs</h5>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="font-label uppercase tracking-widest text-[10px] text-outline border-b border-outline-variant/10">
              <th class="px-10 py-5 font-bold">Mois</th>
              <th class="px-10 py-5 font-bold">Devis</th>
              <th class="px-10 py-5 font-bold">Leads</th>
              <th class="px-10 py-5 font-bold">Tx. Conversion</th>
              <th class="px-10 py-5 font-bold text-right">Revenus</th>
            </tr>
          </thead>
          <tbody class="text-sm font-label text-on-surface">
            <?php
            for ($m = 5; $m >= 0; $m--):
              $mDate = date('Y-m', strtotime("-$m months"));
              [$y, $mo] = explode('-', $mDate);
              $mLabel  = ($moisFr[$mo] ?? $mo) . ' ' . $y;
              $mDevis  = (int)(Database::fetch('SELECT COUNT(*) as c FROM devis WHERE DATE_FORMAT(created_at,"%Y-%m")=?', [$mDate])['c'] ?? 0);
              $mLeads  = (int)(Database::fetch('SELECT COUNT(*) as c FROM leads WHERE DATE_FORMAT(created_at,"%Y-%m")=?', [$mDate])['c'] ?? 0);
              $mRev    = (float)(Database::fetch("SELECT COALESCE(SUM(amount),0) as s FROM paiements WHERE status='paid' AND DATE_FORMAT(created_at,'%Y-%m')=?", [$mDate])['s'] ?? 0);
              $mTx     = $mDevis > 0 ? round($mLeads / $mDevis * 100) : 0;
            ?>
              <tr class="border-b border-outline-variant/5 hover:bg-surface-container transition-colors <?= $m === 0 ? 'font-semibold' : ($m > 3 ? 'opacity-50' : '') ?>">
                <td class="px-10 py-6 font-bold"><?= $mLabel ?></td>
                <td class="px-10 py-6"><?= $mDevis ?></td>
                <td class="px-10 py-6"><?= $mLeads ?></td>
                <td class="px-10 py-6">
                  <div class="flex items-center gap-2">
                    <div class="w-16 h-1.5 bg-outline-variant/20 rounded-full">
                      <div class="h-full bg-primary rounded-full" style="width:<?= min(100, $mTx) ?>%"></div>
                    </div>
                    <span><?= $mTx ?>%</span>
                  </div>
                </td>
                <td class="px-10 py-6 text-right <?= $mRev > 0 ? 'text-primary font-bold' : 'text-outline' ?>">
                  <?= $mRev > 0 ? number_format($mRev, 0, ',', ' ') . ' €' : '—' ?>
                </td>
              </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Citation éditoriale -->
    <div class="mt-20 flex justify-center">
      <div class="max-w-2xl bg-surface-container-highest p-12 relative rounded-lg border border-primary/5">
        <span class="absolute top-4 left-6 text-9xl font-headline opacity-10 text-primary pointer-events-none italic">"</span>
        <blockquote class="relative z-10 text-center">
          <p class="font-headline italic text-2xl text-on-surface leading-relaxed mb-6">
            "La croissance n'est pas qu'une question de volume, c'est l'art de préserver l'excellence artisanale à l'échelle industrielle."
          </p>
          <cite class="not-italic font-label uppercase tracking-[0.2em] text-[10px] text-primary font-bold">— Note de la Rédaction</cite>
        </blockquote>
      </div>
    </div>

  </section>

  <footer class="h-20 border-t border-outline-variant/10 flex items-center justify-between px-12 bg-surface text-[10px] font-label uppercase tracking-[0.2em] text-outline/50">
    <div>© <?= date('Y') ?> Info-Devis Curator System.</div>
    <div class="flex space-x-6">
      <a href="#" class="hover:text-primary transition-colors">Confidentialité</a>
      <a href="#" class="hover:text-primary transition-colors">Audit logs</a>
    </div>
  </footer>
</main>