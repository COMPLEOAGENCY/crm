<?php
namespace Models\Chat;

use Models\Model;

class ChatMessage extends Model
{
    public static $TABLE_NAME = 'chat_message';
    public static $TABLE_INDEX = 'chat_messageid';
    public static $OBJ_INDEX = 'chatMessageId';
    public static $SCHEMA = array(
        "chatMessageId" => array(
            "field" => "chat_messageid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "chatConversationId" => array(
            "field" => "chat_conversationid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "senderType" => array(
            "field" => "sender_type",
            "fieldType" => "string",
            "type" => "string",
            "values" => ["user", "lead", "professional", "system", "ai"],
            "default" => "user"
        ),
        "senderId" => array(
            "field" => "sender_id",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "recipientType" => array(
            "field" => "recipient_type",
            "fieldType" => "string",
            "type" => "string",
            "values" => ["user", "lead", "professional", "all"],
            "default" => "all"
        ),
        "recipientId" => array(
            "field" => "recipient_id",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "content" => array(
            "field" => "content",
            "fieldType" => "text",
            "type" => "string",
            "default" => ""
        ),
        "messageType" => array(
            "field" => "message_type",
            "fieldType" => "string",
            "type" => "string",
            "values" => ["text", "image", "file", "audio", "video", "location", "contact", "system"],
            "default" => "text"
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "isRead" => array(
            "field" => "is_read",
            "fieldType" => "tinyint",
            "type" => "int",
            "default" => 0
        ),
        "readTimestamp" => array(
            "field" => "read_timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "status" => array(
            "field" => "status",
            "fieldType" => "string",
            "type" => "string",
            "values" => ["sent", "delivered", "read", "failed"],
            "default" => "sent"
        ),
        "deliveryTimestamp" => array(
            "field" => "delivery_timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "deliveryAttempts" => array(
            "field" => "delivery_attempts",
            "fieldType" => "int",
            "type" => "int",
            "default" => 1
        ),
        "deliveryError" => array(
            "field" => "delivery_error",
            "fieldType" => "text",
            "type" => "string",
            "default" => null
        ),
        "isProcessed" => array(
            "field" => "is_processed",
            "fieldType" => "tinyint",
            "type" => "int",
            "default" => 0
        )
    );

    /**
     * Marque le message comme lu
     * 
     * @return bool
     */
    public function markAsRead()
    {
        if ($this->isRead) {
            return true; // Déjà marqué comme lu
        }
        
        $this->isRead = 1;
        $this->readTimestamp = time();
        
        return $this->save();
    }
    
    /**
     * Met à jour le statut de livraison du message
     * 
     * @param string $status Le nouveau statut ('sent', 'delivered', 'failed')
     * @param string $error Message d'erreur en cas d'échec (optionnel)
     * @return bool
     */
    public function updateDeliveryStatus($status, $error = null)
    {
        $this->status = $status;
        $this->deliveryTimestamp = time();
        
        if ($status === 'failed') {
            $this->deliveryAttempts++;
            if ($error) {
                $this->deliveryError = $error;
            }
        }
        
        return $this->save();
    }
    
    /**
     * Tente de renvoyer un message échoué
     * 
     * @return bool
     */
    public function retry()
    {
        if ($this->status !== 'failed') {
            return false; // Pas besoin de réessayer
        }
        
        // Logique pour réessayer l'envoi du message via n8n ou autre système
        // ...
        
        $this->deliveryAttempts++;
        $this->updateDeliveryStatus('sent');
        
        return true;
    }

    /**
     * Récupère l'expéditeur du message
     * 
     * @return mixed L'objet expéditeur (User, Lead, etc.)
     */
    public function getSender()
    {
        switch ($this->senderType) {
            case 'user':
                $user = new \Models\User();
                return $user->get($this->senderId);
            case 'lead':
                $lead = new \Models\Lead();
                return $lead->get($this->senderId);
            case 'professional':
                $professional = new \Models\User();
                return $professional->get($this->senderId);
            default:
                return null;
        }
    }

    /**
     * Récupère le destinataire du message
     * 
     * @return mixed L'objet destinataire (User, Lead, etc.) ou null si destiné à tous
     */
    public function getRecipient()
    {
        if ($this->recipientType === 'all') {
            return null; // Message destiné à tous les participants
        }

        switch ($this->recipientType) {
            case 'user':
                $user = new \Models\User();
                return $user->get($this->recipientId);
            case 'lead':
                $lead = new \Models\Lead();
                return $lead->get($this->recipientId);
            case 'professional':
                $professional = new \Models\User();
                return $professional->get($this->recipientId);
            default:
                return null;
        }
    }

    /**
     * Récupère les pièces jointes d'un message
     * 
     * @return array
     */
    public function getAttachments()
    {
        $attachment = new ChatAttachment();
        return $attachment->getAll([
            'where' => 'chat_messageid = :messageId',
            'params' => [':messageId' => $this->chatMessageId]
        ]);
    }
}
