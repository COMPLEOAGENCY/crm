<?php
namespace Models;

class Domain extends Model
{
    public static $TABLE_NAME = 'domain';
    public static $TABLE_INDEX = 'domainid';
    public static $OBJ_INDEX = 'domainId';
    public static $SCHEMA = array(
        "domainId" => array(
            "field" => "domainid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "domain" => array(
            "field" => "domain",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "ip" => array(
            "field" => "ip",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "timestamp_creation" => array(
            "field" => "timestamp_creation",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "timestamp_expiration" => array(
            "field" => "timestamp_expiration",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "spf" => array(
            "field" => "spf",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "dkim" => array(
            "field" => "dkim",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "dmarc" => array(
            "field" => "dmarc",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "abuse" => array(
            "field" => "abuse",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "postmaster" => array(
            "field" => "postmaster",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "seo" => array(
            "field" => "seo",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "mx" => array(
            "field" => "mx",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "http" => array(
            "field" => "http",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "https" => array(
            "field" => "https",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "blacklist_score" => array(
            "field" => "blacklist_score",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "nameservers" => array(
            "field" => "nameservers",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "registrar" => array(
            "field" => "registrar",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "registrar_statut" => array(
            "field" => "registrar_statut",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "statut" => array(
            "field" => "statut",
            "fieldType" => "enum",
            "type" => "string",
            "default" => "on"
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
