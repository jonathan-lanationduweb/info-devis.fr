<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class ChatbotController extends BaseController {

    public function handle(): void {
        if (!Security::checkRateLimit('chatbot_' . Security::getIp(), 30, 60)) {
            $this->json(['error' => 'Trop de requêtes. Attendez un instant.'], 429);
            return;
        }

        $body    = $this->jsonBody();
        $message = Security::sanitize($body['message'] ?? '');
        $session = $body['session_id'] ?? session_id();
        $step    = $body['step'] ?? 'initial';
        $userId  = $_SESSION['user_id'] ?? null;

        if (empty($message)) {
            $this->json(['error' => 'Message vide'], 400);
            return;
        }

        $response = $this->processMessage($message, $step, $body);

        // Sauvegarder conversation
        Database::insert('chatbot_messages', [
            'user_id'             => $userId,
            'session_id'          => $session,
            'message'             => $message,
            'response'            => json_encode($response),
            'detected_categories' => json_encode($response['categories'] ?? []),
            'intent'              => $response['intent'] ?? '',
        ]);

        $this->json($response);
    }

    private function processMessage(string $message, string $step, array $body): array {
        $msg = mb_strtolower($message);

        switch ($step) {
            case 'initial':
                return $this->handleInitial($msg, $message);
            case 'collect_info':
                return $this->handleCollectInfo($msg, $body);
            case 'confirm':
                return $this->handleConfirm($body);
            default:
                return $this->handleInitial($msg, $message);
        }
    }

    private function handleInitial(string $msg, string $original): array {
        // Détecter intention
        if ($this->matchesAny($msg, ['bonjour', 'salut', 'bonsoir', 'coucou', 'hello'])) {
            return [
                'intent'   => 'greeting',
                'type'     => 'text',
                'message'  => 'Bonjour ! Je suis l\'assistant InfoDevis. Comment puis-je vous aider ?',
                'quick_replies' => [
                    'Demander un devis',
                    'Trouver un artisan',
                    'Connaître les prix',
                    'Autre question',
                ],
            ];
        }

        if ($this->matchesAny($msg, ['prix', 'coût', 'combien', 'tarif', 'estimation'])) {
            return $this->handlePriceQuestion($msg);
        }

        if ($this->matchesAny($msg, ['devis', 'artisan', 'professionnel', 'travaux'])) {
            $categories = $this->detectCategories($msg);
            if (!empty($categories)) {
                return $this->buildCategorySelection($categories, $original);
            }
            return [
                'intent'  => 'devis_request',
                'type'    => 'text',
                'message' => 'Quel type de travaux souhaitez-vous réaliser ?',
                'quick_replies' => ['Plomberie', 'Électricité', 'Peinture', 'Toiture', 'Autre'],
            ];
        }

        // Détection automatique problème
        $categories = $this->detectCategories($msg);
        if (!empty($categories)) {
            return $this->buildCategorySelection($categories, $original);
        }

        // Réponse générique
        return [
            'intent'  => 'unknown',
            'type'    => 'text',
            'message' => 'Je peux vous aider à trouver un artisan ou obtenir un devis gratuit. Décrivez-moi votre problème ou besoin.',
            'quick_replies' => ['Demander un devis', 'Voir les catégories', 'Contact'],
        ];
    }

    private function detectCategories(string $msg): array {
        $keywords = Database::fetchAll(
            'SELECT pk.keyword, pk.category_id, pk.priority, c.name as category_name, c.slug
             FROM problem_keywords pk
             JOIN categories c ON c.id = pk.category_id
             ORDER BY pk.priority DESC'
        );

        $detected = [];
        foreach ($keywords as $kw) {
            if (str_contains($msg, mb_strtolower($kw['keyword']))) {
                $catId = $kw['category_id'];
                if (!isset($detected[$catId])) {
                    $detected[$catId] = [
                        'id'       => $catId,
                        'name'     => $kw['category_name'],
                        'slug'     => $kw['slug'],
                        'priority' => (int)$kw['priority'],
                        'keywords' => [],
                    ];
                }
                $detected[$catId]['keywords'][] = $kw['keyword'];
                $detected[$catId]['priority']   = max($detected[$catId]['priority'], (int)$kw['priority']);
            }
        }

        // Trier par priorité
        usort($detected, fn($a, $b) => $b['priority'] <=> $a['priority']);
        return array_values($detected);
    }

    private function buildCategorySelection(array $categories, string $original): array {
        $catNames = array_map(fn($c) => $c['name'], $categories);
        return [
            'intent'      => 'multi_category_detected',
            'type'        => 'category_selection',
            'message'     => 'Nous avons identifié plusieurs types de travaux possibles. Sélectionnez les catégories qui correspondent à votre besoin :',
            'categories'  => $categories,
            'original_msg'=> $original,
            'step'        => 'collect_info',
            'cta'         => 'Envoyer ma demande de devis',
        ];
    }

    private function handleCollectInfo(string $msg, array $body): array {
        $categories    = $body['selected_categories'] ?? [];
        $collectedData = $body['form_data'] ?? [];

        // Champs requis
        $required = ['first_name' => 'Votre prénom', 'email' => 'Votre email', 'ville' => 'Votre ville'];
        $missing  = [];
        foreach ($required as $field => $label) {
            if (empty($collectedData[$field])) $missing[] = $label;
        }

        if (!empty($missing)) {
            return [
                'intent'  => 'collect_form',
                'type'    => 'form',
                'message' => 'Pour vous mettre en relation avec des artisans, j\'ai besoin de quelques informations :',
                'fields'  => [
                    ['name' => 'first_name',  'label' => 'Prénom',       'type' => 'text',  'required' => true],
                    ['name' => 'phone',        'label' => 'Téléphone',    'type' => 'tel',   'required' => false],
                    ['name' => 'email',        'label' => 'Email',        'type' => 'email', 'required' => true],
                    ['name' => 'ville',        'label' => 'Ville',        'type' => 'text',  'required' => true],
                    ['name' => 'code_postal',  'label' => 'Code postal',  'type' => 'text',  'required' => false],
                    ['name' => 'description',  'label' => 'Décrivez votre projet', 'type' => 'textarea', 'required' => false],
                    ['name' => 'urgency',      'label' => 'Urgence',      'type' => 'select', 'options' => ['normal' => 'Normal', 'urgent' => 'Urgent', 'tres_urgent' => 'Très urgent']],
                ],
                'categories' => $categories,
                'step'       => 'confirm',
            ];
        }

        return $this->handleConfirm($body);
    }

    private function handleConfirm(array $body): array {
        $formData   = $body['form_data'] ?? [];
        $categories = $body['selected_categories'] ?? [];

        if (empty($formData['email']) || empty($categories)) {
            return ['intent' => 'error', 'type' => 'text', 'message' => 'Informations incomplètes. Recommençons.'];
        }

        // Valider email
        if (!Security::isValidEmail($formData['email'])) {
            return ['intent' => 'error', 'type' => 'text', 'message' => 'Email invalide. Veuillez le corriger.'];
        }

        // Préparer données pour le contrôleur devis
        return [
            'intent'     => 'submit_devis',
            'type'       => 'confirmation',
            'message'    => 'Parfait ! Votre demande va être envoyée à des artisans qualifiés dans votre zone.',
            'form_data'  => $formData,
            'categories' => $categories,
            'action'     => APP_URL . '/devis',
            'step'       => 'done',
        ];
    }

    private function handlePriceQuestion(string $msg): array {
        $prices = [
            'pompe à chaleur' => '6 000 € à 14 000 €',
            'piscine'         => '8 000 € à 40 000 €',
            'toiture'         => '5 000 € à 25 000 €',
            'électricité'     => '80 € à 8 000 €',
            'plomberie'       => '50 € à 5 000 €',
            'isolation'       => '1 000 € à 20 000 €',
            'panneaux solaires'=> '7 000 € à 18 000 €',
            'climatisation'   => '800 € à 6 000 €',
        ];

        foreach ($prices as $kw => $price) {
            if (str_contains($msg, $kw)) {
                return [
                    'intent'  => 'price_info',
                    'type'    => 'price',
                    'message' => "Le coût d'une installation **{$kw}** est généralement de **{$price}**. Ces prix varient selon votre région et la complexité du projet.",
                    'cta'     => 'Obtenir un devis précis gratuit',
                    'action'  => APP_URL . '/devis',
                ];
            }
        }

        return [
            'intent'  => 'price_general',
            'type'    => 'text',
            'message' => 'Les prix varient selon le type de travaux et votre région. Obtenez des devis gratuits et précis d\'artisans qualifiés !',
            'cta'     => 'Demander un devis gratuit',
            'action'  => APP_URL . '/devis',
        ];
    }

    private function matchesAny(string $text, array $keywords): bool {
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) return true;
        }
        return false;
    }
}
