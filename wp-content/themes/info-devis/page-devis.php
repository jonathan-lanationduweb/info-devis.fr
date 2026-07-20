<?php
/**
 * Formulaire de demande de devis — reproduction fidèle de views/home/devis.php.
 * Rendu : thème. Logique : extension (action idc_submit_devis2, AJAX JSON).
 */

get_header();

$idv_terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0, 'orderby' => 'name', 'order' => 'ASC']);
if (is_wp_error($idv_terms)) {
    $idv_terms = [];
}

// Pré-sélection (?metier=slug depuis la recherche héro / pages catégories) + ville.
$idv_pre_slug  = sanitize_title($_GET['metier'] ?? ($_GET['categorie'] ?? ''));
$idv_pre_ville = sanitize_text_field(wp_unslash($_GET['ville'] ?? ''));
// Pré-sélection multiple (?categories=slug1,slug2 depuis l'assistant/chatbot).
$idv_pre_multi = array_filter(array_map('sanitize_title', explode(',', (string) ($_GET['categories'] ?? ''))));
?>

<div class="flex flex-1 pt-24">

  <!-- Sidebar progression -->
  <aside class="hidden lg:flex flex-col w-72 h-screen p-10 pt-32 fixed left-0 top-0 bg-background border-r border-outline-variant/15">
    <div class="mb-10">
      <h3 class="font-headline italic text-2xl text-primary mb-1">Votre devis</h3>
      <p class="font-label text-xs text-secondary uppercase tracking-widest">Gratuit & sans engagement</p>
    </div>
    <nav class="space-y-8">
      <div class="flex items-center gap-4 text-primary font-bold border-r-2 border-primary pr-4 font-label text-sm">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">architecture</span>
        <span>Votre projet</span>
      </div>
      <div class="flex items-center gap-4 text-primary font-bold border-r-2 border-primary pr-4 font-label text-sm">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">person</span>
        <span>Vos coordonnées</span>
      </div>
      <div class="flex items-center gap-4 text-secondary font-label text-sm">
        <span class="material-symbols-outlined">check_circle</span>
        <span>Confirmation</span>
      </div>
    </nav>
    <!-- Garanties -->
    <div class="mt-12 space-y-4">
      <?php foreach ([
          ['verified', 'Artisans certifiés'],
          ['lock', 'Données sécurisées'],
          ['euro', '100% gratuit'],
          ['schedule', 'Réponse rapide'],
      ] as [$idv_icon, $idv_text]) : ?>
        <div class="flex items-center gap-3 text-sm text-secondary">
          <span class="material-symbols-outlined text-primary text-base"><?php echo esc_html($idv_icon); ?></span>
          <?php echo esc_html($idv_text); ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-auto pb-10">
      <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="flex items-center gap-2 text-primary font-bold text-sm hover:translate-x-1 transition-all">
        <span class="material-symbols-outlined text-lg">help_outline</span>
        Besoin d'aide ?
      </a>
    </div>
  </aside>

  <!-- Contenu principal -->
  <div class="flex-1 lg:ml-72 px-6 py-12 md:px-12 lg:px-24">
    <div class="max-w-2xl mx-auto">

      <header class="mb-12">
        <h1 class="font-headline text-5xl md:text-6xl text-on-surface leading-tight mb-4">
          Décrivez votre <span class="italic text-primary">projet.</span>
        </h1>
        <p class="text-secondary text-lg max-w-md">
          Recevez jusqu'à 5 devis d'artisans qualifiés rapidement.
        </p>
      </header>

      <form id="devis-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" class="space-y-10">
        <input type="hidden" name="action" value="idc_submit_devis2">
        <input type="hidden" name="idc_ajax" id="idc-ajax-flag" value="">
        <?php wp_nonce_field('idc_devis_form', 'idc_devis_nonce_front'); ?>
        <p class="idc-hp-field" aria-hidden="true" style="position:absolute;left:-9999px;"><label>Ne pas remplir<input type="text" name="idc_website" tabindex="-1" autocomplete="off"></label></p>

        <!-- ── SECTION 1 : Votre projet ── -->
        <div class="space-y-8">
          <div class="flex items-center gap-3 pb-3 border-b border-outline-variant/20">
            <span class="w-7 h-7 bg-primary text-on-primary rounded-full flex items-center justify-center font-bold text-sm">1</span>
            <h2 class="font-headline text-2xl text-on-surface">Votre projet</h2>
          </div>

          <!-- Catégories : multi-sélection -->
          <div class="space-y-3">
            <div class="flex items-center justify-between gap-2 flex-wrap">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">
                Type de travaux * <span class="normal-case text-[10px] text-on-surface-variant font-normal italic">(sélectionnez un ou plusieurs métiers)</span>
              </label>
              <span id="cat-counter" class="text-xs text-primary font-bold hidden"></span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
              <?php foreach ($idv_terms as $idv_term) :
                  $idv_checked = ($idv_term->slug === $idv_pre_slug) || in_array($idv_term->slug, $idv_pre_multi, true);
              ?>
                <label class="cat-tile flex items-center gap-2 cursor-pointer bg-surface-container hover:bg-primary/5 p-3 rounded-xl border-2 transition-all <?php echo $idv_checked ? 'border-primary bg-primary/5' : 'border-transparent'; ?>">
                  <input type="checkbox" name="categories[]" value="<?php echo esc_attr($idv_term->slug); ?>"
                         class="w-4 h-4 accent-primary" <?php checked($idv_checked); ?>>
                  <span class="text-sm font-medium text-on-surface"><?php echo esc_html($idv_term->name); ?></span>
                </label>
              <?php endforeach; ?>
            </div>
            <p id="cat-error" class="text-xs text-red-600 hidden">Sélectionnez au moins une catégorie.</p>
          </div>

          <!-- Description -->
          <div class="space-y-2">
            <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Décrivez votre projet *</label>
            <textarea name="description" rows="4" required
              class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface resize-none"
              placeholder="Détaillez vos besoins pour obtenir une estimation précise..."></textarea>
          </div>

          <!-- Urgence + Budget -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-3">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Urgence</label>
              <?php foreach (['normal' => 'Normal', 'urgent' => 'Urgent', 'tres_urgent' => 'Très urgent'] as $idv_val => $idv_lbl) : ?>
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input type="radio" name="urgency" value="<?php echo esc_attr($idv_val); ?>"
                    class="w-5 h-5 text-primary border-outline-variant focus:ring-primary"
                    <?php checked($idv_val, 'normal'); ?>>
                  <span class="text-on-surface group-hover:text-primary transition-colors"><?php echo esc_html($idv_lbl); ?></span>
                </label>
              <?php endforeach; ?>
            </div>
            <div class="space-y-3">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Budget approximatif</label>
              <?php foreach (['non_defini' => 'Non défini', 'moins_500' => '< 500 €', '500_1000' => '500 – 1 000 €', '1000_5000' => '1 000 – 5 000 €', 'plus_5000' => '> 5 000 €'] as $idv_val => $idv_lbl) : ?>
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input type="radio" name="budget" value="<?php echo esc_attr($idv_val); ?>"
                    class="w-5 h-5 text-primary border-outline-variant focus:ring-primary"
                    <?php checked($idv_val, 'non_defini'); ?>>
                  <span class="text-sm text-on-surface group-hover:text-primary transition-colors"><?php echo esc_html($idv_lbl); ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- ── SECTION 2 : Vos coordonnées ── -->
        <div class="space-y-8">
          <div class="flex items-center gap-3 pb-3 border-b border-outline-variant/20">
            <span class="w-7 h-7 bg-primary text-on-primary rounded-full flex items-center justify-center font-bold text-sm">2</span>
            <h2 class="font-headline text-2xl text-on-surface">Vos coordonnées</h2>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Prénom *</label>
              <input type="text" name="first_name" required placeholder="Jean" autocomplete="given-name"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Nom</label>
              <input type="text" name="last_name" placeholder="Dupont" autocomplete="family-name"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Email *</label>
              <input type="email" name="email" required placeholder="jean@example.com" autocomplete="email" inputmode="email"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Téléphone</label>
              <input type="tel" name="phone" placeholder="06 00 00 00 00" autocomplete="tel" inputmode="tel"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Ville *</label>
              <input type="text" name="ville" required placeholder="Paris" autocomplete="address-level2"
                value="<?php echo esc_attr($idv_pre_ville); ?>"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Code postal</label>
              <input type="text" name="code_postal" placeholder="75001" pattern="[0-9]{5}" maxlength="5" autocomplete="postal-code" inputmode="numeric"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
          </div>
        </div>

        <!-- ── SECTION 3 : Consentement & Envoi ── -->
        <div class="space-y-6 pt-4 border-t border-outline-variant/20">

          <label class="flex items-start gap-3 cursor-pointer group">
            <input type="checkbox" name="consent_privacy" value="1" required
              class="mt-1 w-5 h-5 text-primary border-outline-variant focus:ring-primary rounded flex-shrink-0">
            <span class="text-sm text-on-surface-variant leading-relaxed">
              J'accepte que mes données soient transmises à des artisans qualifiés pour répondre à ma demande.
              <a href="<?php echo esc_url(home_url('/confidentialite/')); ?>" class="text-primary hover:underline">Politique de confidentialité</a> *
            </span>
          </label>

          <label class="flex items-start gap-3 cursor-pointer group">
            <input type="checkbox" name="consent_marketing" value="1"
              class="mt-1 w-5 h-5 text-primary border-outline-variant focus:ring-primary rounded flex-shrink-0">
            <span class="text-sm text-on-surface-variant leading-relaxed">
              J'accepte de recevoir des conseils et offres d'InfoDevis par email (optionnel).
            </span>
          </label>

          <button type="submit" id="submit-btn"
            class="w-full bg-primary text-on-primary px-10 py-5 rounded-xl font-bold text-lg shadow-2xl shadow-primary/20 hover:-translate-y-1 transition-all active:scale-95 flex items-center justify-center gap-3">
            <span id="submit-label">Envoyer ma demande</span>
            <span class="material-symbols-outlined text-xl">send</span>
          </button>

          <p class="text-center text-[10px] text-secondary italic">
            Gratuit, sans engagement. Vos données ne sont jamais vendues.
          </p>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  window.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('devis-form');
    if (!form) return;

    const catCheckboxes = form.querySelectorAll('input[name="categories[]"]');
    const counter       = document.getElementById('cat-counter');
    const errEl         = document.getElementById('cat-error');

    function updateCatState() {
      const checked = Array.from(catCheckboxes).filter(c => c.checked);
      catCheckboxes.forEach(c => {
        const tile = c.closest('.cat-tile');
        if (!tile) return;
        if (c.checked) {
          tile.classList.add('border-primary', 'bg-primary/5');
          tile.classList.remove('border-transparent');
        } else {
          tile.classList.remove('border-primary', 'bg-primary/5');
          tile.classList.add('border-transparent');
        }
      });
      if (counter) {
        if (checked.length === 0)      { counter.classList.add('hidden'); }
        else if (checked.length === 1) { counter.textContent = '1 métier sélectionné'; counter.classList.remove('hidden'); }
        else                           { counter.textContent = '📋 Devis groupé · ' + checked.length + ' métiers'; counter.classList.remove('hidden'); }
      }
      if (errEl && checked.length > 0) errEl.classList.add('hidden');
      const lbl = document.getElementById('submit-label');
      if (lbl) {
        lbl.textContent = checked.length > 1
          ? '📋 Envoyer ma demande groupée (' + checked.length + ' métiers)'
          : 'Envoyer ma demande';
      }
    }
    catCheckboxes.forEach(c => c.addEventListener('change', updateCatState));
    updateCatState();

    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      e.stopPropagation();

      const checkedCats = Array.from(catCheckboxes).filter(c => c.checked);
      if (checkedCats.length === 0) {
        if (errEl) errEl.classList.remove('hidden');
        errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      const btn = document.getElementById('submit-btn');
      if (!btn) return;

      document.getElementById('idc-ajax-flag').value = '1';
      btn.disabled = true;
      btn.innerHTML = '<span>Envoi en cours...</span><span class="material-symbols-outlined text-xl">hourglass_empty</span>';

      try {
        const res  = await fetch(form.getAttribute('action'), {
          method: 'POST',
          body: new FormData(form),
          credentials: 'same-origin',
        });
        const data = await res.json();

        if (data.success) {
          window.location.href = data.redirect;
        } else {
          const errDiv = document.createElement('div');
          errDiv.className = 'mb-8 p-5 border-l-4 border-red-400 bg-red-50 rounded-r-xl';
          errDiv.innerHTML = '<p class="font-label text-xs uppercase tracking-widest text-red-600 font-bold mb-2">Veuillez corriger les erreurs suivantes :</p>' +
            (data.errors || ['Erreur inconnue']).map(m => '<p class="text-sm text-red-700">• ' + m + '</p>').join('');
          const existing = document.querySelector('.border-red-400');
          if (existing) existing.remove();
          form.before(errDiv);
          window.scrollTo({ top: 0, behavior: 'smooth' });
          btn.disabled = false;
          btn.innerHTML = '<span>Envoyer ma demande</span><span class="material-symbols-outlined text-xl">send</span>';
        }
      } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<span>Envoyer ma demande</span><span class="material-symbols-outlined text-xl">send</span>';
        alert('Erreur réseau. Réessayez.');
      }
    });
  });
</script>

<?php get_footer(); ?>
