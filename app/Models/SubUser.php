<?php
// Path: src/app/Models/SubUser.php

namespace Models;

class SubUser extends Model
{
    public static $TABLE_NAME = 'user_sub';
    public static $TABLE_INDEX = 'user_subId';
    public static $OBJ_INDEX = 'user_subId';
    public static $SCHEMA = array(
        "user_subId" => array(
            "field" => "user_subId",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "creation_date" => array(
            "field" => "creation_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "update_date" => array(
            "field" => "update_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "userid" => array(
            "field" => "userid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "rolid" => array(
            "field" => "rolid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "first_name" => array(
            "field" => "first_name",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "last_name" => array(
            "field" => "last_name",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "statut" => array(
            "field" => "statut",
            "fieldType" => "string",
            "type" => "string",
            "default" => "on"
        ),
        "email" => array(
            "field" => "email",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "password" => array(
            "field" => "password",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "mobile_phone" => array(
            "field" => "mobile_phone",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "fixed_phone" => array(
            "field" => "fixed_phone",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "use_profile_pic" => array(
            "field" => "use_profile_pic",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "use_email_validation" => array(
            "field" => "use_email_validation",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "use_mobile_phone_validation" => array(
            "field" => "use_mobile_phone_validation",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "shopId" => array(
            "field" => "shopId",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        )
    );

    /**
     * Récupère la liste des sous-utilisateurs d'un utilisateur
     * @param int $userId ID de l'utilisateur parent
     * @return array Liste des sous-utilisateurs
     */
    public function getByUserId($userId)
    {
        return $this->getList(
            1000,
            ['userid' => $userId],
            null,
            null,
            'user_subId',
            'asc'
        );
    }

    /**
     * Génère le hash de connexion pour un sous-utilisateur
     * @return string Hash MD5 pour la connexion
     */
    public function getConnectionHash()
    {
        if (!empty($this->shopId)) {
            return md5($this->shopId . 'salt');
        }
        return '';
    }
}
