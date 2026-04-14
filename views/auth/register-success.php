<?php /* views/auth/register-success.php */ ?>
<main class="min-h-screen flex items-center justify-center px-6 py-24">
  <div class="w-full max-w-md text-center">
    <div class="text-6xl mb-6">🎉</div>
    <h1 class="font-headline text-4xl italic text-on-surface mb-4">Inscription réussie !</h1>
    <p class="text-on-surface-variant mb-2">
      Un email de confirmation a été envoyé à <strong><?= Security::e($email ?? '') ?></strong>.
    </p>
    <p class="text-on-surface-variant mb-8">Cliquez sur le lien dans l'email pour activer votre compte.</p>
    <p class="text-xs text-on-surface-variant italic mb-8">
      Si vous êtes en local, votre compte est déjà activé — connectez-vous directement.
    </p>
    <a href="<?= APP_URL ?>/connexion" class="bg-primary text-on-primary px-8 py-3 rounded-xl font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all inline-block">
      Se connecter
    </a>
  </div>
</main>