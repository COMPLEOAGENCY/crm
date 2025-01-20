<?php
// Path: src/app/Middlewares/DebugBarMiddleware.php
namespace Middlewares;

use Framework\Middleware;
use Framework\DebugBar;
use Framework\CacheManager;

class DebugBarMiddleware extends Middleware
{
    public function handle(\Framework\HTTPRequest $httpRequest, \Framework\HttpResponse $httpResponse): \Framework\HttpResponse
    {
        // Création et configuration de la DebugBar
        if(DebugBar::isSet()){
            // start debugbar middleware time recording
            $debugbar = DebugBar::Instance()->getDebugBar();
            $params = $httpRequest->getParams(); 

            // $debugbar->addCollector(new \DebugBar\DataCollector\ConfigCollector($_ENV));

            $debugbarRenderer = $debugbar->getJavascriptRenderer();
            $debugbar["messages"]->addMessage(["Framework parameters"=>$params]);


            // Définir un callback qui sera exécuté juste avant que la réponse soit envoyée
            $httpResponse->setBeforeSendCallback(function() use ($httpResponse, $debugbarRenderer) {
                // Récupération du contenu HTML de la réponse originale
                $content = $httpResponse->getContent();
    
                // Ajout des scripts et styles nécessaires de DebugBar dans les sections <head> et <body>
                $debugBarHead = $debugbarRenderer->renderHead();
                $debugBarBody = $debugbarRenderer->render();
    
                // Injection des éléments de DebugBar dans le contenu HTML
                $modifiedContent = str_replace('</head>', $debugBarHead . '</head>', $content);
                $modifiedContent = str_replace('</body>', $debugBarBody . '</body>', $modifiedContent);
    
                // Définition du contenu modifié dans l'objet httpResponse
                $httpResponse->setContent($modifiedContent);
            });
        }
        return $this::next($httpRequest, $httpResponse);
    }
}