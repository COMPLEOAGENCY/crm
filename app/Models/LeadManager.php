<?php

namespace App\Models;

use App\Models\Adapters\ContactAdapter;
use App\Models\Adapters\ProjectAdapter;
use App\Models\Adapters\PurchaseAdapter;
use App\Models\Adapters\SaleAdapter;
use App\Models\Adapters\ValidationHistoryAdapter;
use Models\Lead as LegacyLead;

class LeadManager
{
    private LegacyLead $legacyLead;
    private ContactAdapter $contact;
    private ProjectAdapter $project;
    private PurchaseAdapter $purchase;
    private SaleAdapter $sale;
    private ValidationHistoryAdapter $validationHistory;
    
    public function __construct(?int $legacyLeadId = null)
    {
        if ($legacyLeadId) {
            $this->legacyLead = new LegacyLead();
            $loadedLead = $this->legacyLead->get($legacyLeadId);
            if (!$loadedLead) {
                throw new \RuntimeException("Lead not found with ID: {$legacyLeadId}");
            }
            $this->legacyLead = $loadedLead;
        } else {
            $this->legacyLead = new LegacyLead();
        }
        
        $this->initializeComponents();
    }
    
    private function initializeComponents(): void
    {
        $this->contact = new ContactAdapter($this->legacyLead);
        $this->project = new ProjectAdapter($this->legacyLead);
        $this->purchase = new PurchaseAdapter($this->legacyLead);
        $this->sale = new SaleAdapter($this->legacyLead);
        $this->validationHistory = new ValidationHistoryAdapter($this->legacyLead);
    }
    
    public function getContact(): ContactAdapter
    {
        return $this->contact;
    }
    
    public function getProject(): ProjectAdapter
    {
        return $this->project;
    }
    
    public function getPurchase(): PurchaseAdapter
    {
        return $this->purchase;
    }
    
    public function getSale(): SaleAdapter
    {
        return $this->sale;
    }
    
    public function getValidationHistory(): ValidationHistoryAdapter
    {
        return $this->validationHistory;
    }
    
    public function getLegacyLead(): LegacyLead
    {
        return $this->legacyLead;
    }
    
    public function save(): bool
    {
        return $this->legacyLead->save();
    }
}
