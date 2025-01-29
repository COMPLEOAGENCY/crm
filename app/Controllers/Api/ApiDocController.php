<?php
namespace Controllers\Api;

use Framework\Controller;
use Framework\HttpRequest;
use Framework\HttpResponse;
use ReflectionClass;

class ApiDocController extends Controller 
{
    private $apiEndpoints = [
        'GET /api/{model}' => [
            'description' => 'Récupère la liste des éléments',
            'parameters' => [
                'page' => ['type' => 'int', 'description' => 'Numéro de page (pagination)', 'required' => false],
                'limit' => ['type' => 'int', 'description' => 'Nombre d\'éléments par page', 'required' => false],
                'sort' => ['type' => 'string', 'description' => 'Champ de tri', 'required' => false],
                'order' => ['type' => 'string', 'description' => 'Direction du tri (asc/desc)', 'required' => false],
                'filter' => ['type' => 'object', 'description' => 'Filtres à appliquer', 'required' => false]
            ]
        ],
        'GET /api/{model}/{id}' => [
            'description' => 'Récupère un élément spécifique',
            'parameters' => [
                'id' => ['type' => 'int', 'description' => 'Identifiant unique', 'required' => true]
            ]
        ],
        'POST /api/{model}' => [
            'description' => 'Crée un nouvel élément',
            'parameters' => [
                'data' => ['type' => 'object', 'description' => 'Données de l\'élément à créer', 'required' => true]
            ]
        ],
        'PUT /api/{model}/{id}' => [
            'description' => 'Met à jour un élément existant',
            'parameters' => [
                'id' => ['type' => 'int', 'description' => 'Identifiant unique', 'required' => true],
                'data' => ['type' => 'object', 'description' => 'Données à mettre à jour', 'required' => true]
            ]
        ],
        'DELETE /api/{model}/{id}' => [
            'description' => 'Supprime un élément',
            'parameters' => [
                'id' => ['type' => 'int', 'description' => 'Identifiant unique', 'required' => true]
            ]
        ]
    ];

    public function renderDocs($params = [])
    {
        // Récupérer les modèles disponibles
        $models = $this->getAvailableModels();
        
        // Générer la documentation des modèles
        $modelDocs = [];
        foreach ($models as $model) {
            $modelDocs[$model] = $this->generateModelDoc($model);
        }

        $documentation = [
            'title' => 'API Documentation',
            'version' => '2.0.0',
            'baseUrl' => $this->_httpRequest->getScheme() . '://' . $this->_httpRequest->getHost() . '/apiv2',
            'models' => $modelDocs,
            'endpoints' => $this->generateEndpointDocs($modelDocs),
            'authentication' => [
                'type' => 'Bearer Token',
                'description' => 'Utiliser le header Authorization: Bearer {token}'
            ],
            'errors' => [
                '400' => 'Bad Request - La requête est mal formée',
                '401' => 'Unauthorized - Authentication requise',
                '403' => 'Forbidden - Accès non autorisé',
                '404' => 'Not Found - Ressource non trouvée',
                '422' => 'Unprocessable Entity - Données invalides',
                '500' => 'Internal Server Error - Erreur serveur'
            ]
        ];

        return $this->view('api.documentation', $documentation);
    }

    private function getAvailableModels()
    {
        $modelFiles = glob(APPFOLDER . 'Models/*.php');
        $models = [];
        
        foreach ($modelFiles as $file) {
            $modelName = basename($file, '.php');
            if ($modelName !== 'Model' && $modelName !== 'Database') {
                $models[] = $modelName;
            }
        }
        
        return $models;
    }

    private function generateModelDoc($modelName)
    {
        try {
            $modelClass = "\\Models\\" . $modelName;
            
            // Vérifier si la classe existe
            if (!class_exists($modelClass)) {
                return $this->getEmptyModelStructure($modelName);
            }

            $model = new $modelClass();
            
            // Récupérer les propriétés du modèle
            if (!property_exists($modelClass, 'SCHEMA')) {
                return $this->getEmptyModelStructure($modelName);
            }

            $schema = $modelClass::$SCHEMA;
            $table = $modelClass::$TABLE_NAME;
            $index = $modelClass::$TABLE_INDEX;

            // Analyser le schéma pour les contraintes et relations
            $fields = [];
            foreach ($schema as $fieldName => $fieldData) {
                $fields[$fieldName] = [
                    'name' => $fieldName,
                    'type' => $fieldData['type'],
                    'fieldType' => $fieldData['fieldType'],
                    'required' => !isset($fieldData['default']),
                    'default' => $fieldData['default']
                ];
            }

            return [
                'name' => $modelName,
                'table' => $table,
                'index' => $index,
                'schema' => $schema
            ];
        } catch (\Exception $e) {
            return $this->getEmptyModelStructure($modelName);
        }
    }

    private function getEmptyModelStructure($modelName)
    {
        return [
            'name' => $modelName,
            'table' => '',
            'index' => '',
            'schema' => []
        ];
    }

    private function generateExample($fields)
    {
        $example = [];
        foreach ($fields as $fieldName => $field) {
            switch ($field['type']) {
                case 'int':
                    $example[$fieldName] = 1;
                    break;
                case 'float':
                    $example[$fieldName] = 1.0;
                    break;
                case 'string':
                    if (isset($field['enum'])) {
                        $example[$fieldName] = $field['enum'][0];
                    } else {
                        $example[$fieldName] = "exemple_" . $fieldName;
                    }
                    break;
                case 'bool':
                    $example[$fieldName] = true;
                    break;
                default:
                    $example[$fieldName] = null;
            }
        }
        return $example;
    }

    private function generateEndpointDocs($models)
    {
        $endpoints = [];
        foreach ($models as $modelName => $modelData) {
            foreach ($this->apiEndpoints as $path => $data) {
                $endpoint = str_replace('{model}', strtolower($modelName), $path);
                $endpoints[$endpoint] = array_merge($data, [
                    'model' => $modelName,
                    'example' => [
                        'request' => $this->generateRequestExample($data, $modelData),
                        'response' => $this->generateResponseExample($data, $modelData)
                    ]
                ]);
            }
        }
        return $endpoints;
    }

    private function generateRequestExample($endpoint, $modelData)
    {
        if (isset($endpoint['parameters']['data'])) {
            return [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer {token}'
                ],
                'body' => $modelData['example']
            ];
        }
        return [
            'headers' => [
                'Authorization' => 'Bearer {token}'
            ]
        ];
    }

    private function generateResponseExample($endpoint, $modelData)
    {
        $method = explode(' ', array_keys($this->apiEndpoints)[0])[0];
        
        switch ($method) {
            case 'GET':
                if (strpos($endpoint['description'], 'liste') !== false) {
                    return [
                        'status' => 200,
                        'body' => [
                            'data' => [$modelData['example']],
                            'meta' => [
                                'current_page' => 1,
                                'total_pages' => 1,
                                'total_items' => 1,
                                'items_per_page' => 10
                            ]
                        ]
                    ];
                }
                return [
                    'status' => 200,
                    'body' => [
                        'data' => $modelData['example']
                    ]
                ];
            case 'POST':
                return [
                    'status' => 201,
                    'body' => [
                        'data' => $modelData['example'],
                        'message' => 'Ressource créée avec succès'
                    ]
                ];
            case 'PUT':
                return [
                    'status' => 200,
                    'body' => [
                        'data' => $modelData['example'],
                        'message' => 'Ressource mise à jour avec succès'
                    ]
                ];
            case 'DELETE':
                return [
                    'status' => 204,
                    'body' => null
                ];
        }
    }

    private function generateModelEndpoints($modelName)
    {
        $baseUrl = $this->_httpRequest->getScheme() . '://' . $this->_httpRequest->getHost() . '/apiv2';
        $modelPath = strtolower($modelName);
        
        return [
            'list' => "{$baseUrl}/{$modelPath}",
            'get' => "{$baseUrl}/{$modelPath}/{id}",
            'create' => "{$baseUrl}/{$modelPath}",
            'update' => "{$baseUrl}/{$modelPath}/{id}",
            'delete' => "{$baseUrl}/{$modelPath}/{id}"
        ];
    }
}
