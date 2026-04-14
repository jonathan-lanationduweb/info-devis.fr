<?php /* views/home/devis-contact.php — Étape 2 : Vos coordonnées */ ?>

<div class="flex flex-1 pt-24">

  <!-- Sidebar progression -->
  <aside class="hidden lg:flex flex-col w-72 h-screen p-10 pt-32 fixed left-0 top-0 bg-background border-r border-outline-variant/15">
    <div class="mb-10">
      <h3 class="font-headline italic text-2xl text-primary mb-1">Progression</h3>
      <p class="font-label text-xs text-secondary uppercase tracking-widest">Étape 2 sur 3</p>
    </div>
    <nav class="space-y-8">
      <div class="flex items-center gap-4 text-secondary font-label text-sm">
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
    <div class="mt-auto pb-10">
      <a href="<?= APP_URL ?>/contact" class="flex items-center gap-2 text-primary font-bold text-sm hover:translate-x-1 transition-all">
        <span class="material-symbols-outlined text-lg">help_outline</span>
        Besoin d'aide ?
      </a>
    </div>
  </aside>

  <main class="flex-1 lg:ml-72 flex flex-col lg:flex-row gap-12 px-6 py-12 md:px-12 lg:px-24">
    <div class="flex-1 max-w-2xl">

      <header class="mb-12">
        <h1 class="font-headline text-5xl md:text-6xl text-on-surface leading-tight mb-4">
          Parlons de <span class="italic text-primary">vous.</span>
        </h1>
        <p class="text-secondary text-lg max-w-md">
          Pour vous mettre en relation avec les meilleurs artisans, nous avons besoin de quelques informations de contact.
        </p>
      </header>

      <?php if (!empty($errors)): ?>
        <div class="mb-8 p-4 border-l-4 border-red-400 bg-red-50 space-y-1">
          <?php foreach ($errors as $e): ?>
            <p class="text-sm text-red-700"><?= Security::e($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form action="<?= APP_URL ?>/devis" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-10">
        <?= Security::csrfField() ?>
        <input type="hidden" name="step" value="2"/>
        <!-- Données étape 1 transmises -->
        <input type="hidden" name="category_id" value="<?= (int)($devisData['category_id'] ?? 0) ?>"/>
        <input type="hidden" name="description"  value="<?= Security::e($devisData['description'] ?? '') ?>"/>
        <input type="hidden" name="urgency"       value="<?= Security::e($devisData['urgency'] ?? 'normal') ?>"/>
        <input type="hidden" name="budget"        value="<?= Security::e($devisData['budget'] ?? 'non_defini') ?>"/>

        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Prénom *</label>
          <input type="text" name="first_name" required placeholder="Jean"
                 class="w-full bg-surface-container border-none focus:ring-0 focus:border-primary p-4 rounded-lg font-body text-on-surface"
                 value="<?= Security::e($_POST['first_name'] ?? '') ?>"/>
        </div>

        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Nom</label>
          <input type="text" name="last_name" placeholder="Dupont"
                 class="w-full bg-surface-container border-none focus:ring-0 focus:border-primary p-4 rounded-lg font-body text-on-surface"
                 value="<?= Security::e($_POST['last_name'] ?? '') ?>"/>
        </div>

        <div class="space-y-2 md:col-span-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Email *</label>
          <input type="email" name="email" required placeholder="jean.dupont@exemple.fr"
                 class="w-full bg-surface-container border-none focus:ring-0 focus:border-primary p-4 rounded-lg font-body text-on-surface"
                 value="<?= Security::e($_POST['email'] ?? '') ?>"/>
        </div>

        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Téléphone</label>
          <input type="tel" name="phone" placeholder="06 12 34 56 78"
                 class="w-full bg-surface-container border-none focus:ring-0 focus:border-primary p-4 rounded-lg font-body text-on-surface"
                 value="<?= Security::e($_POST['phone'] ?? '') ?>"/>
        </div>

        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Ville *</label>
          <input type="text" name="ville" required placeholder="Paris"
                 class="w-full bg-surface-container border-none focus:ring-0 focus:border-primary p-4 rounded-lg font-body text-on-surface"
                 value="<?= Security::e($_POST['ville'] ?? '') ?>"/>
        </div>

        <div class="space-y-2 md:col-span-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Code postal</label>
          <input type="text" name="code_postal" placeholder="75001"
                 class="w-full bg-surface-container border-none focus:ring-0 focus:border-primary p-4 rounded-lg font-body text-on-surface"
                 value="<?= Security::e($_POST['code_postal'] ?? '') ?>"/>
        </div>

        <div class="md:col-span-2 pt-6 flex gap-4 items-center">
          <a href="<?= APP_URL ?>/devis" class="text-secondary font-label text-sm hover:text-primary transition-colors flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Retour
          </a>
          <button type="submit"
                  class="bg-primary text-on-primary px-10 py-5 rounded-xl font-bold text-lg shadow-2xl shadow-primary/20 hover:-translate-y-1 transition-all active:scale-95">
            Envoyer ma demande gratuite →
          </button>
        </div>
        <p class="md:col-span-2 text-[11px] text-secondary italic">
          * Champs obligatoires. Vos données sont traitées avec le plus grand soin.
        </p>
      </form>
    </div>

    <!-- Sidebar confiance -->
    <?php include __DIR__ . '/../partials/devis-trust.php'; ?>
  </main>
</div>
