<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class ApiPaymentsController extends BaseController {

    public function createIntent(): void {
        $session = $this->requireAuth();
        $body    = $this->jsonBody();
        $type    = $body['type'] ?? 'lead';    // lead | abonnement | acompte
        $amount  = (float)($body['amount'] ?? 0);
        $devisId = (int)($body['devis_id'] ?? 0);

        if ($amount <= 0) { $this->json(['error' => 'Montant invalide'], 400); return; }

        // Stripe API call
        $ch = curl_init('https://api.stripe.com/v1/payment_intents');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ':',
            CURLOPT_POSTFIELDS     => http_build_query([
                'amount'   => (int)($amount * 100), // centimes
                'currency' => 'eur',
                'metadata' => ['user_id' => $session['user_id'], 'type' => $type, 'devis_id' => $devisId],
                'description' => 'InfoDevis — ' . ucfirst($type),
            ]),
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        if ($httpCode !== 200 || empty($data['client_secret'])) {
            error_log('[STRIPE ERROR] ' . $response);
            $this->json(['error' => 'Erreur de paiement'], 500);
            return;
        }

        // Enregistrer en base
        Database::insert('paiements', [
            'user_id'              => $session['user_id'],
            'type'                 => $type,
            'amount'               => $amount,
            'stripe_payment_intent'=> $data['id'],
            'status'               => 'pending',
            'description'          => 'InfoDevis — ' . ucfirst($type),
        ]);

        $this->json(['client_secret' => $data['client_secret'], 'payment_intent_id' => $data['id']]);
    }

    public function webhook(): void {
        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        // Vérification signature webhook
        if (!$this->verifyStripeSignature($payload, $sigHeader)) {
            http_response_code(400);
            die('Signature invalide');
        }

        $event = json_decode($payload, true);
        switch ($event['type'] ?? '') {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($event['data']['object']);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event['data']['object']);
                break;
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $this->handleSubscription($event['data']['object']);
                break;
        }

        http_response_code(200);
        echo 'OK';
    }

    private function handlePaymentSuccess(array $pi): void {
        $intentId = $pi['id'];
        Database::update('paiements', ['status' => 'paid'], ['stripe_payment_intent' => $intentId]);

        $meta = $pi['metadata'] ?? [];
        if (!empty($meta['devis_id']) && ($meta['type'] ?? '') === 'acompte') {
            Database::update('deposits', ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')],
                ['stripe_payment_intent' => $intentId]);
        }

        $this->log('payment_success', 'paiements', 0, ['intent' => $intentId]);
    }

    private function handlePaymentFailed(array $pi): void {
        Database::update('paiements', ['status' => 'failed'], ['stripe_payment_intent' => $pi['id']]);
        $this->log('payment_failed', 'paiements', 0, ['intent' => $pi['id']]);
    }

    private function handleSubscription(array $sub): void {
        // Mettre à jour le plan artisan selon l'abonnement Stripe
        $customerId = $sub['customer'];
        $artisan = Database::fetch('SELECT id FROM artisans WHERE plan != "gratuit" LIMIT 1');
        if ($artisan) {
            Database::update('abonnements', [
                'status'     => $sub['status'] === 'active' ? 'active' : 'cancelled',
                'expires_at' => date('Y-m-d H:i:s', $sub['current_period_end']),
            ], ['stripe_subscription_id' => $sub['id']]);
        }
    }

    private function verifyStripeSignature(string $payload, string $sigHeader): bool {
        if (empty(STRIPE_WEBHOOK_SEC) || str_starts_with(STRIPE_WEBHOOK_SEC, 'whsec_VOTRE')) return true; // dev
        $parts = explode(',', $sigHeader);
        $ts = $v1 = '';
        foreach ($parts as $p) {
            if (str_starts_with($p, 't='))  $ts = substr($p, 2);
            if (str_starts_with($p, 'v1=')) $v1 = substr($p, 3);
        }
        $expected = hash_hmac('sha256', "{$ts}.{$payload}", STRIPE_WEBHOOK_SEC);
        return hash_equals($expected, $v1);
    }
}
