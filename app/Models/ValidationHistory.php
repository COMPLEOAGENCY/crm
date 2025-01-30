<?php

namespace Models;

class ValidationHistory extends Model
{
    public static $TABLE_NAME = 'validation_history';
    public static $TABLE_INDEX = 'validation_historyid';
    public static $OBJ_INDEX = 'validationHistoryId';
    
    public static $SCHEMA = array(
        "validationHistoryId" => array(
            "field" => "validation_historyid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "validationId" => array(
            "field" => "validationid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "leadId" => array(
            "field" => "leadid",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "validationResult" => array(
            "field" => "validation_result",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "scoringAction" => array(
            "field" => "scoring_action",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "statutAction" => array(
            "field" => "statut_action",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "phoneVal" => array(
            "field" => "phone_val",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "phone2Val" => array(
            "field" => "phone2_val",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "emailVal" => array(
            "field" => "email_val",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "cityVal" => array(
            "field" => "city_val",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        )
    );

    /**
     * Constructeur
     *
     * @param array $data Les données initiales pour l'instance du modèle
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Récupère l'historique des validations pour un lead donné
     *
     * @param string $leadId ID du lead
     * @return array Liste des validations
     */
    public function getHistoryForLead(string $leadId): array
    {
        return $this->getList(null, ['leadid' => $leadId], null, null, 'timestamp', 'desc');
    }

    /**
     * Ajoute une nouvelle entrée d'historique
     *
     * @param array $validationData Données de validation
     * @return bool
     */
    public function addHistoryEntry(array $validationData): bool
    {
        $this->timestamp = time();
        
        foreach ($validationData as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        
        return $this->save();
    }
}
