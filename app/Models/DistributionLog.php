<?php
namespace Models;

class DistributionLog extends Model
{
    public static $TABLE_NAME = 'distibution_log';
    public static $TABLE_INDEX = 'distibution_logid';
    public static $OBJ_INDEX = 'distributionLogId';
    public static $SCHEMA = array(
        "distributionLogId" => array(
            "field" => "distibution_logid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "leadid" => array(
            "field" => "leadid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "usercampaignid" => array(
            "field" => "usercampaignid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "userid" => array(
            "field" => "userid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "campaignid" => array(
            "field" => "campaignid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "data" => array(
            "field" => "data",
            "fieldType" => "text",
            "type" => "string",
            "default" => ""
        ),
        "priority" => array(
            "field" => "priority",
            "fieldType" => "tinyint",
            "type" => "int",
            "default" => 0
        ),
        "result" => array(
            "field" => "result",
            "fieldType" => "tinyint",
            "type" => "int",
            "default" => 0
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
