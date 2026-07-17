<?php
/**
 * Contact — reproduction fidèle de views/home/contact.php :
 * hero « À votre écoute », colonne coordonnées + carte OpenStreetMap,
 * formulaire à champs soulignés, citation éditoriale.
 * Traitement : extension (action idc_contact_send).
 */

get_header();
?>

<div class="max-w-screen-xl mx-auto px-6 py-16 md:py-24 pt-32">

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
            <a class="font-headline text-2xl text-on-surface" href="tel:<?php echo esc_attr(str_replace(' ', '', idv_contact('phone'))); ?>"><?php echo esc_html(idv_contact('phone')); ?></a>
          </div>
          <p class="text-sm text-secondary font-light">Du lundi au vendredi, 9h — 18h</p>
        </div>

        <div class="space-y-4">
          <span class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary">Email</span>
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-xl">mail</span>
            <a class="font-headline text-2xl text-on-surface" href="mailto:<?php echo esc_attr(idv_contact('email')); ?>"><?php echo esc_html(idv_contact('email')); ?></a>
          </div>
        </div>

        <div class="space-y-4">
          <span class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary">Adresse</span>
          <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-primary text-xl">location_on</span>
            <address class="not-italic font-headline text-2xl text-on-surface leading-relaxed">
              <?php echo esc_html(idv_contact('address')); ?>
            </address>
          </div>
        </div>

      </div>

      <!-- Carte (OpenStreetMap, pas de clé requise) -->
      <div class="border border-gray-100 overflow-hidden rounded-lg">
        <iframe
          src="https://www.openstreetmap.org/export/embed.html?bbox=2.3870%2C48.8520%2C2.3970%2C48.8570&layer=mapnik&marker=48.8545%2C2.3920"
          width="100%" height="260"
          style="border:0;display:block"
          loading="lazy"
          title="Carte — 45 Rue des Boulets, 75011 Paris"></iframe>
        <a href="https://www.openstreetmap.org/?mlat=48.8545&mlon=2.3920#map=17/48.8545/2.3920"
           target="_blank" rel="noopener"
           class="block text-center text-xs py-2 bg-surface-container-low text-primary hover:bg-primary/5 font-semibold">
          <span class="material-symbols-outlined" style="font-size:14px;vertical-align:middle;">open_in_new</span>
          Ouvrir dans OpenStreetMap
        </a>
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

        <?php if (isset($_GET['contact']) && $_GET['contact'] === 'ok') : ?>
          <div class="mb-8 p-5 border-l-4 border-primary bg-green-50">
            <p class="font-body text-sm text-primary">
              ✓ Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.
            </p>
          </div>
        <?php elseif (isset($_GET['contact'])) : ?>
          <div class="mb-8 p-5 border-l-4 border-red-400 bg-red-50">
            <p class="font-body text-sm text-red-700"><?php echo $_GET['contact'] === 'champs' ? 'Merci de remplir tous les champs obligatoires.' : 'Une erreur est survenue, merci de réessayer.'; ?></p>
          </div>
        <?php endif; ?>

        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" class="space-y-10">
          <input type="hidden" name="action" value="idc_contact_send" />
          <?php wp_nonce_field('idc_contact_send', 'idc_contact_nonce'); ?>
          <p class="idc-hp-field" aria-hidden="true" style="position:absolute;left:-9999px;"><label>Ne pas remplir<input type="text" name="idc_website" tabindex="-1" autocomplete="off"></label></p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div class="space-y-2">
              <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary" for="first_name">Prénom *</label>
              <input
                class="w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-primary px-0 py-3 font-body transition-colors text-on-surface placeholder-gray-300 bg-transparent"
                id="first_name" name="first_name" type="text" required placeholder="Jean" />
            </div>
            <div class="space-y-2">
              <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary" for="last_name">Nom *</label>
              <input
                class="w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-primary px-0 py-3 font-body transition-colors text-on-surface placeholder-gray-300 bg-transparent"
                id="last_name" name="last_name" type="text" required placeholder="Dupont" />
            </div>
          </div>

          <div class="space-y-2">
            <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary" for="email">Email *</label>
            <input
              class="w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-primary px-0 py-3 font-body transition-colors text-on-surface placeholder-gray-300 bg-transparent"
              id="email" name="email" type="email" required placeholder="jean.dupont@exemple.fr" />
          </div>

          <div class="space-y-2">
            <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary" for="subject">Sujet</label>
            <select class="w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-primary px-0 py-3 font-body transition-colors appearance-none bg-transparent" id="subject" name="subject">
              <option value="info">Demande d'information</option>
              <option value="tech">Problème technique</option>
              <option value="artisan">Partenariat artisan</option>
              <option value="signalement">Signalement</option>
              <option value="autre">Autre</option>
            </select>
          </div>

          <div class="space-y-2">
            <label class="block font-label text-[10px] uppercase tracking-[0.2em] text-secondary" for="message">Message *</label>
            <textarea
              class="w-full border-0 border-b border-gray-200 focus:ring-0 focus:border-primary px-0 py-3 font-body transition-colors resize-none text-on-surface placeholder-gray-300 bg-transparent"
              id="message" name="message" rows="5" required placeholder="Comment pouvons-nous vous aider ?"></textarea>
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

</div>

<?php get_footer(); ?>
