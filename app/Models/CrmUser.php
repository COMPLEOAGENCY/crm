<?php
// Path: src/app/Models/CrmUser.php
namespace Models;

class CrmUser extends Model
{
    public static $TABLE_NAME = 'crm_user';
    public static $TABLE_INDEX = 'crm_userId';
    public static $OBJ_INDEX = 'crmUserId';
    public static $SCHEMA = array(
        "crmUserId" => array(
            "field" => "crm_userId",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "crm_user_creation_date" => array(
            "field" => "crm_user_creation_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "crm_user_update_date" => array(
            "field" => "crm_user_update_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "crm_user_last_connexion_date" => array(
            "field" => "crm_user_last_connexion_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "crm_user_firstname" => array(
            "field" => "crm_user_firstname",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "crm_user_lastname" => array(
            "field" => "crm_user_lastname",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "crm_user_mobile" => array(
            "field" => "crm_user_mobile",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "crm_user_email" => array(
            "field" => "crm_user_email",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "crm_user_IP" => array(
            "field" => "crm_user_IP",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "crm_user_fingerprint" => array(
            "field" => "crm_user_fingerprint",
            "fieldType" => "mediumtext",
            "type" => "string",
            "default" => null
        ),
        "crm_user_allowed_page" => array(
            "field" => "crm_user_allowed_page",
            "fieldType" => "text",
            "type" => "string",
            "default" => null
        ),
        "crm_user_forbidden_page" => array(
            "field" => "crm_user_forbidden_page",
            "fieldType" => "text",
            "type" => "string",
            "default" => null
        ),
        "crm_user_role" => array(
            "field" => "crm_user_role",
            "fieldType" => "enum",
            "type" => "string",
            "default" => 'user'
        ),
        "crm_user_statut" => array(
            "field" => "crm_user_statut",
            "fieldType" => "enum",
            "type" => "string",
            "default" => 'on'
        ),
        "crm_user_password" => array(
            "field" => "crm_user_password",
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
