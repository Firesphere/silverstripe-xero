<?php

namespace Firesphere\Xero\Extensions;

use Firesphere\Xero\Models\Xero\Invoice;
use Firesphere\Xero\Models\XeroDebugLog;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;

/**
 * Class \Firesphere\Xero\Extensions\OrderExtension
 *
 * @property string $XeroID
 * @property int $XeroInvoiceID
 * @method Invoice XeroInvoice()
 * @method DataList|XeroDebugLog[] XeroDebugLog()
 */
class OrderExtension extends DataExtension
{
    private static $db = [
        'XeroID' => 'Varchar(255)',
    ];

    private static $has_one = [
        'XeroInvoice' => Invoice::class,
    ];

    private static $has_many = [
        'XeroDebugLog' => XeroDebugLog::class,
    ];
}
