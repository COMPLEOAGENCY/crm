<?php
namespace Controllers\Api;

use Framework\Controller;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Framework\CacheManager;
use Models;

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
 * Exemples de filtrage pour LeadManager :
 * 
 * 1. Filtre simple sur un champ :
 *    ?filter={"leadId": 123}
 * 
 * 2. Filtre avec opérateur :
 *    ?filter={"createdAt": {"operator": ">", "value": "2025-01-01"}}
 * 
 * 3. Filtre sur champ imbriqué (relation) :
 *    ?filter={"contact[phone]": "0612345678"}
 *    ?filter={"contact[email]": {"operator": "LIKE", "value": "%@gmail.com"}}
 * 
 * 4. Filtres multiples :
 *    ?filter={
 *      "contact[firstName]": "John",
 *      "project[address][city]": "Paris",
 *      "sales[price]": {"operator": ">", "value": 1000}
 *    }
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
            // Vérification de la ressource
            if (!$resource || !class_exists("\\Models\\" . ucfirst($resource))) {
                return [
                    'success' => false,
                    'message' => 'Resource non définie ou invalide'
                ];
            }

            // Création de l'instance du modèle
            $modelClass = "\\Models\\" . ucfirst($resource);
            $model = new $modelClass();

            // Traitement selon la méthode
            switch ($method) {
                case 'list':
                    return $this->handleList($model, $limit, $requestData);

                case 'get':
                    if (!$id) {
                        return [
                            'success' => false,
                            'message' => 'ID requis pour la méthode GET'
                        ];
                    }
                    return $this->handleGet($model, $id);

                case 'set':
                    if ($id) {
                        return $this->handleUpdate($model, $id, $requestData);
                    }
                    return $this->handleCreate($model, $requestData);

                case 'delete':
                    if (!$id) {
                        return [
                            'success' => false,
                            'message' => 'ID requis pour la méthode DELETE'
                        ];
                    }
                    return $this->handleDelete($model, $id);

                default:
                    return [
                        'success' => false,
                        'message' => 'Méthode non supportée'
                    ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
                'debug' => isset($params['debug']) ? $e->getTrace() : null
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
        $orderBy = $params['sort'] ?? null;
        $direction = strtolower($params['order'] ?? 'asc');
        
        if (!in_array($direction, self::VALID_SORT_DIRECTIONS)) {
            $direction = 'asc';
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
            $filters = is_string($filterData) 
                ? json_decode($filterData, true, 512, JSON_THROW_ON_ERROR) 
                : $filterData;

            if (!is_array($filters)) {
                return [
                    'success' => false,
                    'message' => 'Le filtre doit être un objet JSON valide'
                ];
            }

            return ['success' => true, 'parameters' => $this->buildFilterParameters($filters, $model)];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Erreur lors du traitement des filtres : {$e->getMessage()}"
            ];
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
        // Extraction du champ et de la relation si nécessaire
        $fieldParts = $this->parseFieldName($field, $model);
        if (!$fieldParts['success']) {
            return null;
        }

        // Construction du paramètre de filtre
        if (is_array($value) && isset($value['operator'], $value['value'])) {
            if (!in_array($value['operator'], self::VALID_OPERATORS)) {
                return null;
            }
            return [$fieldParts['field'], $value['operator'], $value['value']];
        }

        return [$fieldParts['field'], '=', $value];
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
     * @param string $relation Nom de la relation
     * @param string $field Nom du champ dans la relation
     * @param object $model Instance du modèle
     * @return array Informations sur le champ
     */
    private function parseNestedField(string $relation, string $field, $model): array
    {
        if (!isset($model::$SCHEMA[$relation]) || !isset($model::$SCHEMA[$relation]['type']) || $model::$SCHEMA[$relation]['type'] !== 'relation') {
            return ['success' => false];
        }

        $relationSchema = $model::$SCHEMA[$relation]['schema'];
        if (!isset($relationSchema[$field])) {
            return ['success' => false];
        }

        return [
            'success' => true,
            'field' => $relationSchema[$field]['field']
        ];
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
        if (!isset($model::$SCHEMA[$field])) {
            return ['success' => false];
        }

        return [
            'success' => true,
            'field' => $model::$SCHEMA[$field]['field']
        ];
    }

    /**
     * Gère la récupération d'un élément
     * 
     * @param object $model Instance du modèle
     * @param int $id Identifiant de l'élément
     * @return array Réponse contenant l'élément
     */
    private function handleGet($model, $id)
    {
        try {
            $result = $model->get((int)$id);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => "Ressource non trouvée"
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
    private function handleCreate($model, $data)
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
    private function handleUpdate($model, $id, $data)
    {
        try {
            $existingModel = $model->get((int)$id);
            if (!$existingModel) {
                return [
                    'success' => false,
                    'message' => "Ressource non trouvée"
                ];
            }

            // Mise à jour des champs
            foreach ($data as $key => $value) {
                if (property_exists($existingModel, $key)) {
                    $existingModel->$key = $value;
                }
            }

            $result = $existingModel->save();

            if (!$result) {
                return [
                    'success' => false,
                    'message' => "Échec de la mise à jour"
                ];
            }

            return [
                'success' => true,
                'message' => "Ressource mise à jour",
                'result' => $result
            ];
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
    private function handleDelete($model, $id)
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
