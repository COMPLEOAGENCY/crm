<?php

namespace Models\Adapters;

use Models\Project;
use Models\Lead as LegacyLead;
use Models\Question;
use Models\Model;

class ProjectAdapter implements LeadComponentInterface
{
    private ?Project $project;
    private ?LegacyLead $lead;

    public static function getSchema()
    {
        return Project::$SCHEMA;
    }

    public function __construct(?LegacyLead $lead = null)
    {
        $this->lead = $lead;
        $this->project = new Project();

        if (!empty($lead->leadId) && is_int($lead->leadId)) {
            // Initialiser le projet avec le leadId et campaignId pour charger les questions            
            $this->project->get($lead->leadId);
        }
    }

    public function getData()
    {
        if (!$this->project) {
            return null;
        }

        return $this->project;
    }

    public function jsonSerialize(): mixed
    {
        return $this->getData();
    }

    // Lecture seule pour la relation one-to-many
    public function setData(array $data): void
    {
        // Ne pas implémenter pour la lecture seule
    }

    public function save(): bool
    {
        // Ne pas implémenter pour la lecture seule
        return false;
    }
}
