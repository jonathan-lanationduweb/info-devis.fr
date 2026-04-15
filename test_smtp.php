<?php
/**
 * test_smtp.php — Test d'envoi SMTP Gmail pour InfoDevis.fr
 *
 * USAGE :
 *   http://localhost/info-devis/test_smtp.php
 *
 * Ce fichier envoie un email de test avec une pièce jointe PDF minimal
 * et affiche le résultat détaillé (succès ou erreur SMTP précise).
 *
 * SUPPRIMER ce fichier avant la mise en production.
 */

// ── Sécurité basique : accès localhost uniquement ──────────────────────────
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteIp, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true)) {
    http_response_code(403);
    exit('Accès interdit (localhost uniquement).');
}

// ── Chargement de l'environnement ──────────────────────────────────────────
$basePath = __DIR__;
$autoload = $basePath . '/vendor/autoload.php';
$config   = $basePath . '/config/app.php';

if (!file_exists($autoload)) {
    die('<b style="color:red">ERREUR :</b> vendor/autoload.php introuvable.<br>Lancez <code>composer install</code> dans ' . $basePath);
}
if (!file_exists($config)) {
    die('<b style="color:red">ERREUR :</b> config/app.php introuvable.');
}

require_once $autoload;
require_once $config;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

// ── Adresse de destination du test ─────────────────────────────────────────
// Modifiez cette adresse avant de lancer le test
$testTo   = 'jonathan@lanationduweb.fr'; // ← CHANGER ICI
$testName = 'Test InfoDevis';

// ── Affichage HTML ─────────────────────────────────────────────────────────
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Test SMTP — InfoDevis</title>
<style>
  body { font-family: monospace; background: #1e1e2e; color: #cdd6f4; padding: 20px; }
  h1   { color: #89dceb; }
  h2   { color: #a6e3a1; margin-top: 20px; }
  .ok  { color: #a6e3a1; }
  .err { color: #f38ba8; }
  .warn{ color: #fab387; }
  pre  { background: #313244; padding: 12px; border-radius: 6px; white-space: pre-wrap; word-break: break-all; }
  table { border-collapse: collapse; width: 100%; max-width: 700px; }
  td, th { padding: 6px 12px; border: 1px solid #45475a; text-align: left; }
  th { background: #313244; color: #cba6f7; }
</style>
</head>
<body>
<h1>Test SMTP — InfoDevis.fr</h1>
<p>Date : <?= date('d/m/Y H:i:s') ?></p>

<?php
// ── 1. Vérification de la configuration ────────────────────────────────────
echo '<h2>1. Configuration SMTP détectée</h2>';
echo '<table>';
echo '<tr><th>Constante</th><th>Valeur</th><th>Statut</th></tr>';

$checks = [
    'MAIL_HOST'   => defined('MAIL_HOST')   ? MAIL_HOST   : null,
    'MAIL_PORT'   => defined('MAIL_PORT')   ? MAIL_PORT   : null,
    'MAIL_SECURE' => defined('MAIL_SECURE') ? MAIL_SECURE : null,
    'MAIL_USER'   => defined('MAIL_USER')   ? MAIL_USER   : null,
    'MAIL_PASS'   => defined('MAIL_PASS')   ? MAIL_PASS   : null,
    'MAIL_FROM'   => defined('MAIL_FROM')   ? MAIL_FROM   : null,
];

$configOk = true;
foreach ($checks as $key => $val) {
    $display = ($key === 'MAIL_PASS' && !empty($val))
        ? str_repeat('*', max(4, strlen($val) - 4)) . substr($val, -4)
        : (string)$val;

    if (empty($val)) {
        $status   = '<span class="err">VIDE — à remplir</span>';
        $configOk = false;
    } else {
        $status = '<span class="ok">OK</span>';
    }
    echo "<tr><td><b>{$key}</b></td><td>{$display}</td><td>{$status}</td></tr>";
}
echo '</table>';

if (!$configOk) {
    echo '<p class="err"><b>Configuration incomplète.</b> Remplissez MAIL_USER, MAIL_PASS et MAIL_FROM dans config/app.php puis relancez ce test.</p>';
    echo '</body></html>';
    exit;
}

// ── 2. Génération d'un PDF minimal de test ────────────────────────────────
echo '<h2>2. Génération d\'un PDF de test</h2>';

$tmpDir  = $basePath . '/storage/tmp';
if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);

$pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n"
    . "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n"
    . "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Contents 4 0 R"
    . " /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n"
    . "4 0 obj\n<< /Length 44 >>\nstream\nBT /F1 16 Tf 200 700 Td (Test InfoDevis) Tj ET\nendstream\nendobj\n"
    . "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n"
    . "xref\n0 6\n0000000000 65535 f \n"
    . "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n9\n%%EOF";

$pdfPath = $tmpDir . '/test_smtp_' . time() . '.pdf';
file_put_contents($pdfPath, $pdfContent);
$pdfPath = realpath($pdfPath);

if ($pdfPath && file_exists($pdfPath)) {
    echo "<p class='ok'>PDF créé : <code>{$pdfPath}</code> (" . filesize($pdfPath) . " octets)</p>";
} else {
    echo "<p class='err'>Impossible de créer le PDF dans <code>{$tmpDir}</code>. Vérifiez les permissions.</p>";
    echo '</body></html>';
    exit;
}

// ── 3. Connexion SMTP + envoi ─────────────────────────────────────────────
echo '<h2>3. Envoi SMTP en cours…</h2>';

$log = [];

try {
    $mail = new PHPMailer(true);

    // Activer le debug SMTP — niveau 2 = dialogue complet serveur
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function (string $str, int $level) use (&$log) {
        $log[] = htmlspecialchars(trim($str));
    };

    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USER;
    $mail->Password   = MAIL_PASS;
    $mail->SMTPSecure = (MAIL_SECURE === 'ssl')
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int) MAIL_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';

    $mail->setFrom(MAIL_FROM, 'InfoDevis Test');
    $mail->addReplyTo(MAIL_FROM, 'InfoDevis Test');
    $mail->addAddress($testTo, $testName);

    $mail->isHTML(true);
    $mail->Subject = 'InfoDevis — Test SMTP ' . date('d/m/Y H:i:s');
    $mail->Body    = '<h2 style="color:#0e6c48">Test SMTP réussi !</h2>'
        . '<p>Cet email confirme que la configuration Gmail SMTP fonctionne.</p>'
        . '<p>Date : ' . date('d/m/Y H:i:s') . '</p>'
        . '<p>La pièce jointe PDF est en bas de cet email.</p>';
    $mail->AltBody = 'Test SMTP InfoDevis — ' . date('d/m/Y H:i:s');

    $mail->addAttachment($pdfPath, 'Test_InfoDevis.pdf');
    echo "<p>Pièce jointe : <code>{$pdfPath}</code> — file_exists = "
        . (file_exists($pdfPath) ? '<span class="ok">true</span>' : '<span class="err">false</span>')
        . "</p>";

    $mail->send();

    echo "<p class='ok'><b>Email envoyé avec succès !</b></p>";
    echo "<p>Vérifiez la boîte <b>{$testTo}</b> (et le dossier Spam).</p>";

    @unlink($pdfPath);

} catch (MailerException $e) {
    echo "<p class='err'><b>Erreur PHPMailer :</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    @unlink($pdfPath);
} catch (\Exception $e) {
    echo "<p class='err'><b>Exception :</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    @unlink($pdfPath);
}

// ── 4. Journal de débogage SMTP ───────────────────────────────────────────
if (!empty($log)) {
    echo '<h2>4. Journal SMTP (dialogue serveur)</h2>';
    echo '<pre>' . implode("\n", $log) . '</pre>';
}
?>

<h2>Aide rapide</h2>
<table>
  <tr><th>Erreur</th><th>Cause probable</th><th>Solution</th></tr>
  <tr>
    <td>535 Authentication failed</td>
    <td>Mauvais mot de passe d'application</td>
    <td>Regénérez un mot de passe sur myaccount.google.com/apppasswords</td>
  </tr>
  <tr>
    <td>534 Application-specific password required</td>
    <td>Validation en 2 étapes non activée</td>
    <td>Activez-la sur myaccount.google.com/security</td>
  </tr>
  <tr>
    <td>Connection timed out</td>
    <td>Pare-feu WAMP bloque le port 587</td>
    <td>Essayez port 465 avec MAIL_SECURE=ssl, ou vérifiez Windows Defender</td>
  </tr>
  <tr>
    <td>MAIL_FROM doit être = MAIL_USER</td>
    <td>Gmail rejette From ≠ compte connecté</td>
    <td>Mettez la même adresse Gmail dans MAIL_FROM et MAIL_USER</td>
  </tr>
</table>

<p class="warn" style="margin-top:20px"><b>⚠ SUPPRIMEZ ce fichier avant la mise en production !</b></p>
</body>
</html>
