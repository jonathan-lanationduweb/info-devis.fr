<?php /* views/artisan/documents.php */ ?>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
  }

  .serif {
    font-family: 'Newsreader', serif;
  }
</style>

<?php include __DIR__ . '/_sidebar.php'; ?>

<main class="md:ml-72 min-h-screen p-8 pt-28 bg-background">
  <div class="max-w-6xl mx-auto">

    <!-- ── Header ── -->
    <header class="mb-10">
      <h1 class="serif text-5xl font-bold tracking-tight text-on-surface mb-2">Mes Documents</h1>
      <p class="text-on-surface-variant font-medium max-w-xl">
        Gérez vos justificatifs légaux et attestations pour maintenir votre profil d'artisan certifié.
      </p>
    </header>

    <!-- ── Bannière statut ── -->
    <?php
    $vs = $artisan['verification_status'] ?? 'pending';
    if ($vs === 'validated'):
    ?>
      <div class="mb-10 bg-green-50 text-green-800 p-6 rounded-xl border-l-4 border-green-500 flex items-start gap-4 shadow-sm">
        <span class="material-symbols-outlined text-green-600 mt-0.5">verified</span>
        <div>
          <h3 class="font-bold text-lg mb-1">Compte validé ✅</h3>
          <p class="text-sm opacity-90">Vos documents ont été vérifiés. Vous bénéficiez du badge <strong>"Artisan Vérifié"</strong>.</p>
        </div>
      </div>
    <?php elseif ($vs === 'refused'): ?>
      <div class="mb-10 bg-red-50 text-red-800 p-6 rounded-xl border-l-4 border-red-500 flex items-start gap-4 shadow-sm">
        <span class="material-symbols-outlined text-red-600 mt-0.5">report</span>
        <div>
          <h3 class="font-bold text-lg mb-1">Documents refusés</h3>
          <p class="text-sm opacity-90">Certains documents ont été refusés. Veuillez les soumettre à nouveau.</p>
        </div>
      </div>
    <?php else: ?>
      <div class="mb-10 bg-primary/10 text-on-primary-container p-6 rounded-xl border-l-4 border-primary flex items-start gap-4 shadow-sm">
        <span class="material-symbols-outlined text-primary mt-0.5" style="font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24">pending_actions</span>
        <div>
          <h3 class="font-bold text-lg mb-1">Obtenez le badge "Artisan Vérifié"</h3>
          <p class="text-sm opacity-90 leading-relaxed max-w-2xl">
            Pour commencer à recevoir des leads qualifiés, le dépôt de votre <strong>Extrait Kbis</strong> et de votre <strong>Assurance Décennale</strong> est <strong>obligatoire</strong>.
            La vérification de ces documents est la clé pour débloquer votre accès complet à la plateforme.
          </p>
        </div>
      </div>
    <?php endif; ?>

    <!-- ── Grille principale ── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

      <!-- Colonne gauche : upload + citation -->
      <div class="lg:col-span-7 space-y-8">

        <!-- Formulaire upload -->
        <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant/10">
          <div class="flex items-center justify-between mb-8">
            <h3 class="serif text-2xl font-bold">Ajouter un document</h3>
            <span class="text-[10px] uppercase tracking-widest font-bold bg-surface-container-high px-2 py-1 rounded">Action requise</span>
          </div>

          <form id="upload-form" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <!-- Zone drag & drop -->
            <div id="drop-zone" class="border-2 border-dashed border-outline-variant/30 rounded-xl p-10 flex flex-col items-center justify-center hover:border-primary/50 transition-colors group cursor-pointer bg-surface-container-low/30">
              <div class="w-16 h-16 rounded-full bg-primary/5 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-primary text-3xl">upload_file</span>
              </div>
              <p class="font-bold text-on-surface mb-1">Cliquez ou déposez vos fichiers ici</p>
              <p class="text-xs text-on-surface-variant">PDF, JPG ou PNG (Max. 10 Mo)</p>
              <p id="file-name" class="mt-3 text-primary text-sm font-semibold hidden"></p>
            </div>
            <input type="file" id="file-input" name="document" accept=".pdf,.jpg,.jpeg,.png" class="hidden">

            <!-- Info -->
            <div class="flex items-start gap-3 bg-surface-container-low p-4 rounded-xl text-sm text-on-surface-variant">
              <span class="material-symbols-outlined text-base flex-shrink-0 mt-0.5">info</span>
              <span>Assurez-vous que l'Extrait Kbis a moins de 3 mois et que l'attestation d'assurance couvre l'année en cours.</span>
            </div>

            <!-- Type de document -->
            <div>
              <label class="font-label font-bold uppercase tracking-wider text-[11px] text-on-surface-variant block mb-2">Type de document *</label>
              <select name="type" required class="w-full bg-surface-container border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary/20 text-on-surface">
                <option value="">-- Sélectionnez --</option>
                <option value="kbis">Extrait Kbis (moins de 3 mois)</option>
                <option value="assurance">Assurance Décennale</option>
                <option value="carte_identite">Pièce d'identité (Recto/Verso)</option>
                <option value="rib">Relevé d'Identité Bancaire (RIB)</option>
                <option value="autre">Autre document</option>
              </select>
            </div>

            <button type="submit"
              class="w-full bg-primary text-on-primary py-4 rounded-xl font-bold text-sm tracking-wide shadow-lg shadow-primary/10 hover:opacity-90 transition-opacity">
              Soumettre le document
            </button>

            <div id="upload-msg" class="hidden text-sm font-semibold text-center"></div>
          </form>
        </div>

        <!-- Citation -->
        <div class="bg-surface-container-highest p-8 rounded-xl relative overflow-hidden">
          <span class="serif text-8xl absolute -bottom-4 -right-2 opacity-5 pointer-events-none">"</span>
          <p class="serif italic text-lg text-tertiary relative z-10 leading-relaxed">
            "La mise à jour régulière de vos certifications professionnelles renforce la confiance des clients et augmente de 40% vos chances d'être sélectionné."
          </p>
          <p class="mt-4 font-label font-bold text-xs uppercase tracking-widest text-primary">— Guide Artisan InfoDevis</p>
        </div>
      </div>

      <!-- Colonne droite : liste docs + support -->
      <div class="lg:col-span-5 space-y-6">

        <!-- Liste des documents -->
        <div class="bg-surface-container p-8 rounded-xl border border-outline-variant/10">
          <h3 class="serif text-2xl font-bold mb-6">Statut des documents</h3>

          <?php
          $docsByType = [];
          foreach ($documents ?? [] as $doc) {
            $docsByType[$doc['type']] = $doc;
          }

          $essentiels = [
            'kbis'           => ['Extrait Kbis',       'grid_on'],
            'assurance'      => ['Assurance Décennale', 'shield'],
            'carte_identite' => ['Pièce d\'Identité',  'badge'],
          ];
          $additionnels = [
            'rib'   => ['RIB',            'account_balance'],
            'autre' => ['Autre document', 'description'],
          ];

          $statusCfg = [
            'validated' => ['Validé',     'bg-primary-container text-on-primary-container',     'verified_user', 'text-primary'],
            'pending'   => ['En attente', 'bg-secondary-container text-on-secondary-container', 'history',       'text-on-surface-variant'],
            'refused'   => ['Refusé',     'bg-error-container text-on-error-container',         'report',        'text-error'],
          ];

          function renderDocRow(string $type, string $label, string $icon, array $docsByType, array $statusCfg): void
          {
            $doc    = $docsByType[$type] ?? null;
            $status = $doc['status'] ?? null;
            [$sBadge, $sBadgeCls, $sIcon, $sIconCls] = $status
              ? $statusCfg[$status] ?? ['Inconnu', 'bg-stone-100 text-stone-500', 'help', 'text-stone-400']
              : ['Manquant', 'bg-red-100 text-red-700', 'cancel', 'text-red-400'];
          ?>
            <div class="bg-surface-container-lowest p-4 rounded-xl flex items-center justify-between border border-outline-variant/5 <?= $status === 'refused' ? 'border-error/10' : '' ?>">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg <?= $status === 'validated' ? 'bg-primary/10' : ($status === 'refused' ? 'bg-error-container/20' : 'bg-surface-container-high') ?> flex items-center justify-center flex-shrink-0">
                  <span class="material-symbols-outlined <?= $sIconCls ?>"><?= $sIcon ?></span>
                </div>
                <div>
                  <p class="font-bold text-sm"><?= htmlspecialchars($label) ?></p>
                  <?php if ($doc && !empty($doc['uploaded_at'])): ?>
                    <p class="text-[10px] text-on-surface-variant"><?= date('d M Y', strtotime($doc['uploaded_at'])) ?></p>
                  <?php elseif ($status === 'refused'): ?>
                    <p class="text-[10px] text-error"><?= htmlspecialchars($doc['note'] ?? 'Document refusé') ?></p>
                  <?php else: ?>
                    <p class="text-[10px] text-on-surface-variant">Non fourni</p>
                  <?php endif; ?>
                </div>
              </div>
              <span class="text-[10px] font-bold px-2 py-1 rounded <?= $sBadgeCls ?> uppercase tracking-tight whitespace-nowrap"><?= $sBadge ?></span>
            </div>
          <?php } ?>

          <p class="font-label text-[10px] uppercase tracking-[0.2em] font-bold text-outline-variant mb-3">Documents essentiels</p>
          <div class="space-y-3 mb-5">
            <?php foreach ($essentiels as $type => [$label, $icon]):
              renderDocRow($type, $label, $icon, $docsByType, $statusCfg);
            endforeach; ?>
          </div>

          <p class="font-label text-[10px] uppercase tracking-[0.2em] font-bold text-outline-variant mb-3">Documents additionnels</p>
          <div class="space-y-3">
            <?php foreach ($additionnels as $type => [$label, $icon]):
              renderDocRow($type, $label, $icon, $docsByType, $statusCfg);
            endforeach; ?>
          </div>

          <button class="mt-6 w-full border border-outline-variant text-on-surface-variant py-3 rounded-xl font-bold text-[11px] uppercase tracking-widest hover:bg-surface-container-high transition-colors">
            Télécharger l'archive (.zip)
          </button>
        </div>

        <!-- Support -->
        <div class="p-6 rounded-xl bg-primary text-on-primary flex items-center gap-4">
          <div class="w-12 h-12 rounded-full bg-on-primary/10 flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-2xl">support_agent</span>
          </div>
          <div>
            <p class="text-xs font-bold opacity-80 uppercase tracking-tighter mb-1">Besoin d'aide ?</p>
            <p class="text-sm font-medium">Contactez votre conseiller dédié pour valider vos documents.</p>
          </div>
        </div>

      </div>
    </div>

  </div>
</main>

<script>
  // ── Ouvrir le sélecteur de fichier en cliquant sur la zone ──
  var dropZone = document.getElementById('drop-zone');
  var fileInput = document.getElementById('file-input');
  var fileNameEl = document.getElementById('file-name');

  if (dropZone) {
    dropZone.addEventListener('click', function() {
      fileInput.click();
    });
  }

  if (fileInput) {
    fileInput.addEventListener('change', function() {
      if (this.files[0]) {
        fileNameEl.textContent = this.files[0].name;
        fileNameEl.classList.remove('hidden');
      }
    });
  }

  // ── Soumission du formulaire ──
  document.getElementById('upload-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    var btn = this.querySelector('button[type=submit]');
    var msg = document.getElementById('upload-msg');
    btn.disabled = true;
    btn.textContent = 'Envoi en cours...';
    try {
      var res = await fetch('<?= APP_URL ?>/dashboard/artisan/documents', {
        method: 'POST',
        body: new FormData(this)
      });
      var data = await res.json();
      msg.className = data.success ?
        'text-sm font-semibold text-green-700 text-center p-3 bg-green-50 rounded-xl' :
        'text-sm font-semibold text-red-700 text-center p-3 bg-red-50 rounded-xl';
      msg.textContent = data.message || data.error || 'Erreur inconnue';
      msg.classList.remove('hidden');
      if (data.success) setTimeout(function() {
        location.reload();
      }, 1500);
    } catch (err) {
      msg.className = 'text-sm font-semibold text-red-700 text-center p-3 bg-red-50 rounded-xl';
      msg.textContent = 'Erreur réseau. Réessayez.';
      msg.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.textContent = 'Soumettre le document';
  });

  // ── Drag & drop ──
  if (dropZone) {
    dropZone.addEventListener('dragover', function(e) {
      e.preventDefault();
      dropZone.classList.add('border-primary/60');
    });
    dropZone.addEventListener('dragleave', function() {
      dropZone.classList.remove('border-primary/60');
    });
    dropZone.addEventListener('drop', function(e) {
      e.preventDefault();
      dropZone.classList.remove('border-primary/60');
      var file = e.dataTransfer.files[0];
      if (file) {
        fileInput.files = e.dataTransfer.files;
        fileNameEl.textContent = file.name;
        fileNameEl.classList.remove('hidden');
      }
    });
  }
</script>