<?php
/**
 * Template Name: Espace artisan — Apparence
 * Reproduction de views/artisan/apparence.php : personnalisation du thème du
 * dashboard (Gold uniquement) — 7 thèmes prédéfinis + couleur personnalisée.
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_is_gold = function_exists('idc_apparence_is_gold') && idc_apparence_is_gold($idv_fiche);
$idv_theme   = $idv_fiche ? (string) get_post_meta($idv_fiche->ID, '_idc_dashboard_theme', true) : '';
$idv_color   = $idv_fiche ? (string) get_post_meta($idv_fiche->ID, '_idc_dashboard_color', true) : '';

$idv_themes = [
    'forest'  => ['fa-tree', 'Forêt', 'Vert sapin (défaut)', '#2D5F4E'],
    'ocean'   => ['fa-water', 'Océan', 'Bleu profond', '#1E40AF'],
    'rose'    => ['fa-heart', 'Rose', 'Rose pâle', '#EC4899'],
    'sunset'  => ['fa-sun', 'Coucher de soleil', 'Orange chaleureux', '#F97316'],
    'elegant' => ['fa-moon', 'Élégant', 'Noir et blanc', '#1F2937'],
    'purple'  => ['fa-gem', 'Violet', 'Violet moderne', '#7C3AED'],
    'solar'   => ['fa-bolt-lightning', 'Solaire', 'Jaune doré', '#EAB308'],
];
?>
<style>
  .theme-card { position:relative; display:flex; flex-direction:column; align-items:center; gap:8px; padding:22px 16px; border:2px solid #e5e7eb; border-radius:16px; background:#fff; cursor:pointer; transition:all .2s; text-align:center; }
  .theme-card:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(0,0,0,.06); }
  .theme-card input[type=radio] { position:absolute; opacity:0; pointer-events:none; }
  .theme-card.active { border-color:#207752; box-shadow:0 0 0 3px rgba(32,119,82,.15); }
  .theme-card__icon { font-size:28px; }
  .theme-card__name { font-weight:700; font-size:14px; color:#111; font-family:'Newsreader',serif; }
  .theme-card__desc { font-size:11px; color:#6b7280; }
  .theme-card.active::after { content:''; position:absolute; top:8px; right:8px; width:18px; height:18px; border-radius:50%; background:#207752; box-shadow:0 0 0 4px #fff inset,0 0 0 6px #207752; }
</style>

<main class="md:ml-72 min-h-screen p-4 sm:p-6 md:p-8 pt-24 md:pt-28 bg-background">
  <div class="max-w-5xl mx-auto">

    <header class="mb-10">
      <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-on-surface mb-2" style="font-family:'Newsreader',serif;">
        <i class="fa-solid fa-palette text-primary mr-2"></i>
        Personnalisation <span style="font-family:'Newsreader',serif;font-style:italic;">de votre espace</span>
      </h1>
      <p class="text-on-surface-variant max-w-2xl">Choisissez le thème de couleurs qui vous correspond. Le changement s'applique à votre dashboard artisan.</p>
    </header>

    <?php if (!$idv_is_gold) : ?>
      <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-8 text-center">
        <i class="fa-solid fa-lock text-amber-600 mb-3" style="font-size:48px"></i>
        <h2 class="font-headline text-2xl mb-3">La personnalisation du thème est réservée au plan Gold</h2>
        <p class="text-on-surface-variant text-sm max-w-md mx-auto mb-6">Avec un abonnement <strong>Gold (14€/mois)</strong>, débloquez 7 ambiances prédéfinies ainsi qu'un sélecteur de couleur personnalisée pour adapter votre dashboard à votre identité visuelle.</p>
        <ul class="text-sm text-on-surface-variant max-w-md mx-auto mb-6 space-y-2 text-left">
          <li class="flex items-start gap-2"><i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0" style="font-size:12px;"></i><span>7 thèmes prédéfinis (Forêt, Océan, Rose, Coucher de soleil, Élégant, Violet, Solaire)</span></li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0" style="font-size:12px;"></i><span>Couleur d'accent personnalisée (#hex de votre choix)</span></li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0" style="font-size:12px;"></i><span>Identité visuelle cohérente avec votre marque</span></li>
        </ul>
        <a href="<?php echo esc_url(home_url('/dashboard/artisan/abonnement/')); ?>" class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest hover:opacity-90 transition-all"><i class="fa-solid fa-arrow-up"></i> Passer en Gold</a>
      </div>
    <?php else : ?>

    <section class="bg-white p-5 sm:p-8 rounded-2xl border border-outline-variant/15 mb-8">
      <h2 class="text-2xl font-bold mb-2" style="font-family:'Newsreader',serif;">Thèmes prédéfinis</h2>
      <p class="text-sm text-on-surface-variant mb-6">7 ambiances pour adapter votre interface à votre identité.</p>

      <form id="theme-form" onsubmit="event.preventDefault(); saveTheme();">
        <input type="hidden" name="idc_apparence_nonce" value="<?php echo esc_attr(wp_create_nonce('idc_apparence')); ?>">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
          <?php foreach ($idv_themes as $slug => [$icon, $name, $desc, $color]) :
              $active = ($idv_theme === $slug) || ($idv_theme === '' && $slug === 'forest'); ?>
            <label class="theme-card <?php echo $active ? 'active' : ''; ?>" data-theme-card="<?php echo esc_attr($slug); ?>">
              <input type="radio" name="theme" value="<?php echo esc_attr($slug); ?>" <?php checked($active); ?>>
              <i class="fa-solid <?php echo esc_attr($icon); ?> theme-card__icon" style="color:<?php echo esc_attr($color); ?>;"></i>
              <span class="theme-card__name"><?php echo esc_html($name); ?></span>
              <small class="theme-card__desc"><?php echo esc_html($desc); ?></small>
            </label>
          <?php endforeach; ?>
        </div>

        <div class="mt-8 pt-6 border-t border-outline-variant/10">
          <label class="block text-xs uppercase tracking-widest text-on-surface-variant font-bold mb-3"><i class="fa-solid fa-eye-dropper mr-1"></i> Couleur personnalisée (optionnel)</label>
          <div class="flex items-center gap-3 flex-wrap">
            <input type="color" name="custom_color" id="custom-color" value="<?php echo esc_attr($idv_color ?: '#207752'); ?>" style="width:48px;height:48px;border:none;cursor:pointer;border-radius:8px;">
            <p class="text-xs text-on-surface-variant">Définissez votre couleur primaire personnalisée. Surcharge le thème sélectionné.</p>
            <button type="button" onclick="document.getElementById('custom-color').value='#207752';" class="text-xs text-primary font-bold hover:underline">Réinitialiser</button>
          </div>
        </div>

        <div class="mt-8 flex items-center gap-3 flex-wrap">
          <button type="submit" class="bg-primary text-on-primary px-6 py-3 rounded-xl text-sm uppercase tracking-widest font-bold hover:opacity-90 inline-flex items-center gap-2"><i class="fa-solid fa-floppy-disk"></i> Enregistrer mes préférences</button>
          <span id="theme-msg" class="text-sm"></span>
        </div>
      </form>
    </section>

    <section class="bg-white p-6 rounded-2xl border border-outline-variant/15">
      <h3 class="text-sm uppercase tracking-widest text-on-surface-variant font-bold mb-4"><i class="fa-solid fa-eye mr-1"></i> Aperçu en direct</h3>
      <p class="text-xs text-on-surface-variant">Les éléments primaires (boutons, liens, surlignages) utilisent la couleur du thème sélectionné dans tout votre dashboard.</p>
    </section>

    <?php endif; ?>
  </div>
</main>

<script>
  document.querySelectorAll('[data-theme-card]').forEach(function (card) {
    card.addEventListener('click', function () {
      document.querySelectorAll('.theme-card').forEach(function (c) { c.classList.remove('active'); });
      card.classList.add('active');
    });
  });
  async function saveTheme() {
    const msg = document.getElementById('theme-msg');
    msg.textContent = '';
    const fd = new FormData(document.getElementById('theme-form'));
    fd.append('action', 'idc_artisan_apparence_save');
    try {
      const res = await fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
      const data = await res.json();
      if (data.success) {
        msg.innerHTML = '<i class="fa-solid fa-circle-check text-emerald-600"></i> Préférences enregistrées ! Rechargement...';
        msg.className = 'text-sm text-emerald-700';
        setTimeout(function () { window.location.reload(); }, 700);
      } else {
        msg.textContent = data.error || 'Erreur';
        msg.className = 'text-sm text-red-600';
      }
    } catch (e) { msg.textContent = 'Erreur réseau'; msg.className = 'text-sm text-red-600'; }
  }
</script>

<?php get_footer(); ?>
