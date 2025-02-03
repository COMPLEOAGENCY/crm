<?php
namespace Controllers;

use Framework\Controller;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Services\UserService;

/**
 * Contrôleur de gestion de l'administration
 * 
 * Gère les fonctionnalités d'administration du CRM, notamment la gestion
 * des utilisateurs (création, modification, suppression, synchronisation).
 *
 * Fonctionnalités :
 * - Liste des utilisateurs avec filtres
 * - Création/Modification d'utilisateurs
 * - Synchronisation avec les utilisateurs CRM
 * - Suppression d'utilisateurs
 * - Gestion des soldes et transactions
 *
 * @package Controllers
 * @uses \Framework\Controller
 * @uses \Services\UserService
 * @uses \Services\BalanceService
 * @uses \Services\Validation\UserValidationService
 */
class AdminController extends Controller
{
    /** @var UserService Service de gestion des utilisateurs */
    private $userService;

    /**
     * Initialise le contrôleur d'administration
     *
     * Configure les services nécessaires :
     * - UserService pour la gestion des utilisateurs
     * - BalanceService pour la gestion des soldes
     * - Services de validation
     *
     * @param HttpRequest $httpRequest Requête HTTP
     * @param HttpResponse $httpResponse Réponse HTTP
     */
    public function __construct(HttpRequest $httpRequest, HttpResponse $httpResponse)
    {
        parent::__construct($httpRequest, $httpResponse);
        $this->userService = new UserService(
            new \Models\User(), 
            new \Models\CrmUser(),
            $httpRequest->getSession(),
            new \Services\Validation\UserValidationService(),
            new \Services\Validation\ValidationMessageService(),
            new \Services\BalanceService(
                new \Models\User(),
                new \Models\Sale(),
                new \Models\Administration(),
                new \Models\Invoice()
            )      
        );
    }

    /**
     * Affiche et gère la liste des utilisateurs
     *
     * Fonctionnalités :
     * - Filtrage des utilisateurs
     * - Actions groupées
     * - Affichage des utilisateurs CRM associés
     *
     * @param array $params Paramètres de filtrage et d'action
     * @return HttpResponse Vue avec la liste des utilisateurs
     */
    public function userlist($params = [])
    {
        $params = $this->initializeParams($params);

        if (isset($params['submit']) && $params['submit'] === 'valid') {
            $this->processActions($params);
        }

        $users = $this->userService->getFilteredUsers($params);
        $crmUserList = $this->userService->getAllCrmUsers();   
        // filter and order with index of crm user
        $crmUserList = array_column($crmUserList, null, 'crmUserId');     

        return $this->view("admin.userlist", [
            'title' => "Liste des comptes",
            'userList' => $users,
            'crmUserList' => $crmUserList,
            'params' => $params,
            'messages' => $this->userService->validationMessageService->getMessages()
        ]);
    }

    /**
     * Gère l'ajout, la modification et la suppression d'utilisateurs
     *
     * Actions possibles :
     * - valid : Sauvegarde de l'utilisateur
     * - synchro : Synchronisation avec l'utilisateur CRM
     * - delete : Suppression de l'utilisateur
     *
     * @param array $params Paramètres du formulaire
     * @return HttpResponse Vue du formulaire ou redirection
     */
    public function useradd($params = [])
    {
        if (isset($params['submit'])) {
            switch ($params['submit']) {
                case 'valid':
                    $this->userService->saveUser($params);
                    break;
                case 'synchro':
                    $this->userService->synchronizeUser($params['userid'] ?? 0);
                    break;
                case 'delete':
                    $delete = $this->userService->deleteUser($params['userid'] ?? 0);
                    if ($delete) {
                        $this->userService->validationMessageService->addSuccess('Utilisateur supprimé.');
                        // redirection vers userlist
                        $url = URL_SITE . '/admin/userlist/';
                        \Classes\redirect($url);
                    } else {
                        $this->userService->validationMessageService->addError('Erreur lors de la suppression de l\'utilisateur.');
                    }

            }
        }

        return $this->renderUserAddForm($params);
    }

    /**
     * Affiche le formulaire d'ajout/modification d'utilisateur
     *
     * Charge les données nécessaires :
     * - Liste des utilisateurs CRM
     * - Détails de l'utilisateur si modification
     * - Messages de validation
     * - Services de validation
     *
     * @param array $params Paramètres du formulaire
     * @return HttpResponse Vue du formulaire
     * @access private
     */
    private function renderUserAddForm($params = [])    
    {      
        $crmUserList = $this->userService->getAllCrmUsers(); 
        $viewParams = [
            'title' => "Ajout compte client",
            'user' => new \Models\User(), 
            'crmUserList' => $crmUserList,
            'params' => $params,
            'messages' => $this->userService->validationMessageService->getMessages(),
            'validationMessageService' => $this->userService->validationMessageService,
            'validationService' => $this->userService->validationService,
        ];

        if (!empty($params['userid'])) {
            $user = $this->userService->getUserById((int)$params['userid']);
            if ($user) {
                $viewParams['user'] = $user;
                $viewParams['title'] = "Modification d'un compte";
                
                $userDetails = $this->userService->getUserDetails($user->userId);
                $viewParams = array_merge($viewParams, [
                    'balance' => $userDetails['balance'] ?? [],
                    'recentSales' => $userDetails['recentSales'] ?? [],
                    'subUsers' => $userDetails['subUsers'] ?? [],
                    'clients' => $this->userService->getAllUsers()
                ]);
            } else{
                $this->userService->validationMessageService->addError('Utilisateur non trouvé.');
                $url = URL_SITE . '/admin/userlist/';
                \Classes\redirect($url);
                return false; // redirection vers userlist
                // redirection vers userlist

            }
        }

        $this->userService->updateUserFields($viewParams['user'], $params);       

        return $this->view("admin.useradd", $viewParams);
    }    


    private function initializeParams($params): array
    {
        return array_merge([
            'statut' => 'on',
            'type' => null,
            'userid' => null,
            'submit' => null,
            'action' => null,
            'id_array' => [],
            'crm_userid' => null
        ], $params);
    }

    private function processActions($params): void
    {
        if (empty($params['id_array']) || !is_array($params['id_array'])) {
            $this->userService->validationMessageService->addError('Vous devez sélectionner au moins un compte.');
            return;
        }

        if (empty($params['action'])) {
            $this->userService->validationMessageService->addError('Vous devez sélectionner une action.');
            return;
        }

        $userIds = array_keys($params['id_array']);

        switch ($params['action']) {
            case 'synch':
                $this->userService->batchSynchronizeUsers($userIds);
                break;
            case 'copy':
                $this->userService->batchCopyUsers($userIds);
                break;
            case 'delete':
                $this->userService->batchDeleteUsers($userIds);
                break;
            default:
                $this->userService->validationMessageService->addError('Action non valide.');
        }
    }
}
