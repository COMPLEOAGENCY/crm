<?php
namespace Controllers\Api;

use Framework\Controller;
use Framework\HttpRequest;
use Framework\HttpResponse;

class ApiDocController extends Controller 
{
    public function renderDocs($params = [])
    {
        // Récupérer les modèles disponibles
        $models = $this->getAvailableModels();
        
        // Générer la documentation des modèles
        $modelDocs = [];
        foreach ($models as $model) {
            $modelDocs[$model] = $this->generateModelDoc($model);
        }

        return $this->view('api.documentation', [
            'title' => 'API Documentation',
            'models' => $modelDocs,
            'baseUrl' => $this->_httpRequest->getScheme() . '://' . $this->_httpRequest->getHost() . '/apiv2'
        ]);
    }

    private function getAvailableModels()
    {
        // Scan du dossier des modèles
        // echo 'app folder : '.APPFOLDER . 'Models/*.php';exit;
        $modelFiles = glob(APPFOLDER . 'Models/*.php');
        $models = [];
        
        foreach ($modelFiles as $file) {
            $modelName = basename($file, '.php');
            if ($modelName !== 'Model') { // Exclure la classe de base
                $models[] = $modelName;
            }
        }
        
        return $models;
    }

    private function generateModelDoc($modelName)
    {
        $modelClass = "\\Models\\" . $modelName;
        $model = new $modelClass();
        
        return [
            'name' => $modelName,
            'schema' => $modelClass::$SCHEMA ?? [],
            'tableName' => $modelClass::$TABLE_NAME ?? '',
            'primaryKey' => $modelClass::$TABLE_INDEX ?? ''
        ];
    }
}
