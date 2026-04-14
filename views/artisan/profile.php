<?php /* views/artisan/profile.php */ ?>
<?php include __DIR__ . '/_sidebar.php'; ?>

<main class="lg:ml-72 pt-32 pb-20 px-8 max-w-7xl mx-auto">

  <header class="mb-12">
    <h1 class="text-5xl font-headline font-medium tracking-tight mb-2">Mon profil</h1>
    <p class="text-stone-500 font-body text-lg italic font-headline">Gérez votre identité d'artisan et vos domaines d'expertise.</p>
  </header>

  <?php if (isset($_GET['saved'])): ?>
    <div class="mb-8 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl font-semibold text-sm">
      ✅ Profil mis à jour avec succès !
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= APP_URL ?>/dashboard/artisan/profile" enctype="multipart/form-data" class="space-y-12">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

      <!-- Photo profil -->
      <div class="lg:col-span-4 bg-surface-container-low p-8 rounded-xl flex flex-col items-center text-center">
        <h3 class="text-xl mb-6 self-start font-label text-xs tracking-widest text-on-surface-variant font-bold uppercase">Photo de profil</h3>
        <div class="relative group cursor-pointer mb-6" onclick="document.getElementById('avatar-input').click()">
          <div class="w-48 h-48 rounded-full overflow-hidden border-4 border-surface shadow-xl bg-surface-container-high flex items-center justify-center">
            <?php if (!empty($artisan['avatar'])): ?>
              <img src="<?= APP_URL ?>/<?= Security::e($artisan['avatar']) ?>" alt="Avatar" class="w-full h-full object-cover">
            <?php else: ?>
              <span class="material-symbols-outlined text-6xl text-outline-variant">account_circle</span>
            <?php endif; ?>
          </div>
          <div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-full">
            <span class="material-symbols-outlined text-white text-3xl">photo_camera</span>
          </div>
        </div>
        <input type="file" name="avatar" id="avatar-input" accept="image/*" class="hidden">
        <button type="button" onclick="document.getElementById('avatar-input').click()"
          class="text-primary font-label text-xs font-bold uppercase tracking-widest hover:underline">
          Changer la photo
        </button>
        <p class="mt-4 text-stone-400 text-xs italic font-headline">Format recommandé: 800×800px</p>
      </div>

      <!-- Informations entreprise -->
      <div class="lg:col-span-8 bg-surface-container p-8 lg:p-12 rounded-xl">
        <h3 class="text-xl mb-8 font-label text-xs tracking-widest text-on-surface-variant font-bold uppercase">Informations entreprise</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="flex flex-col gap-2">
            <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold">Nom de l'entreprise</label>
            <input type="text" name="company_name" value="<?= Security::e($artisan['company_name'] ?? '') ?>"
              class="bg-surface-container-lowest border-0 border-b-2 border-transparent focus:border-primary focus:ring-0 px-0 py-3 text-lg font-headline italic transition-all">
          </div>
          <div class="flex flex-col gap-2">
            <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold">Rayon d'intervention (km)</label>
            <input type="number" name="radius_km" value="<?= (int)($artisan['radius_km'] ?? 30) ?>" min="5" max="200"
              class="bg-surface-container-lowest border-0 border-b-2 border-transparent focus:border-primary focus:ring-0 px-0 py-3 text-lg font-headline italic transition-all">
          </div>
          <div class="flex flex-col gap-2">
            <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold">Ville</label>
            <input type="text" name="ville" value="<?= Security::e($artisan['ville'] ?? '') ?>"
              class="bg-surface-container-lowest border-0 border-b-2 border-transparent focus:border-primary focus:ring-0 px-0 py-3 text-lg font-headline italic transition-all">
          </div>
          <div class="flex flex-col gap-2">
            <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold">Code postal</label>
            <input type="text" name="code_postal" value="<?= Security::e($artisan['code_postal'] ?? '') ?>"
              class="bg-surface-container-lowest border-0 border-b-2 border-transparent focus:border-primary focus:ring-0 px-0 py-3 text-lg font-headline italic transition-all">
          </div>
          <div class="flex flex-col gap-2 md:col-span-2">
            <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold">Description</label>
            <textarea name="description" rows="4"
              class="bg-surface-container-lowest border-0 border-b-2 border-transparent focus:border-primary focus:ring-0 px-0 py-3 text-base font-body transition-all resize-none"
              placeholder="Décrivez votre entreprise et vos spécialités..."><?= Security::e($artisan['description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Mes métiers -->
    <div class="bg-surface-container-high p-8 lg:p-12 rounded-xl">
      <div class="flex justify-between items-center mb-10">
        <div>
          <h3 class="font-label text-xs tracking-widest text-on-surface-variant font-bold uppercase">Mes métiers</h3>
          <p class="text-stone-500 font-headline italic mt-1">Sélectionnez vos domaines de compétences.</p>
        </div>
        <span class="material-symbols-outlined text-primary-dim text-4xl opacity-20">construction</span>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($categories as $cat): ?>
          <label class="relative bg-surface p-6 rounded-lg cursor-pointer hover:bg-primary-container/30 transition-all border border-outline-variant/10 has-[:checked]:border-primary has-[:checked]:bg-primary-container/20">
            <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>"
              <?= in_array($cat['id'], $myCategories ?? []) ? 'checked' : '' ?>
              class="absolute right-4 top-4 h-5 w-5 text-primary border-outline-variant rounded focus:ring-primary">
            <span class="material-symbols-outlined text-primary mb-3 block text-2xl">home_repair_service</span>
            <span class="block font-label text-sm font-bold uppercase tracking-wider"><?= Security::e($cat['name']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Bouton save -->
    <div class="flex justify-end pt-12">
      <button type="submit"
        class="bg-primary text-on-primary px-12 py-5 rounded-lg flex items-center gap-3 hover:shadow-2xl hover:shadow-primary/20 transition-all group active:opacity-70">
        <span class="font-label font-extrabold uppercase tracking-widest text-sm">Sauvegarder les modifications</span>
        <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
      </button>
    </div>
  </form>

  <!-- Citation -->
  <section class="mt-24 pt-16 border-t border-outline-variant/10">
    <div class="bg-surface-container-highest p-12 rounded-2xl relative overflow-hidden">
      <span class="absolute -top-10 -left-6 text-[15rem] leading-none text-on-surface opacity-5 font-headline">"</span>
      <div class="relative z-10 max-w-2xl">
        <p class="text-3xl font-headline italic text-on-surface leading-tight mb-6">
          "Votre profil est le reflet de votre excellence. Un profil complet augmente de 40% vos chances d'être sélectionné."
        </p>
        <div class="flex items-center gap-4">
          <div class="w-12 h-px bg-primary"></div>
          <span class="font-label text-xs font-bold uppercase tracking-widest text-primary">L'équipe éditoriale InfoDevis</span>
        </div>
      </div>
    </div>
  </section>

</main>