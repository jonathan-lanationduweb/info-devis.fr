<?php /* views/home/devis.php — Formulaire complet */ ?>

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
      <?php foreach (
        [
          ['verified', 'Artisans certifiés'],
          ['lock', 'Données sécurisées'],
          ['euro', '100% gratuit'],
          ['schedule', 'Réponse rapide'],
        ] as [$icon, $text]
      ): ?>
        <div class="flex items-center gap-3 text-sm text-secondary">
          <span class="material-symbols-outlined text-primary text-base"><?= $icon ?></span>
          <?= $text ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-auto pb-10">
      <a href="<?= APP_URL ?>/contact" class="flex items-center gap-2 text-primary font-bold text-sm hover:translate-x-1 transition-all">
        <span class="material-symbols-outlined text-lg">help_outline</span>
        Besoin d'aide ?
      </a>
    </div>
  </aside>

  <!-- Contenu principal -->
  <main class="flex-1 lg:ml-72 px-6 py-12 md:px-12 lg:px-24">
    <div class="max-w-2xl mx-auto">

      <header class="mb-12">
        <h1 class="font-headline text-5xl md:text-6xl text-on-surface leading-tight mb-4">
          Décrivez votre <span class="italic text-primary">projet.</span>
        </h1>
        <p class="text-secondary text-lg max-w-md">
          Recevez jusqu'à 5 devis d'artisans qualifiés rapidement.
        </p>
      </header>

      <!-- Erreurs -->
      <?php if (!empty($errors)): ?>
        <div class="mb-8 p-5 border-l-4 border-red-400 bg-red-50 rounded-r-xl">
          <p class="font-label text-xs uppercase tracking-widest text-red-600 font-bold mb-2">Veuillez corriger les erreurs suivantes :</p>
          <ul class="space-y-1">
            <?php foreach ($errors as $err): ?>
              <li class="text-sm text-red-700 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">error</span>
                <?= Security::e($err) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form id="devis-form" action="<?= APP_URL ?>/devis" method="POST" class="space-y-10">
        <?= Security::csrfField() ?>

        <!-- ── SECTION 1 : Votre projet ── -->
        <div class="space-y-8">
          <div class="flex items-center gap-3 pb-3 border-b border-outline-variant/20">
            <span class="w-7 h-7 bg-primary text-on-primary rounded-full flex items-center justify-center font-bold text-sm">1</span>
            <h2 class="font-headline text-2xl text-on-surface">Votre projet</h2>
          </div>

          <!-- Catégorie -->
          <div class="space-y-2">
            <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Type de travaux *</label>
            <select name="category_id"
              class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface" required>
              <option value="">-- Sélectionnez un métier --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= (($_POST['category_id'] ?? $catId) == $cat['id']) ? 'selected' : '' ?>>
                  <?= Security::e($cat['name']) ?>
                  <?php if (!empty($cat['prix_min'])): ?>
                    (à partir de <?= number_format($cat['prix_min'], 0, ',', ' ') ?> €)
                  <?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Description -->
          <div class="space-y-2">
            <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Décrivez votre projet *</label>
            <textarea name="description" rows="4" required
              class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface resize-none"
              placeholder="Détaillez vos besoins pour obtenir une estimation précise..."><?= Security::e($_POST['description'] ?? '') ?></textarea>
          </div>

          <!-- Urgence + Budget -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-3">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Urgence</label>
              <?php foreach (['normal' => 'Normal', 'urgent' => 'Urgent', 'tres_urgent' => 'Très urgent'] as $val => $lbl): ?>
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input type="radio" name="urgency" value="<?= $val ?>"
                    class="w-5 h-5 text-primary border-outline-variant focus:ring-primary"
                    <?= (($_POST['urgency'] ?? 'normal') === $val) ? 'checked' : '' ?>>
                  <span class="text-on-surface group-hover:text-primary transition-colors"><?= $lbl ?></span>
                </label>
              <?php endforeach; ?>
            </div>
            <div class="space-y-3">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Budget approximatif</label>
              <?php foreach (['non_defini' => 'Non défini', 'moins_500' => '< 500 €', '500_1000' => '500 – 1 000 €', '1000_5000' => '1 000 – 5 000 €', 'plus_5000' => '> 5 000 €'] as $val => $lbl): ?>
                <label class="flex items-center gap-3 cursor-pointer group">
                  <input type="radio" name="budget" value="<?= $val ?>"
                    class="w-5 h-5 text-primary border-outline-variant focus:ring-primary"
                    <?= (($_POST['budget'] ?? 'non_defini') === $val) ? 'checked' : '' ?>>
                  <span class="text-sm text-on-surface group-hover:text-primary transition-colors"><?= $lbl ?></span>
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

          <!-- Nom + Prénom -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Prénom *</label>
              <input type="text" name="first_name" required
                value="<?= Security::e($_POST['first_name'] ?? '') ?>"
                placeholder="Jean"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Nom</label>
              <input type="text" name="last_name"
                value="<?= Security::e($_POST['last_name'] ?? '') ?>"
                placeholder="Dupont"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
          </div>

          <!-- Email + Téléphone -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Email *</label>
              <input type="email" name="email" required
                value="<?= Security::e($_POST['email'] ?? '') ?>"
                placeholder="jean@example.com"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Téléphone</label>
              <input type="tel" name="phone"
                value="<?= Security::e($_POST['phone'] ?? '') ?>"
                placeholder="06 00 00 00 00"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
          </div>

          <!-- Ville + Code postal -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Ville *</label>
              <input type="text" name="ville" required
                value="<?= Security::e($_POST['ville'] ?? '') ?>"
                placeholder="Paris"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Code postal</label>
              <input type="text" name="code_postal"
                value="<?= Security::e($_POST['code_postal'] ?? '') ?>"
                placeholder="75001"
                class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
            </div>
          </div>
        </div>

        <!-- ── SECTION 3 : Consentement & Envoi ── -->
        <div class="space-y-6 pt-4 border-t border-outline-variant/20">

          <label class="flex items-start gap-3 cursor-pointer group">
            <input type="checkbox" name="consent_privacy" value="1" required
              class="mt-1 w-5 h-5 text-primary border-outline-variant focus:ring-primary rounded flex-shrink-0"
              <?= !empty($_POST['consent_privacy']) ? 'checked' : '' ?>>
            <span class="text-sm text-on-surface-variant leading-relaxed">
              J'accepte que mes données soient transmises à des artisans qualifiés pour répondre à ma demande.
              <a href="<?= APP_URL ?>/confidentialite" class="text-primary hover:underline">Politique de confidentialité</a> *
            </span>
          </label>

          <label class="flex items-start gap-3 cursor-pointer group">
            <input type="checkbox" name="consent_marketing" value="1"
              class="mt-1 w-5 h-5 text-primary border-outline-variant focus:ring-primary rounded flex-shrink-0"
              <?= !empty($_POST['consent_marketing']) ? 'checked' : '' ?>>
            <span class="text-sm text-on-surface-variant leading-relaxed">
              J'accepte de recevoir des conseils et offres d'InfoDevis par email (optionnel).
            </span>
          </label>

          <button type="submit" id="submit-btn"
            class="w-full bg-primary text-on-primary px-10 py-5 rounded-xl font-bold text-lg shadow-2xl shadow-primary/20 hover:-translate-y-1 transition-all active:scale-95 flex items-center justify-center gap-3">
            <span>Envoyer ma demande</span>
            <span class="material-symbols-outlined text-xl">send</span>
          </button>

          <p class="text-center text-[10px] text-secondary italic">
            Gratuit, sans engagement. Vos données ne sont jamais vendues.
          </p>
        </div>

      </form>
    </div>
  </main>
</div>

<script>
  // Attendre que le DOM soit prêt
  window.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('devis-form');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      e.stopPropagation();

      const btn = document.getElementById('submit-btn');
      if (!btn) return;

      btn.disabled = true;
      btn.innerHTML = '<span>Envoi en cours...</span><span class="material-symbols-outlined text-xl">hourglass_empty</span>';

      try {
        const res = await fetch('<?= APP_URL ?>/devis', {
          method: 'POST',
          body: new FormData(this),
        });
        const data = await res.json();

        if (data.success) {
          // Succès — redirection
          window.location.href = data.redirect || '<?= APP_URL ?>/devis/confirmation';
        } else {
          // Afficher les erreurs
          const errDiv = document.createElement('div');
          errDiv.className = 'mb-8 p-5 border-l-4 border-red-400 bg-red-50 rounded-r-xl';
          errDiv.innerHTML = '<p class="font-label text-xs uppercase tracking-widest text-red-600 font-bold mb-2">Erreurs :</p>' +
            (data.errors || ['Erreur inconnue']).map(e =>
              `<p class="text-sm text-red-700">• ${e}</p>`
            ).join('');

          const existing = document.querySelector('.border-red-400');
          if (existing) existing.remove();
          this.before(errDiv);
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });

          btn.disabled = false;
          btn.innerHTML = '<span>Envoyer ma demande</span><span class="material-symbols-outlined text-xl">send</span>';
        }
      } catch (err) {
        btn.disabled = false;
        btn.innerHTML = '<span>Envoyer ma demande</span><span class="material-symbols-outlined text-xl">send</span>';
        alert('Erreur réseau. Réessayez.');
      }
    }); // fin submit
  }); // fin DOMContentLoaded
</script>