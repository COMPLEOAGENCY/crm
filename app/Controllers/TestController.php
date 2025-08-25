<?php

namespace Controllers;

use Framework\Controller;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Framework\SessionHandler;
use Services\BalanceService;
use Models\User;
use Models\Sale;
use Models\Administration;
use Models\Invoice;

class TestController extends Controller
{
    /**
     * Test du BalanceService
     * Compare les valeurs avec la fonction legacy
     */
    public function testBalance($userid = null)
    {
        // Debug pour voir ce qui est reçu
        if (is_array($userid)) {
            // Si c'est un tableau, prendre la première valeur ou 'userid' si elle existe
            $userid = isset($userid['userid']) ? (int)$userid['userid'] : (int)reset($userid);
        } else {
            $userid = (int)$userid;
        }
        $userid = $userid ?: 300;
        
        // Récupérer les valeurs legacy via l'API publique (sans authentification)
        $legacyApiUrl = URL_SITE.'/api/balance_legacy.php?userid=' . $userid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $legacyApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('Erreur cURL: ' . $error);
        }
        
        if ($httpCode !== 200) {
            throw new \Exception('Erreur HTTP: ' . $httpCode . ' - Response: ' . $response);
        }
        
        $legacyData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Erreur JSON: ' . json_last_error_msg() . ' - Response: ' . substr($response, 0, 500));
        }
        
        if (!$legacyData || !isset($legacyData['success']) || !$legacyData['success']) {
            throw new \Exception('Données legacy invalides: ' . print_r($legacyData, true));
        }
        
        $legacyDetails = $legacyData['details'];
        $userInfo = $legacyData['user'];
        
        // Récupérer les valeurs du BalanceService
        $balanceService = new BalanceService(
            new User(),
            new Sale(),
            new Administration(),
            new Invoice()
        );
        
        $serviceDetails = $balanceService->getSoldeDetails($userid, true);
        
        // Comparer les valeurs
        $comparison = [];
        $allMatch = true;
        
        foreach ($legacyDetails as $key => $legacyValue) {
            $serviceValue = $serviceDetails[$key] ?? 0;
            $diff = abs($legacyValue - $serviceValue);
            $match = $diff < 0.01; // Tolérance pour les arrondis
            
            if (!$match) $allMatch = false;
            
            $comparison[] = [
                'field' => $key,
                'legacy' => $legacyValue,
                'service' => $serviceValue,
                'difference' => $diff,
                'match' => $match
            ];
        }
        
        // Créer un objet user avec les infos récupérées
        $user = (object) $userInfo;
        
        return $this->view('admin.testbalance', [
            'userid' => $userid,
            'user' => $user,
            'legacyDetails' => $legacyDetails,
            'serviceDetails' => $serviceDetails,
            'comparison' => $comparison,
            'allMatch' => $allMatch
        ]);
        
    }
}
