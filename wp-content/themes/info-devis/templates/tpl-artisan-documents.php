<?php
/**
 * Template Name: Espace artisan — Documents
 * Reproduction de views/artisan/documents.php : upload réel (glisser-déposer,
 * PDF/JPG/PNG 10 Mo), liste des documents avec statut + lien « Voir » (accès
 * contrôlé). La validation est faite par l'admin. Handlers : idc_artisan_document_upload.
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_docs  = ($idv_fiche && function_exists('idc_artisan_documents')) ? idc_artisan_documents($idv_fiche->ID) : [];
$idv_vs    = $idv_fiche ? (string) get_post_meta($idv_fiche->ID, '_idc_verification_status', true) : '';
$idv_nonce = wp_create_nonce('idc_artisan_document');
$idv_ajax  = admin_url('admin-ajax.php');

$idv_types = [
    'kbis'           => ['Extrait Kbis', 'grid_on', true],
    'assurance'      => ['Assurance Décennale', 'shield', true],
    'carte_identite' => ["Pièce d'identité", 'badge', true],
    'rib'            => ['RIB', 'account_balance', false],
    'autre'          => ['Autre document', 'description', false],
];
$idv_status_cfg = [
    'validated' => ['Validé', 'bg-primary-container text-on-primary-container', 'verified_user', 'text-primary'],
    'pending'   => ['En attente', 'bg-secondary-container text-on-secondary-container', 'history', 'text-on-surface-variant'],
    'refused'   => ['Refusé', 'bg-red-100 text-red-700', 'report', 'text-error'],
];
?>
<style>.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }</style>

<main class="md:ml-72 min-h-screen p-8 pt-28 bg-background">
  <div class="max-w-6xl mx-auto">

    <header class="mb-10">
      <h1 class="font-headline text-5xl font-bold tracking-tight text-on-surface mb-2">Mes Documents</h1>
      <p class="text-on-surface-variant font-medium max-w-xl">Gérez vos justificatifs légaux et attestations pour maintenir votre profil d'artisan certifié.</p>
    </header>

    <?php if ($idv_vs === 'validated') : ?>
      <div class="mb-10 bg-green-50 text-green-800 p-6 rounded-xl border-l-4 border-green-500 flex items-start gap-4 shadow-sm">
        <span class="material-symbols-outlined text-green-600 mt-0.5">verified</span>
        <div><h3 class="font-bold text-lg mb-1">Compte validé ✅</h3><p class="text-sm opacity-90">Vos documents ont été vérifiés. Vous bénéficiez du badge <strong>« Artisan Vérifié »</strong>.</p></div>
      </div>
    <?php elseif ($idv_vs === 'refused') : ?>
      <div class="mb-10 bg-red-50 text-red-800 p-6 rounded-xl border-l-4 border-red-500 flex items-start gap-4 shadow-sm">
        <span class="material-symbols-outlined text-red-600 mt-0.5">report</span>
        <div><h3 class="font-bold text-lg mb-1">Documents refusés</h3><p class="text-sm opacity-90">Certains documents ont été refusés. Veuillez les soumettre à nouveau.</p></div>
      </div>
    <?php else : ?>
      <div class="mb-10 bg-primary/10 text-on-primary-container p-6 rounded-xl border-l-4 border-primary flex items-start gap-4 shadow-sm">
        <span class="material-symbols-outlined text-primary mt-0.5">pending_actions</span>
        <div><h3 class="font-bold text-lg mb-1">Obtenez le badge « Artisan Vérifié »</h3><p class="text-sm opacity-90 leading-relaxed max-w-2xl">Le dépôt de votre <strong>Extrait Kbis</strong> et de votre <strong>Assurance Décennale</strong> est <strong>obligatoire</strong> pour débloquer votre accès complet à la plateforme.</p></div>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

      <!-- Upload -->
      <div class="lg:col-span-7 space-y-8">
        <div class="bg-surface-container-lowest p-8 rounded-xl shadow-sm border border-outline-variant/10">
          <div class="flex items-center justify-between mb-8">
            <h3 class="font-headline text-2xl font-bold">Ajouter un document</h3>
            <span class="text-[10px] uppercase tracking-widest font-bold bg-surface-container-high px-2 py-1 rounded">Action requise</span>
          </div>

          <form id="doc-form" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="action" value="idc_artisan_document_upload">
            <input type="hidden" name="idc_doc_nonce" value="<?php echo esc_attr($idv_nonce); ?>">

            <div id="doc-drop" class="border-2 border-dashed border-outline-variant/30 rounded-xl p-10 flex flex-col items-center justify-center hover:border-primary/50 transition-colors group cursor-pointer bg-surface-container-low/30">
              <div class="w-16 h-16 rounded-full bg-primary/5 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-primary text-3xl">upload_file</span>
              </div>
              <p class="font-bold text-on-surface mb-1">Cliquez ou déposez votre fichier ici</p>
              <p class="text-xs text-on-surface-variant">PDF, JPG ou PNG (max 10 Mo)</p>
              <p id="doc-filename" class="mt-3 text-primary text-sm font-semibold hidden"></p>
            </div>
            <input type="file" id="doc-input" name="document" accept=".pdf,.jpg,.jpeg,.png" class="hidden" required>

            <div class="flex items-start gap-3 bg-surface-container-low p-4 rounded-xl text-sm text-on-surface-variant">
              <span class="material-symbols-outlined text-base flex-shrink-0 mt-0.5">info</span>
              <span>Assurez-vous que l'Extrait Kbis a moins de 3 mois et que l'attestation d'assurance couvre l'année en cours.</span>
            </div>

            <div>
              <label class="font-label font-bold uppercase tracking-wider text-[11px] text-on-surface-variant block mb-2">Type de document *</label>
              <select name="type" required class="w-full bg-surface-container border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary/20 text-on-surface">
                <option value="">-- Sélectionnez --</option>
                <?php foreach ($idv_types as $idv_k => [$idv_l]) : ?>
                  <option value="<?php echo esc_attr($idv_k); ?>"><?php echo esc_html($idv_l); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <button type="submit" class="w-full bg-primary text-on-primary py-4 rounded-xl font-bold text-sm tracking-wide shadow-lg shadow-primary/10 hover:opacity-90 transition-opacity">
              Soumettre le document
            </button>
            <div id="doc-msg" class="hidden text-sm font-semibold text-center"></div>
          </form>
        </div>
      </div>

      <!-- Liste -->
      <div class="lg:col-span-5 space-y-6">
        <div class="bg-surface-container p-8 rounded-xl border border-outline-variant/10">
          <h3 class="font-headline text-2xl font-bold mb-6">Statut des documents</h3>
          <div class="space-y-3">
            <?php foreach ($idv_types as $idv_type => [$idv_label, $idv_icon, $idv_essential]) :
                $idv_doc = $idv_docs[$idv_type] ?? null;
                $idv_status = $idv_doc->status ?? null;
                [$idv_sbadge, $idv_sbcls, $idv_sicon, $idv_sicls] = $idv_status
                    ? ($idv_status_cfg[$idv_status] ?? ['Inconnu', 'bg-stone-100 text-stone-500', 'help', 'text-stone-400'])
                    : ['Manquant', 'bg-red-100 text-red-700', 'cancel', 'text-red-400'];
            ?>
              <div class="bg-surface-container-lowest p-4 rounded-xl flex items-center justify-between gap-3 border border-outline-variant/5">
                <div class="flex items-center gap-4 min-w-0">
                  <div class="w-10 h-10 rounded-lg <?php echo $idv_status === 'validated' ? 'bg-primary/10' : 'bg-surface-container-high'; ?> flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined <?php echo esc_attr($idv_sicls); ?>"><?php echo esc_html($idv_sicon); ?></span>
                  </div>
                  <div class="min-w-0">
                    <p class="font-bold text-sm truncate"><?php echo esc_html($idv_label); ?><?php echo $idv_essential ? ' *' : ''; ?></p>
                    <?php if ($idv_doc) : ?>
                      <p class="text-[10px] text-on-surface-variant"><?php echo esc_html(date_i18n('d M Y', strtotime($idv_doc->uploaded_at))); ?></p>
                    <?php else : ?>
                      <p class="text-[10px] text-on-surface-variant">Non fourni</p>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                  <?php if ($idv_doc) : ?>
                    <a href="<?php echo esc_url($idv_ajax . '?action=idc_document_view&id=' . (int) $idv_doc->id); ?>" target="_blank" rel="noopener"
                       class="text-primary hover:bg-primary/10 rounded-lg p-1.5 transition-colors" title="Voir le document">
                      <span class="material-symbols-outlined text-[20px]">visibility</span>
                    </a>
                    <?php if ($idv_status !== 'validated') : ?>
                      <button type="button" data-doc-delete="<?php echo (int) $idv_doc->id; ?>"
                              class="text-red-500 hover:bg-red-50 rounded-lg p-1.5 transition-colors" title="Supprimer ce document">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                      </button>
                    <?php endif; ?>
                  <?php endif; ?>
                  <span class="text-[10px] font-bold px-2 py-1 rounded <?php echo esc_attr($idv_sbcls); ?> uppercase tracking-tight whitespace-nowrap"><?php echo esc_html($idv_sbadge); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

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
(function () {
  const AJAX = '<?php echo esc_js($idv_ajax); ?>';
  const drop = document.getElementById('doc-drop');
  const input = document.getElementById('doc-input');
  const fname = document.getElementById('doc-filename');
  const form = document.getElementById('doc-form');
  const msg = document.getElementById('doc-msg');

  drop.addEventListener('click', () => input.click());
  input.addEventListener('change', () => { if (input.files[0]) { fname.textContent = input.files[0].name; fname.classList.remove('hidden'); } });
  ['dragover', 'dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.toggle('border-primary/60', ev === 'dragover'); }));
  drop.addEventListener('drop', e => { if (e.dataTransfer.files[0]) { input.files = e.dataTransfer.files; fname.textContent = e.dataTransfer.files[0].name; fname.classList.remove('hidden'); } });

  form.addEventListener('submit', async e => {
    e.preventDefault();
    if (!input.files[0]) { input.click(); return; }
    const btn = form.querySelector('button[type=submit]');
    btn.disabled = true; btn.textContent = 'Envoi en cours...';
    try {
      const res = await fetch(AJAX, { method: 'POST', body: new FormData(form), credentials: 'same-origin' });
      const data = await res.json();
      msg.className = 'text-sm font-semibold text-center p-3 rounded-xl ' + (data.success ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50');
      msg.textContent = data.message || data.error || 'Erreur inconnue';
      msg.classList.remove('hidden');
      if (data.success) setTimeout(() => location.reload(), 1500);
    } catch (err) {
      msg.className = 'text-sm font-semibold text-center p-3 rounded-xl text-red-700 bg-red-50';
      msg.textContent = 'Erreur réseau. Réessayez.'; msg.classList.remove('hidden');
    }
    btn.disabled = false; btn.textContent = 'Soumettre le document';
  });

  // Suppression d'un document (tant qu'il n'est pas validé).
  const NONCE = '<?php echo esc_js($idv_nonce); ?>';
  document.addEventListener('click', async e => {
    const del = e.target.closest('[data-doc-delete]');
    if (!del) return;
    if (!confirm('Supprimer ce document ? Cette action est définitive.')) return;
    del.disabled = true;
    const fd = new FormData();
    fd.append('action', 'idc_artisan_document_delete');
    fd.append('idc_doc_nonce', NONCE);
    fd.append('id', del.getAttribute('data-doc-delete'));
    try {
      const res = await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
      const data = await res.json();
      if (data.success) { location.reload(); }
      else { alert(data.error || 'Erreur'); del.disabled = false; }
    } catch (err) { alert('Erreur réseau. Réessayez.'); del.disabled = false; }
  });
})();
</script>

<?php get_footer(); ?>
