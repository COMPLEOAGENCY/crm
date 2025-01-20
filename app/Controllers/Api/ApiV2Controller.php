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
        $this->_httpResponse->headers->set('Access-Control-Allow-Methods', 'GET, POST');
        $this->_httpResponse->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    public function handleRequest($params = [])
    {
        try {
            // Récupération des paramètres de la requête
            $resource = $params['resource'] ?? null;
            $method = $params['method'] ?? 'get'; // Méthode passée en paramètre
            $id = $params['id'] ?? null;
            $limit = $params['limit'] ?? 1000;
            $requestData = $this->_httpRequest->getParams();

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
            switch (strtolower($method)) {
                case 'list':
                    return $this->handleList($model, $limit, $requestData);

                case 'get':
                    return $this->handleGet($model, $id);

                case 'set':
                    if ($id) {
                        return $this->handleUpdate($model, $id, $requestData);
                    }
                    return $this->handleCreate($model, $requestData);

                case 'delete':
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
                'debug' => $params['debug'] ? $e->getTrace() : null
            ], 500);
        }
    }

    private function handleList($model, $limit, $params)
    {
        // Filtrer les paramètres non pertinents
        unset($params['resource'], $params['method'], $params['limit'], $params['debug']);
        
        // Récupérer la liste avec les filtres
        $results = $model->getList($limit, $params);
        
        return $this->jsonResponse([
            'success' => true,
            'message' => '',
            'result' => $results
        ]);
    }

    private function handleGet($model, $id)
    {
        $result = $model->get($id);
        
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
    }

    private function handleCreate($model, $data)
    {
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
    }

    private function handleUpdate($model, $id, $data)
    {
        if (!$model->get($id)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => "Ressource non trouvée"
            ], 404);
        }

        // Mise à jour des champs
        foreach ($data as $key => $value) {
            if (property_exists($model, $key)) {
                $model->$key = $value;
            }
        }

        $result = $model->save();

        return $this->jsonResponse([
            'success' => true,
            'message' => "Ressource mise à jour",
            'result' => $result
        ]);
    }

    private function handleDelete($model, $id)
    {
        if (!$model->get($id)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => "Ressource non trouvée"
            ], 404);
        }

        $result = $model->delete($id);

        return $this->jsonResponse([
            'success' => true,
            'message' => "Ressource supprimée",
            'result' => $result
        ]);
    }

    private function jsonResponse($data, $status = 200)
    {
        $this->_httpResponse->setStatusCode($status);
        return $this->_httpResponse->setContent(json_encode($data, 
            JSON_PRETTY_PRINT | 
            JSON_UNESCAPED_UNICODE | 
            JSON_UNESCAPED_SLASHES
        ));
    }
}
