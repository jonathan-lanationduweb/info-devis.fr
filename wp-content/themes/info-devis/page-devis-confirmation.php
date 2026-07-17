<?php
/**
 * Confirmation de demande de devis — reproduction fidèle de
 * views/home/devis-confirmation.php (étape 3 sur 3).
 * La référence arrive via ?ref= ; les métiers via le transient posé à l'envoi.
 */

get_header();

$idv_ref  = sanitize_text_field(wp_unslash($_GET['ref'] ?? ''));
$idv_data = $idv_ref ? get_transient('idc_confirm_' . $idv_ref) : false;
$idv_cats = is_array($idv_data) ? (array) ($idv_data['cats'] ?? []) : [];
$idv_grouped = count($idv_cats) > 1;
?>

<div class="flex flex-1 pt-24">

  <!-- Sidebar progression -->
  <aside class="hidden lg:flex flex-col w-72 h-screen p-10 pt-32 fixed left-0 top-0 bg-background border-r border-outline-variant/15">
    <div class="mb-10">
      <h3 class="font-headline italic text-2xl text-primary mb-1">Progression</h3>
      <p class="font-label text-xs text-secondary uppercase tracking-widest">Étape 3 sur 3</p>
    </div>
    <nav class="space-y-8">
      <div class="flex items-center gap-4 text-secondary font-label text-sm">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">architecture</span>
        <span>Votre projet</span>
      </div>
      <div class="flex items-center gap-4 text-secondary font-label text-sm">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">person</span>
        <span>Vos coordonnées</span>
      </div>
      <div class="flex items-center gap-4 text-primary font-bold border-r-2 border-primary pr-4 font-label text-sm">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">check_circle</span>
        <span>Confirmation</span>
      </div>
    </nav>
  </aside>

  <div class="flex-1 lg:ml-72 flex items-center justify-center p-8 min-h-[calc(100vh-180px)]">
    <div class="max-w-3xl w-full text-center">

      <!-- Icône succès -->
      <div class="mb-8 flex justify-center">
        <div class="relative">
          <div class="absolute inset-0 bg-primary-container blur-3xl opacity-20 scale-150 rounded-full"></div>
          <div class="relative bg-white p-6 rounded-full inline-flex items-center justify-center shadow-sm border border-outline-variant/10">
            <span class="material-symbols-outlined text-7xl text-primary" style="font-variation-settings:'FILL' 1;">check_circle</span>
          </div>
        </div>
      </div>

      <h1 class="font-headline text-5xl md:text-6xl text-on-surface font-semibold mb-6 leading-tight">
        Votre demande a bien été <span class="italic text-primary">transmise !</span>
      </h1>

      <?php if ($idv_grouped) : ?>
        <p class="font-body text-lg text-secondary leading-relaxed mb-6 max-w-xl mx-auto">
          Votre <strong>demande de devis groupé</strong> a été envoyée à des artisans qualifiés dans
          <strong><?php echo count($idv_cats); ?> métiers</strong>.
        </p>
        <div class="max-w-md mx-auto mb-8 bg-primary/5 border border-primary/20 rounded-2xl p-5 text-left">
          <p class="text-[10px] uppercase tracking-widest text-primary font-bold mb-3 text-center">Vous recevrez plusieurs devis pour :</p>
          <ul class="space-y-2">
            <?php foreach ($idv_cats as $idv_cat) : ?>
              <li class="flex items-center gap-2 text-sm">
                <span class="material-symbols-outlined text-primary" style="font-size:18px;">check_circle</span>
                <strong><?php echo esc_html($idv_cat); ?></strong>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php else : ?>
        <p class="font-body text-lg text-secondary leading-relaxed mb-4 max-w-xl mx-auto">
          Notre équipe traite votre demande. Vous recevrez jusqu'à
          <span class="font-bold text-on-surface">5 devis d'artisans qualifiés</span> sous 24h.
        </p>
      <?php endif; ?>

      <?php if ($idv_ref) : ?>
        <div class="inline-block bg-surface-container px-6 py-3 rounded-lg mb-10">
          <p class="font-label text-xs uppercase tracking-widest text-secondary">Référence de votre demande</p>
          <p class="font-headline text-2xl text-primary font-bold"><?php echo esc_html($idv_ref); ?></p>
        </div>
      <?php endif; ?>

      <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-16">
        <a href="<?php echo esc_url(home_url('/')); ?>"
          class="bg-primary text-on-primary px-10 py-4 rounded-lg font-body font-semibold flex items-center gap-3 transition-all hover:shadow-lg hover:shadow-primary/10 active:scale-95">
          Retourner à l'accueil
          <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
        <?php if (is_user_logged_in()) : ?>
          <a href="<?php echo esc_url(home_url('/espace-membre/')); ?>"
            class="border border-primary text-primary px-10 py-4 rounded-lg font-body font-semibold flex items-center gap-3 hover:bg-primary/5 transition-all">
            Suivre ma demande
          </a>
        <?php endif; ?>
      </div>

      <!-- Bento bas de page -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
        <div class="p-8 rounded-xl bg-surface-container-low border border-outline-variant/5">
          <span class="material-symbols-outlined text-primary mb-4">verified_user</span>
          <h3 class="font-headline italic text-xl mb-2 text-on-surface">Artisans Certifiés</h3>
          <p class="text-xs text-secondary font-body leading-relaxed">Chaque professionnel est rigoureusement sélectionné pour son expertise et sa fiabilité.</p>
        </div>
        <div class="p-8 rounded-xl bg-surface-container-low border border-outline-variant/5">
          <span class="material-symbols-outlined text-primary mb-4">schedule</span>
          <h3 class="font-headline italic text-xl mb-2 text-on-surface">Réponse sous 24h</h3>
          <p class="text-xs text-secondary font-body leading-relaxed">Nos artisans s'engagent à vous répondre rapidement pour démarrer votre projet.</p>
        </div>
        <div class="p-8 rounded-xl bg-surface-container-low border border-outline-variant/5">
          <span class="material-symbols-outlined text-primary mb-4">favorite</span>
          <h3 class="font-headline italic text-xl mb-2 text-on-surface">100% Gratuit</h3>
          <p class="text-xs text-secondary font-body leading-relaxed">Aucun frais caché, aucun engagement. Le service est entièrement gratuit pour vous.</p>
        </div>
      </div>

    </div>
  </div>
</div>

<?php get_footer(); ?>
