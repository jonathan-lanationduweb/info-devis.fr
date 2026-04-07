<?php
/**
 * Service d'envoi d'emails
 * InfoDevis.fr
 */

class MailService {

    private static function send(string $to, string $subject, string $html, string $toName = ''): bool {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . MAIL_NAME . " <" . MAIL_FROM . ">\r\n";
        $headers .= "Reply-To: " . MAIL_FROM . "\r\n";
        $headers .= "X-Mailer: InfoDevis/1.0\r\n";

        $result = mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $headers);
        if (!$result) {
            error_log('[MAIL ERROR] Impossible d\'envoyer à ' . $to);
        }
        return $result;
    }

    private static function template(string $content, string $title = ''): string {
        return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($title) . '</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f5f7fa; color: #1a1a2e; }
  .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 30px rgba(0,0,0,.08); }
  .header { background: linear-gradient(135deg, #1e3a8a 0%, #0f2460 100%); padding: 40px 32px; text-align: center; }
  .header img { height: 40px; margin-bottom: 16px; }
  .header h1 { color: #fff; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }
  .header span { color: #f97316; }
  .body { padding: 40px 32px; }
  .body p { font-size: 16px; line-height: 1.7; color: #374151; margin-bottom: 16px; }
  .btn { display: inline-block; padding: 14px 32px; background: #f97316; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 16px; margin: 24px 0; }
  .badge { display: inline-block; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; border-radius: 20px; padding: 4px 14px; font-size: 13px; font-weight: 600; margin-bottom: 24px; }
  .info-box { background: #f0f7ff; border-left: 4px solid #1e3a8a; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 24px 0; }
  .footer { background: #f9fafb; padding: 24px 32px; text-align: center; border-top: 1px solid #e5e7eb; }
  .footer p { font-size: 13px; color: #9ca3af; line-height: 1.5; }
  .footer a { color: #1e3a8a; text-decoration: none; }
</style></head><body>
<div class="wrapper">
  <div class="header">
    <h1>Info<span>Devis</span></h1>
  </div>
  <div class="body">' . $content . '</div>
  <div class="footer">
    <p>© ' . date('Y') . ' InfoDevis.fr — 45 Rue des Boulets, 75011 Paris<br>
    <a href="' . APP_URL . '">www.info-devis.fr</a> · <a href="' . APP_URL . '/contact">Contact</a></p>
  </div>
</div></body></html>';
    }

    public static function sendVerification(string $email, string $name, string $token): bool {
        $url     = APP_URL . '/verifier-email?token=' . $token;
        $content = '
<span class="badge">✉ Vérification requise</span>
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Merci de vous être inscrit sur <strong>InfoDevis</strong>. Cliquez sur le bouton ci-dessous pour activer votre compte :</p>
<div style="text-align:center"><a href="' . $url . '" class="btn">✓ Vérifier mon email</a></div>
<div class="info-box"><p>Si vous n\'avez pas créé de compte, ignorez cet email.</p></div>';
        return self::send($email, 'Activez votre compte InfoDevis', self::template($content, 'Vérification email'), $name);
    }

    public static function sendDevisConfirmation(string $email, string $name, string $ref, string $ville): bool {
        $content = '
<span class="badge">🎉 Demande enregistrée</span>
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Votre demande de devis a bien été enregistrée. Des artisans qualifiés à <strong>' . htmlspecialchars($ville) . '</strong> vont vous contacter sous <strong>24 heures</strong>.</p>
<div class="info-box">
  <p><strong>Référence :</strong> ' . htmlspecialchars($ref) . '</p>
  <p>Conservez cette référence pour le suivi de votre demande.</p>
</div>
<div style="text-align:center"><a href="' . APP_URL . '/dashboard/client/devis" class="btn">Suivre ma demande</a></div>
<p>Service 100% gratuit — Sans engagement</p>';
        return self::send($email, '🎉 Votre demande est envoyée ! Réf. ' . $ref, self::template($content, 'Confirmation devis'), $name);
    }

    public static function sendPasswordReset(string $email, string $name, string $token): bool {
        $url     = APP_URL . '/reset-password?token=' . $token;
        $content = '
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Vous avez demandé la réinitialisation de votre mot de passe. Cliquez sur le bouton ci-dessous :</p>
<div style="text-align:center"><a href="' . $url . '" class="btn">Réinitialiser mon mot de passe</a></div>
<div class="info-box"><p>Ce lien expire dans <strong>2 heures</strong>. Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.</p></div>';
        return self::send($email, 'Réinitialisation mot de passe InfoDevis', self::template($content, 'Réinitialisation'), $name);
    }

    public static function sendLeadNotification(string $email, string $name, array $lead): bool {
        $content = '
<span class="badge">🔔 Nouveau lead disponible</span>
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Un particulier dans votre zone recherche vos services !</p>
<div class="info-box">
  <p><strong>Ville :</strong> ' . htmlspecialchars($lead['ville'] ?? '') . '</p>
  <p><strong>Urgence :</strong> ' . htmlspecialchars($lead['urgency'] ?? '') . '</p>
  <p><strong>Référence :</strong> ' . htmlspecialchars($lead['ref'] ?? '') . '</p>
</div>
<div style="text-align:center"><a href="' . APP_URL . '/dashboard/artisan/leads" class="btn">Voir le lead</a></div>
<p style="font-size:13px;color:#9ca3af">Répondez rapidement pour augmenter vos chances d\'obtenir ce client.</p>';
        return self::send($email, '🔔 Nouveau lead - ' . ($lead['ref'] ?? ''), self::template($content, 'Nouveau lead'), $name);
    }

    public static function sendAvisRequest(string $email, string $name, int $devisId): bool {
        $content = '
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Vos travaux sont maintenant terminés. Votre avis est précieux pour aider d\'autres particuliers à choisir leur artisan !</p>
<div style="text-align:center"><a href="' . APP_URL . '/dashboard/client/avis?devis=' . $devisId . '" class="btn">⭐ Laisser mon avis</a></div>
<p style="font-size:13px;color:#9ca3af">Cela prend moins de 2 minutes. Merci !</p>';
        return self::send($email, '⭐ Donnez votre avis sur vos travaux', self::template($content, 'Avis'), $name);
    }
}
