<?php

namespace Services\Validation;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Service de validation des données utilisateur
 * 
 * Implémente la validation complète des données utilisateur en utilisant
 * le composant Validator de Symfony. Gère les contraintes de validation
 * pour chaque champ du modèle utilisateur.
 *
 * Fonctionnalités :
 * - Validation des champs obligatoires
 * - Validation des formats (email, téléphone, etc.)
 * - Validation des valeurs numériques
 * - Validation des règles métier spécifiques
 *
 * @package Services\Validation
 * @uses \Symfony\Component\Validator\Validation
 * @uses \Symfony\Component\Validator\Constraints
 */
class UserValidationService
{
    /** @var ValidatorInterface Instance du validateur Symfony */
    private $validator;

    /** @var string Nom du champ en cours de validation */
    private $fieldName;

    /**
     * Liste des champs validables pour un utilisateur
     * 
     * @var array<string> Liste des noms de champs
     * @static
     */
    private static array $fields = [
        'registration_number',
        'company',
        'email',
        'vat_number',
        'phone',
        'mobile',
        'cp',
        'city',
        'country',
        'address',
        'first_name',
        'last_name',
        'civ',
        'type',
        'vendor_id',
        'encours_max',
        'global_day_capping',
        'global_month_capping',
        'statut',
        'marque_blanche',
        'deversoir',
        'legal_sms',
        'welcome_sms',
        'details',
        'email2',
        'sale_notification_email',
        'sale_notification_email2',
        'state',
        'user_exclusion'
    ];

    /**
     * Initialise le service de validation
     *
     * Utilise soit le validateur fourni, soit en crée un nouveau.
     *
     * @param ValidatorInterface|null $validator Instance du validateur Symfony
     */
    public function __construct(ValidatorInterface $validator = null)
    {
        $this->validator = $validator ?? Validation::createValidator();
    }

    /**
     * Valide les données d'un utilisateur
     *
     * Processus de validation :
     * 1. Applique les contraintes de base pour chaque champ
     * 2. Vérifie les champs obligatoires
     * 3. Valide les règles métier spécifiques
     * 4. Vérifie la présence d'au moins un numéro de téléphone
     *
     * @param array $data Données utilisateur à valider
     * @return ConstraintViolationListInterface Liste des violations de contraintes
     */
    public function validateUser(array $data): ConstraintViolationListInterface
    {
        $constraints = new Assert\Collection([
            'fields' => $this->getFieldsConstraints(),
            'allowExtraFields' => true,
            'allowMissingFields' => true,
            'missingFieldsMessage' => 'Le champ {{ field }} est manquant.',
            'extraFieldsMessage' => 'Le champ {{ field }} n\'est pas attendu.',
        ]);

        $violations = new ConstraintViolationList();
        $baseViolations = $this->validator->validate($data, $constraints);

        foreach ($baseViolations as $violation) {
            $violations->add($violation);
        }

        $this->validatePhoneRequirement($data, $violations);

        return $violations;
    }

    /**
     * Récupère les contraintes pour tous les champs
     *
     * Pour chaque champ défini dans self::$fields, recherche une méthode
     * de contraintes spécifique (get[FieldName]Constraints).
     *
     * @return array<string,Assert\Composite> Tableau des contraintes par champ
     * @access private
     */
    private function getFieldsConstraints(): array
    {
        $fieldsConstraints = [];
        foreach (self::$fields as $field) {
            $constraintsMethod = 'get' . ucfirst($field) . 'Constraints';
            if (method_exists($this, $constraintsMethod)) {
                $fieldsConstraints[$field] = $this->$constraintsMethod();
            }
        }
        return $fieldsConstraints;
    }

    /**
     * Vérifie si un champ est obligatoire
     *
     * Un champ est considéré obligatoire s'il a une contrainte NotBlank.
     *
     * @param string $fieldName Nom du champ à vérifier
     * @return bool True si le champ est obligatoire
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

    /**
     * Récupère les contraintes pour un champ spécifique
     *
     * @param string $fieldName Nom du champ
     * @return array<Assert\Constraint> Liste des contraintes du champ
     * @access private
     * @throws \RuntimeException Si la méthode de contraintes n'existe pas
     */
    private function getConstraintsForField(string $fieldName): array
    {
        $method = 'get' . ucfirst($fieldName) . 'Constraints';
        if (method_exists($this, $method)) {
            $constraints = $this->$method();
            return is_array($constraints) ? $constraints : [];
        }
        return [];
    }

    private function validatePhoneRequirement(array $data, ConstraintViolationList $violations): void
    {
        if (empty($data['phone']) && empty($data['mobile'])) {
            $violations->add(new ConstraintViolation(
                'Veuillez fournir au moins un numéro de téléphone fixe ou mobile.',
                null,
                [],
                $data,
                'phone or mobile',
                null
            ));
        }
    }

    private function getCompanyConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le champ société est obligatoire.']),
            new Assert\Length([
                'min' => 2,
                'max' => 100,
                'minMessage' => 'Le champ société doit contenir au moins {{ limit }} caractères.',
                'maxMessage' => 'Le champ société ne peut pas dépasser {{ limit }} caractères.'
            ]),
            new Assert\Type(['type' => 'string', 'message' => 'Ce champ doit être une chaîne de caractères.'])
        ];
    }

    private function getEmailConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le champ Email est obligatoire.']),
            new Assert\Email(['message' => 'Le format de l\'email n\'est pas valide.'])
        ];
    }

    private function getRegistration_numberConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le champ SIREN est obligatoire.']),
            new Assert\Regex(['pattern' => '/^[0-9]{9}$/', 'message' => 'Le format du SIREN est invalide.'])
        ];
    }

    private function getVat_numberConstraints(): array
    {
        return [
            new Assert\Optional([
                new Assert\Regex([
                    'pattern' => '/^(FR)?[0-9A-Z]{2}[0-9]{9}$/',
                    'message' => 'Le format du numéro de TVA est invalide.'
                ])
            ])
        ];
    }

    private function getPhoneConstraints(): array
    {
        return [
            new Assert\Optional([
                new Assert\Regex([
                    'pattern' => '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/',
                    'message' => 'Le format du numéro de téléphone est invalide.'
                ])
            ])
        ];
    }

    private function getMobileConstraints(): array
    {
        return [
            new Assert\Optional([
                new Assert\Regex([
                    'pattern' => '/^(?:(?:\+|00)33|0)\s*[6-7](?:[\s.-]*\d{2}){4}$/',
                    'message' => 'Le format du numéro de mobile est invalide.'
                ])
            ])
        ];
    }

    private function getCpConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le champ Code Postal est obligatoire.']),
            new Assert\Regex(['pattern' => '/^[0-9]{5}$/', 'message' => 'Le format du code postal est invalide.'])
        ];
    }

    private function getCityConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le champ Ville est obligatoire.']),
            new Assert\Length([
                'max' => 100,
                'maxMessage' => 'Le champ Ville ne peut pas dépasser {{ limit }} caractères.'
            ])
        ];
    }

    private function getCountryConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le champ Pays est obligatoire.']),
            new Assert\Length([
                'min' => 2,
                'max' => 2,
                'exactMessage' => 'Le code pays doit contenir exactement {{ limit }} caractères.'
            ])
        ];
    }

    private function getAddressConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le champ Adresse est obligatoire.']),
            new Assert\Length([
                'max' => 255,
                'maxMessage' => 'Le champ adresse ne peut pas dépasser {{ limit }} caractères.'
            ])
        ];
    }

    private function getFirst_nameConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le champ Prénom est obligatoire.']),
            new Assert\Length([
                'max' => 100,
                'maxMessage' => 'Le champ prénom ne peut pas dépasser {{ limit }} caractères.'
            ])
        ];
    }

    private function getLast_nameConstraints(): array
    {
        return [
            new Assert\NotBlank(['message' => 'Le champ Nom est obligatoire.']),
            new Assert\Length([
                'max' => 100,
                'maxMessage' => 'Le champ nom ne peut pas dépasser {{ limit }} caractères.'
            ])
        ];
    }

    private function getCivConstraints(): array
    {
        return [
            new Assert\Optional([
                new Assert\Choice([
                    'choices' => ['M', 'Mme', 'Melle'],
                    'message' => 'La civilité est invalide.'
                ])
            ])
        ];
    }

    private function getTypeConstraints(): array
    {
        return [
            new Assert\Choice([
                'choices' => ['client', 'provider', 'admin'],
                'message' => 'Le type de compte est invalide.'
            ])
        ];
    }

    private function getVendor_idConstraints(): array
    {
        return [
            new Assert\Optional([
                new Assert\Type([
                    'type' => 'string',
                    'message' => 'Le chargé de compte doit être un identifiant valide.'
                ])
            ])
        ];
    }

    private function getEncours_maxConstraints(): array
    {
        return [
            new Assert\Optional(
                new Assert\PositiveOrZero(['message' => 'L\'encours maximum doit être un nombre positif.'])
            )
        ];
    }

    private function getGlobal_day_cappingConstraints(): array
    {
        return [
            new Assert\Optional(
                new Assert\PositiveOrZero(['message' => 'Le capping journalier doit être un entier positif.'])
            )
        ];
    }

    private function getGlobal_month_cappingConstraints(): array
    {
        return [
            new Assert\Optional(
                new Assert\PositiveOrZero(['message' => 'Le capping mensuel doit être un entier positif.'])
            )
        ];
    }

    private function getStatusConstraints(): array
    {
        return [
            new Assert\Choice([
                'choices' => ['on', 'off'],
                'message' => 'Le statut est invalide.'
            ])
        ];
    }

    private function getMarque_blancheConstraints(): array
    {
        return [
            new Assert\Choice([
                'choices' => ['yes', 'no'],
                'message' => 'La valeur de marque blanche est invalide.'
            ])
        ];
    }

    private function getDeversoirConstraints(): array
    {
        return [
            new Assert\Choice([
                'choices' => ['yes', 'no'],
                'message' => 'La valeur de déversoir est invalide.'
            ])
        ];
    }

    private function getLegal_smsConstraints(): array
    {
        return [
            new Assert\Choice([
                'choices' => ['on', 'off'],
                'message' => 'La valeur de SMS légal est invalide.'
            ])
        ];
    }

    private function getWelcome_smsConstraints(): array
    {
        return [
            new Assert\Optional(
                new Assert\Length([
                    'max' => 160,
                    'maxMessage' => 'Le SMS de bienvenue ne peut dépasser {{ limit }} caractères.'
                ])
            )
        ];
    }

    private function getDetailsConstraints(): array
    {
        return [
            new Assert\Optional(
                new Assert\Type([
                    'type' => 'string',
                    'message' => 'Le champ details doit être une chaîne de caractères.'
                ])
            )
        ];
    }

    private function getEmail2Constraints(): array
    {
        return [
            new Assert\Optional(
                new Assert\Email(['message' => 'Le format de l\'email secondaire est invalide.'])
            )
        ];
    }

    private function getSale_notification_emailConstraints(): array
    {
        return [
            new Assert\Type([
                'type' => 'string',
                'message' => 'La notification email de vente doit être un booléen.'
            ])
        ];
    }

    private function getSale_notification_email2Constraints(): array
    {
        return [
            new Assert\Type([
                'type' => 'string',
                'message' => 'La notification email secondaire doit être un booléen.'
            ])
        ];
    }

    private function getStateConstraints(): array
    {
        return [
            new Assert\Optional(
                new Assert\Length([
                    'max' => 100,
                    'maxMessage' => 'Le champ Région ne peut pas dépasser {{ limit }} caractères.'
                ])
            )
        ];
    }

    private function getUser_exclusionConstraints(): array
    {
        return [
            new Assert\Optional([
                new Assert\Type([
                    'type' => 'array',
                    'message' => 'La liste d\'exclusion doit être un tableau.'
                ])
            ])
        ];
    }
}