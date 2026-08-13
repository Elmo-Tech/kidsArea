<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionsSeeder extends Seeder
{
    private const GUARD_NAME = 'web';
    private const SUPER_ADMIN_ROLE = 'super-admin';

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Users
            'users.index',
            'users.show',
            'users.store',
            'users.update',
            'users.destroy',

            // Departments
            'departments.index',
            'departments.show',
            'departments.store',
            'departments.update',
            'departments.destroy',

            // Job Titles
            'job-titles.index',
            'job-titles.show',
            'job-titles.store',
            'job-titles.update',
            'job-titles.destroy',

            // Employees
            'employees.index',
            'employees.show',
            'employees.store',
            'employees.update',
            'employees.destroy',

            // Employee Contracts
            'employee-contracts.index',
            'employee-contracts.show',
            'employee-contracts.store',
            'employee-contracts.update',
            'employee-contracts.destroy',

            // Employee Permissions
            'employee-permissions.index',
            'employee-permissions.show',
            'employee-permissions.store',
            'employee-permissions.update',
            'employee-permissions.destroy',
            'employee-permissions.approve',
            'employee-permissions.reject',

            // Leave Types
            'leave-types.index',
            'leave-types.show',
            'leave-types.store',
            'leave-types.update',
            'leave-types.destroy',

            // Employee Leaves
            'employee-leaves.index',
            'employee-leaves.show',
            'employee-leaves.store',
            'employee-leaves.update',
            'employee-leaves.destroy',
            'employee-leaves.approve',
            'employee-leaves.reject',

            // Employee Attendances
            'employee-attendances.index',
            'employee-attendances.show',
            'employee-attendances.store',
            'employee-attendances.update',
            'employee-attendances.destroy',
            'employee-attendances.sync-day',

            // Leave Payroll Policies
            'leave-payroll-policies.index',
            'leave-payroll-policies.show',
            'leave-payroll-policies.store',
            'leave-payroll-policies.update',
            'leave-payroll-policies.destroy',

            // Payroll Periods
            'payroll-periods.index',
            'payroll-periods.show',
            'payroll-periods.store',
            'payroll-periods.update',
            'payroll-periods.destroy',
            'payroll-periods.generate',
            'payroll-periods.recalculate',
            'payroll-periods.finalize',
            'payroll-periods.summary',

            // Employee Payrolls
            'employee-payrolls.index',
            'employee-payrolls.show',
            'employee-payrolls.payslip',

            // Payroll Adjustments
            'payroll-adjustments.show',
            'payroll-adjustments.store',
            'payroll-adjustments.update',
            'payroll-adjustments.destroy',

            // Payroll Settings
            'payroll-settings.show',
            'payroll-settings.update',

            // Payroll Reports
            'payroll-reports.show',
            'payroll-reports.export',

            // Activity Schedules
            'activity-schedules.index',
            'activity-schedules.show',
            'activity-schedules.store',
            'activity-schedules.update',
            'activity-schedules.destroy',
            'activity-schedules.generate-sessions',

            // Activity Sessions
            'activity-sessions.index',
            'activity-sessions.show',
            'activity-sessions.store',
            'activity-sessions.update',
            'activity-sessions.destroy',
        ];

        foreach ($permissions as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => self::GUARD_NAME,
            ]);
        }

        $superAdminRole = Role::query()->firstOrCreate([
            'name' => self::SUPER_ADMIN_ROLE,
            'guard_name' => self::GUARD_NAME,
        ]);

        $superAdminRole->syncPermissions(
            Permission::query()
                ->where('guard_name', self::GUARD_NAME)
                ->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
