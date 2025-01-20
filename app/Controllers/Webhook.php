<?php

namespace Controllers;

use Framework\Controller;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Framework\QueueManager;
use Framework\Enums\HTTPStatus;

class Webhook extends Controller
{

    public function __construct(HttpRequest $httpRequest, HttpResponse $httpResponse)
    {
        parent::__construct($httpRequest, $httpResponse);
    }    
    /**
     * Point d'entrée pour les webhooks
     * Endpoint: /webhook/receive
     */
    public function receive($params)
    {
        try {
            // Récupérer le payload brut
            $payload = file_get_contents('php://input');
            $data = json_decode($payload, true);
            
            // Validation basique du payload
            if (empty($data)) {
                throw new \Exception("Invalid webhook payload");
            }

            // Vérifier le secret si nécessaire
            $secret = $_ENV['WEBHOOK_SECRET'] ?? null;
            if ($secret) {
                $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
                if (!$this->validateSignature($payload, $signature, $secret)) {
                    throw new \Exception("Invalid signature");
                }
            }

            // Ajouter à la file d'attente pour traitement asynchrone
            $queueManager = QueueManager::instance();
            $queueManager->add('webhook_tasks', [
                'timestamp' => time(),
                'payload' => $data,
                'headers' => getallheaders()
            ]);

            // Répondre immédiatement au webhook
            $this->_httpResponse->setStatusCode(HTTPStatus::OK->value);
            $this->_httpResponse->setContent([
                'success' => true,
                'message' => 'Webhook received and queued'
            ]);
            
        } catch (\Throwable $e) {
            $this->_httpResponse->setStatusCode(HTTPStatus::BAD_REQUEST->value);
            $this->_httpResponse->setContent([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        $this->_httpResponse->headers->set("Content-Type", "application/json");
        return $this->_httpResponse;
    }

    /**
     * Valide la signature du webhook
     */
    private function validateSignature(string $payload, string $signature, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
}