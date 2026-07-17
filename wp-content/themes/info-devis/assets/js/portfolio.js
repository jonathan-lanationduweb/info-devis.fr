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

  // ── Toggle favori (sur projet detail ou cartes pros) ──────────────
  document.querySelectorAll('[data-favori-toggle]').forEach((btn) => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const projetId = btn.dataset.projetId;
      if (!projetId) return;

      // Si user pas connecté, on redirige sur /connexion
      const fd = new FormData();
      fd.append('projet_id', projetId);
      fd.append('csrf_token', csrfToken());

      btn.disabled = true;
      try {
        const res = await fetch(baseUrl() + '/api/favoris/toggle', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin',
        });
        if (res.status === 401) {
          window.location.href = baseUrl() + '/connexion?redirect=' + encodeURIComponent(window.location.pathname);
          return;
        }
        const data = await res.json();
        if (data.success) {
          // Bascule visuel du coeur (FILL=1 si favori)
          const icon = btn.querySelector('.material-symbols-outlined');
          if (icon) {
            if (data.favorited) {
              icon.style.fontVariationSettings = "'FILL' 1";
              icon.style.color = '#207752';
            } else {
              icon.style.fontVariationSettings = "'FILL' 0";
              icon.style.color = '';
            }
          }
          // Met à jour le label si présent
          const label = btn.querySelector('[data-favori-label]');
          if (label) {
            label.textContent = data.favorited ? 'Retiré des favoris' : 'Ajouter aux favoris';
          }
        } else {
          console.error('Erreur favori :', data.error);
        }
      } catch (err) {
        console.error('Réseau :', err);
      } finally {
        btn.disabled = false;
      }
    });
  });
})();
