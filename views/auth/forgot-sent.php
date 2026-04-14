<?php /* views/auth/forgot-sent.php */ ?>
<main class="min-h-screen flex items-center justify-center px-6 py-24">
<div class="w-full max-w-md text-center">
  <div class="text-6xl mb-6">📧</div>
  <h1 class="font-headline text-4xl italic text-on-surface mb-4">Email envoyé !</h1>
  <p class="text-on-surface-variant mb-8">Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation dans quelques minutes.</p>
  <a href="<?= APP_URL ?>/connexion" class="bg-primary text-on-primary px-8 py-3 rounded-xl font-label font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all inline-block">
    Retour à la connexion
  </a>
</div>
</main>
