<?php

namespace App\Models\Adapters;

use Models\Lead as LegacyLead;
use Models\ValidationHistory;

class ValidationHistoryAdapter implements LeadComponentInterface
{
    private LegacyLead $legacyLead;
    private ValidationHistory $validationHistory;
    private array $history = [];
    
    public function __construct(LegacyLead $legacyLead)
    {
        $this->legacyLead = $legacyLead;
        $this->validationHistory = new ValidationHistory();
        $this->loadHistory();
    }
    
    private function loadHistory(): void
    {
        if ($this->legacyLead->leadId) {
            $this->history = $this->validationHistory->getHistoryForLead($this->legacyLead->leadId);
        }
    }
    
    public function getData(): array
    {
        $formattedHistory = [];
        foreach ($this->history as $entry) {
            $formattedHistory[] = [
                'id' => $entry->validationHistoryId,
                'timestamp' => $entry->timestamp,
                'validationId' => $entry->validationId,
                'result' => $entry->validationResult,
                'scoring' => $entry->scoringAction,
                'status' => $entry->statutAction,
                'validations' => [
                    'phone' => $entry->phoneVal,
                    'phone2' => $entry->phone2Val,
                    'email' => $entry->emailVal,
                    'city' => $entry->cityVal
                ]
            ];
        }
        
        return [
            'count' => count($this->history),
            'entries' => $formattedHistory
        ];
    }
    
    public function setData(array $data): void
    {
        // Créer une nouvelle entrée d'historique
        $validationData = [
            'leadId' => $this->legacyLead->leadId,
            'validationId' => $data['validationId'] ?? '',
            'validationResult' => $data['result'] ?? '',
            'scoringAction' => $data['scoring'] ?? '',
            'statutAction' => $data['status'] ?? '',
            'phoneVal' => $data['validations']['phone'] ?? '',
            'phone2Val' => $data['validations']['phone2'] ?? '',
            'emailVal' => $data['validations']['email'] ?? '',
            'cityVal' => $data['validations']['city'] ?? ''
        ];
        
        $this->validationHistory = new ValidationHistory();
        $this->validationHistory->addHistoryEntry($validationData);
        
        // Recharger l'historique
        $this->loadHistory();
    }
    
    public function save(): bool
    {
        return true; // La sauvegarde est déjà gérée dans setData()
    }
    
    /**
     * Récupère la dernière entrée de l'historique
     */
    public function getLatestEntry(): ?array
    {
        if (empty($this->history)) {
            return null;
        }
        
        $latest = reset($this->history); // Premier élément car trié par timestamp DESC
        return [
            'id' => $latest->validationHistoryId,
            'timestamp' => $latest->timestamp,
            'validationId' => $latest->validationId,
            'result' => $latest->validationResult,
            'scoring' => $latest->scoringAction,
            'status' => $latest->statutAction,
            'validations' => [
                'phone' => $latest->phoneVal,
                'phone2' => $latest->phone2Val,
                'email' => $latest->emailVal,
                'city' => $latest->cityVal
            ]
        ];
    }
    
    /**
     * Vérifie si une validation spécifique existe dans l'historique
     */
    public function hasValidation(string $validationId): bool
    {
        foreach ($this->history as $entry) {
            if ($entry->validationId === $validationId) {
                return true;
            }
        }
        return false;
    }
}
