<?php

namespace Firesphere\Xero\Models\Xero;

use SilverStripe\ORM\DataObject;

/**
 * Shadow copy of a Xero Contact Person
 *
 * @property string $FirstName
 * @property string $LastName
 * @property string $EmailAddress
 * @property bool $IncludeInEmails
 * @property int $ContactID
 * @method Contact Contact()
 */
class ContactPerson extends DataObject
{

    private static $table_name = 'XeroContactPerson';

    private static $db = [
        'FirstName'       => 'Varchar(255)',
        'LastName'        => 'Varchar(255)',
        'EmailAddress'    => 'Varchar(255)',
        'IncludeInEmails' => 'Boolean(true)',
    ];

    private static $has_one = [
        'Contact' => Contact::class,
    ];
}