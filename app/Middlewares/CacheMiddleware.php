<?php
// Path: src/app/Middlewares/CacheMiddleware.php
namespace Middlewares;

use Framework\Middleware;
use Framework\CacheManager;
use Framework\DebugBar;

class CacheMiddleware extends Middleware
{
    public function handle(\Framework\HTTPRequest $httpRequest, \Framework\HttpResponse $httpResponse): \Framework\HttpResponse
    {
        $cacheManager = CacheManager::instance();

        // Obtenir le paramètre de cache depuis $httpRequest
        $clearcache = $httpRequest->getParam('clearcache');
        if (isset($clearcache)) {
            $cacheManager::clear();
            if (DebugBar::isSet()) {
                // Commencer l'enregistrement du temps de middleware pour DebugBar
                $debugbar = DebugBar::Instance()->getDebugBar();
                $debugbar["messages"]->addMessage(["Cache cleared" => true]);
            }
        }

        return $this::next($httpRequest, $httpResponse);
    }
}

