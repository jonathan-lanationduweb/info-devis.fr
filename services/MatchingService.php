<?php
/**
 * Service de matching artisans
 * Critères : zone géographique, catégorie, disponibilité, score
 */

class MatchingService {

    public function findArtisans(int $categoryId, string $ville, string $codePostal, int $limit = 5): array {
        $departement = substr($codePostal, 0, 2);

        // Requête principale avec score de matching
        $sql = "
            SELECT DISTINCT
                a.id, a.user_id, a.company_name, a.ville, a.code_postal,
                a.plan, a.badge_verified, a.is_verified, a.matching_score,
                a.rating_avg, a.response_rate,
                u.first_name, u.last_name, u.email,
                CASE a.plan
                    WHEN 'illimite' THEN 4
                    WHEN 'pro'      THEN 3
                    WHEN 'starter'  THEN 2
                    ELSE 1
                END AS plan_score,
                (
                    COALESCE(a.rating_avg, 0) * 20 +
                    COALESCE(a.response_rate, 0) * 0.3 +
                    CASE a.plan
                        WHEN 'illimite' THEN 40
                        WHEN 'pro'      THEN 30
                        WHEN 'starter'  THEN 20
                        ELSE 0
                    END
                ) AS computed_score
            FROM artisans a
            JOIN users u ON u.id = a.user_id
            JOIN artisan_categories ac ON ac.artisan_id = a.id
            WHERE
                ac.category_id = ?
                AND a.is_active = 1
                AND a.is_verified = 1
                AND a.verification_status = 'validated'
                AND (
                    a.ville LIKE ?
                    OR a.code_postal LIKE ?
                    OR EXISTS (
                        SELECT 1 FROM artisan_zones az
                        JOIN zones z ON z.id = az.zone_id
                        WHERE az.artisan_id = a.id
                        AND (z.name LIKE ? OR z.name = ?)
                    )
                )
                AND NOT EXISTS (
                    SELECT 1 FROM availability av
                    WHERE av.artisan_id = a.id
                    AND av.date = CURDATE()
                    AND av.status = 'unavailable'
                )
                AND u.is_active = 1
            ORDER BY computed_score DESC, a.response_rate DESC
            LIMIT ?
        ";

        $params = [
            $categoryId,
            '%' . $ville . '%',
            $departement . '%',
            '%' . $ville . '%',
            $departement,
            $limit,
        ];

        return Database::fetchAll($sql, $params);
    }

    /**
     * Calcule et met à jour le score de matching d'un artisan
     */
    public function updateMatchingScore(int $artisanId): void {
        $stats = Database::fetch(
            'SELECT * FROM artisan_stats WHERE artisan_id = ?',
            [$artisanId]
        );
        $artisan = Database::fetch(
            'SELECT plan, badge_verified FROM artisans WHERE id = ?',
            [$artisanId]
        );

        if (!$stats || !$artisan) return;

        $planBonus = match($artisan['plan']) {
            'illimite' => 40,
            'pro'      => 30,
            'starter'  => 20,
            default    => 0,
        };

        $score =
            ($stats['rating_avg'] ?? 0) * 20 +
            ($stats['response_rate'] ?? 0) * 0.3 +
            $planBonus +
            ($artisan['badge_verified'] ? 10 : 0) +
            min(($stats['leads_accepted'] ?? 0) * 0.5, 20);

        Database::update('artisans', ['matching_score' => $score], ['id' => $artisanId]);
    }

    /**
     * Calcule la distance entre deux points GPS (formule Haversine)
     */
    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float {
        $R    = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
