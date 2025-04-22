<?php
namespace Classes;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

/**
 * Classe pour gérer les numéros de téléphone avec formatage international
 */
class Phone
{
    /**
     * @var string Le numéro de téléphone
     */
    private $number;
    
    /**
     * @var string Le code pays par défaut
     */
    private $defaultCountry;
    
    /**
     * Constructeur
     * 
     * @param string $number Le numéro de téléphone
     * @param string $defaultCountry Le code pays par défaut
     */
    public function __construct($number, $defaultCountry = 'FR')
    {
        $this->number = $number;
        $this->defaultCountry = $defaultCountry;
    }
    
    /**
     * Conversion en chaîne
     * 
     * @return string Le numéro de téléphone brut
     */
    public function __toString()
    {
        return (string)$this->number;
    }
    
    /**
     * Récupère le numéro au format international
     * 
     * @param int $format Format de sortie (E164, INTERNATIONAL, NATIONAL ou RFC3966)
     * @return string Numéro formaté ou numéro original en cas d'erreur
     */
    public function getInternational($format = PhoneNumberFormat::E164)
    {
        if (empty($this->number)) {
            return '';
        }
        
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $numberProto = $phoneUtil->parse($this->number, $this->defaultCountry);
            
            // Vérifier si le numéro est valide
            if ($phoneUtil->isValidNumber($numberProto)) {
                return $phoneUtil->format($numberProto, $format);
            }
            
            // Si le numéro n'est pas valide, retourner le numéro original
            return $this->number;
        } catch (NumberParseException $e) {
            // En cas d'erreur, retourner le numéro original
            return $this->number;
        }
    }
    
    /**
     * Vérifie si le numéro est valide
     * 
     * @return bool
     */
    public function isValid()
    {
        if (empty($this->number)) {
            return false;
        }
        
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $numberProto = $phoneUtil->parse($this->number, $this->defaultCountry);
            return $phoneUtil->isValidNumber($numberProto);
        } catch (NumberParseException $e) {
            return false;
        }
    }
    
    /**
     * Récupère le code pays du numéro
     * 
     * @return string|null Code pays ou null si invalide
     */
    public function getCountryCode()
    {
        if (empty($this->number)) {
            return null;
        }
        
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $numberProto = $phoneUtil->parse($this->number, $this->defaultCountry);
            if ($phoneUtil->isValidNumber($numberProto)) {
                return $phoneUtil->getRegionCodeForNumber($numberProto);
            }
            return null;
        } catch (NumberParseException $e) {
            return null;
        }
    }
}
