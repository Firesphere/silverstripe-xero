<?php

namespace Firesphere\Xero\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;

/**
 * Class \Firesphere\Xero\Models\Scope
 *
 * @property string $Name
 * @property string $Key
 * @property bool $ReadOnly
 * @method ManyManyList|ScopeCategory[] ScopeCategories()
 */
class Scope extends DataObject
{
    private static $table_name = 'XeroScope';

    private static $db = [
        'Name'     => 'Varchar(255)',
        'Key'      => 'Varchar(255)',
        'ReadOnly' => 'Boolean(false)'
    ];

    private static $belongs_many_many = [
        'ScopeCategories' => [
            'through' => ScopeCategory::class,
            'from'    => 'Scope',
            'to'      => 'Category'
        ],
    ];

    private static $default_records = [
        [
            'Name'     => 'Open ID',
            'Key'      => 'openid',
            'ReadOnly' => false
        ],
        [
            'Name'     => 'Email',
            'Key'      => 'email',
            'ReadOnly' => false
        ],
        [
            'Name'     => 'Profile',
            'Key'      => 'profile',
            'ReadOnly' => false
        ],
        [
            'Name'     => 'Offline Access',
            'Key'      => 'offline_access',
            'ReadOnly' => false
        ],
        // Accounting
        [
            'Name'     => 'Transactions',
            'Key'      => 'transactions',
            'ReadOnly' => false
        ],
        [
            'Name'     => 'Reports',
            'Key'      => 'reports',
            'ReadOnly' => true
        ],
        [
            'Name'     => 'Journals',
            'Key'      => 'journals',
            'ReadOnly' => true
        ],
        [
            'Name'     => 'Budgets',
            'Key'      => 'budgets',
            'ReadOnly' => true
        ],
        [
            'Name'     => '1099 reports',
            'Key'      => 'reports.tenninetynine',
            'ReadOnly' => true
        ],
        [
            'Name'     => 'Contacts',
            'Key'      => 'contacts',
            'ReadOnly' => false
        ],
        [
            'Name'     => 'Attachments',
            'Key'      => 'attachments',
            'ReadOnly' => false
        ],
        // Accounting & Payroll
        [
            'Name'     => 'Settings',
            'Key'      => 'settings',
            'ReadOnly' => false
        ],
        // Payroll
        [
            'Name'     => 'Employees',
            'Key'      => 'employees',
            'ReadOnly' => false
        ],
        [
            'Name'     => 'Pay runs',
            'Key'      => 'payruns',
            'ReadOnly' => false
        ],
        [
            'Name'     => 'Pay slips',
            'Key'      => 'payslip',
            'ReadOnly' => false
        ],
        [
            'Name'     => 'Timesheets',
            'Key'      => 'timesheets',
            'ReadOnly' => false
        ],
        // Files assets and projects
        [
            'Name'     => 'Files',
            'Key'      => 'files',
            'ReadOnly' => false
        ],
        [
            'Name'     => 'Assets',
            'Key'      => 'assets',
            'ReadOnly' => false
        ],
        [
            'Name'     => 'Projects',
            'Key'      => 'projects',
            'ReadOnly' => false
        ],
    ];
}
