<?php

namespace Firesphere\Xero\Models;

use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class \Firesphere\Xero\Models\Credentials
 *
 * @property string $LongTermToken
 * @method DataList|Tenant[] Tenants()
 */
class Credentials extends DataObject
{
    private static $table_name = 'XeroCredentials';

    private static $db = [
        'LongTermToken' => DBText::class
    ];

    private static $has_many = [
        'Tenants' => Tenant::class,
    ];

    /**
     * @return static
     * @throws \SilverStripe\ORM\ValidationException
     */
    public static function latest($forTenant = 0): self
    {
        $credentialsObject = self::get();
        if ($forTenant) {
            $credentialsObject = $credentialsObject->filter(['Tenants.ID' => $forTenant]);
        }
        $credentialsObject = $credentialsObject->first();

        if (!$credentialsObject) {
            $credentialsObject = self::create();
            $credentialsObject->write();
        }

        return $credentialsObject;
    }

    public function canView($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canCreate($member = null, $context = [])
    {
        return SiteConfig::current_site_config()->MultiTenant || self::get()->count() === 0;
    }

    public function canEdit($member = null)
    {
        return false;
    }
}
