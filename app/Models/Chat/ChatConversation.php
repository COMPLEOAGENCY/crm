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
        "leadId" => array(
            "field" => "leadid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
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
        ),
        "status" => array(
            "field" => "status",
            "fieldType" => "string",
            "type" => "enum",
            "values" => ["active", "closed", "archived"],
            "default" => "active"
        ),
        "currentAgent" => array(
            "field" => "current_agent",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "summary" => array(
            "field" => "summary",
            "fieldType" => "text",
            "type" => "string",
            "default" => null
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
