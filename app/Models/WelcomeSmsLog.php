<?php
namespace Models;

class WelcomeSmsLog extends Model
{
    public static $TABLE_NAME = 'welcome_sms_log';
    public static $TABLE_INDEX = 'welcome_sms_logid';
    public static $OBJ_INDEX = 'welcomeSmsLogId';
    public static $SCHEMA = array(
        "welcomeSmsLogId" => array(
            "field" => "welcome_sms_logid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp_update" => array(
            "field" => "timestamp_update",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "leadid" => array(
            "field" => "leadid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
