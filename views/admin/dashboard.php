<?php /* views/admin/dashboard.php */ ?>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
  }
</style>

<aside class="fixed left-0 top-20 h-[calc(100vh-5rem)] w-64 border-r border-[#aeb3b2]/15 bg-[#faf9f8] hidden lg:flex flex-col py-8 px-4 gap-2 z-40">
  <div class="mb-8 px-4">
    <h3 class="font-label uppercase tracking-widest text-xs text-primary mb-1">Espace Membre</h3>
    <p class="text-xs text-stone-500">Administration</p>
  </div>
  <nav class="flex-1 space-y-1">
    <a href="<?= APP_URL ?>/admin" class="flex items-center gap-3 bg-primary/5 text-primary rounded-lg px-4 py-3 font-bold">
      <span class="material-symbols-outlined">dashboard</span>
      <span class="font-label uppercase tracking-widest text-xs">Tableau de bord</span>
    </a>
    <a href="<?= APP_URL ?>/admin/artisans" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">handyman</span>
      <span class="font-label uppercase tracking-widest text-xs">Artisans<?= $metrics['pending_artisans'] > 0 ? ' (' . $metrics['pending_artisans'] . ')' : '' ?></span>
    </a>
    <a href="<?= APP_URL ?>/admin/devis" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">description</span>
      <span class="font-label uppercase tracking-widest text-xs">Devis</span>
    </a>
    <a href="<?= APP_URL ?>/admin/users" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">group</span>
      <span class="font-label uppercase tracking-widest text-xs">Utilisateurs</span>
    </a>
    <a href="<?= APP_URL ?>/admin/blog" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">edit_note</span>
      <span class="font-label uppercase tracking-widest text-xs">Blog<?= $metrics['pending_blog'] > 0 ? ' (' . $metrics['pending_blog'] . ')' : '' ?></span>
    </a>
    <a href="<?= APP_URL ?>/admin/paiements" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">payments</span>
      <span class="font-label uppercase tracking-widest text-xs">Paiements</span>
    </a>
    <a href="<?= APP_URL ?>/admin/abonnements" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">card_membership</span>
      <span class="font-label uppercase tracking-widest text-xs">Abonnements</span>
    </a>
    <a href="<?= APP_URL ?>/admin/metrics" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">query_stats</span>
      <span class="font-label uppercase tracking-widest text-xs">Métriques</span>
    </a>
    <a href="<?= APP_URL ?>/admin/categories" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">category</span>
      <span class="font-label uppercase tracking-widest text-xs">Catégories</span>
    </a>
  </nav>
  <div class="p-4 bg-stone-50 rounded-xl border border-outline-variant/10">
    <p class="text-xs font-semibold mb-2">Support admin</p>
    <button class="text-primary text-[10px] font-bold uppercase tracking-widest border-b border-primary/30">Documentation</button>
  </div>
</aside>

<main class="pt-24 pb-12 px-8 lg:ml-64 min-h-screen">

  <!-- Header -->
  <?php
  $moisFrAdmin = [
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
  $todayFr = date('d') . ' ' . ($moisFrAdmin[date('m')] ?? date('m')) . ' ' . date('Y');
  ?>
  <header class="mb-12">
    <h1 class="text-5xl font-headline font-bold text-on-surface tracking-tight mb-2">Vue d'ensemble</h1>
    <p class="text-on-surface-variant font-body">
      Analyse de la performance de la plateforme au <span class="italic"><?= $todayFr ?></span>.
    </p>
  </header>

  <!-- Stats Bento -->
  <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
    <?php
    $statCards = [
      ['Total Artisans vérifiés', $metrics['total_artisans'],   null],
      ['Total Clients',           $metrics['total_clients'],    null],
      ['Devis envoyés',           $metrics['total_devis'],      null],
      ['CA mensuel',              number_format($metrics['revenue_month'], 0, ',', ' ') . ' €', true],
    ];
    foreach ($statCards as [$label, $val, $highlight]):
    ?>
      <div class="bg-surface-container-lowest p-8 rounded-xl border border-outline-variant/10 shadow-sm">
        <p class="font-label text-xs uppercase tracking-widest text-on-surface-variant mb-4"><?= $label ?></p>
        <div class="flex items-end justify-between">
          <span class="text-4xl font-headline font-medium"><?= $val ?></span>
          <?php if ($highlight): ?>
            <span class="text-primary text-xs font-bold bg-primary-container px-2 py-1 rounded">Ce mois</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Colonne principale -->
    <div class="lg:col-span-2 space-y-8">

      <!-- Graphique leads par jour (visuel statique) -->
      <div class="bg-surface-container-lowest p-8 rounded-2xl border border-outline-variant/10">
        <div class="flex justify-between items-center mb-8">
          <h2 class="text-2xl font-headline font-bold">Leads par jour</h2>
          <div class="flex gap-4 text-xs font-bold uppercase tracking-widest">
            <span class="text-primary border-b border-primary">7 derniers jours</span>
            <a href="<?= APP_URL ?>/admin/metrics" class="text-outline-variant hover:text-primary transition-colors">Mensuel</a>
          </div>
        </div>
        <div class="h-40 flex items-end justify-between gap-2">
          <?php
          // Vraies données des 7 derniers jours
          $days = [];
          for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $count = Database::fetch('SELECT COUNT(*) as c FROM leads WHERE DATE(created_at) = ?', [$date])['c'] ?? 0;
            $days[] = ['label' => date('D', strtotime($date)), 'count' => $count];
          }
          $maxCount = max(array_column($days, 'count')) ?: 1;
          foreach ($days as $day):
            $height = max(5, round(($day['count'] / $maxCount) * 100));
          ?>
            <div class="flex-1 flex flex-col items-center gap-2">
              <span class="text-[10px] text-outline font-bold"><?= $day['count'] ?></span>
              <div class="w-full bg-<?= $day['count'] === max(array_column($days, 'count')) ? 'primary' : 'surface-container-high' ?> rounded-t-sm transition-all" style="height:<?= $height ?>%"></div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="flex justify-between mt-4">
          <?php foreach ($days as $day): ?>
            <span class="flex-1 text-center text-[10px] text-outline uppercase tracking-widest font-bold"><?= $day['label'] ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Artisans en attente -->
      <?php if ($metrics['pending_artisans'] > 0):
        $pendingArtisans = Database::fetchAll(
          'SELECT a.*, u.first_name, u.last_name, u.email FROM artisans a JOIN users u ON u.id = a.user_id WHERE a.verification_status = "pending" LIMIT 5'
        );
      ?>
        <div class="bg-surface-container-lowest p-8 rounded-2xl border border-outline-variant/10">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-headline font-bold">Artisans en attente de validation</h2>
            <a href="<?= APP_URL ?>/admin/artisans?status=pending" class="text-xs text-primary font-bold uppercase tracking-widest hover:underline">Voir tout</a>
          </div>
          <div class="space-y-4">
            <?php foreach ($pendingArtisans as $a): ?>
              <div class="flex items-center justify-between p-4 hover:bg-surface-container-low transition-colors rounded-lg" id="pa-<?= $a['id'] ?>">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center font-bold text-primary text-lg">
                    <?= strtoupper(substr($a['company_name'] ?? $a['first_name'], 0, 1)) ?>
                  </div>
                  <div>
                    <h4 class="font-headline font-bold text-lg"><?= Security::e($a['company_name']) ?></h4>
                    <p class="text-xs text-on-surface-variant"><?= Security::e($a['first_name'] . ' ' . $a['last_name']) ?> · <?= Security::e($a['ville'] ?? '') ?></p>
                  </div>
                </div>
                <div class="flex gap-3">
                  <a href="<?= APP_URL ?>/admin/artisans?status=pending" class="px-4 py-2 text-xs font-bold uppercase tracking-widest text-outline-variant hover:text-on-surface transition-colors">Voir dossier</a>
                  <button onclick="quickValidate(<?= $a['id'] ?>)"
                    class="px-6 py-2 bg-primary text-on-primary text-xs font-bold uppercase tracking-widest rounded transition-transform active:scale-95 hover:opacity-90">
                    Approuver
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>

    <!-- Sidebar -->
    <div class="space-y-8">

      <!-- Activité récente -->
      <div class="bg-surface-container-low/50 p-8 rounded-2xl border border-outline-variant/15">
        <h2 class="text-xl font-headline font-bold mb-6">Activité récente</h2>
        <?php
        $recentActivity = Database::fetchAll(
          "SELECT 'devis' as type, reference as label, created_at FROM devis
           UNION ALL
           SELECT 'user' as type, CONCAT(first_name,' ',last_name) as label, created_at FROM users WHERE role='client'
           ORDER BY created_at DESC LIMIT 6"
        );
        $actColors = ['devis' => 'bg-primary', 'user' => 'bg-tertiary'];
        ?>
        <div class="space-y-6 relative before:absolute before:left-2 before:top-2 before:bottom-2 before:w-[1px] before:bg-outline-variant/20">
          <?php if (empty($recentActivity)): ?>
            <p class="text-sm text-on-surface-variant pl-8">Aucune activité récente</p>
          <?php else: ?>
            <?php foreach ($recentActivity as $act): ?>
              <div class="relative pl-8">
                <div class="absolute left-0 top-1.5 w-4 h-4 <?= $actColors[$act['type']] ?? 'bg-secondary' ?> rounded-full border-4 border-surface ring-1 ring-primary/20"></div>
                <p class="text-sm font-headline font-bold"><?= $act['type'] === 'devis' ? 'Nouveau devis' : 'Nouvel inscrit' ?></p>
                <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1">
                  <?= Security::e($act['label']) ?> · <?= !empty($act['created_at']) ? date('d/m H:i', strtotime($act['created_at'])) : '' ?>
                </p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Citation -->
      <div class="bg-surface-container-highest p-8 rounded-2xl relative overflow-hidden">
        <span class="absolute -top-4 -right-4 text-9xl font-headline text-on-surface opacity-[0.03] select-none italic">"</span>
        <p class="text-lg font-headline font-medium leading-relaxed mb-6 relative z-10 italic">
          "La qualité d'un curateur se mesure à la rigueur de sa sélection. Maintenez un standard élevé pour les artisans."
        </p>
        <div class="flex items-center gap-3">
          <div class="w-8 h-[1px] bg-primary"></div>
          <span class="text-[10px] uppercase tracking-widest font-bold text-on-surface">Note de la Rédaction</span>
        </div>
      </div>

      <!-- CA mensuel -->
      <div class="bg-primary p-8 rounded-2xl text-on-primary shadow-xl shadow-primary/10">
        <h3 class="font-label text-xs uppercase tracking-[0.2em] mb-4 opacity-80">Revenus Mensuels</h3>
        <div class="text-3xl font-headline font-bold mb-4 italic">
          <?= number_format($metrics['revenue_month'], 0, ',', ' ') ?> €
        </div>
        <div class="flex gap-1 h-12 items-end">
          <?php
          $mRevs = [];
          for ($m = 4; $m >= 0; $m--) {
            $mDate = date('Y-m', strtotime("-$m months"));
            $row = Database::fetch("SELECT COALESCE(SUM(amount),0) as s FROM paiements WHERE status='paid' AND DATE_FORMAT(created_at,'%Y-%m')=?", [$mDate]);
            $mRevs[] = (float)($row['s'] ?? 0);
          }
          $maxRev = max($mRevs) > 0 ? max($mRevs) : 1;
          foreach ($mRevs as $i => $rev):
            $h = max(10, round(($rev / $maxRev) * 100));
          ?>
            <div class="flex-1 <?= $i === 4 ? 'bg-on-primary' : 'bg-on-primary/30' ?> rounded-sm" style="height:<?= $h ?>%"></div>
          <?php endforeach; ?>
        </div>
        <div class="flex justify-between mt-2">
          <?php for ($m = 4; $m >= 0; $m--): ?>
            <span class="flex-1 text-center text-[9px] opacity-60"><?= date('M', strtotime("-$m months")) ?></span>
          <?php endfor; ?>
        </div>
      </div>

    </div>
  </div>
</main>

<script>
  async function quickValidate(id) {
    if (!confirm('Valider cet artisan ?')) return;
    const form = new FormData();
    form.append('artisan_id', id);
    form.append('action', 'validate');
    form.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    const res = await fetch('<?= APP_URL ?>/admin/artisan/validate', {
      method: 'POST',
      body: form
    });
    const data = await res.json();
    if (data.success) {
      const el = document.getElementById('pa-' + id);
      if (el) el.remove();
    } else alert('Erreur');
  }
</script>