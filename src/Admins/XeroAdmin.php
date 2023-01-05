<?php

namespace Firesphere\Xero\Admins;

use Firesphere\Xero\Models\Tenant;
use SilverStripe\Admin\ModelAdmin;

/**
 * Class \Firesphere\Xero\Admins\XeroAdmin
 *
 */
class XeroAdmin extends ModelAdmin
{
    private static $menu_title = 'Xero Admin';

    private static $url_segment = 'xero-admin';

    private static $managed_models = [
        Tenant::class,
    ];
}
