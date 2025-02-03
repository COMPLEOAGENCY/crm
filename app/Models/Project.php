<?php
namespace Models;

class Project extends Model
{
    public static $TABLE_NAME = 'lead';
    public static $TABLE_INDEX = 'leadid';
    public static $OBJ_INDEX = 'leadId';
    public static $SCHEMA = array(
        "leadId" => array(
            "field" => "leadid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "update_timestamp" => array(
            "field" => "update_timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "status" => array(
            "field" => "statut",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "campaignId" => array(
            "field" => "campaignid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "address1" => array(
            "field" => "address1",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "address2" => array(
            "field" => "address2",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "postal_code" => array(
            "field" => "cp",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "city" => array(
            "field" => "city",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "country" => array(
            "field" => "country",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        )
    );

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
