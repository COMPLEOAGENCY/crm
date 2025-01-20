<?php
namespace Middlewares;

use Framework\Middleware;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Framework\SessionHandler;
use Classes\Logger;


class AuthMiddleware extends Middleware
{
    private const PUBLIC_PATHS = [
        '/loginuser/',
        '/login.php',
        '/login',
        '/logout/'
    ];

    public function handle(HttpRequest $httpRequest, HttpResponse $httpResponse): HttpResponse
    {
        try {
            $session = $httpRequest->getSession();
            $path = $httpRequest->getPath();
            $directory = explode('/', trim($path, '/'))[0] ?? '';
            
            // 1. Déconnexion si demandée

            if ($httpRequest->getParam('logout')) {
                $session->clearSession();
                echo 'Déconnexion si demandée';exit;
                \Classes\redirectToLogin();
            }

            $isAuthenticated = (bool)$session->get('connexion');
            $isPublicPath = in_array('/' . ltrim($path, '/'), self::PUBLIC_PATHS, true);

            // 2. Si authentifié, gérer la redirection post-login
            if ($isAuthenticated && $returnUrl = $httpRequest->getParam('request_uri')) {
                // echo ' Si authentifié, gérer la redirection post-login';exit;
                \Classes\redirect( \Classes\cleanUrl($returnUrl));
            }

            // 3. Si page privée et non authentifié
            if (!$isPublicPath && !$isAuthenticated) {
                // echo 'Si page privée et non authentifié';exit;
                \Classes\redirectToLogin($httpRequest->getFullUrl());
            }

            // 4. Vérifier les permissions si authentifié
            if ($isAuthenticated && !$isPublicPath) {
                
                $userType = $session->has('crm_user') 
                    ? ($session->get('crm_user')->crm_user_role ?? '')
                    : ($session->get('user')->type ?? '');

                if (!$this->checkAccess($directory, $userType)) {
                    \Classes\redirectByUserType($userType);
                }
            }

            return $this::next($httpRequest, $httpResponse);

        } catch (\Throwable $e) {
            Logger::critical("Erreur Auth", [], $e);
            \Classes\redirectToLogin();
        }
    }

    private function checkAccess(string $directory, string $userType): bool
    {
        if (empty($directory) || empty($userType)) return false;

        $accessMap = [
            'admin' => [ADM],
            'provider' => [AFF],
            'client' => [USR],
            'user' => [ADM, USR]
        ];

        return isset($accessMap[$userType]) && in_array($directory, $accessMap[$userType], true);
    }
}