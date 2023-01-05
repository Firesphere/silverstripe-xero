<?php

namespace Firesphere\Xero\Models\Xero;

use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\Xero\Models\Xero\Phone
 *
 * @property string $PhoneType
 * @property string $PhoneNumber
 * @property string $PhoneAreaCode
 * @property string $PhoneCountryCode
 * @property int $ContactID
 * @method Contact Contact()
 */
class Phone extends DataObject
{

    private static $table_name = 'XeroPhone';

    private static $db = [
        'PhoneType'        => 'Varchar(50)',
        'PhoneNumber'      => 'Varchar(50)',
        'PhoneAreaCode'    => 'Varchar(50)',
        'PhoneCountryCode' => 'Varchar(50)',
    ];

    private static $has_one = [
        'Contact' => Contact::class,
    ];
}