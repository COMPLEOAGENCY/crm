<?php
namespace Services;

use Framework\QueueManager;
use Models\Chat\ChatConversation;
use Models\Chat\ChatMessage;
use Models\Chat\ChatParticipant;
use Models\Chat\ChatNotification;
use Models\Chat\ChatAttachment;

/**
 * Service pour gérer les fonctionnalités du chat
 * 
 * Ce service centralise toute la logique métier liée au système de chat,
 * y compris la gestion des conversations, messages, participants et notifications.
 * Il sert également d'interface entre le système de chat et n8n/agent AI via Redis.
 */
class ChatService
{
    /** @var ChatService Instance unique du service (pattern Singleton) */
    private static $instance = null;
    
    /** @var QueueManager Gestionnaire de file d'attente Redis */
    private $queueManager;
    
    /** @var ChatConversation Modèle de conversation */
    private $conversation;
    
    /** @var ChatMessage Modèle de message */
    private $message;
    
    /** @var ChatParticipant Modèle de participant */
    private $participant;
    
    /** @var ChatNotification Modèle de notification */
    private $notification;
    
    /** @var ChatAttachment Modèle de pièce jointe */
    private $attachment;
    
    /**
     * Constructeur privé (pattern Singleton)
     */
    private function __construct()
    {
        $this->queueManager = QueueManager::instance();
        
        // Initialiser les modèles une seule fois
        $this->conversation = new ChatConversation();
        $this->message = new ChatMessage();
        $this->participant = new ChatParticipant();
        $this->notification = new ChatNotification();
        $this->attachment = new ChatAttachment();
    }
    
    /**
     * Récupère l'instance unique du service
     * 
     * @return ChatService
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Crée une nouvelle conversation
     * 
     * @param string $contextType Type de contexte (lead, project, user, custom)
     * @param int $contextId ID du contexte
     * @param string $title Titre de la conversation (optionnel)
     * @param array $participants Liste des participants initiaux
     * @return $this
     */
    public function createConversation($contextType, $contextId, $title = null, $participants = [])
    {
        // Créer la conversation
        $conversation = clone $this->conversation;
        $conversation->contextType = $contextType;
        $conversation->contextId = $contextId;
        $conversation->title = $title;
        $conversation->timestamp = time();
        $conversation->timestampUpdate = time();
        $conversation->status = 'active';
        
        // Générer un slug unique
        $conversation->slug = $conversation->generateSlug();
        
        // Sauvegarder la conversation
        $conversation->save();
        
        // Ajouter les participants
        foreach ($participants as $participant) {
            $this->addParticipant(
                $conversation->chatConversationId,
                $participant['type'],
                $participant['id']
            );
        }
        
        return $this;
    }
    
    /**
     * Récupère une conversation par son slug
     * 
     * @param string $slug Slug de la conversation
     * @return ChatConversation|null
     */
    public function getConversationBySlug($slug)
    {
        return $this->conversation->getBySlug($slug);
    }
    
    /**
     * Récupère une conversation par son ID
     * 
     * @param int $conversationId ID de la conversation
     * @return ChatConversation|null
     */
    public function getConversationById($conversationId)
    {
        $conversation = clone $this->conversation;
        return $conversation->get($conversationId) ? $conversation : null;
    }
    
    /**
     * Récupère les conversations d'un participant
     * 
     * @param string $participantType Type de participant (user, lead, professional)
     * @param int $participantId ID du participant
     * @return array
     */
    public function getConversationsForParticipant($participantType, $participantId)
    {
        $participations = $this->participant->getAll([
            'where' => 'participant_type = :type AND participant_id = :id AND status = "active"',
            'params' => [
                ':type' => $participantType,
                ':id' => $participantId
            ]
        ]);
        
        $conversations = [];
        foreach ($participations as $participation) {
            $conversation = clone $this->conversation;
            if ($conversation->get($participation->chatConversationId)) {
                $conversations[] = $conversation;
            }
        }
        
        return $conversations;
    }
    
    /**
     * Met à jour le statut d'une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param string $status Nouveau statut (active, closed, archived)
     * @return $this
     */
    public function updateConversationStatus($conversationId, $status)
    {
        $conversation = clone $this->conversation;
        if ($conversation->get($conversationId)) {
            $conversation->status = $status;
            $conversation->timestampUpdate = time();
            $conversation->save();
        }
        return $this;
    }
    
    /**
     * Met à jour le titre d'une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param string $title Nouveau titre
     * @return $this
     */
    public function updateConversationTitle($conversationId, $title)
    {
        $conversation = clone $this->conversation;
        if ($conversation->get($conversationId)) {
            $conversation->title = $title;
            $conversation->timestampUpdate = time();
            $conversation->save();
        }
        return $this;
    }
    
    /**
     * Ajoute un participant à une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param string $participantType Type de participant (user, lead, professional)
     * @param int $participantId ID du participant
     * @return $this
     */
    public function addParticipant($conversationId, $participantType, $participantId)
    {
        // Vérifier si le participant existe déjà
        $existing = $this->participant->getAll([
            'where' => 'chat_conversationid = :conversationId AND participant_type = :type AND participant_id = :id',
            'params' => [
                ':conversationId' => $conversationId,
                ':type' => $participantType,
                ':id' => $participantId
            ],
            'limit' => 1
        ]);
        
        if (!empty($existing)) {
            // Si le participant existe mais est inactif, le réactiver
            $participant = $existing[0];
            if ($participant->status !== 'active') {
                $participant->status = 'active';
                $participant->save();
            }
        } else {
            // Créer un nouveau participant
            $participant = clone $this->participant;
            $participant->chatConversationId = $conversationId;
            $participant->participantType = $participantType;
            $participant->participantId = $participantId;
            $participant->joinTimestamp = time();
            $participant->status = 'active';
            $participant->notificationPreference = 'all';
            $participant->save();
        }
        
        // Créer une notification pour les autres participants
        $this->notifyParticipantJoined($conversationId, $participant->chatParticipantId);
        
        return $this;
    }
    
    /**
     * Envoie un message dans une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param string $senderType Type d'expéditeur (user, lead, professional)
     * @param int $senderId ID de l'expéditeur
     * @param string $content Contenu du message
     * @param string $recipientType Type de destinataire (optionnel)
     * @param int $recipientId ID du destinataire (optionnel)
     * @param array $attachments Pièces jointes (optionnel)
     * @return $this
     */
    public function sendMessage($conversationId, $senderType, $senderId, $content, $recipientType = 'all', $recipientId = null, $attachments = [])
    {
        // Vérifier si l'expéditeur est participant à la conversation
        if ($senderType !== 'system' && !$this->isParticipant($conversationId, $senderType, $senderId)) {
            return $this;
        }
        
        // Créer le message
        $message = clone $this->message;
        $message->chatConversationId = $conversationId;
        $message->senderType = $senderType;
        $message->senderId = $senderId;
        $message->recipientType = $recipientType;
        $message->recipientId = $recipientId;
        $message->content = $content;
        $message->timestamp = time();
        $message->deliveryStatus = 'sent';
        $message->save();
        
        // Mettre à jour le timestamp de la conversation
        $conversation = clone $this->conversation;
        if ($conversation->get($conversationId)) {
            $conversation->timestampUpdate = time();
            $conversation->save();
        }
        
        // Ajouter les pièces jointes
        foreach ($attachments as $attachment) {
            $this->addAttachment($message->chatMessageId, $attachment);
        }
        
        // Mettre le message en file d'attente pour traitement asynchrone
        $this->queueManager->add('chat_messages', [
            'message_id' => $message->chatMessageId,
            'timestamp' => time()
        ]);
        
        // Créer des notifications pour les participants
        $this->notifyNewMessage($conversationId, $message->chatMessageId);
        
        return $this;
    }
    
    /**
     * Ajoute une pièce jointe à un message
     * 
     * @param int $messageId ID du message
     * @param array $fileData Données du fichier
     * @return $this
     */
    private function addAttachment($messageId, $fileData)
    {
        $attachment = clone $this->attachment;
        $attachment->chatMessageId = $messageId;
        $attachment->fileName = $fileData['name'];
        $attachment->filePath = $fileData['path'];
        $attachment->fileType = $fileData['type'];
        $attachment->timestamp = time();
        $attachment->save();
        
        return $this;
    }
    
    /**
     * Récupère les pièces jointes d'un message
     * 
     * @param int $messageId ID du message
     * @return array
     */
    public function getAttachments($messageId)
    {
        return $this->attachment->getAll([
            'where' => 'chat_messageid = :messageId',
            'params' => [':messageId' => $messageId]
        ]);
    }
    
    /**
     * Marque un message comme lu par un participant
     * 
     * @param int $messageId ID du message
     * @param int $participantId ID du participant
     * @return $this
     */
    public function markMessageAsRead($messageId, $participantId)
    {
        // Marquer le message comme lu
        $message = clone $this->message;
        if ($message->get($messageId)) {
            // Mettre à jour le statut de lecture
            $message->isRead = 1;
            $message->readTimestamp = time();
            $message->save();
            
            // Mettre à jour le dernier message lu par le participant
            $participant = clone $this->participant;
            if ($participant->get($participantId)) {
                $participant->lastReadMessageId = $messageId;
                $participant->lastReadTimestamp = time();
                $participant->save();
            }
            
            // Marquer les notifications comme lues
            $notifications = $this->notification->getAll([
                'where' => 'chat_participantid = :participantId AND chat_messageid = :messageId',
                'params' => [
                    ':participantId' => $participantId,
                    ':messageId' => $messageId
                ]
            ]);
            
            foreach ($notifications as $notif) {
                $notif->isRead = 1;
                $notif->readTimestamp = time();
                $notif->save();
            }
        }
        
        return $this;
    }
    
    /**
     * Récupère les messages d'une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param int $limit Nombre maximum de messages à récupérer
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function getMessages($conversationId, $limit = 50, $offset = 0)
    {
        return $this->message->getAll([
            'where' => 'chat_conversationid = :conversationId',
            'params' => [':conversationId' => $conversationId],
            'order' => 'timestamp DESC',
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
    
    /**
     * Récupère les messages non lus pour un participant
     * 
     * @param int $participantId ID du participant
     * @return array
     */
    public function getUnreadMessagesForParticipant($participantId)
    {
        $participant = clone $this->participant;
        if (!$participant->get($participantId)) {
            return [];
        }
        
        $lastReadId = $participant->lastReadMessageId ?? 0;
        $conversationId = $participant->chatConversationId;
        
        // Vérifier si la conversation existe
        $conversation = clone $this->conversation;
        if (!$conversation->get($conversationId)) {
            return [];
        }
        
        return $this->message->getAll([
            'where' => 'chat_conversationid = :conversationId AND chat_messageid > :lastReadId',
            'params' => [
                ':conversationId' => $conversationId,
                ':lastReadId' => $lastReadId
            ],
            'order' => 'timestamp ASC'
        ]);
    }
    
    /**
     * Compte les messages non lus pour un participant
     * 
     * @param int $participantId ID du participant
     * @return int
     */
    public function countUnreadMessagesForParticipant($participantId)
    {
        $participant = clone $this->participant;
        if (!$participant->get($participantId)) {
            return 0;
        }
        
        $lastReadId = $participant->lastReadMessageId ?? 0;
        $conversationId = $participant->chatConversationId;
        
        $messages = $this->message->getAll([
            'where' => 'chat_conversationid = :conversationId AND chat_messageid > :lastReadId',
            'params' => [
                ':conversationId' => $conversationId,
                ':lastReadId' => $lastReadId
            ]
        ]);
        
        return count($messages);
    }
    
    /**
     * Met à jour le statut de livraison d'un message
     * 
     * @param int $messageId ID du message
     * @param string $status Nouveau statut (sent, delivered, failed)
     * @param string $error Message d'erreur (optionnel)
     * @return $this
     */
    public function updateMessageDeliveryStatus($messageId, $status, $error = null)
    {
        $message = clone $this->message;
        if ($message->get($messageId)) {
            $message->deliveryStatus = $status;
            if ($error !== null) {
                $message->deliveryError = $error;
            }
            $message->save();
        }
        return $this;
    }
    
    /**
     * Retire un participant d'une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param string $participantType Type de participant (user, lead, professional)
     * @param int $participantId ID du participant
     * @return $this
     */
    public function removeParticipant($conversationId, $participantType, $participantId)
    {
        $existing = $this->participant->getAll([
            'where' => 'chat_conversationid = :conversationId AND participant_type = :type AND participant_id = :id',
            'params' => [
                ':conversationId' => $conversationId,
                ':type' => $participantType,
                ':id' => $participantId
            ],
            'limit' => 1
        ]);
        
        if (!empty($existing)) {
            $participant = $existing[0];
            $participant->status = 'inactive';
            $participant->save();
            
            // Ajouter un message système indiquant que le participant a quitté
            $this->sendMessage(
                $conversationId,
                'system',
                0,
                "Le participant {$participantType} #{$participantId} a quitté la conversation."
            );
        }
        
        return $this;
    }
    
    /**
     * Récupère les participants d'une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @return array
     */
    public function getParticipants($conversationId)
    {
        return $this->participant->getAll([
            'where' => 'chat_conversationid = :conversationId',
            'params' => [':conversationId' => $conversationId]
        ]);
    }
    
    /**
     * Récupère les participants actifs d'une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @return array
     */
    public function getActiveParticipants($conversationId)
    {
        return $this->participant->getAll([
            'where' => 'chat_conversationid = :conversationId AND status = "active"',
            'params' => [':conversationId' => $conversationId]
        ]);
    }
    
    /**
     * Vérifie si un utilisateur est participant à une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param string $participantType Type de participant (user, lead, professional)
     * @param int $participantId ID du participant
     * @return bool
     */
    public function isParticipant($conversationId, $participantType, $participantId)
    {
        $existing = $this->participant->getAll([
            'where' => 'chat_conversationid = :conversationId AND participant_type = :type AND participant_id = :id AND status = "active"',
            'params' => [
                ':conversationId' => $conversationId,
                ':type' => $participantType,
                ':id' => $participantId
            ],
            'limit' => 1
        ]);
        
        return !empty($existing);
    }
    
    /**
     * Met à jour les préférences de notification d'un participant
     * 
     * @param int $participantId ID du participant
     * @param string $preference Préférence de notification (all, mentions, none)
     * @return $this
     */
    public function updateNotificationPreference($participantId, $preference)
    {
        $participant = clone $this->participant;
        if ($participant->get($participantId)) {
            $participant->notificationPreference = $preference;
            $participant->save();
        }
        
        return $this;
    }
    
    /**
     * Récupère les notifications non lues pour un participant
     * 
     * @param int $participantId ID du participant
     * @return array
     */
    public function getUnreadNotifications($participantId)
    {
        return $this->notification->getAll([
            'where' => 'chat_participantid = :participantId AND is_read = 0',
            'params' => [':participantId' => $participantId],
            'order' => 'timestamp DESC'
        ]);
    }
    
    /**
     * Compte les notifications non lues pour un participant
     * 
     * @param int $participantId ID du participant
     * @return int
     */
    public function countUnreadNotifications($participantId)
    {
        $notifications = $this->notification->getAll([
            'where' => 'chat_participantid = :participantId AND is_read = 0',
            'params' => [':participantId' => $participantId]
        ]);
        
        return count($notifications);
    }
    
    /**
     * Marque toutes les notifications d'un participant comme lues
     * 
     * @param int $participantId ID du participant
     * @return $this
     */
    public function markAllNotificationsAsRead($participantId)
    {
        $notifications = $this->notification->getAll([
            'where' => 'chat_participantid = :participantId AND is_read = 0',
            'params' => [':participantId' => $participantId]
        ]);
        
        foreach ($notifications as $notification) {
            $notification->isRead = 1;
            $notification->readTimestamp = time();
            $notification->save();
        }
        
        return $this;
    }
    
    /**
     * Notifie les participants d'un nouveau message
     * 
     * @param int $conversationId ID de la conversation
     * @param int $messageId ID du message
     * @return $this
     */
    private function notifyNewMessage($conversationId, $messageId)
    {
        $participants = $this->participant->getAll([
            'where' => 'chat_conversationid = :conversationId AND status = "active"',
            'params' => [':conversationId' => $conversationId]
        ]);
        
        foreach ($participants as $participant) {
            // Vérifier les préférences de notification
            if ($participant->notificationPreference === 'none') {
                continue;
            }
            
            // Créer une notification
            $notification = clone $this->notification;
            $notification->chatParticipantId = $participant->chatParticipantId;
            $notification->chatMessageId = $messageId;
            $notification->notificationType = 'new_message';
            $notification->timestamp = time();
            $notification->isRead = 0;
            $notification->save();
            
            // Mettre en file d'attente pour traitement asynchrone
            $this->queueManager->add('chat_notifications', [
                'notification_id' => $notification->chatNotificationId,
                'timestamp' => time()
            ]);
        }
        
        return $this;
    }
    
    /**
     * Notifie les participants qu'un nouveau participant a rejoint la conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param int $newParticipantId ID du nouveau participant
     * @return $this
     */
    private function notifyParticipantJoined($conversationId, $newParticipantId)
    {
        $participants = $this->participant->getAll([
            'where' => 'chat_conversationid = :conversationId AND chat_participantid != :newParticipantId AND status = "active"',
            'params' => [
                ':conversationId' => $conversationId,
                ':newParticipantId' => $newParticipantId
            ]
        ]);
        
        foreach ($participants as $participant) {
            // Vérifier les préférences de notification
            if ($participant->notificationPreference === 'none') {
                continue;
            }
            
            // Créer une notification
            $notification = clone $this->notification;
            $notification->chatParticipantId = $participant->chatParticipantId;
            $notification->notificationType = 'participant_joined';
            $notification->timestamp = time();
            $notification->isRead = 0;
            $notification->save();
            
            // Mettre en file d'attente pour traitement asynchrone
            $this->queueManager->add('chat_notifications', [
                'notification_id' => $notification->chatNotificationId,
                'timestamp' => time()
            ]);
        }
        
        return $this;
    }
    
    /**
     * Traite un message en file d'attente
     * 
     * @param array $task Tâche à traiter
     * @return bool
     */
    public function processQueuedMessage($task)
    {
        if (empty($task['message_id'])) {
            return false;
        }
        
        $message = clone $this->message;
        if (!$message->get($task['message_id'])) {
            return false;
        }
        
        // Mettre à jour le statut de livraison
        $message->deliveryStatus = 'delivered';
        $message->save();
        
        return true;
    }
    
    /**
     * Traite une notification en file d'attente
     * 
     * @param array $task Tâche à traiter
     * @return bool
     */
    public function processQueuedNotification($task)
    {
        if (empty($task['notification_id'])) {
            return false;
        }
        
        $notification = clone $this->notification;
        if (!$notification->get($task['notification_id'])) {
            return false;
        }
        
        // Envoyer la notification par le canal approprié
        // (email, push, etc.) selon les préférences de l'utilisateur
        
        return true;
    }
    
    /**
     * Traite une réponse d'agent AI en file d'attente
     * 
     * @param array $task Tâche à traiter
     * @return bool
     */
    public function processQueuedAIResponse($task)
    {
        if (empty($task['conversation_id']) || empty($task['content'])) {
            return false;
        }
        
        // Créer un message de l'agent AI
        $this->sendMessage(
            $task['conversation_id'],
            'system',
            0,
            $task['content']
        );
        
        return true;
    }
}
