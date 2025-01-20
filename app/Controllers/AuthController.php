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

class AuthController extends Controller
{
    private $validationMessageService;
    private $user;
    private $crmUser;
    private $session;

    public function __construct(HttpRequest $httpRequest, HttpResponse $httpResponse)
    {
        parent::__construct($httpRequest, $httpResponse);
        $this->validationMessageService = new ValidationMessageService();
        $this->user = new User();
        $this->crmUser = new CrmUser();
        $this->session = SessionHandler::getInstance();
    }

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

    private function authenticateCrmUser(string $email, string $password)
    {
        $users = $this->crmUser->getList(1, [
            'crm_user_email' => $email,
            'crm_user_password' => md5('compleo'.$password)
        ]);

        

        return !empty($users) ? $users[0] : null;
    }

    private function authenticateUser(string $email, string $password)
    {
        $users = $this->user->getList(1, [
            'email' => $email,
            'password' => md5($password),
            'statut' => 'on'
        ]);

        return !empty($users) ? $users[0] : null;
    }

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

    public function logout(): HttpResponse
    {
        $this->session->clearSession();
        \Classes\redirect('/loginuser/', []);
    }
    
    
}
