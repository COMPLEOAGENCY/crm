<?php

namespace Models\Adapters;

use Models\Lead as LegacyLead;

class ProjectAdapter implements LeadComponentInterface
{
    public static array $SCHEMA = [
        'address' => [
            'type' => 'group',
            'fields' => [
                'address1' => ['field' => 'address1', 'type' => 'string'],
                'address2' => ['field' => 'address2', 'type' => 'string'],
                'postalCode' => ['field' => 'cp', 'type' => 'string'],
                'city' => ['field' => 'city', 'type' => 'string'],
                'country' => ['field' => 'country', 'type' => 'string'],
                'state' => ['field' => 'state', 'type' => 'string']
            ]
        ],
        'geolocation' => [
            'type' => 'group',
            'fields' => [
                'subregion' => ['field' => '_subregion', 'type' => 'string'],
                'region' => ['field' => '_region', 'type' => 'string'],
                'countryName' => ['field' => '_countryname', 'type' => 'string']
            ]
        ],
        'meta' => ['field' => 'meta', 'type' => 'json']
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
            } elseif ($config['type'] === 'json') {
                $data[$key] = $this->legacyLead->getMeta();
            } else {
                $fieldName = $config['field'];
                $data[$key] = $this->legacyLead->$fieldName;
            }
        }
        
        return $data;
    }
    
    public function setData(array $data): void
    {
        // Adresse
        if (isset($data['address'])) {
            if (isset($data['address']['address1'])) $this->legacyLead->address1 = $data['address']['address1'];
            if (isset($data['address']['address2'])) $this->legacyLead->address2 = $data['address']['address2'];
            if (isset($data['address']['postalCode'])) $this->legacyLead->cp = $data['address']['postalCode'];
            if (isset($data['address']['city'])) $this->legacyLead->city = $data['address']['city'];
            if (isset($data['address']['country'])) $this->legacyLead->country = $data['address']['country'];
            if (isset($data['address']['state'])) $this->legacyLead->state = $data['address']['state'];
        }
        
        // Géolocalisation
        if (isset($data['geolocation'])) {
            if (isset($data['geolocation']['subregion'])) $this->legacyLead->_subregion = $data['geolocation']['subregion'];
            if (isset($data['geolocation']['region'])) $this->legacyLead->_region = $data['geolocation']['region'];
            if (isset($data['geolocation']['countryName'])) $this->legacyLead->_countryname = $data['geolocation']['countryName'];
        }
        
        // Meta
        if (isset($data['meta'])) {
            foreach ($data['meta'] as $key => $value) {
                $this->legacyLead->setMeta($key, $value);
            }
        }
    }
    
    public function save(): bool
    {
        return $this->legacyLead->save();
    }
}
