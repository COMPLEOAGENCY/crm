<?php
namespace Models;

use Classes\Phone;

class Contact extends Model
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
        "email" => array(
            "field" => "email",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "country" => array(
            "field" => "country",
            "fieldType" => "string",
            "type" => "string",
            "default" => "FR"
        ),        
        "phone" => array(
            "field" => "phone",
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
        "email_val" => array(
            "field" => "email_val",
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
        "phone2_val" => array(
            "field" => "phone2_val",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "utm_source" => array(
            "field" => "utm_source",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "utm_medium" => array(
            "field" => "utm_medium",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "utm_campaign" => array(
            "field" => "utm_campaign",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "utm_content" => array(
            "field" => "utm_content",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "utm_term" => array(
            "field" => "utm_term",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
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
        )
    );

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Surcharge de la méthode magique __get pour retourner un objet Phone pour les propriétés phone et phone2
     * 
     * @param string $name Nom de la propriété
     * @return mixed Valeur de la propriété ou objet Phone pour phone/phone2
     */
    public function __get($name)
    {
        // Obtenir d'abord la valeur depuis le parent
        $value = parent::__get($name);
        
        // Traitement spécial pour phone et phone2
        if ($name === 'phone' && !empty($value)) {
            return new Phone($value, parent::__get('country'));
        }
        
        if ($name === 'phone2' && !empty($value)) {
            return new Phone($value, parent::__get('country'));
        }
        
        // Pour toutes les autres propriétés, retourner la valeur du parent
        return $value;
    }
    
    /**
     * Surcharge de la méthode get pour traiter les numéros de téléphone
     * 
     * @param int $id L'identifiant du contact à récupérer
     * @return Contact|bool L'instance du contact hydraté ou false en cas d'échec
     */
    public function get(int $id)
    {
        // Appel à la méthode parente pour récupérer le contact
        $result = parent::get($id);
        
        // Si la récupération a réussi, on applique nos transformations
        if ($result !== false) {
            // Si phone2 est vide, on y met le numéro principal au format international
            if (!empty($this->phone)) {
                $phoneValue = parent::__get('phone');
                $countryValue = parent::__get('country');
                $this->phone2 = Phone::format($phoneValue, $countryValue);
            }
            
            // Si phone2_val est vide et phone_val n'est pas vide, on duplique phone_val vers phone2_val
            if (empty($this->phone2_val) && !empty($this->phone_val)) {
                $this->phone2_val = $this->phone_val;
            }
        }
        
        return $result;
    }
    
    /**
     * Surcharge de la méthode getList pour traiter les numéros de téléphone dans les résultats
     * 
     * @param int|null $limit Limite le nombre de résultats
     * @param array|null $sqlParameters Paramètres SQL pour filtrer les résultats
     * @param array|null $jsonParameters Paramètres JSON pour filtrer les résultats
     * @param string|null $groupBy Colonne pour le GROUP BY
     * @param string|null $orderBy Colonne pour le ORDER BY
     * @param string $direction Direction du tri (asc/desc)
     * @return array Liste des contacts avec les modifications demandées
     */
    public function getList($limit = null, array $sqlParameters = null, array $jsonParameters = null, $groupBy = null, $orderBy = null, $direction = 'asc')
    {
        // Appel à la méthode parente pour récupérer la liste
        $results = parent::getList($limit, $sqlParameters, $jsonParameters, $groupBy, $orderBy, $direction);
        
        // Traiter chaque résultat
        foreach ($results as &$result) {
            // Si phone2 est vide, on y met le numéro principal au format international
            if (!empty($result->phone)) {
                $result->phone2 = Phone::format($result->phone, $result->country);
            }
            
            // Si phone2_val est vide et phone_val n'est pas vide, on duplique phone_val vers phone2_val
            if (empty($result->phone2_val) && !empty($result->phone_val)) {
                $result->phone2_val = $result->phone_val;
            }
        }
        // Traitement terminé


        return $results;
    }
}
