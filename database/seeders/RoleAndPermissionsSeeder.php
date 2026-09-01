<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionsSeeder extends Seeder
{
    private const GUARD_NAME = 'web';

    private const SUPER_ADMIN_ROLE = 'super-admin';
    private const EMPLOYEE_ROLE = 'employee';
    private const CASHIER_ROLE = 'cashier';

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

            // Payroll Payments
            'payroll-payments.index',
            'payroll-payments.store',

            // Activities
            'activities.index',
            'activities.show',
            'activities.store',
            'activities.update',
            'activities.destroy',

            // Activity Pricing Plans
            'activity-pricing-plans.index',
            'activity-pricing-plans.show',
            'activity-pricing-plans.store',
            'activity-pricing-plans.update',
            'activity-pricing-plans.destroy',

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

            // Activity Memberships
            'activity-memberships.index',
            'activity-memberships.show',
            'activity-memberships.store',
            'activity-memberships.update',
            'activity-memberships.destroy',
            'activity-memberships.renew',

            // Activity Attendances
            'activity-attendances.index',
            'activity-attendances.show',
            'activity-attendances.store',
            'activity-attendances.update',
            'activity-attendances.destroy',

            // Activity Usages
            'activity-usages.index',
            'activity-usages.show',
            'activity-usages.start',
            'activity-usages.pause',
            'activity-usages.resume',
            'activity-usages.change-type',
            'activity-usages.close',
            'activity-usages.cancel',

            // Visits
            'visits.index',
            'visits.show',
            'visits.store',
            'visits.close',
            'visits.cancel',
            'visits.checkout',

            // Inventory Items
            'inventory-items.index',
            'inventory-items.show',
            'inventory-items.store',
            'inventory-items.update',
            'inventory-items.destroy',

            // Stock Movements
            'stock-movements.index',
            'stock-movements.show',
            'stock-movements.store',

            // Cafe Products
            'cafe-products.index',
            'cafe-products.show',
            'cafe-products.store',
            'cafe-products.update',
            'cafe-products.destroy',

            // Cafe Orders
            'cafe-orders.index',
            'cafe-orders.show',
            'cafe-orders.store',
            'cafe-orders.update',
            'cafe-orders.confirm',
            'cafe-orders.complete',
            'cafe-orders.cancel',

            // Visit Checkouts
            'visit-checkouts.index',
            'visit-checkouts.show',
            'visit-checkouts.store',
            'visit-checkouts.update',
            'visit-checkouts.finalize',
            'visit-checkouts.cancel',

            // Visit Payments
            'visit-payments.index',
            'visit-payments.show',
            'visit-payments.store',
            'visit-payments.summary',

            // Payments
            'payments.activity-usages.store',
            'payments.activity-usages.summary',
            'payments.cafe-orders.store',
            'payments.cafe-orders.summary',

            // Invoices
            'invoices.show',
            'invoices.visits.show',
            'invoices.activity-usages.show',
            'invoices.cafe-orders.show',
            'invoices.pdf',
            'invoices.download',

            // Activity Session Employee Attendances
            'activity-session-employee-attendances.index',
            'activity-session-employee-attendances.show',
            'activity-session-employee-attendances.store',
            'activity-session-employee-attendances.update',
            'activity-session-employee-attendances.destroy',
            'student-activity-attendances.index',

            // Cash Registers
            'cash-registers.main',
            'cash-registers.index',
            'cash-registers.show',
            'cash-registers.store',
            'cash-registers.update',
            'cash-registers.destroy',

            // Cash Shifts
            'cash-shifts.index',
            'cash-shifts.open',
            'cash-shifts.show',
            'cash-shifts.summary',
            'cash-shifts.close',
            'cash-shifts.manage-all',
            'cash-shifts.employee-open',

            // Cash Transactions
            'cash-transactions.index',
            'cash-transactions.store',

            // Cash
            'cash-summary.show',
            'cash-reports.show',

            // Employee Portal
            'employee-activity-attendances',

            // Cash Transfers
            'cash-transfers.store',

            // Children
            'children.index',
            'children.show',
            'children.store',
            'children.update',
            'children.destroy',

            // Activity Membership Payments
            'activity-membership-payments.store',
            'activity-membership-payments.summary',

            // Expense Categories
            'expense-categories.index',
            'expense-categories.show',
            'expense-categories.store',
            'expense-categories.update',
            'expense-categories.destroy',

            // Expenses
            'expenses.index',
            'expenses.show',
            'expenses.store',
            'expenses.update',

            // Dashboard
            'dashboard.show',

            // Refunds
            'refunds.index',
            'refunds.show',
            'refunds.store',

            // Reports
            'reports.financial',
            'reports.memberships',
            'reports.activities',
            'reports.cafe',
            'reports.attendance',
            'reports.expenses'
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

        $employeeRole = Role::query()->firstOrCreate([
            'name' => self::EMPLOYEE_ROLE,
            'guard_name' => self::GUARD_NAME,
        ]);

        $cashierRole = Role::query()->firstOrCreate([
            'name' => self::CASHIER_ROLE,
            'guard_name' => self::GUARD_NAME,
        ]);

        $superAdminRole->syncPermissions(
            Permission::query()
                ->where('guard_name', self::GUARD_NAME)
                ->get()
        );

        /*
         * Employee
         *
         * /me/profile
         * /me/attendance/check-in
         * /me/attendance/check-out
         * /me/attendance/today
         * /me/activity-sessions
         *
         * الـ APIs دي authenticated APIs ومش محتاجة Dashboard permissions.
         *
         * الصلاحيات دي بنسيبها للعمليات الخاصة بالـ sessions
         * لو احتجناها من dashboard أو API محمية بالـ permission.
         */
        $employeeRole->syncPermissions([
            'activity-session-employee-attendances.show',
            'activity-session-employee-attendances.store',
        ]);

        /*
         * Cashier
         *
         * الكاشير Employee عادي، لكنه يمتلك صلاحيات تشغيل إضافية.
         */
        $cashierRole->syncPermissions([
            // Cash Shift
            'cash-registers.index',
            'cash-registers.show',
            'cash-shifts.employee-open',

            // Today's students
            'employee-activity-attendances',

            // Children attendance
            'activity-attendances.index',
            'activity-attendances.show',
            'activity-attendances.store',
            'activity-attendances.update',
            'student-activity-attendances.index',

            // Cafe
            'cafe-products.index',
            'cafe-products.show',

            'cafe-orders.index',
            'cafe-orders.show',
            'cafe-orders.store',
            'cafe-orders.update',
            'cafe-orders.confirm',
            'cafe-orders.complete',
            'cafe-orders.cancel',

            // Standalone cafe payments
            'payments.cafe-orders.store',
            'payments.cafe-orders.summary',

            // Visits
            'visits.index',
            'visits.show',
            'visits.store',
            'visits.checkout',

            // Activity usages
            'activity-usages.index',
            'activity-usages.show',
            'activity-usages.start',
            'activity-usages.pause',
            'activity-usages.resume',
            'activity-usages.change-type',
            'activity-usages.close',

            // Standalone activity payments
            'payments.activity-usages.store',
            'payments.activity-usages.summary',

            // Invoices
            'invoices.show',
            'invoices.visits.show',
            'invoices.activity-usages.show',
            'invoices.cafe-orders.show',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
