<?php
namespace Models\Chat;

use Models\Model;

class ChatAgent extends Model
{
    public static $TABLE_NAME = 'chat_agent';
    public static $TABLE_INDEX = 'chat_agentid';
    public static $OBJ_INDEX = 'chatAgentId';
    public static $SCHEMA = array(
        "chatAgentId" => array(
            "field" => "chat_agentid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "name" => array(
            "field" => "name",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "type" => array(
            "field" => "type",
            "fieldType" => "string",
            "type" => "enum",
            "values" => ["contact_validator", "project_completeness", "technical_expert", "conversation_manager"],
            "default" => "contact_validator"
        ),
        "promptTemplate" => array(
            "field" => "prompt_template",
            "fieldType" => "text",
            "type" => "string",
            "default" => ""
        ),
        "sequenceOrder" => array(
            "field" => "sequence_order",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "status" => array(
            "field" => "status",
            "fieldType" => "string",
            "type" => "enum",
            "values" => ["active", "inactive"],
            "default" => "active"
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
