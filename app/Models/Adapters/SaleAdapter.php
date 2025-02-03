<?php

namespace Models\Adapters;

use Models\Sale;
use Models\Lead as LegacyLead;

class SaleAdapter implements LeadComponentInterface
{
    private ?Sale $sale;
    private ?LegacyLead $lead;
    private array $sales = [];

    public static function getSchema()
    {
        return Sale::$SCHEMA;
    }

    public function __construct(?LegacyLead $lead = null)
    {
        $this->lead = $lead;
        
        if ($lead !== null && !empty($lead->leadId)) {
            // Récupérer toutes les ventes liées au leadId
            $this->sales = (new Sale())->getList(
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
        return $this->sales ?: [];
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
