<?php
namespace Controllers\Api;

use Framework\Controller;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Framework\CacheManager;
use Models;
use Classes\Logger;

/**
 * Contrôleur pour l'API V2
 * 
 * Ce contrôleur gère les requêtes API REST pour les ressources du CRM.
 * Il supporte les opérations CRUD (Create, Read, Update, Delete) sur les modèles,
 * avec des fonctionnalités avancées comme le filtrage, le tri et la pagination.
 * 
 * Endpoints disponibles :
 * - GET /apiv2/leadmanager : Liste des leads
 * - GET /apiv2/leadmanager/{id} : Détails d'un lead
 * - POST /apiv2/leadmanager : Création d'un lead
 * - PUT /apiv2/leadmanager/{id} : Mise à jour d'un lead
 * - DELETE /apiv2/leadmanager/{id} : Suppression d'un lead
 * 
 * Paramètres de liste :
 * - page : Numéro de page (défaut: 1)
 * - limit : Nombre d'éléments par page (défaut: 100, max: 1000)
 * - sort : Champ de tri
 * - order : Direction du tri (asc/desc)
 * - filter : Filtres au format JSON
 * 
 * Exemples de filtrage pour LeadManager (nouvelle syntaxe recommandée) :
 * 
 * 1. Filtre simple sur un champ :
 *    ?filter={"field":"leadId","operator":"=","value":123}
 * 
 * 2. Filtre avec opérateur :
 *    ?filter={"field":"createdAt","operator":">","value":"2025-01-01"}
 * 
 * 3. Filtre sur champ imbriqué (relation) :
 *    ?filter={"field":"contact[phone]","operator":"=","value":"0612345678"}
 *    ?filter={"field":"contact[email]","operator":"LIKE","value":"%@gmail.com"}
 * 
 * 4. Filtres multiples (AND/OR) :
 *    ?filter={"AND":[{"field":"contact[firstName]","operator":"=","value":"John"},{"field":"project[address][city]","operator":"=","value":"Paris"},{"field":"sales[price]","operator":">","value":1000}]}
 * 
 * Exemples de tri pour LeadManager :
 * 
 * 1. Tri simple :
 *    ?sort=leadId&order=desc
 * 
 * 2. Tri sur champ imbriqué :
 *    ?sort=contact[lastName]&order=asc
 * 
 * Structure du schéma LeadManager :
 * - leadId (int)
 * - createdAt (datetime)
 * - updatedAt (datetime)
 * - contact (relation)
 *   - civility (string)
 *   - firstName (string)
 *   - lastName (string)
 *   - email (string)
 *   - phone (string)
 *   - phone2 (string)
 * - project (relation)
 *   - address
 *     - address1 (string)
 *     - address2 (string)
 *     - postalCode (string)
 *     - city (string)
 *     - country (string)
 *   - campaign (relation)
 *     - campaignId (int)
 *     - name (string)
 *     - details (string)
 *     - price (float)
 *   - questions (object)
 *     - [label] (object)
 *       - questionId (int)
 *       - label (string)
 *       - question (string)
 *       - value (mixed)
 * - purchase (relation)
 *   - exists (boolean)
 *   - data
 *     - purchaseId (int)
 *     - timestamp (datetime)
 *     - price (float)
 * - sales (relation)
 *   - exists (boolean)
 *   - data
 *     - saleId (int)
 *     - timestamp (datetime)
 *     - price (float)
 * 
 * Exemples de filtres spéciaux :
 * 
 * 5. Filtre NULL :
 *    ?filter={"field":"contact[email]","operator":"IS","value":null}
 *    ?filter={"field":"contact[email]","operator":"IS NOT","value":null}
 * 
 * 6. Filtre complexe AND/OR :
 *    ?filter={"AND":[{"field":"timestamp","operator":">","value":0},{"OR":[{"field":"project[status]","operator":"=","value":"valid"},{"field":"project[status]","operator":"=","value":"deversoir"}]}]}
 * 
 * Réponse type :
 * {
 *   "success": true,
 *   "message": "",
 *   "pagination": {
 *     "page": 1,
 *     "limit": 100,
 *     "sort": "leadId",
 *     "order": "desc",
 *     "total": 50
 *   },
 *   "filters": {
 *     "sql": [...],
 *     "json": []
 *   },
 *   "result": [...]
 * }
 */
class ApiV2Controller extends Controller 
{
    /** @var array Opérateurs valides pour le filtrage */
    private const VALID_OPERATORS = ['=', '>', '<', '>=', '<=', '!=', 'LIKE', 'IN', 'IS', 'IS NOT'];
    
    /** @var array Directions valides pour le tri */
    private const VALID_SORT_DIRECTIONS = ['asc', 'desc'];

    /** @var array Mapping des noms de ressources pour gérer la casse */
    private const RESOURCE_MAPPING = [
        'leadmanager' => 'LeadManager'

        // Ajout explicite de tous les adapters principaux
    ];

    private $cacheManager;
    
    /**
     * Constructeur du contrôleur
     * 
     * Initialise les propriétés et configure les headers HTTP pour l'API.
     * 
     * @param HttpRequest $httpRequest Objet de requête HTTP
     * @param HttpResponse $httpResponse Objet de réponse HTTP
     */
    public function __construct(HttpRequest $httpRequest, HttpResponse $httpResponse)
    {
        parent::__construct($httpRequest, $httpResponse);
        $this->cacheManager = CacheManager::instance();
        
        // Configuration des headers pour l'API
        $this->_httpResponse->headers->set('Content-Type', 'application/json');
        $this->_httpResponse->headers->set('Access-Control-Allow-Origin', '*');
        $this->_httpResponse->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
        $this->_httpResponse->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    /**
     * Point d'entrée principal de l'API
     * 
     * Traite toutes les requêtes entrantes et les route vers les handlers appropriés
     * en fonction de la méthode HTTP et des paramètres.
     * 
     * @param array $params Paramètres de la requête
     * @return array Réponse formatée en JSON
     */
    public function handleRequest($params = [])
    {
        try {
            // Récupération des paramètres de la requête
            $resource = $params['resource'] ?? null;
            $id = $params['id'] ?? null;
            $limit = $params['limit'] ?? 100;
            $requestData = $this->_httpRequest->getParams();
            
            // Correction de la casse de la ressource
            $resourceLower = strtolower($resource);
            if (isset(self::RESOURCE_MAPPING[$resourceLower])) {
                $resource = self::RESOURCE_MAPPING[$resourceLower];
            }

            // Déterminer la méthode en fonction de la requête HTTP et des paramètres
            $httpMethod = strtolower($this->_httpRequest->getMethod());
            $method = $params['method'] ?? null;

            if ($method === 'list' || ($httpMethod === 'get' && !$id)) {
                $method = 'list';
            } elseif ($method === 'set' || in_array($httpMethod, ['post', 'put'])) {
                $method = 'set';
            } elseif ($method === 'delete' || $httpMethod === 'delete') {
                $method = 'delete';
            } else {
                $method = 'get';
            }

            // Instanciation du modèle correspondant à la ressource
            $modelClass = "\\Models\\{$resource}";
            if (!class_exists($modelClass)) {
                return [
                    'success' => false,
                    'message' => "Ressource inconnue: {$resource}"
                ];
            }
            
            $model = new $modelClass();

            // Routing vers le handler approprié
            switch ($method) {
                case 'list':
                    return $this->handleList($model, $limit, $params);
                
                case 'get':
                    return $this->handleGet($model, $id);
                
                case 'set':
                    if ($id) {
                        return $this->handleUpdate($model, $id, $requestData);
                    } else {
                        return $this->handleCreate($model, $requestData);
                    }
                
                case 'delete':
                    return $this->handleDelete($model, $id);
                
                default:
                    return [
                        'success' => false,
                        'message' => "Méthode non supportée: {$method}"
                    ];
            }
        } catch (\Exception $e) {
            Logger::critical("Erreur API", ['error' => $e->getMessage()], $e);
            return [
                'success' => false,
                'message' => "Erreur serveur: {$e->getMessage()}"
            ];
        }
    }

    /**
     * Gère la récupération d'une liste d'éléments
     * 
     * Supporte :
     * - Pagination (page, limit)
     * - Tri (sort, order)
     * - Filtrage (filter)
     * 
     * @param object $model Instance du modèle
     * @param int $limit Nombre maximum d'éléments à retourner
     * @param array $params Paramètres de la requête
     * @return array Réponse contenant la liste et les métadonnées
     */
    private function handleList($model, $limit, $params): array
    {
        try {
            // Configuration de base
            $config = $this->getListConfig($params, $limit);
            if (!$config['success']) {
                return $config;
            }

            // Traitement des filtres
            $filters = $this->parseFilters($params['filter'] ?? null, $model);
            if (!$filters['success']) {
                return $filters;
            }

            // Récupération des résultats
            $results = $model->getList(
                $config['limit'], 
                $filters['parameters'] ?? [], 
                [], 
                null, 
                $config['orderBy'], 
                $config['direction']
            );
            
            return [
                'success' => true,
                'message' => '',
                'pagination' => [
                    'page' => $config['page'],
                    'limit' => $config['limit'],
                    'sort' => $params['sort'] ?? null,
                    'order' => $config['direction'],
                    'total' => count($results)
                ],
                'filters' => [
                    'sql' => $filters['parameters'] ?? [],
                    'json' => []
                ],
                'result' => $results
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors de la récupération de la liste : {$e->getMessage()}"
            ];
        }
    }

    /**
     * Configure les paramètres de liste (pagination et tri)
     * 
     * @param array $params Paramètres de la requête
     * @param int $limit Limite par défaut
     * @return array Configuration validée
     */
    private function getListConfig(array $params, int $limit): array
    {
        // Pagination
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = min(1000, max(1, (int)$limit));
        
        // Tri
        $sortField = $params['sort'] ?? null;
        $direction = strtolower($params['order'] ?? 'asc');
        
        if (!in_array($direction, self::VALID_SORT_DIRECTIONS)) {
            $direction = 'asc';
        }
        
        // Traitement du champ de tri pour les champs imbriqués
        $orderBy = $sortField;
        if ($sortField && isset($params['resource'])) {
            // Récupérer le modèle
            $resourceLower = strtolower($params['resource']);
            if (isset(self::RESOURCE_MAPPING[$resourceLower])) {
                $resource = self::RESOURCE_MAPPING[$resourceLower];
                $modelClass = "\\Models\\{$resource}";
                if (class_exists($modelClass)) {
                    $model = new $modelClass();
                    // Traiter le champ de tri comme un champ de filtre pour la conversion
                    if (preg_match('/^(\w+)\[(\w+)\]$/', $sortField, $matches)) {
                        $fieldInfo = $this->parseNestedField($matches[1], $matches[2], $model);
                        if ($fieldInfo['success']) {
                            $orderBy = $fieldInfo['field'];
                        }
                    } else {
                        $fieldInfo = $this->parseSimpleField($sortField, $model);
                        if ($fieldInfo['success']) {
                            $orderBy = $fieldInfo['field'];
                        }
                    }
                }
            }
        }

        return [
            'success' => true,
            'page' => $page,
            'limit' => $limit,
            'orderBy' => $orderBy,
            'direction' => $direction
        ];
    }

    /**
     * Parse et valide les filtres de la requête
     * 
     * Supporte :
     * - Filtres simples : {"field": "value"}
     * - Filtres avec opérateur : {"field": {"operator": "=", "value": "test"}}
     * - Filtres imbriqués : {"relation[field]": "value"}
     * - Format direct : {"field": "project[status]", "operator": "=", "value": "valid"}
     * 
     * @param mixed $filterData Données de filtre (JSON ou array)
     * @param object $model Instance du modèle pour validation
     * @return array Filtres parsés et validés
     */
    private function parseFilters($filterData, $model): array
    {
        if (!$filterData) {
            return ['success' => true, 'parameters' => []];
        }
        try {
            Logger::debug("Filtre brut", ['data' => $filterData]);
            $raw = is_string($filterData)
                ? json_decode($filterData, true, 512, JSON_THROW_ON_ERROR)
                : $filterData;
            if (!is_array($raw)) {
                return ['success' => false, 'message' => 'Le filtre doit être un objet JSON valide'];
            }
            
            // Format direct : {"field": "project[status]", "operator": "=", "value": "valid"}
            if (isset($raw['field'], $raw['operator'], $raw['value'])) {
                $p = $this->buildFilterParameter(
                    $raw['field'],
                    ['operator' => $raw['operator'], 'value' => $raw['value']],
                    $model
                );
                if ($p) {
                    Logger::debug("Paramètre de filtre direct généré", ['parameter' => $p]);
                    return ['success' => true, 'parameters' => [$p]];
                } else {
                    return ['success' => false, 'message' => 'Filtre invalide'];
                }
            }
            
            // Déterminer les entrées et la logique
            if (isset($raw['AND']) && is_array($raw['AND'])) {
                $entries = $raw['AND'];
                $logic = 'AND';
            } elseif (isset($raw['OR']) && is_array($raw['OR'])) {
                $entries = $raw['OR'];
                $logic = 'OR';
            } elseif (array_keys($raw) === range(0, count($raw) - 1)) {
                $entries = $raw;
                $logic = 'AND';
            } else {
                // Ancien format key=>value
                $params = $this->buildFilterParameters($raw, $model);
                Logger::debug("Paramètres de filtre générés", ['parameters' => $params]);
                return ['success' => true, 'parameters' => $params];
            }
            $params = [];
            foreach ($entries as $entry) {
                // OR imbriqué
                if (isset($entry['OR']) && is_array($entry['OR'])) {
                    $sub = [];
                    foreach ($entry['OR'] as $cond) {
                        if (!isset($cond['field'], $cond['operator'], $cond['value'])) {
                            continue;
                        }
                        $p = $this->buildFilterParameter(
                            $cond['field'],
                            ['operator' => $cond['operator'], 'value' => $cond['value']],
                            $model
                        );
                        if ($p) {
                            $sub[] = $p;
                        }
                    }
                    if (!empty($sub)) {
                        $params[] = ['OR', $sub];
                    }
                }
                // Condition simple
                elseif (isset($entry['field'], $entry['operator'], $entry['value'])) {
                    $p = $this->buildFilterParameter(
                        $entry['field'],
                        ['operator' => $entry['operator'], 'value' => $entry['value']],
                        $model
                    );
                    if ($p) {
                        $params[] = $p;
                    }
                }
            }
            // Si logique OR au top, envelopper le groupe
            if (isset($logic) && $logic === 'OR' && !empty($params)) {
                $params = [['OR', $params]];
            }
            Logger::debug("Paramètres de filtre générés", ['parameters' => $params]);
            return ['success' => true, 'parameters' => $params];

        } catch (\Exception $e) {
            Logger::critical("Erreur parsing filtres", ['error' => $e->getMessage()], $e);
            return ['success' => false, 'message' => "Erreur lors du traitement des filtres : {$e->getMessage()}"];
        }
    }

    /**
     * Construit les paramètres de filtre SQL
     * 
     * @param array $filters Filtres bruts
     * @param object $model Instance du modèle
     * @return array Paramètres SQL
     */
    private function buildFilterParameters(array $filters, $model): array
    {
        $parameters = [];
        foreach ($filters as $field => $value) {
            $parameter = $this->buildFilterParameter($field, $value, $model);
            if ($parameter) {
                $parameters[] = $parameter;
            }
        }
        return $parameters;
    }

    /**
     * Construit un paramètre de filtre SQL individuel
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur ou configuration du filtre
     * @param object $model Instance du modèle
     * @return array|null Paramètre SQL ou null si invalide
     */
    private function buildFilterParameter(string $field, mixed $value, $model): ?array
    {
        // Log des paramètres d'entrée
        Logger::debug("Construction filtre", [
            'field' => $field,
            'value' => $value
        ]);
        
        // Extraction du champ et de la relation si nécessaire
        $fieldParts = $this->parseFieldName($field, $model);
        Logger::debug("Field parts", ['parts' => $fieldParts]);
        
        if (!$fieldParts['success']) {
            Logger::debug("Échec parsing du champ", ['field' => $field]);
            return null;
        }

        // Construction du paramètre de filtre
        if (is_array($value) && isset($value['operator'], $value['value'])) {
            if (!in_array($value['operator'], self::VALID_OPERATORS)) {
                Logger::debug("Opérateur invalide", ['operator' => $value['operator']]);
                return null;
            }
            $result = [$fieldParts['field'], $value['operator'], $value['value']];
        } else {
            $result = [$fieldParts['field'], '=', $value];
        }
        
        Logger::debug("Paramètre de filtre construit", ['result' => $result]);
        return $result;
    }

    /**
     * Parse un nom de champ, gérant les champs simples et imbriqués
     * 
     * @param string $field Nom du champ
     * @param object $model Instance du modèle
     * @return array Informations sur le champ
     */
    private function parseFieldName(string $field, $model): array
    {
        // Champ imbriqué (ex: contact[phone])
        if (preg_match('/^(\w+)\[(\w+)\]$/', $field, $matches)) {
            return $this->parseNestedField($matches[1], $matches[2], $model);
        }

        // Champ simple
        return $this->parseSimpleField($field, $model);
    }

    /**
     * Parse un champ imbriqué (relation[field])
     * 
     * @param string $relation Nom de la relation (ex: contact)
     * @param string $field Nom du champ dans la relation (ex: phone)
     * @param object $model Instance du modèle
     * @return array Informations sur le champ
     */
    private function parseNestedField(string $relation, string $field, $model): array
    {
        Logger::debug("parseNestedField - Entrée", [
            'relation' => $relation,
            'field' => $field,
            'model_class' => get_class($model)
        ]);

        // Récupérer l'adaptateur correspondant à la relation
        $adapterClass = "\\Models\\Adapters\\" . ucfirst($relation) . "Adapter";
        
        if (!class_exists($adapterClass)) {
            Logger::debug("parseNestedField - Adaptateur non trouvé", [
                'adapter' => $adapterClass
            ]);
            return ['success' => false];
        }

        Logger::debug("parseNestedField - Schéma de l'adaptateur", [
            'adapter' => $adapterClass,
            'schema' => $adapterClass::getSchema()
        ]);

        // Vérifier d'abord dans data.fields si présent
        if (isset($adapterClass::getSchema()['data']) && 
            isset($adapterClass::getSchema()['data']['type']) && 
            $adapterClass::getSchema()['data']['type'] === 'collection' &&
            isset($adapterClass::getSchema()['data']['fields'][$field])) {
            
            $fieldConfig = $adapterClass::getSchema()['data']['fields'][$field];
            Logger::debug("parseNestedField - Champ trouvé dans data.fields", [
                'field' => $field,
                'config' => $fieldConfig
            ]);
            return [
                'success' => true,
                'field' => $fieldConfig['table'] . '.' . $fieldConfig['field']
            ];
        }

        // Chercher le champ dans le schéma de l'adaptateur
        if (isset($adapterClass::getSchema()[$field])) {
            $fieldConfig = $adapterClass::getSchema()[$field];
            Logger::debug("parseNestedField - Champ trouvé directement", [
                'field' => $field,
                'config' => $fieldConfig
            ]);
            
            // Déterminer la table à utiliser
            $table = 'lead'; // Table par défaut
            
            // Essayer de récupérer la table depuis la classe du modèle correspondant
            $relatedModelClass = "\\Models\\" . ucfirst($relation);
            if (class_exists($relatedModelClass)) {
                if (defined($relatedModelClass . '::$TABLE_NAME')) {
                    $table = $relatedModelClass::$TABLE_NAME;
                }
            }
            
            return [
                'success' => true,
                'field' => $table . '.' . $fieldConfig['field']
            ];
        }

        // Chercher dans les groupes au niveau racine et dans data.fields
        $searchInGroups = function($schema) use ($field) {
            foreach ($schema as $group) {
                if (isset($group['type']) && $group['type'] === 'group' && isset($group['fields'][$field])) {
                    return $group['fields'][$field];
                }
            }
            return null;
        };

        // Chercher dans les groupes au niveau racine
        $fieldConfig = $searchInGroups($adapterClass::getSchema());
        if ($fieldConfig) {
            Logger::debug("parseNestedField - Champ trouvé dans un groupe racine", [
                'field' => $field,
                'config' => $fieldConfig
            ]);
            return [
                'success' => true,
                'field' => $fieldConfig['table'] . '.' . $fieldConfig['field']
            ];
        }

        // Chercher dans les groupes de data.fields
        if (isset($adapterClass::getSchema()['data']['fields'])) {
            $fieldConfig = $searchInGroups($adapterClass::getSchema()['data']['fields']);
            if ($fieldConfig) {
                Logger::debug("parseNestedField - Champ trouvé dans un groupe de data.fields", [
                    'field' => $field,
                    'config' => $fieldConfig
                ]);
                return [
                    'success' => true,
                    'field' => $fieldConfig['table'] . '.' . $fieldConfig['field']
                ];
            }
        }

        Logger::debug("parseNestedField - Champ non trouvé", [
            'field' => $field,
            'available_fields' => array_keys($adapterClass::getSchema())
        ]);
        
        return ['success' => false];
    }

    /**
     * Parse un champ simple
     * 
     * @param string $field Nom du champ
     * @param object $model Instance du modèle
     * @return array Informations sur le champ
     */
    private function parseSimpleField(string $field, $model): array
    {
        $schema = method_exists($model, 'getSchema') ? $model::getSchema() : [];
        
        if (isset($schema[$field])) {
            return [
                'success' => true,
                'field' => $schema[$field]['field'] ?? $field
            ];
        }
        
        return [
            'success' => true,
            'field' => $field
        ];
    }

    /**
     * Gère la récupération d'un élément
     * 
     * @param object $model Instance du modèle
     * @param int $id Identifiant de l'élément
     * @return array Réponse contenant l'élément
     */
    private function handleGet($model, $id): array
    {
        try {
            $result = $model->get((int)$id);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Ressource non trouvée'
                ];
            }

            return [
                'success' => true,
                'message' => '',
                'result' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Gère la création d'un élément
     * 
     * @param object $model Instance du modèle
     * @param array $data Données de l'élément
     * @return array Réponse contenant l'élément créé
     */
    private function handleCreate($model, $data): array
    {
        try {
            // Nettoyer les données entrantes
            unset($data['resource'], $data['method'], $data['id']);
            
            foreach ($data as $key => $value) {
                if (property_exists($model, $key)) {
                    $model->$key = $value;
                }
            }

            $result = $model->save();

            if (!$result) {
                return [
                    'success' => false,
                    'message' => "Échec de la création"
                ];
            }

            return [
                'success' => true,
                'message' => "Ressource créée",
                'result' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Gère la mise à jour d'un élément
     * 
     * @param object $model Instance du modèle
     * @param int $id Identifiant de l'élément
     * @param array $data Données de l'élément
     * @return array Réponse contenant l'élément mis à jour
     */
    private function handleUpdate($model, $id, $data): array
    {
        try {
            $existingModel = $model->get((int)$id);
            if (!$existingModel) {
                return [
                    'success' => false,
                    'message' => 'Ressource non trouvée'
                ];
            }

            // S'assurer que l'identifiant principal est correctement défini
            $objIndex = $model::$OBJ_INDEX;
            $existingModel->$objIndex = (int)$id;
            
            // Mise à jour des champs
            foreach ($data as $key => $value) {
                if (property_exists($existingModel, $key)) {
                    $existingModel->$key = $value;
                }
            }

            // Sauvegarde des modifications
            $result = $existingModel->save();
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Ressource mise à jour',
                    'result' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Gère la suppression d'un élément
     * 
     * @param object $model Instance du modèle
     * @param int $id Identifiant de l'élément
     * @return array Réponse de suppression
     */
    private function handleDelete($model, $id): array
    {
        try {
            $result = $model->delete((int)$id);

            if (!$result) {
                return [
                    'success' => false,
                    'message' => "Échec de la suppression"
                ];
            }

            return [
                'success' => true,
                'message' => "Ressource supprimée"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ];
        }
    }
}
