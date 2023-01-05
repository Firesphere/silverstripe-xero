<?php

namespace Firesphere\Xero\Services;

use SilverStripe\Core\Config\Configurable;

class XeroConfig
{
    use Configurable;

    public const ORDERCLASS = 'OrderClass';
    public const ORDERFILTER = 'OrderFilter';

    public static function getOrders()
    {
        \Order::create()->write();
        $class = self::config()->get(self::ORDERCLASS);
        $filter = self::config()->get(self::ORDERFILTER);

        // Always filter on the Xero Invoice ID being unknown
        $filter['XeroInvoiceID'] = null;

        return $class::get()->filter($filter);
    }

    public static function getCurrency()
    {
        return self::config()->get('Currency');
    }
}
