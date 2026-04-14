<?php /* views/auth/forgot.php */ ?>
<main class="min-h-screen flex items-center justify-center px-6 py-24">
<div class="w-full max-w-md">

  <div class="text-center mb-12">
    <h1 class="font-headline text-5xl italic text-on-surface mb-3">Mot de passe oublié</h1>
    <p class="text-on-surface-variant">Entrez votre email pour recevoir un lien de réinitialisation.</p>
  </div>

  <?php if (!empty($error)): ?>
  <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm"><?= Security::e($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= APP_URL ?>/mot-de-passe-oublie" class="space-y-6">
    <?= Security::csrfField() ?>
    <div>
      <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold block mb-2">Email</label>
      <input type="email" name="email" required autofocus
             value="<?= Security::e($_POST['email'] ?? '') ?>"
             placeholder="votre@email.fr"
             class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body text-on-surface">
    </div>
    <button type="submit"
            class="w-full bg-primary text-on-primary py-4 rounded-xl font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all">
      Envoyer le lien
    </button>
    <p class="text-center text-sm text-on-surface-variant">
      <a href="<?= APP_URL ?>/connexion" class="text-primary hover:underline font-semibold">← Retour à la connexion</a>
    </p>
  </form>

</div>
</main>
