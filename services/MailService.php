<?php

/**
 * MailService — InfoDevis.fr
 *
 * - Chargé automatiquement par spl_autoload_register (index.php)
 * - NE PAS mettre de require_once ici
 * - PDF généré en PHP pur (zéro dépendance, zéro extension requise)
 * - Anti-spam : AltBody, Reply-To, DOCTYPE complet, sujet propre
 *
 * Pour forcer l'envoi SMTP en local :
 *   define('MAIL_FORCE_SMTP', true); dans config/app.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

class MailService
{
  // ── Doit-on utiliser le log fichier ou SMTP ? ─────────────────
  private static function useFileLog(): bool
  {
    // MAIL_FORCE_SMTP = true → toujours SMTP, jamais fichier
    if (defined('MAIL_FORCE_SMTP') && MAIL_FORCE_SMTP === true) {
      return false;
    }
    // APP_ENV = 'local' uniquement → fichier (pas 'development')
    if (defined('APP_ENV') && APP_ENV === 'local') {
      return true;
    }
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return in_array($host, ['localhost', '127.0.0.1', 'localhost:80', 'localhost:8080'])
      || str_ends_with($host, '.local');
  }

  // ── Envoi principal ───────────────────────────────────────────
  private static function send(
    string  $to,
    string  $subject,
    string  $html,
    string  $toName      = '',
    ?string $pdfPath     = null,
    string  $pdfFilename = 'document.pdf'
  ): bool {
    // Si des credentials SMTP sont configurés → toujours envoyer via SMTP
    // (peu importe APP_ENV ou MAIL_FORCE_SMTP)
    $hasSmtp = defined('MAIL_HOST') && !empty(MAIL_HOST)
      && defined('MAIL_USER') && !empty(MAIL_USER)
      && defined('MAIL_PASS') && !empty(MAIL_PASS);

    if ($hasSmtp) {
      return self::sendViaSMTP($to, $subject, $html, $toName, $pdfPath, $pdfFilename);
    }

    // Pas de credentials → log fichier (fallback)
    return self::logMail($to, $subject, $html);
  }

  // ── Log local ─────────────────────────────────────────────────
  private static function logMail(string $to, string $subject, string $html): bool
  {
    $logDir = BASE_PATH . '/storage/mails';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    $safe = preg_replace('/[^a-z0-9]/i', '_', $to);
    $file = $logDir . '/' . date('Y-m-d_H-i-s') . '_' . $safe . '.html';
    file_put_contents($file, "<!-- TO:{$to} | SUBJECT:{$subject} | DATE:" . date('Y-m-d H:i:s') . " -->\n" . $html);
    error_log("[MAIL LOCAL] To:{$to} | Subject:{$subject}");
    return true;
  }

  // ── Envoi SMTP ────────────────────────────────────────────────
  private static function sendViaSMTP(
    string  $to,
    string  $subject,
    string  $html,
    string  $toName      = '',
    ?string $pdfPath     = null,
    string  $pdfFilename = 'document.pdf'
  ): bool {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
      error_log('[MAIL ERROR] PHPMailer introuvable. Lancez : composer install');
      return false;
    }

    $from     = defined('MAIL_FROM')   ? MAIL_FROM   : 'noreply@info-devis.fr';
    $fromName = defined('MAIL_NAME')   ? MAIL_NAME   : 'InfoDevis';
    $host     = defined('MAIL_HOST')   ? MAIL_HOST   : 'sandbox.smtp.mailtrap.io';
    $port     = defined('MAIL_PORT')   ? (int)MAIL_PORT : 2525;
    $user     = defined('MAIL_USER')   ? MAIL_USER   : '';
    $pass     = defined('MAIL_PASS')   ? MAIL_PASS   : '';
    $secure   = defined('MAIL_SECURE') ? MAIL_SECURE : 'tls';

    if (empty($user) || empty($pass)) {
      error_log('[MAIL ERROR] MAIL_USER ou MAIL_PASS vide dans config/app.php');
      return false;
    }

    try {
      $mail = new PHPMailer(true);

      $mail->isSMTP();
      $mail->Host       = $host;
      $mail->SMTPAuth   = true;
      $mail->Username   = $user;
      $mail->Password   = $pass;
      $mail->SMTPSecure = ($secure === 'ssl')
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port     = $port;
      $mail->CharSet  = 'UTF-8';
      $mail->Encoding = 'base64';

      // Anti-spam : From + Reply-To cohérents
      $mail->setFrom($from, $fromName);
      $mail->addReplyTo($from, $fromName);
      $mail->addAddress($to, $toName ?: $to);

      // Contenu HTML + texte brut obligatoire (anti-spam)
      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = $html;
      $mail->AltBody = self::htmlToText($html);

      // Pièce jointe PDF
      if ($pdfPath !== null) {
        if (file_exists($pdfPath)) {
          $mail->addAttachment($pdfPath, $pdfFilename);
          error_log('[PDF ATTACH] OK: ' . $pdfPath . ' -> ' . $pdfFilename);
        } else {
          error_log('[PDF ATTACH] FICHIER INTROUVABLE: ' . $pdfPath);
        }
      }

      $mail->send();
      error_log('[MAIL OK] To:' . $to . ' | ' . $subject
        . ($pdfPath ? ' | PDF:' . basename($pdfPath) : ' | no PDF'));

      // Nettoyer le PDF après envoi réussi
      // Note : PHPMailer a déjà lu le fichier avant send()
      // donc on peut supprimer en sécurité ici
      if ($pdfPath && file_exists($pdfPath)) {
        @unlink($pdfPath);
        error_log('[PDF DELETED] ' . $pdfPath);
      }

      return true;
    } catch (MailerException $e) {
      error_log('[MAIL ERROR] ' . $e->getMessage());
      return false;
    } catch (\Exception $e) {
      error_log('[MAIL EXCEPTION] ' . $e->getMessage());
      return false;
    }
  }

  // ── HTML → texte brut propre (AltBody anti-spam) ─────────────
  private static function htmlToText(string $html): string
  {
    $t = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
    $t = str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</h1>', '</h2>', '</h3>', '</li>'], "\n", $t);
    $t = str_replace('<li>', '• ', $t);
    $t = strip_tags($t);
    $t = html_entity_decode($t, ENT_QUOTES, 'UTF-8');
    $t = preg_replace('/\n{3,}/', "\n\n", $t);
    return trim($t);
  }

  // ══════════════════════════════════════════════════════════════
  // GÉNÉRATION PDF EN PHP PUR — zéro dépendance, zéro extension
  // ══════════════════════════════════════════════════════════════
  public static function generateDevisPdf(
    string $ref,
    string $clientName,
    string $clientEmail,
    string $categorie,
    string $ville,
    string $description = '',
    string $urgency     = 'normal'
  ): ?string {
    try {
      // ── Contenu du PDF ──────────────────────────────────────
      // On génère un PDF minimal mais valide et lisible
      // Format A4 portrait, police Helvetica intégrée
      $today     = date('d/m/Y à H:i');
      $urgLabel  = $urgency === 'urgent' ? 'URGENT' : 'Normal';
      $desc      = wordwrap(strip_tags($description), 80, "\n", true);

      // Sérialise une chaîne UTF-8 pour l'insérer dans un stream PDF (literal string entre parenthèses).
      //
      // DEUX RÈGLES CRITIQUES du format PDF :
      //   1. Les délimiteurs ( ) et le caractère \ doivent être échappés : \( \) \\
      //      → sans ça, un @ ou une parenthèse dans l'email/description corrompt le stream
      //   2. Seul l'ASCII imprimable (0x20-0x7E) est sûr sans police avec encodage spécial
      //      → mb_convert_encoding() vers ISO-8859-1 retourne '' sur certains WAMP si
      //        la chaîne contient un caractère hors-plage → les champs apparaissent vides
      //      → on translittère manuellement les accents vers leur base ASCII
      $pdfStr = function(string $s): string {
        $from = [
          'à','â','ä','á','ã','å','ç','è','é','ê','ë','ì','í','î','ï',
          'ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','œ','æ',
          'À','Â','Ä','Á','Ã','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï',
          'Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý','Œ','Æ',
          '—','–',"\xE2\x80\x98","\xE2\x80\x99",'"','"','«','»','…','€','°',
        ];
        $to = [
          'a','a','a','a','a','a','c','e','e','e','e','i','i','i','i',
          'n','o','o','o','o','o','u','u','u','u','y','y','oe','ae',
          'A','A','A','A','A','A','C','E','E','E','E','I','I','I','I',
          'N','O','O','O','O','O','U','U','U','U','Y','OE','AE',
          '-','-',"'","'",'"','"','<<','>>','...','EUR','deg',
        ];
        $s = str_replace($from, $to, $s);
        // Supprimer tout caractère non-ASCII restant (évite corruption du stream PDF)
        $s = preg_replace('/[^\x20-\x7E]/', '', $s);
        // Échapper dans cet ordre : d'abord \ puis ( puis ) — ordre critique
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
      };

      // ── Construction du PDF ─────────────────────────────────
      $objects = [];
      $offsets = [];

      // Objet 1 : catalog
      $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj";

      // Objet 2 : pages
      $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj";

      // Objet 4 : police Helvetica (intégrée dans tout lecteur PDF)
      $objects[4] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj";
      $objects[5] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj";

      // ── Flux de contenu (page) ───────────────────────────────
      $stream = '';

      // Fond vert header — bande de y=725 à y=792 (67pt, origine PDF = bas-gauche)
      $stream .= "0.055 0.424 0.282 rg\n"; // #0e6c48
      $stream .= "0 725 612 67 re f\n";

      // Titre blanc — BT/ET dédié : premier Td = absolu depuis l'origine
      $stream .= "BT\n/F1 20 Tf\n1 1 1 rg\n175 762 Td\n";
      $stream .= "(" . $pdfStr('InfoDevis.fr') . ") Tj\nET\n";

      // Sous-titre — BT/ET séparé : Td repart de (0,0) → absolu
      $stream .= "BT\n/F2 10 Tf\n1 1 1 rg\n148 743 Td\n";
      $stream .= "(" . $pdfStr('La plateforme des artisans qualifies') . ") Tj\nET\n";

      // Référence — sous la bande verte
      $stream .= "BT\n/F1 13 Tf\n0.055 0.424 0.282 rg\n140 706 Td\n";
      $stream .= "(" . $pdfStr('Confirmation de demande — Ref : ' . $ref) . ") Tj\nET\n";

      // Ligne séparatrice
      $stream .= "0.055 0.424 0.282 RG\n";
      $stream .= "0.5 w\n";
      $stream .= "50 695 m 562 695 l S\n";

      // Section : Informations client
      $stream .= "BT\n";
      $stream .= "/F1 12 Tf\n";
      $stream .= "0.941 0.992 0.957 rg\n"; // vert pâle fond
      $stream .= "ET\n";

      // Fond section
      $stream .= "0.941 0.992 0.957 rg\n";
      $stream .= "50 680 512 18 re f\n";
      $stream .= "BT\n";
      $stream .= "/F1 11 Tf\n";
      $stream .= "0.055 0.424 0.282 rg\n";
      $stream .= "55 685 Td\n";
      $stream .= "(" . $pdfStr('INFORMATIONS CLIENT') . ") Tj\n";
      $stream .= "ET\n";

      // Données client
      $rows = [
        ['Nom complet',   $clientName],
        ['Email',         $clientEmail],
        ['Ville',         $ville],
        ['Categorie',     $categorie],
        ['Urgence',       $urgLabel],
        ['Date',          $today],
      ];

      $yPos = 665;
      $stream .= "0.2 0.2 0.2 rg\n";
      foreach ($rows as [$label, $value]) {
        // Label (gras) — BT/ET indépendant : Td est absolu depuis l'origine
        $stream .= "BT\n/F1 10 Tf\n55 {$yPos} Td\n";
        $stream .= "(" . $pdfStr($label . ' :') . ") Tj\nET\n";
        // Valeur (normal) — nouveau BT/ET : Td repart de (0,0) = position absolue
        $stream .= "BT\n/F2 10 Tf\n170 {$yPos} Td\n";
        $stream .= "(" . $pdfStr(mb_substr($value, 0, 60)) . ") Tj\nET\n";
        $yPos -= 15;
      }

      // Section : Description
      $yPos -= 10;
      $stream .= "0.941 0.992 0.957 rg\n";
      $stream .= "50 {$yPos} 512 18 re f\n";
      $stream .= "BT\n/F1 11 Tf\n";
      $stream .= "0.055 0.424 0.282 rg\n";
      $stream .= "55 " . ($yPos + 5) . " Td\n";
      $stream .= "(" . $pdfStr('DESCRIPTION DU PROJET') . ") Tj\nET\n";

      $yPos -= 15;
      $descLines = explode("\n", $desc);
      $stream .= "0.2 0.2 0.2 rg\n";
      foreach (array_slice($descLines, 0, 8) as $line) {
        $stream .= "BT\n/F2 9 Tf\n55 {$yPos} Td\n";
        $stream .= "(" . $pdfStr(trim($line)) . ") Tj\nET\n";
        $yPos -= 13;
      }

      // Section : Étapes suivantes
      $yPos -= 15;
      $stream .= "0.941 0.992 0.957 rg\n";
      $stream .= "50 {$yPos} 512 18 re f\n";
      $stream .= "BT\n/F1 11 Tf\n";
      $stream .= "0.055 0.424 0.282 rg\n";
      $stream .= "55 " . ($yPos + 5) . " Td\n";
      $stream .= "(" . $pdfStr('PROCHAINES ETAPES') . ") Tj\nET\n";

      $yPos -= 18;
      $etapes = [
        '1.  Votre demande est transmise aux artisans verifies de votre region.',
        '2.  Les artisans examinent votre projet et confirment leur interet.',
        '3.  Vous choisissez l\'artisan et demarrez les travaux.',
      ];
      $stream .= "0.2 0.2 0.2 rg\n";
      foreach ($etapes as $etape) {
        $stream .= "BT\n/F2 9 Tf\n55 {$yPos} Td\n";
        $stream .= "(" . $pdfStr($etape) . ") Tj\nET\n";
        $yPos -= 14;
      }

      // Pied de page
      $stream .= "0.8 0.8 0.8 RG\n0.3 w\n50 30 m 562 30 l S\n";
      $stream .= "BT\n/F2 8 Tf\n0.6 0.6 0.6 rg\n";
      $stream .= "120 20 Td\n";
      $stream .= "(" . $pdfStr('InfoDevis.fr  |  contact@info-devis.fr  |  Ce document confirme la reception de votre demande.') . ") Tj\n";
      $stream .= "ET\n";

      // Objet 6 : stream contenu page
      $streamLen = strlen($stream);
      $objects[6] = "6 0 obj\n<< /Length {$streamLen} >>\nstream\n{$stream}\nendstream\nendobj";

      // Objet 3 : page A4
      $objects[3] = "3 0 obj\n<< /Type /Page /Parent 2 0 R"
        . " /MediaBox [0 0 612 842]"
        . " /Contents 6 0 R"
        . " /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> >>\nendobj";

      // ── Assemblage PDF ──────────────────────────────────────
      $pdf = "%PDF-1.4\n";
      $order = [1, 2, 3, 4, 5, 6];
      foreach ($order as $n) {
        $offsets[$n] = strlen($pdf);
        $pdf .= $objects[$n] . "\n";
      }

      // Table xref
      $xrefOffset = strlen($pdf);
      $pdf .= "xref\n0 7\n0000000000 65535 f \n";
      foreach ($order as $n) {
        $pdf .= str_pad($offsets[$n], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
      }

      $pdf .= "trailer\n<< /Size 7 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

      // ── Sauvegarde ──────────────────────────────────────────
      $tmpDir = BASE_PATH . '/storage/tmp';
      if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);
      $tmpFile = $tmpDir . '/devis_' . preg_replace('/[^a-z0-9]/i', '_', $ref) . '_' . time() . '.pdf';
      file_put_contents($tmpFile, $pdf);

      // Normaliser le chemin (résout les ".." → chemin absolu réel)
      // Indispensable pour que file_exists() et addAttachment() fonctionnent
      // correctement sur Windows (chemin mixte C:\...\config/../storage/tmp)
      $realPath = realpath($tmpFile);
      if ($realPath === false) {
        error_log('[PDF ERROR] realpath() a échoué pour : ' . $tmpFile);
        return null;
      }

      error_log('[PDF GENERATED] ' . $realPath . ' (' . strlen($pdf) . ' octets)');
      return $realPath;
    } catch (\Exception $e) {
      error_log('[PDF ERROR] ' . $e->getMessage());
      return null;
    }
  }

  // ── Template HTML ─────────────────────────────────────────────
  private static function template(string $content, string $title = ''): string
  {
    $appUrl = defined('APP_URL') ? APP_URL : '';
    return '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>' . htmlspecialchars($title) . '</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif;background:#f5f7fa;color:#1a1a2e}
.w{max-width:600px;margin:32px auto;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb}
.h{background:#0e6c48;padding:28px 32px;text-align:center}
.h h1{color:#e1ffeb;font-size:22px;font-weight:700;font-style:italic;margin:0}
.h p{color:#a0f4c6;font-size:12px;margin-top:5px}
.b{padding:28px 32px}
p{font-size:14px;line-height:1.75;color:#374151;margin-bottom:12px}
.btn{display:inline-block;padding:11px 26px;background:#0e6c48;color:#fff!important;text-decoration:none;border-radius:6px;font-weight:700;font-size:14px;margin:14px 0}
.badge{display:inline-block;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;border-radius:20px;padding:3px 12px;font-size:12px;font-weight:600;margin-bottom:14px}
.box{background:#f0fdf4;border-left:3px solid #0e6c48;padding:11px 15px;border-radius:0 5px 5px 0;margin:14px 0}
.box p{margin-bottom:4px;font-size:13px}
.box p:last-child{margin-bottom:0}
.step{display:flex;gap:10px;margin-bottom:10px;align-items:flex-start}
.sn{flex-shrink:0;width:24px;height:24px;background:#0e6c48;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px}
.sb p{margin:0;font-size:13px}
.warn{background:#fff8ed;border-left:3px solid #f59e0b;padding:11px 15px;border-radius:0 5px 5px 0;margin:14px 0}
.f{background:#f9fafb;padding:18px 32px;text-align:center;border-top:1px solid #e5e7eb}
.f p{font-size:11px;color:#9ca3af;line-height:1.6}
.f a{color:#0e6c48;text-decoration:none}
</style>
</head>
<body>
<div class="w">
<div class="h"><h1>Info-Devis</h1><p>La plateforme des artisans qualifiés</p></div>
<div class="b">' . $content . '</div>
<div class="f">
<p>&copy; ' . date('Y') . ' InfoDevis.fr &middot; <a href="' . $appUrl . '">www.info-devis.fr</a> &middot; <a href="' . $appUrl . '/contact">Contact</a></p>
<p style="margin-top:5px;font-size:10px;color:#d1d5db">Vous recevez cet email suite à une demande soumise sur InfoDevis.fr</p>
</div>
</div>
</body>
</html>';
  }

    // ══════════════════════════════════════════════════════════════
    // EMAILS MÉTIER
    // ══════════════════════════════════════════════════════════════

  /**
   * Confirmation client — avec PDF récapitulatif en pièce jointe
   * Appel dans DevisController::create() :
   *   MailService::sendDevisReception($email, $firstName, $ref, $catName, $ville, $description, $urgency);
   */
  public static function sendDevisReception(
    string $email,
    string $name,
    string $ref,
    string $categorie,
    string $ville,
    string $description = '',
    string $urgency     = 'normal'
  ): bool {
    $appUrl  = defined('APP_URL') ? APP_URL : '';
    $content = '
<span class="badge">&#10003; Demande bien reçue</span>
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Nous avons bien reçu votre demande. Elle va être transmise aux artisans qualifiés de votre région sous 24h.</p>
<div class="box">
  <p><strong>Référence :</strong> ' . htmlspecialchars($ref) . '</p>
  <p><strong>Catégorie :</strong> ' . htmlspecialchars($categorie) . '</p>
  <p><strong>Ville :</strong> ' . htmlspecialchars($ville) . '</p>
  <p><strong>Urgence :</strong> ' . ($urgency === 'urgent' ? '🔴 Urgent' : '🟢 Normal') . '</p>
</div>
<p><strong>Que se passe-t-il maintenant ?</strong></p>
<div class="step"><div class="sn">1</div><div class="sb"><p>Votre demande est transmise aux artisans vérifiés de votre région.</p></div></div>
<div class="step"><div class="sn">2</div><div class="sb"><p>Les artisans examinent votre projet et confirment leur intérêt.</p></div></div>
<div class="step"><div class="sn">3</div><div class="sb"><p>Vous recevez un email dès qu\'un artisan est prêt à intervenir.</p></div></div>
<div style="text-align:center"><a href="' . $appUrl . '/dashboard/client/devis" class="btn">Suivre ma demande</a></div>
<p style="font-size:11px;color:#9ca3af;text-align:center">Le récapitulatif PDF de votre demande est joint à cet email.</p>';

    $pdfPath = self::generateDevisPdf($ref, $name, $email, $categorie, $ville, $description, $urgency);

    return self::send(
      $email,
      'InfoDevis — Votre demande ' . $ref . ' a bien été reçue',
      self::template($content, 'Confirmation de votre demande'),
      $name,
      $pdfPath,
      'Recapitulatif_' . $ref . '.pdf'
    );
  }

  public static function sendArtisanSelectionne(string $clientEmail, string $clientName, string $ref, string $artisanNom, string $artisanEntreprise, string $artisanVille): bool
  {
    $appUrl = defined('APP_URL') ? APP_URL : '';
    $an     = htmlspecialchars($artisanNom);
    $c      = '
<span class="badge">Artisan sélectionné</span>
<p>Bonjour <strong>' . htmlspecialchars($clientName) . '</strong>,</p>
<p>Un artisan a été retenu pour votre demande <strong>' . htmlspecialchars($ref) . '</strong>.</p>
<div class="box">
  <p><strong>Artisan :</strong> ' . $an . '</p>
  <p><strong>Entreprise :</strong> ' . htmlspecialchars($artisanEntreprise) . '</p>
  <p><strong>Zone :</strong> ' . htmlspecialchars($artisanVille) . '</p>
</div>
<div class="step"><div class="sn">1</div><div class="sb"><p><strong>' . $an . '</strong> va vous contacter directement avec ses coordonnées.</p></div></div>
<div class="step"><div class="sn">2</div><div class="sb"><p>Discutez des détails et recevez un devis écrit signé.</p></div></div>
<div class="step"><div class="sn">3</div><div class="sb"><p>Une fois d\'accord, les travaux démarrent selon votre planning.</p></div></div>
<div class="warn"><p style="color:#92400e;font-weight:700">&#9888; Conseil sécurité</p><p style="color:#92400e;font-size:12px">Ne versez jamais d\'acompte avant d\'avoir rencontré l\'artisan et reçu un devis signé.</p></div>
<div style="text-align:center"><a href="' . $appUrl . '/dashboard/client/devis" class="btn">Voir ma demande</a></div>';
    return self::send($clientEmail, 'InfoDevis — Un artisan a été retenu pour ' . $ref, self::template($c, 'Artisan sélectionné'), $clientName);
  }

  public static function sendLeadNotification(string $email, string $name, array $lead): bool
  {
    $appUrl = defined('APP_URL') ? APP_URL : '';
    $c      = '
<span class="badge">Nouveau lead disponible</span>
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Un particulier dans votre zone recherche vos services !</p>
<div class="box">
  <p><strong>Ville :</strong> ' . htmlspecialchars($lead['ville'] ?? '') . '</p>
  <p><strong>Urgence :</strong> ' . htmlspecialchars($lead['urgency'] ?? 'Normal') . '</p>
  <p><strong>Référence :</strong> ' . htmlspecialchars($lead['ref'] ?? '') . '</p>
</div>
<p>Répondez dans les 2h pour maximiser vos chances d\'être retenu.</p>
<div style="text-align:center"><a href="' . $appUrl . '/dashboard/artisan/leads" class="btn">Voir le lead</a></div>';
    return self::send($email, 'InfoDevis — Nouveau lead : ' . ($lead['ref'] ?? ''), self::template($c, 'Nouveau lead'), $name);
  }

  public static function sendArtisanValidated(string $email, string $firstName, string $companyName): bool
  {
    $url = defined('APP_URL') ? APP_URL . '/dashboard/artisan' : '';
    $c   = '
<span class="badge">Compte validé &#10003;</span>
<p>Bonjour <strong>' . htmlspecialchars($firstName) . '</strong>,</p>
<p>Votre compte artisan <strong>' . htmlspecialchars($companyName) . '</strong> a été validé. Vous pouvez maintenant recevoir des leads qualifiés.</p>
<div style="text-align:center"><a href="' . $url . '" class="btn">Accéder à mon espace</a></div>';
    return self::send($email, 'InfoDevis — Votre compte artisan est validé', self::template($c, 'Compte artisan validé'), $firstName);
  }

  public static function sendArtisanRefused(string $email, string $firstName, string $note): bool
  {
    $url   = defined('APP_URL') ? APP_URL . '/dashboard/artisan/documents' : '';
    $motif = $note ? '<div class="box"><p><strong>Motif :</strong> ' . htmlspecialchars($note) . '</p></div>' : '';
    $c     = '
<p>Bonjour <strong>' . htmlspecialchars($firstName) . '</strong>,</p>
<p>Votre dossier ne peut pas être validé pour le moment.</p>' . $motif . '
<div style="text-align:center"><a href="' . $url . '" class="btn">Mettre à jour mes documents</a></div>';
    return self::send($email, 'InfoDevis — Action requise sur votre dossier', self::template($c, 'Dossier artisan'), $firstName);
  }

  public static function sendVerification(string $email, string $name, string $token): bool
  {
    $url = (defined('APP_URL') ? APP_URL : '') . '/verifier-email?token=' . urlencode($token);
    $c   = '
<span class="badge">Vérification requise</span>
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Cliquez ci-dessous pour activer votre compte InfoDevis :</p>
<div style="text-align:center"><a href="' . $url . '" class="btn">Vérifier mon adresse email</a></div>
<p style="font-size:11px;color:#9ca3af;margin-top:8px">Ce lien est valable 24 heures.</p>';
    return self::send($email, 'InfoDevis — Activez votre compte', self::template($c, 'Activation de votre compte'), $name);
  }

  public static function sendPasswordReset(string $email, string $name, string $token): bool
  {
    $url = (defined('APP_URL') ? APP_URL : '') . '/reset-password?token=' . urlencode($token);
    $c   = '
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
<div style="text-align:center"><a href="' . $url . '" class="btn">Réinitialiser mon mot de passe</a></div>
<div class="box"><p>Ce lien expire dans <strong>2 heures</strong>. Si vous n\'avez pas fait cette demande, ignorez cet email.</p></div>';
    return self::send($email, 'InfoDevis — Réinitialisation de mot de passe', self::template($c, 'Réinitialisation'), $name);
  }

  public static function sendAdminNewArtisan(string $companyName, int $artisanId): bool
  {
    $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@info-devis.fr';
    $url        = (defined('APP_URL') ? APP_URL : '') . '/admin/artisan/' . $artisanId;
    $c          = '
<span class="badge">Action requise</span>
<p>Un nouvel artisan attend votre validation :</p>
<div class="box"><p style="font-size:15px;font-weight:700;color:#0e6c48">' . htmlspecialchars($companyName) . '</p></div>
<div style="text-align:center"><a href="' . $url . '" class="btn">Voir le dossier</a></div>';
    return self::send($adminEmail, 'InfoDevis — Nouvel artisan en attente de validation', self::template($c, 'Nouvel artisan'), 'Admin InfoDevis');
  }

  public static function sendAvisRequest(string $email, string $name, int $devisId): bool
  {
    $appUrl = defined('APP_URL') ? APP_URL : '';
    $c      = '
<p>Bonjour <strong>' . htmlspecialchars($name) . '</strong>,</p>
<p>Vos travaux sont terminés. Votre avis aide les autres clients !</p>
<div style="text-align:center"><a href="' . $appUrl . '/dashboard/client/avis?devis=' . (int)$devisId . '" class="btn">Laisser mon avis</a></div>';
    return self::send($email, 'InfoDevis — Donnez votre avis sur vos travaux', self::template($c, 'Votre avis'), $name);
  }

  public static function sendDevisConfirmation(string $email, string $name, string $ref, string $ville): bool
  {
    return self::sendDevisReception($email, $name, $ref, 'Non précisée', $ville);
  }
}
