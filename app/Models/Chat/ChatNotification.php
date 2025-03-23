<?php
namespace Models\Chat;

use Models\Model;

class ChatNotification extends Model
{
    public static $TABLE_NAME = 'chat_notification';
    public static $TABLE_INDEX = 'chat_notificationid';
    public static $OBJ_INDEX = 'chatNotificationId';
    public static $SCHEMA = array(
        "chatNotificationId" => array(
            "field" => "chat_notificationid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "chatParticipantId" => array(
            "field" => "chat_participantid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "chatMessageId" => array(
            "field" => "chat_messageid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "notificationType" => array(
            "field" => "notification_type",
            "fieldType" => "string",
            "type" => "enum",
            "values" => ["new_message", "mention", "file_shared", "participant_joined", "participant_left"],
            "default" => "new_message"
        ),
        "isRead" => array(
            "field" => "is_read",
            "fieldType" => "tinyint",
            "type" => "int",
            "default" => 0
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        )
    );

    /**
     * Marque la notification comme lue
     * 
     * @return bool
     */
    public function markAsRead()
    {
        $this->isRead = 1;
        return $this->save();
    }
    
    /**
     * Crée une notification pour un nouveau message
     * 
     * @param int $participantId ID du participant à notifier
     * @param int $messageId ID du message
     * @param string $type Type de notification
     * @return ChatNotification
     */
    public static function create($participantId, $messageId, $type = 'new_message')
    {
        $notification = new self();
        $notification->chatParticipantId = $participantId;
        $notification->chatMessageId = $messageId;
        $notification->notificationType = $type;
        $notification->timestamp = time();
        $notification->save();
        
        return $notification;
    }
    
    /**
     * Récupère toutes les notifications non lues pour un participant
     * 
     * @param int $participantId ID du participant
     * @return array
     */
    public static function getUnreadForParticipant($participantId)
    {
        $notification = new self();
        return $notification->getAll([
            'where' => 'chat_participantid = :participantId AND is_read = 0',
            'params' => [':participantId' => $participantId],
            'order' => 'timestamp DESC'
        ]);
    }
    
    /**
     * Récupère le message associé à la notification
     * 
     * @return ChatMessage|null
     */
    public function getMessage()
    {
        $message = new ChatMessage();
        return $message->get($this->chatMessageId);
    }
    
    /**
     * Récupère le participant associé à la notification
     * 
     * @return ChatParticipant|null
     */
    public function getParticipant()
    {
        $participant = new ChatParticipant();
        return $participant->get($this->chatParticipantId);
    }
}
