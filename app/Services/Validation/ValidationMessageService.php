<?php
namespace Services\Validation;

use Framework\SessionHandler;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationMessageService
{
    private $session;
    private $errors = [];
    
    public function __construct()
    {
        $this->session = SessionHandler::getInstance();
    }

    /**
     * Ajoute un message de succès
     */
    public function addSuccess(string $message, array $details = []): void
    {
        $this->addFlashMessage('success', $message, $details);
    }

    /**
     * Ajoute un message d'erreur générique
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
     */
    public function hasFieldError(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    /**
     * Retourne la classe CSS pour les champs en erreur
     */
    public function getFieldClass(string $field): string
    {
        return $this->hasFieldError($field) ? 'is-invalid' : '';
    }

    /**
     * Génère le HTML pour afficher les erreurs d'un champ
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
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors) || !empty($this->session->get('flash_messages.error', []));
    }

    /**
     * Ajoute un message flash à la session
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
