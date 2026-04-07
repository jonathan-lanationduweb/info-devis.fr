#!/usr/bin/env php
<?php
/**
 * Worker file d'attente (Queue)
 * Exécution : php /path/to/info-devis/queue_worker.php
 * Cron recommandé : * * * * * php /var/www/info-devis/queue_worker.php >> /var/log/infodevis-queue.log 2>&1
 */

define('ROOT', __DIR__);
require_once ROOT . '/config/app.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/config/security.php';
require_once ROOT . '/services/MailService.php';

$maxJobs     = 50;
$processed   = 0;
$startTime   = microtime(true);

echo '[' . date('Y-m-d H:i:s') . '] Worker démarré' . PHP_EOL;

while ($processed < $maxJobs) {
    // Prendre le prochain job disponible
    $job = Database::fetch(
        "SELECT * FROM queue_jobs
         WHERE (reserved_at IS NULL OR reserved_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE))
         AND available_at <= NOW()
         AND attempts < 3
         ORDER BY available_at ASC LIMIT 1 FOR UPDATE"
    );

    if (!$job) break;

    // Réserver le job
    Database::update('queue_jobs', [
        'reserved_at' => date('Y-m-d H:i:s'),
        'attempts'    => $job['attempts'] + 1,
    ], ['id' => $job['id']]);

    try {
        $payload = json_decode($job['payload'], true);
        processJob($payload);

        // Supprimer si succès
        Database::query('DELETE FROM queue_jobs WHERE id = ?', [$job['id']]);
        echo '[' . date('H:i:s') . '] ✅ Job traité: ' . ($payload['type'] ?? 'unknown') . PHP_EOL;

    } catch (Exception $e) {
        // Remettre en queue si < 3 tentatives
        Database::update('queue_jobs', ['reserved_at' => null], ['id' => $job['id']]);
        error_log('[QUEUE ERROR] Job ' . $job['id'] . ': ' . $e->getMessage());
        echo '[' . date('H:i:s') . '] ❌ Erreur job ' . $job['id'] . ': ' . $e->getMessage() . PHP_EOL;
    }

    $processed++;
}

$elapsed = round((microtime(true) - $startTime) * 1000);
echo '[' . date('H:i:s') . '] Worker terminé. ' . $processed . ' jobs en ' . $elapsed . 'ms' . PHP_EOL;

function processJob(array $payload): void {
    switch ($payload['type'] ?? '') {
        case 'artisan_lead_notify':
            $artisan = Database::fetch(
                'SELECT a.*, u.email, u.first_name FROM artisans a JOIN users u ON u.id=a.user_id WHERE a.id=?',
                [$payload['artisan_id']]
            );
            if ($artisan) {
                MailService::sendLeadNotification($artisan['email'], $artisan['first_name'], [
                    'ref'     => $payload['ref'],
                    'ville'   => $payload['ville'],
                    'urgency' => $payload['urgency'],
                ]);
            }
            break;

        case 'avis_request':
            $user = Database::fetch('SELECT * FROM users WHERE id = ?', [$payload['user_id']]);
            if ($user) {
                MailService::sendAvisRequest($user['email'], $user['first_name'], $payload['devis_id']);
            }
            break;

        case 'relance':
            processRelance($payload);
            break;

        default:
            throw new Exception('Type de job inconnu: ' . ($payload['type'] ?? 'null'));
    }
}

function processRelance(array $payload): void {
    $relanceId = $payload['relance_id'] ?? 0;
    $relance   = Database::fetch('SELECT * FROM relances WHERE id = ? AND status = "pending"', [$relanceId]);
    if (!$relance) return;

    $user = Database::fetch('SELECT * FROM users WHERE id = ?', [$relance['target_user_id']]);
    if (!$user) return;

    switch ($relance['type']) {
        case 'artisan_lead':
            // Relance artisan sur lead non répondu
            $lead = Database::fetch('SELECT l.*,d.reference FROM leads l JOIN devis d ON d.id=l.devis_id WHERE l.id=?', [$relance['entity_id']]);
            if ($lead && $lead['status'] === 'pending') {
                Database::insert('notifications', [
                    'user_id' => $user['id'],
                    'type'    => 'relance_lead',
                    'title'   => '⏰ Rappel : lead en attente de réponse',
                    'body'    => 'Le lead ' . $lead['reference'] . ' attend votre réponse depuis 2h.',
                ]);
            }
            break;

        case 'client_response':
            $devis = Database::fetch('SELECT * FROM devis WHERE id = ?', [$relance['entity_id']]);
            if ($devis) {
                $leads_count = Database::fetch('SELECT COUNT(*) as c FROM leads WHERE devis_id = ?', [$relance['entity_id']]);
                if (($leads_count['c'] ?? 0) === 0) {
                    // Aucun artisan : relancer
                    Database::insert('notifications', [
                        'user_id' => $user['id'],
                        'type'    => 'relance_client',
                        'title'   => '📬 Aucun artisan pour l\'instant',
                        'body'    => 'Votre demande ' . $devis['reference'] . ' est en cours de traitement.',
                    ]);
                }
            }
            break;
    }

    Database::update('relances', ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')], ['id' => $relanceId]);
}
