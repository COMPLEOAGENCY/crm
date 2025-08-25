<?php
namespace Controllers\Api;

use Framework\HttpRequest;
use Framework\HttpResponse;
use Services\LeadValidationService;

class LeadValidationController extends ApiV2Controller
{
    public function __construct(HttpRequest $httpRequest, HttpResponse $httpResponse)
    {
        parent::__construct($httpRequest, $httpResponse);
    }

    /**
     * Lance la validation des leads via le service et renvoie une réponse JSON.
     * Accepte GET/POST. Paramètres supportés: validationid, statut, limit.
     */
    public function run()
    {
        try {
            $params = $this->_httpRequest->getParams();
            $service = new LeadValidationService();
            $result = $service->run($params);
            $this->_httpResponse->setStatusCode(HttpResponse::HTTP_OK);
            return $result;
        } catch (\Throwable $e) {
            $this->_httpResponse->setStatusCode(HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
            return [
                'success' => false,
                'message' => 'Erreur lors de la validation: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Retourne les leads en statut 'pending' des N derniers jours ou un lead spécifique par id.
     * Paramètres supportés: id (int), limit (int), days (int)
     */
    public function loadPendingLeads()
    {
        try {
            $params = $this->_httpRequest->getParams();
            $service = new LeadValidationService();
            $result = $service->loadPendingLeads($params);
            $this->_httpResponse->setStatusCode(HttpResponse::HTTP_OK);
            return $result;
        } catch (\Throwable $e) {
            $this->_httpResponse->setStatusCode(HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
            return [
                'success' => false,
                'message' => 'Erreur lors du chargement des leads pending: ' . $e->getMessage(),
            ];
        }
    }
}
