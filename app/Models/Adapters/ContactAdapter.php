<?php

namespace Models\Adapters;

use Models\Contact;
use Models\Lead as LegacyLead;

class ContactAdapter implements LeadComponentInterface
{
    private ?Contact $contact;
    private ?LegacyLead $lead;

    public function __construct(?LegacyLead $lead = null)
    {
        $this->lead = $lead;
        $this->contact = new Contact();
        
        if ($lead !== null && !empty($lead->leadId)) {
            $this->contact = $this->contact->get($lead->leadId) ?: new Contact();
        }
    }

    public static function getSchema()
    {
        return Contact::$SCHEMA;
    }

    public function getData()
    {
        if (!$this->contact) {
            return null;
        }
        
        return $this->contact;
    }

    public function setData(array $data): void
    {
        if (!$this->contact) {
            $this->contact = new Contact();
        }

        if ($this->lead) {
            $data['leadId'] = $this->lead->leadId;
        }

        $this->contact->hydrate($data);
    }

    public function save(): bool
    {
        if (!$this->contact) {
            return false;
        }

        return (bool) $this->contact->save();
    }
}
