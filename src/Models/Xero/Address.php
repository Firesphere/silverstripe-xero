<?php

namespace Firesphere\Xero\Models\Xero;

use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\Xero\Models\Xero\Address
 *
 * @property string $AddressType
 * @property string $AddressLine1
 * @property string $AddressLine2
 * @property string $AddressLine3
 * @property string $AddressLine4
 * @property string $City
 * @property string $Region
 * @property string $PostalCode
 * @property string $Country
 * @property string $AttentionTo
 * @property int $ContactID
 * @method Contact Contact()
 */
class Address extends DataObject
{

    private static $table_name = 'XeroAddress';

    private static $db = [
        'AddressType'  => 'Varchar(255)',
        'AddressLine1' => 'Varchar(500)',
        'AddressLine2' => 'Varchar(500)',
        'AddressLine3' => 'Varchar(500)',
        'AddressLine4' => 'Varchar(500)',
        'City'         => 'Varchar(255)',
        'Region'       => 'Varchar(255)',
        'PostalCode'   => 'Varchar(50)',
        'Country'      => 'Varchar(50)',
        'AttentionTo'  => 'Varchar(255)',
    ];

    private static $has_one = [
        'Contact' => Contact::class,
    ];
}