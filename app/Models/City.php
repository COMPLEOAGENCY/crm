<?php
namespace Models;

class City extends Model
{
    public static $TABLE_NAME = 'city';
    public static $TABLE_INDEX = 'cityid';
    public static $OBJ_INDEX = 'cityId';
    public static $SCHEMA = array(
        "cityId" => array(
            "field" => "cityid",
            "fieldType" => "bigint",
            "type" => "int",
            "default" => null
        ),
        "country" => array(
            "field" => "country",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "language" => array(
            "field" => "language",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "region" => array(
            "field" => "region",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "departement" => array(
            "field" => "departement",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "arrondissement" => array(
            "field" => "arrondissement",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "canton" => array(
            "field" => "canton",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "cp" => array(
            "field" => "cp",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "ville" => array(
            "field" => "ville",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "area1" => array(
            "field" => "area1",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "area2" => array(
            "field" => "area2",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "lat" => array(
            "field" => "lat",
            "fieldType" => "double",
            "type" => "float",
            "default" => 0
        ),
        "lng" => array(
            "field" => "lng",
            "fieldType" => "double",
            "type" => "float",
            "default" => 0
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
