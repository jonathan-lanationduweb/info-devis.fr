<footer class="site-footer" role="contentinfo">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div class="footer-brand">
        <a href="<?= APP_URL ?>" class="logo" style="color:#fff;font-family:var(--f-display);font-size:1.5rem;font-weight:800;">
          Info<span style="color:var(--c-orange)">Devis</span>
        </a>
        <p>La plateforme de mise en relation entre particuliers et artisans qualifiés. Gratuit, rapide, sans engagement.</p>

        <div class="footer-contact-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.1 1.25 2 2 0 012.11 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.18 7.86a16 16 0 007.96 7.96l1.21-1.21a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
          <span><?= CONTACT_PHONE ?></span>
        </div>
        <div class="footer-contact-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <span><?= CONTACT_EMAIL ?></span>
        </div>
        <div class="footer-contact-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <span><?= CONTACT_ADDRESS ?></span>
        </div>
        <div class="footer-contact-item">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span><?= CONTACT_HOURS ?></span>
        </div>
      </div>

      <!-- Catégories -->
      <div>
        <p class="footer-heading">Catégories</p>
        <ul class="footer-links">
          <li><a href="<?= APP_URL ?>/categorie/plomberie">Plomberie</a></li>
          <li><a href="<?= APP_URL ?>/categorie/electricite">Électricité</a></li>
          <li><a href="<?= APP_URL ?>/categorie/peinture">Peinture</a></li>
          <li><a href="<?= APP_URL ?>/categorie/toiture">Toiture</a></li>
          <li><a href="<?= APP_URL ?>/categorie/chauffage">Chauffage</a></li>
          <li><a href="<?= APP_URL ?>/categorie/isolation">Isolation</a></li>
          <li><a href="<?= APP_URL ?>/categorie/maconnerie">Maçonnerie</a></li>
          <li><a href="<?= APP_URL ?>/categories">Toutes les catégories →</a></li>
        </ul>
      </div>

      <!-- Services -->
      <div>
        <p class="footer-heading">Services</p>
        <ul class="footer-links">
          <li><a href="<?= APP_URL ?>/devis">Demander un devis</a></li>
          <li><a href="<?= APP_URL ?>/artisans">Trouver un artisan</a></li>
          <li><a href="<?= APP_URL ?>/guides-prix">Guides & Prix</a></li>
          <li><a href="<?= APP_URL ?>/blog">Blog travaux</a></li>
          <li><a href="<?= APP_URL ?>/tarifs-pro">Espace Pro</a></li>
          <li><a href="<?= APP_URL ?>/inscription?type=artisan">Devenir partenaire</a></li>
        </ul>
      </div>

      <!-- Infos -->
      <div>
        <p class="footer-heading">Informations</p>
        <ul class="footer-links">
          <li><a href="<?= APP_URL ?>/contact">Contact</a></li>
          <li><a href="<?= APP_URL ?>/mentions-legales">Mentions légales</a></li>
          <li><a href="<?= APP_URL ?>/politique-confidentialite">Confidentialité</a></li>
          <li><a href="<?= APP_URL ?>/cgv">CGV</a></li>
          <li><a href="<?= APP_URL ?>/sitemap.xml">Sitemap</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p class="footer-copyright">© <?= date('Y') ?> InfoDevis.fr — Tous droits réservés</p>
      <div class="footer-legal">
        <a href="<?= APP_URL ?>/mentions-legales">Mentions légales</a>
        <a href="<?= APP_URL ?>/politique-confidentialite">Confidentialité</a>
        <a href="<?= APP_URL ?>/cgv">CGV</a>
      </div>
    </div>
  </div>
</footer>
