<?php
namespace Models;

class Lead extends Model
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
        "provider_leadid" => array(
            "field" => "provider_leadid",
            "fieldType" => "string",
            "type" => "string", 
            "default" => ""
        ),
        "sourceid" => array(
            "field" => "sourceid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "campaignid" => array(
            "field" => "campaignid", 
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
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
        "last_update_userid" => array(
            "field" => "last_update_userid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "cookie_hash" => array(
            "field" => "cookie_hash",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "hash" => array(
            "field" => "hash",
            "fieldType" => "string", 
            "type" => "string",
            "default" => ""
        ),
        "timestamp_lead" => array(
            "field" => "timestamp_lead",
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
        "phone2" => array(
            "field" => "phone2",
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
        "bloctel_status" => array(
            "field" => "bloctel_status",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "bloctelid" => array(
            "field" => "bloctelid",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "bloctel_timestamp" => array(
            "field" => "bloctel_timestamp",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "email" => array(
            "field" => "email",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "IP" => array(
            "field" => "IP",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "qualification" => array(
            "field" => "qualification",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "comments" => array(
            "field" => "comments",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "admin_comments" => array(
            "field" => "admin_comments",
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
        "phone_val" => array(
            "field" => "phone_val",
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
        "city_val" => array(
            "field" => "city_val",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "scoring" => array(
            "field" => "scoring",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "timestamp_sent" => array(
            "field" => "timestamp_sent",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "timestamp_lastcall" => array(
            "field" => "timestamp_lastcall",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "nb_call" => array(
            "field" => "nb_call",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "statut" => array(
            "field" => "statut",
            "fieldType" => "enum",
            "type" => "string",
            "default" => "pending"
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
        ),
        "mention_rgpd" => array(
            "field" => "mention_rgpd",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "_subRegion" => array(
            "field" => "_subregion",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "_region" => array(
            "field" => "_region",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "_countryName" => array(
            "field" => "_countryname",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "_age_calculation" => array(
            "field" => "_age_calculation",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "_sexe_calculation" => array(
            "field" => "_sexe_calculation",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "_age_min" => array(
            "field" => "_age_min",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "_age_max" => array(
            "field" => "_age_max",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "_gclid" => array(
            "field" => "_gclid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "_msclkid" => array(
            "field" => "_msclkid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "leashopId" => array(
            "field" => "leashopId",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "proshopId" => array(
            "field" => "proshopId",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        )
    );

    protected $questions = [];

    public function __construct($data = [])
    {
        parent::__construct($data);
    }

    public function afterGet() 
    {       
        // Récupérer les métadonnées
        $meta = new Meta();
        $metaData = $meta->getAllMetaForRow($this->leadId, 'lead', false);
        
        if ($metaData) {
            foreach ($metaData as $data) {
                if (!empty($data->value)) {
                    $question = new Question();
                    $questionObj = $question->getQuestionByLabel($data->label);
                    
                    if ($questionObj && is_array($questionObj) && count($questionObj) > 0) {
                        $string = str_ireplace(" ", "_", strtolower($questionObj[0]->label));
                        $this->$string = strtolower($data->value);
                        $this->questions[$data->label] = $data->value;
                    }
                }
            }
        }
    }

    /**
     * Enregistre les métadonnées pour un lead
     * 
     * @param array $data Les données à enregistrer
     * @return bool
     */
    public function recordLeadMeta($data)
    {
        if (empty($data) || empty($this->leadId)) {
            return false;
        }

        if (!is_array($data)) {
            $data = (array) $data;
        }

        // Récupérer les champs de la campagne
        $campaign = new Campaign();
        $fields = $campaign->getFieldsByCampaignId($this->campaignid);
        
        if (empty($fields)) {
            return true; // Pas de champs à enregistrer
        }

        $meta = new Meta();
        $success = true;

        foreach ($fields as $label => $field) {
            if (isset($data[$label])) {
                $value = $data[$label];
                
                // Traitement spécial pour les champs de type select
                if (isset($field['type']) && $field['type'] === 'select' && is_numeric($value)) {
                    if (isset($field['default_values'][$value - 1]) && !is_numeric($field['default_values'][$value - 1])) {
                        $value = $field['default_values'][$value - 1];
                    }
                }

                if (!empty(trim($value))) {
                    if (!$meta->addRowValue('lead', $this->leadId, $label, $value)) {
                        $success = false;
                    }
                }
            }
        }

        return $success;
    }

    /**
     * Récupère toutes les métadonnées d'un lead
     * 
     * @return array|false
     */
    public function getMeta()
    {
        if (empty($this->leadId)) {
            return false;
        }

        $meta = new Meta();
        return $meta->getAllMetaForRow($this->leadId, 'lead');
    }
}
