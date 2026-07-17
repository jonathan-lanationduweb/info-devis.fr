<?php
/**
 * Désinstallation prudente d'Info Devis Core.
 *
 * Les contenus métier (artisans, demandes de devis, avis, réalisations,
 * guides, rendez-vous) et les comptes utilisateurs sont VOLONTAIREMENT
 * conservés : leur suppression serait irréversible. Seules les options
 * techniques de l'extension sont retirées.
 *
 * Pour une purge complète, exporter puis supprimer manuellement les
 * contenus depuis l'administration avant de désinstaller.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('idc_email_templates');
delete_option('idc_email_log');
delete_option('idc_stripe_events');
