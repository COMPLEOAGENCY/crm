<?php

namespace Models\Adapters;

use Models\ValidationHistory;
use Models\Lead as LegacyLead;

class ValidationHistoryAdapter implements LeadComponentInterface
{
    private ?LegacyLead $lead;
    private array $validationHistories = [];

    public static function getSchema()
    {
        return ValidationHistory::$SCHEMA;
    }

    public function __construct(?LegacyLead $lead = null)
    {
        $this->lead = $lead;
        
        if ($lead !== null && !empty($lead->leadId)) {
            // Récupérer tout l'historique de validation lié au leadId
            $this->validationHistories = (new ValidationHistory())->getList(
                null,
                ['leadid' => $lead->leadId],
                null,
                null,
                'timestamp',
                'desc'
            );
        }
    }

    public function getData()
    {
        return $this->validationHistories ?: [];
    }

    // Lecture seule pour la relation one-to-many
    public function setData(array $data): void
    {
        // Ne pas implémenter pour la lecture seule
    }

    public function save(): bool
    {
        // Ne pas implémenter pour la lecture seule
        return false;
    }
}
