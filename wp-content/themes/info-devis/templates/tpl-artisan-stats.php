<?php
/**
 * Template Name: Espace artisan — Statistiques
 * Reproduction de views/artisan/stats.php : KPIs + évolution mensuelle des leads,
 * avec déblocage par plan (Gratuit verrouillé, Silver KPIs, Gold + mensuel).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_plan      = $idv_fiche ? (get_post_meta($idv_fiche->ID, '_idc_plan', true) ?: 'gratuit') : 'gratuit';
$idv_is_free   = $idv_plan === 'gratuit';
$idv_advanced  = in_array($idv_plan, ['gold', 'illimite', 'pro'], true);

// Données réelles.
$idv_leads = ($idv_fiche && function_exists('idc_get_demandes_for_artisan'))
    ? idc_get_demandes_for_artisan($idv_user->ID) : [];
$idv_total_leads = count($idv_leads);
$idv_accepted    = 0;
$idv_monthly     = []; // 'YYYY-MM' => ['leads' => n, 'accepted' => n]
foreach ($idv_leads as $idv_l) {
    $idv_key = get_the_date('Y-m', $idv_l);
    $idv_monthly[$idv_key]['leads'] = ($idv_monthly[$idv_key]['leads'] ?? 0) + 1;
    if ($idv_fiche && get_post_meta($idv_l->ID, '_idc_lead_status_' . $idv_fiche->ID, true) === 'accepted') {
        $idv_accepted++;
        $idv_monthly[$idv_key]['accepted'] = ($idv_monthly[$idv_key]['accepted'] ?? 0) + 1;
    }
}
ksort($idv_monthly);
$idv_monthly = array_slice($idv_monthly, -6, 6, true);
$idv_rating  = $idv_fiche ? (float) get_post_meta($idv_fiche->ID, '_idc_rating_avg', true) : 0;
$idv_avis    = $idv_fiche ? (int) get_post_meta($idv_fiche->ID, '_idc_rating_count', true) : 0;
$idv_taux    = $idv_total_leads > 0 ? round($idv_accepted / $idv_total_leads * 100) : 0;
?>

<main class="md:ml-72 pt-24 md:pt-28 pb-16 min-h-screen bg-surface-container-low">
  <div class="max-w-5xl mx-auto px-6">

    <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
      <div>
        <h1 class="font-headline text-4xl text-on-surface mb-2">Mes statistiques</h1>
        <p class="text-on-surface-variant text-sm">Suivez vos performances sur InfoDevis</p>
      </div>
      <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest <?php echo $idv_advanced ? 'bg-amber-50 text-amber-800 border border-amber-200' : ($idv_plan === 'silver' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-stone-100 text-stone-600 border border-stone-200'); ?>">
        <i class="fa-solid <?php echo $idv_advanced ? 'fa-medal' : ($idv_plan === 'silver' ? 'fa-circle-check' : 'fa-clipboard-check'); ?>"></i>
        Plan <?php echo esc_html(ucfirst($idv_plan)); ?>
      </span>
    </div>

    <?php if ($idv_is_free) : ?>
      <div class="mb-8 bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-6 sm:p-8 text-center">
        <i class="fa-solid fa-lock text-amber-600 mb-3" style="font-size:36px"></i>
        <h2 class="font-headline text-2xl mb-2">Statistiques détaillées réservées aux plans payants</h2>
        <p class="text-on-surface-variant text-sm max-w-md mx-auto mb-5">
          Passez en plan <strong>Silver (10€/mois)</strong> pour les statistiques basiques,
          ou <strong>Gold (14€/mois)</strong> pour les statistiques détaillées et l'évolution mensuelle.
        </p>
        <a href="<?php echo esc_url(home_url('/dashboard/artisan/abonnement/')); ?>"
           class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest hover:opacity-90 transition-all">
          <i class="fa-solid fa-arrow-up"></i> Choisir mon plan
        </a>
      </div>
    <?php else : ?>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <?php foreach ([
            ['Total leads', $idv_total_leads, 'fa-clipboard-list', 'text-on-surface'],
            ['Taux acceptation', $idv_taux . '%', 'fa-circle-check', 'text-primary'],
            ['Note moyenne', number_format($idv_rating, 1) . '/5', 'fa-star', 'text-amber-500'],
            ['Avis reçus', $idv_avis, 'fa-comment-dots', 'text-primary'],
        ] as [$idv_lbl, $idv_val, $idv_ico, $idv_col]) : ?>
          <div class="bg-white rounded-2xl border border-outline-variant/20 p-6 text-center shadow-sm">
            <i class="fa-solid <?php echo esc_attr($idv_ico . ' ' . $idv_col); ?> mb-3" style="font-size:24px"></i>
            <p class="text-3xl font-headline font-bold <?php echo esc_attr($idv_col); ?>"><?php echo esc_html($idv_val); ?></p>
            <p class="text-xs font-label uppercase tracking-widest text-on-surface-variant mt-1"><?php echo esc_html($idv_lbl); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!$idv_advanced && !$idv_is_free) : ?>
      <div class="bg-white p-6 sm:p-8 rounded-2xl border border-outline-variant/10 mb-8 text-center">
        <i class="fa-solid fa-chart-line text-stone-300 mb-3" style="font-size:36px"></i>
        <h3 class="font-headline text-xl mb-2">Évolution mensuelle réservée au plan Gold</h3>
        <p class="text-sm text-on-surface-variant mb-4 max-w-md mx-auto">
          Passez en plan Gold (14€/mois) pour voir l'évolution mensuelle de vos leads et votre taux de conversion détaillé.
        </p>
        <a href="<?php echo esc_url(home_url('/dashboard/artisan/abonnement/')); ?>" class="inline-flex items-center gap-2 text-primary font-bold text-sm hover:underline">
          <i class="fa-solid fa-medal"></i> Voir le plan Gold
        </a>
      </div>
    <?php elseif ($idv_advanced) : ?>
      <div class="bg-white rounded-2xl border border-outline-variant/20 p-8 mb-8 shadow-sm">
        <h2 class="font-headline text-2xl mb-6">Évolution mensuelle des leads</h2>
        <?php if (!$idv_monthly) : ?>
          <div class="text-center py-12 text-on-surface-variant">
            <span class="material-symbols-outlined text-5xl mb-3 block">bar_chart</span>
            <p>Pas encore de données mensuelles.</p>
          </div>
        <?php else :
            $idv_max = max(array_map(static fn($m) => $m['leads'] ?? 0, $idv_monthly)) ?: 1;
        ?>
          <div class="flex items-end justify-between gap-2 h-48 mb-4">
            <?php foreach ($idv_monthly as $idv_mk => $idv_mv) :
                $idv_n = $idv_mv['leads'] ?? 0;
                $idv_h = max(5, round($idv_n / $idv_max * 100));
                $idv_ismax = $idv_n === $idv_max;
            ?>
              <div class="flex-1 flex flex-col items-center gap-1">
                <span class="text-xs font-bold text-on-surface-variant"><?php echo (int) $idv_n; ?></span>
                <div class="w-full <?php echo $idv_ismax ? 'bg-primary' : 'bg-primary/30'; ?> rounded-t-lg hover:bg-primary transition-colors" style="height:<?php echo (int) $idv_h; ?>%"></div>
                <span class="text-[10px] text-on-surface-variant font-label"><?php echo esc_html(substr($idv_mk, 5, 2) . '/' . substr($idv_mk, 2, 2)); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php get_footer(); ?>
