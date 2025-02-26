<?php
namespace Models;

class Project extends Model implements \JsonSerializable
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
        "campaign" => array(
            "field" => null,
            "fieldType" => "array",
            "type" => "array",
            "default" => array()
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

        // Charger la campagne si on a un campaignId
        if (!empty($this->campaignId)) {
            $campaign = new Campaign();
            $this->campaign = $campaign->get($this->campaignId);
            $this->loadCampaignQuestions();
        }
        // var_dump($this);exit;
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

    /**
     * Récupère la campagne associée au projet
     * 
     * @return Campaign|null
     */
    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    /**
     * Sauvegarde le projet et ses questions
     * 
     * @return bool
     */
    public function save()
    {
        // Sauvegarder d'abord le projet lui-même
        $result = parent::save();
        
        if (!$result || empty($this->questions)) {
            return $result;
        }

        // Sauvegarder les réponses aux questions
        $meta = new Meta();
        foreach ($this->questions as $label => $question) {
            if (isset($question['value'])) {
                $meta->addRowValue('lead', $this->leadId, $label, $question['value']);
            }
        }

        return true;
    }

    /**
     * Spécifie les données à sérialiser en JSON
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        $data = [];
        
        // Ajouter toutes les propriétés du schéma
        foreach (static::$SCHEMA as $property => $propertySet) {
            if (property_exists($this, $property)) {
                $data[$property] = $this->$property;
            }
        }
        
        // S'assurer que campaign est correctement initialisé
        if (empty($data['campaign']) && !empty($this->campaignId)) {
            $campaign = new Campaign();
            $data['campaign'] = $campaign->get($this->campaignId);
        }
        
        // S'assurer que questions est correctement initialisé
        if (empty($data['questions']) && !empty($this->campaignId)) {
            $this->loadCampaignQuestions();
            $data['questions'] = $this->questions;
        }
        
        return $data;
    }
}
