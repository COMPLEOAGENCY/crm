<?php
namespace Services;

use Models\User;
use Models\Sale;
use Models\Administration;
use Models\Database;
use Models\Invoice;
use Framework\CacheManager;

/**
 * Service de gestion des soldes utilisateurs
 * 
 * Gère le calcul et la mise en cache des soldes utilisateurs en prenant en compte :
 * - Les ventes (Sales)
 * - Les factures (Invoices)
 * - Les paramètres d'administration (TVA, etc.)
 * - Les dates de facturation spécifiques
 *
 * Utilise un système de cache pour optimiser les performances :
 * - Clé de cache : "balance_user_[id]"
 * - Invalidation via CacheObserver sur les modèles liés
 *
 * @package Services
 * @uses \Models\User
 * @uses \Models\Sale
 * @uses \Models\Administration
 * @uses \Models\Invoice
 * @uses \Framework\CacheManager
 */
class BalanceService
{
    /** @var User Modèle de gestion des utilisateurs */
    private $user;

    /** @var Sale Modèle de gestion des ventes */
    private $sale;

    /** @var Administration Modèle de gestion des paramètres admin */
    private $administration;

    /** @var Invoice Modèle de gestion des factures */
    private $invoice;

    /** @var Database Instance de la base de données */
    private $database;

    /**
     * Initialise le service de gestion des soldes
     *
     * @param User $user Modèle utilisateur
     * @param Sale $sale Modèle vente
     * @param Administration $administration Modèle administration
     * @param Invoice $invoice Modèle facture
     */
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

    /**
     * Récupère les détails du solde d'un utilisateur
     *
     * Utilise un système de cache pour optimiser les performances.
     * Les données sont calculées uniquement si :
     * - Elles ne sont pas en cache
     * - Une mise à jour forcée est demandée
     *
     * @param int $userId ID de l'utilisateur
     * @param bool $forceUpdate Force le recalcul même si présent en cache
     * @return array{
     *     timestamp_start_billing: int,
     *     credits_ht_sale: float,
     *     credits_vat_sale: float,
     *     credits_billed: float,
     *     credits_aaf: float,
     *     solde: float,
     *     invoice_unpaid_ttc: float
     * } Détails du solde
     */
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
     * Calcule les détails du solde pour un utilisateur
     *
     * Processus de calcul :
     * 1. Récupère les informations utilisateur et paramètres (TVA)
     * 2. Calcule le total des ventes depuis la date de facturation
     * 3. Applique la TVA sur les ventes
     * 4. Récupère les totaux des factures
     * 5. Calcule le solde final et les impayés
     *
     * @param int $userId ID de l'utilisateur
     * @return array{
     *     timestamp_start_billing: int,
     *     credits_ht_sale: float,
     *     credits_vat_sale: float,
     *     credits_billed: float,
     *     credits_aaf: float,
     *     solde: float,
     *     invoice_unpaid_ttc: float
     * } Détails du solde calculés
     * @access private
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