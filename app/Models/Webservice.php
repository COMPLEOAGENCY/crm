<?php

namespace Models;
use Framework\CacheManager;

class Webservice extends Model
{
    public static $TABLE_NAME = 'webservice';
    public static $TABLE_INDEX = 'webserviceid';
    public static $OBJ_INDEX = 'webserviceId';
    public static $SCHEMA = array(
        "webserviceId" => array(
            "field" => "webserviceid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "type" => array(
            "field" => "type",
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
        "update_userid" => array(
            "field" => "update_userid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "update_timestamp" => array(
            "field" => "update_timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "file" => array(
            "field" => "file",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "usercampaignid" => array(
            "field" => "usercampaignid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "details" => array(
            "field" => "details",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "extra" => array(
            "field" => "extra",
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
