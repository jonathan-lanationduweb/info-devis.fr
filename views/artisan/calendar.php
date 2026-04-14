<?php /* views/artisan/calendar.php */ ?>
<?php
// ── AUCUN strftime — compatible PHP 8.3 + Windows ─────────
$currentMonth = $month ?? date('Y-m');

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
  '12' => 'Décembre',
];
[$annee, $moisNum] = explode('-', $currentMonth);
$monthLabel  = ($moisFr[$moisNum] ?? $moisNum) . ' ' . $annee;
$prevMonth   = date('Y-m', strtotime($currentMonth . '-01 -1 month'));
$nextMonth   = date('Y-m', strtotime($currentMonth . '-01 +1 month'));
$firstDay    = (int)date('N', strtotime($currentMonth . '-01'));
$daysInMonth = (int)date('t', strtotime($currentMonth . '-01'));

$availMap = [];
foreach ($availability ?? [] as $av) {
  if (!empty($av['date'])) $availMap[$av['date']] = $av['status'] ?? '';
}
$countAv   = count(array_filter($availMap, fn($s) => $s === 'available'));
$countUnav = count(array_filter($availMap, fn($s) => $s === 'unavailable'));
?>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24
  }

  .day-cell {
    cursor: pointer;
    transition: background .1s;
    user-select: none
  }

  .day-cell.past {
    opacity: .28;
    cursor: not-allowed;
    pointer-events: none
  }

  .day-cell.av {
    background: #f0fdf4
  }

  .day-cell.unav {
    background: #fff1f2
  }

  .day-cell:hover:not(.av):not(.unav) {
    background: #f3f4f3
  }
</style>

<?php include __DIR__ . '/_sidebar.php'; ?>

<main class="md:ml-72 pt-28 px-6 pb-16 min-h-screen bg-[#f3f4f3]">
  <div class="max-w-5xl mx-auto">

    <header class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="font-headline text-4xl italic text-on-surface mb-1">Disponibilités</h1>
        <p class="text-sm text-on-surface-variant">
          Clic 1 → <span class="text-green-700 font-bold">Disponible</span> ·
          Clic 2 → <span class="text-red-700 font-bold">Indisponible</span> ·
          Clic 3 → <span class="text-stone-400 font-bold">Effacer</span>
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <a href="<?= APP_URL ?>/dashboard/artisan"
          class="flex items-center gap-1 bg-white border border-stone-200 px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-widest hover:border-primary hover:text-primary transition-all">
          <span class="material-symbols-outlined text-base">home</span> Dashboard
        </a>
        <div class="flex items-center bg-white border border-stone-200 rounded-lg overflow-hidden">
          <a href="?month=<?= $prevMonth ?>" class="p-2 hover:bg-stone-50 border-r border-stone-100">
            <span class="material-symbols-outlined">chevron_left</span>
          </a>
          <span class="font-headline text-base font-semibold px-4 italic whitespace-nowrap"><?= $monthLabel ?></span>
          <a href="?month=<?= $nextMonth ?>" class="p-2 hover:bg-stone-50 border-l border-stone-100">
            <span class="material-symbols-outlined">chevron_right</span>
          </a>
        </div>
      </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

      <aside class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
          <p class="font-label text-[10px] uppercase tracking-widest font-bold text-stone-400 mb-3">Légende</p>
          <div class="space-y-2 text-sm">
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 rounded bg-green-100 border border-green-300"></div><span>Disponible</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 rounded bg-red-100 border border-red-300"></div><span>Indisponible</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 rounded bg-white border border-stone-200"></div><span>Non défini</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-5">
          <p class="font-label text-[10px] uppercase tracking-widest font-bold text-stone-400 mb-3">Ce mois</p>
          <div class="flex justify-between text-sm mb-1.5">
            <span class="text-green-700 font-semibold">✅ Dispos</span>
            <span class="font-bold" id="cnt-av"><?= $countAv ?></span>
          </div>
          <div class="flex justify-between text-sm mb-3">
            <span class="text-red-700 font-semibold">❌ Indispos</span>
            <span class="font-bold" id="cnt-unav"><?= $countUnav ?></span>
          </div>
          <div class="h-2 bg-stone-100 rounded-full overflow-hidden">
            <div id="pct-bar" class="h-full bg-primary rounded-full transition-all"
              style="width:<?= $daysInMonth > 0 ? round($countAv / $daysInMonth * 100) : 0 ?>%"></div>
          </div>
          <p class="text-xs text-stone-400 mt-1"><span id="cnt-lbl"><?= $countAv ?></span>/<?= $daysInMonth ?> jours</p>
        </div>

        <div class="bg-primary/5 border border-primary/15 rounded-xl p-4">
          <p class="font-headline italic text-primary text-sm">"Un artisan organisé rassure ses clients."</p>
        </div>
      </aside>

      <div class="lg:col-span-3 bg-white rounded-xl border border-stone-200 overflow-hidden">
        <div class="grid grid-cols-7 bg-stone-50 border-b border-stone-100">
          <?php foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $j): ?>
            <div class="py-3 text-center text-[10px] font-bold uppercase tracking-widest text-stone-400"><?= $j ?></div>
          <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-7 divide-x divide-y divide-stone-100" style="grid-auto-rows:68px">
          <?php for ($i = 1; $i < $firstDay; $i++): ?>
            <div class="bg-stone-50/40"></div>
          <?php endfor; ?>

          <?php for ($d = 1; $d <= $daysInMonth; $d++):
            $ds     = $currentMonth . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
            $st     = $availMap[$ds] ?? '';
            $isPast = $ds < date('Y-m-d');
            $isToday = $ds === date('Y-m-d');
            $cls    = ($st === 'available' ? 'av' : ($st === 'unavailable' ? 'unav' : ''));
            if ($isPast) $cls .= ' past';
          ?>
            <div class="day-cell <?= $cls ?> flex flex-col items-center justify-center gap-0.5"
              id="d-<?= $ds ?>" data-date="<?= $ds ?>" data-status="<?= $st ?>"
              onclick="toggleDay('<?= $ds ?>')">
              <span class="font-headline text-lg leading-none
            <?= $st === 'available'   ? 'text-green-800 font-bold' : '' ?>
            <?= $st === 'unavailable' ? 'text-red-800 font-bold'   : '' ?>
            <?= $isToday ? 'underline decoration-primary decoration-2 underline-offset-2' : '' ?>">
                <?= $d ?>
              </span>
              <?php if ($st === 'available'): ?><span class="lbl text-[8px] text-green-600 font-bold leading-none">✓ dispo</span>
              <?php elseif ($st === 'unavailable'): ?><span class="lbl text-[8px] text-red-600 font-bold leading-none">✗ indispo</span>
              <?php endif; ?>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>

  </div>
</main>

<script>
  const TOTAL = <?= (int)$daysInMonth ?>;
  const counts = {
    av: <?= (int)$countAv ?>,
    unav: <?= (int)$countUnav ?>
  };

  function toggleDay(date) {
    const el = document.getElementById('d-' + date);
    if (!el || el.classList.contains('past')) return;

    const cur = el.dataset.status;
    const next = cur === '' ? 'available' : cur === 'available' ? 'unavailable' : '';

    if (cur === 'available') counts.av--;
    if (cur === 'unavailable') counts.unav--;
    if (next === 'available') counts.av++;
    if (next === 'unavailable') counts.unav++;

    el.dataset.status = next;
    redraw(el, next);
    refreshUI();
    save(date, next);
  }

  function redraw(el, status) {
    el.classList.remove('av', 'unav');
    const num = el.querySelector('span:first-child');
    if (num) {
      num.classList.remove('text-green-800', 'text-red-800', 'font-bold');
    }
    const lbl = el.querySelector('.lbl');
    if (lbl) lbl.remove();

    if (status === 'available') {
      el.classList.add('av');
      if (num) num.classList.add('text-green-800', 'font-bold');
      const s = document.createElement('span');
      s.className = 'lbl text-[8px] text-green-600 font-bold leading-none';
      s.textContent = '✓ dispo';
      el.appendChild(s);
    } else if (status === 'unavailable') {
      el.classList.add('unav');
      if (num) num.classList.add('text-red-800', 'font-bold');
      const s = document.createElement('span');
      s.className = 'lbl text-[8px] text-red-600 font-bold leading-none';
      s.textContent = '✗ indispo';
      el.appendChild(s);
    }
  }

  function refreshUI() {
    document.getElementById('cnt-av').textContent = counts.av;
    document.getElementById('cnt-unav').textContent = counts.unav;
    document.getElementById('cnt-lbl').textContent = counts.av;
    document.getElementById('pct-bar').style.width = Math.round(counts.av / TOTAL * 100) + '%';
  }

  async function save(date, status) {
    const fd = new FormData();
    fd.append('date', date);
    fd.append('status', status);
    fd.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>');
    try {
      await fetch('<?= APP_URL ?>/dashboard/artisan/calendar', {
        method: 'POST',
        body: fd
      });
    } catch (e) {
      console.error('Erreur save:', e);
    }
  }
</script>