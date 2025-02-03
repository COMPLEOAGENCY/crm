<?php

namespace Models\Adapters;

use Models\Purchase;
use Models\Lead as LegacyLead;

class PurchaseAdapter implements LeadComponentInterface
{
    private ?Purchase $purchase;
    private ?LegacyLead $lead;

    public function __construct(?LegacyLead $lead = null)
    {
        $this->lead = $lead;
        $this->purchase = new Purchase();
        
        if ($lead !== null && !empty($lead->leadId)) {
            $this->purchase = $this->purchase->get($lead->leadId) ?: new Purchase();
        }
    }

    public static function getSchema()
    {
        return Purchase::$SCHEMA;
    }

    public function getData()
    {
        if (!$this->purchase) {
            return null;
        }
        
        return $this->purchase;
    }

    public function setData(array $data): void
    {
        if (!$this->purchase) {
            $this->purchase = new Purchase();
        }

        if ($this->lead) {
            $data['leadId'] = $this->lead->leadId;
        }

        $this->purchase->hydrate($data);
    }

    public function save(): bool
    {
        if (!$this->purchase) {
            return false;
        }

        return (bool) $this->purchase->save();
    }
}
