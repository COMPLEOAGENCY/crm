<?php

namespace Models\Adapters;

use Models\Lead as LegacyLead;

class ContactAdapter implements LeadComponentInterface
{
    public static array $SCHEMA = [
        'leadId' => ['field' => 'leadId', 'type' => 'int'],
        'civility' => ['field' => 'civ', 'type' => 'string'],
        'firstName' => ['field' => 'first_name', 'type' => 'string'],
        'lastName' => ['field' => 'last_name', 'type' => 'string'],
        'email' => ['field' => 'email', 'type' => 'string'],
        'phone' => ['field' => 'phone', 'type' => 'string'],
        'phone2' => ['field' => 'phone2', 'type' => 'string'],
        'validations' => [
            'type' => 'group',
            'fields' => [
                'email' => ['field' => 'email_val', 'type' => 'boolean'],
                'phone' => ['field' => 'phone_val', 'type' => 'boolean'],
                'phone2' => ['field' => 'phone2_val', 'type' => 'boolean']
            ]
        ],
        'marketing' => [
            'type' => 'group',
            'fields' => [
                'utm_source' => ['field' => 'utm_source', 'type' => 'string'],
                'utm_medium' => ['field' => 'utm_medium', 'type' => 'string'],
                'utm_campaign' => ['field' => 'utm_campaign', 'type' => 'string'],
                'utm_content' => ['field' => 'utm_content', 'type' => 'string'],
                'utm_term' => ['field' => 'utm_term', 'type' => 'string'],
                'url' => ['field' => 'url', 'type' => 'string'],
                'referer' => ['field' => 'referer', 'type' => 'string']
            ]
        ]
    ];

    private LegacyLead $legacyLead;
    
    public function __construct(LegacyLead $legacyLead)
    {
        $this->legacyLead = $legacyLead;
    }
    
    public function getData(): array
    {
        $data = [];
        
        foreach (self::$SCHEMA as $key => $config) {
            if ($config['type'] === 'group') {
                $data[$key] = [];
                foreach ($config['fields'] as $fieldKey => $fieldConfig) {
                    $fieldName = $fieldConfig['field'];
                    $data[$key][$fieldKey] = $this->legacyLead->$fieldName;
                }
            } else {
                $fieldName = $config['field'];
                $data[$key] = $this->legacyLead->$fieldName;
            }
        }
        
        return $data;
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
