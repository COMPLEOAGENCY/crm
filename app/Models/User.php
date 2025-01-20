<?php
// Path: src/app/Models/User.php

namespace Models;
use Framework\CacheManager;

class User extends Model
{
    public static $TABLE_NAME = 'user';
    public static $TABLE_INDEX = 'userid';
    public static $OBJ_INDEX = 'userId';
    public static $SCHEMA = array(
        "userId" => array(
            "field" => "userid",
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
        "last_update_timestamp" => array(
            "field" => "last_update_timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "last_update_userid" => array(
            "field" => "last_update_userid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "type" => array(
            "field" => "type",
            "fieldType" => "enum",
            "type" => "string",
            "default" => 'client'
        ),
        "vendor_id" => array(
            "field" => "vendor_id",
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
        "company" => array(
            "field" => "company",
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
        "address" => array(
            "field" => "address",
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
        ),
        "state" => array(
            "field" => "state",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "mobile" => array(
            "field" => "mobile",
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
        "email" => array(
            "field" => "email",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "email2" => array(
            "field" => "email2",
            "fieldType" => "mediumtext",
            "type" => "string",
            "default" => ""
        ),
        "registration_number" => array(
            "field" => "registration_number",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "vat_number" => array(
            "field" => "vat_number",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "sale_notification_email" => array(
            "field" => "sale_notification_email",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "sale_notification_email2" => array(
            "field" => "sale_notification_email2",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "welcome_sms" => array(
            "field" => "welcome_sms",
            "fieldType" => "text",
            "type" => "string",
            "default" => ""
        ),
        "legal_sms" => array(
            "field" => "legal_sms",
            "fieldType" => "enum",
            "type" => "string",
            "default" => 'on'
        ),
        "password" => array(
            "field" => "password",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "details" => array(
            "field" => "details",
            "fieldType" => "mediumtext",
            "type" => "string",
            "default" => ""
        ),
        "encours_max" => array(
            "field" => "encours_max",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0.0
        ),
        "global_day_capping" => array(
            "field" => "global_day_capping",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "global_month_capping" => array(
            "field" => "global_month_capping",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "user_exclusion" => array(
            "field" => "user_exclusion",
            "fieldType" => "json",
            "type" => "array",
            "default" => ""
        ),
        "IP" => array(
            "field" => "IP",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "statut" => array(
            "field" => "statut",
            "fieldType" => "enum",
            "type" => "string",
            "default" => 'on'
        ),
        "deversoir" => array(
            "field" => "deversoir",
            "fieldType" => "enum",
            "type" => "string",
            "default" => 'no'
        ),
        "pro_start_date" => array(
            "field" => "pro_start_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "billing_start_date" => array(
            "field" => "billing_start_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp_connexion" => array(
            "field" => "timestamp_connexion",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "marque_blanche" => array(
            "field" => "marque_blanche",
            "fieldType" => "enum",
            "type" => "string",
            "default" => 'no'
        ),
        "shopId" => array(
            "field" => "shopId",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        )
    );
    
    public function add_solde($userList)
    {
        foreach ($userList as $key => $user) {
            if(isset( $user->solde_detail) && $user->solde_detail != null){
                continue;
            }
        }
        return $userList;
    }


    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
