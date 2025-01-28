<?php
namespace Controllers\Api;

use Framework\Controller;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Framework\CacheManager;

class ApiV2Controller extends Controller 
{
    private $cacheManager;
    
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

    public function handleRequest($params = [])
    {
        try {
            // Récupération des paramètres de la requête
            $resource = $params['resource'] ?? null;
            $id = $params['id'] ?? null;
            $limit = $params['limit'] ?? 1000;
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
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Resource non définie ou invalide'
                ], 404);
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
                        return $this->jsonResponse([
                            'success' => false,
                            'message' => 'ID requis pour la méthode GET'
                        ], 400);
                    }
                    return $this->handleGet($model, $id);

                case 'set':
                    if ($id) {
                        return $this->handleUpdate($model, $id, $requestData);
                    }
                    return $this->handleCreate($model, $requestData);

                case 'delete':
                    if (!$id) {
                        return $this->jsonResponse([
                            'success' => false,
                            'message' => 'ID requis pour la méthode DELETE'
                        ], 400);
                    }
                    return $this->handleDelete($model, $id);

                default:
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => 'Méthode non supportée'
                    ], 405);
            }

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
                'debug' => isset($params['debug']) ? $e->getTrace() : null
            ], 500);
        }
    }

    private function handleList($model, $limit, $params)
    {
        try {
            // Récupérer les paramètres de pagination et tri
            $page = isset($params['page']) ? (int)$params['page'] : 1;
            $limit = isset($params['limit']) ? (int)$params['limit'] : $limit;
            $orderBy = $params['sort'] ?? null;
            $direction = isset($params['order']) ? strtolower($params['order']) : 'asc';
            
            // Valider la direction du tri
            if (!in_array($direction, ['asc', 'desc'])) {
                $direction = 'asc';
            }
            
            // Calculer l'offset pour la pagination
            $offset = ($page - 1) * $limit;
            if ($offset < 0) $offset = 0;

            // Traiter les filtres
            $sqlParameters = [];
            $jsonParameters = [];

            if (isset($params['filter'])) {
                $filters = is_string($params['filter']) ? json_decode($params['filter'], true) : $params['filter'];
                
                if (json_last_error() === JSON_ERROR_NONE || is_array($filters)) {
                    foreach ($filters as $field => $value) {
                        // Vérifier si le champ existe dans le schéma du modèle
                        if (isset(get_class($model)::$SCHEMA[$field])) {
                            $schema = get_class($model)::$SCHEMA[$field];
                            
                            // Si c'est un champ JSON, on l'ajoute aux paramètres JSON
                            if (isset($schema['type']) && $schema['type'] === 'json') {
                                $jsonParameters[$field] = $value;
                            }
                            // Sinon c'est un champ SQL standard
                            else {
                                // Support des opérateurs de comparaison
                                if (is_array($value) && isset($value['operator'], $value['value'])) {
                                    $sqlParameters[] = [$schema['field'], $value['operator'], $value['value']];
                                } else {
                                    $sqlParameters[$schema['field']] = $value;
                                }
                            }
                        }
                    }
                }
            }
            
            // Filtrer les paramètres non pertinents
            unset($params['resource'], $params['method'], $params['limit'], $params['debug'],
                  $params['page'], $params['sort'], $params['order'], $params['filter']);
            
            // Récupérer la liste avec les paramètres de pagination, tri et filtres
            $results = $model->getList($limit, $sqlParameters, $jsonParameters, null, $orderBy, $direction);
            
            if ($results === false) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Erreur lors de la récupération de la liste'
                ], 500);
            }
            
            return $this->jsonResponse([
                'success' => true,
                'message' => '',
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'sort' => $orderBy,
                    'order' => $direction
                ],
                'filters' => [
                    'sql' => $sqlParameters,
                    'json' => $jsonParameters
                ],
                'result' => $results
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la liste: ' . $e->getMessage()
            ], 500);
        }
    }

    private function handleGet($model, $id)
    {
        try {
            $result = $model->get((int)$id);
            
            if (!$result) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Ressource non trouvée"
                ], 404);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => '',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

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
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Échec de la création"
                ], 400);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => "Ressource créée",
                'result' => $result
            ], 201);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    private function handleUpdate($model, $id, $data)
    {
        try {
            $existingModel = $model->get((int)$id);
            if (!$existingModel) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Ressource non trouvée"
                ], 404);
            }

            // Mise à jour des champs
            foreach ($data as $key => $value) {
                if (property_exists($existingModel, $key)) {
                    $existingModel->$key = $value;
                }
            }

            $result = $existingModel->save();

            if (!$result) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Échec de la mise à jour"
                ], 400);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => "Ressource mise à jour",
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    private function handleDelete($model, $id)
    {
        try {
            $result = $model->delete((int)$id);

            if (!$result) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Échec de la suppression"
                ], 400);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => "Ressource supprimée"
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    private function jsonResponse($data, $status = 200)
    {
        $this->_httpResponse->setStatusCode($status);
        $this->_httpResponse->headers->set('Content-Type', 'application/json; charset=utf-8');
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
