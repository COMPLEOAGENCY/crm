<?php
namespace Services;

use Models\User;
use Models\CrmUser;
use Models\Sale;
use Models\Administration;
use Models\Invoice;
use Classes\Logger;
use Framework\SessionHandler;
use Services\Validation\UserValidationService;
use Services\Validation\ValidationMessageService;

class UserService
{
    public $user;
    public $crmUser;
    public $session;
    public $validationService;
    public $validationMessageService;
    public $balanceService;

    public function __construct(
        User $user,
        CrmUser $crmUser
    ) {
        $this->user = $user;
        $this->crmUser = $crmUser;
        $this->session = SessionHandler::getInstance();
        $this->validationService = new UserValidationService();
        $this->validationMessageService = new ValidationMessageService();
        $this->balanceService = new BalanceService(
            new User(),
            new Sale(),
            new Administration(),
            new Invoice()
        );
    }

    /**
     * Récupère un compte par son ID
     */
    public function getUserById(int $userId): ?User
    {
        try {
            return $this->user->get($userId) ?: null;
        } catch (\Exception $e) {
            Logger::critical("Erreur lors de la récupération du compte", ['userId' => $userId], $e);
            return null;
        }
    }


    
    /**
     * Récupère les détails complets d'un compte, y compris son solde
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
            Logger::critical("Erreur lors de la récupération des détails du compte", ['userId' => $userId], $e);
            return ['user' => $user];
        }
    }

    /**
     * Vérifie si un compte a dépassé son encours maximum
     */
    public function hasExceededCreditLimit(int $userId): bool
    {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return false;
            }

            $soldeDetails = $this->balanceService->getSoldeDetails($userId);
            return -$soldeDetails['solde'] > $user->encours_max;
        } catch (\Exception $e) {
            Logger::critical("Erreur lors de la vérification du encours", ['userId' => $userId], $e);
            return false;
        }
    }

    /**
     * Récupère la liste des comptes du CRM
     */
    public function getAllCrmUsers(): array
    {
        try {
            $crmUsers = $this->crmUser->getAll();
            $indexedCrmUsers = [];
            foreach ($crmUsers as $crmUser) {
                $indexedCrmUsers[$crmUser->crm_userId] = $crmUser;
            }
            return $indexedCrmUsers;
        } catch (\Exception $e) {
            Logger::critical("Erreur lors de la récupération des comptes CRM", [], $e);
            return [];
        }
    }

    /**
     * Récupère la liste des comptes
     */
    public function getAllUsers(): array
    {
        try {
            return $this->user->getAll() ?? [];
        } catch (\Exception $e) {
            Logger::critical("Erreur lors de la récupération des comptes", [], $e);
            return [];
        }
    }

    /**
     * Récupère la liste filtrée des comptes
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
            Logger::critical("Erreur lors de la récupération des comptes filtrés", [], $e);
            return [];
        }
    }

    /**
     * Sauvegarde un compte
     */
    public function saveUser(array $userData): bool
    {
        try {
            $violations = $this->validationService->validateUser($userData);
            if (count($violations) > 0) {
                $this->validationMessageService->addViolations($violations);
                return false;
            }

            $user = !empty($userData['userid']) ? $this->user->get($userData['userid']) : $this->user;
            
            if (!$user) {
                $this->validationMessageService->addError("Compte non trouvé.");
                return false;
            }

            $this->updateUserFields($user, $userData);
            $user->last_update_timestamp = time();
            $user->last_update_userid = $this->getCurrentUserId();

            if ($user->save()) {
                $this->validationMessageService->addSuccess("Le compte a été enregistré avec succès.");
                return true;
            }

            $this->validationMessageService->addError("Erreur lors de l'enregistrement.");
            return false;

        } catch (\Exception $e) {
            Logger::critical("Erreur lors de la sauvegarde du compte", [], $e);
            $this->validationMessageService->addError("Une erreur est survenue: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Synchronise un compte
     */
    public function synchronizeUser(int $userId): bool
    {
        try {
            if (!$this->getUserById($userId)) {
                $this->validationMessageService->addError("Compte non trouvé");
                return false;
            }

            // Logique de synchronisation à implémenter
            $result = true;

            if ($result) {
                $this->validationMessageService->addSuccess("Le compte a été synchronisé avec succès.");
                return true;
            }

            $this->validationMessageService->addError("Erreur lors de la synchronisation.");
            return false;

        } catch (\Exception $e) {
            Logger::critical("Erreur lors de la synchronisation", ['userId' => $userId], $e);
            $this->validationMessageService->addError("Une erreur est survenue: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un compte
     */
    public function deleteUser(int $userId): bool
    {
        try {
            if (!$this->getUserById($userId) || !$this->user->delete($userId)) {
                $this->validationMessageService->addError("Erreur lors de la suppression.");
                return false;
            }
            $this->validationMessageService->addSuccess("Le compte a été supprimé avec succès.");
            return true;
        } catch (\Exception $e) {
            Logger::critical("Erreur lors de la suppression du compte", ['userId' => $userId], $e);
            $this->validationMessageService->addError("Une erreur est survenue: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Méthodes privées utilitaires
     */
    private function buildFilterConditions(array $params): array
    {
        $conditions = [];
        if (!empty($params['userid'])) $conditions[] = ['userId', '=', $params['userid']];
        if (!empty($params['type'])) $conditions[] = ['type', '=', $params['type']];
        if (!empty($params['crm_userid'])) $conditions[] = ['vendor_id', '=', $params['crm_userid']];
        if (isset($params['statut']) && in_array($params['statut'], ['on', 'off'])) {
            $conditions[] = ['statut', '=', $params['statut']];
        }
        return $conditions;
    }

    private function getRecentSales(int $userId, int $limit = 10): array
    {
        $sale = new Sale();
        $sales = $sale->getList($limit, [['userid', '=', $userId]], null, null, 'timestamp', 'desc');
        return $sales !== false ? $sales : [];
    }

    private function getSubUsers(int $userId): array
    {
        return []; // Implémentation à faire selon votre modèle de données
    }

    private function getCurrentUserId(): int
    {
        return $this->session->get('crm_user')->crm_userId ?? 0;
    }
    /**
     * Met à jour les champs d'un compte
     */
    public function updateUserFields(User $user, array $userData): void
    {
        $dateFields = ['timestamp', 'billing_start_date', 'pro_start_date', 'last_update_timestamp','timestamp_connexion'];
        foreach (User::$SCHEMA as $field => $schema) {
            if (isset($userData[$field])) {
                $user->$field = in_array($field, $dateFields) 
                    ? strtotime($userData[$field]) 
                    : $userData[$field];
            }
        }
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
            $this->validationMessageService->addError(
                'Erreur lors de la synchronisation de certains comptes.', 
                $errors
            );
        } else {
            $this->validationMessageService->addSuccess('Les comptes ont été synchronisés avec succès.');
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
            $this->validationMessageService->addError(
                'Certains comptes n\'ont pas pu être copiés.', 
                $errors
            );
        } else {
            $this->validationMessageService->addSuccess('Les comptes ont été copiés avec succès.');
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
            $this->validationMessageService->addError(
                'Certains comptes n\'ont pas pu être supprimés.', 
                $errors
            );
        } else {
            $this->validationMessageService->addSuccess('Les comptes ont été supprimés avec succès.');
        }
    }    


}