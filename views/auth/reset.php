<?php /* views/auth/reset.php */ ?>
<main class="min-h-screen flex items-center justify-center px-6 py-24">
<div class="w-full max-w-md">

  <div class="text-center mb-12">
    <h1 class="font-headline text-5xl italic text-on-surface mb-3">Nouveau mot de passe</h1>
    <p class="text-on-surface-variant">Choisissez un nouveau mot de passe sécurisé.</p>
  </div>

  <?php if (!empty($error)): ?>
  <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm"><?= Security::e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= APP_URL ?>/reset-password" class="space-y-6">
    <?= Security::csrfField() ?>
    <input type="hidden" name="token" value="<?= Security::e($token ?? '') ?>">
    <div>
      <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold block mb-2">Nouveau mot de passe</label>
      <input type="password" name="password" required minlength="8" autofocus
             placeholder="8 caractères minimum"
             class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
    </div>
    <button type="submit"
            class="w-full bg-primary text-on-primary py-4 rounded-xl font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all">
      Réinitialiser le mot de passe
    </button>
    <p class="text-center text-sm text-on-surface-variant">
      <a href="<?= APP_URL ?>/connexion" class="text-primary hover:underline font-semibold">← Retour à la connexion</a>
    </p>
  </form>

</div>
</main>
