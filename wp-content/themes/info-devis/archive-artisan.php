<?php
/**
 * Archive du CPT artisan (/artisans/) : redirection 301 vers l'annuaire
 * fidèle /professionnels/ (pages/professionnels.php de l'original), pour
 * éviter deux annuaires au rendu différent. Les fiches /artisan/{slug}
 * restent servies par single-artisan.php.
 */

wp_redirect(home_url('/professionnels/'), 301);
exit;
