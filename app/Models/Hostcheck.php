<?php
namespace Models;

class Hostcheck extends Model
{
    public static $TABLE_NAME = 'hostcheck';
    public static $TABLE_INDEX = 'hostcheckid';
    public static $OBJ_INDEX = 'hostcheckId';
    public static $SCHEMA = array(
        "hostcheckId" => array(
            "field" => "hostcheckid",
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
        "timestamp_check" => array(
            "field" => "timestamp_check",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "host" => array(
            "field" => "host",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "provider" => array(
            "field" => "provider",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "delist" => array(
            "field" => "delist",
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
