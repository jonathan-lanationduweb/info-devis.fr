<?php
$type = $type ?? 'client';
$isArtisan = $type === 'artisan';
include __DIR__ . '/../../includes/navbar.php';
?>
<!DOCTYPE html>
<html class="light" lang="fr">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title><?= $pageTitle ?? 'Inscription | Info-Devis' ?></title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "primary": "#0e6c48",
            "on-primary": "#e1ffeb",
            "background": "#faf9f8",
            "on-background": "#2f3333",
            "surface": "#faf9f8",
            "on-surface": "#2f3333",
            "surface-container": "#edeeed",
            "surface-container-lowest": "#ffffff",
            "on-surface-variant": "#5b605f",
            "outline-variant": "#aeb3b2",
            "primary-container": "#a0f4c6",
            "on-primary-container": "#005e3d",
          },
          "borderRadius": {
            "DEFAULT": "0.125rem",
            "lg": "0.25rem",
            "xl": "0.5rem",
            "full": "0.75rem"
          },
          "fontFamily": {
            "headline": ["Newsreader", "serif"],
            "body": ["Manrope", "sans-serif"],
            "label": ["Manrope", "sans-serif"]
          }
        },
      },
    }
  </script>
  <style>
    body {
      font-family: 'Manrope', sans-serif;
      background-color: #faf9f8;
      color: #2f3333;
    }

    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
    }
  </style>
</head>

<body class="antialiased overflow-x-hidden">

  <main class="min-h-screen flex flex-col md:flex-row">

    <!-- Left Side: Visual -->
    <section class="hidden md:flex md:w-5/12 lg:w-1/2 relative bg-primary items-end p-12 overflow-hidden">
      <img alt="Artisan professionnel" class="absolute inset-0 w-full h-full object-cover opacity-80 mix-blend-multiply"
        src="<?= $isArtisan
                ? 'https://lh3.googleusercontent.com/aida-public/AB6AXuCbLjkg6ihj9IH4Vg_69GkFwwmJ7TypQJ01_Y0KhShUUxawNASRHKHdeSvuEC1XX4HTT3oXBbP5CL0FH7RtTY_IE58gcW7h2RtygrCveAbgSXT9E9OSRpelY-nfJ-NfROtRP3QsLTs24WPLLvcp9wk8csDVnUk0fZcqnT5BjHGR661Z6p1U5tZCDkiDhURoMkogIc0l1b1vXr7UOBN0Y-eUkwWH5JjMBbDIdR3b8feGpximEdfbbPJrko7TfUXNakEZBjji6kQZ15o'
                : 'https://lh3.googleusercontent.com/aida-public/AB6AXuDGDLFS-KopX5I9o8_uXVdEAwwTRkLMmUSdtSIzk-LTkr0957LgIZoGWowskcUPzEOIPmchoW7d1UP95O44EwtfuIAiqOXOJuNqjh8XPjzGtZBW7Hci-M7nFrb69srkGNnBHYzF0JK4fTmgEUz_1aKno7p1e2MVnRmpvDtSsy0GL5wgWxzH_aiKSQ4nPUkVQjNpiUXahehENoL8lIfk_8_Wj8jtn79M9U3J3M3Q0j0bUh5z8-Z0nF158sOGzSNbWoH7CPjEd5F9Vp0'
              ?>" />
      <div class="absolute inset-0 bg-gradient-to-r from-transparent to-primary/20"></div>
      <div class="relative z-10 max-w-md">
        <div class="mb-8">
          <span class="font-headline italic text-on-primary text-4xl block mb-2">Info-Devis</span>
          <div class="w-12 h-px bg-on-primary opacity-40"></div>
        </div>
        <?php if ($isArtisan): ?>
          <blockquote class="font-headline text-3xl text-on-primary leading-tight font-light italic">
            "Rejoindre Info-Devis a transformé ma visibilité locale. La qualité des contacts est incomparable."
          </blockquote>
          <div class="mt-8 flex gap-4 items-center">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center font-bold text-primary text-sm">JB</div>
            <div>
              <p class="text-sm font-bold text-on-primary">Jean-Baptiste Durand</p>
              <p class="text-xs text-on-primary/70 uppercase tracking-tighter">Menuisier Ébéniste • Lyon</p>
            </div>
          </div>
        <?php else: ?>
          <blockquote class="font-headline text-3xl text-on-primary leading-tight font-light italic">
            "L'excellence n'est pas un acte, mais une habitude. Nous connectons les meilleurs artisans avec vos visions."
          </blockquote>
          <div class="mt-8">
            <p class="text-xs font-label uppercase tracking-widest text-on-primary opacity-80">Rejoignez +2,500 professionnels</p>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Right Side: Form -->
    <section class="flex-1 flex items-center justify-center p-6 sm:p-12 lg:p-24 bg-surface">
      <div class="w-full max-w-lg">

        <div class="md:hidden mb-12">
          <a class="font-headline italic text-primary text-3xl" href="<?= APP_URL ?>">Info-Devis</a>
        </div>

        <header class="mb-10">
          <h1 class="font-headline text-5xl text-on-background mb-4">
            <?= $isArtisan ? 'Créer un compte Pro' : 'Créer un compte' ?>
          </h1>
          <p class="font-body text-on-surface-variant text-lg">
            <?= $isArtisan ? 'Recevez des leads qualifiés dans votre zone' : 'Gérez vos demandes de devis gratuitement' ?>
          </p>
        </header>

        <?php if (!empty($errors)): ?>
          <div class="mb-8 p-4 border-l-4 border-red-400 bg-red-50 space-y-1">
            <?php foreach ($errors as $err): ?>
              <p class="text-sm text-red-700 font-body"><?= Security::e($err) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form action="<?= APP_URL ?>/inscription" method="POST" class="space-y-6">
          <?= Security::csrfField() ?>
          <input type="hidden" name="role" value="<?= $isArtisan ? 'artisan' : 'client' ?>" />

          <!-- Infos personnelles -->
          <div class="space-y-6">
            <?php if ($isArtisan): ?>
              <div class="flex items-center gap-2">
                <span class="w-6 h-px bg-primary/30"></span>
                <span class="font-label text-[10px] uppercase tracking-[0.2em] text-primary font-bold">Informations Personnelles</span>
              </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="font-label text-[10px] uppercase tracking-[0.1em] text-on-surface-variant font-bold" for="first_name">Prénom *</label>
                <input class="w-full bg-surface-container border-none py-4 px-4 text-on-surface focus:ring-0 transition-all duration-300"
                  id="first_name" name="first_name" type="text" placeholder="Jean" required
                  value="<?= Security::e($_POST['first_name'] ?? '') ?>" />
              </div>
              <div class="space-y-2">
                <label class="font-label text-[10px] uppercase tracking-[0.1em] text-on-surface-variant font-bold" for="last_name">Nom *</label>
                <input class="w-full bg-surface-container border-none py-4 px-4 text-on-surface focus:ring-0 transition-all duration-300"
                  id="last_name" name="last_name" type="text" placeholder="Dupont" required
                  value="<?= Security::e($_POST['last_name'] ?? '') ?>" />
              </div>
            </div>

            <div class="space-y-2">
              <label class="font-label text-[10px] uppercase tracking-[0.1em] text-on-surface-variant font-bold" for="email">Email *</label>
              <input class="w-full bg-surface-container border-none py-4 px-4 text-on-surface focus:ring-0 transition-all duration-300"
                id="email" name="email" type="email" placeholder="jean.dupont@exemple.fr" required
                value="<?= Security::e($_POST['email'] ?? '') ?>" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="font-label text-[10px] uppercase tracking-[0.1em] text-on-surface-variant font-bold" for="phone">Téléphone</label>
                <input class="w-full bg-surface-container border-none py-4 px-4 text-on-surface focus:ring-0 transition-all duration-300"
                  id="phone" name="phone" type="tel" placeholder="06 12 34 56 78"
                  value="<?= Security::e($_POST['phone'] ?? '') ?>" />
              </div>
              <div class="space-y-2">
                <label class="font-label text-[10px] uppercase tracking-[0.1em] text-on-surface-variant font-bold" for="password">Mot de passe *</label>
                <div class="relative">
                  <input class="w-full bg-surface-container border-none py-4 px-4 text-on-surface focus:ring-0 transition-all duration-300"
                    id="password" name="password" type="password" placeholder="••••••••" required />
                  <button class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors"
                    type="button" onclick="togglePassword()">
                    <span class="material-symbols-outlined text-xl" id="pwd-icon">visibility</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Infos entreprise (artisan uniquement) -->
          <?php if ($isArtisan): ?>
            <div class="space-y-6 pt-4">
              <div class="flex items-center gap-2">
                <span class="w-6 h-px bg-primary/30"></span>
                <span class="font-label text-[10px] uppercase tracking-[0.2em] text-primary font-bold">Détails de l'entreprise</span>
              </div>

              <div class="space-y-2">
                <label class="font-label text-[10px] uppercase tracking-[0.1em] text-on-surface-variant font-bold" for="company_name">Nom de l'entreprise *</label>
                <input class="w-full bg-surface-container border-none py-4 px-4 text-on-surface focus:ring-0 transition-all duration-300"
                  id="company_name" name="company_name" type="text" placeholder="Dupont &amp; Fils Rénovation"
                  value="<?= Security::e($_POST['company_name'] ?? '') ?>" />
              </div>

              <div class="space-y-2">
                <label class="font-label text-[10px] uppercase tracking-[0.1em] text-on-surface-variant font-bold" for="siret">Numéro SIRET *</label>
                <input class="w-full bg-surface-container border-none py-4 px-4 text-on-surface focus:ring-0 transition-all duration-300"
                  id="siret" name="siret" type="text" placeholder="123 456 789 00012"
                  value="<?= Security::e($_POST['siret'] ?? '') ?>" />
                <p class="text-[11px] text-on-surface-variant italic mt-1 opacity-70">Votre SIRET sera vérifié auprès de l'annuaire officiel.</p>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="font-label text-[10px] uppercase tracking-[0.1em] text-on-surface-variant font-bold" for="ville">Ville *</label>
                  <input class="w-full bg-surface-container border-none py-4 px-4 text-on-surface focus:ring-0 transition-all duration-300"
                    id="ville" name="ville" type="text" placeholder="Paris"
                    value="<?= Security::e($_POST['ville'] ?? '') ?>" />
                </div>
                <div class="space-y-2">
                  <label class="font-label text-[10px] uppercase tracking-[0.1em] text-on-surface-variant font-bold" for="code_postal">Code postal</label>
                  <input class="w-full bg-surface-container border-none py-4 px-4 text-on-surface focus:ring-0 transition-all duration-300"
                    id="code_postal" name="code_postal" type="text" placeholder="75001"
                    value="<?= Security::e($_POST['code_postal'] ?? '') ?>" />
                </div>
              </div>
            </div>
          <?php endif; ?>

          <!-- Consentements -->
          <div class="space-y-4 pt-4">
            <label class="flex items-start gap-4 cursor-pointer group">
              <div class="relative flex items-center justify-center mt-1">
                <input class="peer h-5 w-5 border-2 border-outline-variant rounded bg-transparent checked:bg-primary checked:border-primary focus:ring-0 transition-all duration-300"
                  type="checkbox" name="consent_privacy" required
                  <?= !empty($_POST['consent_privacy']) ? 'checked' : '' ?> />
                <span class="material-symbols-outlined absolute text-white scale-0 peer-checked:scale-100 transition-transform text-sm pointer-events-none"
                  style="font-variation-settings: 'FILL' 1;">check</span>
              </div>
              <span class="text-sm text-on-surface-variant font-body leading-relaxed">
                J'accepte la <a class="text-primary underline underline-offset-4" href="#">politique de confidentialité</a>
                et les <a class="text-primary underline underline-offset-4" href="#">conditions générales</a>. *
              </span>
            </label>
            <label class="flex items-start gap-4 cursor-pointer group">
              <div class="relative flex items-center justify-center mt-1">
                <input class="peer h-5 w-5 border-2 border-outline-variant rounded bg-transparent checked:bg-primary checked:border-primary focus:ring-0 transition-all duration-300"
                  type="checkbox" name="consent_marketing"
                  <?= !empty($_POST['consent_marketing']) ? 'checked' : '' ?> />
                <span class="material-symbols-outlined absolute text-white scale-0 peer-checked:scale-100 transition-transform text-sm pointer-events-none"
                  style="font-variation-settings: 'FILL' 1;">check</span>
              </div>
              <span class="text-sm text-on-surface-variant font-body leading-relaxed">
                J'accepte de recevoir des communications marketing (optionnel).
              </span>
            </label>
          </div>

          <!-- Submit -->
          <div class="pt-6">
            <button class="w-full bg-primary text-on-primary font-label uppercase tracking-[0.2em] text-xs font-bold py-5 px-8 rounded shadow-lg hover:brightness-105 active:opacity-90 transition-all duration-300" type="submit">
              <?= $isArtisan ? 'Créer mon compte Pro' : 'Créer mon compte' ?>
            </button>
          </div>
        </form>

        <footer class="mt-12 pt-8 border-t border-outline-variant/10 flex flex-col items-center gap-4">
          <p class="font-body text-sm text-on-surface-variant">
            Déjà un compte ?
            <a class="text-primary font-bold uppercase tracking-widest text-[11px] ml-2 hover:underline underline-offset-4 decoration-2"
              href="<?= APP_URL ?>/connexion">Se connecter</a>
          </p>
          <?php if (!$isArtisan): ?>
            <p class="font-body text-sm text-on-surface-variant">
              Vous êtes artisan ?
              <a class="text-primary font-bold uppercase tracking-widest text-[11px] ml-2 hover:underline underline-offset-4 decoration-2"
                href="<?= APP_URL ?>/inscription?type=artisan">Compte Pro →</a>
            </p>
          <?php endif; ?>
        </footer>
      </div>
    </section>
  </main>

  <script>
    function togglePassword() {
      var input = document.getElementById('password');
      var icon = document.getElementById('pwd-icon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility_off';
      } else {
        input.type = 'password';
        icon.textContent = 'visibility';
      }
    }
  </script>
</body>

</html>