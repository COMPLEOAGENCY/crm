<?php
namespace Models;

class Code extends Model
{
    public static $TABLE_NAME = 'code';
    public static $TABLE_INDEX = 'codeid';
    public static $OBJ_INDEX = 'codeId';
    public static $SCHEMA = array(
        "codeId" => array(
            "field" => "codeid",
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
        "userid" => array(
            "field" => "userid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "timestamp_start" => array(
            "field" => "timestamp_start",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp_end" => array(
            "field" => "timestamp_end",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "bonus_rate_pct" => array(
            "field" => "bonus_rate_pct",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "transaction_min" => array(
            "field" => "transaction_min",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "code_value" => array(
            "field" => "code_value",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "nb" => array(
            "field" => "nb",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "statut" => array(
            "field" => "statut",
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
