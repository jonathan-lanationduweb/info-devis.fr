<?php
/**
 * Template Name: Espace artisan — Mon profil
 * Édition de la fiche : présentation, coordonnées, zone, métiers,
 * disponibilité (action idc_artisan_fiche).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_terms      = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0, 'orderby' => 'name']);
$idv_mes_slugs  = $idv_fiche ? array_map(static fn($t) => $t->slug, get_the_terms($idv_fiche->ID, 'metier') ?: []) : [];
$idv_meta       = static fn(string $k): string => $idv_fiche ? (string) get_post_meta($idv_fiche->ID, '_idc_' . $k, true) : '';
?>

<div class="md:ml-72 pt-28 px-8 pb-24 md:pb-12">

  <div class="mb-12 max-w-5xl">
    <h1 class="text-4xl md:text-5xl font-headline italic text-on-surface mb-4">Mon profil</h1>
    <p class="text-on-surface-variant max-w-2xl font-body leading-relaxed">
      Ces informations alimentent votre fiche publique et le matching des demandes. SIRET : vérifié automatiquement par notre équipe.
    </p>
  </div>

  <?php if (isset($_GET['fiche'])) : ?>
    <div class="mb-8 p-5 border-l-4 <?php echo $_GET['fiche'] === 'ok' ? 'border-primary bg-primary/5' : 'border-red-400 bg-red-50 text-red-700'; ?> rounded-r-xl text-sm">
      <?php echo $_GET['fiche'] === 'ok' ? '✅ Fiche mise à jour.' : ($_GET['fiche'] === 'champs' ? 'Le code postal doit comporter 5 chiffres.' : 'Une erreur est survenue.'); ?>
    </div>
  <?php endif; ?>

  <?php if (!$idv_fiche) : ?>
    <div class="p-8 bg-yellow-50 border border-yellow-200 rounded-2xl max-w-xl">
      <p class="text-sm text-yellow-800">Aucune fiche artisan n'est associée à votre compte. Contactez-nous pour la créer.</p>
    </div>
  <?php else : ?>

  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="max-w-3xl space-y-10">
    <input type="hidden" name="action" value="idc_artisan_fiche">
    <?php wp_nonce_field('idc_artisan_fiche', 'idc_fiche_nonce'); ?>

    <div class="bg-surface-container-low p-10 rounded-2xl space-y-6">
      <h2 class="font-headline text-2xl font-bold flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">badge</span> Présentation
      </h2>
      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Raison sociale</label>
        <input type="text" value="<?php echo esc_attr(get_the_title($idv_fiche)); ?>" disabled
          class="w-full bg-surface-container/50 border-none p-4 rounded-xl font-body text-on-surface-variant">
        <p class="text-[10px] text-on-surface-variant italic">La raison sociale est liée à votre SIRET — contactez-nous pour la modifier.</p>
      </div>
      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Description de votre activité</label>
        <textarea name="description" rows="5"
          class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body resize-none"
          placeholder="Présentez votre entreprise, vos spécialités, votre expérience…"><?php echo esc_textarea($idv_fiche->post_content); ?></textarea>
      </div>
    </div>

    <div class="bg-surface-container-low p-10 rounded-2xl space-y-6">
      <h2 class="font-headline text-2xl font-bold flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">location_on</span> Zone d'intervention
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Ville</label>
          <input type="text" name="ville" value="<?php echo esc_attr($idv_meta('ville')); ?>"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
        </div>
        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Code postal</label>
          <input type="text" name="code_postal" value="<?php echo esc_attr($idv_meta('code_postal')); ?>" pattern="[0-9]{5}" maxlength="5"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
        </div>
        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Rayon (km)</label>
          <input type="number" name="radius_km" min="0" max="200" value="<?php echo esc_attr($idv_meta('radius_km') ?: '30'); ?>"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
        </div>
      </div>
      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Téléphone</label>
        <input type="tel" name="phone" value="<?php echo esc_attr($idv_meta('phone')); ?>"
          class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
      </div>
    </div>

    <div class="bg-surface-container-low p-10 rounded-2xl space-y-6">
      <h2 class="font-headline text-2xl font-bold flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">construction</span> Mes métiers
      </h2>
      <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
        <?php foreach ((array) $idv_terms as $idv_t) :
            $idv_checked = in_array($idv_t->slug, $idv_mes_slugs, true);
        ?>
          <label class="flex items-center gap-2 cursor-pointer bg-surface-container hover:bg-primary/5 p-3 rounded-xl border-2 transition-all <?php echo $idv_checked ? 'border-primary bg-primary/5' : 'border-transparent'; ?>">
            <input type="checkbox" name="metiers[]" value="<?php echo esc_attr($idv_t->slug); ?>" class="w-4 h-4 accent-primary" <?php checked($idv_checked); ?>>
            <span class="text-sm font-medium text-on-surface"><?php echo esc_html($idv_t->name); ?></span>
          </label>
        <?php endforeach; ?>
      </div>

      <label class="flex items-center gap-3 cursor-pointer pt-4 border-t border-outline-variant/10">
        <input type="checkbox" name="is_available" value="1" class="w-5 h-5 accent-primary" <?php checked($idv_meta('is_available') !== '0'); ?>>
        <span class="text-sm font-medium">Je suis actuellement disponible pour de nouveaux chantiers</span>
      </label>
    </div>

    <button type="submit" class="bg-primary text-on-primary px-10 py-4 rounded-xl font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all">
      Enregistrer ma fiche
    </button>
  </form>

  <?php endif; ?>
</div>

<?php get_footer(); ?>
