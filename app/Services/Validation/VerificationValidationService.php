<?php

namespace Services\Validation;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Service de validation des données de "vérification"
 *
 * Aligne la logique sur UserValidationService avec une liste de champs,
 * des méthodes get<Field>Constraints et des règles métier transverses.
 */
class VerificationValidationService
{
    /** @var ValidatorInterface */
    private $validator;

    /**
     * Champs gérés par le service (correspondent aux names du formulaire)
     *
     * @var array<string>
     */
    private static array $fields = [
        'name',
        'type',
        'campaignid',
        'sourceid',
        'type_phone',
        'start_lead_statut',
        'audio_file',
        'audio_file_valid',
        'audio_file_unvalid',
        'audio_file_error',
        'start_hour',
        'end_hour',
        'max_nb',
        'min_delay',
        'first_delay',
        'valid_scoring_action',
        'valid_scoring_statut',
        'unvalid_scoring_action',
        'unvalid_scoring_statut',
        'failed_scoring_action',
        'failed_scoring_statut',
        'reject_scoring_action',
        'reject_scoring_statut',
        'order',
        'statut',
        'api_url',
    ];

    public function __construct(ValidatorInterface $validator = null)
    {
        $this->validator = $validator ?? Validation::createValidator();
    }

    /**
     * Valide les données d'une vérification
     */
    public function validateVerification(array $data): ConstraintViolationListInterface
    {
        $constraints = new Assert\Collection([
            'fields' => $this->getFieldsConstraints(),
            'allowExtraFields' => true,
            'allowMissingFields' => true,
            'missingFieldsMessage' => 'Le champ {{ field }} est manquant.',
            'extraFieldsMessage' => 'Le champ {{ field }} n\'est pas attendu.',
        ]);

        $violations = new ConstraintViolationList();

        // Violations sur contraintes déclaratives
        $base = $this->validator->validate($data, $constraints);
        foreach ($base as $violation) {
            $violations->add($violation);
        }

        // Règles métier transverses (dépendances entre champs)
        $this->validateBusinessRules($data, $violations);

        return $violations;
    }

    /**
     * Indique si un champ est marqué requis (présence d'un NotBlank)
     */
    public function isFieldRequired(string $fieldName): bool
    {
        $constraints = $this->getConstraintsForField($fieldName);
        foreach ($constraints as $constraint) {
            if ($constraint instanceof Assert\NotBlank) {
                return true;
            }
        }
        return false;
    }

    /** @return array<string, Assert\Composite|array> */
    private function getFieldsConstraints(): array
    {
        $map = [];
        foreach (self::$fields as $field) {
            $m = 'get' . ucfirst($field) . 'Constraints';
            if (method_exists($this, $m)) {
                $map[$field] = $this->$m();
            }
        }
        return $map;
    }

    /** @return array<int, Assert\Constraint> */
    private function getConstraintsForField(string $fieldName): array
    {
        $m = 'get' . ucfirst($fieldName) . 'Constraints';
        if (method_exists($this, $m)) {
            $c = $this->$m();
            return is_array($c) ? $c : [];
        }
        return [];
    }

    /**
     * Règles métier transverses
     * - Si type === 'n8n' alors api_url obligatoire et valide
     * - Si start_hour et end_hour fournis, start_hour <= end_hour
     */
    private function validateBusinessRules(array $data, ConstraintViolationList $violations): void
    {
        $type = $data['type'] ?? null;
        if ($type === 'n8n') {
            $url = $data['api_url'] ?? null;
            if ($url === null || $url === '') {
                $violations->add(new ConstraintViolation(
                    'L\'URL du webhook est obligatoire pour le type n8n.',
                    null,
                    [],
                    $data,
                    'api_url',
                    $url
                ));
            }
        }

        $start = isset($data['start_hour']) ? (string)$data['start_hour'] : null;
        $end   = isset($data['end_hour']) ? (string)$data['end_hour'] : null;
        if ($start !== null && $start !== '' && $end !== null && $end !== '') {
            if (is_numeric($start) && is_numeric($end)) {
                if ((float)$start > (float)$end) {
                    $violations->add(new ConstraintViolation(
                        'L\'heure de début doit être inférieure ou égale à l\'heure de fin.',
                        null,
                        [],
                        $data,
                        'start_hour',
                        $start
                    ));
                }
            }
        }
    }

    // -----------------------------
    // Contraintes par champ
    // -----------------------------

    private function getNameConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le nom de la validation est obligatoire.']),
            new Assert\Length([
                'min' => 2,
                'max' => 150,
                'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.'
            ]),
            new Assert\Type(['type' => 'string'])
        ];
    }

    private function getTypeConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le type de validation est obligatoire.']),
            new Assert\Choice([
                'choices' => ['none','tel','audio','starleads','sms','hlr','hlr2','hlr_all','ip','n8n'],
                'message' => 'Le type de validation est invalide.'
            ])
        ];
    }

    private function getCampaignidConstraints(): array
    {
        return [
            new Assert\Optional([
                new Assert\Regex([
                    'pattern' => '/^(all|\d+)$/',
                    'message' => 'La campagne doit être "all" ou un identifiant numérique.'
                ])
            ])
        ];
    }

    private function getSourceidConstraints(): array
    {
        return [
            new Assert\Optional([
                new Assert\Regex([
                    'pattern' => '/^(all|\d+)$/',
                    'message' => 'La source doit être "all" ou un identifiant numérique.'
                ])
            ])
        ];
    }

    private function getType_phoneConstraints(): array
    {
        return [
            new Assert\Choice([
                'choices' => ['all','fixe','mobile'],
                'message' => 'La sélection téléphone est invalide.'
            ])
        ];
    }

    private function getAudio_fileConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Length(['max' => 255])) ];
    }
    private function getAudio_file_validConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Length(['max' => 255])) ];
    }
    private function getAudio_file_unvalidConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Length(['max' => 255])) ];
    }
    private function getAudio_file_errorConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Length(['max' => 255])) ];
    }

    private function getStart_hourConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Range(['min' => 0, 'max' => 24, 'notInRangeMessage' => 'Heure invalide (0-24).'])) ];
    }
    private function getEnd_hourConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Range(['min' => 0, 'max' => 24, 'notInRangeMessage' => 'Heure invalide (0-24).'])) ];
    }

    private function getMax_nbConstraints(): array
    {
        return [ new Assert\Optional(new Assert\PositiveOrZero(['message' => 'Doit être un entier positif.'])) ];
    }
    private function getMin_delayConstraints(): array
    {
        return [ new Assert\Optional(new Assert\PositiveOrZero(['message' => 'Doit être un entier positif.'])) ];
    }
    private function getFirst_delayConstraints(): array
    {
        return [ new Assert\Optional(new Assert\PositiveOrZero(['message' => 'Doit être un entier positif.'])) ];
    }

    private function getValid_scoring_actionConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Range(['min' => 0, 'max' => 1, 'notInRangeMessage' => 'La valeur doit être entre 0 et 1.'])) ];
    }
    private function getUnvalid_scoring_actionConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Range(['min' => 0, 'max' => 1, 'notInRangeMessage' => 'La valeur doit être entre 0 et 1.'])) ];
    }
    private function getFailed_scoring_actionConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Range(['min' => 0, 'max' => 1, 'notInRangeMessage' => 'La valeur doit être entre 0 et 1.'])) ];
    }
    private function getReject_scoring_actionConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Range(['min' => 0, 'max' => 1, 'notInRangeMessage' => 'La valeur doit être entre 0 et 1.'])) ];
    }

    private function getValid_scoring_statutConstraints(): array
    {
        return [ new Assert\Choice(['choices' => ['none','reject','valid','deversoir','pending'], 'message' => 'Statut invalide.']) ];
    }
    private function getUnvalid_scoring_statutConstraints(): array
    {
        return [ new Assert\Choice(['choices' => ['none','reject','valid','deversoir','pending'], 'message' => 'Statut invalide.']) ];
    }
    private function getFailed_scoring_statutConstraints(): array
    {
        return [ new Assert\Choice(['choices' => ['none','reject','valid','deversoir','pending'], 'message' => 'Statut invalide.']) ];
    }
    private function getReject_scoring_statutConstraints(): array
    {
        return [ new Assert\Choice(['choices' => ['none','reject','valid','deversoir','pending'], 'message' => 'Statut invalide.']) ];
    }

    private function getStart_lead_statutConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le statut initial est requis.']),
            new Assert\Choice(['choices' => ['raw','reject','valid','deversoir','pending'], 'message' => 'Statut de départ invalide.'])
        ];
    }

    private function getOrderConstraints(): array
    {
        return [ new Assert\Optional(new Assert\Range(['min' => 1, 'max' => 9, 'notInRangeMessage' => 'La priorité doit être comprise entre 1 et 9.'])) ];
    }

    private function getStatutConstraints(): array
    {
        return [ new Assert\Choice(['choices' => ['on','off'], 'message' => 'Le statut est invalide.']) ];
    }

    private function getApi_urlConstraints(): array
    {
        // Validé conditionnellement obligatoire si type === 'n8n' (règle métier)
        return [ new Assert\Optional(new Assert\Url(['message' => 'L\'URL fournie n\'est pas valide.'])) ];
    }
}
