<?php
namespace Models\Chat;

use Models\Model;

class ChatConversation extends Model
{
    public static $TABLE_NAME = 'chat_conversation';
    public static $TABLE_INDEX = 'chat_conversationid';
    public static $OBJ_INDEX = 'chatConversationId';
    public static $SCHEMA = array(
        "chatConversationId" => array(
            "field" => "chat_conversationid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "slug" => array(
            "field" => "slug",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "contextType" => array(
            "field" => "context_type",
            "fieldType" => "string",
            "type" => "string",
            "values" => ["lead", "project", "user", "custom"],
            "default" => "lead"
        ),
        "contextId" => array(
            "field" => "context_id",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "title" => array(
            "field" => "title",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "status" => array(
            "field" => "status",
            "fieldType" => "string",
            "type" => "string",
            "values" => ["active", "closed", "archived"],
            "default" => "active"
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "timestampUpdate" => array(
            "field" => "timestamp_update",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        )
    );

    /**
     * Génère un slug unique pour la conversation
     * 
     * @param string $base Base du slug (optionnel)
     * @return string
     */
    public function generateSlug($base = null)
    {
        if (empty($base)) {
            // Générer un slug basé sur le contexte
            switch ($this->contextType) {
                case 'lead':
                    $lead = new \Models\Lead();
                    $leadObj = $lead->get($this->contextId);
                    if ($leadObj) {
                        $base = 'lead-' . $leadObj->first_name . '-' . $leadObj->last_name;
                    } else {
                        $base = 'lead-' . $this->contextId;
                    }
                    break;
                case 'project':
                    $base = 'project-' . $this->contextId;
                    break;
                case 'user':
                    $user = new \Models\User();
                    $userObj = $user->get($this->contextId);
                    if ($userObj) {
                        $base = 'user-' . $userObj->first_name . '-' . $userObj->last_name;
                    } else {
                        $base = 'user-' . $this->contextId;
                    }
                    break;
                default:
                    $base = 'conversation-' . time();
            }
        }

        // Convertir en slug
        $slug = $this->slugify($base);
        
        // Vérifier l'unicité
        $count = 0;
        $originalSlug = $slug;
        while ($this->slugExists($slug)) {
            $count++;
            $slug = $originalSlug . '-' . $count;
        }
        
        return $slug;
    }
    
    /**
     * Vérifie si un slug existe déjà
     * 
     * @param string $slug
     * @return bool
     */
    private function slugExists($slug)
    {
        $result = $this->getAll([
            'where' => 'slug = :slug AND chat_conversationid != :id',
            'params' => [
                ':slug' => $slug,
                ':id' => $this->chatConversationId ?: 0
            ],
            'limit' => 1
        ]);
        
        return !empty($result);
    }
    
    /**
     * Convertit une chaîne en slug
     * 
     * @param string $text
     * @return string
     */
    private function slugify($text)
    {
        // Remplacer les caractères non alphanumériques par des tirets
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // Translitérer
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Supprimer les caractères indésirables
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Trim
        $text = trim($text, '-');
        
        // Supprimer les tirets dupliqués
        $text = preg_replace('~-+~', '-', $text);
        
        // Convertir en minuscules
        $text = strtolower($text);
        
        if (empty($text)) {
            return 'conversation-' . time();
        }
        
        return $text;
    }

    /**
     * Récupère une conversation par son slug
     * 
     * @param string $slug
     * @return ChatConversation|null
     */
    public function getBySlug($slug)
    {
        $result = $this->getAll([
            'where' => 'slug = :slug',
            'params' => [':slug' => $slug],
            'limit' => 1
        ]);
        
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Récupère le contexte associé à la conversation
     * 
     * @return mixed
     */
    public function getContext()
    {
        if (empty($this->contextId)) {
            return null;
        }
        
        switch ($this->contextType) {
            case 'lead':
                $lead = new \Models\Lead();
                return $lead->get($this->contextId);
            case 'project':
                $project = new \Models\Project();
                return $project->get($this->contextId);
            case 'user':
                $user = new \Models\User();
                return $user->get($this->contextId);
            default:
                return null;
        }
    }

    /**
     * Récupère tous les messages d'une conversation
     * 
     * @return array
     */
    public function getMessages()
    {
        $message = new ChatMessage();
        return $message->getAll([
            'where' => 'chat_conversationid = :conversationId',
            'params' => [':conversationId' => $this->chatConversationId],
            'order' => 'timestamp ASC'
        ]);
    }

    /**
     * Récupère tous les participants d'une conversation
     * 
     * @return array
     */
    public function getParticipants()
    {
        $participant = new ChatParticipant();
        return $participant->getAll([
            'where' => 'chat_conversationid = :conversationId',
            'params' => [':conversationId' => $this->chatConversationId]
        ]);
    }
}
