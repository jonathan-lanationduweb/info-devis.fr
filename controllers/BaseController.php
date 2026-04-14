<?php

/**
 * Contrôleur de base
 * InfoDevis.fr
 */

class BaseController
{

    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);
        $viewFile   = BASE_PATH . '/views/' . $view . '.php';
        $layoutFile = BASE_PATH . '/views/layout/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            die('Vue introuvable : ' . $view);
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout && file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function redirect(string $path, int $code = 302): void
    {
        header('Location: ' . APP_URL . $path, true, $code);
        exit;
    }

    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function requireAuth(string $role = null): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) {
            if ($this->isApiRequest()) {
                $this->json(['error' => 'Non authentifié'], 401);
            }
            $this->redirect('/connexion');
        }
        if ($role && $_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/');
        }
        return $_SESSION;
    }

    protected function requireAdmin(): array
    {
        $session = $this->requireAuth();
        if ($session['user_role'] !== 'admin') {
            $this->redirect('/');
        }
        return $session;
    }

    protected function csrfCheck(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::verifyCsrf($token)) {
            Security::logSecurity('CSRF_FAIL', ['uri' => $_SERVER['REQUEST_URI']]);
            $this->json(['error' => 'Token invalide'], 403);
        }
    }

    protected function isApiRequest(): bool
    {
        return str_starts_with($_SERVER['REQUEST_URI'], '/api')
            || str_starts_with($_SERVER['REQUEST_URI'], APP_URL . '/api')
            || ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? [];
    }

    protected function log(string $action, string $entity = '', int $entityId = 0, array $data = []): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        try {
            Database::insert('activity_logs', [
                'user_id'    => $userId,
                'action'     => $action,
                'entity'     => $entity,
                'entity_id'  => $entityId,
                'ip_address' => Security::getIp(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                'data'       => json_encode($data),
            ]);
        } catch (\Exception $e) {
            error_log('[LOG ERROR] ' . $e->getMessage());
        }
    }

    protected function paginate(string $sql, array $params, int $page, int $perPage = 20): array
    {
        $countSql = preg_replace('/SELECT .+ FROM/is', 'SELECT COUNT(*) as total FROM', $sql);
        $total = (int)(Database::fetch($countSql, $params)['total'] ?? 0);
        $offset = ($page - 1) * $perPage;
        $items = Database::fetchAll($sql . " LIMIT {$perPage} OFFSET {$offset}", $params);
        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Upload un fichier dans uploads/{subfolder}/
     * Retourne le nom du fichier seul (pas le sous-dossier)
     * Les fichiers sont stockés dans BASE_PATH/uploads/{subfolder}/
     */
    protected function uploadFile(array $file, string $subfolder): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        $maxSize      = defined('UPLOAD_MAX_SIZE')      ? UPLOAD_MAX_SIZE      : 10 * 1024 * 1024;
        $allowedMimes = defined('UPLOAD_ALLOWED_MIME')  ? UPLOAD_ALLOWED_MIME  : ['application/pdf', 'image/jpeg', 'image/png'];

        if ($file['size'] > $maxSize) return null;

        $detectedMime = mime_content_type($file['tmp_name']);
        if (!in_array($detectedMime, $allowedMimes)) return null;

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        // Dossier de destination : BASE_PATH/uploads/{subfolder}/
        $destDir = BASE_PATH . '/uploads/' . $subfolder;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $dest = $destDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) return null;

        // Retourne seulement le nom du fichier
        return $filename;
    }
}
