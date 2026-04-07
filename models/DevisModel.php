<?php
class DevisModel {

    public function getByClient(int $clientId): array {
        return Database::fetchAll(
            'SELECT d.*, c.name as category_name,
                    COUNT(DISTINCT l.id) as leads_count,
                    SUM(CASE WHEN l.status="accepted" THEN 1 ELSE 0 END) as accepted_count
             FROM devis d
             JOIN categories c ON c.id = d.category_id
             LEFT JOIN leads l ON l.devis_id = d.id
             WHERE d.client_id = ?
             GROUP BY d.id
             ORDER BY d.created_at DESC',
            [$clientId]
        );
    }

    public function getById(int $id): ?array {
        return Database::fetch(
            'SELECT d.*, c.name as category_name, u.first_name, u.last_name, u.email, u.phone
             FROM devis d
             JOIN categories c ON c.id = d.category_id
             JOIN users u ON u.id = d.client_id
             WHERE d.id = ?',
            [$id]
        );
    }

    public function getByReference(string $ref): ?array {
        return Database::fetch('SELECT * FROM devis WHERE reference = ?', [$ref]);
    }

    public function getLeads(int $devisId): array {
        return Database::fetchAll(
            'SELECT l.*, a.company_name, a.rating_avg, a.badge_verified,
                    u.first_name, u.last_name, u.phone, u.email
             FROM leads l
             JOIN artisans a ON a.id = l.artisan_id
             JOIN users u ON u.id = a.user_id
             WHERE l.devis_id = ?
             ORDER BY l.status ASC, l.created_at DESC',
            [$devisId]
        );
    }

    public function updateStatus(int $id, string $status): void {
        Database::update('devis', ['status' => $status], ['id' => $id]);
    }

    public function canLeaveReview(int $devisId, int $clientId): bool {
        $devis = Database::fetch(
            'SELECT id FROM devis WHERE id = ? AND client_id = ? AND status = "completed"',
            [$devisId, $clientId]
        );
        if (!$devis) return false;
        $existing = Database::fetch(
            'SELECT id FROM avis WHERE devis_id = ? AND client_id = ?',
            [$devisId, $clientId]
        );
        return !$existing;
    }
}
