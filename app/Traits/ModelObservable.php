<?php
// Path: src/app/Traits/ModelObservable.php
namespace Traits;

/**
 * Trait ModelObservable
 * 
 * Implémente le pattern Observer pour les modèles du CRM.
 * Ce trait permet aux modèles d'être observés par des classes comme CacheObserver
 * pour réagir aux opérations CRUD.
 *
 * Fonctionnalités :
 * - Gestion d'une liste d'observateurs par modèle
 * - Notification automatique lors des opérations CRUD
 * - Prévention des doublons d'observateurs
 *
 * Utilisation typique :
 * ```php
 * class MonModel {
 *     use ModelObservable;
 *     
 *     public function save() {
 *         // Sauvegarde...
 *         $this->notifyObservers('updated');
 *     }
 * }
 * ```
 *
 * @package Framework\Traits
 * @see \Observers\CacheObserver Pour un exemple d'implémentation d'observateur
 */
trait ModelObservable 
{
    /**
     * Liste des observateurs attachés au modèle
     * 
     * Chaque observateur doit implémenter les méthodes :
     * - created(Model $model)
     * - updated(Model $model)
     * - deleted(Model $model)
     *
     * @var array<object>
     * @static
     * @access protected
     */
    protected static $observers = [];

    /**
     * Attache un nouvel observateur au modèle
     *
     * Vérifie que l'observateur n'est pas déjà attaché pour éviter
     * les notifications en double.
     *
     * @param object $observer Instance de l'observateur à attacher
     * @return void
     * @static
     * @throws \InvalidArgumentException Si l'observateur n'implémente pas les méthodes requises
     */
    public static function observe($observer): void 
    {
        // Évite les doublons d'observateurs
        if (!in_array($observer, self::$observers)) {
            self::$observers[] = $observer;
        }
    }

    /**
     * Notifie tous les observateurs attachés d'une action sur le modèle
     *
     * Appelle la méthode correspondante à l'action sur chaque observateur
     * si cette méthode existe.
     *
     * @param string $action Type d'action ('created', 'updated', 'deleted')
     * @return void
     * @access protected
     * @throws \RuntimeException Si la notification échoue
     */
    protected function notifyObservers(string $action): void 
    {
        // echo 'notifyObservers called : '.$action;
        // echo '<pre>';
        // print_r(self::$observers);
        // echo '</pre>';
        foreach (self::$observers as $observer) {
            // Vérifie que l'observateur peut gérer cette action
            if (method_exists($observer, $action)) {
                $observer->$action($this);
            }
        }
    }
}
