<?php

namespace Models\Adapters;

use Models\Lead as LegacyLead;
use Models\Purchase;

class PurchaseAdapter implements LeadComponentInterface
{
    public static array $SCHEMA = [
        'exists' => ['field' => 'exists', 'type' => 'boolean'],
        'data' => [
            'type' => 'group',
            'fields' => [
                'purchaseId' => ['field' => 'purchaseid', 'type' => 'int'],
                'timestamp' => ['field' => 'timestamp', 'type' => 'datetime'],
                'userId' => ['field' => 'userid', 'type' => 'int'],
                'sourceId' => ['field' => 'sourceid', 'type' => 'int'],
                'campaignId' => ['field' => 'campaignid', 'type' => 'int'],
                'targetId' => ['field' => 'targetid', 'type' => 'int'],
                'price' => ['field' => 'price', 'type' => 'float'],
                'status' => ['field' => 'statut', 'type' => 'string'],
                'data' => ['field' => 'data', 'type' => 'json']
            ]
        ]
    ];

    private LegacyLead $legacyLead;
    private ?\stdClass $purchase = null;
    
    public function __construct(LegacyLead $legacyLead)
    {
        $this->legacyLead = $legacyLead;
        $this->loadPurchase();
    }
    
    private function loadPurchase(): void
    {
        if ($this->legacyLead->leadId) {
            $purchase = new Purchase();
            $this->purchase = $purchase->getByLeadId($this->legacyLead->leadId);
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

        $data = [];
        foreach (self::$SCHEMA as $key => $config) {
            if ($config['type'] === 'group') {
                $data[$key] = [];
                foreach ($config['fields'] as $fieldKey => $fieldConfig) {
                    $fieldName = $fieldConfig['field'];
                    $data[$key][$fieldKey] = $this->purchase->$fieldName ?? null;
                }
            } else {
                $data[$key] = $key === 'exists' ? true : null;
            }
        }
        
        return $data;
    }
    
    public function setData(array $data): void
    {
        // Non implémenté car en lecture seule
    }
    
    public function save(): bool
    {
        // Non implémenté car en lecture seule
        return true;
    }
    

}
