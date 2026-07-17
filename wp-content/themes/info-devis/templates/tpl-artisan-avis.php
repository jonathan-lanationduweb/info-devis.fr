<?php
/**
 * Template Name: Espace artisan — Mes avis
 * Avis reçus sur la fiche + réponse de l'artisan (action idc_avis_reply).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_avis = $idv_fiche ? get_posts([
    'post_type'      => 'avis',
    'post_status'    => 'publish',
    'posts_per_page' => 50,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => [
        ['key' => '_idc_artisan_post_id', 'value' => $idv_fiche->ID],
        ['key' => '_idc_status', 'value' => 'approved'],
    ],
]) : [];
$idv_rating  = $idv_fiche ? (string) get_post_meta($idv_fiche->ID, '_idc_rating_avg', true) : '0';
$idv_nb_avis = $idv_fiche ? (int) get_post_meta($idv_fiche->ID, '_idc_rating_count', true) : 0;
?>

<div class="md:ml-72 pt-28 px-8 pb-24 md:pb-12">

  <div class="mb-12 max-w-5xl">
    <h1 class="text-4xl md:text-5xl font-headline italic text-on-surface mb-4">Mes avis</h1>
    <p class="text-on-surface-variant max-w-2xl font-body leading-relaxed">
      Les retours de vos clients, publiés après modération. Répondez-leur pour montrer votre professionnalisme.
    </p>
    <?php if ($idv_nb_avis > 0) : ?>
      <p class="mt-4 text-2xl font-headline font-bold text-primary">★ <?php echo esc_html($idv_rating); ?>/5
        <span class="text-sm text-on-surface-variant font-body font-normal">(<?php echo $idv_nb_avis; ?> avis)</span>
      </p>
    <?php endif; ?>
  </div>

  <?php if (isset($_GET['reponse'])) : ?>
    <div class="mb-8 p-5 border-l-4 <?php echo $_GET['reponse'] === 'ok' ? 'border-primary bg-primary/5' : 'border-red-400 bg-red-50 text-red-700'; ?> rounded-r-xl text-sm">
      <?php echo $_GET['reponse'] === 'ok' ? '✅ Votre réponse a été publiée.' : 'Impossible d’enregistrer la réponse, merci de réessayer.'; ?>
    </div>
  <?php endif; ?>

  <?php if (!$idv_avis) : ?>
    <div class="text-center py-24">
      <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">rate_review</span>
      <h2 class="font-headline text-3xl mb-4">Aucun avis publié</h2>
      <p class="text-on-surface-variant">Vos futurs avis clients apparaîtront ici après modération.</p>
    </div>
  <?php else : ?>
    <div class="space-y-6 max-w-3xl">
      <?php foreach ($idv_avis as $idv_a) :
          $idv_note  = max(0, min(5, (int) get_post_meta($idv_a->ID, '_idc_rating', true)));
          $idv_reply = (string) get_post_meta($idv_a->ID, '_idc_artisan_reply', true);
          $idv_client = (int) get_post_meta($idv_a->ID, '_idc_client_user_id', true);
          $idv_client_name = $idv_client && ($idv_u = get_userdata($idv_client)) ? $idv_u->display_name : 'Client';
      ?>
        <div class="bg-surface-container-low p-8 rounded-xl">
          <div class="flex flex-wrap justify-between items-start gap-3 mb-3">
            <div>
              <p class="font-bold text-sm"><?php echo esc_html($idv_client_name); ?></p>
              <div class="flex items-center gap-0.5 mt-1 text-gold">
                <?php for ($idv_i = 1; $idv_i <= 5; $idv_i++) : ?>
                  <span class="material-symbols-outlined text-lg" style="<?php echo $idv_i <= $idv_note ? "font-variation-settings:'FILL' 1" : ''; ?>">star</span>
                <?php endfor; ?>
              </div>
            </div>
            <span class="text-[10px] text-on-surface-variant"><?php echo esc_html(get_the_date('d M Y', $idv_a)); ?></span>
          </div>
          <p class="text-sm text-on-surface leading-relaxed mb-4"><?php echo esc_html(wp_strip_all_tags($idv_a->post_content)); ?></p>

          <?php if ($idv_reply) : ?>
            <div class="pl-4 border-l-2 border-primary/30 bg-primary/5 rounded-r-lg p-4">
              <p class="text-[10px] uppercase tracking-widest text-primary font-bold mb-1">Votre réponse</p>
              <p class="text-sm text-on-surface-variant"><?php echo esc_html($idv_reply); ?></p>
            </div>
          <?php else : ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="mt-4 space-y-3">
              <input type="hidden" name="action" value="idc_avis_reply">
              <input type="hidden" name="avis_id" value="<?php echo (int) $idv_a->ID; ?>">
              <?php wp_nonce_field('idc_avis_reply', 'idc_reply_nonce'); ?>
              <textarea name="reply" rows="3" required placeholder="Répondez à cet avis…"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3.5 rounded-xl text-sm resize-none"></textarea>
              <button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all">
                Publier ma réponse
              </button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
