<?php
namespace Models\Chat;

use Models\Model;

class ChatParticipant extends Model
{
    public static $TABLE_NAME = 'chat_participant';
    public static $TABLE_INDEX = 'chat_participantid';
    public static $OBJ_INDEX = 'chatParticipantId';
    public static $SCHEMA = array(
        "chatParticipantId" => array(
            "field" => "chat_participantid",
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
        "participantType" => array(
            "field" => "participant_type",
            "fieldType" => "string",
            "type" => "enum",
            "values" => ["user", "lead", "professional"],
            "default" => "user"
        ),
        "participantId" => array(
            "field" => "participant_id",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "joinTimestamp" => array(
            "field" => "join_timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "lastReadMessageId" => array(
            "field" => "last_read_messageid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "status" => array(
            "field" => "status",
            "fieldType" => "string",
            "type" => "enum",
            "values" => ["active", "inactive", "left"],
            "default" => "active"
        ),
        "notificationPreference" => array(
            "field" => "notification_preference",
            "fieldType" => "string",
            "type" => "enum",
            "values" => ["all", "mentions", "none"],
            "default" => "all"
        )
    );

    /**
     * Récupère les informations du participant en fonction de son type
     * 
     * @return mixed L'objet participant (User, Lead, etc.)
     */
    public function getParticipantInfo()
    {
        switch ($this->participantType) {
            case 'user':
                $user = new \Models\User();
                return $user->get($this->participantId);
            case 'lead':
                $lead = new \Models\Lead();
                return $lead->get($this->participantId);
            case 'professional':
                $professional = new \Models\User();
                return $professional->get($this->participantId);
            default:
                return null;
        }
    }
    
    /**
     * Met à jour le dernier message lu par le participant
     * 
     * @param int $messageId ID du message lu
     * @return bool
     */
    public function updateLastReadMessage($messageId)
    {
        $this->lastReadMessageId = $messageId;
        return $this->save();
    }
    
    /**
     * Récupère les messages non lus pour ce participant
     * 
     * @return array
     */
    public function getUnreadMessages()
    {
        $message = new ChatMessage();
        $query = [
            'where' => 'chat_conversationid = :conversationId AND chat_messageid > :lastReadId',
            'params' => [
                ':conversationId' => $this->chatConversationId,
                ':lastReadId' => $this->lastReadMessageId ?: 0
            ],
            'order' => 'timestamp ASC'
        ];
        
        return $message->getAll($query);
    }
    
    /**
     * Met à jour les préférences de notification du participant
     * 
     * @param string $preference Nouvelle préférence ('all', 'mentions', 'none')
     * @return bool
     */
    public function updateNotificationPreference($preference)
    {
        $this->notificationPreference = $preference;
        return $this->save();
    }
}
