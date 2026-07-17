<?php
/**
 * Template Name: Espace client — Mes RDV
 * Liste des rendez-vous du client (CPT rdv).
 */

$idv_user = idv_require_role('client');

get_header();
get_template_part('template-parts/sidebar', 'client', ['user' => $idv_user]);

$idv_rdvs = get_posts([
    'post_type'      => 'rdv',
    'post_status'    => 'publish',
    'posts_per_page' => 50,
    'meta_key'       => '_idc_client_user_id',
    'meta_value'     => $idv_user->ID,
]);
$idv_statuts = [
    'propose'  => ['Proposé', 'bg-secondary-container text-on-secondary-container'],
    'confirme' => ['Confirmé', 'bg-primary-container text-on-primary-container'],
    'annule'   => ['Annulé', 'bg-stone-200 text-stone-500'],
    'termine'  => ['Terminé', 'bg-emerald-100 text-emerald-800'],
];
?>

<div class="md:ml-72 pt-32 pb-24 md:pb-20 px-8 md:px-16 min-h-screen">

  <header class="mb-16 border-b border-outline-variant/10 pb-12">
    <div class="flex items-center gap-4 mb-4">
      <span class="bg-primary/10 text-primary px-3 py-1 text-[10px] font-bold tracking-[0.2em] uppercase rounded-full">Mes RDV</span>
      <span class="h-px w-12 bg-outline-variant/30"></span>
    </div>
    <h1 class="text-5xl md:text-6xl font-headline italic font-bold text-on-surface leading-tight">
      Mes rendez-vous
      <span class="text-outline-variant/40 not-italic font-light">(<?php echo count($idv_rdvs); ?>)</span>
    </h1>
  </header>

  <?php if (!$idv_rdvs) : ?>
    <div class="text-center py-24">
      <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">event_available</span>
      <h2 class="font-headline text-3xl mb-4">Aucun rendez-vous</h2>
      <p class="text-on-surface-variant mb-8">Consultez les disponibilités des artisans pour planifier une intervention.</p>
      <a href="<?php echo esc_url(home_url('/dashboard/client/disponibilites/')); ?>" class="bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all inline-block">
        Voir les disponibilités
      </a>
    </div>
  <?php else : ?>
    <div class="space-y-4 max-w-3xl">
      <?php foreach ($idv_rdvs as $idv_r) :
          $idv_statut  = (string) get_post_meta($idv_r->ID, '_idc_statut', true);
          [$idv_sl, $idv_sc] = $idv_statuts[$idv_statut] ?? $idv_statuts['propose'];
          $idv_artisan = (int) get_post_meta($idv_r->ID, '_idc_artisan_post_id', true);
          $idv_date    = (string) get_post_meta($idv_r->ID, '_idc_date_rdv', true);
      ?>
        <div class="flex items-start gap-4 p-6 rounded-2xl bg-surface-container-low">
          <div class="w-12 h-12 rounded-xl bg-surface-container-high flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-2xl text-primary">event</span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap justify-between items-start gap-2 mb-1">
              <h3 class="font-headline text-lg font-bold"><?php echo esc_html(get_the_title($idv_r)); ?></h3>
              <span class="<?php echo esc_attr($idv_sc); ?> px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full"><?php echo esc_html($idv_sl); ?></span>
            </div>
            <p class="text-sm text-on-surface-variant">
              <?php if ($idv_artisan && get_post($idv_artisan)) : ?>
                Avec <strong><?php echo esc_html(get_the_title($idv_artisan)); ?></strong>
              <?php endif; ?>
              <?php if ($idv_date) : ?> — <?php echo esc_html(date_i18n('l j F Y \à H\hi', strtotime($idv_date))); ?><?php endif; ?>
            </p>
            <?php if (in_array($idv_statut, ['propose', 'confirme'], true)) : ?>
              <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mt-3"
                onsubmit="return confirm('Annuler ce rendez-vous ?');">
                <input type="hidden" name="action" value="idc_rdv_status">
                <input type="hidden" name="rdv_id" value="<?php echo (int) $idv_r->ID; ?>">
                <input type="hidden" name="statut" value="annule">
                <?php wp_nonce_field('idc_rdv_status', 'idc_rdv_nonce'); ?>
                <button type="submit" class="border border-outline-variant/30 text-outline px-4 py-2 rounded-lg font-bold tracking-widest uppercase text-[10px] hover:bg-surface-container transition-colors">
                  Annuler ce RDV
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['rdv']) && $_GET['rdv'] === 'statut_ok') : ?>
    <div class="mt-8 p-5 border-l-4 border-primary bg-primary/5 rounded-r-xl text-sm max-w-3xl">✅ Rendez-vous annulé — l'artisan a été prévenu.</div>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
