<?php
/**
 * Template Name: Espace client — Tableau de bord
 * Reproduction de views/client/dashboard.php (stats, dernières demandes,
 * encart calendriers, accès rapides).
 */

$idv_user     = idv_require_role('client');
$idv_demandes = idv_client_demandes($idv_user);

get_header();

$idv_total    = count($idv_demandes);
$idv_encours  = 0;
$idv_termines = 0;
foreach ($idv_demandes as $idv_d) {
    $idv_s = get_post_meta($idv_d->ID, '_idc_status', true);
    if (in_array($idv_s, ['sent', 'accepted', 'in_progress'], true)) {
        $idv_encours++;
    } elseif ($idv_s === 'completed') {
        $idv_termines++;
    }
}
$idv_prenom = explode(' ', trim($idv_user->display_name ?: 'Client'))[0];

get_template_part('template-parts/sidebar', 'client', ['user' => $idv_user]);
?>

<div class="md:ml-72 pt-20 md:pt-32 px-4 md:px-12 pb-24 md:pb-20">
  <header class="mb-8 md:mb-12">
    <h1 class="font-headline text-3xl md:text-5xl font-medium tracking-tight italic mb-2">
      Bienvenue, <?php echo esc_html($idv_prenom); ?>
    </h1>
    <p class="text-on-surface-variant text-sm md:text-base opacity-80">
      Aperçu de vos demandes et des artisans disponibles près de chez vous.
    </p>
  </header>

  <!-- Stats -->
  <section class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
    <div class="bg-surface-container-lowest p-5 md:p-8 rounded-2xl border border-outline-variant/10 shadow-sm hover:shadow-md transition-all">
      <span class="material-symbols-outlined text-primary mb-3 block text-2xl md:text-[32px]">description</span>
      <p class="text-2xl md:text-3xl font-headline italic font-bold"><?php echo esc_html(str_pad((string) $idv_total, 2, '0', STR_PAD_LEFT)); ?></p>
      <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">Total demandes</p>
    </div>
    <div class="bg-surface-container-lowest p-5 md:p-8 rounded-2xl border border-outline-variant/10 shadow-sm hover:shadow-md transition-all">
      <span class="material-symbols-outlined text-tertiary mb-3 block text-2xl md:text-[32px]">pending_actions</span>
      <p class="text-2xl md:text-3xl font-headline italic font-bold"><?php echo esc_html(str_pad((string) $idv_encours, 2, '0', STR_PAD_LEFT)); ?></p>
      <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">En cours</p>
    </div>
    <div class="bg-surface-container-lowest p-5 md:p-8 rounded-2xl border border-outline-variant/10 shadow-sm hover:shadow-md transition-all">
      <span class="material-symbols-outlined text-secondary mb-3 block text-2xl md:text-[32px]">task_alt</span>
      <p class="text-2xl md:text-3xl font-headline italic font-bold"><?php echo esc_html(str_pad((string) $idv_termines, 2, '0', STR_PAD_LEFT)); ?></p>
      <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">Terminées</p>
    </div>
    <div class="bg-primary-container p-5 md:p-8 rounded-2xl border border-primary/10 shadow-sm relative overflow-hidden">
      <div class="relative z-10">
        <span class="material-symbols-outlined text-on-primary-container mb-3 block text-2xl md:text-[32px]">star_rate</span>
        <p class="text-2xl md:text-3xl font-headline italic font-bold text-on-primary-container"><?php
          $idv_nb_avis = count(get_posts(['post_type' => 'avis', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_idc_client_user_id', 'meta_value' => $idv_user->ID]));
          echo esc_html(str_pad((string) $idv_nb_avis, 2, '0', STR_PAD_LEFT));
        ?></p>
        <p class="text-[10px] uppercase tracking-widest text-on-primary-container mt-1 font-bold">Avis déposés</p>
      </div>
    </div>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Dernières demandes -->
    <div class="lg:col-span-2 space-y-6">
      <div class="flex justify-between items-baseline">
        <h2 class="font-headline text-xl md:text-2xl font-bold">Dernières Demandes</h2>
        <a href="<?php echo esc_url(home_url('/dashboard/client/devis/')); ?>" class="text-xs font-bold uppercase tracking-widest text-primary border-b border-primary/30 hover:border-primary transition-colors">Tout voir</a>
      </div>

      <?php if (!$idv_demandes) : ?>
        <div class="p-8 rounded-2xl bg-surface-container-low/50 text-center">
          <span class="material-symbols-outlined text-4xl text-outline-variant mb-4 block">inbox</span>
          <p class="text-on-surface-variant mb-4 text-sm">Aucune demande pour le moment</p>
          <a href="<?php echo esc_url(home_url('/devis/')); ?>" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label text-xs uppercase tracking-widest font-bold hover:opacity-90 inline-block">
            Faire ma première demande
          </a>
        </div>
      <?php else : ?>
        <?php foreach (array_slice($idv_demandes, 0, 5) as $idv_d) :
            $idv_status = get_post_meta($idv_d->ID, '_idc_status', true);
            [$idv_sl, $idv_sc] = idv_demande_status($idv_status);
        ?>
          <div class="flex items-start gap-4 p-4 md:p-5 rounded-2xl bg-surface-container-low/50 hover:bg-surface-container-lowest transition-all border border-transparent hover:border-outline-variant/10">
            <div class="w-12 h-12 rounded-xl bg-surface-container-high flex items-center justify-center flex-shrink-0">
              <span class="material-symbols-outlined text-2xl text-outline-variant">home_repair_service</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap justify-between items-start gap-1 mb-1">
                <span class="<?php echo esc_attr($idv_sc); ?> text-[9px] uppercase font-bold tracking-widest px-2 py-0.5 rounded"><?php echo esc_html($idv_sl); ?></span>
                <span class="text-[10px] text-on-surface-variant"><?php echo esc_html(get_the_date('d M Y', $idv_d)); ?></span>
              </div>
              <h4 class="font-headline text-base font-bold truncate"><?php echo esc_html(get_the_title($idv_d)); ?></h4>
              <p class="text-xs text-on-surface-variant line-clamp-1 mt-0.5"><?php echo esc_html(wp_trim_words($idv_d->post_content, 14)); ?></p>
              <div class="mt-2 flex flex-wrap gap-3">
                <span class="text-[10px] font-bold flex items-center gap-0.5">
                  <span class="material-symbols-outlined text-sm">location_on</span><?php echo esc_html(get_post_meta($idv_d->ID, '_idc_ville', true)); ?>
                </span>
                <span class="text-[10px] font-bold flex items-center gap-0.5">
                  <span class="material-symbols-outlined text-sm">tag</span><?php echo esc_html(get_post_meta($idv_d->ID, '_idc_reference', true)); ?>
                </span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Encart calendriers artisans -->
      <div class="mt-6 p-5 bg-surface-container-low/50 rounded-xl border border-outline-variant/10 flex items-center gap-4">
        <span class="material-symbols-outlined text-3xl text-outline-variant">calendar_today</span>
        <div>
          <p class="text-sm font-semibold">Calendriers des artisans</p>
          <p class="text-xs text-on-surface-variant">Consultez les disponibilités des artisans vérifiés de votre région.</p>
          <a href="<?php echo esc_url(home_url('/dashboard/client/disponibilites/')); ?>" class="text-xs text-primary font-bold mt-1 inline-flex items-center gap-1">
            Voir les disponibilités <span class="material-symbols-outlined text-sm">arrow_forward</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Colonne latérale -->
    <div class="space-y-6">
      <div class="bg-on-surface text-surface p-6 rounded-2xl shadow-xl relative overflow-hidden">
        <div class="relative z-10">
          <h3 class="font-headline text-xl font-bold mb-3 leading-tight">Un nouveau projet ?</h3>
          <p class="text-sm text-surface/70 mb-5">Laissez nos artisans s'occuper du reste.</p>
          <a href="<?php echo esc_url(home_url('/devis/')); ?>" class="w-full bg-primary text-on-primary py-3 rounded-lg font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all flex justify-center items-center gap-2">
            Nouvelle demande <span class="material-symbols-outlined text-sm">add_circle</span>
          </a>
        </div>
        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent opacity-50 pointer-events-none"></div>
      </div>

      <!-- Accès rapide -->
      <div class="space-y-2">
        <h3 class="font-label uppercase tracking-widest text-[10px] text-stone-500 mb-2">Accès Rapide</h3>
        <?php foreach ([
            [home_url('/dashboard/client/disponibilites/'), 'calendar_today', 'Calendriers', 'Disponibilités artisans', 'text-green-600'],
            [home_url('/dashboard/client/avis/'), 'star_rate', 'Mes Avis', 'Partagez votre expérience', 'text-tertiary'],
            [home_url('/dashboard/client/devis/'), 'receipt_long', 'Mes demandes', 'Suivre mes devis', 'text-secondary'],
            [home_url('/dashboard/client/profile/'), 'account_circle', 'Mon profil', 'Coordonnées et mot de passe', 'text-primary'],
        ] as [$idv_url, $idv_icon, $idv_title, $idv_sub, $idv_color]) : ?>
          <a href="<?php echo esc_url($idv_url); ?>" class="group flex items-center justify-between p-4 bg-surface-container-low rounded-xl border border-outline-variant/10 hover:border-primary/30 transition-all">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-surface-container-highest flex items-center justify-center <?php echo esc_attr($idv_color); ?> group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-base"><?php echo esc_html($idv_icon); ?></span>
              </div>
              <div>
                <p class="text-sm font-bold text-on-surface"><?php echo esc_html($idv_title); ?></p>
                <p class="text-[10px] text-on-surface-variant"><?php echo esc_html($idv_sub); ?></p>
              </div>
            </div>
            <span class="material-symbols-outlined text-outline group-hover:text-primary transition-colors">chevron_right</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php get_footer(); ?>
