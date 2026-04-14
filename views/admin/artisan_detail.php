<?php /* views/admin/artisan_detail.php */ ?>
<?php include BASE_PATH . '/views/admin/_sidebar.php'; ?>

<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
  }
</style>

<main class="lg:ml-72 min-h-screen bg-[#f3f4f3]">
  <div class="pt-28 px-6 pb-16 max-w-6xl mx-auto">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-6">
      <a href="<?= APP_URL ?>/admin/artisans" class="hover:text-primary flex items-center gap-1 transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span> Gestion artisans
      </a>
      <span class="text-outline-variant">/</span>
      <span class="font-semibold text-on-surface"><?= Security::e($artisan['company_name']) ?></span>
    </div>

    <!-- ── Header artisan ─────────────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-outline-variant/20 p-6 mb-6 shadow-sm">
      <div class="flex flex-col md:flex-row md:items-start justify-between gap-5">

        <div class="flex items-center gap-4">
          <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center text-xl font-bold text-primary flex-shrink-0">
            <?= strtoupper(substr($artisan['company_name'] ?? 'A', 0, 1)) ?>
          </div>
          <div>
            <?php
            $vs = $artisan['verification_status'] ?? 'pending';
            $vsCfg = [
              'pending'   => ['⏳ En attente', 'bg-yellow-100 text-yellow-800 border-yellow-200'],
              'validated' => ['✅ Validé',     'bg-green-100 text-green-800 border-green-200'],
              'refused'   => ['❌ Refusé',     'bg-red-100 text-red-800 border-red-200'],
            ];
            [$vslabel, $vscls] = $vsCfg[$vs] ?? ['?', 'bg-stone-100 text-stone-600 border-stone-200'];
            ?>
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <h1 class="font-headline text-2xl font-bold"><?= Security::e($artisan['company_name']) ?></h1>
              <span class="text-xs font-bold px-3 py-1 rounded-full border <?= $vscls ?>"><?= $vslabel ?></span>
              <?php if ($artisan['siret_verified'] ?? 0): ?>
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-blue-100 text-blue-800 border border-blue-200">SIRET ✓</span>
              <?php endif; ?>
            </div>
            <p class="text-sm text-on-surface-variant">
              <?= Security::e(($artisan['first_name'] ?? '') . ' ' . ($artisan['last_name'] ?? '')) ?>
              · <a href="mailto:<?= Security::e($artisan['email']) ?>" class="hover:text-primary"><?= Security::e($artisan['email']) ?></a>
            </p>
          </div>
        </div>

        <!-- Boutons selon le statut actuel -->
        <div class="flex flex-wrap gap-2">
          <?php if ($vs === 'pending'): ?>
            <button onclick="doArtisan('refuse')"
              class="flex items-center gap-1.5 border border-red-300 text-red-600 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-red-50 transition-all">
              <span class="material-symbols-outlined text-sm">cancel</span> Refuser
            </button>
            <button onclick="doArtisan('validate')"
              class="flex items-center gap-1.5 bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:opacity-90 transition-all">
              <span class="material-symbols-outlined text-sm">check_circle</span> Valider le dossier
            </button>
          <?php elseif ($vs === 'validated'): ?>
            <button onclick="doArtisan('cancel')"
              class="flex items-center gap-1.5 border border-orange-300 text-orange-600 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-orange-50 transition-all">
              <span class="material-symbols-outlined text-sm">undo</span> Annuler la vérification
            </button>
          <?php elseif ($vs === 'refused'): ?>
            <button onclick="doArtisan('validate')"
              class="flex items-center gap-1.5 bg-primary text-on-primary px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:opacity-90 transition-all">
              <span class="material-symbols-outlined text-sm">check_circle</span> Valider quand même
            </button>
            <button onclick="doArtisan('cancel')"
              class="flex items-center gap-1.5 border border-orange-300 text-orange-600 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-orange-50 transition-all">
              <span class="material-symbols-outlined text-sm">undo</span> Remettre en attente
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- ── Infos + Note admin ─────────────────────────────────────────── -->
      <div class="space-y-5">

        <div class="bg-white rounded-xl border border-outline-variant/20 p-5 shadow-sm">
          <h2 class="font-headline font-bold mb-4 text-sm uppercase tracking-widest text-on-surface-variant">Informations</h2>
          <dl class="space-y-2.5 text-sm">
            <?php foreach (
              [
                ['SIRET',    Security::e($artisan['siret'] ?? '—')],
                ['Ville',    Security::e(trim(($artisan['ville'] ?? '') . ' ' . ($artisan['code_postal'] ?? '')))],
                ['Téléphone', Security::e($artisan['phone'] ?? '—')],
                ['Plan',     '<span class="capitalize font-semibold text-primary">' . Security::e($artisan['plan'] ?? 'gratuit') . '</span>'],
                ['Rayon',    (int)($artisan['radius_km'] ?? 30) . ' km'],
                ['Inscrit',  !empty($artisan['created_at']) ? date('d/m/Y', strtotime($artisan['created_at'])) : '—'],
              ] as [$label, $val]
            ): ?>
              <div class="flex justify-between gap-2">
                <dt class="text-on-surface-variant flex-shrink-0"><?= $label ?></dt>
                <dd class="font-semibold text-right"><?= $val ?></dd>
              </div>
            <?php endforeach; ?>
          </dl>
        </div>

        <div class="bg-white rounded-xl border border-outline-variant/20 p-5 shadow-sm">
          <h2 class="font-headline font-bold mb-4 text-sm uppercase tracking-widest text-on-surface-variant">Activité</h2>
          <dl class="space-y-2.5 text-sm">
            <div class="flex justify-between">
              <dt class="text-on-surface-variant">Leads acceptés</dt>
              <dd class="font-bold text-primary"><?= (int)($artisan['leads_accepted'] ?? 0) ?></dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-on-surface-variant">Leads refusés</dt>
              <dd class="font-bold text-red-600"><?= (int)($artisan['leads_refused'] ?? 0) ?></dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-on-surface-variant">Note moyenne</dt>
              <dd class="font-bold">⭐ <?= number_format((float)($artisan['rating_avg'] ?? 0), 1) ?>/5</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-on-surface-variant">Avis</dt>
              <dd class="font-bold"><?= (int)($artisan['rating_count'] ?? 0) ?></dd>
            </div>
          </dl>
        </div>

        <!-- Note admin -->
        <div class="bg-white rounded-xl border border-outline-variant/20 p-5 shadow-sm">
          <h2 class="font-headline font-bold mb-3 text-sm uppercase tracking-widest text-on-surface-variant">Note interne</h2>
          <textarea id="admin-note" rows="4"
            class="w-full bg-surface-container-low border border-outline-variant/20 rounded-xl px-3 py-2.5 text-sm focus:ring-1 focus:ring-primary resize-none placeholder:text-outline-variant"
            placeholder="Note visible uniquement par l'admin..."><?= Security::e($artisan['admin_note'] ?? '') ?></textarea>
          <button onclick="saveNote()"
            class="mt-2 w-full bg-surface-container-high text-on-surface px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-surface-container-highest transition-all">
            Sauvegarder la note
          </button>
          <p id="note-ok" class="text-xs text-green-600 text-center mt-1 hidden">✅ Note sauvegardée</p>
        </div>

      </div>

      <!-- ── Documents ──────────────────────────────────────────────────── -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-outline-variant/20 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between p-6 border-b border-outline-variant/10">
          <h2 class="font-headline text-xl font-bold">Documents soumis</h2>
          <span class="text-xs font-label uppercase tracking-widest text-on-surface-variant bg-surface-container px-3 py-1 rounded-full">
            <?= count($documents) ?> fichier<?= count($documents) > 1 ? 's' : '' ?>
          </span>
        </div>

        <?php if (empty($documents)): ?>
          <div class="text-center py-16">
            <span class="material-symbols-outlined text-5xl text-outline-variant block mb-3">folder_open</span>
            <p class="text-on-surface-variant">Aucun document soumis pour le moment</p>
          </div>
        <?php else: ?>

          <div class="divide-y divide-outline-variant/10">
            <?php
            $typeLabels = [
              'kbis'           => ['Extrait Kbis',        'grid_on',         'text-blue-600',  'bg-blue-50'],
              'assurance'      => ['Assurance Décennale',  'shield',          'text-green-600', 'bg-green-50'],
              'carte_identite' => ["Pièce d'identité",    'badge',           'text-purple-600', 'bg-purple-50'],
              'rib'            => ['RIB',                  'account_balance', 'text-orange-600', 'bg-orange-50'],
              'autre'          => ['Autre document',       'description',     'text-stone-600', 'bg-stone-50'],
            ];
            $docStatusCfg = [
              'pending'   => ['⏳ En attente', 'bg-yellow-100 text-yellow-700 border-yellow-200'],
              'validated' => ['✅ Validé',     'bg-green-100  text-green-700  border-green-200'],
              'refused'   => ['❌ Refusé',     'bg-red-100    text-red-700    border-red-200'],
            ];

            foreach ($documents as $doc):
              [$dlabel, $dicon, $diconColor, $dbg] = $typeLabels[$doc['type'] ?? 'autre'] ?? $typeLabels['autre'];
              [$dsBadge, $dsCls] = $docStatusCfg[$doc['status'] ?? 'pending'] ?? ['?', 'bg-stone-100 text-stone-500 border-stone-200'];
              $ext      = strtolower(pathinfo($doc['original_name'] ?? $doc['filename'] ?? '', PATHINFO_EXTENSION));
              $isImage  = in_array($ext, ['jpg', 'jpeg', 'png']);
              $isPdf    = $ext === 'pdf';
              $docUrl   = APP_URL . '/admin/document/' . (int)$doc['id'];
              $docStatus = $doc['status'] ?? 'pending';
            ?>
              <div class="p-5" id="doc-row-<?= $doc['id'] ?>">

                <!-- En-tête du document -->
                <div class="flex items-start gap-4 mb-3">
                  <div class="w-11 h-11 rounded-xl <?= $dbg ?> flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined <?= $diconColor ?>"><?= $dicon ?></span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 mb-1">
                      <p class="font-semibold text-sm"><?= $dlabel ?></p>
                      <span class="text-[10px] font-bold px-3 py-1 rounded-full border <?= $dsCls ?> whitespace-nowrap"><?= $dsBadge ?></span>
                    </div>
                    <p class="text-xs text-on-surface-variant truncate"><?= Security::e($doc['original_name'] ?? $doc['filename'] ?? '') ?></p>
                    <p class="text-xs text-outline-variant">
                      Soumis le <?= !empty($doc['uploaded_at']) ? date('d/m/Y à H:i', strtotime($doc['uploaded_at'])) : '—' ?>
                      <?php if (!empty($doc['validated_at'])): ?>
                        · Traité le <?= date('d/m/Y', strtotime($doc['validated_at'])) ?>
                      <?php endif; ?>
                    </p>
                  </div>
                </div>

                <!-- Motif refus -->
                <?php if ($docStatus === 'refused' && !empty($doc['note'])): ?>
                  <div class="mb-3 text-xs text-red-700 bg-red-50 border border-red-200 px-3 py-2 rounded-lg">
                    <strong>Motif :</strong> <?= Security::e($doc['note']) ?>
                  </div>
                <?php endif; ?>

                <!-- Prévisualisation image inline (cliquable) -->
                <?php if ($isImage && !empty($doc['filename'] ?? '')): ?>
                  <div class="mb-3 cursor-pointer group" onclick="openPreview('<?= $docUrl ?>', 'image', '<?= Security::e($doc['original_name'] ?? $doc['filename'] ?? $dlabel) ?>')">
                    <div class="rounded-xl overflow-hidden border border-outline-variant/15 bg-stone-50 max-h-44 flex items-center justify-center relative">
                      <img src="<?= $docUrl ?>" alt="<?= $dlabel ?>"
                        class="max-w-full max-h-44 object-contain group-hover:opacity-90 transition-opacity">
                      <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/20 rounded-xl">
                        <div class="bg-white/90 rounded-full p-2">
                          <span class="material-symbols-outlined text-primary">zoom_in</span>
                        </div>
                      </div>
                    </div>
                    <p class="text-[10px] text-on-surface-variant text-center mt-1">Cliquez pour agrandir</p>
                  </div>
                <?php elseif ($isPdf && !empty($doc['filename'] ?? '')): ?>
                  <a href="<?= $docUrl ?>" target="_blank"
                    class="mb-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-3 hover:bg-red-100 transition-colors no-underline">
                    <span class="material-symbols-outlined text-red-600 text-2xl">picture_as_pdf</span>
                    <div>
                      <p class="text-sm font-semibold text-red-800"><?= Security::e($doc['original_name'] ?? $doc['filename'] ?? 'document.pdf') ?></p>
                      <p class="text-xs text-red-600">Cliquez pour ouvrir dans un nouvel onglet</p>
                    </div>
                    <span class="material-symbols-outlined text-red-400 ml-auto">open_in_new</span>
                  </a>
                <?php endif; ?>

                <!-- Boutons d'action -->
                <div class="flex flex-wrap gap-2">

                  <?php if (!empty($doc['filename'] ?? '')): ?>
                    <!-- Télécharger -->
                    <a href="<?= $docUrl ?>" target="_blank" download="<?= Security::e($doc['original_name'] ?? $doc['filename'] ?? 'document') ?>"
                      class="flex items-center gap-1.5 text-xs font-bold text-on-surface-variant border border-outline-variant/30 px-3 py-1.5 rounded-lg hover:border-primary hover:text-primary transition-all">
                      <span class="material-symbols-outlined text-sm">download</span> Télécharger
                    </a>
                  <?php endif; ?>

                  <!-- Valider ce doc -->
                  <?php if ($docStatus !== 'validated'): ?>
                    <button onclick="validateDoc(<?= $doc['id'] ?>, 'validated')"
                      class="flex items-center gap-1.5 text-xs font-bold text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-lg hover:bg-green-100 transition-all">
                      <span class="material-symbols-outlined text-sm">check_circle</span> Valider
                    </button>
                  <?php endif; ?>

                  <!-- Refuser ce doc -->
                  <?php if ($docStatus !== 'refused'): ?>
                    <button onclick="validateDoc(<?= $doc['id'] ?>, 'refused')"
                      class="flex items-center gap-1.5 text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-3 py-1.5 rounded-lg hover:bg-red-100 transition-all">
                      <span class="material-symbols-outlined text-sm">cancel</span> Refuser
                    </button>
                  <?php endif; ?>

                  <!-- Remettre en attente -->
                  <?php if ($docStatus !== 'pending'): ?>
                    <button onclick="validateDoc(<?= $doc['id'] ?>, 'pending')"
                      class="flex items-center gap-1.5 text-xs font-bold text-yellow-700 bg-yellow-50 border border-yellow-200 px-3 py-1.5 rounded-lg hover:bg-yellow-100 transition-all">
                      <span class="material-symbols-outlined text-sm">undo</span> En attente
                    </button>
                  <?php endif; ?>

                </div>
              </div>
            <?php endforeach; ?>
          </div>

        <?php endif; ?>
      </div>

    </div>
  </div>
</main>

<!-- ── Modal prévisualisation plein écran ─────────────────────────────── -->
<div id="preview-overlay"
  style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.9);"
  onclick="closePreview()">
  <div style="position:absolute;top:0;left:0;right:0;height:56px;background:#111;display:flex;align-items:center;justify-content:between;padding:0 16px;gap:12px;">
    <span id="preview-filename" style="color:#fff;font-size:13px;font-weight:600;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
    <div style="display:flex;gap:8px;flex-shrink:0;">
      <a id="preview-dl-btn" href="#" target="_blank" download
        style="color:#a9fdce;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;text-decoration:none;display:flex;align-items:center;gap:4px;"
        onclick="event.stopPropagation()">
        ↓ Télécharger
      </a>
      <button onclick="closePreview()" style="color:#fff;background:none;border:none;cursor:pointer;padding:4px;font-size:22px;line-height:1;">✕</button>
    </div>
  </div>
  <div id="preview-body"
    style="position:absolute;top:56px;bottom:0;left:0;right:0;display:flex;align-items:center;justify-content:center;padding:16px;"
    onclick="event.stopPropagation()">
    <!-- contenu injecté par JS -->
  </div>
</div>

<script>
  const ARTISAN_ID = <?= (int)$artisan['id'] ?>;
  const CSRF = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';
  const ADMIN_BASE = '<?= APP_URL ?>';

  // ── Prévisualisation ──────────────────────────────────────────────────────
  function openPreview(url, type, filename) {
    // PDF : ouvrir dans un nouvel onglet (CSP bloque les iframes localhost)
    if (type === 'pdf') {
      window.open(url, '_blank');
      return;
    }

    // Image : modal inline
    document.getElementById('preview-filename').textContent = filename || 'Document';
    document.getElementById('preview-dl-btn').href = url;
    document.getElementById('preview-dl-btn').download = filename || 'document';

    const body = document.getElementById('preview-body');
    body.innerHTML = '<img src="' + url + '" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:8px;">';

    const overlay = document.getElementById('preview-overlay');
    overlay.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }

  function closePreview() {
    document.getElementById('preview-overlay').style.display = 'none';
    document.getElementById('preview-body').innerHTML = '';
    document.body.style.overflow = '';
  }

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closePreview();
  });

  // ── Validation document individuel ───────────────────────────────────────
  async function validateDoc(docId, status) {
    let note = '';
    if (status === 'refused') {
      note = prompt('Motif du refus (optionnel) :');
      if (note === null) return; // annulé
    }

    const fd = new FormData();
    fd.append('document_id', docId);
    fd.append('status', status);
    fd.append('note', note || '');
    fd.append('csrf_token', CSRF);

    try {
      const res = await fetch(ADMIN_BASE + '/admin/document/validate', {
        method: 'POST',
        body: fd
      });
      const text = await res.text();

      // Tenter de parser en JSON — si échec, afficher le texte brut (erreur PHP)
      let data;
      try {
        data = JSON.parse(text);
      } catch (jsonErr) {
        // Le serveur a renvoyé du HTML (erreur PHP ou redirection)
        const tmp = document.createElement('div');
        tmp.innerHTML = text;
        const errMsg = tmp.querySelector('.error-message, .xdebug-error, h1, p')?.textContent?.trim() ||
          text.substring(0, 300);
        alert('Erreur serveur :\n' + errMsg);
        console.error('Réponse non-JSON :', text);
        return;
      }

      if (data.success) {
        location.reload();
      } else {
        alert('Erreur : ' + (data.error || 'inconnue'));
      }
    } catch (e) {
      alert('Erreur réseau : ' + e.message);
    }
  }

  // ── Valider / Refuser / Annuler artisan ───────────────────────────────────
  async function doArtisan(action) {
    let note = '';
    if (action === 'refuse') {
      note = prompt('Motif du refus :');
      if (!note && note !== '') return; // annulé
    } else if (action === 'cancel') {
      if (!confirm('Remettre cet artisan en statut "En attente" ?')) return;
    }

    const fd = new FormData();
    fd.append('artisan_id', ARTISAN_ID);
    fd.append('action', action);
    fd.append('note', note || '');
    fd.append('csrf_token', CSRF);

    try {
      const res = await fetch(ADMIN_BASE + '/admin/artisan/validate', {
        method: 'POST',
        body: fd
      });
      const data = await res.json();
      if (data.success) {
        const msg = {
          validate: '✅ Artisan validé !',
          refuse: '❌ Artisan refusé.',
          cancel: '↩ Remis en attente.',
        } [action] || 'OK';
        alert(msg);
        location.reload();
      } else {
        alert('Erreur : ' + (data.error || 'inconnue'));
      }
    } catch (e) {
      alert('Erreur réseau : ' + e.message);
    }
  }

  // ── Note admin ────────────────────────────────────────────────────────────
  async function saveNote() {
    const note = document.getElementById('admin-note').value;
    const fd = new FormData();
    fd.append('artisan_id', ARTISAN_ID);
    fd.append('note', note);
    fd.append('csrf_token', CSRF);

    try {
      const res = await fetch(ADMIN_BASE + '/admin/artisan/note', {
        method: 'POST',
        body: fd
      });
      const data = await res.json();
      if (data.success) {
        const ok = document.getElementById('note-ok');
        ok.classList.remove('hidden');
        setTimeout(() => ok.classList.add('hidden'), 2500);
      }
    } catch (e) {
      console.error('Erreur save note:', e);
    }
  }
</script>