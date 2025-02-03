<?php
namespace Services;

use Models\User;
use Models\CrmUser;
use Models\Sale;
use Models\Administration;
use Models\Invoice;
use Framework\SessionHandler;
use phpDocumentor\Reflection\Types\Boolean;
use Services\Validation\UserValidationService;
use Services\Validation\ValidationMessageService;
use user as GlobalUser;

/**
 * Service de gestion des utilisateurs
 * 
 * Gère toutes les opérations liées aux utilisateurs :
 * - CRUD utilisateurs standards et CRM
 * - Synchronisation entre utilisateurs standards et CRM
 * - Gestion des limites de crédit
 * - Récupération des détails utilisateurs (soldes, ventes, etc.)
 *
 * Intègre :
 * - Validation des données utilisateur
 * - Gestion des messages de validation
 * - Calcul des soldes via BalanceService
 *
 * @package Services
 * @uses \Models\User
 * @uses \Models\CrmUser
 * @uses \Framework\SessionHandler
 * @uses \Services\Validation\UserValidationService
 * @uses \Services\BalanceService
 */
class UserService
{
    /** @var User Modèle utilisateur standard */
    public User $user;

    /** @var CrmUser Modèle utilisateur CRM */
    public CrmUser $crmUser;

    /** @var SessionHandler Gestionnaire de session */
    public SessionHandler $session;

    /** @var UserValidationService Service de validation des données utilisateur */
    public UserValidationService $validationService;

    /** @var ValidationMessageService Service de gestion des messages de validation */
    public ValidationMessageService $validationMessageService;

    /** @var BalanceService Service de gestion des soldes */
    public BalanceService $balanceService;

    /**
     * Initialise le service utilisateur avec ses dépendances
     *
     * @param User $user Modèle utilisateur standard
     * @param CrmUser $crmUser Modèle utilisateur CRM
     * @param SessionHandler $session Gestionnaire de session
     * @param UserValidationService $validationService Service de validation
     * @param ValidationMessageService $validationMessageService Service de messages
     * @param BalanceService $balanceService Service de gestion des soldes
     */
    public function __construct(
        User $user,
        CrmUser $crmUser,
        SessionHandler $session,
        UserValidationService $validationService,
        ValidationMessageService $validationMessageService,
        BalanceService $balanceService
    ) {
        $this->user = $user;
        $this->crmUser = $crmUser;
        $this->session = $session;
        $this->validationService = $validationService;
        $this->validationMessageService = $validationMessageService;
        $this->balanceService = $balanceService;
    }

    /**
     * Récupère un utilisateur par son ID
     *
     * @param int $userId ID de l'utilisateur
     * @return User|bool|null L'utilisateur trouvé, false si erreur, null si non trouvé
     * @throws \RuntimeException En cas d'erreur de récupération
     */
    public function getUserById(int $userId): User|bool|null
    {
        try {
            return $this->user->get($userId);
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la récupération du compte", 0, $e);
        }
    }

    /**
     * Récupère les détails complets d'un utilisateur
     *
     * Inclut :
     * - Informations de base de l'utilisateur
     * - Solde et détails financiers
     * - Ventes récentes
     * - Utilisateurs subordonnés
     *
     * @param int $userId ID de l'utilisateur
     * @return array{
     *     user: User,
     *     balance: array,
     *     recentSales: array,
     *     subUsers: array
     * } Détails de l'utilisateur
     * @throws \RuntimeException En cas d'erreur de récupération
     */
    public function getUserDetails(int $userId): array
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            return [];
        }

        try {
            return [
                'user' => $user,
                'balance' => $this->balanceService->getSoldeDetails($userId),
                'recentSales' => $this->getRecentSales($userId),
                'subUsers' => $this->getSubUsers($userId)
            ];
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la récupération des détails du compte", 0, $e);
        }
    }

    /**
     * Vérifie si un utilisateur a dépassé sa limite de crédit
     *
     * Compare le solde négatif avec la limite de crédit autorisée
     * définie dans encours_max.
     *
     * @param int $userId ID de l'utilisateur
     * @return bool True si la limite est dépassée
     */
    public function hasExceededCreditLimit(int $userId): bool
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            return false;
        }

        $soldeDetails = $this->balanceService->getSoldeDetails($userId);
        return -$soldeDetails['solde'] > $user->encours_max;
    }

    /**
     * Récupère tous les utilisateurs CRM
     *
     * Retourne un tableau indexé par crm_userId pour faciliter
     * les recherches et associations.
     *
     * @return array<int,CrmUser> Liste des utilisateurs CRM
     * @throws \RuntimeException En cas d'erreur de récupération
     */
    public function getAllCrmUsers(): array
    {
        try {
            return array_column($this->crmUser->getAll(), null, 'crm_userId');
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la récupération des comptes CRM", 0, $e);
        }
    }

    /**
     * Récupère tous les utilisateurs standards
     *
     * @return array<User> Liste des utilisateurs standards
     * @throws \RuntimeException En cas d'erreur de récupération
     */
    public function getAllUsers(): array
    {
        try {
            return $this->user->getAll() ?? [];
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la récupération des comptes", 0, $e);
        }
    }

    /**
     * Sauvegarde un utilisateur (création ou mise à jour)
     *
     * Processus :
     * 1. Validation des données
     * 2. Vérification des doublons email
     * 3. Création/Mise à jour de l'utilisateur
     * 4. Gestion des messages de validation
     *
     * @param array $userData Données de l'utilisateur
     * @return bool True si la sauvegarde est réussie
     * @throws \RuntimeException En cas d'erreur de sauvegarde
     */
    public function saveUser(array $userData): bool
    {
        try {

            // Validation des données utilisateur
            $violations = $this->validationService->validateUser($userData);
            if (count($violations) > 0) {
                $this->validationMessageService->addViolations($violations);
                return false;
            }

            // Chargement ou création de l'utilisateur
            $user = !empty($userData['userid']) ? $this->user->get($userData['userid']) : $this->user;
            if (!$user) {
                return $this->addValidationError("Compte non trouvé.");
            }

            // Mise à jour des champs de l'utilisateur
            $this->updateUserFields($user, $userData);
            $user->last_update_timestamp = time();
            $user->last_update_userid = $this->getCurrentUserId();

            // Sauvegarde et message de validation
            $id=$user->save();
            if($id > 0){
                $this->addValidationSuccess("Le compte a été enregistré avec succès.");
                return $id;
            }else{
                $this->addValidationError("Erreur lors de l'enregistrement.");
                return false;
            }
            
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la sauvegarde du compte", 0, $e);
        }
    }

    private function addValidationError(string $message): bool
    {
        $this->validationMessageService->addError($message);
        return false;
    }

    private function addValidationSuccess(string $message): bool
    {
        $this->validationMessageService->addSuccess($message);
        return true;
    }

    public function getRecentSales(int $userId, int $limit = 10): array
    {
        return (new Sale())->getList($limit, [['userid', '=', $userId]], null, null, 'timestamp', 'desc') ?: [];
    }

    public function getSubUsers(int $userId): array
    {
        return []; // Implémentation à compléter selon votre logique
    }

    public function getCurrentUserId(): int
    {
        return $this->session->get('crm_user')->crm_userId ?? 0;
    }

    public function updateUserFields(User $user, array $userData): void
    {
        $dateFields = ['timestamp', 'billing_start_date', 'pro_start_date', 'last_update_timestamp', 'timestamp_connexion'];
        foreach (User::$SCHEMA as $field => $schema) {
            if (isset($userData[$field])) {
                $user->$field = in_array($field, $dateFields) ? strtotime($userData[$field]) : $userData[$field];
            }
        }
    }

        /**
     * Synchronise un compte
     */
    public function synchronizeUser(int $userId): bool
    {
        $this->addValidationError("Erreur de synchronisation.");
        return false; // Implémentation à compléter selon votre logique
        
    }

    /**
     * Récupère la liste filtrée des comptes.
     */
    public function getFilteredUsers(array $params): array
    {
        $conditions = $this->buildFilterConditions($params);

        try {
            $users = empty($conditions) ? $this->getAllUsers() : $this->user->getList(null, $conditions);

            if (isset($params['statut']) && $params['statut'] === 'credit_over') {
                $users = array_filter($users, fn($user) => $this->hasExceededCreditLimit($user->userId));
            }

            foreach ($users as &$user) {
                $user->solde_details = $this->balanceService->getSoldeDetails($user->userId);
            }

            return $users;
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la récupération des comptes filtrés", 0, $e);
        }
    }

    /**
     * Méthode privée utilitaire pour construire les conditions de filtre.
     */
    private function buildFilterConditions(array $params): array
    {
        $conditions = [];
        if (!empty($params['userid'])) {
            $conditions[] = ['userId', '=', $params['userid']];
        }
        if (!empty($params['type'])) {
            $conditions[] = ['type', '=', $params['type']];
        }
        if (!empty($params['crm_userid'])) {
            $conditions[] = ['vendor_id', '=', $params['crm_userid']];
        }
        if (isset($params['statut']) && in_array($params['statut'], ['on', 'off'])) {
            $conditions[] = ['statut', '=', $params['statut']];
        }
        return $conditions;
    }
        
    /**
     * Synchronise plusieurs comptes
     */
    public function batchSynchronizeUsers(array $userIds): void
    {
        $errors = [];
        foreach ($userIds as $userId) {
            if (!$this->synchronizeUser($userId)) {
                $errors[] = "Erreur lors de la synchronisation du compte $userId";
            }
        }

        if (!empty($errors)) {
            throw new \RuntimeException(
                'Erreur lors de la synchronisation de certains comptes : ' . implode(', ', $errors)
            );
        } else {
            $this->addValidationSuccess("Les comptes ont été synchronisés avec succès.");
        }
    }

    /**
     * Copie plusieurs comptes
     */
    public function batchCopyUsers(array $userIds): void
    {
        $errors = [];
        foreach ($userIds as $userId) {
            try {
                $user = $this->getUserById($userId);
                if (!$user) {
                    $errors[] = "Le compte $userId n'existe pas";
                    continue;
                }

                $newUser = clone $user;
                $newUser->userId = null; // Force l'insertion

                if (!$newUser->save()) {
                    $errors[] = "Erreur lors de la création de la copie du compte $userId";
                }
            } catch (\Exception $e) {
                $errors[] = "Erreur pour le compte $userId: " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw new \RuntimeException(
                'Certains comptes n\'ont pas pu être copiés : ' . implode(', ', $errors)
            );
        } else {
            $this->addValidationSuccess("Les comptes ont été copiés avec succès.");
        }
    }

    /**
     * Supprime plusieurs comptes
     */
    public function batchDeleteUsers(array $userIds): void
    {
        $errors = [];
        foreach ($userIds as $userId) {
            if (!$this->deleteUser($userId)) {
                $errors[] = "Erreur lors de la suppression du compte $userId";
            }
        }

        if (!empty($errors)) {
            throw new \RuntimeException(
                'Certains comptes n\'ont pas pu être supprimés : ' . implode(', ', $errors)
            );
        } else {
            $this->addValidationSuccess("Les comptes ont été supprimés avec succès.");
        }
    }

    /**
     * Supprime un compte
     */
    public function deleteUser(int $userId): bool
    {
        try {
            $user = $this->getUserById($userId);
            if (!$user || !$this->user->delete($userId)) {
                throw new \RuntimeException("Erreur lors de la suppression du compte avec l'ID $userId.");
            }
            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException("Une erreur est survenue lors de la suppression du compte : " . $e->getMessage(), 0, $e);
        }
    }



}
