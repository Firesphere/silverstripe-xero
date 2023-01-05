<?php

namespace Firesphere\Xero\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class \Firesphere\Xero\Models\Tenant
 *
 * @property string $Name
 * @property string $XeroName
 * @property string $XeroID
 * @property int $CredentialsID
 * @method Credentials Credentials()
 * @method ManyManyList|ScopeCategory[] TenantScopes()
 */
class Tenant extends DataObject
{
    private static $table_name = 'XeroTenant';

    private static $db = [
        'Name'     => 'Varchar(255)',
        'XeroName' => 'Varchar(255)',
        'XeroID'   => 'Varchar(255)',
    ];

    private static $belongs_to = [
        'Config' => SiteConfig::class,
    ];

    private static $has_one = [
        'Credentials' => Credentials::class
    ];

    private static $many_many = [
        'TenantScopes' => ScopeCategory::class,
    ];

    private static $default_records = [
        [
            'Name' => 'Default Xero tenant'
        ]
    ];

    private static $default_scopes = [
        'openid',
        'email',
        'profile',
        'offline_access'
    ];

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        /** @var self $tenant */
        $tenant = self::get()->first();
        if (!$tenant->TenantScopes()->count()) {
            $scopes = Scope::get()->filter(['Key' => self::$default_scopes]);
            $ScopeCategories = ScopeCategory::get()->filter(['ScopeID' => $scopes->column('ID')]);
            foreach ($ScopeCategories as $scope) {
                $tenant->TenantScopes()->add($scope);
            }
        }
        if (!$tenant->CredentialsID) {
            $tenant->CredentialsID = Credentials::latest($tenant->ID)->ID;
            $tenant->write();
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->dataFieldByName('XeroID')->setReadonly(true)->setDisabled(true);
        $fields->dataFieldByName('XeroName')->setReadonly(true)->setDisabled(true);
        $fields->dataFieldByName('TenantScopes')->setDescription('If the scopes are changed, a new authentication process needs to be started.');

        return $fields;
    }


    public function canCreate($member = null, $context = [])
    {
        return SiteConfig::current_site_config()->MultiTenant || self::get()->count() === 0;
    }
}
