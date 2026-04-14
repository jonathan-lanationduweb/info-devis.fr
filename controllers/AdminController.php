<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class AdminController extends BaseController
{

    public function index(): void
    {
        $this->requireAdmin();
        $metrics = $this->getMetrics();
        $this->view('admin/dashboard', [
            'pageTitle' => 'Administration | InfoDevis',
            'metrics'   => $metrics,
        ]);
    }

    public function artisans(): void
    {
        $this->requireAdmin();
        $status  = $_GET['status'] ?? 'pending';
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $artisans = Database::fetchAll(
            'SELECT a.*, u.first_name, u.last_name, u.email, u.phone, u.created_at as reg_date
             FROM artisans a JOIN users u ON u.id = a.user_id
             WHERE a.verification_status = ?
             ORDER BY a.created_at DESC LIMIT ? OFFSET ?',
            [$status, $perPage, $offset]
        );
        $total = Database::fetch(
            'SELECT COUNT(*) as c FROM artisans WHERE verification_status = ?',
            [$status]
        )['c'] ?? 0;

        $this->view('admin/artisans', [
            'pageTitle' => 'Gestion artisans | Admin',
            'artisans'  => $artisans,
            'status'    => $status,
            'total'     => $total,
            'page'      => $page,
            'pages'     => (int)ceil($total / $perPage),
        ]);
    }

    // ── Dossier détail artisan ────────────────────────────────────────────
    public function artisanDetail(int $id = 0): void
    {
        $this->requireAdmin();
        if (!$id) $id = (int)($_GET['id'] ?? 0);

        $artisan = Database::fetch(
            'SELECT a.*, u.first_name, u.last_name, u.email, u.phone, u.created_at as reg_date
             FROM artisans a JOIN users u ON u.id = a.user_id WHERE a.id = ?',
            [$id]
        );
        if (!$artisan) $this->redirect('/admin/artisans');

        $documents = Database::fetchAll(
            'SELECT * FROM documents WHERE artisan_id = ? ORDER BY uploaded_at DESC',
            [$id]
        );

        $this->view('admin/artisan_detail', [
            'pageTitle' => 'Dossier — ' . ($artisan['company_name'] ?? ''),
            'artisan'   => $artisan,
            'documents' => $documents,
        ]);
    }

    // ── Voir un document (PDF/image) ──────────────────────────────────────
    public function documentView(int $id = 0): void
    {
        $this->requireAdmin();
        if (!$id) $id = (int)($_GET['id'] ?? 0);

        $doc = Database::fetch('SELECT * FROM documents WHERE id = ?', [$id]);
        if (!$doc) {
            http_response_code(404);
            exit('Document introuvable');
        }

        // Chercher le fichier dans uploads/documents/
        $fileName = $doc['file_path'] ?? $doc['filename'] ?? '';
        // Nettoyer : enlever tout préfixe "documents/" éventuel
        $fileName = ltrim(str_replace('documents/', '', $fileName), '/');

        if (empty($fileName)) {
            http_response_code(404);
            exit('Nom de fichier manquant');
        }

        $filePath = BASE_PATH . '/uploads/documents/' . $fileName;

        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('Fichier introuvable : ' . $filePath);
        }

        $ext  = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf'         => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            default       => 'application/octet-stream',
        };
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    // ── Valider/refuser un document individuel ────────────────────────────
    public function documentValidate(): void
    {
        $this->requireAdmin();
        $this->csrfCheck();

        $docId  = (int)($this->input('document_id') ?? 0);
        $status = (string)($this->input('status') ?? '');
        $note   = (string)($this->input('note') ?? '');

        if (!in_array($status, ['validated', 'refused', 'pending'])) {
            $this->json(['error' => 'Statut invalide'], 400);
            return;
        }
        $doc = Database::fetch('SELECT * FROM documents WHERE id = ?', [$docId]);
        if (!$doc) {
            $this->json(['error' => 'Document introuvable'], 404);
            return;
        }

        Database::query(
            'UPDATE documents SET status=?, note=?, validated_at=NOW() WHERE id=?',
            [$status, $note, $docId]
        );

        $artisan = Database::fetch(
            'SELECT a.user_id FROM artisans a JOIN documents d ON d.artisan_id=a.id WHERE d.id=?',
            [$docId]
        );
        if ($artisan && $status !== 'pending') {
            Database::insert('notifications', [
                'user_id' => $artisan['user_id'],
                'type'    => 'document_' . $status,
                'title'   => $status === 'validated' ? '✅ Document validé' : '❌ Document refusé',
                'body'    => $status === 'validated' ? 'Votre document a été validé.' : 'Votre document a été refusé. Motif : ' . ($note ?: 'Non précisé'),
            ]);
        }
        $this->json(['success' => true]);
    }

    // ── Note admin sur un artisan ─────────────────────────────────────────
    public function artisanNote(): void
    {
        $this->requireAdmin();
        $this->csrfCheck();

        $artisanId = (int)($this->input('artisan_id') ?? 0);
        $note      = (string)($this->input('note') ?? '');

        try {
            Database::query('UPDATE artisans SET admin_note=? WHERE id=?', [$note, $artisanId]);
        } catch (\Exception $e) {
            error_log('[ADMIN NOTE] ' . $e->getMessage());
        }
        $this->json(['success' => true]);
    }

    // ── Sauvegarder une catégorie (ajout/modif) ───────────────────────────
    public function saveCategory(): void
    {
        $this->requireAdmin();
        $this->csrfCheck();

        $id        = (int)($this->input('id') ?? 0);
        $name      = (string)($this->input('name') ?? '');
        $slug      = (string)($this->input('slug') ?? '');
        $icon      = (string)($this->input('icon') ?? 'home_repair_service');
        $sortOrder = (int)($this->input('sort_order') ?? 0);

        if (empty($name)) {
            $this->json(['error' => 'Nom requis'], 400);
            return;
        }
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        }

        if ($id > 0) {
            Database::query(
                'UPDATE categories SET name=?, slug=?, icon=?, sort_order=? WHERE id=?',
                [$name, $slug, $icon, $sortOrder, $id]
            );
        } else {
            Database::insert('categories', [
                'name'       => $name,
                'slug'       => $slug,
                'icon'       => $icon,
                'sort_order' => $sortOrder,
            ]);
        }
        $this->json(['success' => true]);
    }

    public function validateArtisan(): void
    {
        $this->requireAdmin();
        $this->csrfCheck();

        $artisanId = (int)$this->input('artisan_id');
        $action    = $this->input('action'); // validate | refuse | cancel
        $note      = (string)($this->input('note') ?? '');

        // Action "cancel" = remettre en pending
        if ($action === 'cancel') {
            Database::update('artisans', [
                'verification_status' => 'pending',
                'is_verified'         => 0,
            ], ['id' => $artisanId]);
            $this->json(['success' => true, 'message' => 'Artisan remis en attente']);
            return;
        }

        $status   = $action === 'validate' ? 'validated' : 'refused';
        $verified = $action === 'validate' ? 1 : 0;

        Database::update('artisans', [
            'verification_status' => $status,
            'is_verified'         => $verified,
        ], ['id' => $artisanId]);

        $artisan = Database::fetch(
            'SELECT a.*, u.email, u.first_name FROM artisans a JOIN users u ON u.id = a.user_id WHERE a.id = ?',
            [$artisanId]
        );

        if ($artisan) {
            Database::insert('notifications', [
                'user_id' => $artisan['user_id'],
                'type'    => 'account_' . $status,
                'title'   => $action === 'validate' ? '✅ Compte validé' : '❌ Compte refusé',
                'body'    => $action === 'validate'
                    ? 'Félicitations ! Votre compte artisan a été validé. Vous pouvez maintenant recevoir des leads.'
                    : 'Votre compte n\'a pas pu être validé. Motif : ' . ($note ?: 'Non précisé'),
            ]);

            // Envoyer email à l'artisan
            try {
                require_once BASE_PATH . '/services/MailService.php';
                if ($action === 'validate') {
                    MailService::sendArtisanValidated($artisan['email'], $artisan['first_name'], $artisan['company_name'] ?? '');
                } else {
                    MailService::sendArtisanRefused($artisan['email'], $artisan['first_name'], $note);
                }
            } catch (\Exception $e) {
                error_log('[MAIL] ' . $e->getMessage());
            }
        }

        $this->log('artisan_' . $status, 'artisans', $artisanId, ['note' => $note]);
        $this->json(['success' => true, 'message' => 'Artisan ' . $status]);
    }

    public function devis(): void
    {
        $this->requireAdmin();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;
        $status  = $_GET['status'] ?? '';

        $where  = $status ? 'WHERE d.status = ?' : '';
        $params = $status ? [$status, $perPage, $offset] : [$perPage, $offset];

        $devis = Database::fetchAll(
            "SELECT d.*, c.name as cat_name, u.first_name, u.last_name, u.email,
                    COUNT(l.id) as leads_count
             FROM devis d
             JOIN categories c ON c.id = d.category_id
             JOIN users u ON u.id = d.client_id
             LEFT JOIN leads l ON l.devis_id = d.id
             {$where}
             GROUP BY d.id
             ORDER BY d.created_at DESC LIMIT ? OFFSET ?",
            $params
        );

        $total      = Database::fetch('SELECT COUNT(*) as c FROM devis' . ($status ? ' WHERE status=?' : ''), $status ? [$status] : [])['c'] ?? 0;
        $today      = date('Y-m-d');
        $devisToday = Database::fetch('SELECT COUNT(*) as c FROM devis WHERE DATE(created_at)=?', [$today])['c'] ?? 0;

        $this->view('admin/devis', [
            'pageTitle'  => 'Gestion devis | Admin',
            'devis'      => $devis,
            'page'       => $page,
            'totalDevis' => $total,
            'devisToday' => $devisToday,
        ]);
    }

    public function users(): void
    {
        $this->requireAdmin();
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;
        $role    = $_GET['role'] ?? '';

        $where  = $role ? 'WHERE role = ?' : '';
        $params = $role ? [$role, $perPage, $offset] : [$perPage, $offset];

        $users = Database::fetchAll(
            "SELECT * FROM users {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            $params
        );

        $this->view('admin/users', [
            'pageTitle' => 'Gestion utilisateurs | Admin',
            'users'     => $users,
            'page'      => $page,
        ]);
    }

    public function blog(): void
    {
        $this->requireAdmin();
        $status = $_GET['status'] ?? 'pending';
        $posts  = Database::fetchAll(
            'SELECT bp.*, u.first_name, u.last_name FROM blog_posts bp
             JOIN users u ON u.id = bp.author_id
             WHERE bp.status = ? ORDER BY bp.created_at DESC LIMIT 50',
            [$status]
        );
        $this->view('admin/blog', [
            'pageTitle' => 'Gestion blog | Admin',
            'posts'     => $posts,
            'status'    => $status,
        ]);
    }

    public function validateBlog(): void
    {
        $this->requireAdmin();
        $this->csrfCheck();

        $postId = (int)$this->input('post_id');
        $action = $this->input('action');
        $status = $action === 'publish' ? 'published' : 'rejected';

        Database::update('blog_posts', [
            'status'       => $status,
            'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
        ], ['id' => $postId]);

        $this->log('blog_' . $status, 'blog_posts', $postId);
        $this->json(['success' => true]);
    }

    public function paiements(): void
    {
        $this->requireAdmin();
        $paiements = Database::fetchAll(
            'SELECT p.*, u.first_name, u.last_name, u.email FROM paiements p
             JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC LIMIT 100'
        );
        $this->view('admin/paiements', [
            'pageTitle' => 'Paiements | Admin',
            'paiements' => $paiements,
        ]);
    }

    public function abonnements(): void
    {
        $this->requireAdmin();
        $abonnements = Database::fetchAll(
            'SELECT ab.*, u.first_name, u.last_name, u.email, a.company_name
             FROM abonnements ab
             JOIN artisans a ON a.id = ab.artisan_id
             JOIN users u ON u.id = a.user_id
             ORDER BY ab.started_at DESC LIMIT 100'
        );
        $this->view('admin/abonnements', [
            'pageTitle'   => 'Abonnements | Admin',
            'abonnements' => $abonnements,
        ]);
    }

    public function chatbot(): void
    {
        $this->requireAdmin();
        $messages = Database::fetchAll(
            'SELECT cm.*, u.email FROM chatbot_messages cm
             LEFT JOIN users u ON u.id = cm.user_id
             ORDER BY cm.created_at DESC LIMIT 200'
        );
        $this->view('admin/chatbot', [
            'pageTitle' => 'Conversations chatbot | Admin',
            'messages'  => $messages,
        ]);
    }

    public function metrics(): void
    {
        $this->requireAdmin();
        $metrics = $this->getMetrics();
        $monthly = Database::fetchAll('SELECT * FROM business_metrics ORDER BY date DESC LIMIT 30');
        $this->view('admin/metrics', [
            'pageTitle' => 'Métriques business | Admin',
            'metrics'   => $metrics,
            'monthly'   => $monthly,
        ]);
    }

    public function categories(): void
    {
        $this->requireAdmin();
        $categories = Database::fetchAll(
            'SELECT c.*, COUNT(s.id) as services_count
             FROM categories c LEFT JOIN services s ON s.categorie_id = c.id
             GROUP BY c.id ORDER BY c.sort_order ASC, c.name ASC'
        );
        $this->view('admin/categories', [
            'pageTitle'  => 'Gestion catégories | Admin',
            'categories' => $categories,
        ]);
    }

    private function getMetrics(): array
    {
        $today = date('Y-m-d');
        $month = date('Y-m');
        return [
            'total_artisans'   => Database::fetch('SELECT COUNT(*) as c FROM artisans WHERE is_verified = 1')['c'] ?? 0,
            'pending_artisans' => Database::fetch('SELECT COUNT(*) as c FROM artisans WHERE verification_status = "pending"')['c'] ?? 0,
            'total_clients'    => Database::fetch('SELECT COUNT(*) as c FROM users WHERE role = "client"')['c'] ?? 0,
            'total_devis'      => Database::fetch('SELECT COUNT(*) as c FROM devis')['c'] ?? 0,
            'devis_today'      => Database::fetch('SELECT COUNT(*) as c FROM devis WHERE DATE(created_at) = ?', [$today])['c'] ?? 0,
            'revenue_month'    => Database::fetch("SELECT COALESCE(SUM(amount),0) as s FROM paiements WHERE status='paid' AND DATE_FORMAT(created_at,'%Y-%m') = ?", [$month])['s'] ?? 0,
            'active_subs'      => Database::fetch("SELECT COUNT(*) as c FROM abonnements WHERE status='active'")['c'] ?? 0,
            'pending_blog'     => Database::fetch("SELECT COUNT(*) as c FROM blog_posts WHERE status='pending'")['c'] ?? 0,
        ];
    }
}
