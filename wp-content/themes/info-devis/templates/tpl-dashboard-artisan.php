<?php
/**
 * Template Name: Espace artisan — Tableau de bord
 * Stats, dernières opportunités et accès rapides (design system de l'espace
 * artisan original).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);
$idv_leads = idv_artisan_leads($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_pending  = count(array_filter($idv_leads, static fn($l) => $l['status'] === 'pending'));
$idv_accepted = count(array_filter($idv_leads, static fn($l) => $l['status'] === 'accepted'));
$idv_rating   = $idv_fiche ? (string) get_post_meta($idv_fiche->ID, '_idc_rating_avg', true) : '0';
$idv_nb_avis  = $idv_fiche ? (int) get_post_meta($idv_fiche->ID, '_idc_rating_count', true) : 0;
$idv_plan     = $idv_fiche ? (get_post_meta($idv_fiche->ID, '_idc_plan', true) ?: 'gratuit') : 'gratuit';
$idv_prenom   = explode(' ', trim($idv_user->display_name ?: 'Artisan'))[0];
?>

<div class="md:ml-72 pt-20 md:pt-28 px-4 md:px-12 pb-24 md:pb-20">
  <header class="mb-8 md:mb-12">
    <h1 class="font-headline text-3xl md:text-5xl font-medium tracking-tight italic mb-2">
      Bienvenue, <?php echo esc_html($idv_prenom); ?>
    </h1>
    <p class="text-on-surface-variant text-sm md:text-base opacity-80">
      Aperçu de vos opportunités, de votre visibilité et de votre activité.
    </p>
  </header>

  <?php if ($idv_fiche && $idv_fiche->post_status === 'pending') : ?>
    <div class="mb-8 p-5 bg-yellow-50 border border-yellow-200 rounded-2xl flex items-center gap-4">
      <span class="material-symbols-outlined text-yellow-600 text-3xl">hourglass_top</span>
      <div>
        <p class="text-sm font-semibold text-yellow-800">Votre fiche est en cours de validation</p>
        <p class="text-xs text-yellow-700">Notre équipe vérifie vos informations. Vous recevrez un email dès sa publication.</p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Stats -->
  <section class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
    <div class="bg-surface-container-lowest p-5 md:p-8 rounded-2xl border border-outline-variant/10 shadow-sm">
      <span class="material-symbols-outlined text-primary mb-3 block text-2xl md:text-[32px]">lightbulb</span>
      <p class="text-2xl md:text-3xl font-headline italic font-bold"><?php echo esc_html(str_pad((string) $idv_pending, 2, '0', STR_PAD_LEFT)); ?></p>
      <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">Opportunités en attente</p>
    </div>
    <div class="bg-surface-container-lowest p-5 md:p-8 rounded-2xl border border-outline-variant/10 shadow-sm">
      <span class="material-symbols-outlined text-tertiary mb-3 block text-2xl md:text-[32px]">handshake</span>
      <p class="text-2xl md:text-3xl font-headline italic font-bold"><?php echo esc_html(str_pad((string) $idv_accepted, 2, '0', STR_PAD_LEFT)); ?></p>
      <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">Demandes acceptées</p>
    </div>
    <div class="bg-surface-container-lowest p-5 md:p-8 rounded-2xl border border-outline-variant/10 shadow-sm">
      <span class="material-symbols-outlined text-gold mb-3 block text-2xl md:text-[32px]">star_rate</span>
      <p class="text-2xl md:text-3xl font-headline italic font-bold"><?php echo esc_html($idv_nb_avis > 0 ? $idv_rating . '/5' : '—'); ?></p>
      <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-semibold"><?php echo (int) $idv_nb_avis; ?> avis client<?php echo $idv_nb_avis > 1 ? 's' : ''; ?></p>
    </div>
    <div class="bg-primary-container p-5 md:p-8 rounded-2xl border border-primary/10 shadow-sm relative overflow-hidden">
      <div class="relative z-10">
        <span class="material-symbols-outlined text-on-primary-container mb-3 block text-2xl md:text-[32px]">card_membership</span>
        <p class="text-2xl md:text-3xl font-headline italic font-bold text-on-primary-container"><?php echo esc_html(ucfirst($idv_plan)); ?></p>
        <p class="text-[10px] uppercase tracking-widest text-on-primary-container mt-1 font-bold">Abonnement</p>
      </div>
    </div>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Dernières opportunités -->
    <div class="lg:col-span-2 space-y-6">
      <div class="flex justify-between items-baseline">
        <h2 class="font-headline text-xl md:text-2xl font-bold">Dernières opportunités</h2>
        <a href="<?php echo esc_url(home_url('/dashboard/artisan/leads/')); ?>" class="text-xs font-bold uppercase tracking-widest text-primary border-b border-primary/30 hover:border-primary transition-colors">Tout voir</a>
      </div>

      <?php if (!$idv_leads) : ?>
        <div class="p-8 rounded-2xl bg-surface-container-low/50 text-center">
          <span class="material-symbols-outlined text-4xl text-outline-variant mb-4 block">lightbulb</span>
          <p class="text-on-surface-variant mb-4 text-sm">Aucune opportunité pour le moment — elles arrivent selon vos métiers et votre zone.</p>
          <a href="<?php echo esc_url(home_url('/dashboard/artisan/profile/')); ?>" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label text-xs uppercase tracking-widest font-bold hover:opacity-90 inline-block">
            Compléter mon profil
          </a>
        </div>
      <?php else : ?>
        <?php foreach (array_slice($idv_leads, 0, 4) as $idv_lead) :
            $idv_d = $idv_lead['demande'];
            $idv_badge = match ($idv_lead['status']) {
                'accepted' => ['Acceptée', 'bg-primary/10 text-primary'],
                'refused'  => ['Refusée', 'bg-stone-200 text-stone-500'],
                default    => ['En attente', 'bg-tertiary-container text-on-tertiary-container'],
            };
        ?>
          <div class="flex items-start gap-4 p-4 md:p-5 rounded-2xl bg-surface-container-low/50 hover:bg-surface-container-lowest transition-all border border-transparent hover:border-outline-variant/10">
            <div class="w-12 h-12 rounded-xl bg-surface-container-high flex items-center justify-center flex-shrink-0">
              <span class="material-symbols-outlined text-2xl text-outline-variant">home_repair_service</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap justify-between items-start gap-1 mb-1">
                <span class="<?php echo esc_attr($idv_badge[1]); ?> text-[9px] uppercase font-bold tracking-widest px-2 py-0.5 rounded"><?php echo esc_html($idv_badge[0]); ?></span>
                <span class="text-[10px] text-on-surface-variant"><?php echo esc_html(get_the_date('d M Y', $idv_d)); ?></span>
              </div>
              <h4 class="font-headline text-base font-bold truncate"><?php echo esc_html(get_the_title($idv_d)); ?></h4>
              <p class="text-xs text-on-surface-variant line-clamp-1 mt-0.5"><?php echo esc_html(wp_trim_words($idv_d->post_content, 14)); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Colonne latérale -->
    <div class="space-y-6">
      <?php if ($idv_fiche && $idv_fiche->post_status === 'publish') : ?>
        <div class="bg-on-surface text-surface p-6 rounded-2xl shadow-xl relative overflow-hidden">
          <div class="relative z-10">
            <h3 class="font-headline text-xl font-bold mb-3 leading-tight">Votre fiche publique</h3>
            <p class="text-sm text-surface/70 mb-5">Vérifiez ce que voient vos futurs clients.</p>
            <a href="<?php echo esc_url(get_permalink($idv_fiche)); ?>" class="w-full bg-primary text-on-primary py-3 rounded-lg font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all flex justify-center items-center gap-2">
              Voir ma fiche <span class="material-symbols-outlined text-sm">open_in_new</span>
            </a>
          </div>
          <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent opacity-50 pointer-events-none"></div>
        </div>
      <?php endif; ?>

      <div class="space-y-2">
        <h3 class="font-label uppercase tracking-widest text-[10px] text-stone-500 mb-2">Accès Rapide</h3>
        <?php foreach ([
            [home_url('/dashboard/artisan/leads/'), 'lightbulb', 'Mes opportunités', 'Demandes à traiter', 'text-primary'],
            [home_url('/dashboard/artisan/projets/'), 'apartment', 'Mes réalisations', 'Alimentez votre portfolio', 'text-secondary'],
            [home_url('/dashboard/artisan/avis/'), 'rate_review', 'Mes avis', 'Répondez à vos clients', 'text-tertiary'],
            [home_url('/dashboard/artisan/abonnement/'), 'card_membership', 'Abonnement', 'Boostez votre visibilité', 'text-gold'],
        ] as [$idv_u, $idv_i, $idv_t, $idv_s, $idv_c]) : ?>
          <a href="<?php echo esc_url($idv_u); ?>" class="group flex items-center justify-between p-4 bg-surface-container-low rounded-xl border border-outline-variant/10 hover:border-primary/30 transition-all">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-surface-container-highest flex items-center justify-center <?php echo esc_attr($idv_c); ?> group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-base"><?php echo esc_html($idv_i); ?></span>
              </div>
              <div>
                <p class="text-sm font-bold text-on-surface"><?php echo esc_html($idv_t); ?></p>
                <p class="text-[10px] text-on-surface-variant"><?php echo esc_html($idv_s); ?></p>
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
