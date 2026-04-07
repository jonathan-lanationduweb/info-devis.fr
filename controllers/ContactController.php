<?php
require_once BASE_PATH . '/controllers/BaseController.php';
require_once BASE_PATH . '/services/MailService.php';

class ContactController extends BaseController {

    public function index(): void {
        $this->view('home/contact', ['pageTitle' => 'Contact | InfoDevis']);
    }

    public function send(): void {
        $this->csrfCheck();
        if (!Security::checkRateLimit('contact_' . Security::getIp(), 5, 3600)) {
            $this->view('home/contact', ['pageTitle' => 'Contact | InfoDevis', 'error' => 'Trop de messages envoyés. Réessayez dans 1h.']);
            return;
        }

        $fn      = Security::sanitize($this->input('first_name'));
        $ln      = Security::sanitize($this->input('last_name'));
        $email   = $this->input('email');
        $subject = Security::sanitize($this->input('subject', 'Contact site'));
        $message = Security::sanitize($this->input('message'));

        if (!Security::isValidEmail($email) || empty($message)) {
            $this->view('home/contact', ['pageTitle' => 'Contact | InfoDevis', 'error' => 'Informations invalides.']);
            return;
        }

        // Envoi email admin
        $html = "<p><strong>De :</strong> {$fn} {$ln} &lt;{$email}&gt;</p>
                 <p><strong>Sujet :</strong> {$subject}</p>
                 <p><strong>Message :</strong></p><p>" . nl2br($message) . "</p>";
        mail(CONTACT_EMAIL, '[InfoDevis] ' . $subject, $html, 'From: ' . MAIL_FROM . "\r\nContent-Type: text/html; charset=UTF-8\r\n");

        $this->log('contact_sent', 'contact', 0, ['email' => $email, 'subject' => $subject]);
        $this->view('home/contact', ['pageTitle' => 'Contact | InfoDevis', 'success' => true]);
    }
}
