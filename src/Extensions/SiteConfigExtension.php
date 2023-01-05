<?php

namespace Firesphere\Xero\Extensions;

use Firesphere\Xero\Models\Tenant;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

/**
 * Class \Firesphere\Xero\Extensions\SiteConfigExtension
 *
 * @property bool $MultiTenant
 * @property int $DefaultTenantID
 * @method Tenant DefaultTenant()
 */
class SiteConfigExtension extends DataExtension
{
    private static $db = [
        'MultiTenant' => 'Boolean(false)'
    ];

    private static $has_one = [
        'DefaultTenant' => Tenant::class
    ];

    public function updateCMSFields(FieldList $fields)
    {
        parent::updateCMSFields($fields);
        $fields->removeByName(['MultiTenant']);
        $fields->addFieldsToTab(
            'Root.Xero',
            [
                CheckboxField::create('MultiTenant', 'Multiple Xero tenants'),
            ]
        );
    }
}
