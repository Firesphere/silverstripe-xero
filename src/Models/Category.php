<?php

namespace Firesphere\Xero\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;

/**
 * Class \Firesphere\Xero\Models\ScopeCategory
 *
 * @property string $Name
 * @property string $Value
 * @method ManyManyList|ScopeCategory[] Scopes()
 */
class Category extends DataObject
{
    private static $table_name = 'XeroCategory';

    private static $db = [
        'Name'  => 'Varchar(255)',
        'Value' => 'Varchar(255)',
    ];

    private static $many_many = [
        'Scopes' => [
            'through' => ScopeCategory::class,
            'from'    => 'Category',
            'to'      => 'Scope'
        ]
    ];

    private static $default_records = [
        [
            'Name'  => 'Accounting',
            'Value' => 'accounting',
        ],
        [
            'Name'  => 'Payroll (AU/NZ/UK)',
            'Value' => 'payroll'
        ],
    ];

    public function can($perm, $member = null, $context = [])
    {
        if (ucfirst($perm) === 'View') {
            return parent::can($perm, $member, $context);
        }

        return false;
    }
}
