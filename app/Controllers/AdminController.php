<?php
namespace Controllers;

use Framework\Controller;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Services\UserService;
use Services\Validation\ValidationMessageService;
use Models\Validation;

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
 * @uses Services\UserService;
 * @uses Services\ValidationMessageService;
 * @uses Services\UserCampaignService;
 * @uses \Services\Validation\UserValidationService
 */
class AdminController extends Controller
{
    /** @var UserService Service de gestion des utilisateurs */
    private $userService;
    
    /** @var UserCampaignService Service de gestion des commandes */
    private $userCampaignService;

    /** @var ValidationMessageService Service de messages pour les validations */
    private $validationMessageService;

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
        $this->userCampaignService = new \Services\UserCampaignService();
        $this->validationMessageService = new ValidationMessageService();
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
            'clients' => $this->userService->getAllUsers(),
            'shopsList' => $this->getShopsList(),
            'shopBalance' => null
        ];

        if (!empty($params['userid'])) {
            $user = $this->userService->getUserById((int)$params['userid']);
            if ($user) {
                $viewParams['user'] = $user;
                $viewParams['title'] = "Modification d'un compte";
                
                // Récupérer les détails utilisateur via le service
                $userDetails = $this->userService->getUserDetails($user->userId);
                $viewParams = array_merge($viewParams, [
                    'balance' => $userDetails['balance'] ?? [],
                    'recentSales' => array_slice($userDetails['recentSales'] ?? [], 0, 10), // Limiter aux 10 dernières ventes
                    'subUsers' => $userDetails['subUsers'] ?? [],
                ]);
                
                // Récupérer les commandes et webservices de l'utilisateur via le service
                $commands = $this->userCampaignService->getUserCommands($user->userId);
                error_log("Commands for user {$user->userId}: " . json_encode($commands));
                $viewParams['commands'] = $commands;
                
                // Récupération du solde boutique si shopId existe
                if (!empty($user->shopId)) {
                    $viewParams['shopBalance'] = $this->getShopBalance($user->shopId);
                }
            } else{
                $this->userService->validationMessageService->addError('Utilisateur non trouvé.');
                $url = URL_SITE . '/admin/userlist/';
                \Classes\redirect($url);
                return false;
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

    /**
     * Affiche et gère la liste des campagnes clients (commandes)
     * Route: /admin/clientcampaign/list
     *
     * Actions: copy, delete (soft), synch (non implémentée)
     *
     * @param array $params
     * @return HttpResponse
     */
    public function clientcampaignList($params = [])
    {
        // Paramètres par défaut
        $params = array_merge([
            'statut' => 'on',
            'userid' => null,
            'deleted' => null,
            'campaignid' => null,
            'type' => null,
            'crm_userid' => null,
            'submit' => null,
            'action' => null,
            'id_array' => []
        ], $params);

        // Gestion des actions
        if (isset($params['submit']) && $params['submit'] === 'valid') {
            if (empty($params['id_array']) || !is_array($params['id_array'])) {
                $this->validationMessageService->addError('Vous devez sélectionner au moins une campagne client.');
            } elseif (empty($params['action'])) {
                $this->validationMessageService->addError('Vous devez sélectionner une action.');
            } else {
                $ids = array_keys($params['id_array']);
                switch ($params['action']) {
                    case 'copy':
                        $this->copyUserCampaigns($ids);
                        break;
                    case 'delete':
                        $this->deleteUserCampaigns($ids);
                        break;
                    case 'synch':
                        $this->validationMessageService->addError('Action de synchronisation non implémentée pour les campagnes clients.');
                        break;
                    default:
                        $this->validationMessageService->addError('Action non valide.');
                }
            }
        }

        // Filtres pour la liste
        $filters = [];
        if (!empty($params['userid'])) { $filters['userid'] = $params['userid']; }
        if (!empty($params['statut'])) { $filters['statut'] = $params['statut']; }
        if (!empty($params['deleted'])) { $filters['deleted'] = $params['deleted']; }
        if (!empty($params['campaignid'])) { $filters['campaignid'] = $params['campaignid']; }
        if (!empty($params['type'])) { $filters['type'] = $params['type']; }
        if (!empty($params['crm_userid'])) { $filters['crm_userid'] = $params['crm_userid']; }

        // Récupération de la liste des campagnes clients
        $campaignList = $this->userCampaignService->getAllCommands(50000, 0, $filters);

        // Listes pour les filtres (clients, CRM users, campagnes)
        $clients = $this->userService->getAllUsers();
        $crmUserList = $this->userService->getAllCrmUsers();
        $campaigns = (new \Models\Campaign())->getAll() ?? [];

        return $this->view("admin.clientcampaignlist", [
            'title' => "Liste des campagnes clients",
            'campaignList' => $campaignList,
            'clients' => $clients,
            'crmUserList' => $crmUserList,
            'campaigns' => $campaigns,
            'params' => $params,
            'messages' => $this->validationMessageService->getMessages()
        ]);
    }

    /**
     * Copie en masse des campagnes clients
     * @param array $ids
     * @return void
     */
    private function copyUserCampaigns(array $ids): void
    {
        $copied = 0; $failed = 0;
        foreach ($ids as $id) {
            $model = new \Models\UserCampaign();
            $original = $model->get((int)$id);
            if ($original !== false && !empty($model->usercampaignId)) {
                $new = new \Models\UserCampaign();
                $new->importObj($model);
                $new->usercampaignId = null;
                $new->timestamp = time();
                $new->timestamp_update = time();
                $new->statut = 'off';
                $new->deleted = 'no';
                // Tentative d'enregistrement de l'utilisateur CRM en mise à jour
                if (isset($_SESSION['crm_user']) && isset($_SESSION['crm_user']->crm_userId)) {
                    $new->last_update_userid = (int)$_SESSION['crm_user']->crm_userId;
                }
                $res = $new->save();
                $copied += ($res !== false) ? 1 : 0;
                $failed += ($res === false) ? 1 : 0;
            } else {
                $failed++;
            }
        }
        if ($copied > 0) { $this->validationMessageService->addSuccess($copied . ' campagne(s) copiée(s).'); }
        if ($failed > 0) { $this->validationMessageService->addError($failed . ' copie(s) impossible(s).'); }
    }

    /**
     * Suppression (soft delete) en masse des campagnes clients
     * @param array $ids
     * @return void
     */
    private function deleteUserCampaigns(array $ids): void
    {
        $deleted = 0; $failed = 0;
        foreach ($ids as $id) {
            $m = new \Models\UserCampaign();
            $ok = $m->get((int)$id);
            if ($ok !== false && !empty($m->usercampaignId)) {
                $m->deleted = 'yes';
                $m->timestamp_update = time();
                if (isset($_SESSION['crm_user']) && isset($_SESSION['crm_user']->crm_userId)) {
                    $m->last_update_userid = (int)$_SESSION['crm_user']->crm_userId;
                }
                $res = $m->save();
                $deleted += ($res !== false) ? 1 : 0;
                $failed += ($res === false) ? 1 : 0;
            } else {
                $failed++;
            }
        }
        if ($deleted > 0) { $this->validationMessageService->addSuccess($deleted . ' campagne(s) supprimée(s).'); }
        if ($failed > 0) { $this->validationMessageService->addError($failed . ' suppression(s) impossible(s).'); }
    }

    public function blanck($params=[]) {
        return $this->view("admin.template", $params);
    }
    
    /**
     * Récupère la liste des boutiques depuis l'API
     * @return array Liste des boutiques
     */
    private function getShopsList(): array
    {
        try {
            // Pour l'instant, retourner une liste vide
            // TODO: Implémenter l'appel API quand URL_SHOP sera disponible
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Récupère le solde d'une boutique
     * @param int $shopId ID de la boutique
     * @return float|null Solde de la boutique
     */
    private function getShopBalance($shopId): ?float
    {
        try {
            // Fonction legacy get_solde_shop
            if (function_exists('get_solde_shop')) {
                return get_solde_shop($shopId);
            }
            
            // Si URL_SHOP est définie, faire l'appel API
            if (defined('URL_SHOP') && !empty($shopId)) {
                $shop_api_url = URL_SHOP . "/api/v1/balance?comId=" . $shopId;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $shop_api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200 && $response) {
                    $result = json_decode($response);
                    if (isset($result->success) && $result->success === true) {
                        return isset($result->balance) ? (float)$result->balance : 0;
                    }
                }
            }
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Affiche la liste des vérifications (version minimale - variables vides)
     * @param array $params
     * @return HttpResponse
     */
    public function verificationList($params = [])
    {
        // Gestion des actions (copy/delete)
        if (isset($params['submit']) && $params['submit'] === 'valid') {
            $action = $params['action'] ?? '';
            $idArray = $params['id_array'] ?? [];

            if (empty($idArray) || !is_array($idArray)) {
                $this->validationMessageService->addError('Vous devez sélectionner au moins une validation.');
            } elseif (empty($action)) {
                $this->validationMessageService->addError('Vous devez sélectionner une action.');
            } else {
                $ids = array_keys($idArray);
                switch ($action) {
                    case 'copy':
                        $copied = 0; $failed = 0;
                        foreach ($ids as $id) {
                            $model = new Validation();
                            $original = $model->get((int)$id);
                            if ($original !== false && !empty($model->validationId)) {
                                // Créer une copie
                                $new = new Validation();
                                $new->importObj($model);
                                $new->validationId = null;
                                // Nom et timestamp
                                $new->name = trim(($new->name ?? 'Validation') . ' (copie)');
                                $new->timestamp = time();
                                // Sauvegarde
                                $res = $new->save();
                                $copied += ($res !== false) ? 1 : 0;
                                $failed += ($res === false) ? 1 : 0;
                            } else {
                                $failed++;
                            }
                        }
                        if ($copied > 0) {
                            $this->validationMessageService->addSuccess($copied . ' validation(s) copiée(s).');
                        }
                        if ($failed > 0) {
                            $this->validationMessageService->addError($failed . ' copie(s) impossible(s).');
                        }
                        break;

                    case 'delete':
                        $deleted = 0; $failed = 0;
                        foreach ($ids as $id) {
                            $m = new Validation();
                            $ok = $m->delete((int)$id);
                            $deleted += $ok ? 1 : 0;
                            $failed += $ok ? 0 : 1;
                        }
                        if ($deleted > 0) {
                            $this->validationMessageService->addSuccess($deleted . ' validation(s) supprimée(s).');
                        }
                        if ($failed > 0) {
                            $this->validationMessageService->addError($failed . ' suppression(s) impossible(s).');
                        }
                        break;

                    default:
                        $this->validationMessageService->addError('Action non valide.');
                }
            }
        }

        // Récupération de la liste (avec tri par défaut)
        $validationModel = new Validation();
        $validationList = $validationModel->getList(500, null, null, null, 'validationid', 'desc');

        return $this->view("admin.verificationlist", [
            'title' => "Liste des vérifications",
            'validationList' => $validationList,
            'params' => $params,
            'messages' => $this->validationMessageService->getMessages()
        ]);
    }

    /**
     * Affiche le détail d'une vérification (formulaire)
     * - Si un id est fourni, tente de charger la vérification
     * - Sinon, affiche un formulaire vide
     * @param array $params
     * @return HttpResponse
     */
    public function verificationAdd($params = [])
    {
        $validation = new Validation();

        // Gestion des actions (aligné sur la structure de useradd)
        if (isset($params['submit'])) {
            switch ($params['submit']) {
                case 'valid':
                    // Charger si un ID existe (priorité au champ caché validationid du formulaire)
                    $editId = null;
                    if (!empty($params['validationid']) && ctype_digit((string)$params['validationid'])) {
                        $editId = (int)$params['validationid'];
                    } elseif (!empty($params['id']) && ctype_digit((string)$params['id'])) {
                        $editId = (int)$params['id'];
                    }

                    if ($editId) {
                        $validation->get($editId);
                    }

                    // Hydratation contrôlée depuis le schéma du modèle
                    $schema = Validation::getSchema();
                    foreach ($schema as $prop => $def) {
                        if (array_key_exists($prop, $params)) {
                            $validation->$prop = $params[$prop];
                        }
                    }
                    // Valeurs par défaut utiles
                    if (empty($validation->name)) {
                        $validation->name = 'Validation '.rand(10000,99999);
                    }
                    if (empty($validation->timestamp)) {
                        $validation->timestamp = time();
                    }

                    // Validation via le service dédié
                    $verificationValidationService = new \Services\Validation\VerificationValidationService();
                    $violations = $verificationValidationService->validateVerification($params);
                    if (count($violations) > 0) {
                        $this->validationMessageService->addViolations($violations);
                        // Ne pas sauvegarder si erreurs de validation
                        break;
                    }

                    // Sauvegarde
                    $res = $validation->save();
                    if ($res !== false) {
                        $this->validationMessageService->addSuccess('Validation enregistrée.');
                    } else {
                        $this->validationMessageService->addError("Erreur lors de l'enregistrement de la validation.");
                    }
                    break;

                case 'delete':
                    // Suppression puis redirection vers la liste
                    $deleteId = null;
                    if (!empty($params['validationid']) && ctype_digit((string)$params['validationid'])) {
                        $deleteId = (int)$params['validationid'];
                    } elseif (!empty($params['id']) && ctype_digit((string)$params['id'])) {
                        $deleteId = (int)$params['id'];
                    }

                    if ($deleteId) {
                        $m = new Validation();
                        $ok = $m->delete($deleteId);
                        if ($ok) {
                            $this->validationMessageService->addSuccess('Validation supprimée.');
                            $url = URL_SITE . '/admin/verificationlist/';
                            \Classes\redirect($url);
                            return false;
                        }
                        $this->validationMessageService->addError('Erreur lors de la suppression.');
                    } else {
                        $this->validationMessageService->addError('Identifiant de validation manquant.');
                    }
                    break;
            }
        }

        // Chargement pour affichage si un ID est passé (GET/params)
        if (empty($validation->validationId)) {
            $viewId = null;
            if (!empty($params['validationid']) && ctype_digit((string)$params['validationid'])) {
                $viewId = (int)$params['validationid'];
            } elseif (!empty($params['id']) && ctype_digit((string)$params['id'])) {
                $viewId = (int)$params['id'];
            }
            if ($viewId) {
                $validation->get($viewId);
                if (empty($validation->validationId)) {
                    $this->validationMessageService->addError('Validation introuvable.');
                }
            }
        }

        return $this->view("admin.verificationadd", [
            'title' => !empty($validation->validationId) ? "Modification d'une vérification" : "Ajout d'une vérification",
            'validation' => $validation,
            'params' => $params,
            'messages' => $this->validationMessageService->getMessages(),
            'validationMessageService' => $this->validationMessageService,
            'validationService' => new \Services\Validation\VerificationValidationService()
        ]);
    }
}
