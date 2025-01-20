<?php
// Path: src/app/Middlewares/SessionMiddleware.php
namespace Middlewares;

use Framework\Middleware;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Framework\DebugBar;

class SessionMiddleware extends Middleware
{
    public function handle(HttpRequest $httpRequest, HttpResponse $httpResponse): HttpResponse
    {
        // Vérifier et gérer la suppression de la session si 'clearsession' est défini
        $clearsession = $httpRequest->getParam('clearsession');        
        // Démarrer et enregistrer l'état actuel de la session PHP si elle n'est pas déjà ouverte
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            if (isset($clearsession)) {   
                $_SESSION = array();
                session_destroy();
            }
        }
        $oldSessionData = $_SESSION;

        // Fermer immédiatement la session PHP pour éviter les conflits
        session_write_close();

        // Obtenir la session du framework
        $session = $httpRequest->getSession();
        if (!$session) {
            // THrow an error if the session is not started
            Throw new \Exception('Erreur: Impossible de démarrer la session du framework.');
            
            return $this->next($httpRequest, $httpResponse);
        }

        // Synchroniser les données de $_SESSION vers la session du framework
        $this->syncSessionData($oldSessionData, $session);


        if (isset($clearsession)) {   
            $session->clearSession();
            $session->persistNow();
            $_SESSION = array();
            // destroy the session
            if (DebugBar::isSet()) {
                $debugbar = DebugBar::Instance()->getDebugBar();
                $debugbar["messages"]->addMessage(["Session cleared" => true]);
            }
        }

        // Ajouter l'ID de session dans les en-têtes de réponse
        $httpResponse->headers->set('X-Session-ID', $session->getId() ?? '');

        // Continuer le traitement avec les middlewares suivants
        return $this->next($httpRequest, $httpResponse);
    }

    /**
     * Synchronise les données de $_SESSION vers la session du framework.
     */
    private function syncSessionData(array $oldSessionData, $session): void
    {
        // Transfert des données de $_SESSION vers la session du framework
        foreach ($oldSessionData as $key => $value) {
            if (!$session->has($key)) {
                $session->set($key, $value);
            }
        }

        // Persister les modifications dans la session du framework
        $session->persistNow();
    }
}
