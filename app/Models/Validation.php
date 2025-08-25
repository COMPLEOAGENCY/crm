<?php
namespace Models;

class Validation extends Model
{
    public static $TABLE_NAME = 'validation';
    public static $TABLE_INDEX = 'validationid';
    public static $OBJ_INDEX = 'validationId';
    public static $SCHEMA = array(
        // Identifiant
        "validationId" => array(
            "field" => "validationid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        // Champs principaux
        "name" => array(
            "field" => "name",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "type" => array(
            "field" => "type",
            "fieldType" => "string",
            "type" => "string",
            "default" => "none",
            "enum" => ["tel", "audio", "starleads", "sms", "hlr", "hlr2", "hlr_all", "ip", "none", "n8n"]
        ),
        "campaignid" => array(
            "field" => "campaignid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        // Champs supplémentaires (affichage/legacy)
        "sourceid" => array(
            "field" => "sourceid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "type_phone" => array(
            "field" => "type_phone",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "start_lead_statut" => array(
            "field" => "start_lead_statut",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "audio_file" => array(
            "field" => "audio_file",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "audio_file_valid" => array(
            "field" => "audio_file_valid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "audio_file_unvalid" => array(
            "field" => "audio_file_unvalid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "audio_file_error" => array(
            "field" => "audio_file_error",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "start_hour" => array(
            "field" => "start_hour",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "end_hour" => array(
            "field" => "end_hour",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "string",
            "type" => "int",
            "default" => 0
        ),
        "max_nb" => array(
            "field" => "max_nb",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "min_delay" => array(
            "field" => "min_delay",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "first_delay" => array(
            "field" => "first_delay",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "valid_scoring_action" => array(
            "field" => "valid_scoring_action",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "valid_scoring_statut" => array(
            "field" => "valid_scoring_statut",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "unvalid_scoring_action" => array(
            "field" => "unvalid_scoring_action",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "unvalid_scoring_statut" => array(
            "field" => "unvalid_scoring_statut",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "failed_scoring_action" => array(
            "field" => "failed_scoring_action",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "failed_scoring_statut" => array(
            "field" => "failed_scoring_statut",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "reject_scoring_action" => array(
            "field" => "reject_scoring_action",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "reject_scoring_statut" => array(
            "field" => "reject_scoring_statut",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "order" => array(
            "field" => "order",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "statut" => array(
            "field" => "statut",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "api_url" => array(
            "field" => "api_url",
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
