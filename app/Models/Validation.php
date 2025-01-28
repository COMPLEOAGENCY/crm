<?php
namespace Models;

class Validation extends Model
{
    public static $TABLE_NAME = 'validation';
    public static $TABLE_INDEX = 'validationid';
    public static $OBJ_INDEX = 'validationId';
    public static $SCHEMA = array(
        "validationId" => array(
            "field" => "validationid",
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
            "fieldType" => "enum",
            "type" => "string",
            "default" => "none",
            "enum" => ["tel", "audio", "sms", "hlr", "hlr2", "hlr_all", "ip", "none"]
        ),
        "campaignid" => array(
            "field" => "campaignid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
