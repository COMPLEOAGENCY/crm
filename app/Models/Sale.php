<?php
// Path: src/app/Models/Sale.php

namespace Models;
use Framework\CacheManager;

class Sale extends Model
{
    public static $TABLE_NAME = 'sale';
    public static $TABLE_INDEX = 'saleid';
    public static $OBJ_INDEX = 'saleId';
    public static $SCHEMA = array(
        "saleId" => array(
            "field" => "saleid",
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
        "updateTimestamp" => array(
            "field" => "update_timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "leadId" => array(
            "field" => "leadid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "userId" => array(
            "field" => "userid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "userSubId" => array(
            "field" => "user_subid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
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
        "userCampaignId" => array(
            "field" => "usercampaignid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "price" => array(
            "field" => "price",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0.0
        ),
        "tva" => array(
            "field" => "tva",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0.0
        ),
        "saleWeight" => array(
            "field" => "sale_weight",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "clientKpi1" => array(
            "field" => "client_kpi_1",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "clientKpi2" => array(
            "field" => "client_kpi_2",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "clientKpi3" => array(
            "field" => "client_kpi_3",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "refundAskTimestamp" => array(
            "field" => "refund_ask_timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "refundAskReason" => array(
            "field" => "refund_ask_reason",
            "fieldType" => "mediumtext",
            "type" => "string",
            "default" => null
        ),
        "refundStatutTimestamp" => array(
            "field" => "refund_statut_timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "refundStatut" => array(
            "field" => "refund_statut",
            "fieldType" => "enum",
            "type" => "string",
            "default" => 'none'
        ),
        "saleScoring" => array(
            "field" => "sale_scoring",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0.0
        ),
        "smsLogId" => array(
            "field" => "sms_logId",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "shopId" => array(
            "field" => "shopId",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }

    /**
     * Récupère une vente par son leadId
     * @param int $leadId
     * @return \stdClass|null
     */
    public function getByLeadId(int $leadId): ?\stdClass
    {
        $results = $this->getList(['leadid' => $leadId]);
        return !empty($results) ? reset($results) : null;
    }
}
