<?php
/**
 * Template Name: Espace artisan — Mes réalisations
 * Portfolio de l'artisan : liste (publiées + en validation) et ajout
 * (action idc_artisan_projet, publication après validation admin).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_projets = $idv_fiche ? get_posts([
    'post_type'      => 'realisation',
    'post_status'    => ['publish', 'pending'],
    'posts_per_page' => 50,
    'meta_key'       => '_idc_artisan_post_id',
    'meta_value'     => $idv_fiche->ID,
]) : [];
$idv_terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0, 'orderby' => 'name']);
?>

<div class="md:ml-72 pt-28 px-8 pb-24 md:pb-12">

  <div class="mb-12 max-w-5xl">
    <h1 class="text-4xl md:text-5xl font-headline italic text-on-surface mb-4">
      Mes réalisations
      <span class="text-outline-variant/40 not-italic font-light">(<?php echo count($idv_projets); ?>)</span>
    </h1>
    <p class="text-on-surface-variant max-w-2xl font-body leading-relaxed">
      Vos projets terminés inspirent vos futurs clients. Chaque réalisation est publiée après validation par notre équipe.
    </p>
  </div>

  <?php if (isset($_GET['projet'])) : ?>
    <div class="mb-8 p-5 border-l-4 <?php echo $_GET['projet'] === 'ok' ? 'border-primary bg-primary/5' : 'border-red-400 bg-red-50 text-red-700'; ?> rounded-r-xl text-sm">
      <?php echo $_GET['projet'] === 'ok'
          ? '✅ Réalisation envoyée ! Elle sera visible après validation par notre équipe.'
          : ($_GET['projet'] === 'champs' ? 'Merci d’indiquer un titre et une description (10 caractères min.).' : 'Une erreur est survenue, merci de réessayer.'); ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

    <!-- Liste -->
    <div class="lg:col-span-2">
      <?php if (!$idv_projets) : ?>
        <div class="text-center py-24">
          <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">apartment</span>
          <h2 class="font-headline text-3xl mb-4">Aucune réalisation</h2>
          <p class="text-on-surface-variant">Ajoutez votre premier projet avec le formulaire ci-contre.</p>
        </div>
      <?php else : ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <?php foreach ($idv_projets as $idv_p) :
              $idv_pending_p = $idv_p->post_status === 'pending';
              $idv_metiers_p = get_the_terms($idv_p->ID, 'metier') ?: [];
          ?>
            <div class="bg-surface-container-low rounded-xl overflow-hidden border border-outline-variant/10 <?php echo $idv_pending_p ? 'opacity-80' : ''; ?>">
              <div class="h-36 bg-surface-container-high flex items-center justify-center">
                <?php if (has_post_thumbnail($idv_p)) : ?>
                  <?php echo get_the_post_thumbnail($idv_p, 'medium', ['style' => 'width:100%;height:100%;object-fit:cover;']); ?>
                <?php else : ?>
                  <span class="material-symbols-outlined text-4xl text-outline-variant">apartment</span>
                <?php endif; ?>
              </div>
              <div class="p-5">
                <div class="flex justify-between items-start gap-2 mb-2">
                  <h3 class="font-headline text-lg font-bold"><?php echo esc_html(get_the_title($idv_p)); ?></h3>
                  <?php if ($idv_pending_p) : ?>
                    <span class="bg-yellow-50 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase flex-shrink-0">En validation</span>
                  <?php else : ?>
                    <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase flex-shrink-0">Publiée</span>
                  <?php endif; ?>
                </div>
                <p class="text-xs text-on-surface-variant line-clamp-2 mb-2"><?php echo esc_html(wp_trim_words($idv_p->post_content, 16)); ?></p>
                <?php if ($idv_metiers_p) : ?>
                  <span class="card-specialite-tag"><?php echo idv_cat_icon_html($idv_metiers_p[0]->slug); ?> <?php echo esc_html($idv_metiers_p[0]->name); ?></span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Formulaire d'ajout -->
    <div>
      <div class="bg-surface-container-lowest border border-outline-variant/10 rounded-2xl p-8 sticky top-28">
        <h2 class="font-headline text-2xl font-bold mb-6">Ajouter une réalisation</h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="space-y-5">
          <input type="hidden" name="action" value="idc_artisan_projet">
          <?php wp_nonce_field('idc_artisan_projet', 'idc_projet_nonce'); ?>

          <div class="space-y-2">
            <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Titre du projet *</label>
            <input type="text" name="titre" required placeholder="Rénovation salle de bain à Lyon"
              class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3.5 rounded-xl text-sm">
          </div>

          <div class="space-y-2">
            <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Description *</label>
            <textarea name="description" rows="4" required
              class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3.5 rounded-xl text-sm resize-none"
              placeholder="Décrivez les travaux réalisés, les matériaux, la durée…"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Ville</label>
              <input type="text" name="ville" placeholder="Lyon"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3.5 rounded-xl text-sm">
            </div>
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Métier</label>
              <select name="metier" class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3.5 rounded-xl text-sm">
                <option value="">—</option>
                <?php foreach ((array) $idv_terms as $idv_t) : ?>
                  <option value="<?php echo esc_attr($idv_t->slug); ?>"><?php echo esc_html($idv_t->name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <p class="text-[10px] text-on-surface-variant italic">Les photos seront ajoutées par notre équipe lors de la validation (envoi direct de photos : bientôt disponible).</p>

          <button type="submit" class="w-full bg-primary text-on-primary py-3.5 rounded-xl font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all">
            Soumettre pour validation
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php get_footer(); ?>
