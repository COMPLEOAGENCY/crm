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
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "senderType" => array(
            "field" => "sender_type",
            "fieldType" => "string",
            "type" => "enum",
            "values" => ["user", "ai", "professional"],
            "default" => "user"
        ),
        "chatAgentId" => array(
            "field" => "chat_agentid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "content" => array(
            "field" => "content",
            "fieldType" => "text",
            "type" => "string",
            "default" => ""
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
