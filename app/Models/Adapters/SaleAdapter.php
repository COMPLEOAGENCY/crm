<?php

namespace Models\Adapters;

use Models\Lead as LegacyLead;
use Models\Sale;

class SaleAdapter implements LeadComponentInterface
{
    public static array $SCHEMA = [
        'exists' => ['field' => 'exists', 'type' => 'boolean'],
        'data' => [
            'type' => 'collection',
            'fields' => [
                'saleId' => ['field' => 'saleid', 'type' => 'int'],
                'timestamp' => ['field' => 'timestamp', 'type' => 'datetime'],
                'updateTimestamp' => ['field' => 'update_timestamp', 'type' => 'datetime'],
                'userId' => ['field' => 'userid', 'type' => 'int'],
                'userSubId' => ['field' => 'user_subid', 'type' => 'string'],
                'sourceId' => ['field' => 'sourceid', 'type' => 'int'],
                'campaignId' => ['field' => 'campaignid', 'type' => 'int'],
                'userCampaignId' => ['field' => 'usercampaignid', 'type' => 'string'],
                'price' => ['field' => 'price', 'type' => 'float'],
                'tva' => ['field' => 'tva', 'type' => 'float'],
                'saleWeight' => ['field' => 'sale_weight', 'type' => 'float'],
                'status' => ['field' => 'statut', 'type' => 'string'],
                'data' => ['field' => 'data', 'type' => 'json']
            ]
        ]
    ];

    private LegacyLead $legacyLead;
    /** @var \stdClass[] */
    private array $sales = [];
    
    public function __construct(LegacyLead $legacyLead)
    {
        $this->legacyLead = $legacyLead;
        $this->loadSales();
    }
    
    private function loadSales(): void
    {
        if ($this->legacyLead->leadId) {
            $sale = new Sale();
            $sales = $sale->getByLeadId($this->legacyLead->leadId);
            $this->sales = is_array($sales) ? $sales : [];
        }
    }
    
    public function getData(): array
    {
        if (empty($this->sales)) {
            return [
                'exists' => false,
                'data' => []
            ];
        }

        $salesData = [];
        foreach ($this->sales as $sale) {
            $saleData = [];
            foreach (self::$SCHEMA['data']['fields'] as $fieldKey => $fieldConfig) {
                $fieldName = $fieldConfig['field'];
                if ($fieldConfig['type'] === 'json') {
                    $saleData[$fieldKey] = $sale->$fieldName ?? null;
                } else {
                    $saleData[$fieldKey] = $sale->$fieldName ?? null;
                }
            }
            $salesData[] = $saleData;
        }

        return [
            'exists' => true,
            'data' => $salesData
        ];
    }
    
    public function setData(array $data): void
    {
        if (empty($this->sales)) {
            $this->sales[] = new \stdClass();
            $this->sales[0]->leadid = $this->legacyLead->leadId;
            $this->sales[0]->timestamp = time();
        }
        
        $this->sales[0]->update_timestamp = time();
        
        if (isset($data['userId'])) $this->sales[0]->userid = $data['userId'];
        if (isset($data['userSubId'])) $this->sales[0]->user_subid = $data['userSubId'];
        if (isset($data['sourceId'])) $this->sales[0]->sourceid = $data['sourceId'];
        if (isset($data['campaignId'])) $this->sales[0]->campaignid = $data['campaignId'];
        if (isset($data['userCampaignId'])) $this->sales[0]->usercampaignid = $data['userCampaignId'];
        if (isset($data['price'])) $this->sales[0]->price = $data['price'];
        if (isset($data['tva'])) $this->sales[0]->tva = $data['tva'];
        if (isset($data['saleWeight'])) $this->sales[0]->sale_weight = $data['saleWeight'];
        
        // Client KPIs
        if (isset($data['clientKpis'])) {
            if (isset($data['clientKpis']['kpi1'])) $this->sales[0]->client_kpi_1 = $data['clientKpis']['kpi1'];
            if (isset($data['clientKpis']['kpi2'])) $this->sales[0]->client_kpi_2 = $data['clientKpis']['kpi2'];
            if (isset($data['clientKpis']['kpi3'])) $this->sales[0]->client_kpi_3 = $data['clientKpis']['kpi3'];
        }
        
        // Refund
        if (isset($data['refund'])) {
            if (isset($data['refund']['askReason'])) {
                $this->sales[0]->refund_ask_timestamp = time();
                $this->sales[0]->refund_ask_reason = $data['refund']['askReason'];
            }
            if (isset($data['refund']['statut'])) {
                $this->sales[0]->refund_statut_timestamp = time();
                $this->sales[0]->refund_statut = $data['refund']['statut'];
            }
        }
        
        if (isset($data['scoring'])) $this->sales[0]->sale_scoring = $data['scoring'];
        if (isset($data['smsLogId'])) $this->sales[0]->sms_logId = $data['smsLogId'];
        if (isset($data['shopId'])) $this->sales[0]->shopId = $data['shopId'];
    }
    
    public function save(): bool
    {
        if (empty($this->sales)) {
            return false;
        }
        // TODO: Implement save method for stdClass
        return true;
    }
    
    /**
     * Demande un remboursement
     */
    public function requestRefund(string $reason): bool
    {
        if (empty($this->sales)) {
            return false;
        }
        
        $this->sales[0]->refund_ask_timestamp = time();
        $this->sales[0]->refund_ask_reason = $reason;
        $this->sales[0]->refund_statut = 'pending';
        
        // TODO: Implement save method for stdClass
        return true;
    }
    
    /**
     * Valide un remboursement
     */
    public function approveRefund(): bool
    {
        if (empty($this->sales) || $this->sales[0]->refund_statut !== 'pending') {
            return false;
        }
        
        $this->sales[0]->refund_statut = 'valid';
        $this->sales[0]->refund_statut_timestamp = time();
        
        // TODO: Implement save method for stdClass
        return true;
    }
    
    /**
     * Rejette un remboursement
     */
    public function rejectRefund(): bool
    {
        if (empty($this->sales) || $this->sales[0]->refund_statut !== 'pending') {
            return false;
        }
        
        $this->sales[0]->refund_statut = 'reject';
        $this->sales[0]->refund_statut_timestamp = time();
        
        // TODO: Implement save method for stdClass
        return true;
    }
}
