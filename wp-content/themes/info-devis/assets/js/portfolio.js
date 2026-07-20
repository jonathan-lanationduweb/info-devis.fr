/**
 * portfolio.js - Interactions portfolio (favori AJAX, etc.)
 * Brief V2 - 2026-05-15
 */
(function () {
  'use strict';

  // Récupère le token CSRF depuis la meta du layout
  function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function baseUrl() {
    const meta = document.querySelector('meta[name="base-url"]');
    return meta ? meta.getAttribute('content') : '';
  }

  // Les favoris sont gérés en localStorage (data-fav) dans mobile.js — voir
  // template-parts/card-pro.php, single-artisan.php, single-realisation.php.
})();
