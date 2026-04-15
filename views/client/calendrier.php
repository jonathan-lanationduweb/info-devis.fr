<?php /* views/client/calendrier.php */ ?>
<?php
$moisFr = ['01'=>'Jan','02'=>'Fév','03'=>'Mar','04'=>'Avr','05'=>'Mai','06'=>'Jun',
           '07'=>'Jul','08'=>'Aoû','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Déc'];
$currentMonth = date('Y-m');
$daysInMonth  = (int)date('t', strtotime($currentMonth . '-01'));
$days         = [];
for ($d = 1; $d <= $daysInMonth; $d++) {
    $ds = $currentMonth . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
    $days[] = ['date' => $ds, 'day' => $d, 'dow' => date('D', strtotime($ds)), 'past' => $ds < date('Y-m-d')];
}
?>
<?php include BASE_PATH . '/views/client/_sidebar.php'; ?>

<main class="md:ml-64 pt-20 md:pt-32 px-4 md:px-12 pb-24 md:pb-20 min-h-screen bg-[#f3f4f3]">
<div class="max-w-6xl mx-auto">

  <!-- En-tête -->
  <header class="mb-8">
    <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-4">
      <a href="<?= APP_URL ?>/dashboard/client" class="hover:text-primary transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-sm">arrow_back</span> Dashboard
      </a>
    </div>
    <h1 class="font-headline text-3xl md:text-4xl italic text-on-surface mb-2">Disponibilités des artisans</h1>
    <p class="text-sm text-on-surface-variant">Consultez les créneaux disponibles des artisans vérifiés de votre région et prenez contact directement.</p>
  </header>

  <!-- Filtres catégorie -->
  <?php $categories = Database::fetchAll('SELECT * FROM categories ORDER BY sort_order ASC, name ASC LIMIT 12'); ?>
  <div class="flex gap-2 flex-wrap mb-8">
    <a href="?" class="px-3 py-1.5 rounded-full text-xs font-bold border <?= empty($_GET['cat']) ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant/30 text-on-surface-variant hover:border-primary hover:text-primary' ?> transition-all">
      Tous
    </a>
    <?php foreach ($categories as $cat): ?>
    <a href="?cat=<?= urlencode($cat['slug']) ?>"
       class="px-3 py-1.5 rounded-full text-xs font-bold border <?= ($_GET['cat'] ?? '') === $cat['slug'] ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant/30 text-on-surface-variant hover:border-primary hover:text-primary' ?> transition-all">
      <?= Security::e($cat['name']) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($artisans)): ?>
  <div class="text-center py-16">
    <span class="material-symbols-outlined text-5xl text-outline-variant block mb-4">calendar_today</span>
    <p class="text-on-surface-variant">Aucun artisan disponible trouvé.</p>
  </div>
  <?php else: ?>

  <div class="space-y-6">
    <?php foreach ($artisans as $art):
      // Construire la map de disponibilités pour ce mois
      $avMap = [];
      foreach ($art['availability'] ?? [] as $av) {
          $avMap[$av['date']] = ['status' => $av['status'], 'note' => $av['note'] ?? ''];
      }
    ?>
    <div class="bg-white rounded-2xl border border-outline-variant/15 p-5 md:p-6 hover:shadow-sm transition-all">

      <!-- En-tête artisan -->
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-5">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold text-lg flex-shrink-0">
            <?= strtoupper(substr($art['company_name'] ?? 'A', 0, 1)) ?>
          </div>
          <div>
            <div class="flex items-center gap-2 mb-0.5">
              <h3 class="font-headline text-xl font-bold"><?= Security::e($art['company_name']) ?></h3>
              <span class="bg-green-100 text-green-700 text-[9px] font-bold px-2 py-0.5 rounded-full border border-green-200">✓ Vérifié</span>
            </div>
            <p class="text-sm text-on-surface-variant flex items-center gap-1">
              <span class="material-symbols-outlined text-sm">location_on</span>
              <?= Security::e($art['ville'] ?? '') ?>
              <?php if (!empty($art['categories'])): ?> · <?= Security::e($art['categories']) ?><?php endif; ?>
            </p>
            <?php if (!empty($art['calendar_description'])): ?>
            <p class="text-xs text-primary/70 italic mt-1">"<?= Security::e($art['calendar_description']) ?>"</p>
            <?php endif; ?>
          </div>
        </div>
        <a href="<?= APP_URL ?>/devis?artisan=<?= (int)$art['id'] ?>"
           class="flex-shrink-0 bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:opacity-90 transition-all flex items-center gap-1">
          <span class="material-symbols-outlined text-sm">description</span> Demander un devis
        </a>
      </div>

      <!-- Mini calendrier du mois courant -->
      <div class="overflow-x-auto">
        <div class="min-w-max">
          <!-- Jours de la semaine -->
          <div class="flex gap-1 mb-1">
            <?php foreach ($days as $day): ?>
            <div class="w-8 text-center">
              <p class="text-[8px] font-bold uppercase text-stone-400"><?= substr($day['dow'], 0, 2) ?></p>
              <p class="text-[9px] text-stone-500"><?= $day['day'] ?></p>
            </div>
            <?php endforeach; ?>
          </div>
          <!-- Statuts -->
          <div class="flex gap-1">
            <?php foreach ($days as $day):
              $info   = $avMap[$day['date']] ?? ['status' => '', 'note' => ''];
              $status = $info['status'];
              $note   = $info['note'];
              $isPast = $day['past'];
              $isToday = $day['date'] === date('Y-m-d');

              if ($status === 'available') {
                  $bg    = 'bg-green-100 border-green-300';
                  $title = '✓ Disponible' . ($note ? ' — ' . $note : '');
              } elseif ($status === 'unavailable') {
                  $bg    = 'bg-red-100 border-red-300';
                  $title = '✗ Indisponible' . ($note ? ' — ' . $note : '');
              } else {
                  $bg    = 'bg-stone-100 border-stone-200';
                  $title = 'Non renseigné';
              }
              if ($isPast) $bg .= ' opacity-30';
              if ($isToday) $bg .= ' ring-2 ring-primary ring-offset-1';
            ?>
            <div class="w-8 h-8 rounded-lg border <?= $bg ?> flex items-center justify-center cursor-default transition-all hover:scale-110"
                 title="<?= htmlspecialchars($day['date'] . ' — ' . $title) ?>">
              <?php if ($status === 'available'): ?>
                <span style="font-size:10px;color:#166534;font-weight:700">✓</span>
              <?php elseif ($status === 'unavailable'): ?>
                <span style="font-size:10px;color:#991b1b;font-weight:700">✗</span>
              <?php else: ?>
                <span style="font-size:8px;color:#9ca3af">—</span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Légende + bouton -->
      <div class="flex items-center justify-between mt-3 flex-wrap gap-3">
        <div class="flex items-center gap-4 text-[10px] text-on-surface-variant">
          <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-100 border border-green-300 inline-block"></span> Disponible</span>
          <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-100 border border-red-300 inline-block"></span> Indisponible</span>
          <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-stone-100 border border-stone-200 inline-block"></span> Non renseigné</span>
        </div>
        <?php
        $nextDispo = null;
        foreach ($days as $day) {
            if (!$day['past'] && isset($avMap[$day['date']]) && $avMap[$day['date']]['status'] === 'available') {
                $nextDispo = $day['date']; break;
            }
        }
        ?>
        <?php if ($nextDispo): ?>
        <span class="text-xs text-primary font-bold">
          Prochain créneau : <?= date('d/m/Y', strtotime($nextDispo)) ?>
        </span>
        <?php endif; ?>
      </div>

    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>
</div>
</main>
