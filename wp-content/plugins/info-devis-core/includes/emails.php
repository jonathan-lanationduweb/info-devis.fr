<?php
/**
 * Info Devis Core — modèles d'emails administrables.
 *
 * Les modèles sont stockés dans l'option non-autoloadée `idc_email_templates`
 * (Info Devis → Emails). Variables dynamiques : {reference}, {metier}, {ville},
 * {nom}, {email}, {lien_admin}, {lien_espace}, {site}, {entreprise}, {note}…
 * Chaque envoi est journalisé (option `idc_email_log`, 50 derniers, sans
 * contenu sensible).
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Catalogue des modèles : clé => [libellé, sujet par défaut, corps par défaut, variables].
 */
function idc_email_templates_catalog(): array
{
    return [
        'devis_admin' => [
            'label'   => 'Nouvelle demande de devis (admin)',
            'subject' => '[{site}] Nouvelle demande de devis {reference}',
            'body'    => "Nouvelle demande de devis reçue :\n\nRéférence : {reference}\nCatégorie : {metier}\nVille : {ville}\nClient : {nom} ({email})\n\nGérer la demande : {lien_admin}",
            'vars'    => '{site} {reference} {metier} {ville} {nom} {email} {lien_admin}',
        ],
        'devis_client' => [
            'label'   => 'Confirmation de demande (client)',
            'subject' => 'Votre demande de devis {reference} — {site}',
            'body'    => "Bonjour {nom},\n\nNous avons bien reçu votre demande de devis (référence {reference}) pour des travaux de {metier} à {ville}.\n\nLes artisans correspondant à votre projet vont vous recontacter rapidement.\n\nÀ très vite,\nL'équipe {site}",
            'vars'    => '{site} {reference} {metier} {ville} {nom}',
        ],
        'devis_artisan' => [
            'label'   => 'Nouveau projet correspondant (artisan)',
            'subject' => '[{site}] Nouveau projet {metier} à {ville}',
            'body'    => "Bonjour,\n\nUn nouveau projet correspond à votre activité :\n\nCatégorie : {metier}\nVille : {ville}\nRéférence : {reference}\n\nConnectez-vous à votre espace artisan pour voir les détails : {lien_espace}",
            'vars'    => '{site} {reference} {metier} {ville} {lien_espace}',
        ],
        'inscription_artisan_admin' => [
            'label'   => 'Nouvel artisan à valider (admin)',
            'subject' => '[{site}] Nouvel artisan en attente de validation : {entreprise}',
            'body'    => "Un nouvel artisan vient de s'inscrire et attend votre validation :\n\nEntreprise : {entreprise}\nContact : {nom} ({email})\nVille : {ville}\n\nValider la fiche : {lien_admin}",
            'vars'    => '{site} {entreprise} {nom} {email} {ville} {lien_admin}',
        ],
        'inscription_artisan_bienvenue' => [
            'label'   => 'Bienvenue artisan (après inscription)',
            'subject' => 'Bienvenue sur {site} — votre compte artisan',
            'body'    => "Bonjour {nom},\n\nVotre compte artisan {site} a bien été créé pour l'entreprise {entreprise}.\n\nVotre fiche est en cours de vérification par notre équipe. Vous recevrez un email dès sa validation.\n\nAccéder à votre espace : {lien_espace}",
            'vars'    => '{site} {nom} {entreprise} {lien_espace}',
        ],
        'devis_client_accepte' => [
            'label'   => 'Demande acceptée par un artisan (client)',
            'subject' => 'Bonne nouvelle ! Un artisan a accepté votre demande {reference} — {site}',
            'body'    => "Bonjour {nom},\n\nL'entreprise {entreprise} a accepté votre demande de devis (référence {reference}) et va vous recontacter rapidement.\n\nÀ très vite,\nL'équipe {site}",
            'vars'    => '{site} {reference} {entreprise} {nom}',
        ],
        'avis_recu_admin' => [
            'label'   => 'Nouvel avis à modérer (admin)',
            'subject' => '[{site}] Nouvel avis à modérer ({note}/5 pour {entreprise})',
            'body'    => "Un nouvel avis attend votre modération :\n\nArtisan : {entreprise}\nNote : {note}/5\nClient : {nom}\n\nModérer : {lien_admin}",
            'vars'    => '{site} {entreprise} {note} {nom} {lien_admin}',
        ],
        'avis_publie_artisan' => [
            'label'   => 'Avis publié (artisan)',
            'subject' => '[{site}] Un nouvel avis a été publié sur votre fiche',
            'body'    => "Bonjour,\n\nUn nouvel avis client ({note}/5) vient d'être publié sur votre fiche {entreprise}.\n\nConsultez-le depuis votre espace : {lien_espace}",
            'vars'    => '{site} {entreprise} {note} {lien_espace}',
        ],
        'rdv_nouveau_artisan' => [
            'label'   => 'Nouvelle demande de RDV (artisan)',
            'subject' => '[{site}] Nouvelle demande de rendez-vous le {date}',
            'body'    => "Bonjour,\n\n{nom} souhaite prendre rendez-vous avec {entreprise} le {date}.\n\nConfirmez ou proposez un autre créneau depuis votre espace : {lien_espace}",
            'vars'    => '{site} {date} {nom} {entreprise} {lien_espace}',
        ],
        'rdv_recap_client' => [
            'label'   => 'Récapitulatif de demande de RDV (client)',
            'subject' => 'Votre demande de rendez-vous du {date} — {site}',
            'body'    => "Bonjour {nom},\n\nVotre demande de rendez-vous avec {entreprise} le {date} a bien été transmise. Vous recevrez une confirmation dès que l'artisan l'aura validée.\n\nL'équipe {site}",
            'vars'    => '{site} {date} {nom} {entreprise}',
        ],
        'rdv_confirme_client' => [
            'label'   => 'RDV confirmé (client)',
            'subject' => '✅ Rendez-vous confirmé le {date} — {site}',
            'body'    => "Bonjour {nom},\n\n{entreprise} a confirmé votre rendez-vous du {date}.\n\nÀ bientôt,\nL'équipe {site}",
            'vars'    => '{site} {date} {nom} {entreprise}',
        ],
        'rdv_annule' => [
            'label'   => 'RDV annulé (notification)',
            'subject' => 'Rendez-vous du {date} annulé — {site}',
            'body'    => "Bonjour {nom},\n\nLe rendez-vous du {date} avec {entreprise} a été annulé. Vous pouvez reprendre rendez-vous à tout moment depuis le site.\n\nL'équipe {site}",
            'vars'    => '{site} {date} {nom} {entreprise}',
        ],
        'abonnement_active' => [
            'label'   => 'Abonnement activé (artisan)',
            'subject' => '[{site}] Votre abonnement {plan} est actif',
            'body'    => "Bonjour,\n\nVotre abonnement {plan} est maintenant actif. Merci de votre confiance !\n\nGérer votre abonnement : {lien_espace}",
            'vars'    => '{site} {plan} {lien_espace}',
        ],
        'compte_client_bienvenue' => [
            'label'   => 'Bienvenue client (création de compte)',
            'subject' => 'Bienvenue sur {site} !',
            'body'    => "Bonjour {nom},\n\nVotre compte {site} a bien été créé. Vous pouvez suivre vos demandes de devis, prendre rendez-vous et déposer des avis depuis votre espace.\n\nSe connecter : {lien_connexion}\n\nÀ très vite,\nL'équipe {site}",
            'vars'    => '{site} {nom} {email} {lien_connexion} {lien_espace}',
        ],
        'mdp_oublie' => [
            'label'   => 'Mot de passe oublié',
            'subject' => '[{site}] Réinitialisation de votre mot de passe',
            'body'    => "Bonjour {nom},\n\nUne demande de réinitialisation de mot de passe a été faite pour votre compte.\nSi vous n'êtes pas à l'origine de cette demande, ignorez cet email.\n\nPour choisir un nouveau mot de passe, cliquez sur ce lien :\n{lien_reset}\n\nL'équipe {site}",
            'vars'    => '{site} {nom} {lien_reset}',
        ],
        'artisan_valide' => [
            'label'   => 'Fiche artisan validée',
            'subject' => '✅ Votre fiche {entreprise} est en ligne — {site}',
            'body'    => "Bonjour {nom},\n\nBonne nouvelle : votre fiche {entreprise} a été validée par notre équipe et est désormais visible sur {site}. Vous allez commencer à recevoir des opportunités correspondant à vos métiers et votre zone.\n\nVotre espace : {lien_espace}\n\nL'équipe {site}",
            'vars'    => '{site} {nom} {entreprise} {lien_espace}',
        ],
        'artisan_refuse' => [
            'label'   => 'Fiche artisan refusée',
            'subject' => 'Votre inscription {entreprise} — {site}',
            'body'    => "Bonjour {nom},\n\nAprès examen, nous ne pouvons pas valider votre fiche {entreprise} en l'état. Cela peut venir d'informations incomplètes ou de documents manquants.\n\nRépondez à cet email pour compléter votre dossier — nous réexaminerons votre demande rapidement.\n\nL'équipe {site}",
            'vars'    => '{site} {nom} {entreprise}',
        ],
        'abonnement_annule' => [
            'label'   => 'Abonnement annulé (artisan)',
            'subject' => '[{site}] Votre abonnement a été annulé',
            'body'    => "Bonjour,\n\nVotre abonnement {plan} a été annulé : votre compte repasse sur l'offre gratuite (fonctionnalités limitées). Vous pouvez vous réabonner à tout moment depuis votre espace.\n\nVotre espace : {lien_espace}\n\nL'équipe {site}",
            'vars'    => '{site} {plan} {lien_espace}',
        ],
        'paiement_echec' => [
            'label'   => 'Échec de paiement (artisan)',
            'subject' => '[{site}] Échec du paiement de votre abonnement',
            'body'    => "Bonjour,\n\nLe paiement de votre abonnement {site} a échoué. Merci de mettre à jour votre moyen de paiement pour conserver vos avantages.\n\nVotre espace : {lien_espace}",
            'vars'    => '{site} {plan} {lien_espace}',
        ],
        'message_nouveau' => [
            'label'   => 'Nouveau message (client/artisan)',
            'subject' => '[{site}] Nouveau message de {nom}',
            'body'    => "Bonjour,\n\nVous avez reçu un nouveau message de {nom} concernant la demande {reference} ({entreprise}).\n\nConnectez-vous à votre espace pour y répondre.\n\nL'équipe {site}",
            'vars'    => '{site} {nom} {reference} {entreprise}',
        ],
        'devis_signe_artisan' => [
            'label'   => 'Devis signé par le client (artisan)',
            'subject' => '[{site}] Le client a signé le devis {reference}',
            'body'    => "Bonjour,\n\n{nom} vient de signer électroniquement le devis {reference}. Vous pouvez démarrer le projet.\n\nL'équipe {site}",
            'vars'    => '{site} {reference} {nom}',
        ],
    ];
}

/**
 * Modèle effectif (personnalisation enregistrée ou défaut du catalogue).
 */
function idc_get_email_template(string $key): ?array
{
    $catalog = idc_email_templates_catalog();
    if (!isset($catalog[$key])) {
        return null;
    }
    $saved = get_option('idc_email_templates', []);
    $tpl   = $catalog[$key];
    if (isset($saved[$key]) && is_array($saved[$key])) {
        $tpl['subject'] = $saved[$key]['subject'] ?? $tpl['subject'];
        $tpl['body']    = $saved[$key]['body'] ?? $tpl['body'];
        $tpl['enabled'] = !empty($saved[$key]['enabled']);
    } else {
        $tpl['enabled'] = true;
    }
    if (!isset($saved[$key])) {
        $tpl['enabled'] = true;
    }
    return $tpl;
}

/**
 * Alias de variables au format {{double_accolade}} → variables internes.
 * Les deux syntaxes sont acceptées dans les modèles.
 */
function idc_email_var_aliases(): array
{
    return [
        '{{site_name}}'         => '{site}',
        '{{user_name}}'         => '{nom}',
        '{{user_email}}'        => '{email}',
        '{{artisan_name}}'      => '{entreprise}',
        '{{client_name}}'       => '{nom}',
        '{{quote_reference}}'   => '{reference}',
        '{{appointment_date}}'  => '{date}',
        '{{appointment_time}}'  => '{date}',
        '{{payment_amount}}'    => '{montant}',
        '{{subscription_name}}' => '{plan}',
        '{{login_url}}'         => '{lien_connexion}',
        '{{reset_url}}'         => '{lien_reset}',
    ];
}

/**
 * Envoi d'un email à partir d'un modèle + journalisation.
 * $related : ['type' => 'demande|avis|rdv|artisan|user', 'id' => int] pour
 * relier la ligne de journal à l'objet métier.
 */
function idc_send_mail(string $template_key, string $to, array $vars = [], array $related = []): bool
{
    $tpl = idc_get_email_template($template_key);
    if (!$tpl || empty($tpl['enabled']) || !is_email($to)) {
        return false;
    }

    $vars = array_merge([
        '{site}'           => get_bloginfo('name'),
        '{lien_espace}'    => home_url('/espace-membre/'),
        '{lien_connexion}' => home_url('/connexion/'),
    ], $vars);

    // Syntaxe {{variable}} → convertie vers les variables internes.
    $subject = strtr(strtr($tpl['subject'], idc_email_var_aliases()), $vars);
    $body    = strtr(strtr($tpl['body'], idc_email_var_aliases()), $vars);

    // Contexte lu par le journal d'emails (InfoDevis Admin).
    $GLOBALS['idc_current_mail_template'] = $template_key;
    $GLOBALS['idc_current_mail_related']  = $related;
    $sent = wp_mail($to, $subject, $body);
    unset($GLOBALS['idc_current_mail_template'], $GLOBALS['idc_current_mail_related']);

    // Journal (sans corps de message ni donnée sensible).
    $log   = get_option('idc_email_log', []);
    $log[] = [
        'date'     => current_time('mysql'),
        'template' => $template_key,
        'to'       => $to,
        'status'   => $sent ? 'envoye' : 'echec',
    ];
    update_option('idc_email_log', array_slice($log, -50), false);

    return $sent;
}

/* ── Déclencheurs des nouveaux modèles ─────────────────────────────────── */

/** Bienvenue client à la création de compte (les artisans ont déjà le leur). */
add_action('user_register', static function (int $user_id): void {
    $user = get_userdata($user_id);
    if ($user && in_array('client', (array) $user->roles, true)) {
        idc_send_mail('compte_client_bienvenue', $user->user_email, [
            '{nom}'   => $user->display_name,
            '{email}' => $user->user_email,
        ], ['type' => 'user', 'id' => $user_id]);
    }
}, 20);

/** Mot de passe oublié : le modèle remplace le message natif de WordPress. */
add_filter('retrieve_password_message', static function (string $message, string $key, string $user_login, WP_User $user_data): string {
    $tpl = idc_get_email_template('mdp_oublie');
    if (!$tpl || empty($tpl['enabled'])) {
        return $message;
    }
    $reset = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
    $vars  = [
        '{site}'       => get_bloginfo('name'),
        '{nom}'        => $user_data->display_name,
        '{lien_reset}' => $reset,
    ];
    $GLOBALS['idc_current_mail_template'] = 'mdp_oublie';
    return strtr(strtr($tpl['body'], idc_email_var_aliases()), $vars);
}, 10, 4);
add_filter('retrieve_password_title', static function (string $title): string {
    $tpl = idc_get_email_template('mdp_oublie');
    if (!$tpl || empty($tpl['enabled'])) {
        return $title;
    }
    return strtr($tpl['subject'], ['{site}' => get_bloginfo('name')]);
});

/** Validation d'une fiche artisan (pending → publish) : email à l'artisan. */
add_action('transition_post_status', static function (string $new, string $old, WP_Post $post): void {
    if ($post->post_type !== 'artisan' || $new !== 'publish' || $old === 'publish') {
        return;
    }
    $user = get_userdata((int) get_post_meta($post->ID, '_idc_user_id', true));
    if ($user) {
        idc_send_mail('artisan_valide', $user->user_email, [
            '{nom}'        => $user->display_name,
            '{entreprise}' => get_the_title($post),
        ], ['type' => 'artisan', 'id' => $post->ID]);
    }
}, 10, 3);

/** Refus d'une fiche (statut de vérification passé à refused). */
add_action('updated_post_meta', static function (int $meta_id, int $post_id, string $meta_key, $value): void {
    if ($meta_key !== '_idc_verification_status' || $value !== 'refused' || get_post_type($post_id) !== 'artisan') {
        return;
    }
    $user = get_userdata((int) get_post_meta($post_id, '_idc_user_id', true));
    if ($user) {
        idc_send_mail('artisan_refuse', $user->user_email, [
            '{nom}'        => $user->display_name,
            '{entreprise}' => get_the_title($post_id),
        ], ['type' => 'artisan', 'id' => $post_id]);
    }
}, 10, 4);
