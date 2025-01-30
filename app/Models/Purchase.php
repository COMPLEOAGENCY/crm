<?php

namespace Models;

class Purchase extends Model
{
    public static $TABLE_NAME = 'purchase';
    public static $TABLE_INDEX = 'purchaseid';
    public static $OBJ_INDEX = 'purchaseId';
    
    public static $SCHEMA = array(
        "purchaseId" => array(
            "field" => "purchaseid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "leadId" => array(
            "field" => "leadid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "userId" => array(
            "field" => "userid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "sourceId" => array(
            "field" => "sourceid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "campaignId" => array(
            "field" => "campaignid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "targetId" => array(
            "field" => "targetid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "price" => array(
            "field" => "price",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0.0
        ),
        "statut" => array(
            "field" => "statut",
            "fieldType" => "enum",
            "type" => "string",
            "default" => "pending"
        ),
        "data" => array(
            "field" => "data",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        )
    );

    /**
     * Constructeur
     *
     * @param array $data Les données initiales pour l'instance du modèle
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Vérifie si le purchase est en attente
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->statut === 'pending';
    }

    /**
     * Vérifie si le purchase est validé
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->statut === 'valid';
    }

    /**
     * Vérifie si le purchase est rejeté
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->statut === 'reject';
    }

    /**
     * Valide le purchase
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->statut = 'valid';
        return $this->save();
    }

    /**
     * Rejette le purchase
     *
     * @return bool
     */
    public function reject(): bool
    {
        $this->statut = 'reject';
        return $this->save();
    }
}
