/* ============================================================
   InfoDevis — Chatbot JS
   Mots-clés enrichis : incendie, déménagement, déco, sinistre...
   Multi-métiers → 1 seul devis groupé
   ============================================================ */

(function () {
  'use strict';

  var btn      = document.getElementById('chatbot-btn');
  var win      = document.getElementById('chatbot-window');
  var msgs     = document.getElementById('chatbot-messages');
  var qr       = document.getElementById('chatbot-qr');
  var inp      = document.getElementById('chatbot-input');
  var sendBtn  = document.getElementById('chatbot-send');
  var closeBtn = document.getElementById('chatbot-close');

  if (!btn) return;

  var baseUrl = (document.querySelector('meta[name="base-url"]') || {}).content || '';
  var greeted = false;

  /* ── Mots-clés → slugs ─────────────────────────────────── */
  var KEYWORDS = {
    // Plomberie
    'fuite':['plomberie'], 'robinet':['plomberie'], 'tuyau':['plomberie'],
    'canalisation':['plomberie'], 'wc':['plomberie'], 'toilette':['plomberie'],
    'evacuation':['plomberie'], 'siphon':['plomberie'], 'douche':['plomberie','renovation'],
    'baignoire':['plomberie','renovation'], 'chauffe-eau':['plomberie','chauffage'],
    'ballon':['plomberie','chauffage'], 'sanitaire':['plomberie'],
    // Électricité
    'electricite':['electricite'], 'prise':['electricite'], 'tableau':['electricite'],
    'disjoncteur':['electricite'], 'lumiere':['electricite'], 'eclairage':['electricite'],
    'interrupteur':['electricite'], 'cablage':['electricite'], 'compteur':['electricite'],
    'court-circuit':['electricite'], 'fusible':['electricite'], 'circuit':['electricite'],
    // Chauffage
    'chauffage':['chauffage'], 'chaudiere':['chauffage'], 'radiateur':['chauffage'],
    'pac':['chauffage','energies-renouvelables'], 'pompe a chaleur':['chauffage','energies-renouvelables'],
    'plancher chauffant':['chauffage'], 'thermostat':['chauffage'], 'cheminee':['chauffage','maconnerie'],
    // Toiture
    'toiture':['toiture'], 'toit':['toiture'], 'tuile':['toiture'], 'gouttiere':['toiture'],
    'charpente':['toiture'], 'zinguerie':['toiture'], 'lucarne':['toiture'], 'velux':['toiture'],
    'infiltration':['toiture','maconnerie'], 'fuite toit':['toiture'],
    // Peinture
    'peinture':['peinture'], 'repeindre':['peinture'], 'enduit':['peinture','maconnerie'],
    'ravalement':['peinture','maconnerie'], 'tapisserie':['peinture'], 'papier peint':['peinture'],
    'boiserie':['peinture','menuiserie'], 'lasure':['peinture'],
    // Maçonnerie
    'fissure':['maconnerie'], 'lezarde':['maconnerie'], 'mur':['maconnerie','peinture'],
    'plafond':['maconnerie','peinture'], 'facade':['maconnerie','peinture'],
    'beton':['maconnerie'], 'parpaing':['maconnerie'], 'brique':['maconnerie'],
    'demolition':['maconnerie','renovation'], 'dalle':['maconnerie','carrelage'],
    'fondation':['maconnerie'], 'terrassement':['maconnerie','amenagements-exterieurs'],
    // Isolation
    'isolation':['isolation'], 'combles':['isolation'], 'isoler':['isolation'],
    'laine de verre':['isolation'], 'laine de roche':['isolation'],
    'double vitrage':['isolation','menuiserie'], 'pont thermique':['isolation'],
    // Menuiserie
    'fenetre':['menuiserie','isolation'], 'porte':['menuiserie'], 'volet':['menuiserie'],
    'escalier':['menuiserie'], 'parquet':['menuiserie','renovation'], 'placard':['menuiserie'],
    'dressing':['menuiserie'], 'biblioth':['menuiserie'], 'bois':['menuiserie'],
    'portail':['menuiserie','amenagements-exterieurs'], 'cloture':['amenagements-exterieurs'],
    // Rénovation & déco
    'renovation':['renovation'], 'renover':['renovation'], 'refaire':['renovation'],
    'salle de bain':['renovation','plomberie','carrelage'],
    'salle de bains':['renovation','plomberie','carrelage'],
    'cuisine':['renovation','plomberie','electricite'],
    'chambre':['renovation','peinture'], 'salon':['renovation','peinture'],
    'sejour':['renovation','peinture'], 'couloir':['renovation','peinture'],
    'decoration':['renovation','peinture'], 'deco':['renovation','peinture'],
    'interieur':['renovation','peinture'], 'amenagement':['renovation'],
    'parquet flottant':['menuiserie','renovation'], 'sol':['carrelage','renovation'],
    'revetement':['carrelage','renovation'],
    // Sinistres & urgences
    'incendie':['renovation','maconnerie','electricite','peinture'],
    'brule':['renovation','maconnerie','electricite','peinture'],
    'feu':['renovation','maconnerie','electricite'],
    'degat des eaux':['plomberie','renovation'],
    'degats des eaux':['plomberie','renovation'],
    'inondation':['plomberie','renovation','maconnerie'],
    'sinistre':['renovation','maconnerie','plomberie'],
    'assurance':['renovation','maconnerie'],
    'catastrophe':['renovation','maconnerie'],
    'tempete':['toiture','menuiserie'],
    'grele':['toiture','menuiserie'],
    // Déménagement & nouveau logement
    'emmenager':['renovation','peinture'],
    'emenager':['renovation','peinture'],
    'demenager':['renovation','peinture'],
    'demenagement':['renovation','peinture'],
    'nouvelle maison':['renovation','peinture','electricite','plomberie'],
    'nouvel appartement':['renovation','peinture','electricite'],
    'achat maison':['renovation','peinture','electricite','plomberie'],
    'premier logement':['renovation','peinture'],
    'logement':['renovation'],
    'maison':['renovation'],
    // Carrelage
    'carrelage':['carrelage'], 'carreaux':['carrelage'], 'faience':['carrelage'],
    'joint':['carrelage'], 'mosaique':['carrelage'],
    // Jardinage & extérieur
    'jardin':['jardinage'], 'pelouse':['jardinage'], 'haie':['jardinage'],
    'taille':['jardinage'], 'tonte':['jardinage'], 'arbres':['jardinage'],
    'terrasse':['amenagements-exterieurs','jardinage'],
    'terrasse bois':['menuiserie','amenagements-exterieurs'],
    'piscine':['amenagements-exterieurs'],
    'allee':['amenagements-exterieurs','maconnerie'],
    // Climatisation & énergie
    'clim':['climatisation'], 'climatisation':['climatisation'], 'ventilation':['climatisation'],
    'vmc':['climatisation','isolation'], 'aerothermie':['climatisation','energies-renouvelables'],
    'panneau solaire':['energies-renouvelables'], 'solaire':['energies-renouvelables'],
    'photovoltaique':['energies-renouvelables'], 'pompe':['plomberie','chauffage']
  };

  var CATEGORIES = {
    'plomberie':              {label:'Plomberie',              icon:'🔧'},
    'electricite':            {label:'Électricité',             icon:'⚡'},
    'peinture':               {label:'Peinture & déco',        icon:'🖌️'},
    'toiture':                {label:'Toiture',                icon:'🏠'},
    'chauffage':              {label:'Chauffage',              icon:'🔥'},
    'menuiserie':             {label:'Menuiserie',             icon:'🪚'},
    'climatisation':          {label:'Climatisation',          icon:'❄️'},
    'isolation':              {label:'Isolation',              icon:'🧱'},
    'maconnerie':             {label:'Maçonnerie',             icon:'⬛'},
    'carrelage':              {label:'Carrelage',              icon:'◻️'},
    'jardinage':              {label:'Jardinage',              icon:'🌿'},
    'renovation':             {label:'Rénovation',             icon:'🏗️'},
    'energies-renouvelables': {label:'Énergies renouvelables', icon:'☀️'},
    'amenagements-exterieurs':{label:'Aménagements extérieurs',icon:'🏊'}
  };

  /* ── Ouverture / Fermeture ─────────────────────────────── */
  btn.addEventListener('click', function() { win.classList.contains('open') ? fermer() : ouvrir(); });
  closeBtn.addEventListener('click', fermer);

  function ouvrir() {
    win.classList.add('open'); btn.style.display = 'none';
    if (!greeted) { greeted = true; bienvenue(); }
    setTimeout(function() { inp.focus(); }, 300);
  }
  function fermer() { win.classList.remove('open'); btn.style.display = ''; }

  /* ── Bienvenue ─────────────────────────────────────────── */
  function bienvenue() {
    bot('👋 Bonjour ! Je suis l\'assistant InfoDevis.');
    setTimeout(function() {
      bot('Décrivez votre situation en langage naturel — même en cas de sinistre ou déménagement, je comprends !', true);
      rapides(['🔥 Sinistre / incendie', '💧 Fuite ou dégât des eaux', '🏗️ Rénover une nouvelle maison', '⚡ Problème électrique']);
    }, 500);
  }

  /* ── Envoi message ─────────────────────────────────────── */
  sendBtn.addEventListener('click', envoyer);
  inp.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); envoyer(); } });

  function envoyer() {
    var t = inp.value.trim(); if (!t) return;
    inp.value = ''; qr.innerHTML = '';
    user(t); analyser(t);
  }

  /* ── Boutons rapides ───────────────────────────────────── */
  function rapides(liste) {
    qr.innerHTML = '';
    liste.forEach(function(item) {
      var b = document.createElement('button');
      b.className = 'quick-reply-btn'; b.textContent = item;
      b.addEventListener('click', function() { qr.innerHTML = ''; user(item); analyser(item); });
      qr.appendChild(b);
    });
  }

  /* ── Normalisation ─────────────────────────────────────── */
  function norm(t) {
    return t.toLowerCase()
      .replace(/[àáâã]/g,'a').replace(/[éèêë]/g,'e')
      .replace(/[îï]/g,'i').replace(/[ôõö]/g,'o')
      .replace(/[ùúûü]/g,'u').replace(/ç/g,'c')
      .replace(/œ/g,'oe').replace(/æ/g,'ae');
  }

  function detecter(texte) {
    var n = norm(texte), found = {};
    // Trier par longueur décroissante pour détecter d'abord les expressions longues
    var keys = Object.keys(KEYWORDS).sort(function(a,b){ return b.length - a.length; });
    for (var i = 0; i < keys.length; i++) {
      var kw = keys[i];
      if (n.indexOf(norm(kw)) !== -1) {
        KEYWORDS[kw].forEach(function(s){ found[s] = true; });
      }
    }
    return Object.keys(found);
  }

  /* ── Analyse ───────────────────────────────────────────── */
  function analyser(texte) {
    var slugs = detecter(texte);
    setTimeout(function() {
      if (!slugs.length) {
        bot('Je n\'ai pas bien saisi. Pouvez-vous préciser ?');
        setTimeout(function() {
          bot('Essayez des mots comme : <strong>incendie, fuite, rénovation, déménagement, électricité, peinture...</strong>', true);
          rapides(['🔥 Sinistre / incendie', '💧 Fuite ou dégât des eaux', '🏗️ Rénover une nouvelle maison', '⚡ Problème électrique']);
        }, 400);
        return;
      }

      var intro = slugs.length > 1
        ? '👍 J\'ai détecté <strong>' + slugs.length + ' types de travaux</strong> dans votre demande. Je vais créer un <strong>devis groupé</strong> — un seul formulaire pour tout !'
        : '👍 J\'ai bien compris votre problème !';
      bot(intro, true);
      setTimeout(function() { afficherPicker(slugs); }, 300);
    }, 600);
  }

  /* ── Picker catégories ─────────────────────────────────── */
  function afficherPicker(slugs) {
    var wrap = document.createElement('div');
    wrap.className = 'chat-msg bot';

    var hint = document.createElement('p');
    hint.style.cssText = 'font-size:12px;color:#666;margin-bottom:8px';
    hint.textContent = '✏️ Cochez / décochez selon vos besoins :';
    wrap.appendChild(hint);

    var cbs = document.createElement('div');
    cbs.className = 'category-checkboxes';
    slugs.forEach(function(slug) {
      var cat = CATEGORIES[slug]; if (!cat) return;
      var lbl = document.createElement('label'); lbl.className = 'cat-check-label';
      var cb  = document.createElement('input'); cb.type = 'checkbox'; cb.value = slug; cb.checked = true;
      var ico = document.createElement('span'); ico.textContent = cat.icon + ' ';
      var nom = document.createElement('span'); nom.textContent = cat.label;
      lbl.appendChild(cb); lbl.appendChild(ico); lbl.appendChild(nom);
      cbs.appendChild(lbl);
    });
    wrap.appendChild(cbs);

    var btnV = document.createElement('button');
    btnV.className = 'btn btn-primary w-full';
    btnV.style.marginTop = '12px';

    function majBtn() {
      var n = cbs.querySelectorAll('input:checked').length;
      btnV.textContent = n > 1
        ? '📋 Créer 1 devis groupé (' + n + ' métiers) →'
        : '📋 Demander mon devis →';
    }
    majBtn();
    cbs.addEventListener('change', majBtn);

    btnV.addEventListener('click', function() {
      var coches = cbs.querySelectorAll('input:checked');
      if (!coches.length) { bot('Cochez au moins une catégorie.'); return; }
      var labels = [], params = [];
      coches.forEach(function(c) {
        var cat = CATEGORIES[c.value];
        if (cat) labels.push(cat.icon + ' ' + cat.label);
        params.push(c.value);
      });
      wrap.querySelectorAll('input,button').forEach(function(el){ el.disabled = true; });
      user(labels.join(' + '));
      setTimeout(function() {
        var msg = params.length > 1
          ? '✅ Je crée <strong>un seul devis groupé</strong> pour : <strong>' + labels.join(', ') + '</strong>'
          : '✅ Redirection vers le formulaire pour <strong>' + labels[0] + '</strong>';
        bot(msg, true);
        setTimeout(function() {
          var url = baseUrl + '/devis?categories=' + encodeURIComponent(params.join(','));
          var lien = document.createElement('a');
          lien.href = url; lien.className = 'btn btn-primary btn-sm';
          lien.style.cssText = 'display:inline-flex;margin:4px 16px 8px';
          lien.textContent = params.length > 1 ? '📋 Remplir mon devis groupé →' : '📋 Remplir ma demande →';
          msgs.appendChild(lien);
          setTimeout(function() {
            bot('Vous avez d\'autres besoins ?');
            rapides(['↩ Nouveau problème', '➕ Ajouter un autre métier']);
          }, 800);
          msgs.scrollTop = msgs.scrollHeight;
        }, 600);
      }, 400);
    });

    wrap.appendChild(btnV);
    msgs.appendChild(wrap);
    msgs.scrollTop = msgs.scrollHeight;
  }

  function bot(t, html) {
    var d = document.createElement('div'); d.className = 'chat-msg bot';
    if (html) d.innerHTML = t; else d.textContent = t;
    msgs.appendChild(d); msgs.scrollTop = msgs.scrollHeight;
  }
  function user(t) {
    var d = document.createElement('div'); d.className = 'chat-msg user';
    d.textContent = t; msgs.appendChild(d); msgs.scrollTop = msgs.scrollHeight;
  }

})();