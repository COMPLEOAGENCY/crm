<?php
namespace Services\Validation;

use Framework\SessionHandler;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Service de gestion des messages de validation
 * 
 * Gère les messages de validation et d'erreur pour l'interface utilisateur.
 * Intègre la gestion des messages flash via la session et le formatage
 * des erreurs de validation Symfony.
 *
 * Fonctionnalités :
 * - Messages de succès et d'erreur
 * - Messages flash en session
 * - Erreurs par champ de formulaire
 * - Intégration avec les violations de contraintes Symfony
 * - Génération de classes CSS et HTML pour l'affichage
 *
 * @package Services\Validation
 * @uses \Framework\SessionHandler
 * @uses \Symfony\Component\Validator\ConstraintViolationListInterface
 */
class ValidationMessageService
{
    /** @var SessionHandler Gestionnaire de session */
    private $session;

    /** @var array<string,array> Erreurs par champ */
    private $errors = [];
    
    /**
     * Initialise le service de messages
     * 
     * Récupère l'instance du gestionnaire de session pour
     * la gestion des messages flash.
     */
    public function __construct()
    {
        $this->session = SessionHandler::getInstance();
    }

    /**
     * Ajoute un message de succès
     *
     * Le message sera stocké en session et affiché à la prochaine requête.
     *
     * @param string $message Message à afficher
     * @param array $details Détails additionnels (optionnel)
     */
    public function addSuccess(string $message, array $details = []): void
    {
        $this->addFlashMessage('success', $message, $details);
    }

    /**
     * Ajoute un message d'erreur
     *
     * Peut être lié à un champ spécifique du formulaire.
     * Le message sera stocké en session et affiché à la prochaine requête.
     *
     * @param string $message Message d'erreur
     * @param array $details Détails additionnels (optionnel)
     * @param string|null $field Nom du champ en erreur (optionnel)
     */
    public function addError(string $message, array $details = [], ?string $field = null): void
    {
        $this->addFlashMessage('error', $message, $details);
        if ($field) {
            $this->addFieldError($field, $message);
        }
    }

    /**
     * Ajoute les violations de contraintes Symfony
     *
     * Convertit les violations de contraintes en messages d'erreur
     * et les associe aux champs correspondants.
     *
     * @param ConstraintViolationListInterface $violations Liste des violations
     */
    public function addViolations(ConstraintViolationListInterface $violations): void
    {
        foreach ($violations as $violation) {
            $field = str_replace(['fields[', ']', '['], '', $violation->getPropertyPath());
            $message = $violation->getMessage();
            
            // Utiliser uniquement addError qui gérera à la fois le message flash et l'erreur de champ
            $this->addError($message, [], $field);
        }
    }

    /**
     * Ajoute une erreur pour un champ spécifique
     *
     * @param string $field Nom du champ
     * @param string $message Message d'erreur
     * @access private
     */
    private function addFieldError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = ['message' => $message];
    }

    /**
     * Vérifie si un champ a des erreurs
     *
     * @param string $field Nom du champ
     * @return bool True si le champ a des erreurs
     */
    public function hasFieldError(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    /**
     * Retourne la classe CSS pour les champs en erreur
     *
     * Retourne 'is-invalid' si le champ a des erreurs,
     * une chaîne vide sinon.
     *
     * @param string $field Nom du champ
     * @return string Classe CSS
     */
    public function getFieldClass(string $field): string
    {
        return $this->hasFieldError($field) ? 'is-invalid' : '';
    }

    /**
     * Génère le HTML pour afficher les erreurs d'un champ
     *
     * Format du HTML généré :
     * <div class="invalid-feedback d-block">
     *     message1<br/>
     *     message2<br/>
     * </div>
     *
     * @param string $field Nom du champ
     * @return string HTML des messages d'erreur
     */
    public function getErrorHTML(string $field): string
    {
        if (!$this->hasFieldError($field)) {
            return '';
        }

        $html = '<div class="invalid-feedback d-block">';
        foreach ($this->errors[$field] as $error) {
            $html .= htmlspecialchars($error['message']) . '<br/>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Récupère tous les messages flash
     *
     * Retourne un tableau avec les types de messages :
     * - error : Messages d'erreur
     * - success : Messages de succès
     * - warning : Messages d'avertissement
     *
     * @return array<string,array> Messages par type
     */
    public function getMessages(): array
    {
        $messages = [
            'error' => $this->session->get('flash_messages.error', []),
            'success' => $this->session->get('flash_messages.success', [])
        ];

        $this->session->remove('flash_messages.error');
        $this->session->remove('flash_messages.success');

        return $messages;
    }

    /**
     * Vérifie s'il y a des erreurs
     *
     * @return bool True si il y a des erreurs
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors) || !empty($this->session->get('flash_messages.error', []));
    }

    /**
     * Ajoute un message flash à la session
     *
     * @param string $type Type de message (error, success, etc.)
     * @param string $message Message à afficher
     * @param array $details Détails additionnels (optionnel)
     * @access private
     */
    private function addFlashMessage(string $type, string $message, array $details = []): void
    {
        $messages = $this->session->get('flash_messages.' . $type, []);
        
        $messages[] = [
            'message' => $message,
            'details' => $details
        ];
        
        $this->session->set('flash_messages.' . $type, $messages);
        $this->session->persistNow();
    }
}
