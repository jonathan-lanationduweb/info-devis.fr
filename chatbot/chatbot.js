/* ============================================================
   InfoDevis — Chatbot JS
   Détection mots-clés locale, catégories à cocher, sans API
   ============================================================ */

(function () {
  'use strict';

  /* ── Éléments DOM ─────────────────────────────────────── */
  var btn      = document.getElementById('chatbot-btn');
  var win      = document.getElementById('chatbot-window');
  var msgs     = document.getElementById('chatbot-messages');
  var qr       = document.getElementById('chatbot-qr');
  var inp      = document.getElementById('chatbot-input');
  var sendBtn  = document.getElementById('chatbot-send');
  var closeBtn = document.getElementById('chatbot-close');

  if (!btn) return;

  var baseUrl      = document.querySelector('meta[name="base-url"]') ? document.querySelector('meta[name="base-url"]').content : '';
  var greeted      = false;

  /* ── Mots-clés → slugs catégories ────────────────────── */
  var KEYWORDS = {
    'fuite':             ['plomberie'],
    'robinet':           ['plomberie'],
    'tuyau':             ['plomberie'],
    'eau':               ['plomberie'],
    'canalisation':      ['plomberie'],
    'wc':                ['plomberie'],
    'toilette':          ['plomberie'],
    'evacuation':        ['plomberie'],
    'chauffe-eau':       ['plomberie', 'chauffage'],
    'ballon':            ['plomberie', 'chauffage'],
    'fissure':           ['maconnerie'],
    'lezarde':           ['maconnerie'],
    'mur':               ['maconnerie', 'peinture'],
    'plafond':           ['maconnerie', 'peinture', 'plomberie'],
    'facade':            ['maconnerie', 'peinture'],
    'peinture':          ['peinture'],
    'repeindre':         ['peinture'],
    'enduit':            ['peinture', 'maconnerie'],
    'ravalement':        ['peinture', 'maconnerie'],
    'electricite':       ['electricite'],
    'prise':             ['electricite'],
    'tableau':           ['electricite'],
    'disjoncteur':       ['electricite'],
    'lumiere':           ['electricite'],
    'interrupteur':      ['electricite'],
    'cablage':           ['electricite'],
    'chauffage':         ['chauffage'],
    'chaudiere':         ['chauffage'],
    'radiateur':         ['chauffage'],
    'pac':               ['chauffage', 'energies-renouvelables'],
    'toiture':           ['toiture'],
    'toit':              ['toiture'],
    'tuile':             ['toiture'],
    'gouttiere':         ['toiture'],
    'charpente':         ['toiture'],
    'isolation':         ['isolation'],
    'combles':           ['isolation'],
    'isoler':            ['isolation'],
    'fenetre':           ['menuiserie', 'isolation'],
    'porte':             ['menuiserie'],
    'volet':             ['menuiserie'],
    'escalier':          ['menuiserie'],
    'renovation':        ['renovation'],
    'salle de bain':     ['renovation', 'plomberie', 'carrelage'],
    'cuisine':           ['renovation', 'plomberie', 'electricite'],
    'carrelage':         ['carrelage'],
    'parquet':           ['renovation'],
    'cloison':           ['maconnerie', 'renovation'],
    'jardin':            ['jardinage'],
    'pelouse':           ['jardinage'],
    'haie':              ['jardinage'],
    'terrasse':          ['amenagements-exterieurs', 'jardinage'],
    'clim':              ['climatisation'],
    'climatisation':     ['climatisation'],
    'panneau solaire':   ['energies-renouvelables'],
    'solaire':           ['energies-renouvelables']
  };

  /* ── Labels lisibles par slug ─────────────────────────── */
  var CATEGORIES = {
    'plomberie':               { label: 'Plomberie',               icon: '🔧' },
    'electricite':             { label: 'Électricité',              icon: '⚡' },
    'peinture':                { label: 'Peinture',                 icon: '🖌️' },
    'toiture':                 { label: 'Toiture',                  icon: '🏠' },
    'chauffage':               { label: 'Chauffage',                icon: '🔥' },
    'menuiserie':              { label: 'Menuiserie',               icon: '🪚' },
    'climatisation':           { label: 'Climatisation',            icon: '❄️' },
    'isolation':               { label: 'Isolation',                icon: '🧱' },
    'maconnerie':              { label: 'Maçonnerie',               icon: '⬛' },
    'carrelage':               { label: 'Carrelage',                icon: '◻️' },
    'jardinage':               { label: 'Jardinage',                icon: '🌿' },
    'renovation':              { label: 'Rénovation',               icon: '🏗️' },
    'energies-renouvelables':  { label: 'Énergies renouvelables',   icon: '☀️' },
    'amenagements-exterieurs': { label: 'Aménagements extérieurs',  icon: '🏊' }
  };

  /* ── Ouverture / Fermeture ────────────────────────────── */
  btn.addEventListener('click', function () {
    win.classList.contains('open') ? fermer() : ouvrir();
  });

  closeBtn.addEventListener('click', fermer);

  function ouvrir() {
    win.classList.add('open');
    btn.style.display = 'none';
    if (!greeted) { greeted = true; bienvenue(); }
    setTimeout(function () { inp.focus(); }, 300);
  }

  function fermer() {
    win.classList.remove('open');
    btn.style.display = '';
  }

  /* ── Message de bienvenue ─────────────────────────────── */
  function bienvenue() {
    ajouterMsg('👋 Bonjour ! Je suis l\'assistant InfoDevis.', 'bot');

    setTimeout(function () {
      ajouterMsg('Décrivez-moi votre problème et je vous propose les artisans qu\'il vous faut.', 'bot');
      afficherRapides([
        '💧 Fuite d\'eau',
        '⬛ Fissure mur / plafond',
        '⚡ Problème électricité',
        '🏗️ Rénovation'
      ]);
    }, 500);
  }

  /* ── Envoi message ────────────────────────────────────── */
  sendBtn.addEventListener('click', envoyer);
  inp.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); envoyer(); }
  });

  function envoyer() {
    var texte = inp.value.trim();
    if (!texte) return;
    inp.value = '';
    qr.innerHTML = '';
    ajouterMsg(texte, 'user');
    analyser(texte);
  }

  /* ── Boutons rapides ──────────────────────────────────── */
  function afficherRapides(liste) {
    qr.innerHTML = '';
    liste.forEach(function (item) {
      var b = document.createElement('button');
      b.className = 'quick-reply-btn';
      b.textContent = item;
      b.addEventListener('click', function () {
        qr.innerHTML = '';
        ajouterMsg(item, 'user');
        analyser(item);
      });
      qr.appendChild(b);
    });
  }

  /* ── Détection mots-clés ──────────────────────────────── */
  function normaliser(texte) {
    return texte.toLowerCase()
      .replace(/[àáâã]/g, 'a')
      .replace(/[éèêë]/g, 'e')
      .replace(/[îï]/g, 'i')
      .replace(/[ôõ]/g, 'o')
      .replace(/[ùúû]/g, 'u')
      .replace(/ç/g, 'c');
  }

  function detecterCategories(texte) {
    var norm  = normaliser(texte);
    var found = {};
    var kw, slugs, i;
    for (kw in KEYWORDS) {
      if (norm.indexOf(normaliser(kw)) !== -1) {
        slugs = KEYWORDS[kw];
        for (i = 0; i < slugs.length; i++) {
          found[slugs[i]] = true;
        }
      }
    }
    return Object.keys(found);
  }

  function analyser(texte) {
    var slugs = detecterCategories(texte);

    setTimeout(function () {
      if (slugs.length > 0) {
        ajouterMsg('J\'ai bien compris votre problème ! Voici les interventions qui peuvent vous aider :', 'bot');
        setTimeout(function () { afficherCategories(slugs); }, 300);
      } else {
        ajouterMsg('Je n\'ai pas bien saisi votre problème. Pouvez-vous préciser ?', 'bot');
        setTimeout(function () {
          ajouterMsg('Exemple : fuite robinet, fissure mur extérieur, radiateur qui chauffe pas...', 'bot');
          afficherRapides([
            '💧 Fuite d\'eau',
            '⬛ Fissure mur / plafond',
            '⚡ Problème électricité',
            '🏗️ Rénovation'
          ]);
        }, 400);
      }
    }, 600);
  }

  /* ── Affichage catégories à cocher ───────────────────── */
  function afficherCategories(slugs) {
    var wrap = document.createElement('div');
    wrap.className = 'chat-msg bot';
    wrap.id = 'cat-picker';

    var checkboxes = document.createElement('div');
    checkboxes.className = 'category-checkboxes';

    slugs.forEach(function (slug) {
      var cat = CATEGORIES[slug];
      if (!cat) return;

      var label = document.createElement('label');
      label.className = 'cat-check-label';

      var cb = document.createElement('input');
      cb.type    = 'checkbox';
      cb.value   = slug;
      cb.checked = true;

      var icone = document.createElement('span');
      icone.textContent = cat.icon + ' ';

      var nom = document.createElement('span');
      nom.textContent = cat.label;

      label.appendChild(cb);
      label.appendChild(icone);
      label.appendChild(nom);
      checkboxes.appendChild(label);
    });

    var valider = document.createElement('button');
    valider.className = 'btn btn-primary w-full';
    valider.style.marginTop = '12px';
    valider.textContent = 'Demander mes devis →';

    valider.addEventListener('click', function () {
      var coches = checkboxes.querySelectorAll('input:checked');
      if (coches.length === 0) {
        ajouterMsg('Veuillez cocher au moins une catégorie.', 'bot');
        return;
      }

      var labels = [];
      var params = [];
      coches.forEach(function (cb) {
        var cat = CATEGORIES[cb.value];
        if (cat) labels.push(cat.label);
        params.push(cb.value);
      });

      /* Désactiver le picker */
      wrap.querySelectorAll('input, button').forEach(function (el) { el.disabled = true; });

      ajouterMsg(labels.join(', '), 'user');

      setTimeout(function () {
        ajouterMsg('Parfait ! Je vous redirige vers le formulaire de devis pour : ' + labels.join(', ') + '.', 'bot');

        setTimeout(function () {
          var url = baseUrl + '/devis?categories=' + encodeURIComponent(params.join(','));

          var lien = document.createElement('a');
          lien.href      = url;
          lien.className = 'btn btn-primary btn-sm';
          lien.style.cssText = 'display:inline-flex;margin:4px 16px 8px;';
          lien.textContent = 'Remplir ma demande de devis →';
          msgs.appendChild(lien);

          setTimeout(function () {
            ajouterMsg('Vous avez d\'autres problèmes ?', 'bot');
            afficherRapides(['↩ Nouveau problème']);
          }, 800);

          msgs.scrollTop = msgs.scrollHeight;
        }, 600);
      }, 400);
    });

    wrap.appendChild(checkboxes);
    wrap.appendChild(valider);
    msgs.appendChild(wrap);
    msgs.scrollTop = msgs.scrollHeight;
  }

  /* ── Ajouter bulle message ────────────────────────────── */
  function ajouterMsg(texte, type) {
    var div = document.createElement('div');
    div.className = 'chat-msg ' + type;
    div.textContent = texte;
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
  }

})();