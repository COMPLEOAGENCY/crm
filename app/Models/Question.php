<?php
namespace Models;

class Question extends Model
{
    public static $TABLE_NAME = 'question';
    public static $TABLE_INDEX = 'questionid';
    public static $OBJ_INDEX = 'questionId';
    
    public static $SCHEMA = array(
        "questionId" => array(
            "field" => "questionid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "campaignId" => array(
            "field" => "campaignid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "creationDate" => array(
            "field" => "creation_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "updateDate" => array(
            "field" => "update_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "order" => array(
            "field" => "order",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "label" => array(
            "field" => "label",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "question" => array(
            "field" => "question",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "type" => array(
            "field" => "type",
            "fieldType" => "enum",
            "type" => "string",
            "default" => "input"
        ),
        "defaultValues" => array(
            "field" => "default_values",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "regex" => array(
            "field" => "regex",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "defaultCalculation" => array(
            "field" => "default_calculation",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "status" => array(
            "field" => "statut",
            "fieldType" => "enum",
            "type" => "string",
            "default" => "on"
        )
    );

    /**
     * Récupère une question par son label
     * @param string $label Label de la question
     * @return array|null Question trouvée ou null
     */
    public function getQuestionByLabel($label) 
    {
        return $this->getList(
            1, // limite à 1 résultat
            [
                'label' => $label
            ]
        );
    }

    /**
     * Récupère les questions actives d'une campagne
     * @param int $campaignId ID de la campagne
     * @return array Questions de la campagne
     */
    public function getQuestionByCampaignId($campaignId) 
    {
        $questions = $this->getList(
            null, // pas de limite
            [
                'campaignid' => $campaignId,
                'statut' => 'on'
            ],
            null, // pas de paramètres JSON
            null, // pas de GROUP BY
            'order', // ORDER BY order
            'asc' // direction ASC
        );

        // Filtrer les questions qui commencent par _
        return array_filter($questions, function($question) {
            return $question->label[0] !== '_';
        });
    }
}
