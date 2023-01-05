<?php

namespace Firesphere\Xero\Extensions;

use Firesphere\Xero\Models\Xero\Contact;
use Firesphere\Xero\Models\XeroDebugLog;
use SilverStripe\ORM\DataExtension;

/**
 * Class \Firesphere\Xero\Extensions\ClientExtension
 *
 * @property string $XeroContactID
 * @property int $ContactID
 * @method Contact Contact()
 * @method DataList|XeroDebugLog[] XeroDebugLog()
 */
class ClientExtension extends DataExtension
{
    private static $db = [
        'XeroContactID' => 'Varchar(255)',
    ];

    private static $has_one = [
        'CopyContact' => Contact::class,
    ];

    private static $has_many = [
        'XeroDebugLog' => XeroDebugLog::class,
    ];
}
