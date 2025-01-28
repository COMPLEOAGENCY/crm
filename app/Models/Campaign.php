<?php
namespace Models;

class Campaign extends Model
{
    public static $TABLE_NAME = 'campaign';
    public static $TABLE_INDEX = 'campaignid';
    public static $OBJ_INDEX = 'campaignId';
    public static $SCHEMA = array(
        "campaignId" => array(
            "field" => "campaignid",
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
        "last_update_timestamp" => array(
            "field" => "last_update_timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "ads_code" => array(
            "field" => "ads_code",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "name" => array(
            "field" => "name",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "details" => array(
            "field" => "details",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "sale_ratio" => array(
            "field" => "sale_ratio",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "shop_scoring_min" => array(
            "field" => "shop_scoring_min",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "price" => array(
            "field" => "price",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "price_abo" => array(
            "field" => "price_abo",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "price_multi" => array(
            "field" => "price_multi",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "price_multi_abo" => array(
            "field" => "price_multi_abo",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "affiliate_price" => array(
            "field" => "affiliate_price",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "statut" => array(
            "field" => "statut",
            "fieldType" => "enum",
            "type" => "string",
            "default" => "off"
        ),
        "validation" => array(
            "field" => "validation",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "show" => array(
            "field" => "show",
            "fieldType" => "enum",
            "type" => "string",
            "default" => "no"
        ),
        "shopId" => array(
            "field" => "shopId",
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
