<?php
/**
 * Template Name: Espace client — Mes Avis
 * Liste des avis du client + dépôt d'un nouvel avis (anti-doublon côté extension).
 * Mise en page conforme au design system de l'espace client original.
 */

$idv_user = idv_require_role('client');

get_header();
get_template_part('template-parts/sidebar', 'client', ['user' => $idv_user]);

$idv_avis = get_posts([
    'post_type'      => 'avis',
    'post_status'    => 'any',
    'posts_per_page' => 50,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_key'       => '_idc_client_user_id',
    'meta_value'     => $idv_user->ID,
]);

$idv_status_labels = [
    'pending'  => ['En attente de modération', 'bg-secondary-container text-on-secondary-container'],
    'approved' => ['Publié', 'bg-emerald-100 text-emerald-800'],
    'refused'  => ['Refusé', 'bg-stone-200 text-stone-500'],
    'reported' => ['Signalé', 'bg-error-container text-on-error-container'],
    'hidden'   => ['Masqué', 'bg-stone-200 text-stone-500'],
];
?>

<div class="md:ml-72 pt-32 pb-24 md:pb-20 px-8 md:px-16 min-h-screen">

  <header class="mb-16 border-b border-outline-variant/10 pb-12">
    <div class="flex items-center gap-4 mb-4">
      <span class="bg-primary/10 text-primary px-3 py-1 text-[10px] font-bold tracking-[0.2em] uppercase rounded-full">Mes avis</span>
      <span class="h-px w-12 bg-outline-variant/30"></span>
    </div>
    <h1 class="text-5xl md:text-6xl font-headline italic font-bold text-on-surface leading-tight">
      Mes avis
      <span class="text-outline-variant/40 not-italic font-light">(<?php echo count($idv_avis); ?>)</span>
    </h1>
    <p class="mt-4 text-on-surface-variant font-body text-lg leading-relaxed max-w-lg">
      Partagez votre expérience avec les artisans et aidez d'autres particuliers à choisir.
    </p>
  </header>

  <?php if (isset($_GET['avis'])) : ?>
    <?php if ($_GET['avis'] === 'ok') : ?>
      <div class="mb-8 p-5 border-l-4 border-primary bg-primary/5 rounded-r-xl text-sm">✅ Merci ! Votre avis a été envoyé et sera visible après validation par notre équipe.</div>
    <?php else :
        $idv_msgs = ['doublon' => 'Vous avez déjà déposé un avis pour cet artisan.', 'champs' => 'Merci de choisir un artisan, une note et d’écrire un commentaire.', 'erreur' => 'Une erreur est survenue, merci de réessayer.'];
    ?>
      <div class="mb-8 p-5 border-l-4 border-red-400 bg-red-50 rounded-r-xl text-sm text-red-700"><?php echo esc_html($idv_msgs[$_GET['avis']] ?? $idv_msgs['erreur']); ?></div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

    <!-- Liste des avis -->
    <div class="lg:col-span-2 space-y-6">
      <?php if (!$idv_avis) : ?>
        <div class="text-center py-24">
          <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">star_rate</span>
          <h2 class="font-headline text-3xl mb-4">Aucun avis pour le moment</h2>
          <p class="text-on-surface-variant">Après vos travaux, revenez partager votre expérience.</p>
        </div>
      <?php else : ?>
        <?php foreach ($idv_avis as $idv_a) :
            $idv_note    = max(0, min(5, (int) get_post_meta($idv_a->ID, '_idc_rating', true)));
            $idv_statut  = (string) get_post_meta($idv_a->ID, '_idc_status', true);
            [$idv_sl, $idv_sc] = $idv_status_labels[$idv_statut] ?? $idv_status_labels['pending'];
            $idv_artisan = (int) get_post_meta($idv_a->ID, '_idc_artisan_post_id', true);
            $idv_reply   = (string) get_post_meta($idv_a->ID, '_idc_artisan_reply', true);
        ?>
          <div class="bg-surface-container-low p-8 rounded-xl">
            <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
              <div>
                <h3 class="font-headline text-xl font-bold"><?php echo esc_html($idv_artisan && get_post($idv_artisan) ? get_the_title($idv_artisan) : 'Artisan'); ?></h3>
                <div class="flex items-center gap-1 mt-1 text-gold">
                  <?php for ($idv_i = 1; $idv_i <= 5; $idv_i++) : ?>
                    <span class="material-symbols-outlined text-lg" style="<?php echo $idv_i <= $idv_note ? "font-variation-settings:'FILL' 1" : ''; ?>">star</span>
                  <?php endfor; ?>
                </div>
              </div>
              <div class="flex flex-col items-end gap-2">
                <span class="<?php echo esc_attr($idv_sc); ?> px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full"><?php echo esc_html($idv_sl); ?></span>
                <span class="text-[10px] text-on-surface-variant"><?php echo esc_html(get_the_date('d M Y', $idv_a)); ?></span>
              </div>
            </div>
            <p class="text-sm text-on-surface leading-relaxed"><?php echo esc_html(wp_strip_all_tags($idv_a->post_content)); ?></p>
            <?php if ($idv_reply) : ?>
              <div class="mt-4 pl-4 border-l-2 border-primary/30 bg-primary/5 rounded-r-lg p-4">
                <p class="text-[10px] uppercase tracking-widest text-primary font-bold mb-1">Réponse de l'artisan</p>
                <p class="text-sm text-on-surface-variant"><?php echo esc_html($idv_reply); ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Dépôt d'un avis -->
    <div>
      <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-8 sticky top-28">
        <h2 class="font-headline text-2xl font-bold mb-2">Laisser un avis</h2>
        <p class="text-xs text-on-surface-variant mb-6">Votre avis sera publié après validation. Nom affiché : <strong><?php echo esc_html($idv_user->display_name); ?></strong>.</p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="space-y-5">
          <input type="hidden" name="action" value="idc_submit_avis">
          <?php wp_nonce_field('idc_avis_form', 'idc_avis_nonce_front'); ?>

          <div class="space-y-2">
            <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Artisan concerné *</label>
            <select name="idc_artisan" required class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3.5 rounded-xl text-sm">
              <option value="">— Choisir —</option>
              <?php foreach (get_posts(['post_type' => 'artisan', 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC']) as $idv_pro) : ?>
                <option value="<?php echo (int) $idv_pro->ID; ?>"><?php echo esc_html($idv_pro->post_title); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="space-y-2">
            <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Votre note *</label>
            <select name="idc_note" required class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3.5 rounded-xl text-sm">
              <?php foreach ([5 => '★★★★★ Excellent', 4 => '★★★★ Très bien', 3 => '★★★ Bien', 2 => '★★ Moyen', 1 => '★ Décevant'] as $idv_v => $idv_l) : ?>
                <option value="<?php echo $idv_v; ?>"><?php echo esc_html($idv_l); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="space-y-2">
            <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Votre commentaire *</label>
            <textarea name="idc_comment" rows="4" required
              class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3.5 rounded-xl text-sm resize-none"
              placeholder="Décrivez votre expérience avec cet artisan…"></textarea>
          </div>

          <button type="submit" class="w-full bg-primary text-on-primary py-3.5 rounded-xl font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all">
            Envoyer mon avis
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php get_footer(); ?>
