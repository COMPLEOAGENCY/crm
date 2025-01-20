<?php
namespace Services;

use Models\User;
use Models\Sale;
use Models\Administration;
use Models\Database;
use Models\Invoice;
use Framework\CacheManager;

class BalanceService
{
    private $user;
    private $sale;
    private $administration;
    private $invoice;
    private $database;

    public function __construct(
        User $user, 
        Sale $sale, 
        Administration $administration,
        Invoice $invoice
    ) {
        $this->user = $user;
        $this->sale = $sale;
        $this->administration = $administration;
        $this->invoice = $invoice;
        $this->database = Database::instance();
    }

    public function getSoldeDetails(int $userId, bool $forceUpdate = false)
    {
        // Vérifier si le cache contient déjà le solde pour cet utilisateur
        $cache = CacheManager::instance()->getCacheAdapter();
        $balanceCache = $cache->getItem("balance_user_$userId");
    
        // Si les données sont en cache et que la mise à jour forcée n'est pas demandée, retourner les données en cache
        if ($balanceCache->isHit() && !$forceUpdate) {
            return $balanceCache->get();
        }
    
        // Calculer les détails du solde et mettre à jour le cache
        $balanceData = $this->calculateSoldeDetails($userId);
        $balanceCache->set($balanceData);
        $cache->save($balanceCache);
    
        return $balanceData;
    }
    
    /**
     * Calcule les détails du solde pour un utilisateur donné.
     * 
     * @param int $userId L'identifiant de l'utilisateur
     * @return array Les détails du solde calculés
     */
    private function calculateSoldeDetails(int $userId): array
    {
        // Récupérer les informations de l'utilisateur
        $user = $this->user->get($userId);
        if (!$user) {
            return [];
        }
    
        // Taux de TVA et autres calculs
        $tvaAdmin = $this->administration->getByLabel('vat_rate');
        $tvaRate = (float)$tvaAdmin->value;
        $billingStartDate = !empty($user->billing_start_date) ? $user->billing_start_date : 1609455600;
    
        $salesConditions = [
            ['userid', '=', $userId],
            ['refund_statut', '!=', 'valid'],
            ['timestamp', '>=', $billingStartDate]
        ];
        $salesResults = $this->sale->getList(null, $salesConditions);
    
        $salesTotal = array_reduce($salesResults ?: [], fn($carry, $sale) => $carry + $sale->price, 0);
        $salesVAT = $salesTotal * $tvaRate;
        $invoiceTotalsSinceBilling = $this->invoice->getTotalInvoice($userId, $billingStartDate);
        $invoiceTotalsAll = $this->invoice->getTotalInvoice($userId, 0);
    
        $credits = (float)($invoiceTotalsSinceBilling['total_credits'] ?? 0);
        $unpaid = (float)($invoiceTotalsAll['total_unpaid'] ?? 0);
        $solde = $credits - $salesTotal;
    
        return [
            "timestamp_start_billing" => $billingStartDate,
            "credits_ht_sale" => $salesTotal,
            "credits_vat_sale" => $salesVAT,
            "credits_billed" => $credits,
            "credits_aaf" => $salesTotal - $credits,
            "solde" => $solde,
            "invoice_unpaid_ttc" => $unpaid,
        ];
    }
    
}    