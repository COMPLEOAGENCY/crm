<?php
namespace Classes;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

/**
 * Classe Phone - Permet le chaînage sur une propriété de téléphone
 */
class Phone
{
    /**
     * @var string Le numéro de téléphone
     */
    private $number;
    
    /**
     * @var string Le code pays
     */
    private $country;
    
    /**
     * Constructeur
     * 
     * @param string $number Le numéro de téléphone
     * @param string $country Le code pays (format 2 ou 3 lettres)
     */
    public function __construct($number, $country = 'FR')
    {
        $this->number = $number;
        $this->country = $this->normalizeCountryCode($country);
    }
    
    /**
     * Normalise un code pays (convertit 3 lettres en 2 lettres)
     * 
     * @param string $country Code pays à normaliser
     * @return string Code pays normalisé
     */
    private function normalizeCountryCode($country)
    {
        if (empty($country)) {
            return 'FR';
        }
        
        // Convertir le code pays si nécessaire (3 lettres -> 2 lettres)
        if (strlen($country) === 3) {
            return substr($country, 0, 2);
        }
        
        return $country;
    }
    
    /**
     * Conversion en chaîne - retourne le numéro brut
     * 
     * @return string
     */
    public function __toString()
    {
        return (string)$this->number;
    }
    
    /**
     * Format E164 (ex: +33612345678)
     * 
     * @return string
     */
    public function e164()
    {
        return $this->formatNumber(PhoneNumberFormat::E164);
    }
    
    /**
     * Format international (ex: +33 6 12 34 56 78)
     * 
     * @return string
     */
    public function international()
    {
        return $this->formatNumber(PhoneNumberFormat::INTERNATIONAL);
    }
    
    /**
     * Format national (ex: 06 12 34 56 78)
     * 
     * @return string
     */
    public function national()
    {
        return $this->formatNumber(PhoneNumberFormat::NATIONAL);
    }
    
    /**
     * Format RFC3966 (ex: tel:+33-6-12-34-56-78)
     * 
     * @return string
     */
    public function rfc3966()
    {
        return $this->formatNumber(PhoneNumberFormat::RFC3966);
    }
    
    /**
     * Formate le numéro selon le format spécifié
     * 
     * @param int $format Format de sortie
     * @return string Numéro formaté ou numéro original en cas d'erreur
     */
    private function formatNumber($format)
    {
        if (empty($this->number)) {
            return '';
        }
        
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $numberProto = $phoneUtil->parse($this->number, $this->country);
            
            if ($phoneUtil->isValidNumber($numberProto)) {
                return $phoneUtil->format($numberProto, $format);
            }
            
            return $this->number;
        } catch (NumberParseException $e) {
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
            $numberProto = $phoneUtil->parse($this->number, $this->country);
            return $phoneUtil->isValidNumber($numberProto);
        } catch (NumberParseException $e) {
            return false;
        }
    }
    
    /**
     * Récupère le code pays détecté du numéro
     * 
     * @return string
     */
    public function country()
    {
        if (empty($this->number)) {
            return $this->country;
        }
        
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $numberProto = $phoneUtil->parse($this->number, $this->country);
            if ($phoneUtil->isValidNumber($numberProto)) {
                return $phoneUtil->getRegionCodeForNumber($numberProto);
            }
            return $this->country;
        } catch (NumberParseException $e) {
            return $this->country;
        }
    }
    
    /**
     * Méthode statique pour formater un numéro sans créer d'instance
     * 
     * @param string $number Le numéro de téléphone
     * @param string $country Le code pays
     * @param int $format Format de sortie
     * @return string Numéro formaté
     */
    public static function format($number, $country = 'FR', $format = PhoneNumberFormat::E164)
    {
        $phone = new self($number, $country);
        return $phone->formatNumber($format);
    }
}
