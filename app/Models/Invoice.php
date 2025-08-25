<?php
namespace Models;

class Invoice extends Model
{
    public static $TABLE_NAME = 'invoice';
    public static $TABLE_INDEX = 'invoiceid';
    public static $OBJ_INDEX = 'invoiceId';
    public static $SCHEMA = array(
        "invoiceId" => array(
            "field" => "invoiceid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "userid" => array(
            "field" => "userid",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp" => array(
            "field" => "timestamp",
            "fieldType" => "int",
            "type" => "int",
            "default" => null
        ),
        "timestamp_duedate" => array(
            "field" => "timestamp_duedate",
            "fieldType" => "text",
            "type" => "string",
            "default" => null
        ),
        "data" => array(
            "field" => "data",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "ht" => array(
            "field" => "ht",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "tva" => array(
            "field" => "tva",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        ),
        "credits" => array(
            "field" => "credits",
            "fieldType" => "string",
            "type" => "string",
            "default" => null
        )
    );

    public function __construct($data = [])
    {
        parent::__construct($data);
    }

    /**
     * Custom method to get total invoice data by user and timestamp.
     * @param int|null $userid
     * @param int $timestamp
     * @return array
     */
    public function getTotalInvoice($userid = null, $timestamp = 1609459200): array
    {
        $userid = $userid ?? $this->userid;
        // echo "getTotalInvoice<br/>";
        // echo "userid: $userid<br/>";
        $total_ht = $total_tva = $total_credits = $total_paid = $total_unpaid = 0;
        // echo "timestamp: $timestamp<br/>";

        // Récupère les factures dont le timestamp est supérieur à une date donnée et qui appartiennent à un utilisateur spécifique.
        $invoices = $this->getList(
            1000000, 
            [
                ['timestamp', '>', $timestamp],  // Factures créées après $timestamp
                ['userid', '=', $userid]         // Factures appartenant à l'utilisateur $userid
            ]
        );

        if(is_countable($invoices)){
            foreach ($invoices as $invoice) {
                // Calculer le montant payé pour cette facture via invoice_payment
                $invoicePayment = new \Models\InvoicePayment();
                $paid = $invoicePayment->sumPaid($invoice->invoiceId);
                
                $total_paid += $paid;
                $total_ht += $invoice->ht;
                $total_tva += $invoice->tva;
                $total_credits += $invoice->credits;
            }
        }

        $total_unpaid = $total_ht + $total_tva - $total_paid;
        return [
            "total_ht" => $total_ht,
            "total_tva" => $total_tva,
            "total_credits" => $total_credits,
            "total_paid" => $total_paid,
            "total_unpaid" => $total_unpaid,
        ];
    }
}
