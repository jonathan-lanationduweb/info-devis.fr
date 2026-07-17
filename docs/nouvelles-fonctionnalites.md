# Nouvelles fonctionnalités portées depuis l'original (16/07/2026)

Suivi du chantier « compléter ce qui manquait par rapport au site original ».

## ✅ Cœur marketplace

### Messagerie client ↔ artisan
- Table `{prefix}idc_messages` ; un fil = (demande_devis, fiche artisan), ouvert dès qu'un
  artisan a répondu à la demande.
- `includes/messages.php` : accès (client propriétaire / artisan lié), fils par rôle, envoi
  AJAX `idc_message_send`, marquage lu `idc_messages_read`, compteur non-lus.
- Templates `tpl-client-messages.php` / `tpl-artisan-messages.php` + partial `messagerie.php`
  (liste des fils + conversation bulles + envoi sans rechargement). Entrées « Messages »
  dans les deux sidebars. Email `message_nouveau` à l'autre partie.
- Pages : `dashboard/client/messages`, `dashboard/artisan/messages`.
- Sécurité vérifiée : IDOR (fil d'autrui) bloqué, nonce vérifié.

### Signature électronique du devis
- Table `{prefix}idc_signatures` ; `includes/signature.php`.
- Reproduit `views/client/signature.php` : canvas manuscrit → PNG, preuve SHA-256 + IP +
  user-agent + horodatage. Statut de la demande → `in_progress`. Email `devis_signe_artisan`.
- Template `tpl-client-signature.php`, page `dashboard/client/signature` (?d={demande}).
  Bouton « Signer le devis » sur les projets acceptés, badge « Devis signé » ensuite.
- Sécurité : propriété + statut « accepted » requis, anti double-signature, nonce.

### Paiement en ligne d'un devis — REPORTÉ (décision utilisateur)
Constat : jamais implémenté dans l'original (pas de page de paiement, aucun montant sur les
devis). Décision du 16/07 : laissé de côté (paiement hors plateforme). À reprendre plus tard
(nécessite une étape de chiffrage du devis par l'artisan + Stripe).

## ✅ Espace artisan (en cours)

### Statistiques
- Template `tpl-artisan-stats.php`, page `dashboard/artisan/stats`, entrée sidebar « Statistiques ».
- Reproduit `views/artisan/stats.php` : KPIs (leads, taux d'acceptation, note, avis) +
  évolution mensuelle, avec déblocage par plan (Gratuit verrouillé → Silver KPIs → Gold + mensuel).
  Données calculées depuis les leads réels (`idc_get_demandes_for_artisan`) et metas de notation.

## ✅ Chatbot (assistant guidé)
- Porté 1:1 de l'original (`chatbot/chatbot.js` v3, **100 % côté navigateur, sans IA, sans coût**).
- Assets : `assets/js/chatbot.js`, `assets/css/chatbot.css`, widget `template-parts/chatbot.php`.
- Intégration : enqueue global + `<meta name="base-url">` (wp_head) + widget en `wp_footer` sur tout le site.
- Détecte le besoin par mots-clés, donne des conseils, pré-coche la/les catégorie(s) et redirige
  vers `/devis?categories=slug1,slug2`. Le formulaire de devis pré-coche désormais depuis
  `?categories=` (multiple) en plus de `?metier=`.
- Testé : ouverture, détection « fuite » → plomberie, lien devis → formulaire pré-coché ✓.

## ✅ Cloche de notifications in-app
- Table `{prefix}idc_notifications` ; `includes/notifications.php` (helper `idc_notify`,
  API AJAX `idc_notifications_list` / `idc_notifications_read`, compteur non-lus, « il y a … »).
- Widget `template-parts/notifications-bell.php` + CSS `assets/css/notifications.css` (porté de
  `notifications_bell.php`) : cloche + badge rouge + menu déroulant, « Tout marquer lu »,
  rafraîchissement auto 60 s. Affiché dans l'en-tête pour les rôles client/artisan.
- Déclencheurs branchés (via `do_action`) sur les événements existants : nouveau message,
  nouvelle demande de RDV (artisan), RDV confirmé (client), nouvel avis (artisan), devis signé
  (artisan), nouvelle opportunité/lead (artisan).
- Testé : API renvoie les non-lus, badge « 3 » + menu déroulant OK, réservé client/artisan.

## ✅ Page détail d'une réalisation
- Template `single-realisation.php` (porté 1:1 de `views/pages/projet_detail.php`) : fil
  d'ariane (Professionnels › fiche › titre), en-tête (badge métier + lieu + année + titre
  serif + résumé italique), grande image de couverture, colonne « À propos de ce projet »,
  sidebar Caractéristiques (surface/budget/durée/vues), carte « L'artisan » (avatar + nom +
  ville + « Voir le profil → »), bouton favoris, section « Autres réalisations de … ».
- Compteur de vues incrémenté (hors admin) en meta `_idc_vues`.
- Testé : rendu conforme à la maquette, responsive OK à 320/390/768.

## ✅ Page détail d'un guide métier
- Template `single-guide.php` (porté 1:1 de `views/pages/guide_detail.php`) : fil d'ariane,
  pill noire « Métier » + compteur de vues, titre serif (partie après « & » ou dernier mot en
  italique vert), intro, image hero (cover locale du métier, sinon photo de la config
  catégorie), blocs éditoriaux stylés reconstruits depuis le contenu HTML (chaque `<h2>` +
  sa liste → carte `.idv-info-block` avec icône verte : cible/médaille/question), CTA vert
  « Prêt à passer à l'action ? » (Voir les artisans certifiés + Demander un devis),
  section « Découvrez d'autres guides » (3 cartes).
- Compteur de vues `_idc_vues`. Le contenu migré (h2 + ul) est découpé en sections via DOMDocument.
- Testé : rendu conforme à la maquette (Plomberie & sanitaire), responsive OK à 320/390/768.

## ⏳ Reste à faire (chantier en cours)

- **Espace artisan** : rédaction d'articles de blog (modération), personnalisation de
  l'apparence de la fiche, détail d'un RDV (notes/montant/proposer créneau/document).
- **Fidélité & SEO** : template `single-realisation.php`, pages prestation par sous-métier,
  cloche de notifications in-app.
- **Confort & sécurité** : mot de passe oublié/réinitialisation au design du site,
  confirmation d'email à l'inscription, persistance des favoris.
