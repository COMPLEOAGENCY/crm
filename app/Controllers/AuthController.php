<?php
// Path: src/app/Controllers/AuthController.php
namespace Controllers;

use Framework\Controller;
use Framework\HttpRequest;
use Framework\HttpResponse;
use Framework\SessionHandler;
use Framework\CacheManager;
use Models\User;
use Models\CrmUser;
use Classes\Logger;
use crm_user;
use Services\Validation\ValidationMessageService;

/**
 * Contrôleur de gestion de l'authentification
 * 
 * Gère les processus d'authentification pour les utilisateurs CRM et standards.
 * Implémente la logique de connexion, validation et redirection post-login.
 *
 * Fonctionnalités :
 * - Authentification double (CRM/Standard)
 * - Validation des données de connexion
 * - Gestion des messages d'erreur
 * - Redirection intelligente selon le rôle
 * - Journalisation des tentatives
 *
 * @package Controllers
 * @uses \Framework\Controller
 * @uses \Models\User
 * @uses \Models\CrmUser
 * @uses \Services\Validation\ValidationMessageService
 */
class AuthController extends Controller
{
    /** @var ValidationMessageService Service de gestion des messages de validation */
    private $validationMessageService;
    
    /** @var User Modèle utilisateur standard */
    private $user;
    
    /** @var CrmUser Modèle utilisateur CRM */
    private $crmUser;
    
    /** @var SessionHandler Gestionnaire de session */
    private $session;

    /**
     * Initialise le contrôleur d'authentification
     *
     * @param HttpRequest $httpRequest Requête HTTP
     * @param HttpResponse $httpResponse Réponse HTTP
     */
    public function __construct(HttpRequest $httpRequest, HttpResponse $httpResponse)
    {
        parent::__construct($httpRequest, $httpResponse);
        $this->validationMessageService = new ValidationMessageService();
        $this->user = new User();
        $this->crmUser = new CrmUser();
        $this->session = SessionHandler::getInstance();
    }

    /**
     * Gère le processus de connexion
     *
     * Vérifie si l'utilisateur est déjà connecté, traite le formulaire
     * de connexion ou affiche le formulaire selon le contexte.
     *
     * @return HttpResponse Vue de connexion ou redirection
     */
    public function login()
    {

        // Si déjà connecté, rediriger vers la page appropriée
        if ($this->session->get('connexion')) {
            $userType = $this->session->has('crm_user') 
                ? ($this->session->get('crm_user')->crm_user_role ?? '')
                : ($this->session->get('user')->type ?? '');
                \Classes\redirectByUserType($userType);
        }

        // Traitement du formulaire de connexion
        $params = $this->_httpRequest->getParams();
        if (isset($params['connexion']) && $params['connexion'] === 'valid') {
            return $this->processLogin($params);
        }

        // Affichage du formulaire
        return $this->view('admin.login', [
            'title' => 'Connexion',             
            'messages' => $this->validationMessageService->getMessages()
        ]);
    }

    /**
     * Traite la soumission du formulaire de connexion
     *
     * Processus :
     * 1. Valide les champs requis
     * 2. Tente l'authentification CRM
     * 3. Si échec, tente l'authentification standard
     * 4. Gère les erreurs et journalise les tentatives
     *
     * @param array $params Paramètres du formulaire
     * @return HttpResponse Redirection ou vue avec messages d'erreur
     * @access private
     * @throws \Exception En cas d'erreur système
     */
    private function processLogin(array $params): HttpResponse
    {
        try {
            // Validation des champs
            if (empty($params['email']) || empty($params['password'])) {
                $this->validationMessageService->addError("Veuillez remplir tous les champs");
                return $this->view('admin.login', [
                    'messages' => $this->validationMessageService->getMessages()
                ]);
            }

            // Tentative de connexion utilisateur CRM
            $crmUser = $this->authenticateCrmUser($params['email'], $params['password']);
            if ($crmUser) {
                return $this->handleSuccessfulCrmLogin($crmUser, $params);
            }

            // Tentative de connexion utilisateur normal
            $user = $this->authenticateUser($params['email'], $params['password']);
            if ($user) {
                return $this->handleSuccessfulUserLogin($user, $params);
            }

            // Échec de l'authentification
            $this->validationMessageService->addError("Email ou mot de passe incorrect");
            Logger::debug("Tentative de connexion échouée", [
                'email' => $params['email'],
                'ip' => $this->_httpRequest->getClientIp()
            ]);

            return $this->view('admin.login', [
                'messages' => $this->validationMessageService->getMessages()
            ]);

        } catch (\Exception $e) {
            Logger::critical("Erreur lors de la tentative de connexion", [], $e);
            $this->validationMessageService->addError("Une erreur est survenue lors de la connexion");
            
            return $this->view('admin.login', [
                'messages' => $this->validationMessageService->getMessages()
            ]);
        }
    }

    /**
     * Tente l'authentification d'un utilisateur CRM
     *
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe en clair
     * @return CrmUser|null L'utilisateur authentifié ou null
     * @access private
     */
    private function authenticateCrmUser(string $email, string $password)
    {
        $users = $this->crmUser->getList(1, [
            'crm_user_email' => $email,
            'crm_user_password' => md5('compleo'.$password)
        ]);

        

        return !empty($users) ? $users[0] : null;
    }

    /**
     * Tente l'authentification d'un utilisateur standard
     *
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe en clair
     * @return User|null L'utilisateur authentifié ou null
     * @access private
     */
    private function authenticateUser(string $email, string $password)
    {
        $users = $this->user->getList(1, [
            'email' => $email,
            'password' => md5($password),
            'statut' => 'on'
        ]);

        return !empty($users) ? $users[0] : null;
    }

    /**
     * Gère la connexion réussie d'un utilisateur CRM
     *
     * Met à jour la date de dernière connexion, crée la session et redirige
     * l'utilisateur vers la page appropriée.
     *
     * @param CrmUser $crmUser Utilisateur CRM authentifié
     * @param array $params Paramètres du formulaire
     * @return HttpResponse Redirection
     * @access private
     */
    private function handleSuccessfulCrmLogin($crmUser, array $params): HttpResponse
    {
        // Mise à jour date dernière connexion

        $crmUserObj = new CrmUser();
        $crmUserObj->Get($crmUser->crmUserId);
        $crmUserObj->crm_user_last_connexion_date = time();
        
        $crmUserObj->Save();
        // Création de la session
        $this->session->set('connexion', true);
        $this->session->set('crm_user', $crmUser);

        // Enregistrer dans la session PHP legacy

        // Redirection
        if (!empty($params['request_uri'])) {
            $redirect_url = \Classes\cleanUrl($params['request_uri']);
        } else {
            $redirect_url = '/admin/';
        }

                // Enregistrer dans la session PHP legacy puis redirection normale
        // bride_session url
        $session = json_encode([
            'connexion' => true,
            'crm_user' => $crmUser
        ]);
        $url = URL_SITE.'/session_bridge.php?session='.$session.'&redirect='.$redirect_url;
        \Classes\redirect($url);
        // \Classes\redirect('/admin/');
        
    }

    /**
     * Gère la connexion réussie d'un utilisateur standard
     *
     * Met à jour la date de dernière connexion, crée la session et redirige
     * l'utilisateur vers la page appropriée.
     *
     * @param User $user Utilisateur standard authentifié
     * @param array $params Paramètres du formulaire
     * @return HttpResponse Redirection
     * @access private
     */
    private function handleSuccessfulUserLogin($user, array $params): HttpResponse
    {
        // Mise à jour date dernière connexion
        $user->timestamp_connexion = time();
        $user->save();

        // Création de la session
        $this->session->set('connexion', true);
        $this->session->set('user', $user);




        // Redirection
        if (!empty($params['request_uri'])) {
            \Classes\redirect(\Classes\cleanUrl($params['request_uri']));
        }


        // Enregistrer dans la session PHP legacy puis redirection normale
        // bride_session url
        $session = json_encode([
            'connexion' => true,
            'user' => $user
        ]);
        $url = URL_SITE.'/session_bridge.php?session='.$session.'&redirect='.$this->getDefaultRedirect($user->type);
        \Classes\redirect($url);

        // \Classes\redirect($this->getDefaultRedirect($user->type));
    }

    /**
     * Détermine la redirection par défaut en fonction du type d'utilisateur
     *
     * @param string $userType Type d'utilisateur
     * @return string URL de redirection
     * @access private
     */
    private function getDefaultRedirect(string $userType): string
    {
        $redirectMap = [
            'admin' => '/admin/',
            'provider' => '/affiliate/',
            'client' => '/user/',
            'user' => '/admin/'
        ];

        return $redirectMap[$userType] ?? '/admin/';
    }

    /**
     * Gère la déconnexion de l'utilisateur
     *
     * Efface la session et redirige l'utilisateur vers la page de connexion.
     *
     * @return HttpResponse Redirection
     */
    public function logout(): HttpResponse
    {
        $this->session->clearSession();
        \Classes\redirect('/loginuser/', []);
    }
}
