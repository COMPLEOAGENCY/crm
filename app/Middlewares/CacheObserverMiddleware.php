<?php
namespace Middlewares;

use Framework\Middleware;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Framework\DebugBar;
use Models\Model;
use Observers\CacheObserver;

class CacheObserverMiddleware extends Middleware
{
    private static $initialized = false;

    public function handle(HttpRequest $httpRequest, HttpResponse $httpResponse): HttpResponse
    {
        // Ne initialiser qu'une seule fois
        if (!self::$initialized) {
            // Initialiser l'observer
            Model::observe(new CacheObserver());
            self::$initialized = true;

            if (DebugBar::isSet()) {
                $debugbar = DebugBar::Instance()->getDebugBar();
                $debugbar["messages"]->addMessage([
                    'type' => 'cache_observer',
                    'message' => 'CacheObserver initialized',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return $this::next($httpRequest, $httpResponse);
    }
}
