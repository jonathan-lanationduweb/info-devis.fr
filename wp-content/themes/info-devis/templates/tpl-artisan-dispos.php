<?php
/**
 * Template Name: Espace artisan — Disponibilités
 * Planning hebdomadaire + indisponibilités + réglages de RDV
 * (action idc_dispos_save).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_week     = $idv_fiche ? idc_rdv_week($idv_fiche->ID) : [];
$idv_indispos = $idv_fiche ? idc_rdv_indispos($idv_fiche->ID) : [];
$idv_m        = static fn(string $k, string $def): string => $idv_fiche ? ((string) get_post_meta($idv_fiche->ID, '_idc_' . $k, true) ?: $def) : $def;
?>

<div class="md:ml-72 pt-28 px-8 pb-24 md:pb-12">

  <div class="mb-12 max-w-5xl">
    <h1 class="text-4xl md:text-5xl font-headline italic text-on-surface mb-4">Mes disponibilités</h1>
    <p class="text-on-surface-variant max-w-2xl font-body leading-relaxed">
      Définissez vos créneaux hebdomadaires : les clients réservent directement dans votre agenda, sans double réservation.
    </p>
  </div>

  <?php if (isset($_GET['dispos'])) : ?>
    <div class="mb-8 p-5 border-l-4 <?php echo $_GET['dispos'] === 'ok' ? 'border-primary bg-primary/5' : 'border-red-400 bg-red-50 text-red-700'; ?> rounded-r-xl text-sm">
      <?php echo $_GET['dispos'] === 'ok' ? '✅ Disponibilités enregistrées.' : 'Une erreur est survenue, merci de réessayer.'; ?>
    </div>
  <?php endif; ?>

  <?php if (!$idv_fiche) : ?>
    <div class="p-8 bg-yellow-50 border border-yellow-200 rounded-2xl max-w-xl">
      <p class="text-sm text-yellow-800">Aucune fiche artisan n'est associée à votre compte.</p>
    </div>
  <?php else : ?>

  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="max-w-3xl space-y-10">
    <input type="hidden" name="action" value="idc_dispos_save">
    <?php wp_nonce_field('idc_dispos_save', 'idc_dispos_nonce'); ?>

    <!-- Planning hebdomadaire -->
    <div class="bg-surface-container-low p-10 rounded-2xl space-y-4">
      <h2 class="font-headline text-2xl font-bold flex items-center gap-3 mb-6">
        <span class="material-symbols-outlined text-primary">calendar_month</span> Semaine type
      </h2>
      <?php foreach (IDC_RDV_JOURS as $idv_key => $idv_label) :
          $idv_cfg = $idv_week[$idv_key];
      ?>
        <div class="flex flex-wrap items-center gap-4 py-3 border-b border-outline-variant/10">
          <label class="flex items-center gap-3 w-36 cursor-pointer">
            <input type="checkbox" name="on_<?php echo esc_attr($idv_key); ?>" value="1" class="w-5 h-5 accent-primary" <?php checked(!empty($idv_cfg['on'])); ?>>
            <span class="text-sm font-bold"><?php echo esc_html($idv_label); ?></span>
          </label>
          <div class="flex items-center gap-2 text-sm">
            <span class="text-on-surface-variant">de</span>
            <input type="time" name="start_<?php echo esc_attr($idv_key); ?>" value="<?php echo esc_attr($idv_cfg['start']); ?>"
              class="bg-surface-container border-none focus:ring-1 focus:ring-primary p-2.5 rounded-lg">
            <span class="text-on-surface-variant">à</span>
            <input type="time" name="end_<?php echo esc_attr($idv_key); ?>" value="<?php echo esc_attr($idv_cfg['end']); ?>"
              class="bg-surface-container border-none focus:ring-1 focus:ring-primary p-2.5 rounded-lg">
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Réglages -->
    <div class="bg-surface-container-low p-10 rounded-2xl space-y-6">
      <h2 class="font-headline text-2xl font-bold flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">tune</span> Réglages des rendez-vous
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Durée d'un RDV (min)</label>
          <input type="number" name="duree" min="15" max="480" step="15" value="<?php echo esc_attr($idv_m('duree_rdv_default_min', '90')); ?>"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
        </div>
        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Délai de prévenance (h)</label>
          <input type="number" name="delai" min="0" max="168" value="<?php echo esc_attr($idv_m('delai_prevenance_h', '24')); ?>"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
        </div>
        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">RDV max / jour (0 = illimité)</label>
          <input type="number" name="max_jour" min="0" max="20" value="<?php echo esc_attr($idv_m('max_rdv_jour', '0')); ?>"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
        </div>
      </div>
      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Indisponibilités ponctuelles (dates séparées par des virgules, format AAAA-MM-JJ)</label>
        <input type="text" name="indispos" value="<?php echo esc_attr(implode(', ', $idv_indispos)); ?>" placeholder="2026-08-04, 2026-08-05"
          class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
      </div>
      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Message affiché sur votre calendrier</label>
        <input type="text" name="calendar_description" value="<?php echo esc_attr($idv_m('calendar_description', '')); ?>" placeholder="Ex : interventions sur Lyon et 40 km alentour"
          class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
      </div>
    </div>

    <button type="submit" class="bg-primary text-on-primary px-10 py-4 rounded-xl font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all">
      Enregistrer mes disponibilités
    </button>
  </form>

  <!-- Aperçu des prochains créneaux tels que vus par les clients -->
  <?php $idv_slots = idc_rdv_slots($idv_fiche->ID, 7); ?>
  <div class="mt-16 max-w-3xl">
    <h2 class="font-headline text-2xl font-bold mb-6">Aperçu : vos créneaux des 7 prochains jours</h2>
    <?php if (!$idv_slots) : ?>
      <p class="text-sm text-on-surface-variant">Aucun créneau réservable sur la période (jours désactivés, indisponibilités ou délai de prévenance).</p>
    <?php else : ?>
      <div class="space-y-4">
        <?php foreach ($idv_slots as $idv_date => $idv_heures) : ?>
          <div class="flex flex-wrap items-center gap-3">
            <span class="w-32 text-sm font-bold"><?php echo esc_html(date_i18n('D j M', strtotime($idv_date))); ?></span>
            <?php foreach ($idv_heures as $idv_h) : ?>
              <span class="bg-primary/5 text-primary border border-primary/20 px-3 py-1 rounded-lg text-xs font-bold"><?php echo esc_html($idv_h); ?></span>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php endif; ?>
</div>

<?php get_footer(); ?>
