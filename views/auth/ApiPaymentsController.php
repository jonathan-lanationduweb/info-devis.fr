<?php
/**
 * ApiPaymentsController.php
 * Gestion des paiements Stripe
 */
require_once BASE_PATH . '/controllers/BaseController.php';

class ApiPaymentsController extends BaseController {

    // ── Montants des plans en centimes (EUR) ──────────────────
    private const PLAN_AMOUNTS = [
        'starter'       => 4900,   // 49,00 €
        'pro'           => 9900,   // 99,00 €
        'illimite'      => 19900,  // 199,00 €
        'lead_unitaire' => 990,    // 9,90 €
    ];

    private const PLAN_LABELS = [
        'starter'       => 'Abonnement Starter — InfoDevis',
        'pro'           => 'Abonnement Pro — InfoDevis',
        'illimite'      => 'Abonnement Illimité — InfoDevis',
        'lead_unitaire' => 'Lead à l\'unité — InfoDevis',
    ];

    public function createIntent(): void {
        $session = $this->requireAuth('artisan');

        $plan   = (string)($this->input('plan') ?? '');
        $amount = self::PLAN_AMOUNTS[$plan] ?? null;

        if (!$amount) {
            $this->json(['error' => 'Plan invalide : '.$plan], 400);
            return;
        }

        if (!defined('STRIPE_SECRET_KEY') || empty(STRIPE_SECRET_KEY)) {
            // Mode local : simuler un client_secret
            if ($this->isLocal()) {
                $this->json([
                    'client_secret' => 'pi_local_test_'.bin2hex(random_bytes(8)).'_secret_local',
                    'amount'        => $amount,
                    'plan'          => $plan,
                ]);
                return;
            }
            $this->json(['error' => 'Stripe non configuré.'], 500);
            return;
        }

        // Appel API Stripe
        $payload = http_build_query([
            'amount'   => $amount,
            'currency' => 'eur',
            'metadata[plan]'       => $plan,
            'metadata[artisan_id]' => $session['user_id'],
            'description'          => self::PLAN_LABELS[$plan] ?? 'InfoDevis',
        ]);

        $ch = curl_init('https://api.stripe.com/v1/payment_intents');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer '.STRIPE_SECRET_KEY,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('[STRIPE ERROR] HTTP '.$httpCode.' — '.$response);
            $this->json(['error' => 'Erreur Stripe. Réessayez.'], 500);
            return;
        }

        $data = json_decode($response, true);

        if (empty($data['client_secret'])) {
            error_log('[STRIPE ERROR] No client_secret: '.$response);
            $this->json(['error' => 'Erreur de paiement. Réessayez.'], 500);
            return;
        }

        $this->json([
            'client_secret' => $data['client_secret'],
            'amount'        => $amount,
            'plan'          => $plan,
        ]);
    }

    public function webhook(): void {
        $payload   = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        if (!defined('STRIPE_WEBHOOK_SECRET') || empty(STRIPE_WEBHOOK_SECRET)) {
            http_response_code(400);
            exit('Webhook secret manquant');
        }

        // Vérification signature Stripe
        $event = null;
        try {
            $parts     = explode(',', $sigHeader);
            $timestamp = null;
            $signatures = [];
            foreach ($parts as $part) {
                if (str_starts_with($part, 't=')) $timestamp = substr($part, 2);
                if (str_starts_with($part, 'v1=')) $signatures[] = substr($part, 3);
            }
            $signedPayload = $timestamp.'.'.$payload;
            $expectedSig   = hash_hmac('sha256', $signedPayload, STRIPE_WEBHOOK_SECRET);
            if (!in_array($expectedSig, $signatures)) {
                http_response_code(400); exit('Signature invalide');
            }
            $event = json_decode($payload, true);
        } catch (Exception $e) {
            http_response_code(400); exit($e->getMessage());
        }

        // Traitement des événements
        if ($event['type'] === 'payment_intent.succeeded') {
            $pi     = $event['data']['object'];
            $plan   = $pi['metadata']['plan'] ?? '';
            $userId = (int)($pi['metadata']['artisan_id'] ?? 0);

            if ($userId && $plan) {
                $artisan = Database::fetch('SELECT id FROM artisans WHERE user_id = ?', [$userId]);
                if ($artisan) {
                    // Créer/renouveler l'abonnement
                    Database::query(
                        'UPDATE abonnements SET status = "cancelled" WHERE artisan_id = ? AND status = "active"',
                        [$artisan['id']]
                    );

                    $endsAt = $plan === 'lead_unitaire' ? null : date('Y-m-d H:i:s', strtotime('+1 month'));

                    if ($plan !== 'lead_unitaire') {
                        Database::insert('abonnements', [
                            'artisan_id' => $artisan['id'],
                            'plan'       => $plan,
                            'status'     => 'active',
                            'started_at' => date('Y-m-d H:i:s'),
                            'ends_at'    => $endsAt,
                            'stripe_pi'  => $pi['id'],
                        ]);
                        Database::update('artisans', ['plan' => $plan], ['id' => $artisan['id']]);
                    }

                    // Enregistrer paiement
                    Database::insert('paiements', [
                        'user_id'    => $userId,
                        'amount'     => $pi['amount'] / 100,
                        'currency'   => $pi['currency'],
                        'status'     => 'paid',
                        'type'       => $plan,
                        'stripe_pi'  => $pi['id'],
                    ]);

                    // Notifier l'artisan
                    Database::insert('notifications', [
                        'user_id' => $userId,
                        'type'    => 'payment_success',
                        'title'   => '✅ Paiement confirmé',
                        'body'    => 'Votre plan '.ucfirst($plan).' est maintenant actif.',
                    ]);
                }
            }
        }

        http_response_code(200);
        echo json_encode(['received' => true]);
    }

    private function isLocal(): bool {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return in_array($host, ['localhost','127.0.0.1','localhost:80','localhost:8080'])
            || (defined('APP_ENV') && APP_ENV === 'local');
    }
}
