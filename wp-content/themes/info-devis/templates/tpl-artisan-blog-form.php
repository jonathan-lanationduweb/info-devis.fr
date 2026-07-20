<?php
/**
 * Template Name: Espace artisan — Éditeur d'article
 * Reproduction de views/artisan/blog_form.php : création/édition d'un article.
 * ?id= pour éditer un article existant de l'auteur. Soumission → admin-post.php
 * (idc_blog_save), statut « en attente de validation ».
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_list = home_url('/dashboard/artisan/blog/');

// Vérifie l'accès (plan payant) ; sinon retour à la liste (qui montre le lock).
if (!function_exists('idc_blog_allowed') || !idc_blog_allowed($idv_fiche)) {
    echo '<main class="md:ml-72 min-h-screen p-8 pt-28 bg-background"><div class="max-w-4xl mx-auto"><div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center"><i class="fa-solid fa-lock text-amber-600 mb-3" style="font-size:40px"></i><h2 class="font-headline text-2xl mb-3">Réservé aux plans payants</h2><a href="' . esc_url($idv_list) . '" class="text-primary font-bold hover:underline">Retour à mes articles</a></div></div></main>';
    get_footer();
    return;
}

$idv_post = null;
$idv_edit_id = (int) ($_GET['id'] ?? 0);
if ($idv_edit_id) {
    $p = get_post($idv_edit_id);
    if ($p && $p->post_type === 'post' && (int) $p->post_author === (int) $idv_user->ID) {
        $idv_post = $p;
    }
}
$idv_note = $idv_post ? (string) get_post_meta($idv_post->ID, '_idc_admin_note', true) : '';
$idv_cover = '';
if ($idv_post) {
    $idv_cover = get_the_post_thumbnail_url($idv_post, 'large') ?: (string) get_post_meta($idv_post->ID, '_idc_cover_url', true);
}
?>
<main class="md:ml-72 min-h-screen p-4 sm:p-6 md:p-8 pt-24 md:pt-28 bg-background">
  <div class="max-w-4xl mx-auto">

    <header class="mb-8">
      <a href="<?php echo esc_url($idv_list); ?>" class="text-xs text-on-surface-variant hover:text-primary inline-flex items-center gap-1 mb-3"><i class="fa-solid fa-arrow-left"></i> Retour à mes articles</a>
      <h1 class="text-3xl md:text-4xl font-bold tracking-tight" style="font-family:'Newsreader',serif;"><?php echo $idv_post ? 'Éditer mon article' : 'Nouvel article'; ?></h1>
      <p class="text-sm text-on-surface-variant mt-2"><i class="fa-solid fa-circle-info text-primary mr-1"></i> Une fois soumis, votre article sera <strong>validé par notre équipe</strong> avant publication (~24h).</p>
    </header>

    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="space-y-6">
      <input type="hidden" name="action" value="idc_blog_save">
      <input type="hidden" name="idc_blog_nonce" value="<?php echo esc_attr(wp_create_nonce('idc_blog_save')); ?>">
      <?php if ($idv_post) : ?><input type="hidden" name="id" value="<?php echo (int) $idv_post->ID; ?>"><?php endif; ?>

      <div class="bg-white p-6 rounded-2xl border border-outline-variant/10">
        <label class="block font-label text-[10px] uppercase tracking-widest text-on-surface-variant font-bold mb-2">Titre *</label>
        <input type="text" name="title" required maxlength="300" value="<?php echo esc_attr($idv_post->post_title ?? ''); ?>" class="w-full bg-surface-container-low border-0 border-b-2 border-transparent focus:border-primary focus:ring-0 px-3 py-3 rounded-lg text-2xl font-semibold transition-all" placeholder="Ex: Comment isoler ses combles efficacement ?">
      </div>

      <div class="bg-white p-6 rounded-2xl border border-outline-variant/10">
        <label class="block font-label text-[10px] uppercase tracking-widest text-on-surface-variant font-bold mb-2">Résumé court</label>
        <textarea name="excerpt" rows="2" maxlength="500" class="w-full bg-surface-container-low border-0 border-b-2 border-transparent focus:border-primary focus:ring-0 px-3 py-2.5 rounded-lg text-sm transition-all resize-none" placeholder="2-3 phrases qui résument l'article. Apparaît sur la grille du blog (max 500 caractères)."><?php echo esc_textarea($idv_post->post_excerpt ?? ''); ?></textarea>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-outline-variant/10">
        <label class="block font-label text-[10px] uppercase tracking-widest text-on-surface-variant font-bold mb-3">Image de couverture</label>
        <div class="w-full aspect-[16/9] bg-surface-container-low rounded-lg overflow-hidden border border-outline-variant/20 mb-3 flex items-center justify-center">
          <img id="cover-preview" src="<?php echo esc_url($idv_cover); ?>" alt="Aperçu" class="w-full h-full object-cover <?php echo $idv_cover ? '' : 'hidden'; ?>">
          <p id="cover-empty" class="text-xs text-on-surface-variant italic <?php echo $idv_cover ? 'hidden' : ''; ?>"><i class="fa-solid fa-image text-3xl mb-2 block opacity-40"></i> Aucune image</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap mb-3">
          <label for="cover-file" class="inline-flex items-center gap-2 bg-primary text-on-primary px-4 py-2 rounded-lg text-xs uppercase tracking-widest font-bold cursor-pointer hover:opacity-90"><i class="fa-solid fa-upload"></i> Téléverser</label>
          <input type="file" name="cover_file" id="cover-file" accept="image/jpeg,image/png,image/webp" class="hidden">
          <span class="text-xs text-on-surface-variant italic">ou URL externe (.jpg, .png, .webp) ↓</span>
        </div>
        <input type="url" name="cover_image" value="" placeholder="https://images.unsplash.com/photo-xxx.jpg" class="w-full bg-surface-container-low border-0 border-b-2 border-transparent focus:border-primary focus:ring-0 px-3 py-2.5 rounded-lg text-sm transition-all">
      </div>

      <div class="bg-white p-6 rounded-2xl border border-outline-variant/10">
        <label class="block font-label text-[10px] uppercase tracking-widest text-on-surface-variant font-bold mb-2">Contenu de l'article *</label>
        <textarea name="content" rows="18" required class="w-full bg-surface-container-low border-0 border-b-2 border-transparent focus:border-primary focus:ring-0 px-3 py-2.5 rounded-lg text-sm font-mono leading-relaxed transition-all" placeholder="Vous pouvez utiliser des balises HTML simples : <h2> pour les titres, <p> pour les paragraphes, <strong> pour le gras, <ul><li> pour les listes."><?php echo esc_textarea($idv_post->post_content ?? ''); ?></textarea>
        <p class="text-xs text-on-surface-variant/70 mt-2 italic">HTML simple autorisé (titres, paragraphes, listes, liens, gras).</p>
      </div>

      <?php if ($idv_note) : ?>
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl">
          <p class="text-sm font-bold text-red-800 mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Retour de notre équipe :</p>
          <p class="text-sm text-red-700"><?php echo esc_html($idv_note); ?></p>
        </div>
      <?php endif; ?>

      <div class="bg-white p-6 rounded-2xl border border-outline-variant/10 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs text-on-surface-variant italic"><i class="fa-solid fa-clock text-amber-600 mr-1"></i> Statut après envoi : <strong>En attente de validation</strong> par notre équipe.</p>
        <div class="flex items-center gap-2">
          <a href="<?php echo esc_url($idv_list); ?>" class="text-xs px-4 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container">Annuler</a>
          <button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-widest hover:opacity-90 inline-flex items-center gap-2"><i class="fa-solid fa-paper-plane"></i> <?php echo $idv_post ? 'Renvoyer pour validation' : 'Soumettre pour validation'; ?></button>
        </div>
      </div>
    </form>

  </div>
</main>

<script>
(function () {
  const inp = document.getElementById('cover-file');
  const img = document.getElementById('cover-preview');
  const empty = document.getElementById('cover-empty');
  inp.addEventListener('change', function () {
    const f = inp.files && inp.files[0];
    if (!f) return;
    if (f.size > 5 * 1024 * 1024) { alert('Fichier trop volumineux (max 5 Mo)'); inp.value = ''; return; }
    const reader = new FileReader();
    reader.onload = function (e) { img.src = e.target.result; img.classList.remove('hidden'); empty.classList.add('hidden'); };
    reader.readAsDataURL(f);
  });
})();
</script>

<?php get_footer(); ?>
