<?php
/**
 * Service anti-doublon demandes de devis
 */

class AntiDuplicateService {

    public function isDuplicate(string $hash): bool {
        $hours  = ANTIDUPLICATE_HOURS;
        $result = Database::fetch(
            "SELECT id FROM devis
             WHERE anti_duplicate_hash = ?
             AND created_at > DATE_SUB(NOW(), INTERVAL {$hours} HOUR)
             LIMIT 1",
            [$hash]
        );
        return $result !== null;
    }
}
