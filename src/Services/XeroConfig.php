<?php

namespace Firesphere\Xero\Services;

use SilverStripe\Core\Config\Configurable;

/**
 *
 */
class XeroConfig
{
    use Configurable;

    /**
     *
     */
    public const ORDERCLASS = 'OrderClass';
    /**
     *
     */
    public const ORDERFILTER = 'OrderFilter';

    /**
     * @return mixed
     * @throws \SilverStripe\ORM\ValidationException
     */
    public static function getOrders()
    {
        $id = \Order::create(['ClientID' => 2])->write();
        \OrderItem::create([
            'Quantity' => 1,
            'Price'    => 1,
            'Item'     => 'Bike',
            'OrderID'  => $id
        ])->write();
        $class = self::config()->get(self::ORDERCLASS);
        $filter = self::config()->get(self::ORDERFILTER);

        // Always filter on the Xero Invoice ID being unknown
        $filter['XeroID'] = [null, '00000000-0000-0000-0000-00000000', ''];

        return $class::get()->filter($filter);
    }

    /**
     * @return mixed
     */
    public static function getCurrency()
    {
        return self::config()->get('Currency');
    }

    /**
     * @return mixed
     */
    public static function getShadowCopy()
    {
        return self::config()->get('ShadowCopy');
    }
}
