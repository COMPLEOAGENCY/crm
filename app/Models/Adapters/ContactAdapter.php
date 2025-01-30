<?php

namespace App\Models\Adapters;

use Models\Lead as LegacyLead;

class ContactAdapter implements LeadComponentInterface
{
    private LegacyLead $legacyLead;
    
    public function __construct(LegacyLead $legacyLead)
    {
        $this->legacyLead = $legacyLead;
    }
    
    public function getData(): array
    {
        return [
            'civility' => $this->legacyLead->civ,
            'firstName' => $this->legacyLead->first_name,
            'lastName' => $this->legacyLead->last_name,
            'email' => $this->legacyLead->email,
            'phone' => $this->legacyLead->phone,
            'phone2' => $this->legacyLead->phone2,
            'validations' => [
                'email' => $this->legacyLead->email_val,
                'phone' => $this->legacyLead->phone_val,
                'phone2' => $this->legacyLead->phone2_val,
            ],
            'marketing' => [
                'utm_source' => $this->legacyLead->utm_source,
                'utm_medium' => $this->legacyLead->utm_medium,
                'utm_campaign' => $this->legacyLead->utm_campaign,
                'utm_content' => $this->legacyLead->utm_content,
                'utm_term' => $this->legacyLead->utm_term,
                'url' => $this->legacyLead->url,
                'referer' => $this->legacyLead->referer,
            ]
        ];
    }
    
    public function setData(array $data): void
    {
        // Informations personnelles
        if (isset($data['civility'])) $this->legacyLead->civ = $data['civility'];
        if (isset($data['firstName'])) $this->legacyLead->first_name = $data['firstName'];
        if (isset($data['lastName'])) $this->legacyLead->last_name = $data['lastName'];
        if (isset($data['email'])) $this->legacyLead->email = $data['email'];
        if (isset($data['phone'])) $this->legacyLead->phone = $data['phone'];
        if (isset($data['phone2'])) $this->legacyLead->phone2 = $data['phone2'];
        
        // Validations
        if (isset($data['validations'])) {
            if (isset($data['validations']['email'])) $this->legacyLead->email_val = $data['validations']['email'];
            if (isset($data['validations']['phone'])) $this->legacyLead->phone_val = $data['validations']['phone'];
            if (isset($data['validations']['phone2'])) $this->legacyLead->phone2_val = $data['validations']['phone2'];
        }
        
        // Marketing
        if (isset($data['marketing'])) {
            if (isset($data['marketing']['utm_source'])) $this->legacyLead->utm_source = $data['marketing']['utm_source'];
            if (isset($data['marketing']['utm_medium'])) $this->legacyLead->utm_medium = $data['marketing']['utm_medium'];
            if (isset($data['marketing']['utm_campaign'])) $this->legacyLead->utm_campaign = $data['marketing']['utm_campaign'];
            if (isset($data['marketing']['utm_content'])) $this->legacyLead->utm_content = $data['marketing']['utm_content'];
            if (isset($data['marketing']['utm_term'])) $this->legacyLead->utm_term = $data['marketing']['utm_term'];
            if (isset($data['marketing']['url'])) $this->legacyLead->url = $data['marketing']['url'];
            if (isset($data['marketing']['referer'])) $this->legacyLead->referer = $data['marketing']['referer'];
        }
    }
    
    public function save(): bool
    {
        return $this->legacyLead->save();
    }
}
