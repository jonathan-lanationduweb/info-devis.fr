<?php
require_once BASE_PATH . '/controllers/BaseController.php';
require_once BASE_PATH . '/models/ArtisanModel.php';
require_once BASE_PATH . '/models/CategoryModel.php';
require_once BASE_PATH . '/services/MatchingService.php';

class DashboardArtisanController extends BaseController
{
    private ArtisanModel    $artisanModel;
    private MatchingService $matchingService;

    public function __construct()
    {
        $this->artisanModel    = new ArtisanModel();
        $this->matchingService = new MatchingService();
    }

    public function index(): void
    {
        $session = $this->requireAuth('artisan');
        $artisan = $this->artisanModel->findByUserId($session['user_id']);
        if (!$artisan) {
            $this->redirect('/');
            return;
        }
        $stats = $this->getDashboardStats($artisan['id']);
        $this->view('artisan/dashboard', [
            'pageTitle' => 'Mon tableau de bord | InfoDevis',
            'artisan'   => $artisan,
            'stats'     => $stats,
        ]);
    }

    public function leads(): void
    {
        $session = $this->requireAuth('artisan');
        $artisan = $this->artisanModel->findByUserId($session['user_id']);
        if (!$artisan) {
            $this->redirect('/');
            return;
        }
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $leads = $this->artisanModel->getPendingLeads($artisan['id'], $page);
        $this->view('artisan/leads', [
            'pageTitle' => 'Mes leads | InfoDevis',
            'artisan'   => $artisan,
            'leads'     => $leads,
            'page'      => $page,
        ]);
    }

    public function respondLead(): void
    {
        $session = $this->requireAuth('artisan');
        $this->csrfCheck();
        $artisan = $this->artisanModel->findByUserId($session['user_id']);

        $leadId = (int)$this->input('lead_id');
        $status = $this->input('status');
        $note   = $this->input('note');
        $price  = $this->input('price') ? (float)$this->input('price') : null;

        if (!in_array($status, ['accepted', 'refused'])) {
            $this->json(['error' => 'Statut invalide'], 400);
            return;
        }
        $ok = $this->artisanModel->respondToLead($leadId, $artisan['id'], $status, $note, $price);
        if (!$ok) {
            $this->json(['error' => 'Lead introuvable'], 404);
            return;
        }

        $lead = Database::fetch(
            'SELECT l.*, d.client_id, d.reference FROM leads l JOIN devis d ON d.id = l.devis_id WHERE l.id = ?',
            [$leadId]
        );
        if ($lead) {
            Database::insert('notifications', [
                'user_id' => $lead['client_id'],
                'type'    => 'lead_' . $status,
                'title'   => $status === 'accepted' ? 'Un artisan a accepté votre devis' : 'Un artisan a refusé votre devis',
                'body'    => 'Référence : ' . $lead['reference'],
                'data'    => json_encode(['lead_id' => $leadId]),
            ]);

            // ── Email client si artisan accepte ──────────────────────────
            if ($status === 'accepted') {
                try {
                    $devisInfo = Database::fetch(
                        'SELECT d.*, u.email, u.first_name, u.last_name
                         FROM devis d JOIN users u ON u.id = d.client_id WHERE d.id = ?',
                        [$lead['devis_id'] ?? 0]
                    );
                    if ($devisInfo && class_exists('MailService')) {
                        MailService::sendArtisanSelectionne(
                            $devisInfo['email'],
                            $devisInfo['first_name'] . ' ' . $devisInfo['last_name'],
                            $devisInfo['reference'] ?? 'DV-' . ($lead['devis_id'] ?? 0),
                            $artisan['first_name'] . ' ' . $artisan['last_name'],
                            $artisan['company_name'] ?? 'Artisan InfoDevis',
                            $artisan['ville'] ?? ''
                        );
                    }
                } catch (\Exception $e) {
                    error_log('[MAIL LEAD] ' . $e->getMessage());
                }
            }
        }

        $this->matchingService->updateMatchingScore($artisan['id']);
        $this->log('lead_responded', 'leads', $leadId, ['status' => $status]);

        if ($status === 'accepted' && $lead) {
            try {
                Database::insert('relances', [
                    'type'           => 'devis_signature',
                    'entity_id'      => $leadId,
                    'target_user_id' => $lead['client_id'] ?? 0,
                    'scheduled_at'   => date('Y-m-d H:i:s', strtotime('+48 hours')),
                ]);
            } catch (\Exception $e) {
                error_log('[RELANCE] ' . $e->getMessage());
            }
        }
        $this->json(['success' => true, 'message' => 'Réponse enregistrée.']);
    }

    public function messages(): void
    {
        $session = $this->requireAuth('artisan');
        $artisan = $this->artisanModel->findByUserId($session['user_id']);
        $conversations = Database::fetchAll(
            'SELECT DISTINCT l.id as lead_id, d.reference, d.title,
                    u.first_name, u.last_name,
                    (SELECT content FROM messages WHERE lead_id = l.id ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM messages WHERE lead_id = l.id ORDER BY created_at DESC LIMIT 1) as last_at,
                    (SELECT COUNT(*) FROM messages WHERE lead_id = l.id AND receiver_id = ? AND is_read = 0) as unread
             FROM leads l
             JOIN devis d ON d.id = l.devis_id
             JOIN users u ON u.id = d.client_id
             WHERE l.artisan_id = ? AND l.status = "accepted"
             ORDER BY last_at DESC',
            [$session['user_id'], $artisan['id']]
        );
        $this->view('artisan/messages', [
            'pageTitle'     => 'Messages | InfoDevis',
            'artisan'       => $artisan,
            'conversations' => $conversations,
        ]);
    }

    public function profile(): void
    {
        $session      = $this->requireAuth('artisan');
        $artisan      = $this->artisanModel->findByUserId($session['user_id']);
        $categories   = (new CategoryModel())->getMainCategories();
        $myCategories = $this->artisanModel->getCategories($artisan['id']);
        $myCatIds     = array_column($myCategories, 'id');
        $this->view('artisan/profile', [
            'pageTitle'    => 'Mon profil | InfoDevis',
            'artisan'      => $artisan,
            'categories'   => $categories,
            'myCategories' => $myCatIds,
        ]);
    }

    public function updateProfile(): void
    {
        $session = $this->requireAuth('artisan');
        $this->csrfCheck();
        $artisan = $this->artisanModel->findByUserId($session['user_id']);
        $data = [
            'company_name' => $this->input('company_name'),
            'description'  => $this->input('description'),
            'ville'        => $this->input('ville'),
            'code_postal'  => $this->input('code_postal'),
            'radius_km'    => (int)$this->input('radius_km', 30),
        ];
        $this->artisanModel->updateProfile($artisan['id'], $data);
        $cats = $_POST['categories'] ?? [];
        Database::query('DELETE FROM artisan_categories WHERE artisan_id = ?', [$artisan['id']]);
        foreach ($cats as $catId) {
            Database::insert('artisan_categories', ['artisan_id' => $artisan['id'], 'category_id' => (int)$catId]);
        }
        if (!empty($_FILES['avatar']['name'])) {
            $path = $this->uploadFile($_FILES['avatar'], 'profile');
            if ($path) Database::update('users', ['avatar' => 'profile/' . $path], ['id' => $session['user_id']]);
        }
        $this->log('profile_updated', 'artisans', $artisan['id']);
        $this->redirect('/dashboard/artisan/profile?saved=1');
    }

    public function stats(): void
    {
        $session = $this->requireAuth('artisan');
        $artisan = $this->artisanModel->findByUserId($session['user_id']);
        $stats   = $this->getDashboardStats($artisan['id']);
        $monthly = $this->getMonthlyStats($artisan['id']);
        $this->view('artisan/stats', [
            'pageTitle' => 'Mes statistiques | InfoDevis',
            'artisan'   => $artisan,
            'stats'     => $stats,
            'monthly'   => $monthly,
        ]);
    }

    // ── Calendrier ────────────────────────────────────────────────
    public function calendar(): void
    {
        $session = $this->requireAuth('artisan');
        $artisan = $this->artisanModel->findByUserId($session['user_id']);
        $month   = $_GET['month'] ?? date('Y-m');

        // Récupérer les disponibilités avec les notes
        $availability = Database::fetchAll(
            'SELECT date, status, note FROM availability WHERE artisan_id = ? AND DATE_FORMAT(date, "%Y-%m") = ?',
            [$artisan['id'], $month]
        );

        $this->view('artisan/calendar', [
            'pageTitle'    => 'Disponibilités | InfoDevis',
            'artisan'      => $artisan,
            'availability' => $availability,
            'month'        => $month,
        ]);
    }

    // ── Sauvegarde disponibilité (statut + note + description) ────
    public function saveAvailability(): void
    {
        $session = $this->requireAuth('artisan');
        $this->csrfCheck();
        $artisan = $this->artisanModel->findByUserId($session['user_id']);

        // ── Action : description générale du calendrier ──────────────
        if ($this->input('action') === 'save_description') {
            $desc = (string)($this->input('calendar_description') ?? '');
            try {
                Database::query('UPDATE artisans SET calendar_description=? WHERE id=?', [$desc, $artisan['id']]);
            } catch (\Exception $e) {
                // ALTER TABLE artisans ADD COLUMN calendar_description TEXT NULL;
                error_log('[CAL DESC] ' . $e->getMessage());
            }
            $this->json(['success' => true]);
            return;
        }

        // ── Action : sauvegarder un jour (statut + note optionnelle) ──
        $singleDate   = $this->input('date');
        $singleStatus = (string)($this->input('status') ?? '');
        $singleNote   = (string)($this->input('note') ?? '');

        $dates = $singleDate ? [$singleDate] : ($_POST['dates'] ?? []);

        foreach ($dates as $date) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) continue;

            $existing = Database::fetch(
                'SELECT id FROM availability WHERE artisan_id = ? AND date = ?',
                [$artisan['id'], $date]
            );

            if ($singleStatus === '') {
                // Effacer
                if ($existing) Database::query('DELETE FROM availability WHERE id = ?', [$existing['id']]);
            } elseif ($existing) {
                Database::update('availability', [
                    'status' => $singleStatus,
                    'note'   => $singleNote ?: null,
                ], ['id' => $existing['id']]);
            } else {
                Database::insert('availability', [
                    'artisan_id' => $artisan['id'],
                    'date'       => $date,
                    'status'     => $singleStatus,
                    'note'       => $singleNote ?: null,
                ]);
            }
        }
        $this->json(['success' => true]);
    }

    public function abonnement(): void
    {
        $session = $this->requireAuth('artisan');
        $artisan = $this->artisanModel->findByUserId($session['user_id']);
        $current = Database::fetch(
            'SELECT * FROM abonnements WHERE artisan_id = ? AND status = "active" ORDER BY started_at DESC LIMIT 1',
            [$artisan['id']]
        );
        $this->view('artisan/abonnement', [
            'pageTitle'  => 'Mon abonnement | InfoDevis',
            'artisan'    => $artisan,
            'current'    => $current,
            'plans'      => defined('PLAN_PRICES') ? PLAN_PRICES : [],
            'stripeKey'  => defined('STRIPE_PUBLIC_KEY') ? STRIPE_PUBLIC_KEY : '',
        ]);
    }

    public function documents(): void
    {
        $session = $this->requireAuth('artisan');
        $artisan = $this->artisanModel->findByUserId($session['user_id']);
        $docs    = Database::fetchAll(
            'SELECT * FROM documents WHERE artisan_id = ? ORDER BY uploaded_at DESC',
            [$artisan['id']]
        );
        $this->view('artisan/documents', [
            'pageTitle' => 'Mes documents | InfoDevis',
            'artisan'   => $artisan,
            'documents' => $docs,
        ]);
    }

    public function uploadDocument(): void
    {
        $session = $this->requireAuth('artisan');
        $this->csrfCheck();
        $artisan = $this->artisanModel->findByUserId($session['user_id']);

        if (empty($_FILES['document']['name'])) {
            $this->json(['error' => 'Aucun fichier'], 400);
            return;
        }
        $type    = $this->input('type', 'autre');
        $allowed = ['kbis', 'assurance', 'carte_identite', 'rib', 'autre'];
        if (!in_array($type, $allowed)) {
            $this->json(['error' => 'Type invalide'], 400);
            return;
        }

        $filename = $this->uploadFile($_FILES['document'], 'documents');
        if (!$filename) {
            $this->json(['error' => 'Erreur upload. Vérifiez le format (PDF, JPG, PNG) et la taille (max 10 Mo).'], 500);
            return;
        }

        $existing = Database::fetch(
            'SELECT id, filename FROM documents WHERE artisan_id = ? AND type = ?',
            [$artisan['id'], $type]
        );
        if ($existing) {
            $oldPath = BASE_PATH . '/storage/documents/' . ($existing['filename'] ?? '');
            if (!empty($existing['filename']) && file_exists($oldPath)) @unlink($oldPath);
            Database::query(
                'UPDATE documents SET filename=?, original_name=?, status="pending", uploaded_at=NOW(), note=NULL WHERE id=?',
                [$filename, $_FILES['document']['name'], $existing['id']]
            );
        } else {
            Database::insert('documents', [
                'artisan_id'    => $artisan['id'],
                'type'          => $type,
                'filename'      => $filename,
                'original_name' => $_FILES['document']['name'],
                'file_size'     => $_FILES['document']['size'],
                'mime_type'     => $_FILES['document']['type'],
                'status'        => 'pending',
                'uploaded_at'   => date('Y-m-d H:i:s'),
            ]);
        }
        $this->log('document_uploaded', 'documents', $artisan['id'], ['type' => $type]);
        $this->json(['success' => true, 'message' => 'Document envoyé. En attente de validation (24-48h).']);
    }

    // ── Helpers ────────────────────────────────────────────────────
    private function getDashboardStats(int $artisanId): array
    {
        $leads    = Database::fetch('SELECT COUNT(*) as c FROM leads WHERE artisan_id = ?', [$artisanId]);
        $accepted = Database::fetch('SELECT COUNT(*) as c FROM leads WHERE artisan_id = ? AND status = "accepted"', [$artisanId]);
        $pending  = Database::fetch('SELECT COUNT(*) as c FROM leads WHERE artisan_id = ? AND status = "pending"', [$artisanId]);
        $rating   = Database::fetch('SELECT AVG(rating) as avg, COUNT(*) as c FROM avis WHERE artisan_id = ?', [$artisanId]);
        $userId   = $_SESSION['user_id'] ?? 0;
        $unread   = Database::fetch(
            'SELECT COUNT(*) as c FROM messages m JOIN leads l ON l.id = m.lead_id WHERE l.artisan_id = ? AND m.is_read = 0 AND m.receiver_id != ?',
            [$artisanId, $userId]
        );
        return [
            'total_leads'     => (int)($leads['c']    ?? 0),
            'accepted_leads'  => (int)($accepted['c'] ?? 0),
            'pending_leads'   => (int)($pending['c']  ?? 0),
            'avg_rating'      => round((float)($rating['avg'] ?? 0), 1),
            'total_avis'      => (int)($rating['c']   ?? 0),
            'unread_messages' => (int)($unread['c']   ?? 0),
        ];
    }

    private function getMonthlyStats(int $artisanId): array
    {
        return Database::fetchAll(
            'SELECT DATE_FORMAT(created_at, "%Y-%m") as month,
                    COUNT(*) as leads,
                    SUM(CASE WHEN status="accepted" THEN 1 ELSE 0 END) as accepted
             FROM leads WHERE artisan_id = ?
             GROUP BY month ORDER BY month DESC LIMIT 12',
            [$artisanId]
        );
    }
}
