<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class AdminController extends BaseController {

    public function index(): void {
        $this->requireAdmin();
        $metrics = $this->getMetrics();
        $this->view('admin/dashboard', [
            'pageTitle' => 'Administration | InfoDevis',
            'metrics'   => $metrics,
        ]);
    }

    public function artisans(): void {
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
            'SELECT COUNT(*) as c FROM artisans WHERE verification_status = ?', [$status]
        )['c'] ?? 0;

        $this->view('admin/artisans', [
            'pageTitle' => 'Gestion artisans | Admin',
            'artisans'  => $artisans,
            'status'    => $status,
            'total'     => $total,
            'page'      => $page,
            'pages'     => ceil($total / $perPage),
        ]);
    }

    public function validateArtisan(): void {
        $this->requireAdmin();
        $this->csrfCheck();

        $artisanId = (int)$this->input('artisan_id');
        $action    = $this->input('action'); // validate | refuse
        $note      = $this->input('note');

        $status   = $action === 'validate' ? 'validated' : 'refused';
        $verified = $action === 'validate' ? 1 : 0;

        Database::update('artisans', [
            'verification_status' => $status,
            'is_verified'         => $verified,
        ], ['id' => $artisanId]);

        // Notifier l'artisan
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
        }

        $this->log('artisan_' . $status, 'artisans', $artisanId, ['note' => $note]);
        $this->json(['success' => true, 'message' => 'Artisan ' . $status]);
    }

    public function devis(): void {
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

        $this->view('admin/devis', [
            'pageTitle' => 'Gestion devis | Admin',
            'devis'     => $devis,
            'page'      => $page,
        ]);
    }

    public function users(): void {
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

    public function blog(): void {
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

    public function validateBlog(): void {
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

    public function paiements(): void {
        $this->requireAdmin();
        $paiements = Database::fetchAll(
            'SELECT p.*, u.first_name, u.last_name, u.email FROM paiements p
             JOIN users u ON u.id = p.user_id
             ORDER BY p.created_at DESC LIMIT 100'
        );

        $this->view('admin/paiements', [
            'pageTitle' => 'Paiements | Admin',
            'paiements' => $paiements,
        ]);
    }

    public function abonnements(): void {
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

    public function chatbot(): void {
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

    public function metrics(): void {
        $this->requireAdmin();
        $metrics  = $this->getMetrics();
        $monthly  = Database::fetchAll(
            'SELECT * FROM business_metrics ORDER BY date DESC LIMIT 30'
        );

        $this->view('admin/metrics', [
            'pageTitle' => 'Métriques business | Admin',
            'metrics'   => $metrics,
            'monthly'   => $monthly,
        ]);
    }

    public function categories(): void {
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

    private function getMetrics(): array {
        $today = date('Y-m-d');
        $month = date('Y-m');

        return [
            'total_artisans'     => Database::fetch('SELECT COUNT(*) as c FROM artisans WHERE is_verified = 1')['c'] ?? 0,
            'pending_artisans'   => Database::fetch('SELECT COUNT(*) as c FROM artisans WHERE verification_status = "pending"')['c'] ?? 0,
            'total_clients'      => Database::fetch('SELECT COUNT(*) as c FROM users WHERE role = "client"')['c'] ?? 0,
            'total_devis'        => Database::fetch('SELECT COUNT(*) as c FROM devis')['c'] ?? 0,
            'devis_today'        => Database::fetch('SELECT COUNT(*) as c FROM devis WHERE DATE(created_at) = ?', [$today])['c'] ?? 0,
            'revenue_month'      => Database::fetch("SELECT COALESCE(SUM(amount),0) as s FROM paiements WHERE status='paid' AND DATE_FORMAT(created_at,'%Y-%m') = ?", [$month])['s'] ?? 0,
            'active_subs'        => Database::fetch("SELECT COUNT(*) as c FROM abonnements WHERE status='active'")['c'] ?? 0,
            'pending_blog'       => Database::fetch("SELECT COUNT(*) as c FROM blog_posts WHERE status='pending'")['c'] ?? 0,
        ];
    }
}
