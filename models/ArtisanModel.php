<?php
class ArtisanModel {

    public function findByUserId(int $userId): ?array {
        return Database::fetch(
            'SELECT a.*, u.first_name, u.last_name, u.email, u.phone
             FROM artisans a JOIN users u ON u.id = a.user_id
             WHERE a.user_id = ?',
            [$userId]
        );
    }

    public function getTopArtisans(int $limit = 6): array {
        return Database::fetchAll(
            'SELECT a.*, u.first_name, u.last_name,
                    GROUP_CONCAT(DISTINCT c.name SEPARATOR ", ") as categories
             FROM artisans a
             JOIN users u ON u.id = a.user_id
             LEFT JOIN artisan_categories ac ON ac.artisan_id = a.id
             LEFT JOIN categories c ON c.id = ac.category_id
             WHERE a.is_verified = 1 AND a.verification_status = "validated"
             GROUP BY a.id
             ORDER BY a.matching_score DESC, a.rating_avg DESC
             LIMIT ?',
            [$limit]
        );
    }

    public function getWithStats(int $artisanId): ?array {
        return Database::fetch(
            'SELECT a.*, u.first_name, u.last_name, u.email, u.phone, u.avatar,
                    ast.rating_avg, ast.response_rate, ast.leads_accepted, ast.leads_refused
             FROM artisans a
             JOIN users u ON u.id = a.user_id
             LEFT JOIN artisan_stats ast ON ast.artisan_id = a.id
             WHERE a.id = ?',
            [$artisanId]
        );
    }

    public function updateProfile(int $artisanId, array $data): void {
        Database::update('artisans', $data, ['id' => $artisanId]);
    }

    public function getCategories(int $artisanId): array {
        return Database::fetchAll(
            'SELECT c.* FROM categories c
             JOIN artisan_categories ac ON ac.category_id = c.id
             WHERE ac.artisan_id = ?',
            [$artisanId]
        );
    }

    public function getPendingLeads(int $artisanId, int $page = 1): array {
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;
        return Database::fetchAll(
            'SELECT l.*, d.reference, d.title, d.description, d.ville, d.urgency,
                    d.created_at as devis_date, c.name as category_name,
                    u.first_name, u.last_name
             FROM leads l
             JOIN devis d ON d.id = l.devis_id
             JOIN categories c ON c.id = l.category_id
             JOIN users u ON u.id = d.client_id
             WHERE l.artisan_id = ?
             ORDER BY l.created_at DESC
             LIMIT ? OFFSET ?',
            [$artisanId, $perPage, $offset]
        );
    }

    public function respondToLead(int $leadId, int $artisanId, string $status, ?string $note = null, ?float $price = null): bool {
        $rows = Database::update('leads', [
            'status'       => $status,
            'note'         => $note,
            'price_offered'=> $price,
            'responded_at' => date('Y-m-d H:i:s'),
        ], ['id' => $leadId, 'artisan_id' => $artisanId]);

        if ($rows > 0) {
            // Mettre à jour stats
            $col = $status === 'accepted' ? 'leads_accepted' : 'leads_refused';
            Database::query(
                "UPDATE artisan_stats SET {$col} = {$col} + 1 WHERE artisan_id = ?",
                [$artisanId]
            );
            return true;
        }
        return false;
    }
}
