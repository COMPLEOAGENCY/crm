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

class UserService
{
    public User $user;
    public CrmUser $crmUser;
    public SessionHandler $session;
    public UserValidationService $validationService;
    public ValidationMessageService $validationMessageService;
    public BalanceService $balanceService;

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
     * @param int $userId
     * @return User|bool|null
     */
    public function getUserById(int $userId): User|bool|null
    {
        try {
            return $this->user->get($userId);
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la récupération du compte", 0, $e);
        }
    }

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

    public function hasExceededCreditLimit(int $userId): bool
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            return false;
        }

        $soldeDetails = $this->balanceService->getSoldeDetails($userId);
        return -$soldeDetails['solde'] > $user->encours_max;
    }

    public function getAllCrmUsers(): array
    {
        try {
            return array_column($this->crmUser->getAll(), null, 'crm_userId');
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la récupération des comptes CRM", 0, $e);
        }
    }

    public function getAllUsers(): array
    {
        try {
            return $this->user->getAll() ?? [];
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la récupération des comptes", 0, $e);
        }
    }

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
