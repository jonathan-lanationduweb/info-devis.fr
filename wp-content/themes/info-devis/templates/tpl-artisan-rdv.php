<?php
/**
 * Template Name: Espace artisan — Mes interventions
 * Reproduction de views/artisan/rdv_liste.php (+ détail) : liste à 3 onglets
 * (Demandes / À venir / Passés) + vue détail (?id=) avec client, adresse, photos,
 * actions confirmer / annuler / terminer (idc_rdv_status). Vue agenda liée.
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_tz  = wp_timezone();
$idv_now = current_time('mysql');

$idv_status_cfg = [
    'propose'  => ['amber', 'En attente'],
    'confirme' => ['emerald', 'Confirmé'],
    'termine'  => ['stone', 'Terminé'],
    'annule'   => ['red', 'Annulé'],
];

$idv_all = $idv_fiche ? get_posts([
    'post_type'      => 'rdv',
    'post_status'    => 'publish',
    'posts_per_page' => 200,
    'meta_key'       => '_idc_artisan_post_id',
    'meta_value'     => $idv_fiche->ID,
]) : [];

$idv_detail_id = (int) ($_GET['id'] ?? 0);
$idv_detail = null;
if ($idv_detail_id && $idv_fiche) {
    foreach ($idv_all as $r) {
        if ($r->ID === $idv_detail_id) { $idv_detail = $r; break; }
    }
}
?>

<div class="md:ml-72 min-h-screen p-8 pt-28 bg-background">
  <div class="max-w-6xl mx-auto">

<?php if ($idv_detail) :
    $idv_dt     = (string) get_post_meta($idv_detail->ID, '_idc_date_rdv', true);
    $idv_dobj   = $idv_dt ? new DateTimeImmutable($idv_dt, $idv_tz) : null;
    $idv_statut = (string) get_post_meta($idv_detail->ID, '_idc_statut', true);
    [$idv_col, $idv_lbl] = $idv_status_cfg[$idv_statut] ?? ['stone', $idv_statut];
    $idv_client_id = (int) get_post_meta($idv_detail->ID, '_idc_client_user_id', true);
    $idv_client = $idv_client_id ? get_userdata($idv_client_id) : null;
    $idv_adresse = (string) get_post_meta($idv_detail->ID, '_idc_adresse', true);
    $idv_cp = (string) get_post_meta($idv_detail->ID, '_idc_code_postal', true);
    $idv_ville = (string) get_post_meta($idv_detail->ID, '_idc_ville', true);
    $idv_photos = get_posts(['post_type' => 'attachment', 'post_parent' => $idv_detail->ID, 'numberposts' => 10, 'post_mime_type' => 'image']);
?>
    <a href="<?php echo esc_url(home_url('/dashboard/artisan/rdv/')); ?>" class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary mb-6">
      <span class="material-symbols-outlined text-[18px]">arrow_back</span> Retour aux interventions
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-8 rounded-2xl border border-outline-variant/10">
          <div class="mb-6">
            <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-<?php echo esc_attr($idv_col); ?>-100 text-<?php echo esc_attr($idv_col); ?>-800"><?php echo esc_html($idv_lbl); ?></span>
            <h1 class="text-3xl font-headline italic mt-3">Intervention</h1>
            <?php if ($idv_dobj) : ?><p class="text-on-surface-variant mt-1"><?php echo esc_html(ucfirst(wp_date('l j F Y \à H:i', $idv_dobj->getTimestamp(), $idv_tz))); ?></p><?php endif; ?>
          </div>

          <?php if ($idv_detail->post_content) : ?>
            <div class="mb-6">
              <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Précisions du client</p>
              <p class="text-on-surface leading-relaxed"><?php echo nl2br(esc_html($idv_detail->post_content)); ?></p>
            </div>
          <?php endif; ?>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-surface-container-low p-4 rounded-xl">
              <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Client</p>
              <p class="font-semibold"><?php echo esc_html($idv_client ? $idv_client->display_name : (get_post_meta($idv_detail->ID, '_idc_contact_name', true) ?: 'Client')); ?></p>
              <?php if ($idv_client && $idv_client->user_email) : ?><p class="text-sm text-on-surface-variant"><?php echo esc_html($idv_client->user_email); ?></p><?php endif; ?>
            </div>
            <div class="bg-surface-container-low p-4 rounded-xl">
              <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-1">Adresse du chantier</p>
              <p class="font-semibold"><?php echo esc_html($idv_adresse ?: '—'); ?></p>
              <p class="text-sm text-on-surface-variant"><?php echo esc_html(trim($idv_cp . ' ' . $idv_ville)); ?></p>
            </div>
          </div>

          <?php if ($idv_photos) : ?>
            <div class="mt-6">
              <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mb-2">Photos du chantier</p>
              <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                <?php foreach ($idv_photos as $idv_ph) : ?>
                  <a href="<?php echo esc_url(wp_get_attachment_url($idv_ph->ID)); ?>" target="_blank" rel="noopener" class="block rounded-lg overflow-hidden aspect-square bg-surface-container">
                    <img src="<?php echo esc_url(wp_get_attachment_image_url($idv_ph->ID, 'medium')); ?>" class="w-full h-full object-cover" alt="">
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <aside class="space-y-4">
        <div class="bg-white p-6 rounded-2xl border border-outline-variant/10">
          <h2 class="font-bold text-sm uppercase tracking-widest text-on-surface-variant mb-4">Gérer l'intervention</h2>
          <?php if ($idv_statut === 'propose') : ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mb-2">
              <input type="hidden" name="action" value="idc_rdv_status">
              <input type="hidden" name="rdv_id" value="<?php echo (int) $idv_detail->ID; ?>">
              <input type="hidden" name="statut" value="confirme">
              <?php wp_nonce_field('idc_rdv_status', 'idc_rdv_nonce'); ?>
              <button class="w-full bg-primary text-on-primary py-3 rounded-xl font-bold text-sm hover:opacity-90 transition-all">✓ Confirmer le RDV</button>
            </form>
          <?php elseif ($idv_statut === 'confirme') : ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mb-2">
              <input type="hidden" name="action" value="idc_rdv_status">
              <input type="hidden" name="rdv_id" value="<?php echo (int) $idv_detail->ID; ?>">
              <input type="hidden" name="statut" value="termine">
              <?php wp_nonce_field('idc_rdv_status', 'idc_rdv_nonce'); ?>
              <button class="w-full bg-primary text-on-primary py-3 rounded-xl font-bold text-sm hover:opacity-90 transition-all">Marquer comme terminé</button>
            </form>
          <?php endif; ?>
          <?php if (in_array($idv_statut, ['propose', 'confirme'], true)) : ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Annuler cette intervention ?');">
              <input type="hidden" name="action" value="idc_rdv_status">
              <input type="hidden" name="rdv_id" value="<?php echo (int) $idv_detail->ID; ?>">
              <input type="hidden" name="statut" value="annule">
              <?php wp_nonce_field('idc_rdv_status', 'idc_rdv_nonce'); ?>
              <button class="w-full border border-red-300 text-red-600 py-3 rounded-xl font-bold text-sm hover:bg-red-50 transition-all">Annuler</button>
            </form>
          <?php else : ?>
            <p class="text-sm text-on-surface-variant text-center py-2">Aucune action disponible.</p>
          <?php endif; ?>
        </div>
        <?php if ($idv_client_id) : ?>
          <a href="<?php echo esc_url(home_url('/dashboard/artisan/messages/')); ?>" class="flex items-center justify-center gap-2 bg-surface-container-low py-3 rounded-xl font-bold text-sm hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-[18px]">forum</span> Contacter le client
          </a>
        <?php endif; ?>
      </aside>
    </div>

<?php else :
    $idv_tab = in_array($_GET['tab'] ?? '', ['demandes', 'a_venir', 'passes'], true) ? $_GET['tab'] : 'demandes';
    $idv_buckets = ['demandes' => [], 'a_venir' => [], 'passes' => []];
    foreach ($idv_all as $r) {
        $st = (string) get_post_meta($r->ID, '_idc_statut', true);
        $dt = (string) get_post_meta($r->ID, '_idc_date_rdv', true);
        if ($st === 'propose') {
            $idv_buckets['demandes'][] = $r;
        } elseif ($st === 'confirme' && $dt >= $idv_now) {
            $idv_buckets['a_venir'][] = $r;
        } else {
            $idv_buckets['passes'][] = $r;
        }
    }
    $idv_sort = static fn($a, $b) => strcmp((string) get_post_meta($a->ID, '_idc_date_rdv', true), (string) get_post_meta($b->ID, '_idc_date_rdv', true));
    foreach ($idv_buckets as &$idv_b) { usort($idv_b, $idv_sort); } unset($idv_b);
    $idv_rows = $idv_buckets[$idv_tab];
?>
<style>
  .interv-tab { padding:10px 20px; border-radius:9999px; background:#fff; border:1px solid #e5e7eb; color:#4b5563; font-weight:600; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:all .2s; }
  .interv-tab:hover { border-color:#207752; color:#207752; }
  .interv-tab--active { background:#207752; color:#fff; border-color:#207752; }
  .interv-tab__count { background:rgba(0,0,0,.1); padding:1px 8px; border-radius:9999px; font-size:11px; font-weight:700; }
  .interv-tab--active .interv-tab__count { background:rgba(255,255,255,.25); }
  .interv-row { display:grid; grid-template-columns:60px 1fr 1fr auto auto; gap:16px; align-items:center; padding:16px; background:#fff; border-radius:12px; border:1px solid #f3f4f6; margin-bottom:8px; text-decoration:none; color:inherit; transition:border-color .15s, box-shadow .15s; }
  .interv-row:hover { border-color:#207752; box-shadow:0 4px 12px rgba(32,119,82,.08); }
  .interv-row__day { font-size:10px; text-transform:uppercase; color:#9CA3AF; letter-spacing:.08em; text-align:center; }
  .interv-row__num { font-family:'Newsreader',serif; font-size:22px; font-weight:600; line-height:1; text-align:center; }
  .interv-row__time { font-size:11px; color:#207752; font-weight:700; margin-top:2px; text-align:center; }
</style>

    <header class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <h1 class="font-headline text-5xl font-bold tracking-tight mb-2">Mes interventions</h1>
        <p class="text-on-surface-variant">Gérez vos demandes, confirmez les RDV et suivez vos interventions.</p>
      </div>
      <a href="<?php echo esc_url(home_url('/dashboard/artisan/agenda/')); ?>" class="inline-flex items-center gap-2 border border-outline-variant/30 rounded-full px-5 py-2.5 text-sm hover:border-primary transition-colors">
        <span class="material-symbols-outlined" style="font-size:18px;">calendar_view_week</span> Vue agenda
      </a>
    </header>

    <div class="flex flex-wrap gap-3 mb-8">
      <a href="<?php echo esc_url(add_query_arg('tab', 'demandes', home_url('/dashboard/artisan/rdv/'))); ?>" class="interv-tab <?php echo $idv_tab === 'demandes' ? 'interv-tab--active' : ''; ?>">🔔 Demandes <span class="interv-tab__count"><?php echo count($idv_buckets['demandes']); ?></span></a>
      <a href="<?php echo esc_url(add_query_arg('tab', 'a_venir', home_url('/dashboard/artisan/rdv/'))); ?>" class="interv-tab <?php echo $idv_tab === 'a_venir' ? 'interv-tab--active' : ''; ?>">📅 À venir <span class="interv-tab__count"><?php echo count($idv_buckets['a_venir']); ?></span></a>
      <a href="<?php echo esc_url(add_query_arg('tab', 'passes', home_url('/dashboard/artisan/rdv/'))); ?>" class="interv-tab <?php echo $idv_tab === 'passes' ? 'interv-tab--active' : ''; ?>">✓ Passés <span class="interv-tab__count"><?php echo count($idv_buckets['passes']); ?></span></a>
    </div>

    <?php if (!$idv_rows) : ?>
      <div class="text-center py-24 bg-white rounded-2xl border border-outline-variant/10">
        <span class="material-symbols-outlined" style="font-size:48px;opacity:.4;">event_busy</span>
        <p class="mt-4 text-lg text-on-surface-variant"><?php echo esc_html(['demandes' => 'Aucune demande en attente.', 'a_venir' => 'Aucune intervention à venir.', 'passes' => 'Aucune intervention passée.'][$idv_tab]); ?></p>
      </div>
    <?php else : ?>
      <?php foreach ($idv_rows as $r) :
          $dt = (string) get_post_meta($r->ID, '_idc_date_rdv', true);
          $d = $dt ? new DateTimeImmutable($dt, $idv_tz) : null;
          $st = (string) get_post_meta($r->ID, '_idc_statut', true);
          [$col, $lbl] = $idv_status_cfg[$st] ?? ['stone', $st];
          $cid = (int) get_post_meta($r->ID, '_idc_client_user_id', true);
          $cu = $cid ? get_userdata($cid) : null;
          $ville = (string) get_post_meta($r->ID, '_idc_ville', true);
      ?>
        <a href="<?php echo esc_url(add_query_arg('id', $r->ID, home_url('/dashboard/artisan/rdv/'))); ?>" class="interv-row">
          <div>
            <div class="interv-row__day"><?php echo $d ? esc_html($d->format('D')) : ''; ?></div>
            <div class="interv-row__num"><?php echo $d ? esc_html($d->format('j')) : '—'; ?></div>
            <div class="interv-row__time"><?php echo $d ? esc_html($d->format('H:i')) : ''; ?></div>
          </div>
          <div>
            <p class="font-semibold text-on-surface mb-1">Intervention</p>
            <p class="text-xs text-on-surface-variant"><span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">person</span> <?php echo esc_html($cu ? $cu->display_name : (get_post_meta($r->ID, '_idc_contact_name', true) ?: 'Client')); ?></p>
          </div>
          <div class="text-sm text-on-surface-variant"><span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">location_on</span> <?php echo esc_html($ville ?: '—'); ?></div>
          <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-<?php echo esc_attr($col); ?>-100 text-<?php echo esc_attr($col); ?>-800 whitespace-nowrap"><?php echo esc_html($lbl); ?></span>
          <span class="material-symbols-outlined text-on-surface-variant">chevron_right</span>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>

<?php endif; ?>

  </div>
</div>

<?php get_footer(); ?>
