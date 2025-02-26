<?php

namespace Models;

use Models\Adapters\ContactAdapter;
use Models\Adapters\ProjectAdapter;
use Models\Adapters\PurchaseAdapter;
use Models\Adapters\SaleAdapter;
use Models\Adapters\ValidationHistoryAdapter;
use Models\Lead as LegacyLead;
use Classes\Logger;

class LeadManager implements \JsonSerializable
{
    public static ?array $SCHEMA = null;
    
    public static function getSchema(): array
    {
        if (self::$SCHEMA === null) {
            self::$SCHEMA = [
                'leadId' => ['field' => 'leadId', 'type' => 'int'],
                'createdAt' => ['field' => 'createdAt', 'type' => 'datetime'],
                'updatedAt' => ['field' => 'updatedAt', 'type' => 'datetime'],
                'contact' => [
                    'type' => 'relation',
                    'model' => ContactAdapter::class,
                    'schema' => ContactAdapter::getSchema()
                ],
                'project' => [
                    'type' => 'relation',
                    'model' => ProjectAdapter::class,
                    'schema' => ProjectAdapter::getSchema()                    
                ],
                'purchase' => [
                    'type' => 'relation',
                    'model' => PurchaseAdapter::class,
                    'schema' => PurchaseAdapter::getSchema()
                ],
                'sales' => [
                    'type' => 'relation',
                    'model' => SaleAdapter::class,
                    'schema' => SaleAdapter::getSchema()
                ]
            ];
        }
        return self::$SCHEMA;
    }

    private LegacyLead $legacyLead;
    private ContactAdapter $contact;
    private ProjectAdapter $project;
    private PurchaseAdapter $purchase;
    private SaleAdapter $sales;    
    
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
        $this->sales = new SaleAdapter($this->legacyLead);        
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
    
    public function getSales(): SaleAdapter
    {
        return $this->sales;
    }
    

    
    public function getLegacyLead(): LegacyLead
    {
        return $this->legacyLead;
    }
    
    public function save(): bool
    {
        // Sauvegarde des composants
        $this->contact->save();
        $this->project->save();
        $this->purchase->save();
        $this->sales->save();


        // Invalidation du cache selon le pattern existant
        $result = $this->legacyLead->save();
        return $result;
    }
    
    public static function getList(int $limit = 1000, array $sqlParameters = [], array $jsonParameters = [], ?string $search = null, ?string $orderBy = null, string $direction = 'asc'): array
    {
        // Log des paramètres d'entrée
        Logger::debug("LeadManager::getList - Paramètres", [
            'sql' => $sqlParameters,
            'json' => $jsonParameters
        ]);
        
        $legacyLead = new LegacyLead();
        
        // Traitement des paramètres imbriqués
        $processedSqlParams = array_map(
            fn($param) => self::processFilterParameter($param), 
            $sqlParameters
        );
        
        // Log des paramètres traités
        Logger::debug("LeadManager::getList - Paramètres SQL traités", [
            'params' => $processedSqlParams
        ]);

        // Traitement du tri
        $processedOrderBy = self::processOrderBy($orderBy);
        
        // Récupération des leads
        $legacyLeads = $legacyLead->getList(
            $limit, 
            array_filter($processedSqlParams), 
            $jsonParameters, 
            $search, 
            $processedOrderBy, 
            $direction
        );
        
        // Log du nombre de résultats
        Logger::debug("LeadManager::getList - Résultats", [
            'count' => count($legacyLeads)
        ]);
        
        return array_map(
            fn($legacyLeadData) => new self($legacyLeadData->leadId), 
            $legacyLeads
        );
    }

    private static function processFilterParameter(array $param): ?array
    {
        Logger::debug("processFilterParameter - Paramètre", [
            'param' => $param
        ]);
        
        if (count($param) !== 3) {
            Logger::debug("processFilterParameter - Format invalide (!=3)");
            return $param;
        }

        [$field, $operator, $value] = $param;
        Logger::debug("processFilterParameter - Décomposition", [
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ]);

        // Vérifier si c'est un champ imbriqué (ex: contact[phone])
        if (!preg_match('/^(\w+)\[(\w+)\]$/', $field, $matches)) {
            Logger::debug("processFilterParameter - Champ non imbriqué");
            return $param;
        }

        // Extraction du champ réel depuis le schéma
        $fieldInfo = self::extractFieldFromSchema($matches[1], $matches[2]);
        Logger::debug("processFilterParameter - Field info", [
            'info' => $fieldInfo
        ]);
        
        if (!$fieldInfo) {
            Logger::debug("processFilterParameter - Champ non trouvé dans le schéma");
            return null;
        }

        $result = [$fieldInfo, $operator, $value];
        Logger::debug("processFilterParameter - Résultat", [
            'result' => $result
        ]);
        return $result;
    }

    private static function processOrderBy(?string $orderBy): ?string
    {
        if (!$orderBy) {
            return null;
        }

        // Vérifier si c'est un champ imbriqué
        if (preg_match('/^(\w+)\[(\w+)\]$/', $orderBy, $matches)) {
            return self::extractFieldFromSchema($matches[1], $matches[2]);
        }

        // Champ simple
        $schema = self::getSchema();
        return isset($schema[$orderBy]) ? $schema[$orderBy]['field'] : null;
    }

    private static function extractFieldFromSchema(string $relation, string $field): ?string
    {
        $schema = self::getSchema();
        
        if (!isset($schema[$relation]) || 
            $schema[$relation]['type'] !== 'relation' || 
            !isset($schema[$relation]['schema'][$field])) {
            return null;
        }

        return $schema[$relation]['schema'][$field]['field'];
    }
    
    /**
     * Récupère un lead par son ID
     * 
     * @param int $id ID du lead à récupérer
     * @return LeadManager Instance de LeadManager
     * @throws \RuntimeException Si le lead n'est pas trouvé
     */
    public static function get(int $id): LeadManager
    {
        return new self($id);
    }
    
    /**
     * Spécifie les données à sérialiser en JSON
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'contact' => $this->contact->getData(),
            'project' => $this->project->getData(),
            'purchase' => $this->purchase->getData(),
            'sales' => $this->sales->getData()
        ];
    }
    
    /**
     * Convertit l'instance en tableau pour l'API
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
