<?php
/**
 * Template Name: Espace client — Mes Projets
 * Reproduction de views/client/devis.php (filtres par statut, cartes demandes,
 * citation éditoriale).
 */

$idv_user     = idv_require_role('client');
$idv_demandes = idv_client_demandes($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'client', ['user' => $idv_user]);

$idv_filters = ['all' => 'Tous', 'sent' => 'En attente', 'in_progress' => 'En cours', 'completed' => 'Terminé', 'cancelled' => 'Annulé'];
$idv_active_filter = isset($_GET['status']) && isset($idv_filters[$_GET['status']]) ? $_GET['status'] : 'all';

// Regroupement des statuts internes vers les filtres originaux.
$idv_bucket = static function (string $status): string {
    return match ($status) {
        'pending', 'sent'         => 'sent',
        'accepted', 'in_progress' => 'in_progress',
        'completed'               => 'completed',
        'cancelled', 'refused'    => 'cancelled',
        default                   => 'sent',
    };
};

$idv_filtered = $idv_active_filter === 'all'
    ? $idv_demandes
    : array_values(array_filter($idv_demandes, static fn($d) => $idv_bucket((string) get_post_meta($d->ID, '_idc_status', true)) === $idv_active_filter));
?>

<div class="md:ml-72 pt-32 pb-24 md:pb-20 px-8 md:px-16 min-h-screen">

  <!-- Header -->
  <header class="flex flex-col md:flex-row justify-between items-end gap-6 mb-16 border-b border-outline-variant/10 pb-12">
    <div class="max-w-2xl">
      <div class="flex items-center gap-4 mb-4">
        <span class="bg-primary/10 text-primary px-3 py-1 text-[10px] font-bold tracking-[0.2em] uppercase rounded-full">Mes projets</span>
        <span class="h-px w-12 bg-outline-variant/30"></span>
      </div>
      <h1 class="text-5xl md:text-6xl font-headline italic font-bold text-on-surface leading-tight">
        Mes demandes
        <span class="text-outline-variant/40 not-italic font-light">(<?php echo count($idv_demandes); ?>)</span>
      </h1>
      <p class="mt-4 text-on-surface-variant font-body text-lg leading-relaxed max-w-lg">
        Gérez vos projets de rénovation et suivez l'avancement de vos devis.
      </p>
    </div>
    <a href="<?php echo esc_url(home_url('/devis/')); ?>"
      class="flex items-center gap-3 bg-primary text-on-primary px-8 py-4 font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all shadow-xl shadow-primary/10 rounded-lg">
      <span class="material-symbols-outlined text-lg">add</span>
      Nouvelle demande
    </a>
  </header>

  <!-- Filtres -->
  <div class="flex gap-10 mb-12 overflow-x-auto pb-4">
    <?php foreach ($idv_filters as $idv_val => $idv_label) :
        $idv_on = $idv_active_filter === $idv_val;
    ?>
      <a href="<?php echo esc_url(add_query_arg('status', $idv_val)); ?>"
        class="<?php echo $idv_on ? 'text-primary border-b-2 border-primary' : 'text-stone-400 hover:text-primary'; ?> pb-2 text-xs font-bold uppercase tracking-widest transition-all whitespace-nowrap">
        <?php echo esc_html($idv_label); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (!$idv_filtered) : ?>
    <div class="text-center py-24">
      <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">inbox</span>
      <h2 class="font-headline text-3xl mb-4">Aucune demande</h2>
      <p class="text-on-surface-variant mb-8">Vous n'avez pas encore fait de demande de devis.</p>
      <a href="<?php echo esc_url(home_url('/devis/')); ?>" class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all inline-block">
        Faire ma première demande
      </a>
    </div>
  <?php else : ?>

    <!-- Grille des demandes -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-x-12 gap-y-16">
      <?php foreach ($idv_filtered as $idv_d) :
          $idv_status = (string) get_post_meta($idv_d->ID, '_idc_status', true);
          [$idv_sl, $idv_sc] = idv_demande_status($idv_status);
          $idv_cancelled = $idv_bucket($idv_status) === 'cancelled';
      ?>
        <div class="group relative <?php echo $idv_cancelled ? 'bg-stone-50/50 border border-dashed border-stone-200 grayscale' : 'bg-surface-container-low hover:bg-surface-container'; ?> p-10 transition-all duration-500 rounded-xl">
          <?php if (!$idv_cancelled) : ?>
            <div class="absolute -top-4 -left-4 w-24 h-24 bg-primary/5 -z-10 group-hover:scale-110 transition-transform rounded"></div>
          <?php endif; ?>

          <div class="flex justify-between items-start mb-10">
            <div class="flex flex-col gap-1">
              <span class="text-[10px] font-bold text-stone-400 uppercase tracking-tighter font-mono">Réf. <?php echo esc_html(get_post_meta($idv_d->ID, '_idc_reference', true)); ?></span>
              <h3 class="text-3xl font-headline font-bold text-on-surface <?php echo !$idv_cancelled ? 'group-hover:text-primary transition-colors' : 'text-stone-400'; ?>">
                <?php echo esc_html(get_the_title($idv_d)); ?>
              </h3>
            </div>
            <span class="<?php echo esc_attr($idv_sc); ?> px-4 py-1.5 text-[10px] font-bold uppercase tracking-widest rounded-full flex-shrink-0"><?php echo esc_html($idv_sl); ?></span>
          </div>

          <div class="flex gap-8 mb-10 <?php echo $idv_cancelled ? 'opacity-50' : ''; ?>">
            <div class="w-24 h-24 rounded-xl bg-surface-container-high flex items-center justify-center flex-shrink-0">
              <span class="material-symbols-outlined text-3xl text-outline-variant">home_repair_service</span>
            </div>
            <div class="flex flex-col justify-center gap-3">
              <div class="flex items-center gap-3 text-on-surface-variant">
                <span class="material-symbols-outlined text-lg">location_on</span>
                <span class="text-sm"><?php echo esc_html(get_post_meta($idv_d->ID, '_idc_ville', true) ?: 'Ville non précisée'); ?></span>
              </div>
              <div class="flex items-center gap-3 text-on-surface-variant">
                <span class="material-symbols-outlined text-lg">calendar_today</span>
                <span class="text-xs"><?php echo esc_html(get_the_date('d F Y', $idv_d)); ?></span>
              </div>
              <p class="text-sm text-on-surface-variant line-clamp-2"><?php echo esc_html(wp_trim_words($idv_d->post_content, 18)); ?></p>
            </div>
          </div>

          <div class="flex items-center justify-between pt-8 border-t <?php echo $idv_cancelled ? 'border-stone-100' : 'border-outline-variant/20'; ?>">
            <div class="flex flex-col">
              <span class="text-[10px] text-stone-400 uppercase font-bold tracking-widest mb-1">Statut</span>
              <span class="text-sm font-semibold text-on-surface"><?php echo esc_html($idv_sl); ?></span>
            </div>

            <?php if ($idv_bucket($idv_status) === 'completed') : ?>
              <a href="<?php echo esc_url(home_url('/dashboard/client/avis/')); ?>"
                class="bg-primary/5 text-primary px-6 py-3 text-[10px] font-bold uppercase tracking-[0.2em] border border-primary/20 hover:bg-primary/10 transition-colors flex items-center gap-3 rounded-lg">
                Laisser un avis
                <span class="material-symbols-outlined text-sm">grade</span>
              </a>
            <?php elseif ($idv_status === 'accepted') :
                $idv_signed = function_exists('idc_signature_get') && idc_signature_get($idv_d->ID); ?>
              <?php if ($idv_signed) : ?>
                <span class="bg-primary/5 text-primary px-6 py-3 text-[10px] font-bold uppercase tracking-[0.2em] border border-primary/20 rounded-lg flex items-center gap-2">
                  <span class="material-symbols-outlined text-sm">verified</span> Devis signé
                </span>
              <?php else : ?>
                <a href="<?php echo esc_url(add_query_arg('d', $idv_d->ID, home_url('/dashboard/client/signature/'))); ?>"
                  class="bg-primary text-on-primary px-6 py-3 text-[10px] font-bold uppercase tracking-[0.2em] hover:opacity-90 transition-colors flex items-center gap-3 rounded-lg shadow-lg shadow-primary/10">
                  Signer le devis
                  <span class="material-symbols-outlined text-sm">draw</span>
                </a>
              <?php endif; ?>
            <?php elseif ($idv_status === 'in_progress' && function_exists('idc_signature_get') && idc_signature_get($idv_d->ID)) : ?>
              <span class="bg-primary/5 text-primary px-6 py-3 text-[10px] font-bold uppercase tracking-[0.2em] border border-primary/20 rounded-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">verified</span> Devis signé
              </span>
            <?php elseif ($idv_bucket($idv_status) === 'sent') : ?>
              <span class="text-on-surface px-6 py-3 text-[10px] font-bold uppercase tracking-[0.2em] border border-outline-variant/30 rounded-lg text-on-surface-variant">
                En attente d'artisans
              </span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

  <!-- Citation éditoriale -->
  <section class="mt-24">
    <div class="bg-surface-container-highest p-16 relative overflow-hidden rounded-xl">
      <span class="absolute -top-10 -left-10 text-[12rem] font-headline text-on-surface opacity-5 select-none">"</span>
      <div class="relative z-10 max-w-3xl mx-auto text-center">
        <p class="text-2xl md:text-3xl font-headline italic text-on-surface mb-8 leading-relaxed">
          La qualité d'un devis ne se mesure pas seulement au prix, mais à la précision des détails techniques fournis.
        </p>
        <div class="flex flex-col items-center gap-2">
          <span class="h-8 w-px bg-primary mb-2"></span>
          <p class="text-xs text-on-surface-variant italic">L'équipe InfoDevis</p>
        </div>
      </div>
    </div>
  </section>

</div>

<?php get_footer(); ?>
