<?php

/**
 * Page Contact — Info-Devis
 * Design: Newsreader + Manrope, palette primary #207752
 */
?>
<!DOCTYPE html>
<html class="light" lang="fr">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Contact — Info-Devis</title>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "primary": "#207752",
            "on-primary": "#ffffff",
            "background": "#ffffff",
            "on-background": "#1a1a1a",
            "surface": "#ffffff",
            "on-surface": "#1a1a1a",
            "outline": "#e5e7eb",
            "secondary": "#6b7280"
          },
          "borderRadius": {
            "DEFAULT": "0px",
            "lg": "0px",
            "xl": "0px",
            "full": "0px"
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
    .material-symbols-outlined {
      font-variation-settings: "FILL" 0, "wght" 300, "GRAD" 0, "opsz" 24
    }

    body {
      background-color: #ffffff;
    }

    input,
    textarea,
    select {
      border-radius: 0 !important;
    }
  </style>
</head>

<body class="font-body text-on-background antialiased">

  <?php include __DIR__ . '/../../includes/navbar.php'; ?>

  <main class="max-w-screen-xl mx-auto px-6 py-16 md:py-24">

    <!-- Hero Section -->
    <header class="mb-24">
      <h1 class="font-headline text-5xl md:text-7xl text-on-surface leading-tight mb-6">
        À votre <span class="italic font-light">écoute</span>
      </h1>
      <p class="font-body text-lg text-secondary max-w-xl leading-relaxed">
        Notre équipe est disponible du lundi au vendredi de 9h à 18h pour vous accompagner dans vos projets.
      </p>
    </header>

    <!-- Main Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-20 items-start">

      <!-- Contact Information Sidebar -->
      <aside class="lg:col-span-4 space-y-16">
        <div class="space-y-12">

          <div class="space-y-4">
            <span class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary">Téléphone</span>
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-primary text-xl">call</span>
              <a class="font-headline text-2xl text-on-surface" href="tel:<?= str_replace(' ', '', CONTACT_PHONE) ?>"><?= CONTACT_PHONE ?></a>
            </div>
            <p class="text-sm text-secondary font-light"><?= CONTACT_HOURS ?></p>
          </div>

          <div class="space-y-4">
            <span class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary">Email</span>
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-primary text-xl">mail</span>
              <a class="font-headline text-2xl text-on-surface" href="mailto:<?= CONTACT_EMAIL ?>"><?= CONTACT_EMAIL ?></a>
            </div>
          </div>

          <div class="space-y-4">
            <span class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary">Adresse</span>
            <div class="flex items-start gap-3">
              <span class="material-symbols-outlined text-primary text-xl">location_on</span>
              <address class="not-italic font-headline text-2xl text-on-surface leading-relaxed">
                <?= nl2br(CONTACT_ADDRESS) ?>
              </address>
            </div>
          </div>

        </div>

        <!-- Google Maps -->
        <div class="border border-gray-100 overflow-hidden">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.5!2d2.3722!3d48.8535!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2s45+Rue+des+Boulets+75011+Paris!5e0!3m2!1sfr!2sfr!4v1"
            width="100%" height="260" style="border:0;display:block" allowfullscreen loading="lazy">
          </iframe>
        </div>

        <div class="pt-8 border-t border-gray-100">
          <p class="font-headline italic text-lg text-secondary leading-relaxed">
            "L'art de l'artisanat rencontre la précision digitale."
          </p>
        </div>
      </aside>

      <!-- Message Form Section -->
      <section class="lg:col-span-8">
        <div class="max-w-2xl">
          <h2 class="font-headline text-3xl mb-12 text-on-surface">Envoyez-nous un message</h2>

          <?php if (!empty($success)): ?>
            <div class="mb-8 p-5 border-l-4 border-primary bg-green-50">
              <p class="font-body text-sm text-primary">
                ✓ Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.
              </p>
            </div>
          <?php endif; ?>

          <?php if (!empty($error)): ?>
            <div class="mb-8 p-5 border-l-4 border-red-400 bg-red-50">
              <p class="font-body text-sm text-red-700"><?= Security::e($error) ?></p>
            </div>
          <?php endif; ?>

          <form action="<?= APP_URL ?>/contact" method="POST" class="space-y-10" data-validate>
            <?= Security::csrfField() ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
              <div class="space-y-2">
                <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary" for="first_name">Prénom *</label>
                <input
                  class="w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-primary px-0 py-3 font-body transition-colors text-on-surface placeholder-gray-300"
                  id="first_name" name="first_name" type="text" required placeholder="Jean"
                  value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>" />
              </div>
              <div class="space-y-2">
                <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary" for="last_name">Nom *</label>
                <input
                  class="w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-primary px-0 py-3 font-body transition-colors text-on-surface placeholder-gray-300"
                  id="last_name" name="last_name" type="text" required placeholder="Dupont"
                  value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>" />
              </div>
            </div>

            <div class="space-y-2">
              <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary" for="email">Email *</label>
              <input
                class="w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-primary px-0 py-3 font-body transition-colors text-on-surface placeholder-gray-300"
                id="email" name="email" type="email" required placeholder="jean.dupont@exemple.fr"
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" />
            </div>

            <div class="space-y-2">
              <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary" for="subject">Sujet</label>
              <select class="w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-primary px-0 py-3 font-body transition-colors appearance-none bg-transparent" id="subject" name="subject">
                <option value="info" <?= (isset($_POST['subject']) && $_POST['subject'] === 'info') ? 'selected' : '' ?>>Demande d'information</option>
                <option value="tech" <?= (isset($_POST['subject']) && $_POST['subject'] === 'tech') ? 'selected' : '' ?>>Problème technique</option>
                <option value="artisan" <?= (isset($_POST['subject']) && $_POST['subject'] === 'artisan') ? 'selected' : '' ?>>Partenariat artisan</option>
                <option value="signalement" <?= (isset($_POST['subject']) && $_POST['subject'] === 'signalement') ? 'selected' : '' ?>>Signalement</option>
                <option value="autre" <?= (isset($_POST['subject']) && $_POST['subject'] === 'autre') ? 'selected' : '' ?>>Autre</option>
              </select>
            </div>

            <div class="space-y-2">
              <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary" for="message">Message *</label>
              <textarea
                class="w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-primary px-0 py-3 font-body transition-colors resize-none text-on-surface placeholder-gray-300"
                id="message" name="message" rows="5" required placeholder="Comment pouvons-nous vous aider ?"><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
            </div>

            <div class="pt-6">
              <button class="bg-primary text-white px-10 py-4 font-label text-xs uppercase tracking-widest transition-all hover:bg-opacity-90" type="submit">
                Envoyer le message
              </button>
            </div>
          </form>
        </div>
      </section>

    </div>

    <!-- Editorial Quote Section -->
    <section class="mt-32 pt-16 border-t border-gray-100">
      <div class="max-w-3xl">
        <p class="font-headline text-2xl italic text-on-surface mb-6 leading-relaxed">
          "L'excellence n'est pas un acte, c'est une habitude. Chaque demande traitée par nos équipes est le reflet de notre engagement pour l'artisanat français."
        </p>
        <cite class="not-italic font-label text-[10px] uppercase tracking-[0.3em] text-secondary">Direction de la Relation Client — Info-Devis</cite>
      </div>
    </section>

  </main>


</body>

</html>