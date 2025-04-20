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
                'sort' => ['type' => 'string', 'description' => 'Champ de tri (peut être un champ simple ou imbriqué comme contact[timestamp])', 'required' => false],
                'order' => ['type' => 'string', 'description' => 'Direction du tri (asc/desc)', 'required' => false],
                'filter' => ['type' => 'object', 'description' => 'Filtres à appliquer (supporte les formats simples, complexes et imbriqués)', 'required' => false]
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
        // Documentation spécifique pour LeadManager
        if ($modelName === 'LeadManager') {
            return $this->generateLeadManagerDoc();
        }

        try {
            $modelClass = "\\Models\\" . $modelName;
            
            // Vérifier si la classe existe
            if (!class_exists($modelClass)) {
                return $this->getEmptyModelStructure($modelName);
            }

            $model = new $modelClass();
            
            // Récupérer les propriétés du modèle
            if (!method_exists($modelClass, 'getSchema')) {
                return $this->getEmptyModelStructure($modelName);
            }

            $schema = $modelClass::getSchema();
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

    private function generateLeadManagerDoc()
    {
        return [
            'name' => 'LeadManager',
            'description' => 'Gestion des leads avec leurs relations (contact, project, purchase, sales, validationHistories)',
            'schema' => [
                'leadId' => [
                    'type' => 'int',
                    'description' => 'Identifiant unique du lead',
                    'example' => 123
                ],
                'createdAt' => [
                    'type' => 'datetime',
                    'description' => 'Date de création',
                    'example' => '2025-01-01 12:00:00'
                ],
                'updatedAt' => [
                    'type' => 'datetime',
                    'description' => 'Date de dernière modification',
                    'example' => '2025-01-01 14:30:00'
                ],
                'contact' => [
                    'type' => 'object',
                    'description' => 'Informations de contact',
                    'adapter' => 'ContactAdapter',
                    'fields' => [
                        'civility' => ['type' => 'string', 'example' => 'M.'],
                        'firstName' => ['type' => 'string', 'example' => 'John'],
                        'lastName' => ['type' => 'string', 'example' => 'Doe'],
                        'email' => ['type' => 'string', 'example' => 'john.doe@example.com'],
                        'phone' => ['type' => 'string', 'example' => '0612345678'],
                        'phone2' => ['type' => 'string', 'example' => '0123456789']
                    ]
                ],
                'project' => [
                    'type' => 'object',
                    'description' => 'Informations du projet et questions de la campagne',
                    'adapter' => 'ProjectAdapter',
                    'fields' => [
                        'leadId' => ['type' => 'int', 'example' => 123],
                        'campaignId' => ['type' => 'int', 'example' => 456],
                        'address' => [
                            'type' => 'object',
                            'fields' => [
                                'address1' => ['type' => 'string', 'example' => '123 rue Example'],
                                'address2' => ['type' => 'string', 'example' => 'Apt 4B'],
                                'postalCode' => ['type' => 'string', 'example' => '75001'],
                                'city' => ['type' => 'string', 'example' => 'Paris'],
                                'country' => ['type' => 'string', 'example' => 'France']
                            ]
                        ],
                        'questions' => [
                            'type' => 'object',
                            'description' => 'Questions de la campagne avec leurs réponses',
                            'example' => [
                                'type_travaux' => [
                                    'questionId' => 555,
                                    'label' => 'type_travaux',
                                    'question' => 'Type de travaux',
                                    'type' => 'select',
                                    'defaultValues' => "Rénovation\nConstruction neuve\nAutre",
                                    'value' => 'Rénovation'
                                ]
                            ]
                        ],
                        'campaign' => [
                            'type' => 'object',
                            'description' => 'Informations détaillées sur la campagne associée au projet',
                            'fields' => [
                                'campaignId' => ['type' => 'int', 'example' => 456],
                                'name' => ['type' => 'string', 'example' => 'Campagne Rénovation 2025'],
                                'details' => ['type' => 'string', 'example' => 'Campagne de rénovation énergétique'],
                                'price' => ['type' => 'float', 'example' => 10.0]
                            ]
                        ]
                    ]
                ],
                'purchase' => [
                    'type' => 'object',
                    'description' => 'Données d\'achat',
                    'adapter' => 'PurchaseAdapter',
                    'fields' => [
                        'exists' => ['type' => 'boolean', 'example' => true],
                        'data' => [
                            'type' => 'object',
                            'fields' => [
                                'purchaseId' => ['type' => 'int', 'example' => 456],
                                'timestamp' => ['type' => 'datetime', 'example' => '2025-01-01 15:00:00'],
                                'price' => ['type' => 'float', 'example' => 1500.50]
                            ]
                        ]
                    ]
                ],
                'sales' => [
                    'type' => 'array',
                    'description' => 'Données des ventes',
                    'adapter' => 'SaleAdapter',
                    'fields' => [
                        'exists' => ['type' => 'boolean', 'example' => true],
                        'data' => [
                            'type' => 'object',
                            'fields' => [
                                'saleId' => ['type' => 'int', 'example' => 789],
                                'timestamp' => ['type' => 'datetime', 'example' => '2025-01-02 10:00:00'],
                                'price' => ['type' => 'float', 'example' => 2000.00]
                            ]
                        ]
                    ]
                ]
            ],
            'examples' => [
                'filtrage' => [
                    [
                        'description' => 'Filtre simple sur l\'ID',
                        'request' => 'GET /apiv2/leadmanager?filter={"leadId":123}',
                        'explanation' => 'Récupère le lead avec l\'ID 123'
                    ],
                    [
                        'description' => 'Filtre direct avec opérateur',
                        'request' => 'GET /apiv2/leadmanager?filter={"field":"timestamp","operator":">","value":0}',
                        'explanation' => 'Récupère les leads créés après le timestamp 0'
                    ],
                    [
                        'description' => 'Filtre sur champ imbriqué (contact)',
                        'request' => 'GET /apiv2/leadmanager?filter={"field":"contact[email]","operator":"LIKE","value":"%@gmail.com"}',
                        'explanation' => 'Récupère les leads dont l\'email du contact se termine par @gmail.com'
                    ],
                    [
                        'description' => 'Filtre complexe avec conditions AND/OR',
                        'request' => 'GET /apiv2/leadmanager?filter={"AND":[{"field":"timestamp","operator":">","value":0},{"OR":[{"field":"project[status]","operator":"=","value":"valid"},{"field":"project[status]","operator":"=","value":"deversoir"}]}]}',
                        'explanation' => 'Récupère les leads créés après le timestamp 0 ET dont le statut du projet est soit "valid" soit "deversoir"'
                    ],
                    [
                        'description' => 'Filtre avec opérateur de négation',
                        'request' => 'GET /apiv2/leadmanager?filter={"field":"project[status]","operator":"!=","value":"invalid"}',
                        'explanation' => 'Récupère les leads dont le statut du projet n\'est pas "invalid"'
                    ],
                    [
                        'description' => 'Tri sur champ imbriqué',
                        'request' => 'GET /apiv2/leadmanager?sort=contact[timestamp]&order=desc&limit=5',
                        'explanation' => 'Récupère 5 leads triés par date de création du contact en ordre décroissant'
                    ],
                    [
                        'description' => 'Filtre sur questions de campagne',
                        'request' => 'GET /apiv2/leadmanager?filter={"project[questions][type_travaux][value]":"Rénovation"}',
                        'explanation' => 'Récupère les leads dont le type de travaux est Rénovation'
                    ]
                ]
            ]
        ];
    }
}
