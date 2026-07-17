<?php
/**
 * Template Name: Espace artisan — Mes interventions
 * Rendez-vous de la fiche : confirmer / annuler / terminer (action idc_rdv_status).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_rdvs = $idv_fiche ? get_posts([
    'post_type'      => 'rdv',
    'post_status'    => 'publish',
    'posts_per_page' => 100,
    'meta_key'       => '_idc_artisan_post_id',
    'meta_value'     => $idv_fiche->ID,
]) : [];
usort($idv_rdvs, static fn($a, $b) => strcmp(
    (string) get_post_meta($a->ID, '_idc_date_rdv', true),
    (string) get_post_meta($b->ID, '_idc_date_rdv', true)
));

$idv_statuts = [
    'propose'  => ['Proposé — à confirmer', 'bg-yellow-50 text-yellow-700 border border-yellow-200'],
    'confirme' => ['Confirmé', 'bg-primary-container text-on-primary-container'],
    'annule'   => ['Annulé', 'bg-stone-200 text-stone-500'],
    'termine'  => ['Terminé', 'bg-emerald-100 text-emerald-800'],
];
?>

<div class="md:ml-72 pt-28 px-8 pb-24 md:pb-12">

  <div class="mb-12 max-w-5xl">
    <h1 class="text-4xl md:text-5xl font-headline italic text-on-surface mb-4">
      Mes interventions
      <span class="text-outline-variant/40 not-italic font-light">(<?php echo count($idv_rdvs); ?>)</span>
    </h1>
    <p class="text-on-surface-variant max-w-2xl font-body leading-relaxed">
      Les rendez-vous pris par vos clients. Confirmez-les rapidement : ils sont prévenus par email à chaque changement.
    </p>
  </div>

  <?php if (isset($_GET['rdv']) && $_GET['rdv'] === 'statut_ok') : ?>
    <div class="mb-8 p-5 border-l-4 border-primary bg-primary/5 rounded-r-xl text-sm">✅ Statut mis à jour — le client a été notifié.</div>
  <?php endif; ?>

  <?php if (!$idv_rdvs) : ?>
    <div class="text-center py-24">
      <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">engineering</span>
      <h2 class="font-headline text-3xl mb-4">Aucune intervention planifiée</h2>
      <p class="text-on-surface-variant mb-8">Renseignez vos disponibilités pour que les clients puissent réserver.</p>
      <a href="<?php echo esc_url(home_url('/dashboard/artisan/disponibilites/')); ?>" class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 inline-block">
        Gérer mes disponibilités
      </a>
    </div>
  <?php else : ?>
    <div class="space-y-4 max-w-3xl">
      <?php foreach ($idv_rdvs as $idv_r) :
          $idv_statut = (string) get_post_meta($idv_r->ID, '_idc_statut', true);
          [$idv_sl, $idv_sc] = $idv_statuts[$idv_statut] ?? $idv_statuts['propose'];
          $idv_date   = (string) get_post_meta($idv_r->ID, '_idc_date_rdv', true);
          $idv_duree  = (int) get_post_meta($idv_r->ID, '_idc_duree_min', true);
          $idv_client = get_userdata((int) get_post_meta($idv_r->ID, '_idc_client_user_id', true));
      ?>
        <div class="bg-surface-container-low p-6 rounded-2xl">
          <div class="flex flex-wrap justify-between items-start gap-3 mb-3">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-surface-container-high flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-2xl text-primary">event</span>
              </div>
              <div>
                <p class="font-headline text-lg font-bold"><?php echo esc_html(date_i18n('l j F Y \à H\hi', strtotime($idv_date))); ?></p>
                <p class="text-xs text-on-surface-variant">
                  <?php echo $idv_duree; ?> min
                  <?php if ($idv_client) : ?> — avec <strong><?php echo esc_html($idv_client->display_name); ?></strong><?php endif; ?>
                </p>
              </div>
            </div>
            <span class="<?php echo esc_attr($idv_sc); ?> px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full"><?php echo esc_html($idv_sl); ?></span>
          </div>

          <?php if ($idv_r->post_content) : ?>
            <p class="text-sm text-on-surface-variant mb-4 pl-16">« <?php echo esc_html(wp_strip_all_tags($idv_r->post_content)); ?> »</p>
          <?php endif; ?>

          <?php if (in_array($idv_statut, ['propose', 'confirme'], true)) : ?>
            <div class="flex flex-wrap gap-3 pl-16">
              <?php foreach ([
                  'confirme' => ['Confirmer', 'bg-primary text-on-primary', $idv_statut === 'propose'],
                  'termine'  => ['Marquer terminé', 'bg-on-surface text-surface', $idv_statut === 'confirme'],
                  'annule'   => ['Annuler', 'border border-outline-variant/30 text-outline', true],
              ] as $idv_new => [$idv_lbl, $idv_cls, $idv_show]) :
                  if (!$idv_show) {
                      continue;
                  }
              ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js($idv_lbl); ?> ce rendez-vous ?');">
                  <input type="hidden" name="action" value="idc_rdv_status">
                  <input type="hidden" name="rdv_id" value="<?php echo (int) $idv_r->ID; ?>">
                  <input type="hidden" name="statut" value="<?php echo esc_attr($idv_new); ?>">
                  <?php wp_nonce_field('idc_rdv_status', 'idc_rdv_nonce'); ?>
                  <button type="submit" class="<?php echo esc_attr($idv_cls); ?> px-5 py-2.5 rounded-lg font-bold tracking-widest uppercase text-[10px] hover:opacity-90 transition-all">
                    <?php echo esc_html($idv_lbl); ?>
                  </button>
                </form>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
