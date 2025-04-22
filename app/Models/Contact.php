<?php
namespace Models;

class Contact extends Model
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
        "civ" => array(
            "field" => "civ",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "first_name" => array(
            "field" => "first_name",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "last_name" => array(
            "field" => "last_name",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "email" => array(
            "field" => "email",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "country" => array(
            "field" => "country",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),        
        "phone" => array(
            "field" => "phone",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "phone2" => array(
            "field" => "phone2",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "email_val" => array(
            "field" => "email_val",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "phone_val" => array(
            "field" => "phone_val",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "phone2_val" => array(
            "field" => "phone2_val",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "utm_source" => array(
            "field" => "utm_source",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "utm_medium" => array(
            "field" => "utm_medium",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "utm_campaign" => array(
            "field" => "utm_campaign",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "utm_content" => array(
            "field" => "utm_content",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "utm_term" => array(
            "field" => "utm_term",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "url" => array(
            "field" => "url",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "referer" => array(
            "field" => "referer",
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
