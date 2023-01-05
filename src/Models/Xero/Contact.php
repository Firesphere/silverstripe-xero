<?php

namespace Firesphere\Xero\Models\Xero;

use Firesphere\Xero\Models\Tenant;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBDate;

/**
 * Shadow copy of what's in Xero
 *
 * @property string $ContactID
 * @property string $ContactNumber
 * @property string $AccountNumber
 * @property string $ContactStatus
 * @property string $Name
 * @property string $FirstName
 * @property string $LastName
 * @property string $EmailAddress
 * @property string $SkypeUserName
 * @property string $BankAccountDetails
 * @property string $CompanyNumber
 * @property string $TaxNumber
 * @property string $AccountsReceivableTaxType
 * @property string $AccountsPayableTaxType
 * @property bool $IsSupplier
 * @property bool $IsCustomer
 * @property string $DefaultCurrency
 * @property string $UpdatedDateUTC
 * @property int $TenantID
 * @method Tenant Tenant()
 * @method DataList|Address[] Addresses()
 * @method DataList|ContactPerson[] ContactPersons()
 * @method DataList|Phone[] Phones()
 */
class Contact extends DataObject
{
    private static $table_name = 'XeroContact';

    private static $db = [
        'ContactID'                 => 'Varchar(255)',
        'ContactNumber'             => 'Varchar(255)',
        'AccountNumber'             => 'Varchar(255)',
        'ContactStatus'             => 'Enum("ACTIVE,ARCHIVED,GDPRREQUEST", "ACTIVE")',
        'Name'                      => 'Varchar(255)',
        'FirstName'                 => 'Varchar(255)',
        'LastName'                  => 'Varchar(255)',
        'EmailAddress'              => 'Varchar(255)',
        'SkypeUserName'             => 'Varchar(255)',
        'BankAccountDetails'        => 'Varchar(255)',
        'CompanyNumber'             => 'Varchar(255)',
        'TaxNumber'                 => 'Varchar(255)',
        'AccountsReceivableTaxType' => 'Varchar(255)',
        'AccountsPayableTaxType'    => 'Varchar(255)',
        'IsSupplier'                => DBBoolean::class,
        'IsCustomer'                => DBBoolean::class,
        'DefaultCurrency'           => 'Varchar(255)',
        'UpdatedDateUTC'            => DBDate::class,
    ];

    private static $has_one = [
        'Tenant' => Tenant::class,
    ];

    private static $has_many = [
        'Addresses'      => Address::class,
        'ContactPersons' => ContactPerson::class,
        'Phones'         => Phone::class,
    ];

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->UpdatedDateUTC && !is_int($this->UpdatedDateUTC)) {
            $orig = $this->UpdatedDateUTC;
            $timestamp = str_replace(["/Date(", "+0000)/"], "", $orig);
            $this->UpdatedDateUTC = date('Y-m-d', $timestamp);
        }
    }
}