<?php

namespace App\Models\Adapters;

use Models\Lead as LegacyLead;
use Models\Purchase;

class PurchaseAdapter implements LeadComponentInterface
{
    private LegacyLead $legacyLead;
    private ?Purchase $purchase = null;
    
    public function __construct(LegacyLead $legacyLead)
    {
        $this->legacyLead = $legacyLead;
        $this->loadPurchase();
    }
    
    private function loadPurchase(): void
    {
        if ($this->legacyLead->leadId) {
            $purchase = new Purchase();
            $result = $purchase->get($this->legacyLead->leadId);
            if ($result) {
                $this->purchase = $result;
            }
        }
    }
    
    public function getData(): array
    {
        if (!$this->purchase) {
            return [
                'exists' => false,
                'data' => []
            ];
        }

        return [
            'exists' => true,
            'data' => [
                'purchaseId' => $this->purchase->purchaseId,
                'timestamp' => $this->purchase->timestamp,
                'userId' => $this->purchase->userId,
                'sourceId' => $this->purchase->sourceId,
                'campaignId' => $this->purchase->campaignId,
                'targetId' => $this->purchase->targetId,
                'price' => $this->purchase->price,
                'status' => $this->purchase->statut,
                'data' => $this->purchase->data
            ]
        ];
    }
    
    public function setData(array $data): void
    {
        if (!$this->purchase) {
            $this->purchase = new Purchase();
            $this->purchase->leadId = $this->legacyLead->leadId;
        }
        
        if (isset($data['userId'])) $this->purchase->userId = $data['userId'];
        if (isset($data['sourceId'])) $this->purchase->sourceId = $data['sourceId'];
        if (isset($data['campaignId'])) $this->purchase->campaignId = $data['campaignId'];
        if (isset($data['targetId'])) $this->purchase->targetId = $data['targetId'];
        if (isset($data['price'])) $this->purchase->price = $data['price'];
        if (isset($data['status'])) $this->purchase->statut = $data['status'];
        if (isset($data['data'])) $this->purchase->data = $data['data'];
        
        // Mettre à jour le timestamp si c'est un nouveau purchase
        if (!$this->purchase->purchaseId) {
            $this->purchase->timestamp = time();
        }
    }
    
    public function save(): bool
    {
        if (!$this->purchase) {
            return false;
        }
        return $this->purchase->save();
    }
    
    /**
     * Valide le purchase associé
     */
    public function validate(): bool
    {
        if (!$this->purchase) {
            return false;
        }
        return $this->purchase->validate();
    }
    
    /**
     * Rejette le purchase associé
     */
    public function reject(): bool
    {
        if (!$this->purchase) {
            return false;
        }
        return $this->purchase->reject();
    }
}
