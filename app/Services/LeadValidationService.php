<?php
namespace Services;

use Models\Validation;
use Models\Lead;

class LeadValidationService
{
    /**
     * Lance la validation des leads selon les paramètres fournis.
     * Cette première version se concentre sur l'orchestration et le retour de métriques basiques.
     *
     * Paramètres supportés (query/body):
     * - validationid (int) : exécuter pour une configuration précise
     * - statut (string) : filtre des configurations (ex: 'on')
     * - limit (int) : limite de configurations à charger (défaut 100)
     */
    public function run(array $params = []): array
    {
        $startedAt = microtime(true);

        // Paramètres en minuscules uniquement (URLs/QueryString)
        $validationId = isset($params['validationid']) ? (int)$params['validationid'] : null;
        $statut       = isset($params['statut']) ? (string)$params['statut'] : 'on';
        $limit        = isset($params['limit']) ? max(1, (int)$params['limit']) : 100;

        // Récupérer les configurations de validation
        $validationModel = new Validation();
        $filters = [];
        if ($validationId) {
            // Le schéma mappe le champ primaire en base: validationid
            $filters['validationid'] = $validationId;
        } elseif ($statut !== null && $statut !== '') {
            $filters['statut'] = $statut;
        }

        $validations = $validationModel->getList(
            $limit,
            $filters,
            null,
            null,
            'validationid',
            'asc'
        );

        // Placeholder d'exécution: ici on ne fait qu'énumérer; la logique métier sera branchée ultérieurement
        $executions = [];
        $errors = [];

        foreach ($validations as $conf) {
            $executions[] = [
                'validationid' => $conf->validationId ?? null,
                'name' => $conf->name ?? '',
                'type' => $conf->type ?? 'none',
                'status' => $conf->statut ?? '',
                'executed' => false,
                'notes' => 'Exécution non implémentée (à venir)'
            ];
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        return [
            'success' => true,
            'message' => 'Validation lancée',
            'input' => [
                'validationid' => $validationId,
                'statut' => $statut,
                'limit' => $limit,
            ],
            'metrics' => [
                'validations_count' => count($validations),
                'executions_count' => count($executions),
                'errors' => count($errors),
                'duration_ms' => $durationMs,
            ],
            'executions' => $executions,
            'errors_list' => $errors,
        ];
    }

    /**
     * Charge les leads en attente (statut 'pending') des N derniers jours (défaut 30),
     * ou un lead précis par identifiant si `id` est fourni (>0).
     *
     * Paramètres supportés (query/body):
     * - id (int) : identifiant du lead ciblé (prioritaire sur la fenêtre de temps)
     * - limit (int) : nombre max de leads à retourner (défaut 100)
     * - days (int) : fenêtre temporelle en jours pour la recherche (défaut 30)
     */
    public function loadPendingLeads(array $params = []): array
    {
        $startedAt = microtime(true);
        $id    = isset($params['id']) ? (int)$params['id'] : null;
        $limit = isset($params['limit']) ? max(1, (int)$params['limit']) : 100;
        $days  = isset($params['days']) ? max(1, (int)$params['days']) : 30;
        $sinceTimestamp = time() - ($days * 24 * 60 * 60);

        $leadModel = new Lead();

        if (!empty($id) && $id > 0) {
            // Reproduit le legacy: par id + statut pending (sans contrainte de date)
            $sqlParameters = [
                ['leadid', '=', $id],
                ['statut', '=', 'pending'],
            ];
        } else {
            // Fenêtre glissante: timestamp > 0 ET > since, et statut pending
            $sqlParameters = [
                ['timestamp', '>', 0],
                ['timestamp', '>', $sinceTimestamp],
                ['statut', '=', 'pending'],
            ];
        }

        $leads = $leadModel->getList(
            $limit,
            $sqlParameters,
            null,
            null,
            'leadid',
            'asc'
        );

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        return [
            'success' => true,
            'message' => 'Leads pending chargés',
            'input' => [
                'id' => $id,
                'limit' => $limit,
                'days' => $days,
            ],
            'metrics' => [
                'leads_count' => count($leads),
                'duration_ms' => $durationMs,
            ],
            'leads' => $leads,
        ];
    }
}
