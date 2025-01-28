<?php
namespace Models;

class Bloctel extends Model
{
    public static $TABLE_NAME = 'bloctel';
    public static $TABLE_INDEX = 'bloctelid';
    public static $OBJ_INDEX = 'bloctelId';
    public static $SCHEMA = array(
        "bloctelId" => array(
            "field" => "bloctelid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "file_name" => array(
            "field" => "file_name",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
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
        "timestamp_file_sent" => array(
            "field" => "timestamp_file_sent",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp_file_received" => array(
            "field" => "timestamp_file_received",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "bloctel_status" => array(
            "field" => "bloctel_status",
            "fieldType" => "string",
            "type" => "string",
            "default" => "created"
        ),
        "error" => array(
            "field" => "error",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}