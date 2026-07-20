<?php
/**
 * Template Name: Espace artisan — Mon agenda
 * Reproduction de views/artisan/calendar.php : agenda hebdomadaire LECTURE SEULE,
 * superposition Ouvert (vert) / Indispo (rouge) / RDV pris (bleu) / Fermé (blanc),
 * navigation par semaine, compteur RDV, récap textuel. Données : idc_rdv_week /
 * idc_rdv_indispos + CPT rdv de la fiche.
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_tz    = wp_timezone();
$idv_today = new DateTimeImmutable('today', $idv_tz);
$idv_wraw  = sanitize_text_field(wp_unslash($_GET['week'] ?? ''));
try {
    $idv_week_start = preg_match('/^\d{4}-\d{2}-\d{2}$/', $idv_wraw)
        ? (new DateTimeImmutable($idv_wraw, $idv_tz))->modify('monday this week')
        : $idv_today->modify('monday this week');
} catch (Exception $e) {
    $idv_week_start = $idv_today->modify('monday this week');
}
$idv_week_end = $idv_week_start->modify('+6 days');
$idv_prev     = $idv_week_start->modify('-7 days')->format('Y-m-d');
$idv_next     = $idv_week_start->modify('+7 days')->format('Y-m-d');

$idv_week     = ($idv_fiche && function_exists('idc_rdv_week')) ? idc_rdv_week($idv_fiche->ID) : [];
$idv_indispos = ($idv_fiche && function_exists('idc_rdv_indispos')) ? idc_rdv_indispos($idv_fiche->ID) : [];
$idv_keys     = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

// RDV de la semaine (propose/confirme/termine), indexés 'Y-m-d G'.
$idv_rdv_map = [];
$idv_rdv_list = [];
if ($idv_fiche) {
    $idv_rdvs = get_posts([
        'post_type'   => 'rdv',
        'post_status' => 'publish',
        'numberposts' => 100,
        'meta_query'  => [
            ['key' => '_idc_artisan_post_id', 'value' => $idv_fiche->ID],
            ['key' => '_idc_statut', 'value' => ['propose', 'confirme', 'termine'], 'compare' => 'IN'],
        ],
    ]);
    foreach ($idv_rdvs as $idv_r) {
        $idv_dt = (string) get_post_meta($idv_r->ID, '_idc_date_rdv', true);
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $idv_dt)) {
            continue;
        }
        $idv_d = new DateTimeImmutable($idv_dt, $idv_tz);
        if ($idv_d >= $idv_week_start && $idv_d <= $idv_week_end->modify('+1 day')) {
            $idv_rdv_map[$idv_d->format('Y-m-d') . ' ' . (int) $idv_d->format('G')] = $idv_r;
            $idv_rdv_list[] = ['post' => $idv_r, 'dt' => $idv_d];
        }
    }
    usort($idv_rdv_list, static fn($a, $b) => $a['dt'] <=> $b['dt']);
}

$idv_hours  = range(8, 18);
$idv_jourFr = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
$idv_moisFr = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$idv_lbl    = $idv_week_start->format('j') . ' ' . $idv_moisFr[(int) $idv_week_start->format('n')] . ' — ' . $idv_week_end->format('j') . ' ' . $idv_moisFr[(int) $idv_week_end->format('n')] . ' ' . $idv_week_end->format('Y');

$idv_slot_open = static function (int $day_idx, int $hour) use ($idv_week, $idv_keys): bool {
    $cfg = $idv_week[$idv_keys[$day_idx]] ?? null;
    if (!$cfg || empty($cfg['on'])) {
        return false;
    }
    $start = (int) substr($cfg['start'], 0, 2);
    $end   = (int) substr($cfg['end'], 0, 2);
    return $hour >= $start && $hour < $end;
};
?>
<style>
  .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; }
  .cal-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .cal-grid { display: grid; grid-template-columns: 60px repeat(7, minmax(80px, 1fr)); gap: 1px; background: #e5e7eb; border-radius: 12px; overflow: hidden; min-width: 620px; }
  @media (max-width: 640px) { .cal-grid { grid-template-columns: 50px repeat(7, minmax(70px, 1fr)); min-width: 540px; } .cal-head__date { font-size: 18px; } .cal-slot { height: 44px; } }
  .cal-head { background: #fff; padding: 12px 8px; text-align: center; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #5b605f; letter-spacing: .05em; }
  .cal-head--today { background: #f0fdf4; color: #207752; }
  .cal-head__date { display: block; font-size: 22px; font-family: 'Newsreader', serif; font-weight: 600; color: #111; text-transform: none; letter-spacing: 0; margin-top: 2px; }
  .cal-hour { background: #fff; padding: 8px; font-size: 11px; text-align: right; color: #9CA3AF; font-weight: 600; }
  .cal-slot { background: #fff; height: 56px; position: relative; border: 2px solid transparent; }
  .cal-slot--available { background: #d1fae5; }
  .cal-slot--unavailable { background: #fee2e2; }
  .cal-slot--rdv { background: #dbeafe; text-decoration: none; }
  .cal-slot--past { background: #fafafa; opacity: .5; }
  .cal-slot__icon { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 16px; }
  .cal-slot--available .cal-slot__icon { color: #065f46; }
  .cal-slot--unavailable .cal-slot__icon { color: #991b1b; }
  .cal-slot--rdv .cal-slot__icon { color: #1e40af; }
  .legend-dot { display: inline-block; width: 12px; height: 12px; border-radius: 3px; margin-right: 6px; vertical-align: middle; }
</style>

<main class="md:ml-72 min-h-screen p-4 sm:p-6 md:p-8 pt-24 md:pt-28 bg-background">
  <div class="max-w-6xl mx-auto">

    <header class="flex flex-col md:flex-row md:items-end justify-between gap-4 sm:gap-6 mb-6">
      <div>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight mb-2 font-headline">Mon agenda</h1>
        <p class="text-on-surface-variant max-w-xl">
          Vue lecture seule de votre semaine. Pour modifier vos horaires d'ouverture, allez dans
          <a href="<?php echo esc_url(home_url('/dashboard/artisan/disponibilites/')); ?>" class="text-primary font-semibold hover:underline">Disponibilités</a>.
        </p>
      </div>
      <div class="flex items-center gap-4 text-xs flex-wrap">
        <span><span class="legend-dot" style="background:#d1fae5;"></span> Ouvert</span>
        <span><span class="legend-dot" style="background:#fee2e2;"></span> Indispo</span>
        <span><span class="legend-dot" style="background:#dbeafe;"></span> RDV pris</span>
        <span><span class="legend-dot" style="background:#fff;border:1px solid #e5e7eb;"></span> Fermé</span>
      </div>
    </header>

    <div class="bg-white p-4 rounded-2xl border border-outline-variant/10 mb-4 flex items-center justify-between flex-wrap gap-3">
      <span><strong class="text-2xl font-headline italic text-primary"><?php echo count($idv_rdv_list); ?></strong> RDV cette semaine</span>
      <a href="<?php echo esc_url(home_url('/dashboard/artisan/disponibilites/')); ?>" class="text-xs font-semibold text-primary hover:underline flex items-center gap-1">
        <span class="material-symbols-outlined" style="font-size:14px;">edit_calendar</span> Modifier mes horaires
      </a>
    </div>

    <div class="flex flex-col md:flex-row items-stretch md:items-center md:justify-between gap-3 mb-6 bg-white p-3 sm:p-4 rounded-2xl border border-outline-variant/10">
      <div class="order-2 md:order-1 flex items-center gap-2">
        <a href="<?php echo esc_url(add_query_arg('week', $idv_prev)); ?>" class="inline-flex items-center gap-1 border border-outline-variant/30 rounded-full px-4 py-2 text-sm hover:border-primary transition-colors">
          <span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span><span class="hidden sm:inline">Précédente</span>
        </a>
        <a href="<?php echo esc_url(add_query_arg('week', $idv_next)); ?>" class="inline-flex items-center gap-1 border border-outline-variant/30 rounded-full px-4 py-2 text-sm hover:border-primary transition-colors">
          <span class="hidden sm:inline">Suivante</span><span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
        </a>
      </div>
      <div class="order-1 md:order-2 text-center">
        <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold mb-1">Semaine du</p>
        <p class="text-base sm:text-lg md:text-xl font-semibold whitespace-nowrap font-headline"><?php echo esc_html($idv_lbl); ?></p>
      </div>
      <div class="order-3 hidden md:flex">
        <a href="<?php echo esc_url(home_url('/dashboard/artisan/agenda/')); ?>" class="inline-flex items-center border border-outline-variant/30 rounded-full px-4 py-2 text-sm hover:border-primary transition-colors">Aujourd'hui</a>
      </div>
    </div>

    <div class="cal-wrap">
      <div class="cal-grid">
        <div class="cal-head" style="background:#fafafa;"></div>
        <?php foreach (range(0, 6) as $i) :
            $d = $idv_week_start->modify("+$i days");
            $isToday = $d->format('Y-m-d') === $idv_today->format('Y-m-d');
        ?>
          <div class="cal-head <?php echo $isToday ? 'cal-head--today' : ''; ?>">
            <?php echo esc_html($idv_jourFr[$i]); ?><span class="cal-head__date"><?php echo esc_html($d->format('j')); ?></span>
          </div>
        <?php endforeach; ?>

        <?php foreach ($idv_hours as $h) : ?>
          <div class="cal-hour"><?php echo esc_html(str_pad((string) $h, 2, '0', STR_PAD_LEFT) . ':00'); ?></div>
          <?php foreach (range(0, 6) as $i) :
              $d       = $idv_week_start->modify("+$i days");
              $dateStr = $d->format('Y-m-d');
              $isPast  = $d < $idv_today;
              $rdv     = $idv_rdv_map[$dateStr . ' ' . $h] ?? null;
              $indispo = in_array($dateStr, $idv_indispos, true);
              $open    = $idv_slot_open($i, $h);
              $cls = 'cal-slot';
              if ($isPast) $cls .= ' cal-slot--past';
              if ($rdv)          $cls .= ' cal-slot--rdv';
              elseif ($indispo)  $cls .= ' cal-slot--unavailable';
              elseif ($open)     $cls .= ' cal-slot--available';
          ?>
            <?php if ($rdv) :
                $rdvTime = date_i18n('H:i', strtotime((string) get_post_meta($rdv->ID, '_idc_date_rdv', true)));
            ?>
              <a href="<?php echo esc_url(home_url('/dashboard/artisan/rdv/')); ?>" class="<?php echo $cls; ?>" title="RDV <?php echo esc_attr($rdvTime); ?>">
                <span class="material-symbols-outlined cal-slot__icon">event</span>
              </a>
            <?php else : ?>
              <div class="<?php echo $cls; ?>">
                <?php if ($open && !$indispo) : ?><span class="material-symbols-outlined cal-slot__icon">check</span>
                <?php elseif ($indispo) : ?><span class="material-symbols-outlined cal-slot__icon">close</span><?php endif; ?>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($idv_rdv_list) : ?>
      <section class="mt-8">
        <h2 class="text-2xl font-bold tracking-tight mb-4 font-headline">Détail des RDV de la semaine</h2>
        <div class="space-y-2">
          <?php foreach ($idv_rdv_list as $idv_item) :
              $idv_r = $idv_item['post']; $idv_d = $idv_item['dt'];
              $idv_client_id = (int) get_post_meta($idv_r->ID, '_idc_client_user_id', true);
              $idv_client = $idv_client_id ? get_userdata($idv_client_id) : null;
              $idv_name = $idv_client ? $idv_client->display_name : 'Client';
              $idv_statut = (string) get_post_meta($idv_r->ID, '_idc_statut', true);
              $idv_lblmap = ['propose' => ['amber', '🔔 En attente'], 'confirme' => ['emerald', '✓ Confirmé'], 'termine' => ['stone', 'Terminé']];
              [$idv_col, $idv_slbl] = $idv_lblmap[$idv_statut] ?? ['stone', $idv_statut];
              $idv_ville = (string) get_post_meta($idv_r->ID, '_idc_ville', true);
          ?>
            <a href="<?php echo esc_url(home_url('/dashboard/artisan/rdv/')); ?>" class="flex items-center justify-between gap-4 bg-white p-4 rounded-xl border border-outline-variant/10 hover:border-primary transition-colors no-underline text-on-surface">
              <div class="flex items-center gap-4">
                <div class="text-center" style="min-width:60px;">
                  <div class="text-xs text-on-surface-variant uppercase tracking-wider"><?php echo esc_html($idv_d->format('D')); ?></div>
                  <div class="text-2xl font-headline italic"><?php echo esc_html($idv_d->format('j')); ?></div>
                  <div class="text-xs font-bold text-primary"><?php echo esc_html($idv_d->format('H:i')); ?></div>
                </div>
                <div>
                  <p class="font-semibold"><?php echo esc_html(get_post_meta($idv_r->ID, '_idc_adresse', true) ? 'Rendez-vous' : 'Rendez-vous'); ?></p>
                  <p class="text-sm text-on-surface-variant"><?php echo esc_html($idv_name); ?><?php echo $idv_ville ? ' · ' . esc_html($idv_ville) : ''; ?></p>
                </div>
              </div>
              <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-<?php echo esc_attr($idv_col); ?>-100 text-<?php echo esc_attr($idv_col); ?>-800"><?php echo esc_html($idv_slbl); ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <p class="text-xs text-on-surface-variant mt-8 italic">
      📌 Vue lecture seule. Pour ajouter/retirer des plages horaires, utilisez la page
      <a href="<?php echo esc_url(home_url('/dashboard/artisan/disponibilites/')); ?>" class="text-primary font-semibold">Disponibilités</a>.
    </p>

  </div>
</main>

<?php get_footer(); ?>
