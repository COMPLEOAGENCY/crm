<?php
// Path: src/app/Middlewares/CacheMiddleware.php
namespace Middlewares;

use Framework\Middleware;
use Framework\CacheManager;
use Framework\DebugBar;

/**
 * Middleware de gestion du cache
 * 
 * Gère les opérations de cache au niveau des requêtes HTTP.
 * Permet notamment de vider le cache via le paramètre 'clearcache'.
 *
 * Fonctionnalités :
 * - Vidage du cache via paramètre GET
 * - Intégration avec DebugBar pour le monitoring
 *
 * @package Middlewares
 * @uses \Framework\Middleware
 * @uses \Framework\CacheManager
 * @uses \Framework\DebugBar
 */
class CacheMiddleware extends Middleware
{
    /**
     * Traite la requête HTTP pour la gestion du cache
     *
     * Si le paramètre 'clearcache' est présent dans la requête,
     * vide complètement le cache et enregistre l'action dans DebugBar.
     *
     * @param \Framework\HTTPRequest $httpRequest Requête HTTP entrante
     * @param \Framework\HttpResponse $httpResponse Réponse HTTP
     * @return \Framework\HttpResponse Réponse HTTP modifiée
     * @throws \RuntimeException En cas d'erreur de manipulation du cache
     */
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
