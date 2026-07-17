<?php
/**
 * Template Name: Espace artisan — Documents
 * État des 6 documents de vérification (statuts migrés de l'ancienne appli).
 * Le dépôt de fichiers en ligne arrive avec le module vérification (V2) —
 * en attendant, envoi par email (réel, pas un bouton factice).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_docs = [
    'kbis'           => ['Extrait Kbis', 'description'],
    'rc_pro'         => ['Assurance RC Pro', 'shield'],
    'identite'       => ['Pièce d\'identité', 'badge'],
    'decennale'      => ['Garantie décennale', 'foundation'],
    'qualifications' => ['Qualifications', 'school'],
    'certifications' => ['Certifications', 'workspace_premium'],
];
$idv_statuts = [
    'missing'  => ['Non fourni', 'bg-stone-200 text-stone-600'],
    'pending'  => ['En vérification', 'bg-yellow-50 text-yellow-700 border border-yellow-200'],
    'verified' => ['Vérifié ✓', 'bg-emerald-100 text-emerald-800'],
    'rejected' => ['Refusé — à renvoyer', 'bg-red-50 text-red-700 border border-red-200'],
];
?>

<div class="md:ml-72 pt-28 px-8 pb-24 md:pb-12">

  <div class="mb-12 max-w-5xl">
    <h1 class="text-4xl md:text-5xl font-headline italic text-on-surface mb-4">Documents</h1>
    <p class="text-on-surface-variant max-w-2xl font-body leading-relaxed">
      Vos documents de vérification déterminent votre niveau de confiance (badges Vérifié / Vérifié Pro) et rassurent vos clients.
    </p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl mb-12">
    <?php foreach ($idv_docs as $idv_key => [$idv_label, $idv_icon]) :
        $idv_s = $idv_fiche ? ((string) get_post_meta($idv_fiche->ID, '_idc_doc_' . $idv_key . '_status', true) ?: 'missing') : 'missing';
        [$idv_sl, $idv_sc] = $idv_statuts[$idv_s] ?? $idv_statuts['missing'];
    ?>
      <div class="bg-surface-container-low p-6 rounded-xl flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-surface-container-high flex items-center justify-center flex-shrink-0">
          <span class="material-symbols-outlined text-2xl text-primary"><?php echo esc_html($idv_icon); ?></span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-bold text-sm"><?php echo esc_html($idv_label); ?></p>
          <span class="<?php echo esc_attr($idv_sc); ?> inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest"><?php echo esc_html($idv_sl); ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="max-w-3xl p-8 bg-surface-container-lowest border border-outline-variant/10 rounded-2xl">
    <h2 class="font-headline text-xl font-bold mb-3 flex items-center gap-2">
      <span class="material-symbols-outlined text-primary">upload_file</span> Transmettre un document
    </h2>
    <p class="text-sm text-on-surface-variant mb-4">
      Le dépôt de fichiers directement depuis cet espace arrive prochainement. En attendant, envoyez vos documents
      (PDF ou photo lisible) à notre équipe de vérification — ils seront traités sous 48&nbsp;h ouvrées :
    </p>
    <a href="mailto:<?php echo esc_attr(idv_contact('email')); ?>?subject=Documents de vérification — <?php echo esc_attr($idv_fiche ? get_the_title($idv_fiche) : ''); ?>"
      class="inline-flex items-center gap-2 bg-primary text-on-primary px-8 py-3.5 rounded-xl font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all">
      <span class="material-symbols-outlined text-base">mail</span> Envoyer mes documents
    </a>
  </div>
</div>

<?php get_footer(); ?>
