<?php

namespace Models\Adapters;
use Models\Lead as LegacyLead;
interface LeadComponentInterface
{
    // create object
    public function __construct(?LegacyLead $lead = null);

    // get data for view
    public function getData();

    // set data from view following models rules
    public function setData(array $data): void;

    // save data to database following models rules    
    public function save(): bool;   
}