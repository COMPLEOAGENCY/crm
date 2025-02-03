<?php
namespace Middlewares;

use Framework\Middleware;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Framework\SessionHandler;
use Classes\Logger;

/**
 * Middleware d'authentification et d'autorisation
 * 
 * Gère l'authentification des utilisateurs et contrôle l'accès aux différentes
 * sections de l'application en fonction des rôles utilisateur.
 *
 * Fonctionnalités :
 * 1. Gestion de la déconnexion
 * 2. Redirection post-login
 * 3. Protection des routes privées
 * 4. Vérification des permissions par rôle
 *
 * @package Middlewares
 * @uses \Framework\Middleware
 * @uses \Framework\HttpRequest
 * @uses \Framework\HttpResponse
 * @uses \Framework\SessionHandler
 */
class AuthMiddleware extends Middleware
{
    /**
     * Liste des chemins publics accessibles sans authentification
     * 
     * @var array<string>
     * @access private
     * @const
     */
    private const PUBLIC_PATHS = [
        '/loginuser/',
        '/login.php',
        '/login',
        '/logout/'
    ];

    /**
     * Gère la requête HTTP et applique les règles d'authentification
     *
     * Processus :
     * 1. Vérifie si une déconnexion est demandée
     * 2. Gère la redirection post-login si authentifié
     * 3. Protège les routes privées
     * 4. Vérifie les permissions selon le rôle
     *
     * @param HttpRequest $httpRequest Requête HTTP entrante
     * @param HttpResponse $httpResponse Réponse HTTP
     * @return HttpResponse Réponse HTTP modifiée
     * @throws \RuntimeException En cas d'erreur d'authentification
     */
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

    /**
     * Vérifie les permissions d'accès selon le répertoire et le type d'utilisateur
     *
     * Mapping des accès :
     * - admin : ADM
     * - provider : AFF
     * - client : USR
     * - user : ADM, USR
     *
     * @param string $directory Répertoire demandé
     * @param string $userType Type d'utilisateur
     * @return bool True si l'accès est autorisé
     * @access private
     */
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