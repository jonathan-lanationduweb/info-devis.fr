<?php

/**
 * MailService — InfoDevis.fr
 *
 * Utilise le SDK Mailtrap officiel (installé via Composer).
 * Requiert : composer require railsware/mailtrap-php
 *
 * ── CONFIGURATION dans config/app.php ────────────────────────────────────
 *   define('MAILTRAP_API_KEY', 'votre_token_api');   ← mailtrap.io > API Tokens
 *   define('MAIL_FROM',        'noreply@info-devis.fr');
 *   define('MAIL_NAME',        'InfoDevis');
 *   define('ADMIN_EMAIL',      'admin@info-devis.fr');
 *
 * ── EN LOCAL : les emails sont sauvegardés dans storage/mails/ ───────────
 *   Ouvrez les fichiers .html dans votre navigateur pour les prévisualiser.
 */

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

class MailService
{
  // ── Détecte si on est en local (WAMP) ────────────────────────
  private static function isLocal(): bool
  {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return in_array($host, ['localhost', '127.0.0.1', 'localhost:80', 'localhost:8080'])
      || str_ends_with($host, '.local')
      || (defined('APP_ENV') && APP_ENV === 'local');
  }

  // ── Envoi principal ───────────────────────────────────────────
  private static function send(string $to, string $subject, string $html, string $toName = ''): bool
  {
    if (self::isLocal()) {
      return self::logMail($to, $subject, $html);
    }
    return self::sendViaMailtrap($to, $subject, $html, $toName);
  }

  // ── Log local dans un fichier HTML ───────────────────────────
  private static function logMail(string $to, string $subject, string $html): bool
  {
    $logDir = BASE_PATH . '/storage/mails';
    if (!is_dir($logDir)) {
      mkdir($logDir, 0755, true);
    }
    $safeFile   = preg_replace('/[^a-z0-9]/i', '_', $to);
    $filename   = $logDir . '/' . date('Y-m-d_H-i-s') . '_' . $safeFile . '.html';
    $logContent = "<!-- TO:{$to} | SUBJECT:{$subject} | DATE:" . date('Y-m-d H:i:s') . " -->\n" . $html;
    file_put_contents($filename, $logContent);
    error_log("[MAIL LOCAL] To:{$to} | Subject:{$subject} | File:{$filename}");
    return true;
  }

  // ── Envoi via SDK Mailtrap (production) ───────────────────────
  private static function sendViaMailtrap(string $to, string $subject, string $html, string $toName = ''): bool
  {
    // Vérifie les constantes avant utilisation
    $apiKey   = defined('MAILTRAP_API_KEY') ? MAILTRAP_API_KEY : '';
    $from     = defined('MAIL_FROM')        ? MAIL_FROM        : 'noreply@info-devis.fr';
    $fromName = defined('MAIL_NAME')        ? MAIL_NAME        : 'InfoDevis';

    if (empty($apiKey)) {
      error_log('[MAIL ERROR] MAILTRAP_API_KEY non défini dans config/app.php');
      return false;
    }

    // Vérifier que le SDK est bien chargé
    if (!class_exists('Mailtrap\MailtrapClient')) {
      error_log('[MAIL ERROR] SDK Mailtrap introuvable. Lancez : composer require railsware/mailtrap-php');
      return false;
    }

    try {
      // Initialisation du client Mailtrap (exactement comme votre exemple)
      $mailtrap = MailtrapClient::initSendingEmails(apiKey: $apiKey);

      // Construction de l'email
      $email = (new MailtrapEmail())
        ->from(new Address($from, $fromName))
        ->to(new Address($to, $toName ?: $to))
        ->subject($subject)
        ->html($html)
        ->text(strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $html)))
        ->category('InfoDevis');

      // Envoi
      $response = $mailtrap->send($email);
      $result   = ResponseHelper::toArray($response);

      // Vérifier succès
      if (!empty($result['errors'])) {
        error_log('[MAIL ERROR] ' . json_encode($result['errors']));
        return false;
      }

      error_log('[MAIL OK] To:' . $to . ' | Subject:' . $subject);
      return true;
    } catch (\Exception $e) {
      error_log('[MAIL EXCEPTION] ' . $e->getMessage());
      return false;
    }
  }

  // ── Template HTML commun ──────────────────────────────────────
  private static function template(string $content, string $title = ''): string
  {
    $appUrl = defined('APP_URL') ? APP_URL : '';
    return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
<title>' . htmlspecialchars($title) . '</title>
<style>
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; background:#f5f7fa; color:#1a1a2e; }
  .wrapper { max-width:600px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 30px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#0e6c48 0%,#065030 100%); padding:36px 32px; text-align:center; }
  .header h1 { color:#e1ffeb; font-size:26px; font-weight:700; font-style:italic; }
  .header p { color:#a0f4c6; font-size:13px; margin-top:4px; }
  .body { padding:36px 32px; }
  .body p { font-size:15px; line-height:1.75; color:#374151; margin-bottom:14px; }
  .btn { display:inline-block; padding:13px 28px; background:#0e6c48; color:#e1ffeb; text-decoration:none; border-radius:8px; font-weight:700; font-size:15px; margin:20px 0; }
  .badge { display:inline-block; background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; border-radius:20px; padding:4px 14px; font-size:13px; font-weight:600; margin-bottom:20px; }
  .info-box { background:#f0fdf4; border-left:4px solid #0e6c48; padding:14px 18px; border-radius:0 8px 8px 0; margin:20px 0; }
  .info-box p { margin-bottom:6px; font-size:14px; }
  .info-box p:last-child { margin-bottom:0; }
  .step { display:flex; gap:14px; margin-bottom:16px; }
  .step-num { flex-shrink:0; width:28px; height:28px; background:#0e6c48; color:#e1ffeb; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; }
  .step-body p { margin-bottom:0; }
  .footer { background:#f9fafb; padding:22px 32px; text-align:center; border-top:1px solid #e5e7eb; }
  .footer p { font-size:12px; color:#9ca3af; line-height:1.5; }
  .footer a { color:#0e6c48; text-decoration:none; }
</style></head><body>
<div class="wrapper">
  <div class="header">
    <h1>Info-Devis</h1>
    <p>La plateforme des artisans qualifiés</p>
  </div>
  <div class="body">' . $content . '</div>
  <div class="footer">
    <p>&copy; ' . date('Y') . ' InfoDevis.fr &middot;
    <a href="' . $appUrl . '">www.info-devis.fr</a> &middot;
    <a href="' . $appUrl . '/contact">Contact</a></p>
  </div>
</div></body></html>';
  }

    // ══════════════════════════════════════════════════════════════
    // EMAILS CLIENTS
    // ══════════════════════════════════════════════════════════════

  /**
   * Email 1 — Confirmation réception devis
   * Envoyé dès que le client soumet sa demande
   */
  public static function sendDevisReception(string $email, string $name, string $ref, string $categorie, string $ville): bool
  {
    $appUrl  = defined('APP_URL') ? APP_URL : '';
    $content = '
<span class="badge">✅ Demande bien reçue</span>
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Nous avons bien reçu votre demande de devis et elle va être traitée dans les meilleurs délais.</p>

<div class="info-box">
  <p><strong>Référence :</strong> ' . htmlspecialchars($ref) . '</p>
  <p><strong>Catégorie :</strong> ' . htmlspecialchars($categorie) . '</p>
  <p><strong>Ville :</strong> ' . htmlspecialchars($ville) . '</p>
</div>

<p><strong>Que se passe-t-il maintenant ?</strong></p>
<div class="step">
  <div class="step-num">1</div>
  <div class="step-body"><p>Notre équipe analyse votre demande et la transmet aux artisans qualifiés près de chez vous.</p></div>
</div>
<div class="step">
  <div class="step-num">2</div>
  <div class="step-body"><p>Les artisans sélectionnés examinent votre projet et confirment leur intérêt.</p></div>
</div>
<div class="step">
  <div class="step-num">3</div>
  <div class="step-body"><p>Vous recevrez un email dès qu\'un artisan est disponible pour votre projet.</p></div>
</div>

<div style="text-align:center">
  <a href="' . $appUrl . '/dashboard/client/devis" class="btn">Suivre ma demande</a>
</div>
<p style="font-size:13px;color:#9ca3af;text-align:center">Vous pouvez suivre l\'avancement de votre devis depuis votre espace client.</p>';

    return self::send(
      $email,
      '✅ Votre demande de devis a bien été reçue — InfoDevis',
      self::template($content, 'Confirmation de devis'),
      $name
    );
  }

  /**
   * Email 2 — Artisan sélectionné pour le devis
   * Envoyé quand un artisan accepte le lead
   * L'artisan devra lui-même contacter le client (email / téléphone / WhatsApp)
   */
  public static function sendArtisanSelectionne(
    string $clientEmail,
    string $clientName,
    string $ref,
    string $artisanNom,
    string $artisanEntreprise,
    string $artisanVille
  ): bool {
    $appUrl  = defined('APP_URL') ? APP_URL : '';
    $content = '
<span class="badge">🔨 Artisan sélectionné</span>
<p>Bonjour <strong>' . htmlspecialchars($clientName) . '</strong>,</p>
<p>Bonne nouvelle ! Un artisan a été sélectionné pour votre devis <strong>' . htmlspecialchars($ref) . '</strong> et va prochainement vous contacter.</p>

<div class="info-box">
  <p><strong>Artisan :</strong> ' . htmlspecialchars($artisanNom) . '</p>
  <p><strong>Entreprise :</strong> ' . htmlspecialchars($artisanEntreprise) . '</p>
  <p><strong>Zone d\'intervention :</strong> ' . htmlspecialchars($artisanVille) . '</p>
</div>

<p><strong>Comment se déroule la suite ?</strong></p>
<div class="step">
  <div class="step-num">1</div>
  <div class="step-body"><p><strong>' . htmlspecialchars($artisanNom) . '</strong> va vous contacter directement pour convenir d\'un rendez-vous. Il vous communiquera lui-même ses coordonnées (téléphone, email ou autre).</p></div>
</div>
<div class="step">
  <div class="step-num">2</div>
  <div class="step-body"><p>Discutez des détails de votre projet et obtenez un devis précis.</p></div>
</div>
<div class="step">
  <div class="step-num">3</div>
  <div class="step-body"><p>Une fois le devis signé, les travaux peuvent commencer selon votre planning.</p></div>
</div>

<div class="info-box" style="background:#fff8ed;border-left-color:#f59e0b">
  <p style="color:#92400e"><strong>⚠️ Conseil sécurité</strong></p>
  <p style="color:#92400e;font-size:13px">Ne versez jamais d\'acompte avant d\'avoir rencontré l\'artisan et reçu un devis signé. InfoDevis ne vous demandera jamais de paiement par email.</p>
</div>

<div style="text-align:center">
  <a href="' . $appUrl . '/dashboard/client/devis" class="btn">Voir ma demande</a>
</div>
<p style="font-size:13px;color:#9ca3af;text-align:center">Vous pourrez laisser un avis après la réalisation des travaux.</p>';

    return self::send(
      $clientEmail,
      '🔨 Un artisan a été sélectionné pour votre devis — InfoDevis',
      self::template($content, 'Artisan sélectionné'),
      $clientName
    );
  }

  // ══════════════════════════════════════════════════════════════
  // EMAILS ARTISANS
  // ══════════════════════════════════════════════════════════════

  public static function sendLeadNotification(string $email, string $name, array $lead): bool
  {
    $appUrl  = defined('APP_URL') ? APP_URL : '';
    $content = '
<span class="badge">🔔 Nouveau lead disponible</span>
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Un particulier dans votre zone recherche vos services !</p>
<div class="info-box">
  <p><strong>Ville :</strong> ' . htmlspecialchars($lead['ville'] ?? '') . '</p>
  <p><strong>Urgence :</strong> ' . htmlspecialchars($lead['urgency'] ?? 'Standard') . '</p>
  <p><strong>Référence :</strong> ' . htmlspecialchars($lead['ref'] ?? '') . '</p>
</div>
<p><strong>Répondez rapidement</strong> — les artisans qui acceptent dans les 2h ont 3× plus de chances d\'être retenus.</p>
<div style="text-align:center"><a href="' . $appUrl . '/dashboard/artisan/leads" class="btn">Voir le lead</a></div>';
    return self::send($email, '🔔 Nouveau lead — ' . ($lead['ref'] ?? ''), self::template($content, 'Nouveau lead'), $name);
  }

  public static function sendArtisanValidated(string $email, string $firstName, string $companyName): bool
  {
    $url     = defined('APP_URL') ? APP_URL . '/dashboard/artisan' : '';
    $content = '
<span class="badge">✅ Compte validé</span>
<p>Bonjour <strong>' . htmlspecialchars($firstName) . '</strong>,</p>
<p>Excellente nouvelle ! Votre compte artisan <strong>' . htmlspecialchars($companyName) . '</strong> a été validé par notre équipe.</p>
<p>Vous pouvez dès maintenant recevoir des leads qualifiés et développer votre activité sur InfoDevis.</p>
<div style="text-align:center"><a href="' . $url . '" class="btn">Accéder à mon espace</a></div>';
    return self::send($email, '✅ Votre compte artisan est validé — InfoDevis', self::template($content, 'Compte validé'), $firstName);
  }

  public static function sendArtisanRefused(string $email, string $firstName, string $note): bool
  {
    $url     = defined('APP_URL') ? APP_URL . '/dashboard/artisan/documents' : '';
    $motif   = $note ? '<div class="info-box"><p><strong>Motif :</strong> ' . htmlspecialchars($note) . '</p></div>' : '';
    $content = '
<p>Bonjour <strong>' . htmlspecialchars($firstName) . '</strong>,</p>
<p>Notre équipe a examiné votre dossier. Malheureusement, nous ne pouvons pas valider votre compte pour le moment.</p>
' . $motif . '
<p>Vous pouvez corriger vos documents et les soumettre à nouveau depuis votre espace.</p>
<div style="text-align:center"><a href="' . $url . '" class="btn">Mettre à jour mes documents</a></div>';
    return self::send($email, 'Votre dossier artisan — InfoDevis', self::template($content, 'Dossier artisan'), $firstName);
  }

  // ══════════════════════════════════════════════════════════════
  // AUTRES EMAILS
  // ══════════════════════════════════════════════════════════════

  public static function sendVerification(string $email, string $name, string $token): bool
  {
    $url     = (defined('APP_URL') ? APP_URL : '') . '/verifier-email?token=' . $token;
    $content = '
<span class="badge">Vérification requise</span>
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Merci de vous être inscrit sur <strong>InfoDevis</strong>. Cliquez ci-dessous pour activer votre compte :</p>
<div style="text-align:center"><a href="' . $url . '" class="btn">Vérifier mon email</a></div>
<p style="font-size:13px;color:#9ca3af">Ou copiez ce lien : ' . $url . '</p>';
    return self::send($email, 'Activez votre compte InfoDevis', self::template($content, 'Vérification email'), $name);
  }

  public static function sendPasswordReset(string $email, string $name, string $token): bool
  {
    $url     = (defined('APP_URL') ? APP_URL : '') . '/reset-password?token=' . $token;
    $content = '
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
<div style="text-align:center"><a href="' . $url . '" class="btn">Réinitialiser mon mot de passe</a></div>
<div class="info-box"><p>Ce lien expire dans <strong>2 heures</strong>.</p></div>';
    return self::send($email, 'Réinitialisation mot de passe — InfoDevis', self::template($content, 'Réinitialisation'), $name);
  }

  public static function sendAdminNewArtisan(string $companyName, int $artisanId): bool
  {
    $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@info-devis.fr';
    $url        = (defined('APP_URL') ? APP_URL : '') . '/admin/artisan/' . $artisanId;
    $content    = '
<span class="badge">Action requise</span>
<p>Un nouvel artisan vient de s\'inscrire et attend votre validation :</p>
<div class="info-box">
  <p style="font-size:18px;font-weight:700;color:#0e6c48">' . htmlspecialchars($companyName) . '</p>
</div>
<div style="text-align:center"><a href="' . $url . '" class="btn">Voir le dossier</a></div>';
    return self::send($adminEmail, 'Nouvel artisan en attente : ' . $companyName, self::template($content, 'Admin — Nouvel artisan'), 'Admin');
  }

  public static function sendAvisRequest(string $email, string $name, int $devisId): bool
  {
    $appUrl  = defined('APP_URL') ? APP_URL : '';
    $content = '
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Vos travaux sont terminés. Votre avis aide les autres clients à choisir le bon artisan !</p>
<div style="text-align:center"><a href="' . $appUrl . '/dashboard/client/avis?devis=' . $devisId . '" class="btn">Laisser mon avis</a></div>';
    return self::send($email, 'Donnez votre avis sur vos travaux — InfoDevis', self::template($content, 'Avis travaux'), $name);
  }

  // Alias pour compatibilité avec l'ancien code
  public static function sendDevisConfirmation(string $email, string $name, string $ref, string $ville): bool
  {
    return self::sendDevisReception($email, $name, $ref, 'Non précisée', $ville);
  }
}
