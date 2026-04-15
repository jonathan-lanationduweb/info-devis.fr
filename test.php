<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// On charge l'autoloader que tu as installé avec Composer
require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // --- CONFIGURATION AVEC TES CODES MAILTRAP ---
    $mail->isSMTP();
    $mail->Host       = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth   = true;
    $mail->Port       = 2525;
    $mail->Username   = '48ca78bab706c9';
    $mail->Password   = 'a15ced477683da';

    // --- EXPÉDITEUR ET DESTINATAIRE ---
    $mail->setFrom('test@info-devis.local', 'Admin Devis');
    $mail->addAddress('jonathan@lanationduweb.fr'); // C'est ici que tu recevras le mail dans Mailtrap

    // --- CONTENU ---
    $mail->isHTML(true);
    $mail->Subject = 'Test PHPMailer Reussi !';
    $mail->Body    = '<h1>Bravo Jonathan</h1><p>Tes identifiants Mailtrap sont bien configurés dans PHPMailer.</p>';

    $mail->send();
    echo 'C\'est envoyé ! Connecte-toi sur Mailtrap.io, va dans "My Inbox" et tu verras ton mail.';
} catch (Exception $e) {
    echo "L'envoi a échoué. Erreur : {$mail->ErrorInfo}";
}
