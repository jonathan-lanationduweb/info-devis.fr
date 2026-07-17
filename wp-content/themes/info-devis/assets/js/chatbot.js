/* ============================================================
   InfoDevis — Chatbot JS (v2 — FAQ + intents + fuzzy match)
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

  /* ── Mots-clés métiers → slugs ─────────────────────────── */
  var KEYWORDS = {
    // Plomberie
    'fuite':['plomberie'], 'robinet':['plomberie'], 'tuyau':['plomberie'],
    'canalisation':['plomberie'], 'wc':['plomberie'], 'toilette':['plomberie'],
    'evacuation':['plomberie'], 'siphon':['plomberie'], 'douche':['plomberie','renovation'],
    'baignoire':['plomberie','renovation'], 'chauffe-eau':['plomberie','chauffage'],
    'ballon':['plomberie','chauffage'], 'sanitaire':['plomberie'],
    'plombier':['plomberie'], 'plomberie':['plomberie'],
    // Électricité
    'electricite':['electricite'], 'electricien':['electricite'], 'prise':['electricite'],
    'tableau':['electricite'], 'disjoncteur':['electricite'], 'lumiere':['electricite'],
    'eclairage':['electricite'], 'interrupteur':['electricite'], 'cablage':['electricite'],
    'compteur':['electricite'], 'court-circuit':['electricite'], 'fusible':['electricite'],
    'circuit':['electricite'], 'electrique':['electricite'],
    // Chauffage
    'chauffage':['chauffage'], 'chaudiere':['chauffage'], 'radiateur':['chauffage'],
    'pac':['chauffage','energies-renouvelables'], 'pompe a chaleur':['chauffage','energies-renouvelables'],
    'plancher chauffant':['chauffage'], 'thermostat':['chauffage'], 'cheminee':['chauffage','maconnerie'],
    'poele':['chauffage'], 'bois':['chauffage'],
    // Toiture
    'toiture':['toiture'], 'toit':['toiture'], 'tuile':['toiture'], 'gouttiere':['toiture'],
    'charpente':['toiture'], 'zinguerie':['toiture'], 'lucarne':['toiture'], 'velux':['toiture'],
    'infiltration':['toiture','maconnerie'], 'fuite toit':['toiture'], 'couvreur':['toiture'],
    // Peinture
    'peinture':['peinture'], 'repeindre':['peinture'], 'enduit':['peinture','maconnerie'],
    'ravalement':['peinture','maconnerie'], 'tapisserie':['peinture'], 'papier peint':['peinture'],
    'boiserie':['peinture','menuiserie'], 'lasure':['peinture'], 'peintre':['peinture'],
    // Maçonnerie
    'fissure':['maconnerie'], 'lezarde':['maconnerie'], 'mur':['maconnerie','peinture'],
    'plafond':['maconnerie','peinture'], 'facade':['maconnerie','peinture'],
    'beton':['maconnerie'], 'parpaing':['maconnerie'], 'brique':['maconnerie'],
    'demolition':['maconnerie','renovation'], 'dalle':['maconnerie','carrelage'],
    'fondation':['maconnerie'], 'terrassement':['maconnerie','amenagements-exterieurs'],
    'macon':['maconnerie'], 'maconnerie':['maconnerie'],
    // Isolation
    'isolation':['isolation'], 'combles':['isolation'], 'isoler':['isolation'],
    'laine de verre':['isolation'], 'laine de roche':['isolation'],
    'double vitrage':['isolation','menuiserie'], 'pont thermique':['isolation'],
    'froid':['isolation','chauffage'], 'humidite':['isolation','maconnerie'],
    // Menuiserie
    'fenetre':['menuiserie','isolation'], 'porte':['menuiserie'], 'volet':['menuiserie'],
    'escalier':['menuiserie'], 'parquet':['menuiserie','renovation'], 'placard':['menuiserie'],
    'dressing':['menuiserie'], 'biblioth':['menuiserie'], 'menuiserie':['menuiserie'],
    'portail':['menuiserie','amenagements-exterieurs'], 'cloture':['amenagements-exterieurs'],
    'menuisier':['menuiserie'],
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
    'tempete':['toiture','menuiserie'], 'grele':['toiture','menuiserie'],
    'orage':['toiture','electricite'],
    // Déménagement & nouveau logement
    'emmenager':['renovation','peinture'], 'emenager':['renovation','peinture'],
    'demenager':['renovation','peinture'], 'demenagement':['renovation','peinture'],
    'nouvelle maison':['renovation','peinture','electricite','plomberie'],
    'nouvel appartement':['renovation','peinture','electricite'],
    'achat maison':['renovation','peinture','electricite','plomberie'],
    'premier logement':['renovation','peinture'], 'logement':['renovation'],
    'maison':['renovation'], 'appartement':['renovation'],
    // Carrelage
    'carrelage':['carrelage'], 'carreaux':['carrelage'], 'faience':['carrelage'],
    'joint':['carrelage'], 'mosaique':['carrelage'], 'carreleur':['carrelage'],
    // Jardinage & extérieur
    'jardin':['jardinage'], 'pelouse':['jardinage'], 'haie':['jardinage'],
    'taille':['jardinage'], 'tonte':['jardinage'], 'arbres':['jardinage'],
    'terrasse':['amenagements-exterieurs','jardinage'],
    'terrasse bois':['menuiserie','amenagements-exterieurs'],
    'piscine':['amenagements-exterieurs'],
    'allee':['amenagements-exterieurs','maconnerie'],
    'paysagiste':['jardinage'], 'jardinier':['jardinage'],
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

  /* ── Intents (FAQ + actions hors devis) ────────────────── */
  // Chaque intent : patterns (mots clés), handle (fonction réponse)
  // Les boutons rapides envoient un texte normalisé qui DOIT matcher au moins un intent
  var INTENTS = [
    {
      name: 'request_devis',
      patterns: [
        'demander un devis','demande de devis','je veux un devis','un devis',
        'faire ma demande','remplir devis','obtenir un devis','demander devis',
        'devis gratuit','devis pour mes travaux','nouveau probleme'
      ],
      handle: function() {
        bot('📋 <strong>Très bien !</strong> Décrivez-moi vos travaux en quelques mots et je vous mets en relation avec les bons artisans.', true);
        setTimeout(function(){
          bot('Exemples : <em>« j\'ai une fuite sous l\'évier »</em>, <em>« je rénove ma cuisine »</em>, <em>« panne électrique »</em>…', true);
          rapides([
            '💧 Plomberie / fuite',
            '⚡ Électricité',
            '🏠 Toiture',
            '🏗️ Rénovation complète',
            '🔥 Sinistre / incendie',
            '🌿 Jardin / extérieur'
          ]);
        }, 400);
      }
    },
    {
      name: 'sinistre',
      patterns: ['sinistre','incendie','feu','brule','catastrophe','assurance habitation'],
      handle: function() {
        bot('🔥 <strong>Sinistre incendie / dégâts</strong>', true);
        setTimeout(function(){
          bot(
            '<ol style="margin:0;padding-left:20px;line-height:1.8">' +
            '<li>📞 Contactez votre <strong>assurance habitation</strong></li>' +
            '<li>🚒 Constat des pompiers / police</li>' +
            '<li>📸 Photographiez tous les dégâts</li>' +
            '<li>📑 Conservez tous les justificatifs</li>' +
            '</ol>', true);
          setTimeout(function(){
            bot('Pour les travaux de remise en état, je peux vous trouver les bons artisans :');
            afficherPickerStatique(['renovation','maconnerie','electricite','peinture']);
          }, 400);
        }, 300);
      }
    },
    {
      name: 'fuite_eau',
      patterns: [
        'fuite eau','degat des eaux','degats des eaux','inondation','fuite',
        'plomberie / fuite','plomberie fuite','probleme plomberie'
      ],
      handle: function() {
        bot('💧 <strong>Fuite / dégât des eaux</strong>', true);
        setTimeout(function(){
          bot(
            '<ol style="margin:0;padding-left:20px;line-height:1.8">' +
            '<li>🚰 <strong>Coupez l\'arrivée d\'eau</strong> immédiatement</li>' +
            '<li>📞 Contactez votre assurance</li>' +
            '<li>📸 Photographiez les dégâts</li>' +
            '<li>👷 Faites venir un plombier en urgence</li>' +
            '</ol>', true);
          setTimeout(function(){
            bot('Je vous aide à trouver un plombier disponible :');
            afficherPickerStatique(['plomberie']);
          }, 400);
        }, 300);
      }
    },
    {
      name: 'urgence_electrique',
      patterns: [
        'probleme electrique','urgence electrique','panne electrique','panne courant',
        'pas d electricite','court circuit','court-circuit','disjoncteur',
        'electricite','elec','electrique','plus de courant','coup de courant'
      ],
      handle: function() {
        bot('⚡ <strong>Problème électrique</strong> — Attention, peut être dangereux.', true);
        setTimeout(function(){
          bot(
            '<ol style="margin:0;padding-left:20px;line-height:1.8">' +
            '<li>🔌 <strong>Coupez le disjoncteur principal</strong></li>' +
            '<li>💧 Ne touchez à rien d\'humide</li>' +
            '<li>👷 Appelez un électricien qualifié</li>' +
            '</ol>', true);
          setTimeout(function(){
            bot('Je peux vous trouver un électricien certifié près de chez vous :');
            afficherPickerStatique(['electricite']);
          }, 400);
        }, 300);
      }
    },
    {
      name: 'renovation_complete',
      patterns: [
        'renovation complete','renover une nouvelle maison','renover ma maison',
        'renovation totale','renovation maison','renovation appartement',
        'rénovation complète','nouvelle maison a renover'
      ],
      handle: function() {
        bot('🏗️ <strong>Rénovation complète</strong> — beau projet !', true);
        setTimeout(function(){
          bot(
            'Étapes recommandées :' +
            '<ul style="margin:6px 0;padding-left:20px;line-height:1.7">' +
            '<li>📋 Diagnostic technique du bien</li>' +
            '<li>💰 Devis détaillés (minimum 3 artisans)</li>' +
            '<li>👷 Choix des artisans qualifiés</li>' +
            '<li>📅 Planning des travaux</li>' +
            '</ul>', true);
          setTimeout(function(){
            bot('Sélectionnez les corps de métier concernés :');
            afficherPickerStatique(['renovation','peinture','plomberie','electricite','maconnerie']);
          }, 400);
        }, 300);
      }
    },
    {
      name: 'toiture',
      patterns: ['toiture','toit','tuile','couvreur','probleme toit'],
      handle: function() {
        bot('🏠 <strong>Travaux de toiture</strong> — je vous trouve un couvreur qualifié.');
        afficherPickerStatique(['toiture']);
      }
    },
    {
      name: 'jardin',
      patterns: ['jardin','jardinage','paysagiste','jardinier','pelouse','haie','exterieur'],
      handle: function() {
        bot('🌿 <strong>Aménagement extérieur / jardin</strong>');
        afficherPickerStatique(['jardinage','amenagements-exterieurs']);
      }
    },
    {
      name: 'urgence_generique',
      patterns: ['urgence','urgent','tres urgent','intervention rapide','immediat','tout de suite','vite vite'],
      handle: function() {
        bot('🚨 <strong>Besoin urgent</strong> — sélectionnez le type de problème :');
        rapides([
          '💧 Plomberie / fuite',
          '⚡ Électricité',
          '🏠 Toiture',
          '🔥 Sinistre / incendie'
        ]);
      }
    },
    {
      name: 'greeting',
      patterns: ['bonjour','salut','bonsoir','coucou','hello','hi','hey','yo','bjr','slt'],
      handle: function() {
        bot('👋 Bonjour ! Comment puis-je vous aider aujourd\'hui ?', true);
        menuPrincipal();
      }
    },
    {
      name: 'thanks',
      patterns: ['merci','thanks','remercie','genial','super','parfait','top','nickel','cool'],
      handle: function() {
        bot('Avec plaisir ! 😊 N\'hésitez pas si vous avez d\'autres questions.');
        rapides(['📋 Demander un devis','💰 Voir les tarifs','📞 Contact','↩ Menu']);
      }
    },
    {
      name: 'menu',
      patterns: ['↩ menu','retour menu','menu principal','accueil chatbot','recommencer'],
      handle: function() {
        bot('Pas de problème, on recommence ! Comment puis-je vous aider ?');
        menuPrincipal();
      }
    },
    {
      name: 'bye',
      patterns: ['au revoir','aurevoir','a bientot','ciao','adieu','bye'],
      handle: function() {
        bot('À très bientôt sur InfoDevis ! 👋');
      }
    },
    {
      name: 'how_works',
      patterns: ['comment ca marche','comment marche','comment fonctionne','c\'est quoi infodevis','qu\'est ce qu\'infodevis','presentation','expliquer','comment utiliser','marche','fonctionnement'],
      handle: function() {
        bot('📝 <strong>InfoDevis en 3 étapes :</strong>', true);
        setTimeout(function(){
          bot(
            '<ol style="margin:0;padding-left:20px;line-height:1.8">' +
            '<li><strong>Vous décrivez vos travaux</strong> via notre formulaire ou ce chat</li>' +
            '<li><strong>On vous met en relation</strong> avec 3 artisans qualifiés près de chez vous</li>' +
            '<li><strong>Vous recevez des devis gratuits</strong> et choisissez le meilleur pro</li>' +
            '</ol>', true);
          rapides(['📋 Demander un devis','💰 Voir les tarifs','🛠️ Je suis artisan']);
        }, 400);
      }
    },
    {
      name: 'is_free',
      patterns: ['c\'est gratuit','est gratuit','sans engagement','prix de votre service','combien ca coute infodevis','je paye combien','infodevis gratuit'],
      handle: function() {
        bot('✅ <strong>InfoDevis est 100% gratuit</strong> pour les particuliers : demande de devis, mise en relation, rendez-vous… tout est offert, sans engagement.', true);
        setTimeout(function(){
          bot('Seuls les artisans peuvent souscrire à un abonnement (Silver 10€ ou Gold 14€/mois) pour développer leur visibilité.');
          rapides(['📋 Demander un devis','🛠️ Je suis artisan','💰 Abonnements artisans']);
        }, 400);
      }
    },
    {
      name: 'delay',
      patterns: ['delai','combien de temps','quand','rapidite','vite','temps de reponse','urgent','vais je etre rappele','rappel'],
      handle: function() {
        bot('⚡ <strong>Réponse rapide :</strong> les artisans vous contactent généralement sous <strong>24 à 48h</strong>. Pour les urgences (fuite, panne…), précisez-le dans votre demande.', true);
        rapides(['🚨 Urgence','📋 Demander un devis']);
      }
    },
    {
      name: 'guarantee',
      patterns: ['garantie','qualite','serieux','confiance','verifie','arnaque','fiable','assurance artisan','badge'],
      handle: function() {
        bot('🛡️ <strong>Nos artisans sont vérifiés :</strong>', true);
        setTimeout(function(){
          bot(
            '<ul style="margin:0;padding-left:20px;line-height:1.8">' +
            '<li>✅ SIRET vérifié (Badge <em>Référencé</em>)</li>' +
            '<li>✅ KBIS + RC Pro + identité (Badge <em>Vérifié</em>)</li>' +
            '<li>✅ Décennale + Qualifications RGE/Qualibat (Badge <em>Vérifié Pro</em>)</li>' +
            '</ul>', true);
          lien('En savoir plus sur les niveaux','/nos-niveaux-de-confiance');
          rapides(['📋 Trouver un artisan','💰 Voir les tarifs']);
        }, 400);
      }
    },
    {
      name: 'become_artisan',
      patterns: ['devenir artisan','je suis artisan','inscription artisan','m\'inscrire pro','rejoindre','professionnel inscription','artisan inscription','espace pro','s\'inscrire artisan'],
      handle: function() {
        bot('🛠️ <strong>Vous êtes artisan ?</strong> Rejoignez InfoDevis pour recevoir des demandes de devis qualifiées dans votre région.', true);
        setTimeout(function(){
          bot('<strong>3 plans</strong> : Gratuit (0€), Silver (10€/mois), Gold (14€/mois). Réservations en ligne et statistiques inclus !');
          lien('S\'inscrire gratuitement','/inscription?role=artisan');
          rapides(['💰 Voir les tarifs','❓ Comment ça marche']);
        }, 400);
      }
    },
    {
      name: 'pricing_plans',
      patterns: ['voir les tarifs','les tarifs','tarifs','tarif','plan','abonnement','silver','gold','offre','tarif artisan','prix abonnement','combien coute votre abonnement','abonnements artisans'],
      handle: function() {
        bot('💰 <strong>3 plans pour artisans :</strong>', true);
        setTimeout(function(){
          bot(
            '<ul style="margin:0;padding-left:20px;line-height:1.8">' +
            '<li>📍 <strong>Gratuit</strong> (0€) — 1 réalisation, badge Référencé</li>' +
            '<li>📈 <strong>Silver</strong> (10€/mois) — 5 réalisations, badge Vérifié, visibilité renforcée</li>' +
            '<li>⭐ <strong>Gold</strong> (14€/mois) — illimité, badge Vérifié Pro, TOP 5</li>' +
            '</ul>', true);
          lien('Voir tous les détails','/tarifs');
          rapides(['🛠️ Je suis artisan','📞 Contact']);
        }, 400);
      }
    },
    {
      name: 'find_artisan',
      patterns: ['trouver un artisan','liste artisan','annuaire','professionnels','voir les artisans','chercher un pro','rechercher artisan'],
      handle: function() {
        bot('🔍 Parcourez notre annuaire d\'artisans qualifiés près de chez vous.');
        lien('Voir tous les professionnels','/professionnels');
        rapides(['📋 Demander un devis','❓ Comment ça marche']);
      }
    },
    {
      name: 'contact',
      patterns: ['contact','telephone','email','support','aide','reclamation','sav','joindre','appeler'],
      handle: function() {
        bot('📞 <strong>Notre équipe est là pour vous aider.</strong>', true);
        setTimeout(function(){
          lien('Page contact','/contact');
          rapides(['❓ Comment ça marche','📋 Faire ma demande']);
        }, 300);
      }
    },
    {
      name: 'login',
      patterns: ['connexion','me connecter','login','identifier','mot de passe oublie','mes devis','mon compte','espace client'],
      handle: function() {
        bot('🔐 Connectez-vous pour accéder à votre espace personnel.');
        lien('Se connecter','/connexion');
        rapides(['📋 Demander un devis','🛠️ S\'inscrire']);
      }
    },
    {
      name: 'parler_humain',
      patterns: [
        'parler a un humain','parler humain','vrai personne','un humain','agent',
        'conseiller','etre humain','je veux parler a quelqu\'un','parler a quelqu\'un',
        'support humain','ras le bol','tu comprends rien','assistance humaine'
      ],
      handle: function() {
        bot('👤 <strong>Je vous mets en relation avec notre équipe.</strong>', true);
        setTimeout(function(){
          bot('Notre support répond du lundi au vendredi de 9h à 18h.');
          lien('Contacter le support','/contact');
          rapides(['📋 Demander un devis','❓ Comment ça marche']);
        }, 300);
      }
    }
  ];

  /* ── Prix indicatifs ───────────────────────────────────── */
  var PRIX = {
    'pompe a chaleur':'6 000 € à 14 000 €',
    'piscine':'8 000 € à 40 000 €',
    'toiture':'5 000 € à 25 000 €',
    'electricite':'80 € à 8 000 €',
    'plomberie':'50 € à 5 000 €',
    'isolation':'1 000 € à 20 000 €',
    'panneau solaire':'7 000 € à 18 000 €',
    'climatisation':'800 € à 6 000 €',
    'cuisine':'5 000 € à 25 000 €',
    'salle de bain':'4 000 € à 15 000 €',
    'peinture':'25 € à 50 €/m²',
    'parquet':'30 € à 120 €/m²',
    'carrelage':'40 € à 150 €/m²',
    'fenetre':'500 € à 1 500 €/unité',
    'velux':'600 € à 1 800 €',
    'ravalement':'40 € à 100 €/m²'
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
      bot('Comment puis-je vous aider ?', true);
      menuPrincipal();
    }, 500);
  }

  function menuPrincipal() {
    rapides([
      '📋 Demander un devis',
      '💰 Voir les tarifs',
      '🛠️ Je suis artisan',
      '❓ Comment ça marche'
    ]);
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

  /* ── Lien CTA ──────────────────────────────────────────── */
  function lien(label, path) {
    var a = document.createElement('a');
    a.href = baseUrl + path;
    a.className = 'btn btn-primary btn-sm';
    a.style.cssText = 'display:inline-flex;margin:6px 16px 8px;';
    a.textContent = label + ' →';
    msgs.appendChild(a);
    msgs.scrollTop = msgs.scrollHeight;
  }

  /* ── Normalisation + distance (typo tolerance) ─────────── */
  function norm(t) {
    return (t || '').toLowerCase()
      .replace(/[àáâã]/g,'a').replace(/[éèêë]/g,'e')
      .replace(/[îï]/g,'i').replace(/[ôõö]/g,'o')
      .replace(/[ùúûü]/g,'u').replace(/ç/g,'c')
      .replace(/œ/g,'oe').replace(/æ/g,'ae')
      .replace(/[^\w\s'-]/g,' ')
      .replace(/\s+/g,' ').trim();
  }

  // Distance de Levenshtein (max 25 chars pour perf)
  function lev(a, b) {
    a = a.slice(0,30); b = b.slice(0,30);
    if (a === b) return 0;
    var m = a.length, n = b.length;
    if (!m) return n; if (!n) return m;
    var prev = [], curr = [], i, j;
    for (j = 0; j <= n; j++) prev[j] = j;
    for (i = 1; i <= m; i++) {
      curr[0] = i;
      for (j = 1; j <= n; j++) {
        var cost = a[i-1] === b[j-1] ? 0 : 1;
        curr[j] = Math.min(curr[j-1]+1, prev[j]+1, prev[j-1]+cost);
      }
      prev = curr.slice();
    }
    return prev[n];
  }

  // Match flou : contient OU distance <= seuil tolérance
  function matchFlou(texte, motCle) {
    var t = norm(texte), m = norm(motCle);
    if (!t || !m) return false;
    if (t.indexOf(m) !== -1) return true;
    // Tolérance fautes : 1 caractère pour mots courts, 2 pour longs
    var tol = m.length <= 5 ? 1 : (m.length <= 9 ? 2 : 3);
    // Découper le texte en mots et chercher correspondance approchée
    var mots = t.split(/\s+/);
    for (var i = 0; i < mots.length; i++) {
      if (mots[i].length >= 3 && lev(mots[i], m) <= tol) return true;
    }
    return false;
  }

  /* ── Détection intents (FAQ + actions) ─────────────────── */
  function detecterIntent(texte) {
    for (var i = 0; i < INTENTS.length; i++) {
      var it = INTENTS[i];
      for (var j = 0; j < it.patterns.length; j++) {
        if (matchFlou(texte, it.patterns[j])) return it;
      }
    }
    return null;
  }

  /* ── Détection prix ────────────────────────────────────── */
  function detecterPrix(texte) {
    var n = norm(texte);
    if (!/(prix|cout|coute|combien|tarif|estimation|budget|cher)/.test(n)) return null;
    var keys = Object.keys(PRIX).sort(function(a,b){ return b.length - a.length; });
    for (var i = 0; i < keys.length; i++) {
      if (n.indexOf(norm(keys[i])) !== -1) return { kw: keys[i], prix: PRIX[keys[i]] };
    }
    return { generique: true };
  }

  /* ── Détection métiers ─────────────────────────────────── */
  function detecter(texte) {
    var found = {};
    var keys = Object.keys(KEYWORDS).sort(function(a,b){ return b.length - a.length; });
    for (var i = 0; i < keys.length; i++) {
      if (matchFlou(texte, keys[i])) {
        KEYWORDS[keys[i]].forEach(function(s){ found[s] = true; });
      }
    }
    return Object.keys(found);
  }

  /* ── Analyse principale ────────────────────────────────── */
  function analyser(texte) {
    setTimeout(function() {
      // 1) Intent FAQ / action prioritaire
      var intent = detecterIntent(texte);
      if (intent) { intent.handle(); return; }

      // 2) Question de prix
      var p = detecterPrix(texte);
      if (p) {
        if (p.generique) {
          bot('💰 Les prix varient selon le type de travaux et votre région. Obtenez des devis gratuits et précis !');
          lien('Demander un devis gratuit','/devis');
          rapides(['📋 Plomberie','📋 Électricité','📋 Toiture','❓ Comment ça marche']);
        } else {
          bot('💰 Une installation <strong>' + p.kw + '</strong> coûte généralement <strong>' + p.prix + '</strong>. Ces prix varient selon votre région et la complexité.', true);
          lien('Obtenir un devis précis gratuit','/devis');
          rapides(['📋 Demander mon devis','❓ Autre question']);
        }
        return;
      }

      // 3) Détection métiers → devis
      var slugs = detecter(texte);
      if (slugs.length) {
        var intro = slugs.length > 1
          ? '👍 J\'ai détecté <strong>' + slugs.length + ' types de travaux</strong> dans votre demande. Je vais créer un <strong>devis groupé</strong> — un seul formulaire pour tout !'
          : '👍 J\'ai bien compris votre problème !';
        bot(intro, true);
        setTimeout(function() { afficherPicker(slugs); }, 300);
        return;
      }

      // 4) Fallback intelligent
      fallback(texte);
    }, 500);
  }

  /* ── Fallback contextuel ───────────────────────────────── */
  function fallback(texte) {
    bot('🤔 Je n\'ai pas bien compris votre demande, mais je peux vous aider sur :', false);
    setTimeout(function() {
      bot(
        '<ul style="margin:6px 0;padding-left:20px;line-height:1.7">' +
        '<li>🔧 <strong>Trouver un artisan</strong> près de chez vous</li>' +
        '<li>💰 <strong>Voir les tarifs</strong> et abonnements</li>' +
        '<li>📅 <strong>Prendre rendez-vous</strong> en ligne</li>' +
        '<li>👤 <strong>Parler au support</strong> humain</li>' +
        '</ul>' +
        '<p style="margin-top:8px;font-size:12px;color:#666"><em>Ou reformulez votre question, je vais essayer de mieux comprendre !</em></p>', true);
      rapides([
        '📋 Demander un devis',
        '💰 Voir les tarifs',
        '👤 Parler à un humain',
        '↩ Menu'
      ]);
    }, 350);
  }

  /* ── Picker catégories (version "statique" depuis intent) ─ */
  function afficherPickerStatique(slugs) {
    // Filtre slugs valides + dédup
    var seen = {}, valid = [];
    slugs.forEach(function(s){ if (CATEGORIES[s] && !seen[s]) { seen[s] = 1; valid.push(s); } });
    if (!valid.length) {
      rapides(['📋 Demander un devis','💰 Voir les tarifs','📞 Contact']);
      return;
    }
    setTimeout(function(){ afficherPicker(valid); }, 250);
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
          var a = document.createElement('a');
          a.href = url; a.className = 'btn btn-primary btn-sm';
          a.style.cssText = 'display:inline-flex;margin:4px 16px 8px';
          a.textContent = params.length > 1 ? '📋 Remplir mon devis groupé →' : '📋 Remplir ma demande →';
          msgs.appendChild(a);
          setTimeout(function() {
            bot('Vous avez d\'autres besoins ?');
            rapides(['↩ Nouveau problème', '➕ Autre métier', '💰 Voir les tarifs']);
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
