<?php
/**
 * Template Name: Espace artisan — Disponibilités
 * Reproduction de views/artisan/disponibilites.php : progression, stats, config
 * rapide (templates), synchro agenda (waitlist), horaires hebdo multi-plages avec
 * copier-coller, absences datées, options avancées (Gold), mode terrain.
 * Auto-save AJAX (idc_dispos_save / idc_indispo_* / idc_params_save / idc_notify_launch).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

if (!$idv_fiche) {
    echo '<main class="md:ml-72 min-h-screen p-8 pt-28 bg-background"><div class="max-w-xl mx-auto p-8 bg-yellow-50 border border-yellow-200 rounded-2xl"><p class="text-sm text-yellow-800">Aucune fiche artisan n\'est associée à votre compte.</p></div></main>';
    get_footer();
    return;
}

$idv_ranges  = idc_rdv_ranges($idv_fiche->ID);
$idv_indispos = idc_rdv_indispos_list($idv_fiche->ID);
$idv_plan    = get_post_meta($idv_fiche->ID, '_idc_plan', true) ?: 'gratuit';
$idv_is_gold = in_array($idv_plan, ['gold', 'illimite', 'pro'], true);
$idv_nonce   = wp_create_nonce('idc_dispos');
$idv_ajax    = admin_url('admin-ajax.php');

$idv_jour_fr    = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];
$idv_jour_court = [1 => 'L', 2 => 'M', 3 => 'M', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];

$idv_total_min = 0;
foreach ($idv_ranges as $plages) {
    foreach ($plages as $p) {
        $d = strtotime($p[0]);
        $f = strtotime($p[1]);
        if ($f > $d) {
            $idv_total_min += ($f - $d) / 60;
        }
    }
}
$idv_total_h = round($idv_total_min / 60, 1);
$idv_jours_config = count(array_filter($idv_ranges, static fn($p) => count($p) > 0));
$idv_progress = min(100, (int) (($idv_jours_config / 7) * 70 + 30));

$idv_params = [
    'duree_rdv_default_min' => (int) (get_post_meta($idv_fiche->ID, '_idc_duree_rdv_default_min', true) ?: 90),
    'pause_entre_rdv_min'   => (int) get_post_meta($idv_fiche->ID, '_idc_pause_entre_rdv_min', true),
    'delai_prevenance_h'    => (int) (get_post_meta($idv_fiche->ID, '_idc_delai_prevenance_h', true) ?: 24),
    'max_rdv_jour'          => (int) get_post_meta($idv_fiche->ID, '_idc_max_rdv_jour', true),
    'accepter_jour_meme'    => (int) get_post_meta($idv_fiche->ID, '_idc_accepter_jour_meme', true),
    'pause_dejeuner'        => (int) get_post_meta($idv_fiche->ID, '_idc_pause_dejeuner', true),
];
?>
<style>
  .serif-it { font-family:'Newsreader',serif; font-style:italic; }
  .tpl-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
  @media (max-width:900px){ .tpl-grid { grid-template-columns:repeat(2,1fr); } }
  .tpl-card { background:#fff; border:2px solid #e5e7eb; border-radius:14px; padding:22px 16px; cursor:pointer; transition:all .2s; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:flex-start; gap:10px; }
  .tpl-card:hover { border-color:#207752; transform:translateY(-2px); box-shadow:0 8px 24px rgba(32,119,82,.08); }
  .tpl-card--active { border-color:#207752; background:linear-gradient(135deg,#f0fdf4 0%,#fff 100%); }
  .tpl-card__emoji { font-size:28px; line-height:1; display:inline-flex; align-items:center; justify-content:center; height:36px; width:36px; }
  .tpl-card__title { font-family:'Newsreader',serif; font-size:17px; font-weight:600; color:#207752; margin:0; }
  .tpl-card__schedule { font-size:11px; color:#5b605f; line-height:1.5; margin:0; }
  .sync-btn { display:flex; align-items:center; gap:10px; padding:12px 16px; border:1px solid #e5e7eb; border-radius:9999px; background:#fff; font-size:13px; font-weight:600; color:#2f3333; }
  .day-row { background:#fff; border:1px solid #f3f4f6; border-radius:14px; padding:20px; margin-bottom:12px; transition:opacity .2s; }
  .day-row--off { opacity:.5; }
  .switch { position:relative; display:inline-block; width:44px; height:24px; }
  .switch input { opacity:0; width:0; height:0; }
  .switch__slider { position:absolute; cursor:pointer; inset:0; background:#e5e7eb; border-radius:9999px; transition:background .2s; }
  .switch__slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.1); }
  .switch input:checked + .switch__slider { background:#207752; }
  .switch input:checked + .switch__slider:before { transform:translateX(20px); }
  .plage-row { display:flex; align-items:center; gap:10px; padding:6px 10px; background:#f9fafb; border-radius:8px; margin-bottom:6px; }
  .plage-row input[type="time"] { border:1px solid #e5e7eb; border-radius:6px; padding:6px 10px; font-size:13px; background:#fff; }
  .plage-row input[type="time"]:focus { outline:none; border-color:#207752; }
  .copy-menu { position:relative; display:inline-block; }
  .copy-menu__btn { background:none; border:1px solid #e5e7eb; border-radius:9999px; padding:4px 12px; cursor:pointer; font-size:12px; color:#5b605f; }
  .copy-menu__btn:hover { border-color:#207752; color:#207752; }
  .copy-menu__dropdown { position:absolute; top:100%; right:0; margin-top:4px; background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.12); z-index:10; min-width:200px; padding:6px; display:none; }
  .copy-menu--open .copy-menu__dropdown { display:block; }
  .copy-menu__item { display:block; width:100%; padding:8px 10px; border-radius:6px; font-size:12px; color:#2f3333; cursor:pointer; background:none; border:none; text-align:left; }
  .copy-menu__item:hover { background:#f3f4f6; color:#207752; }
  .stat-tile { background:#fff; border-radius:14px; padding:16px 20px; border:1px solid #f3f4f6; flex:1; }
  .stat-tile__label { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#9CA3AF; font-weight:700; }
  .stat-tile__value { font-family:'Newsreader',serif; font-size:28px; font-weight:600; color:#207752; margin-top:4px; }
  .progress-bar { height:6px; background:#f3f4f6; border-radius:9999px; overflow:hidden; }
  .progress-bar__fill { height:100%; background:linear-gradient(90deg,#207752 0%,#34d399 100%); border-radius:9999px; transition:width .5s; }
  .save-indicator { position:fixed; bottom:24px; right:24px; background:#207752; color:#fff; padding:10px 18px; border-radius:9999px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px; box-shadow:0 8px 24px rgba(32,119,82,.3); opacity:0; transform:translateY(20px); transition:all .3s; z-index:1000; }
  .save-indicator--visible { opacity:1; transform:translateY(0); }
  .mini-cal { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
  .mini-cal__day { height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:9px; color:#9CA3AF; background:#f9fafb; }
  .mini-cal__day--ouvert { background:#d1fae5; color:#065f46; font-weight:700; }
  .pill-outline { display:inline-flex; align-items:center; gap:8px; padding:8px 16px; border:1px solid #e5e7eb; border-radius:9999px; background:#fff; font-size:12px; font-weight:700; color:#2f3333; }
  .pill-primary { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:9999px; background:#207752; color:#fff; font-size:13px; font-weight:700; border:none; cursor:pointer; }
</style>

<main class="md:ml-72 min-h-screen p-8 pt-28 bg-background">
  <div class="max-w-5xl mx-auto">

    <header class="mb-10">
      <h1 class="text-5xl font-bold tracking-tight text-on-surface mb-2" style="font-family:'Newsreader',serif;">Mes <span class="serif-it">disponibilités</span></h1>
      <p class="text-on-surface-variant max-w-2xl mb-6">Configurez vos horaires d'ouverture en moins de 30 secondes grâce aux templates et au copier-coller. Vos modifications sont enregistrées automatiquement.</p>
      <div class="flex flex-wrap items-center gap-4 mb-3">
        <span class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Configuration</span>
        <div class="flex-1 max-w-xs progress-bar"><div class="progress-bar__fill" style="width:<?php echo (int) $idv_progress; ?>%;"></div></div>
        <span class="text-sm font-semibold text-primary" id="progress-label"><?php echo (int) $idv_progress; ?>% complète</span>
      </div>
    </header>

    <div class="flex gap-4 mb-10 flex-wrap">
      <div class="stat-tile">
        <p class="stat-tile__label">Cette semaine, vous êtes disponible</p>
        <p class="stat-tile__value"><span id="total-heures"><?php echo esc_html($idv_total_h); ?></span> h</p>
      </div>
      <div class="stat-tile">
        <p class="stat-tile__label">Jours actifs</p>
        <p class="stat-tile__value"><span id="total-jours"><?php echo (int) $idv_jours_config; ?></span> / 7</p>
      </div>
      <div class="stat-tile" style="min-width:180px;">
        <p class="stat-tile__label">Aperçu semaine type</p>
        <div class="mini-cal mt-2" id="mini-cal">
          <?php for ($j = 1; $j <= 7; $j++) : ?>
            <div class="mini-cal__day <?php echo !empty($idv_ranges[$j]) ? 'mini-cal__day--ouvert' : ''; ?>" data-mini-day="<?php echo $j; ?>"><?php echo $idv_jour_court[$j]; ?></div>
          <?php endfor; ?>
        </div>
      </div>
    </div>

    <!-- Templates rapides -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold mb-1" style="font-family:'Newsreader',serif;"><i class="fa-solid fa-bolt-lightning text-primary mr-1"></i> Configuration <span class="serif-it">rapide</span></h2>
      <p class="text-sm text-on-surface-variant mb-5">Choisissez un template — vos horaires se rempliront en un clic.</p>
      <div class="tpl-grid">
        <button type="button" class="tpl-card" data-template="classique"><i class="fa-solid fa-briefcase tpl-card__emoji" style="color:#a16207;"></i><h3 class="tpl-card__title">Artisan classique</h3><p class="tpl-card__schedule">Lun–Ven : 8h–12h / 14h–18h<br>Week-end fermé</p></button>
        <button type="button" class="tpl-card" data-template="matinees"><i class="fa-solid fa-sun tpl-card__emoji" style="color:#f59e0b;"></i><h3 class="tpl-card__title">Matinées</h3><p class="tpl-card__schedule">Lun–Ven : 7h–13h<br>Après-midi libre</p></button>
        <button type="button" class="tpl-card" data-template="etendu"><i class="fa-solid fa-moon tpl-card__emoji" style="color:#1e40af;"></i><h3 class="tpl-card__title">Étendu</h3><p class="tpl-card__schedule">Lun–Sam : 7h–19h<br>Dimanche fermé</p></button>
        <button type="button" class="tpl-card" data-template="custom"><i class="fa-solid fa-gear tpl-card__emoji" style="color:#6b7280;"></i><h3 class="tpl-card__title">Personnalisé</h3><p class="tpl-card__schedule">Je configure moi-même<br>(formulaire ci-dessous)</p></button>
      </div>
    </section>

    <!-- Synchronisation agenda -->
    <section class="mb-10 bg-white p-6 rounded-2xl border border-outline-variant/15">
      <div class="flex items-center gap-3 mb-3">
        <i class="fa-solid fa-arrows-rotate text-primary" style="font-size:22px;"></i>
        <h2 class="text-xl font-bold" style="font-family:'Newsreader',serif;">Synchronisez votre <span class="serif-it">agenda</span></h2>
      </div>
      <p class="text-sm text-on-surface-variant mb-4">Vos rendez-vous existants bloqueront automatiquement les créneaux. <strong>Intégrations bientôt disponibles</strong> — un email vous préviendra dès le lancement.</p>
      <form id="notify-launch-form" class="flex items-center gap-2 flex-wrap mb-4 max-w-xl" onsubmit="event.preventDefault(); idvNotifyLaunch(this);">
        <input type="email" name="email" required placeholder="Votre email pour être prévenu" value="<?php echo esc_attr($idv_user->user_email); ?>" class="flex-1 min-w-[200px] px-4 py-2.5 rounded-xl border border-outline-variant/40 focus:border-primary focus:ring-2 focus:ring-primary/10 focus:outline-none text-sm">
        <input type="hidden" name="feature" value="calendar_integration">
        <button type="submit" class="bg-primary text-on-primary px-4 py-2.5 rounded-xl text-xs uppercase tracking-widest font-bold hover:opacity-90 inline-flex items-center gap-2"><i class="fa-solid fa-bell"></i> Me prévenir</button>
        <span id="notify-launch-msg" class="text-xs ml-1"></span>
      </form>
      <div class="flex flex-wrap gap-3">
        <button type="button" class="sync-btn" disabled style="opacity:.7;cursor:not-allowed;"><i class="fa-brands fa-google" style="color:#4285F4;font-size:16px;"></i> Google Calendar <i class="fa-solid fa-clock" style="font-size:12px;color:#9CA3AF;"></i></button>
        <button type="button" class="sync-btn" disabled style="opacity:.7;cursor:not-allowed;"><i class="fa-brands fa-microsoft" style="color:#00A4EF;font-size:16px;"></i> Outlook <i class="fa-solid fa-clock" style="font-size:12px;color:#9CA3AF;"></i></button>
        <button type="button" class="sync-btn" disabled style="opacity:.7;cursor:not-allowed;"><i class="fa-brands fa-apple" style="color:#000;font-size:16px;"></i> Apple Calendar <i class="fa-solid fa-clock" style="font-size:12px;color:#9CA3AF;"></i></button>
      </div>
    </section>

    <!-- Horaires hebdomadaires -->
    <section class="mb-10">
      <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <h2 class="text-2xl font-bold" style="font-family:'Newsreader',serif;">Horaires <span class="serif-it">hebdomadaires</span></h2>
        <div class="flex gap-2">
          <button type="button" id="apply-all-week" class="text-xs font-bold text-primary hover:underline px-3 py-2">🔁 Appliquer Lundi à toute la semaine</button>
          <button type="button" id="clear-all" class="text-xs font-bold text-red-600 hover:underline px-3 py-2">🗑️ Tout effacer</button>
        </div>
      </div>
      <div id="days-container">
        <?php foreach ($idv_jour_fr as $num => $label) :
            $plages = $idv_ranges[$num];
            $has = !empty($plages);
        ?>
          <div class="day-row <?php echo $has ? '' : 'day-row--off'; ?>" data-day="<?php echo $num; ?>">
            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
              <div class="flex items-center gap-4">
                <label class="switch"><input type="checkbox" data-day-toggle="<?php echo $num; ?>" <?php checked($has); ?>><span class="switch__slider"></span></label>
                <h3 class="font-semibold text-on-surface" style="min-width:90px;"><?php echo $label; ?></h3>
                <span class="text-xs text-on-surface-variant day-summary"></span>
              </div>
              <div class="flex gap-2 items-center day-actions" style="display:<?php echo $has ? 'flex' : 'none'; ?>;">
                <button type="button" data-add-plage="<?php echo $num; ?>" class="text-xs font-semibold text-primary hover:underline">+ Ajouter une plage</button>
                <div class="copy-menu">
                  <button type="button" class="copy-menu__btn" data-copy-toggle="<?php echo $num; ?>">📋 Copier sur…</button>
                  <div class="copy-menu__dropdown" data-copy-dropdown="<?php echo $num; ?>">
                    <button type="button" class="copy-menu__item" data-copy-target="all">Tous les jours</button>
                    <button type="button" class="copy-menu__item" data-copy-target="weekdays">Jours ouvrés (Lun–Ven)</button>
                    <button type="button" class="copy-menu__item" data-copy-target="weekend">Week-end (Sam–Dim)</button>
                    <hr style="margin:4px 0;border:0;border-top:1px solid #f3f4f6;">
                    <?php foreach ($idv_jour_fr as $tn => $tl) : if ($tn === $num) { continue; } ?>
                      <button type="button" class="copy-menu__item" data-copy-target="<?php echo $tn; ?>">→ <?php echo $tl; ?></button>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="plages-day" data-plages-day="<?php echo $num; ?>" style="display:<?php echo $has ? 'block' : 'none'; ?>;">
              <?php foreach ($plages as $p) : ?>
                <div class="plage-row">
                  <input type="time" value="<?php echo esc_attr($p[0]); ?>" class="plage-debut" step="1800">
                  <span class="text-on-surface-variant text-sm">→</span>
                  <input type="time" value="<?php echo esc_attr($p[1]); ?>" class="plage-fin" step="1800">
                  <button type="button" class="del-plage text-red-500 hover:bg-red-50 p-1 rounded ml-auto" title="Supprimer"><span class="material-symbols-outlined" style="font-size:16px;">delete</span></button>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Absences -->
    <section class="mb-10 bg-white p-6 rounded-2xl border border-outline-variant/15">
      <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
          <h2 class="text-xl font-bold" style="font-family:'Newsreader',serif;">📅 Mes <span class="serif-it">absences</span></h2>
          <p class="text-sm text-on-surface-variant mt-1">Congés, formations, jours fériés…</p>
        </div>
        <button type="button" id="quick-absent-today" class="pill-outline"><span class="material-symbols-outlined" style="font-size:14px;">today</span> Indisponible aujourd'hui</button>
      </div>
      <form id="indispo-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-5">
        <input type="datetime-local" name="date_debut" required class="bg-surface-container border-none rounded-xl p-3 text-sm">
        <input type="datetime-local" name="date_fin" required class="bg-surface-container border-none rounded-xl p-3 text-sm">
        <input type="text" name="motif" placeholder="Motif (vacances, formation…)" maxlength="100" class="bg-surface-container border-none rounded-xl p-3 text-sm">
        <button type="submit" class="pill-primary" style="justify-content:center;"><span class="material-symbols-outlined" style="font-size:18px;">add</span> Ajouter</button>
      </form>
      <div id="indispos-list" class="space-y-2">
        <?php if (!$idv_indispos) : ?>
          <p class="text-sm text-on-surface-variant italic text-center py-4">Aucune absence programmée.</p>
        <?php else : foreach ($idv_indispos as $i) : ?>
          <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-amber-50 border border-amber-200" data-indispo-id="<?php echo (int) $i['id']; ?>">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-amber-600">event_busy</span>
              <div>
                <p class="font-semibold text-sm"><?php echo esc_html(wp_date('d/m/Y H:i', strtotime($i['start']))); ?> → <?php echo esc_html(wp_date('d/m/Y H:i', strtotime($i['end']))); ?></p>
                <?php if ($i['motif']) : ?><p class="text-xs text-on-surface-variant"><?php echo esc_html($i['motif']); ?></p><?php endif; ?>
              </div>
            </div>
            <button type="button" class="del-indispo text-red-500 hover:bg-red-100 p-2 rounded-lg"><span class="material-symbols-outlined" style="font-size:16px;">delete</span></button>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </section>

    <!-- Options avancées -->
    <?php if (!$idv_is_gold) : ?>
    <section class="mb-10">
      <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-6 sm:p-8 flex items-start gap-4 flex-wrap">
        <i class="fa-solid fa-lock text-amber-600 flex-shrink-0" style="font-size:32px"></i>
        <div class="flex-1 min-w-[260px]">
          <h2 class="text-xl font-bold mb-2" style="font-family:'Newsreader',serif;">Options avancées RDV — Plan Gold</h2>
          <p class="text-sm text-on-surface-variant mb-3">Configurez la durée par défaut d'un RDV, la pause obligatoire entre 2 RDV, le préavis minimum, le nombre maximum de RDV par jour, l'acceptation des RDV jour même et la pause déjeuner automatique.</p>
          <ul class="text-xs text-on-surface-variant space-y-1 mb-4">
            <li><i class="fa-solid fa-circle-check text-primary mr-1"></i> Créneaux multiples (15 min à 3 h)</li>
            <li><i class="fa-solid fa-circle-check text-primary mr-1"></i> Pause inter-RDV configurable (0 à 60 min)</li>
            <li><i class="fa-solid fa-circle-check text-primary mr-1"></i> Limite quotidienne de RDV pour éviter le burn-out</li>
            <li><i class="fa-solid fa-circle-check text-primary mr-1"></i> Préavis minimum (0 h à 3 jours)</li>
          </ul>
          <a href="<?php echo esc_url(home_url('/dashboard/artisan/abonnement/')); ?>" class="inline-flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest hover:opacity-90 transition-all"><i class="fa-solid fa-arrow-up"></i> Passer en Gold (14€/mois)</a>
        </div>
      </div>
    </section>
    <?php else : ?>
    <section class="mb-10">
      <button type="button" id="toggle-advanced" class="flex items-center justify-between w-full bg-white p-5 rounded-2xl border border-outline-variant/15 hover:border-primary transition-colors">
        <div class="text-left">
          <h2 class="text-xl font-bold" style="font-family:'Newsreader',serif;"><i class="fa-solid fa-bolt text-amber-500 mr-1"></i> Options <span class="serif-it">avancées</span></h2>
          <p class="text-sm text-on-surface-variant mt-1">Durée RDV, pause entre RDV, préavis minimum…</p>
        </div>
        <span class="material-symbols-outlined transition-transform" id="advanced-chevron">expand_more</span>
      </button>
      <div class="bg-white border-x border-b border-outline-variant/15 rounded-b-2xl" id="advanced-panel" style="margin-top:-1px;display:none;">
        <form id="params-form" class="p-6 space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Durée par défaut d'un RDV</label>
              <select name="duree_rdv_default_min" class="w-full bg-surface-container border-none rounded-xl p-3 text-sm">
                <?php foreach ([15, 30, 45, 60, 90, 120, 180] as $m) : ?><option value="<?php echo $m; ?>" <?php selected($idv_params['duree_rdv_default_min'], $m); ?>><?php echo $m >= 60 ? floor($m / 60) . 'h' . ($m % 60 ? ' ' . ($m % 60) . 'min' : '') : $m . ' min'; ?></option><?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Pause entre 2 RDV</label>
              <select name="pause_entre_rdv_min" class="w-full bg-surface-container border-none rounded-xl p-3 text-sm">
                <?php foreach ([0, 15, 30, 45, 60] as $m) : ?><option value="<?php echo $m; ?>" <?php selected($idv_params['pause_entre_rdv_min'], $m); ?>><?php echo $m === 0 ? 'Aucune' : $m . ' min'; ?></option><?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Préavis minimum (RDV pris X h à l'avance)</label>
              <select name="delai_prevenance_h" class="w-full bg-surface-container border-none rounded-xl p-3 text-sm">
                <?php foreach ([0, 2, 6, 12, 24, 48, 72] as $h) : $lbl = $h === 0 ? 'Aucun' : ($h < 24 ? $h . 'h' : floor($h / 24) . ' jour' . ($h >= 48 ? 's' : '')); ?><option value="<?php echo $h; ?>" <?php selected($idv_params['delai_prevenance_h'], $h); ?>><?php echo $lbl; ?></option><?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-2">Limite de RDV par jour</label>
              <select name="max_rdv_jour" class="w-full bg-surface-container border-none rounded-xl p-3 text-sm">
                <?php foreach ([0, 3, 5, 8, 10, 15] as $n) : ?><option value="<?php echo $n; ?>" <?php selected($idv_params['max_rdv_jour'], $n); ?>><?php echo $n === 0 ? 'Illimité' : 'Max ' . $n; ?></option><?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="space-y-3 pt-3 border-t border-outline-variant/10">
            <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" name="accepter_jour_meme" value="1" <?php checked($idv_params['accepter_jour_meme'], 1); ?> class="w-5 h-5 accent-primary rounded"><span class="text-sm">Accepter les RDV le jour même</span></label>
            <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" name="pause_dejeuner" value="1" <?php checked($idv_params['pause_dejeuner'], 1); ?> class="w-5 h-5 accent-primary rounded"><span class="text-sm">Pause déjeuner automatique 12h–14h (créneaux bloqués)</span></label>
          </div>
        </form>
      </div>
    </section>
    <?php endif; ?>

    <!-- Mode terrain -->
    <section class="mb-10 bg-gradient-to-br from-primary/5 to-emerald-50 p-6 rounded-2xl border border-primary/15">
      <div class="flex items-center gap-4 flex-wrap">
        <i class="fa-solid fa-mobile-screen-button text-primary" style="font-size:28px"></i>
        <div class="flex-1 min-w-0">
          <h2 class="text-lg font-bold" style="font-family:'Newsreader',serif;">Mode <span class="serif-it">terrain</span></h2>
          <p class="text-sm text-on-surface-variant">Sur l'application mobile, ouvrez ou fermez vos créneaux en un seul geste depuis votre chantier.</p>
        </div>
        <button type="button" class="pill-outline" disabled style="opacity:.7;cursor:not-allowed;"><span class="material-symbols-outlined" style="font-size:18px;">phone_iphone</span> Bientôt disponible</button>
      </div>
    </section>

  </div>
</main>

<div id="save-indicator" class="save-indicator"><span class="material-symbols-outlined" style="font-size:16px;">check_circle</span> <span id="save-indicator-text">Enregistré</span></div>

<script>
(function () {
  const AJAX = '<?php echo esc_js($idv_ajax); ?>';
  const NONCE = '<?php echo esc_js($idv_nonce); ?>';

  function showSaveIndicator(text, isError) {
    const ind = document.getElementById('save-indicator');
    document.getElementById('save-indicator-text').textContent = text || 'Enregistré';
    ind.style.background = isError ? '#dc2626' : '#207752';
    ind.classList.add('save-indicator--visible');
    clearTimeout(window._saveTimer);
    window._saveTimer = setTimeout(function () { ind.classList.remove('save-indicator--visible'); }, 2000);
  }
  let saveTimer;
  function scheduleSave() { clearTimeout(saveTimer); saveTimer = setTimeout(saveDispos, 600); }

  function makePlageRow(debut, fin) {
    const div = document.createElement('div');
    div.className = 'plage-row';
    div.innerHTML = '<input type="time" value="' + debut + '" class="plage-debut" step="1800">'
      + '<span class="text-on-surface-variant text-sm">→</span>'
      + '<input type="time" value="' + fin + '" class="plage-fin" step="1800">'
      + '<button type="button" class="del-plage text-red-500 hover:bg-red-50 p-1 rounded ml-auto" title="Supprimer"><span class="material-symbols-outlined" style="font-size:16px;">delete</span></button>';
    bindPlageRow(div);
    return div;
  }
  function bindPlageRow(row) {
    row.querySelector('.del-plage').addEventListener('click', function () {
      const day = row.closest('.day-row'); row.remove(); updateDaySummary(day); scheduleSave();
    });
    row.querySelectorAll('input[type="time"]').forEach(function (inp) {
      inp.addEventListener('change', function () { updateDaySummary(row.closest('.day-row')); scheduleSave(); });
    });
  }
  document.querySelectorAll('.plage-row').forEach(bindPlageRow);

  document.querySelectorAll('[data-add-plage]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const container = document.querySelector('[data-plages-day="' + btn.dataset.addPlage + '"]');
      container.appendChild(makePlageRow('09:00', '12:00'));
      updateDaySummary(btn.closest('.day-row')); scheduleSave();
    });
  });

  document.querySelectorAll('[data-day-toggle]').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
      const dayRow = toggle.closest('.day-row');
      const dayNum = toggle.dataset.dayToggle;
      const plagesContainer = dayRow.querySelector('[data-plages-day="' + dayNum + '"]');
      const actions = dayRow.querySelector('.day-actions');
      if (toggle.checked) {
        dayRow.classList.remove('day-row--off');
        plagesContainer.style.display = 'block'; actions.style.display = 'flex';
        if (plagesContainer.children.length === 0) plagesContainer.appendChild(makePlageRow('09:00', '12:00'));
      } else {
        dayRow.classList.add('day-row--off');
        plagesContainer.style.display = 'none'; actions.style.display = 'none'; plagesContainer.innerHTML = '';
      }
      updateDaySummary(dayRow); scheduleSave();
    });
  });

  function updateDaySummary(dayRow) {
    if (!dayRow) return;
    const plages = dayRow.querySelectorAll('.plage-row');
    let total = 0; const parts = [];
    plages.forEach(function (p) {
      const d = p.querySelector('.plage-debut').value, f = p.querySelector('.plage-fin').value;
      if (d && f && d < f) {
        total += (parseInt(f.slice(0,2))*60+parseInt(f.slice(3))) - (parseInt(d.slice(0,2))*60+parseInt(d.slice(3)));
        parts.push(d + '–' + f);
      }
    });
    const summary = dayRow.querySelector('.day-summary');
    summary.textContent = plages.length === 0 ? 'Fermé' : parts.join(' · ') + ' · ' + Math.round(total/60*10)/10 + 'h';
    updateGlobalStats();
  }
  function updateGlobalStats() {
    let totalMin = 0, joursActifs = 0;
    document.querySelectorAll('.day-row').forEach(function (row) {
      let dayMin = 0;
      row.querySelectorAll('.plage-row').forEach(function (p) {
        const d = p.querySelector('.plage-debut').value, f = p.querySelector('.plage-fin').value;
        if (d && f && d < f) dayMin += (parseInt(f.slice(0,2))*60+parseInt(f.slice(3))) - (parseInt(d.slice(0,2))*60+parseInt(d.slice(3)));
      });
      if (dayMin > 0) joursActifs++;
      totalMin += dayMin;
      const miniDay = document.querySelector('[data-mini-day="' + row.dataset.day + '"]');
      if (miniDay) miniDay.classList.toggle('mini-cal__day--ouvert', dayMin > 0);
    });
    document.getElementById('total-heures').textContent = (totalMin/60).toFixed(1);
    document.getElementById('total-jours').textContent = joursActifs;
    const progress = Math.min(100, Math.round(30 + (joursActifs/7)*70));
    document.querySelector('.progress-bar__fill').style.width = progress + '%';
    document.getElementById('progress-label').textContent = progress + '% complète';
  }
  document.querySelectorAll('.day-row').forEach(updateDaySummary);

  const TEMPLATES = {
    classique: {1:[['08:00','12:00'],['14:00','18:00']],2:[['08:00','12:00'],['14:00','18:00']],3:[['08:00','12:00'],['14:00','18:00']],4:[['08:00','12:00'],['14:00','18:00']],5:[['08:00','12:00'],['14:00','18:00']],6:[],7:[]},
    matinees: {1:[['07:00','13:00']],2:[['07:00','13:00']],3:[['07:00','13:00']],4:[['07:00','13:00']],5:[['07:00','13:00']],6:[],7:[]},
    etendu: {1:[['07:00','19:00']],2:[['07:00','19:00']],3:[['07:00','19:00']],4:[['07:00','19:00']],5:[['07:00','19:00']],6:[['07:00','19:00']],7:[]},
    custom: null
  };
  document.querySelectorAll('[data-template]').forEach(function (card) {
    card.addEventListener('click', function () {
      const key = card.dataset.template;
      if (key === 'custom') { document.querySelector('#days-container').scrollIntoView({behavior:'smooth',block:'start'}); return; }
      const tpl = TEMPLATES[key]; if (!tpl) return;
      if (!confirm('Remplacer vos horaires actuels par ce template ?')) return;
      applyTemplate(tpl);
      document.querySelectorAll('.tpl-card').forEach(function (c) { c.classList.remove('tpl-card--active'); });
      card.classList.add('tpl-card--active'); scheduleSave();
    });
  });
  function applyTemplate(tpl) {
    Object.entries(tpl).forEach(function (entry) {
      const day = entry[0], plages = entry[1];
      const dayRow = document.querySelector('[data-day="' + day + '"]');
      const container = dayRow.querySelector('[data-plages-day="' + day + '"]');
      const toggle = dayRow.querySelector('[data-day-toggle]');
      const actions = dayRow.querySelector('.day-actions');
      container.innerHTML = '';
      if (plages.length === 0) { toggle.checked = false; dayRow.classList.add('day-row--off'); container.style.display = 'none'; actions.style.display = 'none'; }
      else { toggle.checked = true; dayRow.classList.remove('day-row--off'); container.style.display = 'block'; actions.style.display = 'flex'; plages.forEach(function (p) { container.appendChild(makePlageRow(p[0], p[1])); }); }
      updateDaySummary(dayRow);
    });
  }

  document.querySelectorAll('[data-copy-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      const menu = btn.closest('.copy-menu');
      document.querySelectorAll('.copy-menu--open').forEach(function (m) { if (m !== menu) m.classList.remove('copy-menu--open'); });
      menu.classList.toggle('copy-menu--open');
    });
  });
  document.addEventListener('click', function () { document.querySelectorAll('.copy-menu--open').forEach(function (m) { m.classList.remove('copy-menu--open'); }); });
  document.querySelectorAll('.copy-menu__item').forEach(function (item) {
    item.addEventListener('click', function (e) {
      e.stopPropagation();
      copyDayTo(item.closest('[data-copy-dropdown]').dataset.copyDropdown, item.dataset.copyTarget);
      document.querySelectorAll('.copy-menu--open').forEach(function (m) { m.classList.remove('copy-menu--open'); });
    });
  });
  function copyDayTo(sourceDay, target) {
    const sourceRow = document.querySelector('[data-day="' + sourceDay + '"]');
    const sourcePlages = [].slice.call(sourceRow.querySelectorAll('.plage-row')).map(function (p) { return [p.querySelector('.plage-debut').value, p.querySelector('.plage-fin').value]; });
    const targets = target === 'all' ? [1,2,3,4,5,6,7] : target === 'weekdays' ? [1,2,3,4,5] : target === 'weekend' ? [6,7] : [parseInt(target)];
    targets.forEach(function (d) {
      if (String(d) === String(sourceDay)) return;
      const dayRow = document.querySelector('[data-day="' + d + '"]');
      const container = dayRow.querySelector('[data-plages-day="' + d + '"]');
      const toggle = dayRow.querySelector('[data-day-toggle]');
      const actions = dayRow.querySelector('.day-actions');
      container.innerHTML = '';
      if (sourcePlages.length === 0) { toggle.checked = false; dayRow.classList.add('day-row--off'); container.style.display = 'none'; actions.style.display = 'none'; }
      else { toggle.checked = true; dayRow.classList.remove('day-row--off'); container.style.display = 'block'; actions.style.display = 'flex'; sourcePlages.forEach(function (p) { container.appendChild(makePlageRow(p[0], p[1])); }); }
      updateDaySummary(dayRow);
    });
    scheduleSave();
  }
  document.getElementById('apply-all-week').addEventListener('click', function () { if (!confirm('Appliquer les horaires du Lundi à toute la semaine (Lun–Dim) ?')) return; copyDayTo('1', 'all'); });
  document.getElementById('clear-all').addEventListener('click', function () {
    if (!confirm('Supprimer toutes les disponibilités ?')) return;
    document.querySelectorAll('.day-row').forEach(function (row) {
      row.querySelector('[data-plages-day]').innerHTML = '';
      row.querySelector('[data-day-toggle]').checked = false;
      row.classList.add('day-row--off');
      row.querySelector('[data-plages-day]').style.display = 'none';
      row.querySelector('.day-actions').style.display = 'none';
      updateDaySummary(row);
    });
    scheduleSave();
  });

  async function saveDispos() {
    const fd = new FormData();
    fd.append('action', 'idc_dispos_save');
    fd.append('idc_dispos_nonce', NONCE);
    let count = 0;
    document.querySelectorAll('.day-row').forEach(function (row) {
      if (row.classList.contains('day-row--off')) return;
      const day = row.dataset.day;
      row.querySelectorAll('.plage-row').forEach(function (p) {
        const d = p.querySelector('.plage-debut').value, f = p.querySelector('.plage-fin').value;
        if (d && f && d < f) { fd.append('creneaux[]', day + '|' + d + '|' + f); count++; }
      });
    });
    try {
      const res = await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
      const data = await res.json();
      showSaveIndicator(data.success ? ('✓ ' + count + ' plages enregistrées') : 'Erreur', !data.success);
    } catch (e) { showSaveIndicator('Erreur réseau', true); }
  }

  document.getElementById('indispo-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fd.append('action', 'idc_indispo_add');
    fd.append('idc_dispos_nonce', NONCE);
    try {
      const res = await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
      const data = await res.json();
      if (data.success) location.reload(); else showSaveIndicator('⚠ ' + (data.error || 'Erreur'), true);
    } catch (e) { showSaveIndicator('Erreur réseau', true); }
  });
  document.querySelectorAll('.del-indispo').forEach(function (btn) {
    btn.addEventListener('click', async function () {
      const card = btn.closest('[data-indispo-id]');
      if (!confirm('Supprimer cette absence ?')) return;
      const fd = new FormData();
      fd.append('action', 'idc_indispo_delete');
      fd.append('idc_dispos_nonce', NONCE);
      fd.append('id', card.dataset.indispoId);
      await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
      card.remove(); showSaveIndicator('Absence supprimée');
    });
  });
  document.getElementById('quick-absent-today').addEventListener('click', async function () {
    if (!confirm('Marquer toute la journée d\'aujourd\'hui comme indisponible ?')) return;
    const t = new Date();
    const day = t.getFullYear() + '-' + String(t.getMonth()+1).padStart(2,'0') + '-' + String(t.getDate()).padStart(2,'0');
    const fd = new FormData();
    fd.append('action', 'idc_indispo_add');
    fd.append('idc_dispos_nonce', NONCE);
    fd.append('date_debut', day + ' 00:00');
    fd.append('date_fin', day + ' 23:59');
    fd.append('motif', 'Indisponible (rapide)');
    const res = await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
    if ((await res.json()).success) location.reload();
  });

  <?php if ($idv_is_gold) : ?>
  const adv = document.getElementById('advanced-panel');
  document.getElementById('toggle-advanced').addEventListener('click', function () {
    const isOpen = adv.style.display !== 'none';
    adv.style.display = isOpen ? 'none' : 'block';
    document.getElementById('advanced-chevron').style.transform = isOpen ? '' : 'rotate(180deg)';
  });
  const paramsForm = document.getElementById('params-form');
  paramsForm.querySelectorAll('select, input').forEach(function (input) {
    input.addEventListener('change', async function () {
      const fd = new FormData(paramsForm);
      fd.append('action', 'idc_params_save');
      fd.append('idc_dispos_nonce', NONCE);
      try {
        const res = await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json();
        showSaveIndicator(data.success ? '✓ Paramètres enregistrés' : ('⚠ ' + (data.error || 'Erreur')), !data.success);
      } catch (e) { showSaveIndicator('Erreur réseau', true); }
    });
  });
  <?php endif; ?>

  window.idvNotifyLaunch = async function (form) {
    const msg = document.getElementById('notify-launch-msg');
    msg.textContent = '';
    const fd = new FormData(form);
    fd.append('action', 'idc_notify_launch');
    fd.append('idc_dispos_nonce', NONCE);
    try {
      const res = await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
      const data = await res.json();
      if (data.success) {
        msg.textContent = '✓ ' + (data.message || 'Vous serez prévenu !');
        msg.className = 'text-xs ml-1 text-emerald-600';
        form.querySelector('input[name="email"]').disabled = true;
        form.querySelector('button[type="submit"]').disabled = true;
      } else { msg.textContent = data.error || 'Erreur'; msg.className = 'text-xs ml-1 text-red-600'; }
    } catch (e) { msg.textContent = 'Erreur réseau'; msg.className = 'text-xs ml-1 text-red-600'; }
  };
})();
</script>

<?php get_footer(); ?>
