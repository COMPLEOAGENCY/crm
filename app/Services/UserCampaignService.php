<?php

namespace Services;

use Models\UserCampaign;
use Models\Campaign;
use Models\Webservice;
use Models\User;
use Models\Sale;
use Models\Administration;
use Models\Invoice;

class UserCampaignService
{
    /**
     * Récupère les commandes d'un utilisateur avec leurs webservices
     * @param int $userId ID de l'utilisateur
     * @return array Liste des commandes avec webservices
     */
    public function getUserCommands($userId): array
    {
        try {
            $userCampaignModel = new UserCampaign();
            $campaignModel = new Campaign();
            
            // Récupérer les UserCampaigns de l'utilisateur
            $userCampaigns = $userCampaignModel->getList(
                1000,  // limit
                ['userid' => $userId],  // conditions
                null,  // jsonParameters
                null,  // groupBy
                'usercampaignid',  // orderBy
                'desc'  // direction
            );
            
            $commandsList = [];
            foreach ($userCampaigns as $userCampaign) {
                // Récupérer les détails de la campagne
                $campaignName = $userCampaign->name ?? '';
                
                // Si campaignid_list existe et est un tableau
                if (!empty($userCampaign->campaignid_list) && is_array($userCampaign->campaignid_list)) {
                    $campaignNames = [];
                    foreach ($userCampaign->campaignid_list as $campaignId) {
                        $campaign = $campaignModel->get($campaignId);
                        if ($campaign && !empty($campaign->name)) {
                            $campaignNames[] = $campaign->name;
                        }
                    }
                    if (!empty($campaignNames)) {
                        $campaignName = implode(', ', $campaignNames);
                    }
                } elseif (!empty($userCampaign->campaignid)) {
                    // Fallback sur campaignid simple
                    $campaign = $campaignModel->get($userCampaign->campaignid);
                    if ($campaign && !empty($campaign->name)) {
                        $campaignName = $campaign->name;
                    }
                }
                
                // Récupérer les webservices associés
                $webservices = $this->getCommandWebservices($userCampaign->usercampaignId);
                
                // Déterminer le statut et le style
                $status = 'on';
                $deleted = false;
                
                if (isset($userCampaign->statut) && $userCampaign->statut != 'on') {
                    $status = 'off';
                }
                
                if (isset($userCampaign->deleted) && $userCampaign->deleted == 'yes') {
                    $deleted = true;
                }
                
                $commandsList[] = [
                    'campaignId' => $userCampaign->usercampaignId,
                    'name' => $campaignName,
                    'fullName' => $userCampaign->name ?? '',
                    'products' => $this->parseProducts($campaignName),
                    'reference' => $this->extractReference($campaignName),
                    'webservices' => $webservices,
                    'status' => $status,
                    'deleted' => $deleted
                ];
            }
            
            return $commandsList;
        } catch (\Exception $e) {
            error_log("Erreur getUserCommands: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère les webservices liés à une commande
     * @param int $usercampaignId ID de la UserCampaign
     * @return array Liste des webservices
     */
    public function getCommandWebservices($usercampaignId): array
    {
        try {
            $webserviceModel = new Webservice();
            
            $webservices = $webserviceModel->getList(
                null,  // limit (pas de limite)
                ['usercampaignid' => $usercampaignId],  // sqlParameters
                null,  // jsonParameters
                null,  // groupBy
                'webserviceId',  // orderBy
                'desc'  // direction
            );
            
            $wsList = [];
            foreach ($webservices as $w) {
                $wsList[] = [
                    'webserviceId' => $w->webserviceId,
                    'timestamp' => $w->timestamp ?? null,
                    'type' => $w->type ?? 'webservice',
                    'start_date' => isset($w->timestamp_start) && $w->timestamp_start ? date('d-m-Y', $w->timestamp_start) : 'non précisée',
                    'end_date' => isset($w->timestamp_end) && $w->timestamp_end ? date('d-m-Y', $w->timestamp_end) : 'non précisée',
                    'file' => $w->file ?? '',
                    'status' => isset($w->statut) && $w->statut == 'on' ? 'On' : 'Off'
                ];
            }
            
            return $wsList;
        } catch (\Exception $e) {
            error_log("Erreur getCommandWebservices: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Parse les produits depuis le nom de la campagne
     * @param string $campaignName Nom de la campagne
     * @return array Liste des produits
     */
    public function parseProducts($campaignName): array
    {
        // Extraction des produits depuis le nom de la campagne
        // Format attendu: "Produit1, Produit2 - PPF XX"
        $products = [];
        
        if (!empty($campaignName)) {
            // Retirer la référence PPF si présente
            $parts = explode(' - PPF', $campaignName);
            $productString = $parts[0];
            
            // Séparer les produits
            $products = array_map('trim', explode(',', $productString));
        }
        
        return $products;
    }
    
    /**
     * Extrait la référence PPF depuis le nom de la campagne
     * @param string $campaignName Nom de la campagne
     * @return string Référence PPF ou chaîne vide
     */
    public function extractReference($campaignName): string
    {
        // Extraction de la référence PPF
        // Format attendu: "... - PPF XX"
        if (preg_match('/PPF\s+(\d+)/i', $campaignName, $matches)) {
            return 'PPF ' . $matches[1];
        }
        
        return '';
    }
    
    /**
     * Récupère toutes les commandes avec pagination
     * @param int $limit Nombre de résultats
     * @param int $offset Décalage
     * @param array $filters Filtres optionnels
     * @return array
     */
    public function getAllCommands($limit = 50, $offset = 0, $filters = []): array
    {
        try {
            $userCampaignModel = new UserCampaign();
            
            // Construire les conditions SQL
            $conditions = [];
            if (!empty($filters['userid'])) {
                $conditions['userid'] = $filters['userid'];
            }
            // Gestion spéciale du statut: si 'credit_over', on ne met pas de filtre SQL sur statut
            if (!empty($filters['statut']) && $filters['statut'] !== 'credit_over') {
                $conditions['statut'] = $filters['statut'];
            }
            if (!empty($filters['deleted'])) {
                $conditions['deleted'] = $filters['deleted'];
            }
            if (!empty($filters['campaignid'])) {
                $conditions['campaignid'] = $filters['campaignid'];
            }
            if (!empty($filters['type'])) {
                $conditions['type'] = $filters['type'];
            }
            
            $userCampaigns = $userCampaignModel->getList(
                $limit,
                $conditions,
                null,
                null,
                'usercampaignid',
                'desc'
            );
            
            // Post-filtrage pour crm_userid (gestionnaire) et credit_over si nécessaire
            $needsCrmFilter = !empty($filters['crm_userid']);
            $needsCreditOver = !empty($filters['statut']) && $filters['statut'] === 'credit_over';

            if (!$needsCrmFilter && !$needsCreditOver) {
                return $userCampaigns;
            }

            // Préparer les services et données nécessaires pour le post-filtrage
            $userModel = new User();
            $balanceService = null;
            if ($needsCreditOver) {
                $balanceService = new BalanceService(
                    new User(),
                    new Sale(),
                    new Administration(),
                    new Invoice()
                );
            }

            // Mise en cache locale des utilisateurs pour limiter les requêtes
            $userCache = [];
            $filtered = [];
            foreach ($userCampaigns as $uc) {
                $uid = $uc->userid ?? null;
                if (empty($uid)) { continue; }

                if (!isset($userCache[$uid])) {
                    $u = $userModel->get((int)$uid);
                    $userCache[$uid] = $u ?: null;
                }
                $user = $userCache[$uid];
                if (!$user) { continue; }

                // Filtre gestionnaire (crm_userid) basé sur User.vendor_id
                if ($needsCrmFilter) {
                    if (!isset($user->vendor_id) || (int)$user->vendor_id !== (int)$filters['crm_userid']) {
                        continue;
                    }
                }

                // Filtre credit_over: inclure uniquement si la dette dépasse l'encours_max
                if ($needsCreditOver) {
                    $balance = $balanceService->getSoldeDetails((int)$uid);
                    $solde = isset($balance['solde']) ? (float)$balance['solde'] : 0.0;
                    $encoursMax = isset($user->encours_max) ? (float)$user->encours_max : 0.0;
                    $dette = -1 * $solde; // dette positive si solde négatif
                    if (!($dette > $encoursMax)) {
                        // si la dette ne dépasse pas l'encours max, on exclut
                        continue;
                    }
                }

                $filtered[] = $uc;
            }

            return $filtered;
        } catch (\Exception $e) {
            error_log("Erreur getAllCommands: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère une commande spécifique
     * @param int $usercampaignId
     * @return object|null
     */
    public function getCommand($usercampaignId)
    {
        try {
            $userCampaignModel = new UserCampaign();
            return $userCampaignModel->get($usercampaignId);
        } catch (\Exception $e) {
            error_log("Erreur getCommand: " . $e->getMessage());
            return null;
        }
    }
}
