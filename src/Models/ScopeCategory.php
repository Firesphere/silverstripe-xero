<?php

namespace Firesphere\Xero\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;

/**
 * Class \Firesphere\Xero\Models\ScopeCategory
 *
 * @property int $ScopeID
 * @property int $CategoryID
 * @method Scope Scope()
 * @method Category Category()
 * @method ManyManyList|Tenant[] Tenants()
 */
class ScopeCategory extends DataObject
{
    private static $table_name = 'XeroScopeCategory';

    private static $has_one = [
        'Scope'    => Scope::class,
        'Category' => Category::class
    ];

    private static $belongs_many_many = [
        'Tenants' => Tenant::class
    ];

    private static $scopeMap = [
        'accounting' => [
            'transactions',
            'reports',
            'journals',
            'settings',
            'contacts',
            'attachments',
            'budgets',
            'reports.tenninetynine',
        ],
        'payroll'    => [
            'employees',
            'payruns',
            'payslip',
            'timesheets',
            'settings',
        ],
        'None'       => [
            'openid',
            'email',
            'profile',
            'offline_access',
            'files',
            'assets',
            'projects',
        ]
    ];

    private static $summary_fields = [
        'Category.Name',
        'Scope.Name'
    ];

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        $categories = Category::get()->map('Value', 'ID');
        $scopes = Scope::get()->map('Key', 'ID');
        foreach ($categories as $category => $id) {
            if (!self::get()->filter(['CategoryID' => $id])->exists()) {
                foreach (self::$scopeMap[$category] as $scope) {
                    self::create([
                        'CategoryID' => $id,
                        'ScopeID'    => $scopes[$scope]
                    ])->write();
                }
            }
        }

        foreach (self::$scopeMap['None'] as $scope) {
            if (!self::get()->filter(['ScopeID' => $scopes[$scope]])->exists()) {
                self::create(['ScopeID' => $scopes[$scope]])->write();
            }
        }
    }

    public function can($perm, $member = null, $context = [])
    {
        return false;
    }

    /**
     * @return string
     */
    public function getScopeForAuth()
    {
        $scope = [];
        if ($this->CategoryID) {
            $scope[] = $this->Category()->Value;
        }
        $scope[] = $this->Scope()->Key;
        if ($this->Scope()->ReadOnly) {
            $scope[] = 'read';
        }

        return implode('.', $scope);
    }
}
