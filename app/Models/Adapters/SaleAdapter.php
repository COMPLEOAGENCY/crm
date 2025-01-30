<?php

namespace App\Models\Adapters;

use Models\Lead as LegacyLead;
use Models\Sale;

class SaleAdapter implements LeadComponentInterface
{
    private LegacyLead $legacyLead;
    private ?Sale $sale = null;
    
    public function __construct(LegacyLead $legacyLead)
    {
        $this->legacyLead = $legacyLead;
        $this->loadSale();
    }
    
    private function loadSale(): void
    {
        if ($this->legacyLead->leadId) {
            $sale = new Sale();
            $result = $sale->get($this->legacyLead->leadId);
            if ($result) {
                $this->sale = $result;
            }
        }
    }
    
    public function getData(): array
    {
        if (!$this->sale) {
            return [
                'exists' => false,
                'data' => []
            ];
        }

        return [
            'exists' => true,
            'data' => [
                'saleId' => $this->sale->saleId,
                'timestamp' => $this->sale->timestamp,
                'updateTimestamp' => $this->sale->update_timestamp,
                'userId' => $this->sale->userId,
                'userSubId' => $this->sale->user_subid,
                'sourceId' => $this->sale->sourceId,
                'campaignId' => $this->sale->campaignId,
                'userCampaignId' => $this->sale->userCampaignId,
                'price' => $this->sale->price,
                'tva' => $this->sale->tva,
                'saleWeight' => $this->sale->sale_weight,
                'clientKpis' => [
                    'kpi1' => $this->sale->client_kpi_1,
                    'kpi2' => $this->sale->client_kpi_2,
                    'kpi3' => $this->sale->client_kpi_3
                ],
                'refund' => [
                    'askTimestamp' => $this->sale->refund_ask_timestamp,
                    'askReason' => $this->sale->refund_ask_reason,
                    'statutTimestamp' => $this->sale->refund_statut_timestamp,
                    'statut' => $this->sale->refund_statut
                ],
                'scoring' => $this->sale->sale_scoring,
                'smsLogId' => $this->sale->sms_logId,
                'shopId' => $this->sale->shopId
            ]
        ];
    }
    
    public function setData(array $data): void
    {
        if (!$this->sale) {
            $this->sale = new Sale();
            $this->sale->leadId = $this->legacyLead->leadId;
            $this->sale->timestamp = time();
        }
        
        $this->sale->update_timestamp = time();
        
        if (isset($data['userId'])) $this->sale->userId = $data['userId'];
        if (isset($data['userSubId'])) $this->sale->user_subid = $data['userSubId'];
        if (isset($data['sourceId'])) $this->sale->sourceId = $data['sourceId'];
        if (isset($data['campaignId'])) $this->sale->campaignId = $data['campaignId'];
        if (isset($data['userCampaignId'])) $this->sale->userCampaignId = $data['userCampaignId'];
        if (isset($data['price'])) $this->sale->price = $data['price'];
        if (isset($data['tva'])) $this->sale->tva = $data['tva'];
        if (isset($data['saleWeight'])) $this->sale->sale_weight = $data['saleWeight'];
        
        // Client KPIs
        if (isset($data['clientKpis'])) {
            if (isset($data['clientKpis']['kpi1'])) $this->sale->client_kpi_1 = $data['clientKpis']['kpi1'];
            if (isset($data['clientKpis']['kpi2'])) $this->sale->client_kpi_2 = $data['clientKpis']['kpi2'];
            if (isset($data['clientKpis']['kpi3'])) $this->sale->client_kpi_3 = $data['clientKpis']['kpi3'];
        }
        
        // Refund
        if (isset($data['refund'])) {
            if (isset($data['refund']['askReason'])) {
                $this->sale->refund_ask_timestamp = time();
                $this->sale->refund_ask_reason = $data['refund']['askReason'];
            }
            if (isset($data['refund']['statut'])) {
                $this->sale->refund_statut_timestamp = time();
                $this->sale->refund_statut = $data['refund']['statut'];
            }
        }
        
        if (isset($data['scoring'])) $this->sale->sale_scoring = $data['scoring'];
        if (isset($data['smsLogId'])) $this->sale->sms_logId = $data['smsLogId'];
        if (isset($data['shopId'])) $this->sale->shopId = $data['shopId'];
    }
    
    public function save(): bool
    {
        if (!$this->sale) {
            return false;
        }
        return $this->sale->save();
    }
    
    /**
     * Demande un remboursement
     */
    public function requestRefund(string $reason): bool
    {
        if (!$this->sale) {
            return false;
        }
        
        $this->sale->refund_ask_timestamp = time();
        $this->sale->refund_ask_reason = $reason;
        $this->sale->refund_statut = 'pending';
        
        return $this->sale->save();
    }
    
    /**
     * Valide un remboursement
     */
    public function approveRefund(): bool
    {
        if (!$this->sale || $this->sale->refund_statut !== 'pending') {
            return false;
        }
        
        $this->sale->refund_statut = 'valid';
        $this->sale->refund_statut_timestamp = time();
        
        return $this->sale->save();
    }
    
    /**
     * Rejette un remboursement
     */
    public function rejectRefund(): bool
    {
        if (!$this->sale || $this->sale->refund_statut !== 'pending') {
            return false;
        }
        
        $this->sale->refund_statut = 'reject';
        $this->sale->refund_statut_timestamp = time();
        
        return $this->sale->save();
    }
}
