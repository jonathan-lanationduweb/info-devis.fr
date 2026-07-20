<?php
/**
 * Template Name: Espace artisan — Vérification
 * Reproduction de views/artisan/verification.php : badge actuel + strip 3 niveaux
 * (Référencé / Vérifié / Vérifié Pro) + 6 documents typés à uploader (statut par
 * document). Réutilise la table idc_documents et l'AJAX idc_artisan_document_upload.
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_docs = ($idv_fiche && function_exists('idc_artisan_documents')) ? idc_artisan_documents($idv_fiche->ID) : [];
$idv_plan = $idv_fiche ? (get_post_meta($idv_fiche->ID, '_idc_plan', true) ?: 'gratuit') : 'gratuit';
$idv_current = in_array($idv_plan, ['gold', 'illimite', 'pro'], true) ? 'verified_pro' : ($idv_plan === 'silver' ? 'verified' : 'referenced');

$idv_doc_types = [
    'kbis'          => ['business', 'Extrait KBIS', 'Document récent (- 3 mois)', 'verified'],
    'rc_pro'        => ['shield', 'Assurance RC Pro', 'Attestation en cours de validité', 'verified'],
    'identite'      => ['badge', "Pièce d'identité", 'CNI ou passeport du dirigeant', 'verified'],
    'decennale'     => ['verified_user', 'Assurance Décennale', 'Pour les travaux concernés', 'verified_pro'],
    'qualification' => ['school', 'Qualifications professionnelles', 'CAP, BP, BTS…', 'verified_pro'],
    'certification' => ['workspace_premium', 'Certifications', 'RGE, Qualibat, Qualifelec…', 'verified_pro'],
];

$idv_status_badge = static function (?string $s): array {
    return match ($s) {
        'validated' => ['bg-emerald-100 text-emerald-800', 'task_alt', 'Validé'],
        'pending'   => ['bg-amber-100 text-amber-800', 'pending', 'En attente'],
        'rejected'  => ['bg-red-100 text-red-800', 'cancel', 'Refusé'],
        default     => ['bg-stone-100 text-stone-600', 'cloud_upload', 'À uploader'],
    };
};

$idv_levels = [
    'referenced'   => ['Référencé', 'Gratuit · 0€/mois', 'fa-clipboard-check'],
    'verified'     => ['Vérifié', 'Silver · 10€/mois', 'fa-circle-check'],
    'verified_pro' => ['Vérifié Pro', 'Gold · 14€/mois', 'fa-medal'],
];
$idv_level_keys = array_keys($idv_levels);
$idv_current_idx = array_search($idv_current, $idv_level_keys, true);
?>
<style>
  .doc-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:20px; transition:border-color .2s, box-shadow .2s; }
  .doc-card--validated { border-color:#10b981; background:linear-gradient(135deg,#f0fdf4 0%,#fff 100%); }
  .doc-card--pending { border-color:#f59e0b; }
  .doc-card--rejected { border-color:#ef4444; }
  .doc-card:hover { box-shadow:0 8px 20px rgba(0,0,0,.05); }
  .upload-mini { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:8px; background:#207752; color:#fff; font-size:12px; font-weight:700; cursor:pointer; transition:filter .15s; border:none; }
  .upload-mini:hover { filter:brightness(1.1); }
  .level-strip { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
  @media (max-width:600px){ .level-strip { grid-template-columns:1fr; } }
  .level-step { background:#f3f4f6; padding:16px 12px; border-radius:10px; text-align:center; transition:all .2s; display:flex; flex-direction:column; align-items:center; gap:6px; }
  .level-step--current { background:#207752; color:#fff; transform:scale(1.02); }
  .level-step--passed { background:#d1fae5; color:#047857; }
  .level-step__title { display:inline-flex; align-items:center; gap:6px; font-size:14px; font-weight:600; font-family:'Newsreader',serif; line-height:1; }
  .level-step__title i { font-size:14px; }
</style>

<main class="md:ml-72 min-h-screen p-8 pt-28 bg-background">
  <div class="max-w-5xl mx-auto">

    <header class="mb-10">
      <h1 class="text-5xl font-bold tracking-tight text-on-surface mb-2" style="font-family:'Newsreader',serif;">
        Ma <span style="font-family:'Newsreader',serif;font-style:italic;">vérification</span>
      </h1>
      <p class="text-on-surface-variant max-w-2xl">Faites vérifier vos documents officiels pour obtenir un badge supérieur et gagner la confiance des clients.</p>
    </header>

    <section class="bg-white p-8 rounded-2xl border border-outline-variant/15 mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
        <div>
          <p class="text-xs uppercase tracking-widest text-on-surface-variant font-bold mb-2">Votre badge actuel</p>
          <?php
          $idv_badge_map = [
              'referenced'   => ['fa-clipboard-check', 'Référencé', 'idv-badge--referenced'],
              'verified'     => ['fa-circle-check', 'Vérifié', 'idv-badge--verified'],
              'verified_pro' => ['fa-medal', 'Vérifié Pro', 'idv-badge--verified-pro'],
          ];
          [$idv_bi, $idv_bl, $idv_bc] = $idv_badge_map[$idv_current];
          ?>
          <span class="idv-badge <?php echo esc_attr($idv_bc); ?>"><i class="fa-solid <?php echo esc_attr($idv_bi); ?> idv-badge__icon"></i><span class="idv-badge__label"><?php echo esc_html($idv_bl); ?></span></span>
        </div>
        <a href="<?php echo esc_url(home_url('/nos-niveaux-de-confiance/')); ?>" class="text-sm font-bold text-primary hover:underline whitespace-nowrap inline-flex items-center gap-1">Voir les 3 niveaux <i class="fa-solid fa-arrow-right"></i></a>
      </div>

      <div class="level-strip">
        <?php foreach ($idv_levels as $k => [$lbl, $plan, $icon]) :
            $idx = array_search($k, $idv_level_keys, true);
            $cls = $idx < $idv_current_idx ? 'level-step--passed' : ($idx === $idv_current_idx ? 'level-step--current' : '');
        ?>
          <div class="level-step <?php echo $cls; ?>">
            <p class="text-[10px] uppercase tracking-widest font-bold opacity-80">Niveau <?php echo $idx + 1; ?></p>
            <p class="level-step__title"><i class="fa-solid <?php echo esc_attr($icon); ?>"></i><span><?php echo esc_html($lbl); ?></span></p>
            <p class="text-[10px] opacity-70"><?php echo esc_html($plan); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="mb-8">
      <h2 class="text-2xl font-headline mb-2">Documents à fournir</h2>
      <p class="text-sm text-on-surface-variant mb-6">Uploadez chaque document. Notre équipe valide sous 48h. Plus vous fournissez, plus votre badge monte en niveau.</p>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($idv_doc_types as $type => [$icon, $label, $desc, $req]) :
            $doc = $idv_docs[$type] ?? null;
            $status = $doc->status ?? 'missing';
            [$pillCls, $pillIcon, $pillLbl] = $idv_status_badge($status);
            $cardCls = in_array($status, ['validated', 'pending', 'rejected'], true) ? 'doc-card--' . $status : '';
        ?>
          <div class="doc-card <?php echo $cardCls; ?>">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary flex-shrink-0">
                <span class="material-symbols-outlined"><?php echo esc_html($icon); ?></span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-1">
                  <h3 class="font-bold font-headline"><?php echo esc_html($label); ?></h3>
                  <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider px-2 py-1 rounded-full <?php echo esc_attr($pillCls); ?>">
                    <span class="material-symbols-outlined" style="font-size:14px;"><?php echo esc_html($pillIcon); ?></span><?php echo esc_html($pillLbl); ?>
                  </span>
                </div>
                <p class="text-xs text-on-surface-variant mb-2"><?php echo esc_html($desc); ?></p>
                <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mb-3">Requis pour : <strong><?php echo $req === 'verified' ? 'Vérifié' : 'Vérifié Pro'; ?></strong></p>

                <?php if ($doc) : ?>
                  <ul class="space-y-1 mb-3 text-xs">
                    <li class="flex items-center gap-2 text-on-surface-variant">
                      <span class="material-symbols-outlined" style="font-size:14px;color:#9CA3AF;">attach_file</span>
                      <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=idc_document_view&id=' . (int) $doc->id)); ?>" target="_blank" rel="noopener" class="truncate hover:text-primary underline"><?php echo esc_html(basename(get_attached_file((int) $doc->attachment_id)) ?: 'document'); ?></a>
                      <span class="ml-auto text-[10px]"><?php echo esc_html(wp_date('d/m/Y', strtotime($doc->uploaded_at))); ?></span>
                    </li>
                  </ul>
                <?php endif; ?>

                <form class="doc-upload-form" data-doc-type="<?php echo esc_attr($type); ?>" enctype="multipart/form-data">
                  <input type="hidden" name="type" value="<?php echo esc_attr($type); ?>">
                  <input type="hidden" name="idc_doc_nonce" value="<?php echo esc_attr(wp_create_nonce('idc_artisan_document')); ?>">
                  <label class="upload-mini">
                    <span class="material-symbols-outlined" style="font-size:14px;">upload</span>
                    <?php echo $doc ? 'Remplacer' : 'Uploader'; ?>
                    <input type="file" name="document" accept="application/pdf,image/jpeg,image/png" class="hidden" required>
                  </label>
                  <span class="upload-msg ml-2 text-xs"></span>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="bg-primary/5 p-6 rounded-2xl border border-primary/10">
      <h3 class="font-headline text-lg font-bold mb-3 text-primary">💡 Comment monter de niveau ?</h3>
      <ol class="text-sm text-on-surface-variant space-y-2 list-decimal list-inside">
        <li>Choisissez le plan correspondant à votre objectif sur <a href="<?php echo esc_url(home_url('/dashboard/artisan/abonnement/')); ?>" class="text-primary hover:underline font-semibold">votre abonnement</a></li>
        <li>Uploadez les documents requis pour ce niveau</li>
        <li>Notre équipe valide sous 48h ouvrées (vous recevrez un email)</li>
        <li>Votre badge est mis à jour automatiquement dès validation</li>
      </ol>
    </section>

  </div>
</main>

<script>
(function () {
  const AJAX = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
  document.querySelectorAll('.doc-upload-form').forEach(function (form) {
    const fileInput = form.querySelector('input[type="file"]');
    const msg = form.querySelector('.upload-msg');
    fileInput.addEventListener('change', async function () {
      if (!fileInput.files[0]) return;
      if (fileInput.files[0].size > 10 * 1024 * 1024) { msg.textContent = '⚠ Max 10 Mo'; msg.style.color = '#dc2626'; fileInput.value = ''; return; }
      msg.textContent = 'Upload…'; msg.style.color = '#5b605f';
      const fd = new FormData(form);
      fd.append('action', 'idc_artisan_document_upload');
      try {
        const res = await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json();
        if (data.success) { msg.textContent = '✓ Envoyé — en attente de validation'; msg.style.color = '#047857'; setTimeout(function () { location.reload(); }, 800); }
        else { msg.textContent = '⚠ ' + (data.error || 'Erreur'); msg.style.color = '#dc2626'; }
      } catch (e) { msg.textContent = '⚠ Erreur réseau'; msg.style.color = '#dc2626'; }
    });
  });
})();
</script>

<?php get_footer(); ?>
