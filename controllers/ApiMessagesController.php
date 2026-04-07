<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class ApiMessagesController extends BaseController {

    public function send(): void {
        $session = $this->requireAuth();
        $body    = $this->jsonBody();
        $leadId  = (int)($body['lead_id'] ?? 0);
        $content = Security::sanitize($body['content'] ?? '');

        if (empty($content) || !$leadId) {
            $this->json(['error' => 'Données manquantes'], 400);
            return;
        }
        if (strlen($content) > 2000) {
            $this->json(['error' => 'Message trop long (2000 car. max)'], 400);
            return;
        }

        // Vérifier que l'utilisateur peut accéder à ce lead
        $lead = $this->getAccessibleLead($leadId, $session['user_id'], $session['user_role']);
        if (!$lead) {
            $this->json(['error' => 'Lead introuvable'], 404);
            return;
        }

        // Déterminer le destinataire
        $receiverId = $session['user_role'] === 'artisan' ? $lead['client_id'] : $lead['artisan_user_id'];

        $msgId = Database::insert('messages', [
            'lead_id'     => $leadId,
            'sender_id'   => $session['user_id'],
            'receiver_id' => $receiverId,
            'content'     => $content,
        ]);

        // Notifier
        Database::insert('notifications', [
            'user_id' => $receiverId,
            'type'    => 'new_message',
            'title'   => 'Nouveau message',
            'body'    => substr($content, 0, 80) . (strlen($content) > 80 ? '...' : ''),
            'data'    => json_encode(['lead_id' => $leadId, 'message_id' => $msgId]),
        ]);

        $this->log('message_sent', 'messages', $msgId, ['lead_id' => $leadId]);
        $this->json(['success' => true, 'message_id' => $msgId, 'sent_at' => date('Y-m-d H:i:s')]);
    }

    public function get(int $leadId): void {
        $session  = $this->requireAuth();
        $since    = $_GET['since'] ?? null;

        $lead = $this->getAccessibleLead($leadId, $session['user_id'], $session['user_role']);
        if (!$lead) {
            $this->json(['error' => 'Lead introuvable'], 404);
            return;
        }

        $params = [$leadId];
        $sql = 'SELECT m.*, u.first_name, u.last_name, u.role
                FROM messages m JOIN users u ON u.id = m.sender_id
                WHERE m.lead_id = ?';
        if ($since) {
            $sql .= ' AND m.created_at > ?';
            $params[] = $since;
        }
        $sql .= ' ORDER BY m.created_at ASC LIMIT 100';

        $messages = Database::fetchAll($sql, $params);

        // Marquer comme lus
        Database::query(
            'UPDATE messages SET is_read = 1 WHERE lead_id = ? AND receiver_id = ? AND is_read = 0',
            [$leadId, $session['user_id']]
        );

        $this->json(['messages' => $messages, 'lead' => $lead]);
    }

    private function getAccessibleLead(int $leadId, int $userId, string $role): ?array {
        if ($role === 'artisan') {
            $artisan = Database::fetch('SELECT id FROM artisans WHERE user_id = ?', [$userId]);
            if (!$artisan) return null;
            return Database::fetch(
                'SELECT l.*, d.client_id, a.user_id as artisan_user_id
                 FROM leads l JOIN devis d ON d.id=l.devis_id JOIN artisans a ON a.id=l.artisan_id
                 WHERE l.id = ? AND l.artisan_id = ? AND l.status = "accepted"',
                [$leadId, $artisan['id']]
            );
        }
        // Client
        return Database::fetch(
            'SELECT l.*, d.client_id, a.user_id as artisan_user_id
             FROM leads l JOIN devis d ON d.id=l.devis_id JOIN artisans a ON a.id=l.artisan_id
             WHERE l.id = ? AND d.client_id = ? AND l.status = "accepted"',
            [$leadId, $userId]
        );
    }
}
