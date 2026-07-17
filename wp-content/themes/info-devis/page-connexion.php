<?php
/**
 * Connexion — reproduction fidèle de views/auth/login.php, branchée sur
 * l'authentification WordPress (champs log/pwd → wp-login.php).
 * Le bouton « Continuer avec Google » sera réintroduit avec le portage
 * OAuth (module reporté — voir audit) : pas de bouton factice.
 */

if (is_user_logged_in()) {
    $idv_roles = (array) wp_get_current_user()->roles;
    wp_safe_redirect(in_array('artisan', $idv_roles, true) ? home_url('/dashboard/artisan/') : (in_array('client', $idv_roles, true) ? home_url('/dashboard/client/') : admin_url()));
    exit;
}

$idv_redirect = isset($_GET['redirect_to']) ? esc_url_raw(wp_unslash($_GET['redirect_to'])) : home_url('/espace-membre/');

get_header();
?>

<div class="min-h-screen bg-stone-50 flex items-center justify-center px-4 py-16 pt-32">
  <div class="w-full max-w-md">

    <div class="mb-10">
      <h1 class="font-headline text-4xl md:text-5xl text-on-background mb-3 font-medium">Bon retour parmi nous.</h1>
      <p class="font-label text-sm text-on-surface-variant tracking-wide">Accédez à vos projets et devis personnalisés.</p>
    </div>

    <?php if (isset($_GET['erreur'])) : ?>
      <div class="mb-6 p-4 border-l-4 border-red-400 bg-red-50">
        <p class="text-sm text-red-700 font-body">Identifiants incorrects. Vérifiez votre email et votre mot de passe.</p>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['reset'])) : ?>
      <div class="mb-6 p-4 border-l-4 border-primary bg-green-50">
        <p class="text-sm text-primary font-body">Mot de passe mis à jour. Connectez-vous.</p>
      </div>
    <?php endif; ?>

    <form action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="POST" class="space-y-8 bg-white p-8 shadow-sm border border-gray-100">
      <input type="hidden" name="redirect_to" value="<?php echo esc_attr($idv_redirect); ?>">
      <input type="hidden" name="testcookie" value="1">

      <div class="space-y-2">
        <label class="font-label text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold block" for="email">Adresse Email</label>
        <input class="w-full bg-surface-container border-none py-4 px-4 text-on-surface focus:ring-0 focus:border-b-2 focus:border-primary transition-all font-body"
          id="email" name="log" type="text" placeholder="votre@email.fr" required autocomplete="username" />
      </div>

      <div class="space-y-2">
        <div class="flex justify-between items-center mb-2">
          <label class="font-label text-[10px] uppercase tracking-[0.15em] text-on-surface-variant font-bold" for="password">Mot de passe</label>
          <a class="text-[10px] uppercase tracking-wider text-primary/70 font-bold hover:text-primary transition-colors"
            href="<?php echo esc_url(wp_lostpassword_url(home_url('/connexion/?reset=1'))); ?>">Oublié ?</a>
        </div>
        <div class="relative">
          <input class="w-full bg-surface-container border-none py-4 px-4 pr-12 text-on-surface focus:ring-0 focus:border-b-2 focus:border-primary transition-all font-body"
            id="password" name="pwd" type="password" placeholder="••••••••" required autocomplete="current-password" />
          <button type="button" onclick="togglePwd()"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors p-1" aria-label="Afficher le mot de passe">
            <svg id="eye-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
            <svg id="eye-off-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none">
              <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" />
              <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
          </button>
        </div>
      </div>

      <button class="w-full bg-primary text-white py-4 px-6 font-label text-xs uppercase tracking-[0.2em] font-bold hover:bg-primary-dk transition-all duration-300" type="submit">
        Se connecter
      </button>
    </form>

    <div class="mt-8 text-center space-y-4">
      <p class="text-sm text-on-surface-variant font-body">
        Pas encore de compte ?
        <a class="text-primary font-bold ml-1 hover:underline underline-offset-4"
          href="<?php echo esc_url(home_url('/inscription/')); ?>">S'inscrire gratuitement</a>
      </p>
      <p class="text-sm text-on-surface-variant font-body">
        Vous êtes artisan ?
        <a class="text-primary font-bold ml-1 hover:underline underline-offset-4"
          href="<?php echo esc_url(home_url('/inscription/?type=artisan')); ?>">Créer un compte Pro →</a>
      </p>
    </div>

  </div>
</div>

<script>
  function togglePwd() {
    var input = document.getElementById('password');
    var eyeOn = document.getElementById('eye-icon');
    var eyeOff = document.getElementById('eye-off-icon');
    if (input.type === 'password') {
      input.type = 'text';
      eyeOn.style.display = 'none';
      eyeOff.style.display = 'block';
    } else {
      input.type = 'password';
      eyeOn.style.display = 'block';
      eyeOff.style.display = 'none';
    }
  }
</script>

<?php get_footer(); ?>
