<?php
namespace Models;

class Project extends Model
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
        "status" => array(
            "field" => "statut",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "campaignId" => array(
            "field" => "campaignid",
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
        "postal_code" => array(
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
        "questions" => array(
            "field" => null,
            "fieldType" => "array",
            "type" => "array",
            "default" => array()
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
        
        if (!empty($this->campaignId)) {
            $this->loadCampaignQuestions();
        }
    }

    /**
     * Charge toutes les questions de la campagne et leurs valeurs si disponibles
     */
    protected function loadCampaignQuestions()
    {
        // Charger d'abord les métadonnées si on a un leadId
        $metaData = [];
        if (!empty($this->leadId)) {
            $meta = new Meta();
            $metaData = $meta->getAllMetaForRow($this->leadId, 'lead', true);
        }

        // Charger toutes les questions de la campagne
        $question = new Question();
        $campaignQuestions = $question->getQuestionByCampaignId($this->campaignId);

        $this->questions = [];

        // Initialiser toutes les questions avec leurs valeurs
        foreach ($campaignQuestions as $question) {
            // Déterminer la valeur
            $value = null;
            if (isset($metaData[$question->label])) {
                // Valeur trouvée dans les métadonnées
                $value = $metaData[$question->label];
            }

            // Stocker la question avec toutes ses propriétés
            $this->questions[$question->label] = array_merge((array) $question, [
                'value' => $value
            ]);

            // Créer la propriété dynamique
            // $this->{$question->label} = $value;
        }
    }
}
