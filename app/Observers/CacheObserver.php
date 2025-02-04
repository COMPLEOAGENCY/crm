<?php
// Path: src/app/Observers/CacheObserver.php
namespace Observers;

use Framework\CacheManager;
use Services\BalanceService;
use Models\Model;
use Models\Sale;
use Models\User;
use Models\Invoice;
use Classes\Logger;
use Framework\DebugBar;

/**
 * Class CacheObserver
 * 
 * Implémente le pattern Observer pour gérer l'invalidation intelligente du cache.
 * Cette classe est responsable de :
 * 1. Observer les changements (create/update/delete) sur tous les modèles
 * 2. Invalider les caches appropriés selon le type de modèle
 * 3. Recalculer les données dépendantes (ex: soldes utilisateurs)
 *
 * Configuration :
 * - LOG_CACHE_OBSERVER = true pour activer les logs détaillés
 * - Utilise la DebugBar pour tracer les invalidations
 *
 * @package Framework\Observers
 * @see \Framework\CacheManager Pour la gestion du cache
 * @see \Services\BalanceService Pour le recalcul des soldes
 * @see \Traits\ModelObservable Pour l'implémentation du pattern Observer
 */
class CacheObserver 
{
    /** 
     * @var CacheManager Instance du gestionnaire de cache
     * @access private 
     */
    private $cacheManager;

    /** 
     * @var BalanceService Service de gestion des soldes utilisateurs
     * @access private 
     */
    private $balanceService;

    /**
     * Constructeur de l'observateur de cache
     * 
     * Initialise les services nécessaires :
     * - CacheManager pour la gestion du cache
     * - BalanceService pour le recalcul des soldes
     *
     * @throws \RuntimeException Si l'initialisation des services échoue
     */
    public function __construct()
    {
        $this->cacheManager = CacheManager::instance();
        // Initialise le service de balance nécessaire pour les recalculs
        $this->balanceService = new BalanceService(
            new \Models\User(),
            new \Models\Sale(),
            new \Models\Administration(),
            new \Models\Invoice()
        );
    }

    /**
     * Gère la création d'un nouveau modèle
     *
     * Déclenche l'invalidation du cache appropriée selon le type de modèle.
     * Pour les modèles User/Sale/Invoice, recalcule également les soldes impactés.
     *
     * @param Model $model Le modèle qui vient d'être créé
     * @return void
     * @throws \Exception En cas d'erreur lors de l'invalidation
     */
    public function created(Model $model): void 
    {
        try {
            $this->handleModelChange($model, 'created');
        } catch (\Exception $e) {
            Logger::critical("Erreur lors de l'observation de la création", [], $e);
        }
    }

    /**
     * Gère la mise à jour d'un modèle existant
     *
     * Invalide les caches impactés et recalcule les données dépendantes.
     * Le comportement spécifique dépend du type de modèle :
     * - User : Invalide 'UserList' et 'balance_user_[id]'
     * - Sale/Invoice : Invalide 'balance_user_[id]' et recalcule le solde
     *
     * @param Model $model Le modèle qui vient d'être mis à jour
     * @return void
     * @throws \Exception En cas d'erreur lors de l'invalidation
     */
    public function updated(Model $model): void 
    {
        try {
            $this->handleModelChange($model, 'updated');
        } catch (\Exception $e) {
            Logger::critical("Erreur lors de l'observation de la mise à jour", [], $e);
        }
    }

    /**
     * Gère la suppression d'un modèle
     *
     * Invalide tous les caches liés au modèle supprimé et met à jour
     * les données dépendantes si nécessaire.
     *
     * @param Model $model Le modèle qui vient d'être supprimé
     * @return void
     * @throws \Exception En cas d'erreur lors de l'invalidation
     */
    public function deleted(Model $model): void 
    {
        try {
            $this->handleModelChange($model, 'deleted');
        } catch (\Exception $e) {
            Logger::critical("Erreur lors de l'observation de la suppression", [], $e);
        }
    }

    /**
     * Gère les changements de modèle selon leur type
     *
     * Implémente la logique spécifique d'invalidation pour chaque type de modèle :
     * - User : Gestion des listes et soldes utilisateurs
     * - Sale/Invoice : Gestion des soldes et transactions
     * - Autres : Invalidation simple des listes
     *
     * @param Model $model Le modèle modifié
     * @param string $action Type d'action ('created', 'updated', 'deleted')
     * @return void
     * @access private
     * @throws \RuntimeException Si le type de modèle n'est pas géré
     */
    private function handleModelChange(Model $model, string $action): void 
    {
        $cache = $this->cacheManager->getCacheAdapter();
        $modelClass = get_class($model);

        // Sélectionne le handler approprié selon le type de modèle
        switch ($modelClass) {
            case Sale::class:
                $this->handleSaleChange($model);
                break;

            case User::class:
                $this->handleUserChange($model, $action);
                break;

            case Invoice::class:
                $this->handleInvoiceChange($model);
                break;
            default:
                $className = (new \ReflectionClass($model))->getShortName();
                $this->{"handle{$className}Change"}($model, $action);   
            break;                
        }

        // Log en mode debug
        if (defined("LOG_CACHE_OBSERVER") && LOG_CACHE_OBSERVER) {
            Logger::debug("Cache observer", [
                'model' => $modelClass,
                'action' => $action,
                'id' => $model->{$model::$OBJ_INDEX}
            ]);
        }


        // Log dans DebugBar si elle est active
        if (DebugBar::isSet()) {
            $debugbar = DebugBar::instance()->getDebugBar();
            $debugbar["messages"]->addMessage([
                'type' => 'cache_invalidation',
                'model' => $modelClass,
                'id' => $model->{$model::$OBJ_INDEX},
                'action' => $action,
                'timestamp' => date('Y-m-d H:i:s'),
                'cache_keys_invalidated' => $this->getCacheKeysToInvalidate($model, $action)
            ]);
        }        
    }

    /**
     * Détermine les clés de cache qui seront invalidées
     * 
     * @param Model $model Le modèle concerné
     * @param string $action L'action effectuée
     * @return array Liste des clés de cache qui seront invalidées
     */
    public function getCacheKeysToInvalidate(Model $model, string $action): array
    {
        $keys = [];
        
        switch (get_class($model)) {
            case Sale::class:
                $keys[] = 'balance_user_' . $model->userId;
                break;

            case User::class:
                $keys[] = 'UserList';
                if ($action !== 'created') {
                    $keys[] = 'balance_user_' . $model->{$model::$OBJ_INDEX};
                }
                break;

            case Invoice::class:
                $keys[] = 'balance_user_' . $model->userid;
                break;
        }

        return $keys;
    }    

    /**
     * Gère les changements spécifiques aux ventes
     *
     * @param Sale $sale Instance de la vente modifiée
     * @return void
     */
    private function handleSaleChange(Sale $sale): void
    {
        $cache = $this->cacheManager->getCacheAdapter();
        // Une vente modifie le solde de l'utilisateur
        $cache->delete('balance_user_' . $sale->userId);
        
        // Force le recalcul immédiat du solde
        $this->balanceService->getSoldeDetails($sale->userId, true);
    }

    /**
     * Gère les changements spécifiques aux utilisateurs
     *
     * @param User $user Instance de l'utilisateur modifié
     * @param string $action Type d'action effectuée
     * @return void
     */
    private function handleUserChange(User $user, string $action): void
    {
        $cache = $this->cacheManager->getCacheAdapter();
        // Toute modification d'utilisateur invalide la liste
        $cache->delete('UserList');
        
        // Invalide le cache du solde sauf pour une création
        if ($action !== 'created') {
            $cache->delete('balance_user_' . $user->{$user::$OBJ_INDEX});
        }
    }

    /**
     * Gère les changements spécifiques aux factures
     *
     * @param Invoice $invoice Instance de la facture modifiée
     * @return void
     */
    private function handleInvoiceChange(Invoice $invoice): void
    {
        $cache = $this->cacheManager->getCacheAdapter();
        // Une facture modifie le solde de l'utilisateur
        $cache->delete('balance_user_' . $invoice->userid);
        
        // Force le recalcul immédiat du solde
        $this->balanceService->getSoldeDetails($invoice->userid, true);
    }
}
