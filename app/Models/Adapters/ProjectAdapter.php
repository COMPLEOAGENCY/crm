<?php

namespace App\Models\Adapters;

use Models\Lead as LegacyLead;

class ProjectAdapter implements LeadComponentInterface
{
    private LegacyLead $legacyLead;
    
    public function __construct(LegacyLead $legacyLead)
    {
        $this->legacyLead = $legacyLead;
    }
    
    public function getData(): array
    {
        return [
            'address' => [
                'address1' => $this->legacyLead->address1,
                'address2' => $this->legacyLead->address2,
                'postalCode' => $this->legacyLead->cp,
                'city' => $this->legacyLead->city,
                'country' => $this->legacyLead->country,
                'state' => $this->legacyLead->state,
            ],
            'geolocation' => [
                'subregion' => $this->legacyLead->_subregion,
                'region' => $this->legacyLead->_region,
                'countryName' => $this->legacyLead->_countryname,
            ],
            'meta' => $this->legacyLead->getMeta()
        ];
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
