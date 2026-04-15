<?php
require_once BASE_PATH . '/controllers/BaseController.php';
require_once BASE_PATH . '/models/DevisModel.php';

class DashboardClientController extends BaseController
{
    private DevisModel $devisModel;

    public function __construct()
    {
        $this->devisModel = new DevisModel();
    }

    public function index(): void
    {
        $session = $this->requireAuth('client');
        $devis   = $this->devisModel->getByClient($session['user_id']);
        $notifs  = $this->getNotifications($session['user_id']);

        $this->view('client/dashboard', [
            'pageTitle' => 'Mon espace | InfoDevis',
            'devis'     => $devis,
            'notifs'    => $notifs,
        ]);
    }

    public function devis(): void
    {
        $session = $this->requireAuth('client');
        $devis   = $this->devisModel->getByClient($session['user_id']);

        $this->view('client/devis', [
            'pageTitle' => 'Mes demandes | InfoDevis',
            'devis'     => $devis,
        ]);
    }

    public function messages(): void
    {
        $session = $this->requireAuth('client');

        $conversations = Database::fetchAll(
            'SELECT DISTINCT l.id as lead_id, d.reference, d.title,
                    a.company_name, u2.first_name, u2.last_name, a.badge_verified,
                    (SELECT content FROM messages WHERE lead_id = l.id ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM messages WHERE lead_id = l.id ORDER BY created_at DESC LIMIT 1) as last_at,
                    (SELECT COUNT(*) FROM messages WHERE lead_id = l.id AND receiver_id = ? AND is_read = 0) as unread
             FROM leads l
             JOIN devis d ON d.id = l.devis_id
             JOIN artisans a ON a.id = l.artisan_id
             JOIN users u2 ON u2.id = a.user_id
             WHERE d.client_id = ? AND l.status = "accepted"
             ORDER BY last_at DESC',
            [$session['user_id'], $session['user_id']]
        );

        $this->view('client/messages', [
            'pageTitle'     => 'Mes messages | InfoDevis',
            'conversations' => $conversations,
        ]);
    }

    public function avis(): void
    {
        $session = $this->requireAuth('client');
        $myAvis  = Database::fetchAll(
            'SELECT av.*, a.company_name, u.first_name, u.last_name, d.reference
             FROM avis av
             JOIN artisans a ON a.id = av.artisan_id
             JOIN users u ON u.id = a.user_id
             JOIN devis d ON d.id = av.devis_id
             WHERE av.client_id = ?
             ORDER BY av.created_at DESC',
            [$session['user_id']]
        );

        $pending = Database::fetchAll(
            'SELECT d.id, d.reference, a.company_name, l.artisan_id
             FROM devis d
             JOIN leads l ON l.devis_id = d.id AND l.status = "accepted"
             JOIN artisans a ON a.id = l.artisan_id
             WHERE d.client_id = ? AND d.status = "completed"
             AND NOT EXISTS (SELECT 1 FROM avis av WHERE av.devis_id = d.id AND av.client_id = ?)
             ORDER BY d.updated_at DESC',
            [$session['user_id'], $session['user_id']]
        );

        $this->view('client/avis', [
            'pageTitle' => 'Mes avis | InfoDevis',
            'myAvis'    => $myAvis,
            'pending'   => $pending,
        ]);
    }

    public function createAvis(): void
    {
        $session = $this->requireAuth('client');
        $this->csrfCheck();

        $devisId   = (int)$this->input('devis_id');
        $artisanId = (int)$this->input('artisan_id');
        $rating    = min(5, max(1, (int)$this->input('rating')));
        $comment   = $this->input('comment');

        if (!$this->devisModel->canLeaveReview($devisId, $session['user_id'])) {
            $this->json(['error' => 'Impossible de laisser un avis pour ce devis.'], 403);
            return;
        }

        Database::insert('avis', [
            'devis_id'   => $devisId,
            'client_id'  => $session['user_id'],
            'artisan_id' => $artisanId,
            'rating'     => $rating,
            'comment'    => $comment,
            'verified'   => 1,
        ]);

        $avg = Database::fetch(
            'SELECT AVG(rating) as avg, COUNT(*) as c FROM avis WHERE artisan_id = ? AND verified = 1',
            [$artisanId]
        );
        Database::update('artisans', [
            'rating_avg'   => round((float)($avg['avg'] ?? 0), 2),
            'rating_count' => (int)($avg['c'] ?? 0),
        ], ['id' => $artisanId]);

        $this->log('avis_created', 'avis', $devisId, ['artisan_id' => $artisanId, 'rating' => $rating]);
        $this->json(['success' => true, 'message' => 'Merci pour votre avis !']);
    }

    // ── Calendriers des artisans disponibles ──────────────────────
    public function calendrier(): void
    {
        $this->requireAuth('client');

        $catSlug = $_GET['cat'] ?? '';
        $month   = date('Y-m');

        // Requête artisans vérifiés avec filtrage catégorie optionnel
        if ($catSlug) {
            $artisans = Database::fetchAll(
                "SELECT DISTINCT a.id, a.company_name, a.ville, a.calendar_description,
                        u.first_name, u.last_name,
                        GROUP_CONCAT(DISTINCT cat.name ORDER BY cat.name SEPARATOR ', ') as categories
                 FROM artisans a
                 JOIN users u ON u.id = a.user_id
                 JOIN artisan_categories ac ON ac.artisan_id = a.id
                 JOIN categories cat ON cat.id = ac.category_id
                 JOIN artisan_categories acf ON acf.artisan_id = a.id
                 JOIN categories cf ON cf.id = acf.category_id AND cf.slug = ?
                 WHERE a.is_verified = 1
                 GROUP BY a.id
                 ORDER BY a.company_name ASC
                 LIMIT 20",
                [$catSlug]
            );
        } else {
            $artisans = Database::fetchAll(
                "SELECT DISTINCT a.id, a.company_name, a.ville, a.calendar_description,
                        u.first_name, u.last_name,
                        GROUP_CONCAT(DISTINCT cat.name ORDER BY cat.name SEPARATOR ', ') as categories
                 FROM artisans a
                 JOIN users u ON u.id = a.user_id
                 LEFT JOIN artisan_categories ac ON ac.artisan_id = a.id
                 LEFT JOIN categories cat ON cat.id = ac.category_id
                 WHERE a.is_verified = 1
                 GROUP BY a.id
                 ORDER BY a.company_name ASC
                 LIMIT 20"
            );
        }

        // Pour chaque artisan, récupérer ses disponibilités du mois courant
        foreach ($artisans as &$art) {
            $art['availability'] = Database::fetchAll(
                'SELECT date, status, note FROM availability
                 WHERE artisan_id = ? AND DATE_FORMAT(date, "%Y-%m") = ?',
                [$art['id'], $month]
            );
        }
        unset($art);

        $this->view('client/calendrier', [
            'pageTitle' => 'Calendriers artisans | InfoDevis',
            'artisans'  => $artisans,
            'month'     => $month,
        ]);
    }

    // ── Calendrier d'un artisan spécifique ────────────────────────
    public function calendrierArtisan(int $id = 0): void
    {
        $this->requireAuth('client');
        if (!$id) $id = (int)($_GET['id'] ?? 0);

        $artisan = Database::fetch(
            'SELECT a.*, u.first_name, u.last_name
             FROM artisans a JOIN users u ON u.id = a.user_id
             WHERE a.id = ? AND a.is_verified = 1',
            [$id]
        );
        if (!$artisan) {
            $this->redirect('/dashboard/client/calendrier');
            return;
        }

        $month = $_GET['month'] ?? date('Y-m');

        $availability = Database::fetchAll(
            'SELECT date, status, note FROM availability
             WHERE artisan_id = ? AND DATE_FORMAT(date, "%Y-%m") = ?',
            [$id, $month]
        );

        $categories = Database::fetchAll(
            'SELECT cat.name FROM artisan_categories ac
             JOIN categories cat ON cat.id = ac.category_id
             WHERE ac.artisan_id = ?',
            [$id]
        );

        $this->view('client/calendrier_artisan', [
            'pageTitle'    => 'Calendrier — ' . ($artisan['company_name'] ?? ''),
            'artisan'      => $artisan,
            'availability' => $availability,
            'categories'   => $categories,
            'month'        => $month,
        ]);
    }

    public function signature(int $devisId): void
    {
        $session = $this->requireAuth('client');
        $devis   = $this->devisModel->getById($devisId);

        if (!$devis || $devis['client_id'] !== $session['user_id']) {
            $this->redirect('/dashboard/client/devis');
            return;
        }

        $lead = Database::fetch(
            'SELECT l.*, a.company_name FROM leads l JOIN artisans a ON a.id = l.artisan_id
             WHERE l.devis_id = ? AND l.status = "accepted" LIMIT 1',
            [$devisId]
        );

        $this->view('client/signature', [
            'pageTitle' => 'Signer le devis | InfoDevis',
            'devis'     => $devis,
            'lead'      => $lead,
        ]);
    }

    public function sign(): void
    {
        $session = $this->requireAuth('client');
        $this->csrfCheck();

        $devisId       = (int)$this->input('devis_id');
        $signatureData = $this->input('signature_data');

        if (empty($signatureData)) {
            $this->json(['error' => 'Signature manquante'], 400);
            return;
        }

        $devis = $this->devisModel->getById($devisId);
        if (!$devis || $devis['client_id'] !== $session['user_id']) {
            $this->json(['error' => 'Devis introuvable'], 404);
            return;
        }

        $docHash = hash('sha256', $devisId . $session['user_id'] . $signatureData . time());

        Database::insert('signatures', [
            'devis_id'       => $devisId,
            'user_id'        => $session['user_id'],
            'signature_data' => $signatureData,
            'ip_address'     => Security::getIp(),
            'user_agent'     => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            'document_hash'  => $docHash,
        ]);

        $this->devisModel->updateStatus($devisId, 'in_progress');
        $this->log('devis_signed', 'devis', $devisId);

        $this->json(['success' => true, 'message' => 'Devis signé avec succès !', 'hash' => $docHash]);
    }

    public function paiement(int $devisId): void
    {
        $session = $this->requireAuth('client');
        $devis   = $this->devisModel->getById($devisId);

        if (!$devis || $devis['client_id'] !== $session['user_id']) {
            $this->redirect('/dashboard/client/devis');
            return;
        }

        $this->view('client/paiement', [
            'pageTitle'  => 'Paiement acompte | InfoDevis',
            'devis'      => $devis,
            'stripeKey'  => STRIPE_PUBLIC_KEY,
        ]);
    }

    private function getNotifications(int $userId): array
    {
        $notifs = Database::fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10',
            [$userId]
        );
        Database::query('UPDATE notifications SET is_read = 1 WHERE user_id = ?', [$userId]);
        return $notifs;
    }
}
