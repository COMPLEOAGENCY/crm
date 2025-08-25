<?php
namespace Models;

class InvoicePayment extends Model
{
    public static $TABLE_NAME = 'invoice_payment';
    public static $TABLE_INDEX = 'invoice_paymentid';
    public static $OBJ_INDEX = 'invoicePaymentId';
    public static $SCHEMA = array(
        "invoicePaymentId" => array(
            "field" => "invoice_paymentid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "creation_date" => array(
            "field" => "creation_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "update_date" => array(
            "field" => "update_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "payment_date" => array(
            "field" => "payment_date",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "reference" => array(
            "field" => "reference",
            "fieldType" => "string",
            "type" => "string",
            "default" => ""
        ),
        "invoiceid" => array(
            "field" => "invoiceid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "amount" => array(
            "field" => "amount",
            "fieldType" => "float",
            "type" => "float",
            "default" => 0
        ),
        "methodid" => array(
            "field" => "methodid",
            "fieldType" => "int",
            "type" => "int",
            "default" => 0
        ),
        "currency" => array(
            "field" => "currency",
            "fieldType" => "string",
            "type" => "string",
            "default" => "EUR"
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
     * Calcule la somme des paiements pour une facture
     * @param int $invoiceId
     * @return float
     */
    public function sumPaid($invoiceId = null): float
    {
        $invoiceId = $invoiceId ?? $this->invoiceid;
        if (empty($invoiceId)) {
            return 0;
        }
        
        $payments = $this->getList(1000000, [
            ['invoiceid', '=', $invoiceId]
        ]);
        
        $totalPaid = 0;
        if (is_array($payments)) {
            foreach ($payments as $payment) {
                $totalPaid += $payment->amount;
            }
        }
        
        return $totalPaid;
    }
}
