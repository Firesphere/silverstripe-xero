<?php

namespace Firesphere\Xero\Extensions;

use Firesphere\Xero\Models\XeroDebugLog;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;

/**
 * Class \Firesphere\Xero\Extensions\OrderExtension
 *
 * @property string $XeroInvoiceID
 * @method DataList|XeroDebugLog[] XeroDebugLog()
 */
class OrderExtension extends DataExtension
{
    private static $db = [
        'XeroInvoiceID' => 'Varchar(255)',
    ];

    private static $has_many = [
        'XeroDebugLog' => XeroDebugLog::class,
    ];
}
