<?php
/**
 * Footer — reproduction fidèle de includes/footer.php (site original).
 * Aucune mention Astra / WordPress / « Propulsé par ».
 */
?>
  </main>

  <footer class="bg-[#071e24] py-16 px-8 border-t border-white/08 <?php echo !empty($GLOBALS['idv_has_sidebar']) ? 'idv-footer--offset' : ''; ?>" role="contentinfo">
    <div class="max-w-screen-xl mx-auto">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-14">

        <!-- Brand -->
        <div class="col-span-1">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="font-headline text-2xl font-bold text-white block mb-5">
            Info<span class="text-primary">Devis</span>
          </a>
          <p class="text-white/55 text-sm font-light leading-relaxed mb-6">
            La plateforme de mise en relation entre particuliers et artisans qualifiés. Gratuit, rapide, sans engagement.
          </p>
          <div class="flex flex-col gap-3">
            <div class="flex items-center gap-3 text-white/55 text-sm">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.1 1.25 2 2 0 012.11 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.18 7.86a16 16 0 007.96 7.96l1.21-1.21a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" />
              </svg>
              <span><?php echo esc_html(idv_contact('phone')); ?></span>
            </div>
            <div class="flex items-center gap-3 text-white/55 text-sm">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
              </svg>
              <span><?php echo esc_html(idv_contact('email')); ?></span>
            </div>
            <div class="flex items-center gap-3 text-white/55 text-sm">
              <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
              <span><?php echo esc_html(idv_contact('address')); ?></span>
            </div>
          </div>
        </div>

        <!-- Catégories -->
        <div>
          <h4 class="font-bold text-xs tracking-widest uppercase mb-5 text-white">Catégories</h4>
          <ul class="grid grid-cols-2 gap-x-6 gap-y-3 text-white/55 text-sm font-medium">
            <li><a href="<?php echo esc_url(home_url('/categorie/plomberie/')); ?>" class="hover:text-primary transition-colors">Plomberie</a></li>
            <li><a href="<?php echo esc_url(home_url('/categorie/electricite/')); ?>" class="hover:text-primary transition-colors">Électricité</a></li>
            <li><a href="<?php echo esc_url(home_url('/categorie/peinture/')); ?>" class="hover:text-primary transition-colors">Peinture</a></li>
            <li><a href="<?php echo esc_url(home_url('/categorie/toiture/')); ?>" class="hover:text-primary transition-colors">Toiture</a></li>
            <li><a href="<?php echo esc_url(home_url('/categorie/chauffage/')); ?>" class="hover:text-primary transition-colors">Chauffage</a></li>
            <li><a href="<?php echo esc_url(home_url('/categorie/isolation/')); ?>" class="hover:text-primary transition-colors">Isolation</a></li>
            <li><a href="<?php echo esc_url(home_url('/categorie/maconnerie/')); ?>" class="hover:text-primary transition-colors">Maçonnerie</a></li>
            <li><a href="<?php echo esc_url(home_url('/categories/')); ?>" class="hover:text-primary transition-colors font-semibold">Toutes les catégories →</a></li>
          </ul>
        </div>

        <!-- Services -->
        <div>
          <h4 class="font-bold text-xs tracking-widest uppercase mb-5 text-white">Services</h4>
          <ul class="grid grid-cols-2 gap-x-6 gap-y-3 text-white/55 text-sm font-medium">
            <li><a href="<?php echo esc_url(home_url('/devis/')); ?>" class="hover:text-primary transition-colors">Demander un devis</a></li>
            <li><a href="<?php echo esc_url(home_url('/professionnels/')); ?>" class="hover:text-primary transition-colors">Trouver un artisan</a></li>
            <li><a href="<?php echo esc_url(home_url('/guides/')); ?>" class="hover:text-primary transition-colors">Guides travaux</a></li>
            <li><a href="<?php echo esc_url(home_url('/blog/')); ?>" class="hover:text-primary transition-colors">Blog travaux</a></li>
            <li><a href="<?php echo esc_url(home_url('/tarifs-pro/')); ?>" class="hover:text-primary transition-colors">Espace Pro</a></li>
            <li><a href="<?php echo esc_url(home_url('/inscription/?type=artisan')); ?>" class="hover:text-primary transition-colors">Devenir partenaire</a></li>
          </ul>
        </div>

        <!-- Infos -->
        <div>
          <h4 class="font-bold text-xs tracking-widest uppercase mb-5 text-white">Informations</h4>
          <ul class="grid grid-cols-2 gap-x-6 gap-y-3 text-white/55 text-sm font-medium">
            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>" class="hover:text-primary transition-colors">Contact</a></li>
            <li><a href="<?php echo esc_url(home_url('/mentions-legales/')); ?>" class="hover:text-primary transition-colors">Mentions légales</a></li>
            <li><a href="<?php echo esc_url(home_url('/confidentialite/')); ?>" class="hover:text-primary transition-colors">Confidentialité</a></li>
            <li><a href="<?php echo esc_url(home_url('/cgv/')); ?>" class="hover:text-primary transition-colors">CGV</a></li>
            <li><a href="<?php echo esc_url(home_url('/plan-du-site/')); ?>" class="hover:text-primary transition-colors">Plan du site</a></li>
          </ul>
        </div>
      </div>

      <!-- Bottom -->
      <div class="flex flex-col md:flex-row justify-between items-center gap-5 pt-10 border-t border-white/08">
        <p class="text-white/35 text-xs tracking-wide">© <?php echo esc_html(date_i18n('Y')); ?> InfoDevis.fr — Tous droits réservés</p>
        <div class="flex flex-wrap gap-6 text-white/35 text-xs font-medium">
          <a href="<?php echo esc_url(home_url('/mentions-legales/')); ?>" class="hover:text-white/75 transition-colors">Mentions légales</a>
          <a href="<?php echo esc_url(home_url('/confidentialite/')); ?>" class="hover:text-white/75 transition-colors">Confidentialité</a>
          <a href="<?php echo esc_url(home_url('/cgv/')); ?>" class="hover:text-white/75 transition-colors">CGV</a>
          <a href="<?php echo esc_url(home_url('/plan-du-site/')); ?>" class="hover:text-white/75 transition-colors">Plan du site</a>
          <?php if (function_exists('idc_ga4_id') && idc_ga4_id() !== '') : ?>
            <button type="button" data-open-consent class="hover:text-white/75 transition-colors">Gérer les cookies</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </footer>

  <?php
  // Menu mobile plein écran (toutes les pages front).
  get_template_part('template-parts/mobile-menu');
  // Barre de navigation inférieure : uniquement sur les pages front (pas les
  // dashboards, qui ont leur propre navigation avec sidebar + bottom nav).
  if (empty($GLOBALS['idv_has_sidebar'])) {
      get_template_part('template-parts/bottom-nav');
  }
  ?>

  <?php wp_footer(); ?>
</body>

</html>
