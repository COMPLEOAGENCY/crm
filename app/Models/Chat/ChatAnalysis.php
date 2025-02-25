<?php
namespace Models\Chat;

use Models\Model;

class ChatAnalysis extends Model
{
    public static $TABLE_NAME = 'chat_analysis';
    public static $TABLE_INDEX = 'chat_analysisid';
    public static $OBJ_INDEX = 'chatAnalysisId';
    public static $SCHEMA = array(
        "chatAnalysisId" => array(
            "field" => "chat_analysisid",
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
        "chatAgentId" => array(
            "field" => "chat_agentid",
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
        "score" => array(
            "field" => "score",
            "fieldType" => "float",
            "type" => "float",
            "default" => null
        ),
        "analysisData" => array(
            "field" => "analysis_data",
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
