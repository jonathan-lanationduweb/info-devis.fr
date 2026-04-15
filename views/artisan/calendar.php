<?php /* views/artisan/calendar.php */ ?>
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

$currentMonth = $month ?? date('Y-m');
[$annee, $moisNum] = explode('-', $currentMonth);
$monthLabel  = ($moisFr[$moisNum] ?? $moisNum) . ' ' . $annee;
$prevMonth   = date('Y-m', strtotime($currentMonth . '-01 -1 month'));
$nextMonth   = date('Y-m', strtotime($currentMonth . '-01 +1 month'));
$firstDay    = (int)date('N', strtotime($currentMonth . '-01'));
$daysInMonth = (int)date('t',  strtotime($currentMonth . '-01'));

// Construire la map : date => {status, note}
$availMap = [];
foreach ($availability ?? [] as $av) {
  if (!empty($av['date'])) {
    $availMap[$av['date']] = ['status' => $av['status'] ?? '', 'note' => $av['note'] ?? ''];
  }
}
$countAv   = count(array_filter($availMap, fn($v) => $v['status'] === 'available'));
$countUnav = count(array_filter($availMap, fn($v) => $v['status'] === 'unavailable'));
?>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24
  }

  .day-cell {
    cursor: pointer;
    transition: background .1s;
    user-select: none;
    position: relative
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

  .day-cell:hover:not(.av):not(.unav):not(.past) {
    background: #f3f4f3
  }

  .day-cell.has-note::after {
    content: '';
    position: absolute;
    top: 4px;
    right: 4px;
    width: 5px;
    height: 5px;
    background: #0e6c48;
    border-radius: 50%
  }

  /* Modal note */
  #note-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, .5);
    align-items: center;
    justify-content: center
  }

  #note-modal.open {
    display: flex
  }
</style>

<?php include __DIR__ . '/_sidebar.php'; ?>

<main class="md:ml-72 pt-20 md:pt-28 px-4 md:px-6 pb-20 md:pb-16 min-h-screen bg-[#f3f4f3]">
  <div class="max-w-5xl mx-auto">

    <!-- En-tête -->
    <header class="mb-6 md:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="font-headline text-3xl md:text-4xl italic text-on-surface mb-1">Disponibilités</h1>
        <p class="text-xs md:text-sm text-on-surface-variant">
          Clic 1 → <span class="text-green-700 font-bold">Disponible</span> ·
          Clic 2 → <span class="text-red-700 font-bold">Indisponible</span> ·
          Clic 3 → Effacer · <span class="text-primary font-bold">Maintien = ajouter une note</span>
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <button onclick="openNoteModal(null)"
          class="flex items-center gap-1 bg-primary text-on-primary px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-widest hover:opacity-90 transition-all">
          <span class="material-symbols-outlined text-base">edit_note</span> Note générale
        </button>
        <div class="flex items-center bg-white border border-stone-200 rounded-lg overflow-hidden">
          <a href="?month=<?= $prevMonth ?>" class="p-2 hover:bg-stone-50 border-r border-stone-100">
            <span class="material-symbols-outlined">chevron_left</span>
          </a>
          <span class="font-headline text-sm md:text-base font-semibold px-3 md:px-4 italic whitespace-nowrap"><?= $monthLabel ?></span>
          <a href="?month=<?= $nextMonth ?>" class="p-2 hover:bg-stone-50 border-l border-stone-100">
            <span class="material-symbols-outlined">chevron_right</span>
          </a>
        </div>
      </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6">

      <!-- Colonne info -->
      <aside class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl border border-stone-200 p-4 md:p-5">
          <p class="font-label text-[10px] uppercase tracking-widest font-bold text-stone-400 mb-3">Légende</p>
          <div class="space-y-2 text-sm">
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded bg-green-100 border border-green-300"></div><span>Disponible</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded bg-red-100 border border-red-300"></div><span>Indisponible</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded bg-white border border-stone-200"></div><span>Non défini</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded bg-white border border-stone-200 relative">
                <div style="width:6px;height:6px;background:#0e6c48;border-radius:50%;position:absolute;top:2px;right:2px"></div>
              </div><span class="text-xs">● = note</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-4 md:p-5">
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

        <!-- Description générale du calendrier -->
        <div class="bg-white rounded-xl border border-stone-200 p-4 md:p-5">
          <p class="font-label text-[10px] uppercase tracking-widest font-bold text-stone-400 mb-2">Description calendrier</p>
          <p class="text-xs text-stone-500 mb-2">Visible par les clients qui consultent votre profil</p>
          <textarea id="cal-description" rows="3"
            class="w-full border border-stone-200 rounded-lg px-3 py-2 text-xs focus:ring-1 focus:ring-primary resize-none"
            placeholder="Ex: Disponible en semaine, pas le weekend. Délai d'intervention : 48h..."><?= Security::e($artisan['calendar_description'] ?? '') ?></textarea>
          <button onclick="saveCalDescription()"
            class="mt-2 w-full bg-primary/10 text-primary px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-primary/20 transition-all">
            Sauvegarder
          </button>
          <p id="desc-ok" class="text-xs text-green-600 text-center mt-1 hidden">✅ Sauvegardé</p>
        </div>

        <div class="bg-primary/5 border border-primary/15 rounded-xl p-4 hidden md:block">
          <p class="font-headline italic text-primary text-sm">"Un artisan organisé rassure ses clients."</p>
        </div>
      </aside>

      <!-- Calendrier -->
      <div class="lg:col-span-3 bg-white rounded-xl border border-stone-200 overflow-hidden">
        <div class="grid grid-cols-7 bg-stone-50 border-b border-stone-100">
          <?php foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $j): ?>
            <div class="py-2 md:py-3 text-center text-[9px] md:text-[10px] font-bold uppercase tracking-widest text-stone-400"><?= $j ?></div>
          <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-7 divide-x divide-y divide-stone-100" style="grid-auto-rows:56px">
          <?php for ($i = 1; $i < $firstDay; $i++): ?>
            <div class="bg-stone-50/40"></div>
          <?php endfor; ?>

          <?php for ($d = 1; $d <= $daysInMonth; $d++):
            $ds      = $currentMonth . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
            $info    = $availMap[$ds] ?? ['status' => '', 'note' => ''];
            $st      = $info['status'];
            $note    = $info['note'];
            $isPast  = $ds < date('Y-m-d');
            $isToday = $ds === date('Y-m-d');
            $cls     = ($st === 'available' ? 'av' : ($st === 'unavailable' ? 'unav' : ''));
            if ($isPast)   $cls .= ' past';
            if (!empty($note)) $cls .= ' has-note';
          ?>
            <div class="day-cell <?= $cls ?> flex flex-col items-center justify-center gap-0.5"
              id="d-<?= $ds ?>" data-date="<?= $ds ?>" data-status="<?= $st ?>"
              data-note="<?= htmlspecialchars($note) ?>"
              onclick="toggleDay('<?= $ds ?>')"
              oncontextmenu="return openNoteModal('<?= $ds ?>')"
              title="<?= $note ? 'Note: ' . htmlspecialchars($note) : 'Clic = changer statut | Clic droit = note' ?>">
              <span class="font-headline text-sm md:text-base leading-none
            <?= $st === 'available'   ? 'text-green-800 font-bold' : '' ?>
            <?= $st === 'unavailable' ? 'text-red-800   font-bold' : '' ?>
            <?= $isToday ? 'underline decoration-primary decoration-2 underline-offset-2' : '' ?>">
                <?= $d ?>
              </span>
              <?php if ($st === 'available'):   ?><span class="lbl text-[7px] md:text-[8px] text-green-600 font-bold leading-none">✓</span>
              <?php elseif ($st === 'unavailable'): ?><span class="lbl text-[7px] md:text-[8px] text-red-600 font-bold leading-none">✗</span>
              <?php endif; ?>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- ── Modal note par jour ─────────────────────────────────────────────── -->
<div id="note-modal" onclick="closeNoteModal(event)">
  <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4" onclick="event.stopPropagation()">
    <h3 class="font-headline text-xl italic mb-1" id="note-modal-title">Note du jour</h3>
    <p class="text-xs text-on-surface-variant mb-4" id="note-modal-date"></p>
    <textarea id="note-input" rows="4"
      class="w-full border border-outline-variant/20 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-primary resize-none"
      placeholder="Ex: Déplacement prévu, chantier en cours, disponible uniquement le matin..."></textarea>
    <div class="flex gap-3 mt-4">
      <button onclick="closeNoteModal()"
        class="flex-1 py-2.5 border border-outline-variant/30 rounded-xl text-sm text-on-surface-variant hover:bg-surface-container transition-all">
        Annuler
      </button>
      <button onclick="saveNote()"
        class="flex-[2] py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:opacity-90 transition-all">
        Enregistrer
      </button>
    </div>
  </div>
</div>

<script>
  const TOTAL = <?= (int)$daysInMonth ?>;
  const CSRF = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';
  const BASE = '<?= APP_URL ?>';
  const counts = {
    av: <?= (int)$countAv ?>,
    unav: <?= (int)$countUnav ?>
  };

  let noteTargetDate = null;

  /* ── Toggle statut (clic gauche) ─────────────────────────── */
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
    redraw(el, next, el.dataset.note || '');
    refreshUI();
    saveDay(date, next, el.dataset.note || '');
  }

  /* ── Redessiner une cellule ──────────────────────────────── */
  function redraw(el, status, note) {
    el.classList.remove('av', 'unav', 'has-note');
    const num = el.querySelector('span:first-child');
    if (num) num.classList.remove('text-green-800', 'text-red-800', 'font-bold');
    const lbl = el.querySelector('.lbl');
    if (lbl) lbl.remove();

    if (status === 'available') {
      el.classList.add('av');
      if (num) num.classList.add('text-green-800', 'font-bold');
      const s = document.createElement('span');
      s.className = 'lbl text-[7px] md:text-[8px] text-green-600 font-bold leading-none';
      s.textContent = '✓';
      el.appendChild(s);
    } else if (status === 'unavailable') {
      el.classList.add('unav');
      if (num) num.classList.add('text-red-800', 'font-bold');
      const s = document.createElement('span');
      s.className = 'lbl text-[7px] md:text-[8px] text-red-600 font-bold leading-none';
      s.textContent = '✗';
      el.appendChild(s);
    }
    if (note) el.classList.add('has-note');
    el.title = note ? 'Note: ' + note : 'Clic = changer statut | Clic droit = note';
  }

  /* ── Compteurs ───────────────────────────────────────────── */
  function refreshUI() {
    document.getElementById('cnt-av').textContent = counts.av;
    document.getElementById('cnt-unav').textContent = counts.unav;
    document.getElementById('cnt-lbl').textContent = counts.av;
    document.getElementById('pct-bar').style.width = Math.round(counts.av / TOTAL * 100) + '%';
  }

  /* ── Sauvegarde AJAX jour ────────────────────────────────── */
  async function saveDay(date, status, note) {
    const fd = new FormData();
    fd.append('date', date);
    fd.append('status', status);
    fd.append('note', note || '');
    fd.append('csrf_token', CSRF);
    try {
      await fetch(BASE + '/dashboard/artisan/calendar', {
        method: 'POST',
        body: fd
      });
    } catch (e) {
      console.error('Erreur save:', e);
    }
  }

  /* ── Modal note ──────────────────────────────────────────── */
  function openNoteModal(date) {
    noteTargetDate = date;
    const modal = document.getElementById('note-modal');
    const title = document.getElementById('note-modal-title');
    const sub = document.getElementById('note-modal-date');
    const inp = document.getElementById('note-input');

    if (date) {
      title.textContent = 'Note du ' + date;
      sub.textContent = 'Cette note sera visible dans votre calendrier';
      const el = document.getElementById('d-' + date);
      inp.value = el ? (el.dataset.note || '') : '';
    } else {
      title.textContent = 'Description générale';
      sub.textContent = 'Visible par les clients (horaires, délais, préférences...)';
      inp.value = document.getElementById('cal-description').value;
    }
    modal.classList.add('open');
    setTimeout(() => inp.focus(), 100);
    return false; // empêche le menu contextuel natif
  }

  function closeNoteModal(e) {
    if (!e || e.target === document.getElementById('note-modal')) {
      document.getElementById('note-modal').classList.remove('open');
      noteTargetDate = null;
    }
  }

  async function saveNote() {
    const inp = document.getElementById('note-input');
    const note = inp.value.trim();

    if (!noteTargetDate) {
      // Note générale du calendrier
      document.getElementById('cal-description').value = note;
      await saveCalDescriptionVal(note);
    } else {
      // Note d'un jour spécifique
      const el = document.getElementById('d-' + noteTargetDate);
      if (el) {
        el.dataset.note = note;
        redraw(el, el.dataset.status, note);
        await saveDay(noteTargetDate, el.dataset.status, note);
      }
    }
    closeNoteModal();
  }

  /* ── Sauvegarde description calendrier ───────────────────── */
  async function saveCalDescription() {
    const val = document.getElementById('cal-description').value;
    await saveCalDescriptionVal(val);
  }

  async function saveCalDescriptionVal(val) {
    const fd = new FormData();
    fd.append('action', 'save_description');
    fd.append('calendar_description', val);
    fd.append('csrf_token', CSRF);
    try {
      await fetch(BASE + '/dashboard/artisan/calendar', {
        method: 'POST',
        body: fd
      });
      const ok = document.getElementById('desc-ok');
      if (ok) {
        ok.classList.remove('hidden');
        setTimeout(() => ok.classList.add('hidden'), 2000);
      }
    } catch (e) {
      console.error(e);
    }
  }

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeNoteModal();
  });
</script>