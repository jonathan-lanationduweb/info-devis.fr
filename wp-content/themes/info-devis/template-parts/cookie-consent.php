<?php
/**
 * Bandeau de consentement cookies (RGPD/CNIL).
 * Affiché/piloté par consent.js UNIQUEMENT si un ID GA4 est configuré et
 * qu'aucun choix n'a encore été fait. Aucun script tiers avant acceptation.
 */
$idv_policy = home_url('/confidentialite/');
?>
<div id="idc-consent" class="idc-consent" role="dialog" aria-modal="false" aria-label="Gestion des cookies" aria-live="polite">
  <div class="idc-consent__box">
    <div class="idc-consent__body">
      <h2 class="idc-consent__title">🍪 Votre vie privée</h2>
      <p class="idc-consent__text">
        Nous utilisons des cookies pour mesurer l'audience et améliorer votre expérience.
        Vous pouvez accepter, refuser ou choisir. Voir notre
        <a href="<?php echo esc_url($idv_policy); ?>">politique de confidentialité</a>.
      </p>

      <!-- Réglages détaillés (affichés via « Personnaliser ») -->
      <div class="idc-consent__cats">
        <label class="idc-consent__cat idc-consent__cat--locked">
          <input type="checkbox" checked disabled>
          <span><strong>Nécessaires</strong> — indispensables au fonctionnement (connexion, panier). Toujours actifs.</span>
        </label>
        <label class="idc-consent__cat">
          <input type="checkbox" id="idc-cat-analytics" checked>
          <span><strong>Mesure d'audience</strong> — statistiques de visite anonymisées (Google Analytics).</span>
        </label>
        <label class="idc-consent__cat">
          <input type="checkbox" id="idc-cat-ads">
          <span><strong>Publicité</strong> — mesure des campagnes et personnalisation.</span>
        </label>
      </div>
    </div>

    <div class="idc-consent__actions">
      <button type="button" class="idc-consent__btn idc-consent__btn--ghost" data-consent="refuse">Refuser</button>
      <button type="button" class="idc-consent__btn idc-consent__btn--ghost idc-consent__btn--customize" data-consent="customize">Personnaliser</button>
      <button type="button" class="idc-consent__btn idc-consent__btn--ghost idc-consent__btn--save" data-consent="save">Enregistrer mes choix</button>
      <button type="button" class="idc-consent__btn idc-consent__btn--primary" data-consent="accept">Tout accepter</button>
    </div>
  </div>
</div>
