<?php

namespace Models\Adapters;

use Models\Project;
use Models\Lead as LegacyLead;

class ProjectAdapter implements LeadComponentInterface
{
    private ?Project $project;
    private ?LegacyLead $lead;
    private array $relatedProjects = [];

    public static function getSchema()
    {
        return Project::$SCHEMA;
    }

    public function __construct(?LegacyLead $lead = null)
    {
        $this->lead = $lead;
        $this->project = new Project();
        
        if ($lead !== null && !empty($lead->leadId)) {
            // Récupérer le projet principal par leadId
            $this->project = $this->project->get($lead->leadId) ?: new Project();
            
            // Récupérer les projets liés par email
            if ($lead->email) {
                $this->relatedProjects = $this->project->getList(
                    null, 
                    ['email' => $lead->email],
                    null,
                    null,
                    'timestamp',
                    'desc'
                );
            }
        }
    }

    public function getData()
    {
        if (!$this->project) {
            return null;
        }
        
        $data = $this->project;
        $data->relatedProjects = $this->relatedProjects;
        
        return $data;
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
