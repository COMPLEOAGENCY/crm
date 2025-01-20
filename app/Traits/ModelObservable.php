<?php
// Path: src/app/Traits/ModelObservable.php
namespace Traits;

/**
 * Trait ModelObservable
 * 
 * Implémente le pattern Observer pour les modèles.
 * Permet d'attacher des observateurs qui seront notifiés lors des opérations
 * de création, mise à jour et suppression sur les modèles.
 *
 * @package Framework\Traits
 */
trait ModelObservable 
{
    /**
     * Liste des observateurs attachés au modèle
     * @var array
     */
    protected static $observers = [];

    /**
     * Attache un nouvel observateur au modèle
     *
     * @param object $observer Instance de l'observateur à attacher
     * @return void
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
     * @param string $action Type d'action ('created', 'updated', 'deleted')
     * @return void
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
