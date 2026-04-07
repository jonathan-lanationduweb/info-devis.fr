/* ============================================================
   InfoDevis — Chatbot JS
   Détection mots-clés, multi-catégories, collecte formulaire
   ============================================================ */

(function () {
  'use strict';

  const BASE_URL  = document.querySelector('meta[name="base-url"]')?.content || '';
  const API_URL   = BASE_URL + '/api/chatbot';
  const SESSION   = 'cb_' + Math.random().toString(36).substr(2, 9);

  let step            = 'initial';
  let selectedCats    = [];
  let formData        = {};
  let autoOpened      = false;

  const btn     = document.getElementById('chatbot-btn');
  const win     = document.getElementById('chatbot-window');
  const msgs    = document.getElementById('chatbot-messages');
  const qr      = document.getElementById('chatbot-qr');
  const inp     = document.getElementById('chatbot-input');
  const send    = document.getElementById('chatbot-send');
  const closeBtn= document.getElementById('chatbot-close');

  if (!btn) return;

  // ── Toggle ───────────────────────────────────────────────
  btn.addEventListener('click', toggle);
  closeBtn.addEventListener('click', close);

  function toggle() { win.classList.contains('open') ? close() : open(); }
  function open()   { win.classList.add('open'); btn.style.display = 'none'; if (!msgs.children.length) welcome(); }
  function close()  { win.classList.remove('open'); btn.style.display = ''; }

  // Auto-open après 8s
  setTimeout(() => {
    if (!autoOpened && !win.classList.contains('open')) {
      autoOpened = true; open();
    }
  }, 8000);

  // ── Send ────────────────────────────────────────────────
  send.addEventListener('click', handleSend);
  inp.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleSend(); } });

  function handleSend() {
    const text = inp.value.trim();
    if (!text) return;
    inp.value = '';
    addMsg(text, 'user');
    sendToApi(text);
  }

  // ── API ────────────────────────────────────────────────
  async function sendToApi(message) {
    addTyping();
    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message, session_id: SESSION, step, selected_categories: selectedCats, form_data: formData }),
      });
      const data = await res.json();
      removeTyping();
      handleResponse(data);
    } catch {
      removeTyping();
      addMsg('Désolé, une erreur s\'est produite. Réessayez.', 'bot');
    }
  }

  // ── Response handler ───────────────────────────────────
  function handleResponse(data) {
    if (data.step) step = data.step;

    switch (data.type) {
      case 'category_selection':
        addMsg(data.message, 'bot');
        renderCategorySelection(data.categories, data.cta);
        break;
      case 'form':
        addMsg(data.message, 'bot');
        renderForm(data.fields, data.categories);
        break;
      case 'confirmation':
        addMsg(data.message, 'bot');
        renderConfirmation(data);
        break;
      case 'price':
        addMsg(data.message.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>'), 'bot', true);
        if (data.cta) addCta(data.cta, data.action);
        break;
      default:
        addMsg(data.message, 'bot');
        if (data.quick_replies?.length) renderQuickReplies(data.quick_replies);
        if (data.cta) addCta(data.cta, data.action);
    }

    msgs.scrollTop = msgs.scrollHeight;
  }

  // ── Welcome ────────────────────────────────────────────
  function welcome() {
    setTimeout(() => {
      addMsg('👋 Bonjour ! Je suis l\'assistant InfoDevis. Je peux vous aider à trouver un artisan ou à obtenir un devis gratuit.', 'bot');
      renderQuickReplies(['Demander un devis', 'Trouver un artisan', 'Connaître les prix', 'Autre question']);
    }, 400);
  }

  // ── Category selection ─────────────────────────────────
  function renderCategorySelection(categories, ctaLabel) {
    qr.innerHTML = '';
    const div = document.createElement('div');
    div.className = 'category-checkboxes';
    div.style.padding = '8px 16px';

    categories.forEach(cat => {
      const label = document.createElement('label');
      label.className = 'cat-check-label';
      label.innerHTML = `
        <input type="checkbox" value="${cat.id}" data-name="${cat.name}" checked>
        <span>${cat.name}</span>
      `;
      div.appendChild(label);
    });

    const cta = document.createElement('button');
    cta.className = 'btn btn-primary w-full';
    cta.style.margin = '12px 0 8px';
    cta.textContent = ctaLabel || 'Envoyer ma demande de devis';
    cta.addEventListener('click', () => {
      selectedCats = [...div.querySelectorAll('input:checked')].map(i => ({id: i.value, name: i.dataset.name}));
      if (!selectedCats.length) { addMsg('Veuillez sélectionner au moins une catégorie.', 'bot'); return; }
      div.remove();
      sendToApi('Catégories sélectionnées: ' + selectedCats.map(c => c.name).join(', '));
    });

    msgs.appendChild(div);
    msgs.appendChild(cta);
    msgs.scrollTop = msgs.scrollHeight;
  }

  // ── Form ───────────────────────────────────────────────
  function renderForm(fields, categories) {
    selectedCats = categories || selectedCats;
    const container = document.createElement('div');
    container.style.cssText = 'padding:8px 16px;display:flex;flex-direction:column;gap:10px;';

    fields.forEach(f => {
      const wrap = document.createElement('div');
      if (f.type === 'textarea') {
        wrap.innerHTML = `
          <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px">${f.label}${f.required ? ' *' : ''}</label>
          <textarea name="${f.name}" class="chatbot-input" style="min-height:70px;resize:vertical;border-radius:12px;padding:10px 14px;width:100%;" placeholder="${f.label}"></textarea>`;
      } else if (f.type === 'select') {
        const opts = Object.entries(f.options || {}).map(([v,l]) => `<option value="${v}">${l}</option>`).join('');
        wrap.innerHTML = `
          <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px">${f.label}</label>
          <select name="${f.name}" class="chatbot-input" style="border-radius:12px;padding:10px 14px;width:100%;">${opts}</select>`;
      } else {
        wrap.innerHTML = `
          <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px">${f.label}${f.required ? ' *' : ''}</label>
          <input type="${f.type}" name="${f.name}" class="chatbot-input" placeholder="${f.label}" style="border-radius:12px;padding:10px 14px;width:100%;">`;
      }
      container.appendChild(wrap);
    });

    const btn = document.createElement('button');
    btn.className = 'btn btn-primary w-full';
    btn.textContent = 'Envoyer ma demande →';
    btn.addEventListener('click', () => {
      const inputs = container.querySelectorAll('input,textarea,select');
      inputs.forEach(i => { formData[i.name] = i.value; });

      // Validation basique
      const email = formData.email;
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        addMsg('L\'email saisi n\'est pas valide.', 'bot'); return;
      }
      if (!formData.first_name || !formData.email || !formData.ville) {
        addMsg('Merci de remplir les champs obligatoires (prénom, email, ville).', 'bot'); return;
      }

      container.remove();
      sendToApi('Informations complètes');
    });

    msgs.appendChild(container);
    msgs.appendChild(btn);
    msgs.scrollTop = msgs.scrollHeight;
  }

  // ── Confirmation ───────────────────────────────────────
  function renderConfirmation(data) {
    // Soumettre automatiquement le devis via fetch
    const payload = new FormData();
    payload.append('csrf_token', getCsrf());
    payload.append('email', data.form_data.email || '');
    payload.append('first_name', data.form_data.first_name || '');
    payload.append('last_name', data.form_data.last_name || '');
    payload.append('phone', data.form_data.phone || '');
    payload.append('ville', data.form_data.ville || '');
    payload.append('code_postal', data.form_data.code_postal || '');
    payload.append('description', data.form_data.description || 'Demande via chatbot');
    payload.append('urgency', data.form_data.urgency || 'normal');
    payload.append('consent_privacy', '1');
    (data.categories || selectedCats).forEach(c => payload.append('categories[]', c.id || c));

    fetch((data.action || '') + '').then(r => r.json()).then(resp => {
      if (resp.success) {
        addMsg('✅ ' + (resp.message || 'Demande envoyée avec succès !'), 'bot');
        addMsg('Référence : ' + resp.reference + '. Vous recevrez des réponses sous 24 à 48h.', 'bot');
        addCta('Suivre ma demande', window.location.origin + '/dashboard/client');
      } else {
        addMsg('Votre demande n\'a pas pu être envoyée. Contactez-nous directement.', 'bot');
        addCta('Voir le formulaire complet', data.action || '/devis');
      }
    }).catch(() => {
      addMsg('Redirection vers le formulaire complet...', 'bot');
      setTimeout(() => { window.location.href = data.action || '/devis'; }, 1500);
    });
  }

  // ── Quick replies ──────────────────────────────────────
  function renderQuickReplies(replies) {
    qr.innerHTML = '';
    replies.forEach(r => {
      const btn = document.createElement('button');
      btn.className = 'quick-reply-btn';
      btn.textContent = r;
      btn.addEventListener('click', () => {
        qr.innerHTML = '';
        addMsg(r, 'user');
        sendToApi(r);
      });
      qr.appendChild(btn);
    });
  }

  // ── CTA ────────────────────────────────────────────────
  function addCta(label, url) {
    if (!url) return;
    const a = document.createElement('a');
    a.href = url; a.className = 'btn btn-primary btn-sm';
    a.style.cssText = 'display:inline-flex;margin:4px 16px 8px;';
    a.textContent = label;
    msgs.appendChild(a);
    msgs.scrollTop = msgs.scrollHeight;
  }

  // ── Message bubble ─────────────────────────────────────
  function addMsg(text, type, html = false) {
    const div = document.createElement('div');
    div.className = 'chat-msg ' + type;
    if (html) div.innerHTML = text; else div.textContent = text;
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
  }

  // ── Typing indicator ───────────────────────────────────
  function addTyping() {
    const div = document.createElement('div');
    div.className = 'chat-msg bot'; div.id = 'typing';
    div.innerHTML = '<span style="display:flex;gap:4px;align-items:center">' +
      '<span style="width:7px;height:7px;background:#9ca3af;border-radius:50%;animation:typingDot 1s .0s infinite alternate"></span>' +
      '<span style="width:7px;height:7px;background:#9ca3af;border-radius:50%;animation:typingDot 1s .2s infinite alternate"></span>' +
      '<span style="width:7px;height:7px;background:#9ca3af;border-radius:50%;animation:typingDot 1s .4s infinite alternate"></span>' +
      '</span>';
    if (!document.getElementById('typing-style')) {
      const s = document.createElement('style');
      s.id = 'typing-style';
      s.textContent = '@keyframes typingDot{from{transform:translateY(0)}to{transform:translateY(-5px)}}';
      document.head.appendChild(s);
    }
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
  }
  function removeTyping() { document.getElementById('typing')?.remove(); }

  function getCsrf() {
    return document.querySelector('input[name="csrf_token"]')?.value ||
           document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

})();
