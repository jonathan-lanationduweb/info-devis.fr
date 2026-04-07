<!-- Chatbot InfoDevis -->
<button class="chatbot-btn" id="chatbot-btn" aria-label="Ouvrir l'assistant" title="Assistant InfoDevis">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
  </svg>
</button>

<div class="chatbot-window" id="chatbot-window" role="dialog" aria-label="Assistant InfoDevis">
  <div class="chatbot-header">
    <div class="chatbot-avatar">🤖</div>
    <div>
      <div class="chatbot-name">Assistant InfoDevis</div>
      <div class="chatbot-status">● En ligne — Répond en quelques secondes</div>
    </div>
    <button class="chatbot-close" id="chatbot-close" aria-label="Fermer">✕</button>
  </div>

  <div class="chatbot-messages" id="chatbot-messages">
    <!-- Messages injectés par JS -->
  </div>

  <div class="chatbot-quick-replies" id="chatbot-qr"></div>

  <div class="chatbot-input-bar">
    <input
      type="text"
      id="chatbot-input"
      class="chatbot-input"
      placeholder="Décrivez votre problème ou besoin..."
      maxlength="500"
      autocomplete="off"
    >
    <button class="chatbot-send" id="chatbot-send" aria-label="Envoyer">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
      </svg>
    </button>
  </div>
</div>
