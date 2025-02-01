<?php

namespace Models\Adapters;

use Models\Lead as LegacyLead;
use Models\ValidationHistory;

class ValidationHistoryAdapter implements LeadComponentInterface
{
    public static array $SCHEMA = [
        'exists' => ['field' => 'exists', 'type' => 'boolean'],
        'data' => [
            'type' => 'collection',
            'fields' => [
                'historyId' => ['field' => 'historyId', 'type' => 'int'],
                'leadId' => ['field' => 'leadId', 'type' => 'int'],
                'timestamp' => ['field' => 'timestamp', 'type' => 'datetime'],
                'userId' => ['field' => 'userId', 'type' => 'int'],
                'action' => ['field' => 'action', 'type' => 'string'],
                'field' => ['field' => 'field', 'type' => 'string'],
                'oldValue' => ['field' => 'oldValue', 'type' => 'string'],
                'newValue' => ['field' => 'newValue', 'type' => 'string']
            ]
        ]
    ];

    private LegacyLead $legacyLead;
    private ?array $history = null;
    
    public function __construct(LegacyLead $legacyLead)
    {
        $this->legacyLead = $legacyLead;
        $this->loadHistory();
    }
    
    private function loadHistory(): void
    {
        if ($this->legacyLead->leadId) {
            $validationHistory = new ValidationHistory();
            $this->history = $validationHistory->getByLeadId($this->legacyLead->leadId);
        }
    }
    
    public function getData(): array
    {
        if (!$this->history) {
            return [
                'exists' => false,
                'data' => []
            ];
        }

        $historyData = [];
        foreach ($this->history as $item) {
            $itemData = [];
            foreach (self::$SCHEMA['data']['fields'] as $fieldKey => $fieldConfig) {
                $fieldName = $fieldConfig['field'];
                $itemData[$fieldKey] = $item->$fieldName ?? null;
            }
            $historyData[] = $itemData;
        }

        return [
            'exists' => true,
            'data' => $historyData
        ];
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
