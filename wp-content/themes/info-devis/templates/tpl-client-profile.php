<?php
/**
 * Template Name: Espace client — Mon profil
 * Coordonnées + changement de mot de passe (traitement : extension,
 * action idc_client_profile).
 */

$idv_user = idv_require_role('client');

get_header();
get_template_part('template-parts/sidebar', 'client', ['user' => $idv_user]);

$idv_phone = get_user_meta($idv_user->ID, '_idc_phone', true);
$idv_ville = get_user_meta($idv_user->ID, '_idc_ville', true);
$idv_cp    = get_user_meta($idv_user->ID, '_idc_code_postal', true);
?>

<div class="md:ml-72 pt-32 pb-24 md:pb-20 px-8 md:px-16 min-h-screen">

  <header class="mb-16 border-b border-outline-variant/10 pb-12">
    <div class="flex items-center gap-4 mb-4">
      <span class="bg-primary/10 text-primary px-3 py-1 text-[10px] font-bold tracking-[0.2em] uppercase rounded-full">Mon profil</span>
      <span class="h-px w-12 bg-outline-variant/30"></span>
    </div>
    <h1 class="text-5xl md:text-6xl font-headline italic font-bold text-on-surface leading-tight">Mes informations</h1>
    <p class="mt-4 text-on-surface-variant font-body text-lg leading-relaxed max-w-lg">
      Gérez vos coordonnées et votre mot de passe.
    </p>
  </header>

  <?php if (isset($_GET['profil'])) : ?>
    <?php if ($_GET['profil'] === 'ok') : ?>
      <div class="mb-8 p-5 border-l-4 border-primary bg-primary/5 rounded-r-xl text-sm">✅ Profil mis à jour.</div>
    <?php else :
        $idv_msgs = [
            'mdp'        => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'mdp_actuel' => 'Le mot de passe actuel est incorrect.',
            'champs'     => 'Merci de remplir les champs obligatoires.',
            'erreur'     => 'Une erreur est survenue, merci de réessayer.',
        ];
    ?>
      <div class="mb-8 p-5 border-l-4 border-red-400 bg-red-50 rounded-r-xl text-sm text-red-700"><?php echo esc_html($idv_msgs[$_GET['profil']] ?? $idv_msgs['erreur']); ?></div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-5xl">

    <!-- Coordonnées -->
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="bg-surface-container-low p-10 rounded-2xl space-y-6">
      <input type="hidden" name="action" value="idc_client_profile">
      <input type="hidden" name="idc_section" value="coordonnees">
      <?php wp_nonce_field('idc_client_profile', 'idc_profile_nonce'); ?>

      <h2 class="font-headline text-2xl font-bold flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">account_circle</span> Coordonnées
      </h2>

      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Nom affiché *</label>
        <input type="text" name="display_name" required value="<?php echo esc_attr($idv_user->display_name); ?>"
          class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
      </div>

      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Adresse e-mail</label>
        <input type="email" value="<?php echo esc_attr($idv_user->user_email); ?>" disabled
          class="w-full bg-surface-container/50 border-none p-4 rounded-xl font-body text-on-surface-variant">
        <p class="text-[10px] text-on-surface-variant italic">L'adresse e-mail sert d'identifiant — contactez-nous pour la modifier.</p>
      </div>

      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Téléphone</label>
        <input type="tel" name="phone" value="<?php echo esc_attr($idv_phone); ?>" placeholder="06 00 00 00 00"
          class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Ville</label>
          <input type="text" name="ville" value="<?php echo esc_attr($idv_ville); ?>" placeholder="Paris"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
        </div>
        <div class="space-y-2">
          <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Code postal</label>
          <input type="text" name="code_postal" value="<?php echo esc_attr($idv_cp); ?>" placeholder="75001" pattern="[0-9]{5}" maxlength="5"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
        </div>
      </div>

      <button type="submit" class="bg-primary text-on-primary px-8 py-3.5 rounded-xl font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all">
        Enregistrer
      </button>
    </form>

    <!-- Mot de passe -->
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="bg-surface-container-low p-10 rounded-2xl space-y-6 h-fit">
      <input type="hidden" name="action" value="idc_client_profile">
      <input type="hidden" name="idc_section" value="password">
      <?php wp_nonce_field('idc_client_profile', 'idc_profile_nonce'); ?>

      <h2 class="font-headline text-2xl font-bold flex items-center gap-3">
        <span class="material-symbols-outlined text-primary">lock</span> Mot de passe
      </h2>

      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Mot de passe actuel *</label>
        <input type="password" name="current_password" required autocomplete="current-password"
          class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
      </div>

      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-widest text-secondary font-bold">Nouveau mot de passe * (8 caractères min.)</label>
        <input type="password" name="new_password" required minlength="8" autocomplete="new-password"
          class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-4 rounded-xl font-body">
      </div>

      <button type="submit" class="bg-on-surface text-surface px-8 py-3.5 rounded-xl font-bold tracking-widest uppercase text-xs hover:bg-primary transition-colors">
        Changer le mot de passe
      </button>
    </form>
  </div>
</div>

<?php get_footer(); ?>
