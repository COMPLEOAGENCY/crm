<?php

namespace Models;
use Framework\CacheManager;

class UserCampaign extends Model
{
    public static $TABLE_NAME = 'usercampaign';
    public static $TABLE_INDEX = 'usercampaignid';
    public static $OBJ_INDEX = 'usercampaignId';
    public static $SCHEMA = array(
        "usercampaignId" => array(
            "field" => "usercampaignid",
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
        "last_update_userid" => array(
            "field" => "last_update_userid",
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
        "type" => array(
            "field" => "type",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "vendor_id" => array(
            "field" => "vendor_id",
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
        "userid" => array(
            "field" => "userid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "campaignid" => array(
            "field" => "campaignid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "campaignid_list" => array(
            "field" => "campaignid_list",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "sourceid" => array(
            "field" => "sourceid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "filters" => array(
            "field" => "filters",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "state" => array(
            "field" => "state",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "max_sale_ratio" => array(
            "field" => "max_sale_ratio",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "scoring_min" => array(
            "field" => "scoring_min",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "scoring_max" => array(
            "field" => "scoring_max",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "scoring_average" => array(
            "field" => "scoring_average",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "max_cross_rate_same_user" => array(
            "field" => "max_cross_rate_same_user",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "max_cross_rate_other_user" => array(
            "field" => "max_cross_rate_other_user",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "attach_to_campaign" => array(
            "field" => "attach_to_campaign",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "delay" => array(
            "field" => "delay",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }    

}
