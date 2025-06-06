<?php
// Path: src/app/Models/Model.php

namespace Models;
use Traits\ModelObservable;
use Framework\CacheManager;

use stdClass;
/**
 * Classe abstraite Model
 * 
 * Cette classe représente le modèle de base pour interagir avec les tables de la base de données.
 * Elle implémente un système de cache en trois parties :
 * 1. Gestion basique du cache (updateAll/getAll)
 * 2. Intégration avec CacheObserver pour l'invalidation intelligente
 * 3. Utilisation du trait ModelObservable pour la notification des changements
 *
 * @package Models
 * @abstract
 * @uses \Traits\ModelObservable
 * @uses \Framework\CacheManager
 */
abstract class Model
{
    use ModelObservable;

    /**
     * @var string Le nom de la table dans la base de données
     * @static
     */
    public static $TABLE_NAME;
    /**
     * @var string L'index principal de la table (clé primaire)
     * @static
     */
    public static $TABLE_INDEX;

    /**
     * @var string L'index de l'objet dans la table (peut être différent de TABLE_INDEX)
     * @static
     */
    public static $OBJ_INDEX;

    /**
     * @var array Le schéma de la table définissant la structure et les types des champs
     * Format attendu:
     * [
     *    'property_name' => [
     *        'field' => 'db_field_name',
     *        'type' => 'type_php',
     *        'default' => 'valeur_par_defaut'
     *    ]
     * ]
     * @static
     */
    public static $SCHEMA;

    /**
     * Retourne le schéma du modèle
     * 
     * @static
     * @return array Le schéma complet du modèle
     */
    public static function getSchema(): array
    {
        return static::$SCHEMA;
    }

    /**
     * Constructeur du modèle
     * 
     * Initialise une nouvelle instance en utilisant le schéma défini.
     * Les propriétés sont configurées selon les valeurs par défaut du schéma
     * ou les données fournies.
     * 
     * @param array $data Données initiales pour hydrater le modèle
     */
    public function __construct(array $data = [])
    {
        $this->initializeSchema($data);
    }

    /**
     * Initialise les propriétés du modèle selon le schéma
     * 
     * Pour chaque propriété définie dans le schéma :
     * - Utilise la valeur fournie si elle existe
     * - Sinon utilise la valeur par défaut du schéma
     * - Applique la conversion de type si spécifiée
     * 
     * @param array $data Données pour initialiser les propriétés
     * @return void
     * @access private
     */
    private function initializeSchema($data): void
    {

        foreach(static::$SCHEMA as $property => $propertySet) {
            $default = $propertySet["default"] ?? null;
            if(isset($data[$property])){
                $this->$property = $data[$property];
                if (isset($propertySet['type']) AND  $this->$property !== null ) {
                    settype($this->$property, $propertySet['type']);
                }             
            } else {
                $this->$property = $default;
            }
        }

    }

    /**
     * Hydrate l'instance avec les données fournies
     * 
     * Convertit et assigne les données selon le schéma défini.
     * En mode strict, retourne un nouvel objet au lieu de modifier l'instance.
     * 
     * @param array $data Données à hydrater
     * @param bool $strict Si true, retourne un nouvel objet au lieu de modifier l'instance
     * @return Model|stdClass L'instance hydratée ou un nouvel objet en mode strict
     */
    public function hydrate(array $data = [], bool $strict = false)
    {
        $obj = $strict ? [] : $this;

        foreach (static::$SCHEMA as $property => $propertySet) {
            if(isset($data[$propertySet['field']])){
                $value = $data[$propertySet['field']];
                
                // Convertir les chaînes "NULL" en valeur null réelle
                if ($value === "NULL" || $value === "null") {
                    $value = null;
                }
                
                $value = $this->handleTypeConversion($value, $propertySet);
            } else {
                $value = $propertySet["default"] ?? null;
            }            

            if ($strict) {
                $obj[$property] = $value;
            } else {
                $this->$property = $value;
            }
        }
        return $strict ? (object)$obj : $this;
    }

    /**
     * Gère la conversion de type pour une propriété donnée.
     * 
     * @param mixed $value La valeur à convertir.
     * @param array $propertySet Le schéma de la propriété.
     * @return mixed La valeur convertie.
     */
    private function handleTypeConversion($value, array $propertySet = [])
    {
        // Si la valeur est null, retourner null directement
        if ($value === null) { return null; }
        
        // Convertir les chaînes "NULL" en valeur null réelle
        if ($value === "NULL" || $value === "null") {
            return null;
        }
        
        if(isset($propertySet['fieldType']) && isset($propertySet['type'])){
            if($propertySet['fieldType'] == $propertySet['type']){
                return $value;
            }
            $value = $this->convertType($value, $propertySet['fieldType'], $propertySet['type']);            
        }

        return $value;
    }    

    /**
     * Convertit un tableau en objet.
     * 
     * @param array $temp Le tableau à convertir.
     * @return stdClass L'objet converti.
     */    
    private function toObject(array $temp): stdClass
    {
        $obj = new stdClass();
        foreach ($temp as $property => $value) $obj->$property = $value;
        return $obj;
    }

    /**
     * Affecte les propriétés de l'instance du modèle avec les données fournies.
     * 
     * @param array $temp Les données à utiliser pour affecter les propriétés.
     * @return Model L'instance du modèle.
     */
    private function assignProperties(array $temp): self
    {
        foreach ($temp as $property => $value) $this->$property = $value;
        return $this;
    }

    /**
     * Importe un objet dans l'instance du modèle.
     * 
     * @param object $obj L'objet à importer.
     * @return Model L'instance du modèle.
     */
    public function importObj(object $obj)
    {
        foreach($obj as $k=>$v){
            $this->$k=$v;
        }
        return $this;
    }

    /**
     * Convertit une valeur d'un type à un autre.
     * 
     * @param mixed $v La valeur à convertir.
     * @param string $fromType Le type de la valeur à convertir.
     * @param string $toType Le type de la valeur convertie.
     * @return mixed La valeur convertie.
     */
    private function convertType($v, $fromType, $toType)
    {
        // Si la valeur est null, on retourne directement
        if ($v === null) {
            return null;
        }
        
        // Traiter les chaînes "NULL" comme des valeurs null
        if (is_string($v) && ($v == "NULL" || $v == "null")) {
            return null;
        }
    
        // Si les types sont identiques, on retourne la valeur telle quelle
        if ($fromType === $toType) {
            return $v;
        }
    
        // Cas spécial pour les ENUM - on les traite comme des strings
        if ($toType === "enum") {
            return (string)$v;
        }
    
        // Conversion vers JSON (chaîne) pour les arrays ou objets
        if ($toType === "json") {
            return json_encode($v);
        }
    
        // Conversion string/json vers array
        if (($fromType === "json" || $fromType === "string") && $toType === "array") {
            try {
                $decodedArray = json_decode($v, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $exception) {
                $decodedArray = [];
            }
            return $decodedArray;
        }
    
        // Conversion standard pour les autres types (en cas d’échec, retourner $v)
        $validTypes = ['int', 'float', 'string', 'bool', 'array'];  // Types compatibles avec settype
        if ($fromType !== $toType && in_array($toType, $validTypes, true)) {
            settype($v, $toType);
            return $v;
        }
    
        // Si la conversion échoue ou si le type n'est pas valide, renvoyer la valeur initiale
        return $v;
    }       
    
    /**
     * Récupère un enregistrement de la base de données.
     * 
     * @param int $id L'identifiant de l'objet à récupérer.
     * @return Model|bool L'instance du modèle hydraté. En cas d'échec, retourne `false`.
     */
    public function get(int $id)
    {
        if (empty($id)) {
            return false;
        }
        $sqlParameters =   [static::$TABLE_INDEX=>$id];
        
        $results = Database::instance()->fetch(static::$TABLE_NAME, 1, $sqlParameters);
        if (!empty($results[0])) {
            return $this->hydrate((array)$results[0]);
        } else {
            $this->hydrate();
            return false;
        }
    }

    /**
     * Récupère une liste d'enregistrements de la base de données.
     * 
     * @param int|null $limit Limite le nombre de résultats
     * @param array|null $sqlParameters Paramètres SQL pour filtrer les résultats
     * @param array|null $jsonParameters Paramètres JSON pour filtrer les résultats
     * @param string|null $groupBy Colonne pour le GROUP BY
     * @param string|null $orderBy Colonne pour le ORDER BY
     * @param string $direction Direction du tri (asc/desc)
     * @return array<stdClass> Liste des résultats sous forme d'objets stdClass
     */
    public function getList($limit = null, array $sqlParameters = null, array $jsonParameters = null, $groupBy = null,$orderBy = null,$direction = 'asc')
    {         
        $results = Database::instance()->fetch(static::$TABLE_NAME, $limit, $sqlParameters, $jsonParameters, $groupBy,$orderBy,$direction);

        return $results ? $this->processResults($results) : [];
    }
    
    public function getListDistinctField($column = [],array $jsonKeys = [], $limit = null, array $sqlParameters = [], $groupBy = null ,$reorder = true)
    {
        // Obtenir l'instance du Query Builder via la classe Database personnalisée
        $query = Database::instance()->buildQuery(static::$TABLE_NAME);

        // Appliquer 'distinct' et sélectionner la ou les colonnes
        $query->distinct()->select($column);

        // Appliquer des filtres additionnels SQL si fournis
        if (!empty($sqlParameters)) {
            foreach ($sqlParameters as $key=>$parameter) {
                if(!empty($key) && !is_array($parameter)){
                    $query->where($key, $parameter);
                }
                if (isset($parameter[0], $parameter[1], $parameter[2]) && is_string($parameter[0]) && is_string($parameter[1])) {
                    $query->where($parameter[0], $parameter[1], $parameter[2]);
                }                
            }            
        }
        //die();

        // Si des paramètres JSON sont fournis pour filtrer, les traiter ici
        // Cette partie serait spécifique à vos besoins et pourrait nécessiter une logique complexe
        // selon la structure de vos données JSON et les types de filtres que vous souhaitez appliquer.

        // Sélectionner les valeurs DISTINCT pour les clés JSON spécifiques dans la colonne 'parameters'
        // Parcourir chaque colonne JSON pour extraire les clés spécifiées
        foreach ($jsonKeys as $column => $keys) {
            foreach ($keys as $key) {
                // Construire la requête pour extraire les valeurs distinctes pour chaque clé JSON spécifiée
                // dans la colonne JSON. Adaptation nécessaire pour la syntaxe spécifique au SGBD utilisé.
                $query->addSelect(Database::instance()->DB::raw(" JSON_UNQUOTE(JSON_EXTRACT($column, '$.\"$key\"')) AS $key"));
            }
        }

        // Gérer la logique groupBy si nécessaire
        if ($groupBy) {
            $query->groupBy($groupBy);
        }

        // Appliquer une limite si elle est fournie
        if ($limit) {
            $query->limit($limit);
        }

        // Exécuter la requête et récupérer les résultats
        $results = $query->get()->toArray();

        // Transformer les résultats en un tableau simple, en utilisant le nom des propriétés de l'objet
        return $reorder ? $this->transformResults($results) : $results;


    }

    function transformResults($results){
        // Transformer les résultats en un tableau simple        
        $simpleResults = [];
        if(is_array($results)){
            foreach ($results as $result) {
                foreach ($result as $key => $value) {
                    if(!empty($value)){
                        $simpleResults[$key][] = $value;
                    }
                }
            }
        }
        return $simpleResults;
    }    

    /**
     * Traite les résultats de la base de données.
     * 
     * @param array $results Les résultats à traiter.
     * @return array Les résultats traités.
     */
    private function processResults(array $results): array
    {
        // Précalcul des champs avec valeur par défaut NULL pour optimisation
        static $nullDefaultFields = null;
        if ($nullDefaultFields === null) {
            $nullDefaultFields = [];
            foreach (static::$SCHEMA as $property => $propertySet) {
                if (isset($propertySet['default']) && $propertySet['default'] === null) {
                    $nullDefaultFields[$propertySet['field']] = $property;
                }
            }
        }
        
        $processedResults = [];
        foreach ($results as $result) {
            $resultArray = (array)$result;
            
            // Traitement optimisé des valeurs
            foreach ($resultArray as $key => $value) {
                // Traitement des chaînes "NULL"
                if ($value === "NULL" || $value === "null") {
                    $resultArray[$key] = null;
                    continue;
                }
                
                // Traitement des chaînes vides uniquement pour les champs avec default=null
                if ($value === "" && isset($nullDefaultFields[$key])) {
                    $resultArray[$key] = null;
                }
            }
            
            $processedResults[] = $this->hydrate($resultArray, true);
        }
        return $processedResults;
    }
    


    /**
     * Sauvegarde l'instance du modèle dans la base de données.
     * 
     * Cette méthode détermine si elle doit effectuer une insertion ou une mise à jour dans la base de données.
     * Si un identifiant de l'objet est présent (`$OBJ_INDEX`), elle effectue une mise à jour ; sinon, elle insère un nouvel enregistrement.
     * Après une insertion réussie, l'identifiant généré est affecté à l'objet.
     * 
     * @return mixed Le résultat de l'opération de base de données. En cas de succès, retourne l'identifiant de l'objet inséré ou mis à jour.
     *               En cas d'échec, retourne `false`.
     */
    public function save()
    {
        $data = $this->prepareDataForSave();
        $whereParameter = !empty($this->{static::$OBJ_INDEX}) ? [static::$TABLE_INDEX => $this->{static::$OBJ_INDEX}] : [];
        $result = $this->performSave($data, $whereParameter);
        return $result;
    }


    /**
     * Prépare les données pour l'opération de sauvegarde.
     * 
     * @return array Les données préparées.
     */
    private function prepareDataForSave(): array
    {
        $data = [];
        foreach ($this as $k => $v) {
            if (array_key_exists($k, static::$SCHEMA)) {
                // echo '<li>convert '.$k.' from '.static::$SCHEMA[$k]['type'].' to '.static::$SCHEMA[$k]['fieldType'].'<br/>';
                $data[static::$SCHEMA[$k]['field']] = $this->convertType($v, static::$SCHEMA[$k]['type'], static::$SCHEMA[$k]['fieldType']);
            }
        }
        return $data;
    }


    /**
     * Exécute l'opération de sauvegarde de l'instance du modèle dans la base de données.
     * 
     * Cette méthode détermine si elle doit effectuer une insertion ou une mise à jour dans la base de données.
     * Si un identifiant de l'objet est présent (`$OBJ_INDEX`), elle effectue une mise à jour ; sinon, elle insère un nouvel enregistrement.
     * Après une insertion réussie, l'identifiant généré est affecté à l'objet.
     * 
     * @param array $data Les données à sauvegarder. Doivent correspondre au schéma de la table.
     * @param array $whereParameter Les paramètres conditionnels pour la requête de mise à jour.
     *                              Utilisés pour identifier l'enregistrement à mettre à jour.
     * 
     * @return mixed Le résultat de l'opération de base de données. En cas de succès, retourne l'identifiant de l'objet inséré ou mis à jour.
     *               En cas d'échec, retourne `false`.
     */    
    private function performSave(array $data, array $whereParameter)
    {
        $isNew = empty($this->{static::$OBJ_INDEX});
        $result = Database::instance()->updateOrInsert(static::$TABLE_NAME, static::$TABLE_INDEX, $whereParameter, $data);
        if ($result !== false) {
            // Mise à jour de l'ID si c'est une nouvelle insertion
            if ($isNew) {
                $this->{static::$OBJ_INDEX} = $result;
            }
            // Notification des observateurs seulement si l'opération a réussi
            $this->notifyObservers($isNew ? 'created' : 'updated');
        }
        return $result;
    }

    /**
     * Supprime un enregistrement de la base de données.
     * 
     * @param int $id L'identifiant de l'objet à supprimer.
     * @return bool Le succès ou l'échec de l'opération.
     */
    public function delete(int $id)
    {
        if (empty($id)) {
            return false;
        }

        $sqlParameters = [static::$TABLE_INDEX => $id];
        $result = Database::instance()->delete(static::$TABLE_NAME, $sqlParameters);
        
        if ($result) {
            $this->notifyObservers('deleted');
        }

        return $result;
    }
    /**
     * supprime plusieurs enregistrements de la base de données.
     *
     * @param array $sqlParameters
     * @return bool Le succès ou l'échec de l'opération.
     */

    public function deleteList(array $sqlParameters = [])
    {
        return Database::instance()->delete(static::$TABLE_NAME, $sqlParameters);
    }

    public function getClassName() {
        return substr(strrchr(get_called_class(), '\\'), 1);
    }

    public function updateAll($list){
        $cacheManager = CacheManager::instance(); // Accès à l'instance du singleton
        $cache = $cacheManager->getCacheAdapter(); // Obtention de l'adaptateur de cache
        $testListCache = $cache->getItem($this->getClassName().'List');
        $testListCache->set($list);
        // Ne pas définir de durée d'expiration pour un TTL indéfini
        $cache->save($testListCache);
    }

    public function getAll($limit = 100000){
        $cacheManager = CacheManager::instance(); // Accès à l'instance du singleton
        $cache = $cacheManager->getCacheAdapter(); // Obtention de l'adaptateur de cache
        $testListCache = $cache->getItem($this->getClassName().'List');
        if ($testListCache->isHit()) {
            $list = $testListCache->get();
        } else {
            $list = $this->getList($limit);
            $this->updateAll($list);
        }
        $cacheManager->logDebugBar($this->getClassName().'List', $list,0); // Log dans DebugBar sans durée d'expiration
        return $list;
    }    

    /**
     * Convertit les noms de propriétés en noms de champs de la base de données
     * 
     * @param array $parameters Paramètres avec les noms de propriétés
     * @param array $schema Schéma contenant le mapping propriété -> champ
     * @return array Paramètres avec les noms de champs
     */
    public static function convertToFieldNames(array $parameters, array $schema): array
    {
        if (empty($parameters)) {
            return [];
        }

        $converted = [];
        foreach ($parameters as $key => $value) {
            // Si la clé existe dans le schéma, utiliser le nom du champ
            if (isset($schema[$key])) {
                $converted[$schema[$key]['field']] = $value;
            } else {
                // Sinon garder la clé telle quelle
                $converted[$key] = $value;
            }
        }
        return $converted;
    }

    /**
     * Méthode magique pour définir des propriétés dynamiques
     * 
     * Permet d'ajouter des propriétés qui ne sont pas définies dans le schéma
     * en les stockant dans un tableau spécial pour les propriétés dynamiques.
     * 
     * @param string $name Nom de la propriété
     * @param mixed $value Valeur de la propriété
     * @return void
     */
    public function __set(string $name, $value): void
    {
        // Vérifier si la propriété est définie dans le schéma
        if (isset(static::$SCHEMA[$name])) {
            // Si oui, utiliser le type défini dans le schéma
            $this->{$name} = $value;
            if (isset(static::$SCHEMA[$name]["type"]) && static::$SCHEMA[$name]["type"] !== "mixed") {
                settype($this->{$name}, static::$SCHEMA[$name]["type"]);
            }
        } else {
            // Si non, permettre l'ajout de propriétés dynamiques
            // en les stockant dans un tableau spécial pour les propriétés dynamiques
            if (!isset($this->_dynamicProperties)) {
                $this->_dynamicProperties = [];
            }
            $this->_dynamicProperties[$name] = $value;
        }
    }

    /**
     * Méthode magique pour récupérer des propriétés dynamiques
     * 
     * Permet d'accéder aux propriétés qui ne sont pas définies dans le schéma
     * mais qui ont été ajoutées dynamiquement.
     * 
     * @param string $name Nom de la propriété
     * @return mixed Valeur de la propriété ou null si elle n'existe pas
     */
    public function __get(string $name)
    {
        // Vérifier si la propriété est définie dans le schéma
        if (isset(static::$SCHEMA[$name])) {
            return $this->{$name} ?? null;
        }
        
        // Vérifier si la propriété existe dans les propriétés dynamiques
        if (isset($this->_dynamicProperties) && isset($this->_dynamicProperties[$name])) {
            return $this->_dynamicProperties[$name];
        }
        
        return null;
    }
}
