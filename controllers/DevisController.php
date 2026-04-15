<?php
require_once BASE_PATH . '/controllers/BaseController.php';
require_once BASE_PATH . '/models/DevisModel.php';
require_once BASE_PATH . '/models/CategoryModel.php';
require_once BASE_PATH . '/services/MatchingService.php';
require_once BASE_PATH . '/services/MailService.php';
require_once BASE_PATH . '/services/AntiDuplicateService.php';

class DevisController extends BaseController
{

    private DevisModel          $devisModel;
    private CategoryModel       $categoryModel;
    private MatchingService     $matchingService;
    private AntiDuplicateService $antiDupService;

    public function __construct()
    {
        $this->devisModel      = new DevisModel();
        $this->categoryModel   = new CategoryModel();
        $this->matchingService = new MatchingService();
        $this->antiDupService  = new AntiDuplicateService();
    }

    public function form(): void
    {
        $categories = $this->categoryModel->getMainCategories();
        $catId      = (int)($_GET['categorie'] ?? 0);
        $this->view('home/devis', [
            'pageTitle'  => 'Demander un devis gratuit | InfoDevis',
            'categories' => $categories,
            'catId'      => $catId,
        ]);
    }

    public function create(): void
    {
        $this->csrfCheck();

        // ── CORRECTION : cast string pour éviter null sur isValidEmail ──
        $email      = (string)($this->input('email') ?? '');
        $phone      = (string)($this->input('phone') ?? '');
        $firstName  = (string)($this->input('first_name') ?? '');
        $lastName   = (string)($this->input('last_name') ?? '');
        $ville      = (string)($this->input('ville') ?? '');
        $codePostal = (string)($this->input('code_postal') ?? '');
        $catId      = (int)($this->input('category_id') ?? 0);
        $categories = $_POST['categories'] ?? ($catId > 0 ? [$catId] : []);
        $description = (string)($this->input('description') ?? '');
        $urgency    = (string)($this->input('urgency') ?? 'normal');

        // Validation
        $errors = [];
        if (empty($email) || !Security::isValidEmail($email)) $errors[] = 'Email invalide.';
        if (empty($firstName))               $errors[] = 'Prénom requis.';
        if (empty($ville))                   $errors[] = 'Ville requise.';
        if (empty($categories))              $errors[] = 'Catégorie requise.';
        if (empty($description))             $errors[] = 'Description requise.';
        if (!$this->input('consent_privacy')) $errors[] = 'Consentement requis.';

        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
            return;
        }

        // Vérification score client (anti-spam)
        $clientScore = $this->getClientScore($email);
        if ($clientScore && $clientScore['status'] === 'bloque') {
            $this->json(['success' => false, 'errors' => ['Accès temporairement bloqué.']], 429);
            return;
        }

        // Anti-doublon
        $catIdMain = (int)$categories[0];
        $dupHash   = Security::generateLeadHash($email, $phone, $catIdMain, $ville);
        if ($this->antiDupService->isDuplicate($dupHash)) {
            $this->json(['success' => false, 'errors' => ['Une demande similaire existe déjà. Attendez 24h.']], 409);
            return;
        }

        Database::beginTransaction();
        try {
            // Trouver ou créer utilisateur
            $userId = $this->findOrCreateClient($email, $firstName, $lastName, $phone);

            // Créer le devis
            $ref     = 'DV' . date('Ymd') . strtoupper(substr(uniqid(), -6));
            $title   = $this->generateTitle($categories, $description);
            $devisId = Database::insert('devis', [
                'reference'            => $ref,
                'client_id'            => $userId,
                'title'                => $title,
                'description'          => $description,
                'category_id'          => $catIdMain,
                'ville'                => $ville,
                'code_postal'          => $codePostal,
                'urgency'              => $urgency,
                'status'               => 'sent',
                'anti_duplicate_hash'  => $dupHash,
            ]);

            // Association multi-catégories
            foreach ($categories as $cid) {
                $cid = (int)$cid;
                if ($cid > 0) {
                    Database::insert('devis_categories', [
                        'devis_id'    => $devisId,
                        'category_id' => $cid,
                    ]);
                }
            }

            // Consentement RGPD
            Database::insert('consents', [
                'user_id'      => $userId,
                'ip_address'   => Security::getIp(),
                'consent_type' => 'privacy_policy',
            ]);

            // Matching artisans
            $totalLeads = 0;
            foreach ($categories as $cid) {
                $cid      = (int)$cid;
                $artisans = $this->matchingService->findArtisans($cid, $ville, $codePostal, 5);
                foreach ($artisans as $artisan) {
                    Database::insert('leads', [
                        'devis_id'    => $devisId,
                        'artisan_id'  => $artisan['id'],
                        'category_id' => $cid,
                        'status'      => 'pending',
                        'notified_at' => date('Y-m-d H:i:s'),
                    ]);
                    $this->notifyArtisan($artisan, $devisId, $ref, $ville, $description, $urgency);
                    $totalLeads++;
                }
            }

            // Email confirmation client (ignoré si SMTP non dispo en local)
            try {
                $catName = Database::fetch('SELECT name FROM categories WHERE id=?', [$catIdMain])['name'] ?? 'Travaux';
                MailService::sendDevisReception($email, $firstName, $ref, $catName, $ville, $description, $urgency);
            } catch (Exception $mailEx) {
                error_log('[MAIL SKIP] ' . $mailEx->getMessage());
            }

            $this->scheduleRelance($devisId, $userId);
            $this->incrementClientRequests($userId);

            Database::commit();
            $this->log('lead_created', 'devis', $devisId, ['ref' => $ref, 'artisans' => $totalLeads]);

            $this->json([
                'success'     => true,
                'message'     => '🎉 Demande envoyée ! Vous recevrez vos premiers devis sous 24 heures.',
                'reference'   => $ref,
                'leads_count' => $totalLeads,
                'redirect'    => APP_URL . '/devis/confirmation?ref=' . $ref,
            ]);
        } catch (Exception $e) {
            Database::rollback();
            error_log('[DEVIS ERROR] ' . $e->getMessage());
            $this->json(['success' => false, 'errors' => ['Erreur interne. Réessayez.']], 500);
        }
    }

    public function confirmation(): void
    {
        $ref = Security::sanitize($_GET['ref'] ?? '');
        $this->view('home/devis-confirmation', [
            'pageTitle' => 'Demande envoyée | InfoDevis',
            'reference' => $ref,
        ]);
    }

    private function findOrCreateClient(string $email, string $fn, string $ln, string $phone): int
    {
        $user = Database::fetch('SELECT id FROM users WHERE email = ?', [$email]);
        if ($user) return $user['id'];

        $userId = Database::insert('users', [
            'email'            => $email,
            'password'         => Security::hashPassword(bin2hex(random_bytes(12))),
            'role'             => 'client',
            'first_name'       => $fn,
            'last_name'        => $ln,
            'phone'            => $phone,
            // ── CORRECTION : email vérifié automatiquement pour les clients devis ──
            'email_verified_at' => date('Y-m-d H:i:s'),
            'is_active'        => 1,
        ]);
        Database::insert('client_score', ['user_id' => $userId]);
        return $userId;
    }

    private function generateTitle(array $catIds, string $description): string
    {
        if (count($catIds) > 1) {
            return 'Travaux multiples - ' . substr($description, 0, 60);
        }
        $cat = Database::fetch('SELECT name FROM categories WHERE id = ?', [(int)$catIds[0]]);
        return ($cat['name'] ?? 'Travaux') . ' - ' . substr($description, 0, 50);
    }

    private function notifyArtisan(array $artisan, int $devisId, string $ref, string $ville, string $desc, string $urgency): void
    {
        try {
            Database::insert('notifications', [
                'user_id' => $artisan['user_id'],
                'type'    => 'new_lead',
                'title'   => 'Nouveau lead : ' . $ref,
                'body'    => 'Un particulier à ' . $ville . ' cherche un artisan.',
                'data'    => json_encode(['devis_id' => $devisId, 'ref' => $ref, 'ville' => $ville]),
            ]);
        } catch (Exception $e) {
            error_log('[NOTIFY] ' . $e->getMessage());
        }
        try {
            Database::insert('queue_jobs', [
                'queue'   => 'emails',
                'payload' => json_encode([
                    'type'      => 'artisan_lead_notify',
                    'artisan_id' => $artisan['id'],
                    'devis_id'  => $devisId,
                    'ref'       => $ref,
                    'ville'     => $ville,
                    'urgency'   => $urgency,
                ]),
            ]);
        } catch (Exception $e) {
            error_log('[QUEUE] ' . $e->getMessage());
        }
    }

    private function scheduleRelance(int $devisId, int $userId): void
    {
        try {
            Database::insert('relances', [
                'type'          => 'client_response',
                'entity_id'     => $devisId,
                'target_user_id' => $userId,
                'scheduled_at'  => date('Y-m-d H:i:s', strtotime('+24 hours')),
            ]);
        } catch (Exception $e) {
            error_log('[RELANCE] ' . $e->getMessage());
        }
    }

    private function getClientScore(string $email): ?array
    {
        return Database::fetch(
            'SELECT cs.* FROM client_score cs JOIN users u ON cs.user_id = u.id WHERE u.email = ?',
            [$email]
        );
    }

    private function incrementClientRequests(int $userId): void
    {
        Database::query(
            'UPDATE client_score SET total_requests = total_requests + 1 WHERE user_id = ?',
            [$userId]
        );
    }
}
