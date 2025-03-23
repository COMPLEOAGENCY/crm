<?php
namespace Controllers\Api;

use Framework\QueueManager;
use Framework\HttpResponse;
use Services\ChatService;
use Models\Project;

/**
 * Contrôleur API pour l'intégration du chat avec N8N
 * 
 * Ce contrôleur fournit les endpoints nécessaires pour l'intégration
 * entre le système de chat du CRM et N8N pour la qualification automatique
 * des projets via une IA.
 */
class AIChatApiController extends ApiV2Controller
{
    /** @var ChatService Instance du service de chat */
    private $chatService;
    
    /** @var QueueManager Gestionnaire de file d'attente */
    private $queueManager;
    
    /**
     * Constructeur du contrôleur
     */
    public function __construct()
    {
        parent::__construct();
        $this->chatService = ChatService::instance();
        $this->queueManager = QueueManager::instance();
    }
    
    /**
     * Vérifie la clé API fournie par N8N
     * 
     * @return bool
     */
    private function verifyApiKey()
    {
        $headers = getallheaders();
        $apiKey = $headers['X-Api-Key'] ?? '';
        
        // Récupérer la clé API depuis la configuration
        $validApiKey = getenv('N8N_API_KEY');
        
        return !empty($validApiKey) && $apiKey === $validApiKey;
    }
    
    /**
     * Renvoie une réponse non autorisée
     * 
     * @return array
     */
    private function respondUnauthorized()
    {
        $this->_httpResponse->setStatusCode(HttpResponse::HTTP_UNAUTHORIZED);
        return [
            'success' => false,
            'message' => 'Non autorisé'
        ];
    }
    
    /**
     * Renvoie une réponse avec une erreur de requête
     * 
     * @param array $data
     * @return array
     */
    private function respondBadRequest($data = [])
    {
        $this->_httpResponse->setStatusCode(HttpResponse::HTTP_BAD_REQUEST);
        return array_merge([
            'success' => false,
            'message' => 'Requête incorrecte'
        ], $data);
    }
    
    /**
     * Renvoie une réponse réussie
     * 
     * @param array $data
     * @return array
     */
    private function respondOK($data = [])
    {
        $this->_httpResponse->setStatusCode(HttpResponse::HTTP_OK);
        return array_merge([
            'success' => true,
            'message' => 'Succès'
        ], $data);
    }
    
    /**
     * Récupère les données de la requête
     * 
     * @return array
     */
    private function getRequestPayload()
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?: [];
    }
    
    /**
     * Endpoint pour recevoir les messages des utilisateurs via N8N
     * N8N a déjà intercepté ces messages depuis WhatsApp, SMS, etc.
     * 
     * @return array
     */
    public function storeUserMessage()
    {
        // Vérifier l'authentification
        if (!$this->verifyApiKey()) {
            return $this->respondUnauthorized();
        }
        
        $payload = $this->getRequestPayload();
        
        // Vérifier les données requises
        if (empty($payload['project_id']) || empty($payload['sender_id']) || empty($payload['content'])) {
            return $this->respondBadRequest(['error' => 'Champs requis manquants']);
        }
        
        // Créer ou récupérer la conversation
        $conversationSlug = "project-{$payload['project_id']}-main";
        $conversation = $this->chatService->getConversationBySlug($conversationSlug);
        
        if (!$conversation) {
            // Créer une nouvelle conversation
            $this->chatService->createConversation(
                'project',
                $payload['project_id'],
                "Conversation principale du projet #{$payload['project_id']}",
                [
                    ['type' => 'user', 'id' => $payload['sender_id']]
                ]
            );
            $conversation = $this->chatService->getConversationBySlug($conversationSlug);
        }
        
        // Ajouter le message à la conversation
        $message = new \Models\Chat\ChatMessage();
        $this->chatService->sendMessage(
            $conversation->chatConversationId,
            'user',
            $payload['sender_id'],
            $payload['content'],
            'all',
            null,
            $payload['attachments'] ?? []
        );
        
        // IMPORTANT: Ici, nous ne déclenchons pas de notification externe
        // car c'est N8N qui gère l'envoi des messages aux utilisateurs
        
        return $this->respondOK([
            'status' => 'success', 
            'conversation_id' => $conversation->chatConversationId,
            'message_id' => $message->chatMessageId ?? 0
        ]);
    }
    
    /**
     * Endpoint pour que N8N récupère les messages à analyser
     * 
     * @return array
     */
    public function getMessagesToAnalyze()
    {
        // Vérifier l'authentification
        if (!$this->verifyApiKey()) {
            return $this->respondUnauthorized();
        }
        
        // Récupérer les messages en file d'attente
        // Comme nous n'avons pas accès à une méthode 'get' du QueueManager,
        // nous allons récupérer les messages non traités directement depuis la base de données
        $messageModel = new \Models\Chat\ChatMessage();
        
        // Utiliser la méthode getAll disponible dans la classe Model
        $recentMessages = $messageModel->getAll(10);
        
        // Filtrer les messages non traités envoyés par les utilisateurs
        $recentMessages = array_filter($recentMessages, function($message) {
            return $message->isProcessed == 0 && $message->senderType == 'user';
        });
        
        $tasksWithContext = [];
        foreach ($recentMessages as $message) {
            // Récupérer la conversation
            $conversation = $this->chatService->getConversationById($message->chatConversationId);
            if (!$conversation || $conversation->contextType !== 'project') continue;
            
            // Vérifier si c'est une conversation principale (avec le slug project-X-main)
            $slug = $conversation->slug;
            if (!preg_match('/^project-(\d+)-main$/', $slug)) continue;
            
            // Récupérer l'historique des messages
            $messages = $this->chatService->getMessages($conversation->chatConversationId, 50);
            
            // Récupérer les données du projet
            $project = new Project();
            $projectData = $project->get($conversation->contextId);
            
            $tasksWithContext[] = [
                'task_id' => uniqid('task_'),
                'message_id' => $message->chatMessageId,
                'conversation_id' => $conversation->chatConversationId,
                'project_id' => $conversation->contextId,
                'project_data' => $projectData ? $this->formatProjectData($projectData) : null,
                'message_history' => $this->formatMessageHistory($messages),
                'sender_type' => $message->senderType,
                'sender_id' => $message->senderId,
                'content' => $message->content
            ];
            
            // Marquer le message comme étant en cours de traitement
            $message->isProcessed = 1;
            $message->save();
        }
        
        return $this->respondOK(['tasks' => $tasksWithContext]);
    }
    
    /**
     * Endpoint pour recevoir les réponses de l'IA via N8N
     * N8N est responsable d'envoyer ces réponses aux utilisateurs via les canaux appropriés
     * 
     * @return array
     */
    public function storeAIResponse()
    {
        // Vérifier l'authentification
        if (!$this->verifyApiKey()) {
            return $this->respondUnauthorized();
        }
        
        $payload = $this->getRequestPayload();
        
        // Vérifier les données requises
        if (empty($payload['conversation_id']) || empty($payload['content'])) {
            return $this->respondBadRequest(['error' => 'Champs requis manquants']);
        }
        
        // Ajouter la réponse de l'IA à la conversation
        $this->chatService->sendMessage(
            $payload['conversation_id'],
            'system',
            0,
            $payload['content']
        );
        
        // Si des mises à jour de projet sont fournies, les appliquer
        if (!empty($payload['project_updates']) && !empty($payload['project_id'])) {
            $project = new Project();
            $projectObj = $project->get($payload['project_id']);
            
            if ($projectObj) {
                // Mettre à jour les champs du projet
                foreach ($payload['project_updates'] as $field => $value) {
                    if (property_exists($projectObj, $field)) {
                        $projectObj->$field = $value;
                    }
                }
                $projectObj->save();
            }
        }
        
        // IMPORTANT: N8N est responsable d'envoyer ce message à l'utilisateur
        // via le canal approprié (WhatsApp, SMS, etc.)
        
        return $this->respondOK(['status' => 'success']);
    }
    
    /**
     * Formate les données du projet pour l'API
     * 
     * @param Project $project
     * @return array
     */
    private function formatProjectData($project)
    {
        // Retourne les données pertinentes du projet
        return [
            'id' => $project->projectid,
            'title' => $project->title ?? '',
            'description' => $project->description ?? '',
            'status' => $project->status ?? '',
            // Ajouter d'autres champs pertinents...
        ];
    }
    
    /**
     * Formate l'historique des messages pour l'API
     * 
     * @param array $messages
     * @return array
     */
    private function formatMessageHistory($messages)
    {
        $formatted = [];
        foreach ($messages as $message) {
            $formatted[] = [
                'id' => $message->chatMessageId,
                'sender_type' => $message->senderType,
                'sender_id' => $message->senderId,
                'content' => $message->content,
                'timestamp' => $message->timestamp,
                'is_read' => (bool)$message->isRead
            ];
        }
        return $formatted;
    }
}
