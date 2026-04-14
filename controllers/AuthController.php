<?php
require_once BASE_PATH . '/controllers/BaseController.php';
require_once BASE_PATH . '/models/UserModel.php';
require_once BASE_PATH . '/models/ArtisanModel.php';
require_once BASE_PATH . '/services/MailService.php';
require_once BASE_PATH . '/services/SiretService.php';

class AuthController extends BaseController
{

    private UserModel $userModel;
    private ArtisanModel $artisanModel;

    // ── Détecte si on est en local WAMP ──────────────────────
    private function isLocal(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return in_array($host, ['localhost', '127.0.0.1', 'localhost:80', 'localhost:8080'])
            || str_ends_with($host, '.local')
            || (defined('APP_ENV') && APP_ENV === 'local');
    }

    public function __construct()
    {
        $this->userModel    = new UserModel();
        $this->artisanModel = new ArtisanModel();
    }

    public function loginForm(): void
    {
        $this->view('auth/login', ['pageTitle' => 'Connexion']);
    }

    public function login(): void
    {
        $this->csrfCheck();

        $email    = (string)($this->input('email') ?? '');
        $password = (string)($this->input('password') ?? '');

        if (!Security::isValidEmail($email)) {
            $this->view('auth/login', ['error' => 'Email invalide.', 'pageTitle' => 'Connexion']);
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !Security::verifyPassword($password, $user['password'])) {
            Security::logSecurity('LOGIN_FAIL', ['email' => $email]);
            $this->view('auth/login', ['error' => 'Identifiants incorrects.', 'pageTitle' => 'Connexion']);
            return;
        }

        if (!$user['is_active']) {
            $this->view('auth/login', ['error' => 'Compte désactivé.', 'pageTitle' => 'Connexion']);
            return;
        }

        // ── CORRECTION : en local, on bypasse la vérification email ──
        if (!$user['email_verified_at'] && !$this->isLocal()) {
            $this->view('auth/login', ['error' => 'Veuillez vérifier votre email.', 'pageTitle' => 'Connexion']);
            return;
        }

        $this->userModel->updateLastLogin($user['id']);
        $this->setSession($user);
        $this->log('user_login', 'users', $user['id']);

        switch ($user['role']) {
            case 'admin':
                $this->redirect('/admin');
                break;
            case 'artisan':
                $this->redirect('/dashboard/artisan');
                break;
            default:
                $this->redirect('/dashboard/client');
                break;
        }
    }

    public function registerForm(): void
    {
        $type = $_GET['type'] ?? 'client';
        $this->view('auth/register', ['pageTitle' => 'Inscription', 'type' => $type]);
    }

    public function register(): void
    {
        $this->csrfCheck();
        $role = (string)($this->input('role') ?? 'client');

        $email     = (string)($this->input('email') ?? '');
        $password  = (string)($this->input('password') ?? '');
        $firstName = (string)($this->input('first_name') ?? '');
        $lastName  = (string)($this->input('last_name') ?? '');
        $phone     = (string)($this->input('phone') ?? '');

        $errors = [];
        if (!Security::isValidEmail($email))         $errors[] = 'Email invalide.';
        if (strlen($password) < 8)                   $errors[] = 'Mot de passe trop court (8 caractères min).';
        if (empty($firstName) || empty($lastName))   $errors[] = 'Nom et prénom requis.';
        if ($this->userModel->findByEmail($email))   $errors[] = 'Email déjà utilisé.';
        if (!$this->input('consent_privacy'))        $errors[] = 'Vous devez accepter la politique de confidentialité.';

        if (!empty($errors)) {
            $this->view('auth/register', [
                'errors'    => $errors,
                'pageTitle' => 'Inscription',
                'type'      => $role,
            ]);
            return;
        }

        Database::beginTransaction();
        try {
            $token = bin2hex(random_bytes(32));

            // ── CORRECTION : en local, email vérifié immédiatement ──
            $emailVerifiedAt = $this->isLocal() ? date('Y-m-d H:i:s') : null;

            $userId = $this->userModel->create([
                'email'                      => $email,
                'password'                   => Security::hashPassword($password),
                'role'                       => $role,
                'first_name'                 => $firstName,
                'last_name'                  => $lastName,
                'phone'                      => $phone,
                'email_verification_token'   => $token,
                'email_verified_at'          => $emailVerifiedAt,
            ]);

            Database::insert('consents', [
                'user_id'      => $userId,
                'ip_address'   => Security::getIp(),
                'consent_type' => 'privacy_policy',
            ]);

            if ($this->input('consent_marketing')) {
                Database::insert('consents', [
                    'user_id'      => $userId,
                    'ip_address'   => Security::getIp(),
                    'consent_type' => 'marketing',
                ]);
            }

            if ($role === 'artisan') {
                $this->registerArtisan($userId);
            }

            if ($role === 'client') {
                Database::insert('client_score', ['user_id' => $userId, 'score' => 100]);
            }

            // ── CORRECTION : mail ignoré silencieusement en local ──
            if (!$this->isLocal()) {
                MailService::sendVerification($email, $firstName, $token);
            } else {
                error_log('[LOCAL] Mail verification skipped for: ' . $email . ' | token: ' . $token);
            }

            Database::commit();
            $this->log('user_register', 'users', $userId, ['role' => $role]);

            // ── En local : connecter directement, pas besoin de vérifier l'email ──
            if ($this->isLocal()) {
                $user = $this->userModel->findByEmail($email);
                if ($user) {
                    $this->setSession($user);
                    switch ($role) {
                        case 'artisan':
                            $this->redirect('/dashboard/artisan');
                            break;
                        case 'admin':
                            $this->redirect('/admin');
                            break;
                        default:
                            $this->redirect('/dashboard/client');
                            break;
                    }
                    return;
                }
            }

            $this->view('auth/register-success', ['pageTitle' => 'Inscription réussie', 'email' => $email]);
        } catch (Exception $e) {
            Database::rollback();
            error_log('[REGISTER ERROR] ' . $e->getMessage());
            $this->view('auth/register', [
                'errors'    => ['Erreur lors de l\'inscription. Réessayez. (' . $e->getMessage() . ')'],
                'pageTitle' => 'Inscription',
                'type'      => $role,
            ]);
        }
    }

    private function registerArtisan(int $userId): void
    {
        $siret       = preg_replace('/\s/', '', (string)($this->input('siret') ?? ''));
        $companyName = (string)($this->input('company_name') ?? '');
        $ville       = (string)($this->input('ville') ?? '');
        $codePostal  = (string)($this->input('code_postal') ?? '');

        $errors = [];
        if (!Security::isValidSiret($siret)) $errors[] = 'Numéro SIRET invalide.';
        if (empty($companyName))             $errors[] = 'Nom de l\'entreprise requis.';
        if (empty($ville))                   $errors[] = 'Ville requise.';

        if (!empty($errors)) throw new Exception(implode(' ', $errors));

        // ── En local : SIRET pas vérifié via API externe ──
        $siretVerified = $this->isLocal() ? 1 : (SiretService::verify($siret) ? 1 : 0);

        $artisanId = Database::insert('artisans', [
            'user_id'             => $userId,
            'siret'               => $siret,
            'siret_verified'      => $siretVerified,
            'company_name'        => $companyName,
            'ville'               => $ville,
            'code_postal'         => $codePostal,
            'verification_status' => 'pending',
            'plan'                => 'gratuit',
        ]);

        Database::insert('artisan_stats', ['artisan_id' => $artisanId]);
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->log('user_logout', 'users', $_SESSION['user_id'] ?? 0);
        session_destroy();
        $this->redirect('/connexion');
    }

    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';
        $user  = $this->userModel->findByVerificationToken($token);
        if (!$user) {
            $this->view('auth/verify-error', ['pageTitle' => 'Lien invalide']);
            return;
        }
        $this->userModel->verifyEmail($user['id']);
        $this->view('auth/verify-success', ['pageTitle' => 'Email vérifié']);
    }

    public function forgotForm(): void
    {
        $this->view('auth/forgot', ['pageTitle' => 'Mot de passe oublié']);
    }

    public function forgot(): void
    {
        $this->csrfCheck();
        $email = (string)($this->input('email') ?? '');
        $user  = $this->userModel->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->setResetToken($user['id'], $token);
            try {
                MailService::sendPasswordReset($email, $user['first_name'], $token);
            } catch (Exception $e) {
                error_log('[LOCAL] Reset mail skipped: ' . $e->getMessage());
            }
        }

        $this->view('auth/forgot-sent', ['pageTitle' => 'Email envoyé']);
    }

    public function resetForm(): void
    {
        $token = $_GET['token'] ?? '';
        $this->view('auth/reset', ['pageTitle' => 'Nouveau mot de passe', 'token' => $token]);
    }

    public function reset(): void
    {
        $this->csrfCheck();
        $token    = (string)($this->input('token') ?? '');
        $password = (string)($this->input('password') ?? '');

        if (strlen($password) < 8) {
            $this->view('auth/reset', [
                'error'     => 'Mot de passe trop court.',
                'pageTitle' => 'Nouveau mot de passe',
                'token'     => $token,
            ]);
            return;
        }

        $user = $this->userModel->findByResetToken($token);
        if (!$user) {
            $this->view('auth/reset', [
                'error'     => 'Lien invalide ou expiré.',
                'pageTitle' => 'Nouveau mot de passe',
                'token'     => $token,
            ]);
            return;
        }

        $this->userModel->updatePassword($user['id'], Security::hashPassword($password));
        $this->log('password_reset', 'users', $user['id']);
        $this->redirect('/connexion?reset=1');
    }

    private function setSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
    }
}
