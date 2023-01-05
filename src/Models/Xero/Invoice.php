<?php

namespace Firesphere\Xero\Models\Xero;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverStripe\ORM\FieldType\DBDate;

/**
 * Class \Firesphere\Xero\Models\Xero\Invoice
 *
 * @property string $Type
 * @property string $Date
 * @property string $DueDate
 * @property string $Status
 * @property string $LineAmountTypes
 * @property float $SubTotal
 * @property float $TotalTax
 * @property float $Total
 * @property float $TotalDiscount
 * @property string $UpdatedDateUTC
 * @property string $CurrencyCode
 * @property string $CurrencyRate
 * @property string $InvoiceID
 * @property string $InvoiceNumber
 * @property string $Reference
 * @property string $Url
 * @property string $SentToContact
 * @property string $ExpectedPaymentDate
 * @property string $PlannedPaymentDate
 * @property bool $HasAttachments
 * @property string $RepeatingInvoiceID
 * @property string $CreditNotes
 * @property float $AmountDue
 * @property float $AmountPaid
 * @property string $CISDeduction
 * @property string $FullyPaidOnDate
 * @property float $AmountCredited
 * @property int $ContactID
 * @method Contact Contact()
 */
class Invoice extends DataObject
{
   private static $table_name = 'XeroInvoice';

    private static $db = [
        'Type'                => 'Varchar(255)',
        'Date'                => 'Varchar(255)',
        'DueDate'             => 'Varchar(255)',
        'Status'              => 'Varchar(255)',
        'LineAmountTypes'     => 'Varchar(255)',
        'SubTotal'            => DBCurrency::class,
        'TotalTax'            => DBCurrency::class,
        'Total'               => DBCurrency::class,
        'TotalDiscount'       => DBCurrency::class,
        'UpdatedDateUTC'      => DBDate::class,
        'CurrencyCode'        => 'Varchar(255)',
        'CurrencyRate'        => 'Varchar(255)',
        'InvoiceID'           => 'Varchar(255)',
        'InvoiceNumber'       => 'Varchar(255)',
        'Reference'           => 'Varchar(255)',
        'Url'                 => 'Varchar(255)',
        'SentToContact'       => 'Varchar(255)',
        'ExpectedPaymentDate' => DBDate::class,
        'PlannedPaymentDate'  => DBDate::class,
        'HasAttachments'      => 'Boolean(false)',
        'RepeatingInvoiceID'  => 'Varchar(255)',
        'CreditNotes'         => 'Varchar(255)',
        'AmountDue'           => DBCurrency::class,
        'AmountPaid'          => DBCurrency::class,
        'CISDeduction'        => 'Varchar(255)',
        'FullyPaidOnDate'     => DBDate::class,
        'AmountCredited'      => DBCurrency::class,
    ];

    private static $has_one = [
        'Contact' => Contact::class,
    ];
}