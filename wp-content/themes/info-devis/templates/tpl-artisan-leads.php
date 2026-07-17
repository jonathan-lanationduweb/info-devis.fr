<?php
/**
 * Template Name: Espace artisan — Mes opportunités
 * Reproduction de views/artisan/leads.php : filtres, grande carte bento pour
 * le premier lead + petites cartes, accepter/refuser (AJAX JSON), bloc
 * performance du mois.
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);
$idv_leads = idv_artisan_leads($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_urgent  = array_values(array_filter($idv_leads, static fn($l) => in_array(get_post_meta($l['demande']->ID, '_idc_urgency', true), ['urgent', 'tres_urgent'], true)));
$idv_pending = array_values(array_filter($idv_leads, static fn($l) => $l['status'] === 'pending'));

$idv_f = $_GET['f'] ?? '';
$idv_filtered = $idv_f === 'urgent' ? $idv_urgent : ($idv_f === 'pending' ? $idv_pending : $idv_leads);

$idv_total    = count($idv_leads);
$idv_accepted = count(array_filter($idv_leads, static fn($l) => $l['status'] === 'accepted'));
$idv_status_fr = ['accepted' => 'Acceptée', 'refused' => 'Refusée'];
?>

<div class="md:ml-72 pt-28 px-8 pb-24 md:pb-12">

  <div class="mb-12 max-w-5xl">
    <h1 class="text-4xl md:text-5xl font-headline italic text-on-surface mb-4">Mes opportunités</h1>
    <p class="text-on-surface-variant max-w-2xl font-body leading-relaxed">
      Gérez vos demandes entrantes. Chaque lead est qualifié par nos soins pour garantir la pertinence de votre futur chantier.
    </p>
  </div>

  <!-- Filtres -->
  <div class="flex items-center gap-4 mb-8 overflow-x-auto pb-2">
    <span class="text-xs font-label font-bold uppercase tracking-widest text-outline flex-shrink-0">Filtrer par:</span>
    <a href="<?php echo esc_url(remove_query_arg('f')); ?>" class="<?php echo $idv_f === '' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest'; ?> px-6 py-2 rounded-full text-xs font-label font-bold uppercase tracking-widest transition-colors whitespace-nowrap">
      Tous (<?php echo count($idv_leads); ?>)
    </a>
    <a href="<?php echo esc_url(add_query_arg('f', 'urgent')); ?>" class="<?php echo $idv_f === 'urgent' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest'; ?> px-6 py-2 rounded-full text-xs font-label font-bold uppercase tracking-widest transition-colors whitespace-nowrap">
      Urgent (<?php echo count($idv_urgent); ?>)
    </a>
    <a href="<?php echo esc_url(add_query_arg('f', 'pending')); ?>" class="<?php echo $idv_f === 'pending' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest'; ?> px-6 py-2 rounded-full text-xs font-label font-bold uppercase tracking-widest transition-colors whitespace-nowrap">
      En attente (<?php echo count($idv_pending); ?>)
    </a>
  </div>

  <?php if (!$idv_filtered) : ?>
    <div class="text-center py-24">
      <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">format_list_bulleted</span>
      <h2 class="font-headline text-3xl mb-4">Aucun lead pour le moment</h2>
      <p class="text-on-surface-variant mb-8">Les leads apparaissent selon votre zone et vos catégories</p>
      <a href="<?php echo esc_url(home_url('/dashboard/artisan/profile/')); ?>" class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 inline-block">
        Compléter mon profil
      </a>
    </div>
  <?php else : ?>

    <!-- Grille bento leads -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <?php $idv_first = true;
      foreach ($idv_filtered as $idv_lead) :
          $idv_d       = $idv_lead['demande'];
          $idv_stat    = $idv_lead['status'];
          $idv_ref     = get_post_meta($idv_d->ID, '_idc_reference', true);
          $idv_ville   = get_post_meta($idv_d->ID, '_idc_ville', true);
          $idv_urgentl = in_array(get_post_meta($idv_d->ID, '_idc_urgency', true), ['urgent', 'tres_urgent'], true);
          $idv_pend    = $idv_stat === 'pending';
      ?>

        <?php if ($idv_first) : /* Grande carte pour le premier lead */ ?>
          <div class="lg:col-span-8 bg-surface-container-lowest p-8 rounded-xl relative overflow-hidden group shadow-sm border border-outline-variant/10" id="lead-<?php echo (int) $idv_d->ID; ?>">
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                  <span class="font-mono text-[10px] tracking-widest text-outline uppercase"><?php echo esc_html($idv_ref); ?></span>
                  <?php if ($idv_urgentl) : ?>
                    <span class="bg-error/10 text-error px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tighter flex items-center gap-1">
                      <span class="material-symbols-outlined text-[12px]" style="font-variation-settings:'FILL' 1">warning</span> Urgent
                    </span>
                  <?php endif; ?>
                </div>
                <h3 class="text-3xl font-headline font-medium text-on-surface mb-2"><?php echo esc_html(get_the_title($idv_d)); ?></h3>
                <p class="text-on-surface-variant font-body line-clamp-2 max-w-xl"><?php echo esc_html(wp_trim_words($idv_d->post_content, 30)); ?></p>
              </div>
              <div class="flex flex-col items-end">
                <span class="text-xs font-label font-bold uppercase tracking-widest text-outline mb-1">Localisation</span>
                <span class="text-xl font-headline italic font-bold text-primary"><?php echo esc_html($idv_ville); ?></span>
              </div>
            </div>
            <?php if ($idv_pend) : ?>
              <div class="flex flex-wrap items-center gap-4 mt-auto">
                <button onclick="respondLead(<?php echo (int) $idv_d->ID; ?>, 'accepted')"
                  class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-sm hover:opacity-90 transition-opacity flex items-center gap-2">
                  Accepter cette demande <span class="material-symbols-outlined">arrow_forward</span>
                </button>
                <button onclick="respondLead(<?php echo (int) $idv_d->ID; ?>, 'refused')"
                  class="border border-outline-variant/30 text-outline px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-sm hover:bg-surface-container transition-colors">
                  Refuser
                </button>
                <div class="ml-auto hidden sm:flex items-center gap-4 text-outline-variant text-sm">
                  <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">location_on</span> <?php echo esc_html($idv_ville); ?></div>
                  <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">calendar_today</span> <?php echo esc_html(get_the_date('d/m', $idv_d)); ?></div>
                </div>
              </div>
            <?php else : ?>
              <div class="flex items-center gap-3">
                <span class="<?php echo $idv_stat === 'accepted' ? 'text-primary' : 'text-outline-variant'; ?> font-label text-xs uppercase tracking-widest font-bold">
                  <?php echo esc_html($idv_status_fr[$idv_stat] ?? $idv_stat); ?>
                </span>
              </div>
            <?php endif; ?>
          </div>
          <?php $idv_first = false; ?>

        <?php else : /* Petites cartes pour les suivants */ ?>
          <div class="lg:col-span-4 bg-surface-container-low p-6 rounded-xl border border-outline-variant/10 flex flex-col" id="lead-<?php echo (int) $idv_d->ID; ?>">
            <div class="flex items-center justify-between mb-4">
              <span class="font-mono text-[10px] tracking-widest text-outline uppercase"><?php echo esc_html($idv_ref); ?></span>
              <?php if ($idv_urgentl) : ?>
                <span class="bg-error/10 text-error px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tighter">Urgent</span>
              <?php else : ?>
                <span class="text-[10px] font-label font-bold uppercase tracking-widest text-on-surface-variant/60">Standard</span>
              <?php endif; ?>
            </div>
            <h3 class="text-xl font-headline font-medium text-on-surface mb-3"><?php echo esc_html(get_the_title($idv_d)); ?></h3>
            <p class="text-on-surface-variant text-sm font-body line-clamp-2 mb-6"><?php echo esc_html(wp_trim_words($idv_d->post_content, 16)); ?></p>
            <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-4">
              <span class="material-symbols-outlined text-sm">location_on</span>
              <?php echo esc_html($idv_ville); ?>
            </div>
            <?php if ($idv_pend) : ?>
              <div class="mt-auto space-y-3">
                <button onclick="respondLead(<?php echo (int) $idv_d->ID; ?>, 'accepted')"
                  class="w-full bg-primary text-on-primary py-3 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-opacity">
                  Accepter
                </button>
                <button onclick="respondLead(<?php echo (int) $idv_d->ID; ?>, 'refused')"
                  class="w-full border border-outline-variant/30 text-outline py-3 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:bg-surface-container transition-colors">
                  Refuser
                </button>
              </div>
            <?php else : ?>
              <span class="mt-auto text-xs font-label uppercase tracking-widest font-bold <?php echo $idv_stat === 'accepted' ? 'text-primary' : 'text-outline-variant'; ?>">
                <?php echo esc_html($idv_status_fr[$idv_stat] ?? $idv_stat); ?>
              </span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <!-- Stats performance -->
    <section class="mt-20 border-t border-outline-variant/20 pt-16">
      <div class="bg-surface-container-high rounded-2xl p-12 flex flex-col md:flex-row items-center gap-12">
        <div class="flex-1">
          <span class="material-symbols-outlined text-4xl text-primary mb-4 block" style="font-variation-settings:'FILL' 1">analytics</span>
          <h2 class="text-3xl font-headline italic text-on-surface mb-4">Performance ce mois-ci</h2>
          <p class="text-on-surface-variant font-body leading-relaxed max-w-md">
            Restez réactif sur les leads urgents pour améliorer votre taux de conversion.
          </p>
        </div>
        <div class="grid grid-cols-2 gap-8 md:border-l border-outline-variant/30 md:pl-12">
          <div>
            <span class="block text-4xl font-headline font-bold text-primary">
              <?php echo $idv_total > 0 ? round($idv_accepted / $idv_total * 100) : 0; ?>%
            </span>
            <span class="text-[10px] font-label font-bold uppercase tracking-widest text-outline">Taux d'acceptation</span>
          </div>
          <div>
            <span class="block text-4xl font-headline font-bold text-primary"><?php echo $idv_total; ?></span>
            <span class="text-[10px] font-label font-bold uppercase tracking-widest text-outline">Opportunités totales</span>
          </div>
        </div>
      </div>
    </section>

  <?php endif; ?>
</div>

<script>
  async function respondLead(leadId, status) {
    if (!confirm(status === 'accepted' ? 'Accepter cette demande de devis ?' : 'Refuser cette demande ?')) return;
    const form = new FormData();
    form.append('action', 'idc_lead_respond');
    form.append('lead_id', leadId);
    form.append('status', status);
    form.append('idc_lead_nonce', '<?php echo esc_js(wp_create_nonce('idc_lead_respond')); ?>');
    const res = await fetch('<?php echo esc_url(admin_url('admin-post.php')); ?>', {
      method: 'POST',
      body: form,
      credentials: 'same-origin'
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.error || 'Erreur');
  }
</script>

<?php get_footer(); ?>
