<?php
// Path: src/app/Models/Model.php

namespace Models;
use Traits\ModelObservable;

use stdClass;
/**
 * Classe Model
 * 
 * Cette classe représente le modèle de base pour interagir avec les tables de la base de données.
 * Elle définit la structure commune et les fonctionnalités de base pour les modèles dérivés.
 */

use Framework\CacheManager;

abstract class Model
{

    use ModelObservable;
    /**
     * @var string Le nom de la table dans la base de données.
     */
    public static $TABLE_NAME;
    /**
     * @var string L'index principal de la table.
     */
    public static $TABLE_INDEX;

    /**
     * @var string L'index de l'objet dans la table.
     */
    public static $OBJ_INDEX;

    /**
     * @var array La structure du schéma de la table.
     */
    public static $SCHEMA;

    /**
     * Retourne le schéma du modèle.
     * 
     * @return array Le schéma du modèle
     */
    public static function getSchema(): array
    {
        return static::$SCHEMA;
    }

    /**
     * Constructeur de la classe Model.
     * 
     * Initialise une nouvelle instance du modèle en configurant le schéma.
     * 
     * @param array $data Les données initiales pour l'instance du modèle.
     */
    public function __construct(array $data = [])
    {
        $this->initializeSchema($data);
    }

    /**
     * Initialise le schéma du modèle.
     * 
     * Cette méthode configure les propriétés du modèle en fonction du schéma défini.
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
     * Hydrate l'instance du modèle avec les données fournies, en traitant le schéma.
     * 
     * @param array $data Les données à utiliser pour hydrater le modèle.
     * @param bool $strict Définit si les données doivent être strictement conformes au schéma.
     * @return Model|stdClass L'instance du modèle hydraté, ou un objet stdClass si strict est true.
     */
    public function hydrate(array $data = [], bool $strict = false)
    {
        $obj = $strict ? [] : $this;

        foreach (static::$SCHEMA as $property => $propertySet) {
            if($data[$propertySet['field']]){
                $value = $data[$propertySet['field']];
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
        if ($value === null) { return; }
        if(isset($propertySet['fieldType']) && isset($propertySet['fieldType'])){
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
        
        $processedResults = [];
        foreach ($results as $result) {
            $processedResults[] = $this->hydrate((array)$result, true);
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
        // print_r('object to prepare for saving');
        // print_r($this);
        $data = $this->prepareDataForSave();

        $whereParameter = !empty($this->{static::$OBJ_INDEX})
            ? [static::$TABLE_INDEX => $this->{static::$OBJ_INDEX}]
            : [];
            // print_r('object finalized for saving');
            // print_r($data);
            // print_r('whereParameter');     
            // print_r($whereParameter);                   
            // // die();
        return $this->performSave($data, $whereParameter);
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

}
