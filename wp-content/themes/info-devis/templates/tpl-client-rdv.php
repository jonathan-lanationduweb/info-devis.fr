<?php
/**
 * Template Name: Espace client — Mes RDV
 * Reproduction de views/pages/mes_rdv_liste.php + mes_rdv_detail.php :
 * liste (Référence / Artisan / Date / Motif / Statut / Détails →) + vue détail
 * (?id=) avec informations, photos, documents et annulation (idc_rdv_status).
 */

$idv_user = idv_require_role('client');

get_header();
get_template_part('template-parts/sidebar', 'client', ['user' => $idv_user]);

$idv_tz = wp_timezone();
$idv_statuts = [
    'propose'  => ['En attente', 'bg-amber-100 text-amber-800'],
    'confirme' => ['Confirmé', 'bg-emerald-100 text-emerald-800'],
    'annule'   => ['Annulé', 'bg-red-100 text-red-700'],
    'termine'  => ['Terminé', 'bg-stone-200 text-stone-600'],
];

$idv_rdvs = get_posts([
    'post_type'      => 'rdv',
    'post_status'    => 'publish',
    'posts_per_page' => 100,
    'meta_key'       => '_idc_client_user_id',
    'meta_value'     => $idv_user->ID,
    'orderby'        => 'meta_value',
    'meta_key_2'     => '_idc_date_rdv',
]);

$idv_detail_id = (int) ($_GET['id'] ?? 0);
$idv_detail = null;
if ($idv_detail_id) {
    foreach ($idv_rdvs as $r) {
        if ($r->ID === $idv_detail_id) { $idv_detail = $r; break; }
    }
}
?>

<div class="md:ml-72 pt-32 pb-24 md:pb-20 px-8 md:px-16 min-h-screen">

<?php if ($idv_detail) :
    $idv_statut  = (string) get_post_meta($idv_detail->ID, '_idc_statut', true);
    [$idv_sl, $idv_sc] = $idv_statuts[$idv_statut] ?? $idv_statuts['propose'];
    $idv_artisan = (int) get_post_meta($idv_detail->ID, '_idc_artisan_post_id', true);
    $idv_dt      = (string) get_post_meta($idv_detail->ID, '_idc_date_rdv', true);
    $idv_dur     = (int) get_post_meta($idv_detail->ID, '_idc_duree_min', true);
    $idv_adr     = (string) get_post_meta($idv_detail->ID, '_idc_adresse', true);
    $idv_cp      = (string) get_post_meta($idv_detail->ID, '_idc_code_postal', true);
    $idv_ville   = (string) get_post_meta($idv_detail->ID, '_idc_ville', true);
    $idv_photos  = get_posts(['post_type' => 'attachment', 'post_parent' => $idv_detail->ID, 'numberposts' => 12, 'post_mime_type' => 'image']);
?>
  <nav class="flex items-center gap-2 text-sm text-on-surface-variant mb-6">
    <a href="<?php echo esc_url(home_url('/mes-rdv/')); ?>" class="hover:text-primary">Mes rendez-vous</a>
    <span>›</span><span>Détails</span>
  </nav>

  <header class="mb-10 border-b border-outline-variant/10 pb-8">
    <h1 class="text-4xl md:text-5xl font-headline italic font-bold text-on-surface leading-tight mb-2">
      <?php echo esc_html(get_the_title($idv_artisan) ?: 'Rendez-vous'); ?>
    </h1>
    <p class="text-on-surface-variant">Statut : <span class="<?php echo esc_attr($idv_sc); ?> px-3 py-1 text-[11px] font-bold uppercase tracking-widest rounded-full"><?php echo esc_html($idv_sl); ?></span></p>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-[1fr_280px] gap-8 max-w-5xl">
    <div class="space-y-6">
      <section class="bg-surface-container-low rounded-2xl p-8">
        <h2 class="font-headline italic text-2xl mb-5">Informations</h2>
        <dl class="space-y-3 text-on-surface">
          <?php if ($idv_artisan && get_post($idv_artisan)) : ?><p><strong>Artisan :</strong> <?php echo esc_html(get_the_title($idv_artisan)); ?></p><?php endif; ?>
          <?php if ($idv_dt) : ?><p><strong>Date :</strong> <?php echo esc_html(wp_date('l j F Y \à H\hi', (new DateTimeImmutable($idv_dt, $idv_tz))->getTimestamp(), $idv_tz)); ?><?php echo $idv_dur ? ' (~' . $idv_dur . ' min)' : ''; ?></p><?php endif; ?>
          <?php if ($idv_adr) : ?><p><strong>Adresse :</strong> <?php echo esc_html(trim($idv_adr . ($idv_cp ? ', ' . $idv_cp : '') . ($idv_ville ? ' ' . $idv_ville : ''))); ?></p><?php endif; ?>
          <?php if ($idv_detail->post_content) : ?><p><strong>Description :</strong><br><?php echo nl2br(esc_html($idv_detail->post_content)); ?></p><?php endif; ?>
        </dl>
      </section>

      <?php if ($idv_photos) : ?>
        <section class="bg-surface-container-low rounded-2xl p-8">
          <h2 class="font-headline italic text-2xl mb-5">📷 Photos du chantier (<?php echo count($idv_photos); ?>)</h2>
          <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
            <?php foreach ($idv_photos as $idv_ph) : ?>
              <a href="<?php echo esc_url(wp_get_attachment_url($idv_ph->ID)); ?>" target="_blank" rel="noopener" class="block aspect-square rounded-lg overflow-hidden border border-outline-variant/20 bg-surface-container">
                <img src="<?php echo esc_url(wp_get_attachment_image_url($idv_ph->ID, 'medium')); ?>" loading="lazy" class="w-full h-full object-cover" alt="">
              </a>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>

    <aside>
      <?php if (in_array($idv_statut, ['propose', 'confirme'], true)) : ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Confirmer l\'annulation du RDV ?');">
          <input type="hidden" name="action" value="idc_rdv_status">
          <input type="hidden" name="rdv_id" value="<?php echo (int) $idv_detail->ID; ?>">
          <input type="hidden" name="statut" value="annule">
          <?php wp_nonce_field('idc_rdv_status', 'idc_rdv_nonce'); ?>
          <button class="w-full border border-red-200 text-red-600 py-3 rounded-xl font-bold text-sm hover:bg-red-50 transition-all">Annuler ce rendez-vous</button>
        </form>
      <?php endif; ?>
      <a href="<?php echo esc_url(home_url('/dashboard/client/messages/')); ?>" class="mt-3 flex items-center justify-center gap-2 bg-surface-container-low py-3 rounded-xl font-bold text-sm hover:bg-surface-container transition-all">
        <span class="material-symbols-outlined text-[18px]">forum</span> Contacter l'artisan
      </a>
    </aside>
  </div>

<?php else : ?>

  <nav class="flex items-center gap-2 text-sm text-on-surface-variant mb-6">
    <a href="<?php echo esc_url(home_url('/dashboard/client/')); ?>" class="hover:text-primary">Tableau de bord</a>
    <span>›</span><span>Mes rendez-vous</span>
  </nav>

  <header class="mb-10 border-b border-outline-variant/10 pb-8">
    <div class="flex items-center gap-4 mb-4">
      <span class="bg-primary/10 text-primary px-3 py-1 text-[10px] font-bold tracking-[0.2em] uppercase rounded-full">Mes RDV</span>
      <span class="h-px w-12 bg-outline-variant/30"></span>
    </div>
    <h1 class="text-5xl md:text-6xl font-headline italic font-bold text-on-surface leading-tight">Mes rendez-vous</h1>
    <p class="text-on-surface-variant mt-3"><?php echo count($idv_rdvs); ?> RDV au total</p>
  </header>

  <?php if (isset($_GET['rdv']) && $_GET['rdv'] === 'statut_ok') : ?>
    <div class="mb-8 p-5 border-l-4 border-primary bg-primary/5 rounded-r-xl text-sm max-w-3xl">✅ Rendez-vous annulé — l'artisan a été prévenu.</div>
  <?php endif; ?>

  <?php if (!$idv_rdvs) : ?>
    <div class="text-center py-24">
      <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">event_available</span>
      <h2 class="font-headline italic text-3xl mb-4">Vous n'avez encore aucun rendez-vous.</h2>
      <a href="<?php echo esc_url(home_url('/professionnels/')); ?>" class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all inline-block mt-4">Trouver un artisan</a>
    </div>
  <?php else : ?>
    <div class="bg-white rounded-2xl border border-outline-variant/10 overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[640px]">
        <thead>
          <tr class="border-b border-outline-variant/15">
            <?php foreach (['Référence', 'Artisan', 'Date', 'Statut', ''] as $th) : ?>
              <th class="px-4 py-3 text-[11px] uppercase tracking-widest text-on-surface-variant font-bold"><?php echo esc_html($th); ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($idv_rdvs as $idv_r) :
            $idv_statut = (string) get_post_meta($idv_r->ID, '_idc_statut', true);
            [$idv_sl, $idv_sc] = $idv_statuts[$idv_statut] ?? $idv_statuts['propose'];
            $idv_artisan = (int) get_post_meta($idv_r->ID, '_idc_artisan_post_id', true);
            $idv_dt      = (string) get_post_meta($idv_r->ID, '_idc_date_rdv', true);
            $idv_ville   = (string) get_post_meta($idv_r->ID, '_idc_ville', true);
        ?>
          <tr class="border-b border-outline-variant/8 hover:bg-surface-container-low/40 transition-colors">
            <td class="px-4 py-4 font-mono text-xs text-on-surface-variant">#<?php echo (int) $idv_r->ID; ?></td>
            <td class="px-4 py-4">
              <div class="font-semibold"><?php echo esc_html($idv_artisan && get_post($idv_artisan) ? get_the_title($idv_artisan) : '—'); ?></div>
              <?php if ($idv_ville) : ?><div class="text-xs text-on-surface-variant"><?php echo esc_html($idv_ville); ?></div><?php endif; ?>
            </td>
            <td class="px-4 py-4 text-sm"><?php echo $idv_dt ? esc_html(wp_date('d/m/Y H:i', (new DateTimeImmutable($idv_dt, $idv_tz))->getTimestamp(), $idv_tz)) : '—'; ?></td>
            <td class="px-4 py-4"><span class="<?php echo esc_attr($idv_sc); ?> px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full whitespace-nowrap"><?php echo esc_html($idv_sl); ?></span></td>
            <td class="px-4 py-4 text-right">
              <a href="<?php echo esc_url(add_query_arg('id', $idv_r->ID, home_url('/mes-rdv/'))); ?>" class="text-primary font-semibold text-sm hover:underline whitespace-nowrap">Détails →</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

<?php endif; ?>
</div>

<?php get_footer(); ?>
