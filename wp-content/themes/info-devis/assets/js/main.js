/* ============================================================
   InfoDevis — Main JS
   ============================================================ */
'use strict';

// ── Header scroll ─────────────────────────────────────────────
const header = document.getElementById('site-header');
if (header) {
  window.addEventListener('scroll', () => {
    header.classList.toggle('scrolled', window.scrollY > 20);
  }, { passive: true });
}

// ── Hamburger menu ────────────────────────────────────────────
const burger    = document.getElementById('hamburger');
const mobileNav = document.getElementById('mobile-nav');
if (burger && mobileNav) {
  burger.addEventListener('click', () => {
    const open = mobileNav.classList.toggle('open');
    burger.classList.toggle('active', open);
    burger.setAttribute('aria-expanded', open);
  });
  // Fermer au clic extérieur
  document.addEventListener('click', (e) => {
    if (!burger.contains(e.target) && !mobileNav.contains(e.target)) {
      mobileNav.classList.remove('open');
      burger.classList.remove('active');
    }
  });
}

// ── Scroll reveal ─────────────────────────────────────────────
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(el => {
    if (el.isIntersecting) { el.target.classList.add('visible'); }
  });
}, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// ── Flash messages auto-dismiss ───────────────────────────────
document.querySelectorAll('.alert[data-auto-dismiss]').forEach(el => {
  setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 4000);
});

// ── Form validation ───────────────────────────────────────────
document.querySelectorAll('form[data-validate]').forEach(form => {
  form.addEventListener('submit', (e) => {
    let valid = true;
    form.querySelectorAll('[required]').forEach(field => {
      field.classList.remove('error');
      const err = field.parentNode.querySelector('.form-error');
      if (err) err.remove();
      if (!field.value.trim()) {
        valid = false;
        field.classList.add('error');
        const msg = document.createElement('p');
        msg.className = 'form-error';
        msg.textContent = 'Ce champ est requis.';
        field.parentNode.appendChild(msg);
      }
    });
    if (!valid) e.preventDefault();
  });
});

// NB : l'envoi AJAX du formulaire de devis est géré par le script inline de
// page-devis.php (gabarit dédié, gestion d'erreurs par étape). L'ancien
// gestionnaire générique a été retiré d'ici pour éviter un double envoi.

// ── Rating stars interactive ──────────────────────────────────
document.querySelectorAll('.star-rating').forEach(container => {
  const stars  = container.querySelectorAll('[data-star]');
  const input  = container.querySelector('input[name="rating"]');
  stars.forEach((star, i) => {
    star.addEventListener('mouseenter', () => {
      stars.forEach((s, j) => s.textContent = j <= i ? '★' : '☆');
    });
    star.addEventListener('click', () => {
      if (input) input.value = i + 1;
      stars.forEach((s, j) => {
        s.style.color = j <= i ? '#fbbf24' : '#d1d5db';
        s.textContent = j <= i ? '★' : '☆';
      });
    });
  });
  container.addEventListener('mouseleave', () => {
    const val = parseInt(input?.value || 0);
    stars.forEach((s, j) => {
      s.style.color = j < val ? '#fbbf24' : '#d1d5db';
      s.textContent = j < val ? '★' : '☆';
    });
  });
});

// ── Stripe payment init ───────────────────────────────────────
async function initStripePayment(clientSecret, publicKey) {
  if (!window.Stripe) return;
  const stripe  = Stripe(publicKey);
  const elements = stripe.elements({ clientSecret });
  const payment = elements.create('payment');
  payment.mount('#stripe-element');

  document.getElementById('pay-btn')?.addEventListener('click', async () => {
    const { error } = await stripe.confirmPayment({ elements, confirmParams: { return_url: window.location.href + '?paid=1' } });
    if (error) {
      document.getElementById('pay-error').textContent = error.message;
    }
  });
}

// ── Signature pad ─────────────────────────────────────────────
function initSignaturePad(canvasId) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx    = canvas.getContext('2d');
  let drawing  = false;
  let lastX = 0, lastY = 0;

  canvas.addEventListener('mousedown',  e => { drawing = true; [lastX, lastY] = [e.offsetX, e.offsetY]; });
  canvas.addEventListener('mousemove',  e => {
    if (!drawing) return;
    ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(e.offsetX, e.offsetY);
    ctx.strokeStyle = '#0f2460'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke();
    [lastX, lastY] = [e.offsetX, e.offsetY];
  });
  canvas.addEventListener('mouseup',   () => { drawing = false; });
  canvas.addEventListener('mouseleave',() => { drawing = false; });
  canvas.addEventListener('touchstart', e => {
    e.preventDefault();
    const t = e.touches[0];
    const r = canvas.getBoundingClientRect();
    [lastX, lastY] = [t.clientX - r.left, t.clientY - r.top]; drawing = true;
  }, { passive: false });
  canvas.addEventListener('touchmove', e => {
    e.preventDefault();
    if (!drawing) return;
    const t = e.touches[0];
    const r = canvas.getBoundingClientRect();
    const x = t.clientX - r.left, y = t.clientY - r.top;
    ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(x, y);
    ctx.strokeStyle = '#0f2460'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke();
    [lastX, lastY] = [x, y];
  }, { passive: false });
  canvas.addEventListener('touchend', () => { drawing = false; });

  document.getElementById('clear-sig')?.addEventListener('click', () => ctx.clearRect(0, 0, canvas.width, canvas.height));

  return () => canvas.toDataURL();
}

// ── Admin: confirm actions ────────────────────────────────────
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', (e) => {
    if (!confirm(el.dataset.confirm || 'Êtes-vous sûr ?')) e.preventDefault();
  });
});

// ── Notifications auto-mark read ─────────────────────────────
document.querySelectorAll('.notification-item[data-id]').forEach(el => {
  const id = el.dataset.id;
  fetch('/api/notifications/' + id + '/read', { method: 'PATCH' }).catch(() => {});
});

// ── Tabs ─────────────────────────────────────────────────────
document.querySelectorAll('[data-tab-target]').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = document.querySelector(btn.dataset.tabTarget);
    if (!target) return;
    const container = target.closest('[data-tabs]');
    container.querySelectorAll('[data-tab-panel]').forEach(p => p.hidden = true);
    container.querySelectorAll('[data-tab-target]').forEach(b => b.classList.remove('active'));
    target.hidden = false;
    btn.classList.add('active');
  });
});
