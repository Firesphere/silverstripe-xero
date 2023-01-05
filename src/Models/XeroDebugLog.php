<?php

namespace Firesphere\Xero\Models;

use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\Xero\Models\XeroDebugLog
 *
 * @property string $Message
 * @property string $XeroUUID
 * @property string $Level
 * @property int $OrderID
 * @property string $OrderClass
 * @property int $ClientID
 * @property string $ClientClass
 * @method DataObject Order()
 * @method DataObject Client()
 */
class XeroDebugLog extends DataObject
{
    private static $table_name = 'XeroLog';

    private static $db = [
        'Message'  => 'Text',
        'XeroUUID' => 'Varchar(255)',
        'Level'    => 'Enum("ERROR,WARNING,INFO","INFO")'
    ];

    private static $has_one = [
        'Order'  => DataObject::class,
        'Client' => DataObject::class,
    ];

    public static function logXero($data)
    {
        self::create($data)->write();
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $this->extend('updateLog');
    }
}
