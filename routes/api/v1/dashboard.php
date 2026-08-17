<?php

/*use App\Http\Controllers\Api\V1\Admin\ActivityController;
use App\Http\Controllers\Api\V1\Admin\ActivityPricingPlanController;
use App\Http\Controllers\Api\V1\Admin\DepartmentController;
use App\Http\Controllers\Api\V1\Admin\EmployeeContractController;
use App\Http\Controllers\Api\V1\Admin\EmployeeController;
use App\Http\Controllers\Api\V1\Admin\JobTitleController;
use App\Http\Controllers\Api\V1\Admin\EmployeePermissionController;
use App\Http\Controllers\Api\V1\Admin\LeaveTypeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\EmployeeLeaveController;
use App\Http\Controllers\Api\V1\Admin\EmployeeAttendanceController;
use App\Http\Controllers\Api\V1\Admin\EmployeePayrollController;
use App\Http\Controllers\Api\V1\Admin\ExportPayrollReportController;
use App\Http\Controllers\Api\V1\Admin\FinalizePayrollController;
use App\Http\Controllers\Api\V1\Admin\GeneratePayrollController;
use App\Http\Controllers\Api\V1\Admin\LeavePayrollPolicyController;
use App\Http\Controllers\Api\V1\Admin\PayrollAdjustmentController;
use App\Http\Controllers\Api\V1\Admin\PayrollPeriodController;
use App\Http\Controllers\Api\V1\Admin\PayrollSummaryController;
use App\Http\Controllers\Api\V1\Admin\RecalculatePayrollController;
use App\Http\Controllers\Api\V1\Admin\SyncEmployeeAttendanceController;
use App\Http\Controllers\Api\V1\Admin\PayrollSettingController;
use App\Http\Controllers\Api\V1\Admin\PayslipController;
use App\Http\Controllers\Api\V1\Admin\PayrollReportController;
use App\Http\Controllers\Api\V1\Admin\PayrollPaymentController;
use App\Http\Controllers\Api\V1\Admin\ActivityScheduleController;
use App\Http\Controllers\Api\V1\Admin\ActivitySessionController;
use App\Http\Controllers\Api\V1\Admin\GenerateActivitySessionsController;
use App\Http\Controllers\Api\V1\Admin\ActivityMembershipController;
use App\Http\Controllers\Api\V1\Admin\ActivityAttendanceController;
use App\Http\Controllers\Api\V1\Admin\ActivityUsageController;
use App\Http\Controllers\Api\V1\Admin\VisitController;
use App\Http\Controllers\Api\V1\Admin\InventoryItemController;
use App\Http\Controllers\Api\V1\Admin\StockMovementController;
use App\Http\Controllers\Api\V1\Admin\CafeProductController;

Route::prefix('/dashboard')->middleware(['locale', 'auth:sanctum'])
    ->group(function (): void {

        Route::prefix('/departments')->group(function (): void {
            Route::get('/', [DepartmentController::class, 'index']);
            Route::post('/', [DepartmentController::class, 'store']);
            Route::get('/{department}', [DepartmentController::class, 'show']);
            Route::put('/{department}', [DepartmentController::class, 'update']);
            Route::delete('/{department}', [DepartmentController::class, 'destroy']);
        });

        Route::prefix('/job-titles')->group(function(): void {
            Route::get('/', [JobTitleController::class, 'index']);
            Route::post('/', [JobTitleController::class, 'store']);
            Route::get('/{jobTitle}', [JobTitleController::class, 'edit']);
            Route::put('/{jobTitle}', [JobTitleController::class, 'update']);
            Route::delete('/{jobTitle}', [JobTitleController::class, 'destroy']);
        });

        // Employee Routes
        Route::prefix('/employees')->group(function(): void {
            Route::get('/', [EmployeeController::class, 'index']);
            Route::post('/', [EmployeeController::class, 'store']);
            Route::get('/{employee}', [EmployeeController::class, 'show']);
            Route::put('/{employee}', [EmployeeController::class, 'update']);
            Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
        });

        // Employee Contracts Routes
        Route::prefix('/employees/{employee}/contracts')->group(function(): void {
            Route::get('/', [EmployeeContractController::class, 'index']);
            Route::post('/', [EmployeeContractController::class, 'store']);
        });

        Route::prefix('/contracts/{contract}')->group(function(): void {
            Route::get('/', [EmployeeContractController::class, 'show']);
            Route::put('/', [EmployeeContractController::class, 'update']);
            Route::delete('/', [EmployeeContractController::class, 'destroy']);
        });


        // Employee Permissions Routes
        Route::prefix('employee-permissions')->group(function () {
            Route::get('/', [EmployeePermissionController::class, 'index']);
            Route::post('/', [EmployeePermissionController::class, 'store']);

            Route::get('/{permission}', [EmployeePermissionController::class, 'show']);

            Route::put('/{permission}', [EmployeePermissionController::class, 'update']);

            Route::delete('/{permission}', [EmployeePermissionController::class, 'destroy']);

            Route::patch('/{permission}/approve', [
                EmployeePermissionController::class,
                'approve',
            ]);

            Route::patch('/{permission}/reject', [
                EmployeePermissionController::class,
                'reject',
            ]);
        });


        // leave types routes
        Route::prefix('/leave-types')->group(function (): void {
            Route::get('/', [LeaveTypeController::class, 'index']);
            Route::post('/', [LeaveTypeController::class, 'store']);
            Route::get('/{leaveType}', [LeaveTypeController::class, 'show']);
            Route::put('/{leaveType}', [LeaveTypeController::class, 'update']);
            Route::delete('/{leaveType}', [LeaveTypeController::class, 'destroy']);
        });

        // employee leaves routes
        Route::prefix('/employee-leaves')->group(function (): void {
            Route::get('/', [EmployeeLeaveController::class, 'index']);
            Route::post('/', [EmployeeLeaveController::class, 'store']);
            Route::get('/{leave}', [EmployeeLeaveController::class, 'show']);
            Route::put('/{leave}', [EmployeeLeaveController::class, 'update']);
            Route::delete('/{leave}', [EmployeeLeaveController::class, 'destroy']);
            Route::patch('/{leave}/approve', [EmployeeLeaveController::class, 'approve']);
            Route::patch('/{leave}/reject', [EmployeeLeaveController::class, 'reject']);
        });

        // employee attendances routes
        Route::prefix('/employee-attendances')->group(function (): void {
            Route::post('/sync-day', SyncEmployeeAttendanceController::class);
            Route::get('/', [EmployeeAttendanceController::class, 'index']);
            Route::post('/', [EmployeeAttendanceController::class, 'store']);
            Route::get('/{attendance}', [EmployeeAttendanceController::class, 'show']);
            Route::put('/{attendance}', [EmployeeAttendanceController::class, 'update']);
            Route::delete('/{attendance}', [EmployeeAttendanceController::class, 'destroy']);
        });


        Route::prefix('leave-payroll-policies')->group(function (): void {
            Route::get('/', [LeavePayrollPolicyController::class, 'index']);
            Route::post('/', [LeavePayrollPolicyController::class, 'store']);
            Route::get('/{policy}', [LeavePayrollPolicyController::class,'show',]);
            Route::put('/{policy}', [LeavePayrollPolicyController::class,'update']);
            Route::delete('/{policy}', [LeavePayrollPolicyController::class,'destroy']);
        });


    Route::prefix('payroll-periods')->group(function (): void {

        // Period listing / create
        Route::get('/', [PayrollPeriodController::class,'index']);
        Route::post('/', [PayrollPeriodController::class,'store']);

        // Period actions
        Route::post('/{period}/generate',GeneratePayrollController::class);
        Route::post('/{period}/recalculate',RecalculatePayrollController::class);
        Route::post('/{period}/finalize',FinalizePayrollController::class);
        Route::get('/{period}/summary',PayrollSummaryController::class);

        // Employee payrolls
        Route::get('/{period}/employees',[EmployeePayrollController::class, 'index']);
        Route::get('/{period}/employees/{employeePayroll}',[EmployeePayrollController::class, 'show']);

        // Payroll adjustments
        Route::post('/{period}/employees/{employeePayroll}/adjustments', [PayrollAdjustmentController::class, 'store']);
        Route::get('/{period}/employees/{employeePayroll}/adjustments/{adjustment}',[PayrollAdjustmentController::class, 'show']);
        Route::put('/{period}/employees/{employeePayroll}/adjustments/{adjustment}',[PayrollAdjustmentController::class, 'update']);
        Route::delete(
            '/{period}/employees/{employeePayroll}/adjustments/{adjustment}',
            [PayrollAdjustmentController::class, 'destroy']
        );

        // Period CRUD by ID
        Route::get('/{period}', [
            PayrollPeriodController::class,
            'show'
        ]);

        Route::put('/{period}', [
            PayrollPeriodController::class,
            'update'
        ]);

        Route::delete('/{period}', [
            PayrollPeriodController::class,
            'destroy'
        ]);

        Route::get(
            '/{period}/employees/{employeePayroll}/payslip',
            PayslipController::class
        );
    });

    Route::prefix('payroll-settings')->group(function (): void {
        Route::get('/', [
            PayrollSettingController::class,
            'show',
        ]);

        Route::patch('/', [
            PayrollSettingController::class,
            'update',
        ]);
    });

    Route::prefix('reports')->group(function (): void {
        Route::get(
            '/payroll/{period}',
            PayrollReportController::class
        );

         Route::get(
            '/payroll/{period}/export',
            ExportPayrollReportController::class
        );
    });

    Route::prefix('employee-payrolls')->group(function (): void {

        Route::get(
            '/{employeePayroll}/payments',
            [PayrollPaymentController::class, 'index']
        );

        Route::post(
            '/{employeePayroll}/payments',
            [PayrollPaymentController::class, 'store']
        );
    });

    Route::prefix('activities')->group(function (): void {

    Route::get('/', [
            ActivityController::class,
            'index'
        ])->middleware(
            'permission:activities.index'
        );

        Route::post('/', [
            ActivityController::class,
            'store'
        ])->middleware(
            'permission:activities.store'
        );

        Route::get('/{activity}', [
            ActivityController::class,
            'show'
        ])->middleware(
            'permission:activities.show'
        );

        Route::put('/{activity}', [
            ActivityController::class,
            'update'
        ])->middleware(
            'permission:activities.update'
        );

        Route::delete('/{activity}', [
            ActivityController::class,
            'destroy'
        ])->middleware(
            'permission:activities.destroy'
        );
    });

    Route::prefix('activity-pricing-plans')->group(function (): void {

    Route::get('/', [
        ActivityPricingPlanController::class,
        'index'
    ])->middleware(
        'permission:activity-pricing-plans.index'
    );

    Route::post('/', [
        ActivityPricingPlanController::class,
        'store'
    ])->middleware(
        'permission:activity-pricing-plans.store'
    );

    Route::get('/{pricingPlan}', [
        ActivityPricingPlanController::class,
        'show'
    ])->middleware(
        'permission:activity-pricing-plans.show'
    );

    Route::put('/{pricingPlan}', [
        ActivityPricingPlanController::class,
        'update'
    ])->middleware(
        'permission:activity-pricing-plans.update'
    );

    Route::delete('/{pricingPlan}', [
        ActivityPricingPlanController::class,
        'destroy'
    ])->middleware(
        'permission:activity-pricing-plans.destroy'
    );
});

Route::prefix('activity-schedules')->group(function (): void {

    Route::get('/', [
        ActivityScheduleController::class,
        'index'
    ])->middleware(
        'permission:activity-schedules.index'
    );

    Route::post('/', [
        ActivityScheduleController::class,
        'store'
    ])->middleware(
        'permission:activity-schedules.store'
    );

    Route::post(
        '/{schedule}/generate-sessions',
        GenerateActivitySessionsController::class
    )->middleware(
        'permission:activity-schedules.generate-sessions'
    );

    Route::get('/{schedule}', [
        ActivityScheduleController::class,
        'show'
    ])->middleware(
        'permission:activity-schedules.show'
    );

    Route::put('/{schedule}', [
        ActivityScheduleController::class,
        'update'
    ])->middleware(
        'permission:activity-schedules.update'
    );

    Route::delete('/{schedule}', [
        ActivityScheduleController::class,
        'destroy'
    ])->middleware(
        'permission:activity-schedules.destroy'
    );
});

Route::prefix('activity-sessions')->group(function (): void {

    Route::get('/', [
        ActivitySessionController::class,
        'index'
    ])->middleware(
        'permission:activity-sessions.index'
    );

    Route::post('/', [
        ActivitySessionController::class,
        'store'
    ])->middleware(
        'permission:activity-sessions.store'
    );

    Route::get('/{session}', [
        ActivitySessionController::class,
        'show'
    ])->middleware(
        'permission:activity-sessions.show'
    );

    Route::put('/{session}', [
        ActivitySessionController::class,
        'update'
    ])->middleware(
        'permission:activity-sessions.update'
    );

    Route::delete('/{session}', [
        ActivitySessionController::class,
        'destroy'
    ])->middleware(
        'permission:activity-sessions.destroy'
    );
});

Route::prefix('activity-memberships')->group(function (): void {

    Route::get('/', [
        ActivityMembershipController::class,
        'index'
    ])->middleware(
        'permission:activity-memberships.index'
    );

    Route::post('/', [
        ActivityMembershipController::class,
        'store'
    ])->middleware(
        'permission:activity-memberships.store'
    );

    Route::get('/{membership}', [
        ActivityMembershipController::class,
        'show'
    ])->middleware(
        'permission:activity-memberships.show'
    );

    Route::put('/{membership}', [
        ActivityMembershipController::class,
        'update'
    ])->middleware(
        'permission:activity-memberships.update'
    );

    Route::delete('/{membership}', [
        ActivityMembershipController::class,
        'destroy'
    ])->middleware(
        'permission:activity-memberships.destroy'
    );
});

Route::prefix('activity-attendances')->group(function (): void {

    Route::get('/', [
        ActivityAttendanceController::class,
        'index'
    ])->middleware(
        'permission:activity-attendances.index'
    );

    Route::post('/', [
        ActivityAttendanceController::class,
        'store'
    ])->middleware(
        'permission:activity-attendances.store'
    );

    Route::get('/{attendance}', [
        ActivityAttendanceController::class,
        'show'
    ])->middleware(
        'permission:activity-attendances.show'
    );

    Route::put('/{attendance}', [
        ActivityAttendanceController::class,
        'update'
    ])->middleware(
        'permission:activity-attendances.update'
    );

    Route::delete('/{attendance}', [
        ActivityAttendanceController::class,
        'destroy'
    ])->middleware(
        'permission:activity-attendances.destroy'
    );
});


    Route::prefix('activity-usages')->group(function (): void {
        Route::get('/', [ActivityUsageController::class, 'index'])->middleware('permission:activity-usages.index');
        Route::post('/start', [ActivityUsageController::class, 'start'])->middleware('permission:activity-usages.start');
        Route::get('/{usage}', [ActivityUsageController::class, 'show'])->middleware('permission:activity-usages.show');
        Route::post('/{usage}/pause', [ActivityUsageController::class, 'pause'])->middleware('permission:activity-usages.pause');
        Route::post('/{usage}/resume', [ActivityUsageController::class, 'resume'])->middleware('permission:activity-usages.resume');
        Route::patch('/{usage}/change-type', [ActivityUsageController::class, 'changeType'])->middleware('permission:activity-usages.change-type');
        Route::post('/{usage}/close', [ActivityUsageController::class, 'close'])->middleware('permission:activity-usages.close');
        Route::post('/{usage}/cancel', [ActivityUsageController::class, 'cancel'])->middleware('permission:activity-usages.cancel');
    });

    Route::prefix('visits')->group(function (): void {
        Route::get('/', [VisitController::class, 'index'])->middleware('permission:visits.index');
        Route::post('/', [VisitController::class, 'store'])->middleware('permission:visits.store');
        Route::get('/{visit}', [VisitController::class, 'show'])->middleware('permission:visits.show');
        Route::post('/{visit}/close', [VisitController::class, 'close'])->middleware('permission:visits.close');
        Route::post('/{visit}/cancel', [VisitController::class, 'cancel'])->middleware('permission:visits.cancel');
    });

    Route::prefix('inventory-items')->group(function (): void {
        Route::get('/', [InventoryItemController::class, 'index'])->middleware('permission:inventory-items.index');
        Route::post('/', [InventoryItemController::class, 'store'])->middleware('permission:inventory-items.store');
        Route::get('/{inventoryItem}', [InventoryItemController::class, 'show'])->middleware('permission:inventory-items.show');
        Route::put('/{inventoryItem}', [InventoryItemController::class, 'update'])->middleware('permission:inventory-items.update');
        Route::delete('/{inventoryItem}', [InventoryItemController::class, 'destroy'])->middleware('permission:inventory-items.destroy');
    });

    Route::prefix('stock-movements')->group(function (): void {
        Route::get('/', [StockMovementController::class, 'index'])->middleware('permission:stock-movements.index');
        Route::post('/', [StockMovementController::class, 'store'])->middleware('permission:stock-movements.store');
        Route::get('/{stockMovement}', [StockMovementController::class, 'show'])->middleware('permission:stock-movements.show');
    });

    Route::prefix('cafe-products')->group(function (): void {
        Route::get('/', [CafeProductController::class, 'index'])->middleware('permission:cafe-products.index');
        Route::post('/', [CafeProductController::class, 'store'])->middleware('permission:cafe-products.store');
        Route::get('/{cafeProduct}', [CafeProductController::class, 'show'])->middleware('permission:cafe-products.show');
        Route::put('/{cafeProduct}', [CafeProductController::class, 'update'])->middleware('permission:cafe-products.update');
        Route::delete('/{cafeProduct}', [CafeProductController::class, 'destroy'])->middleware('permission:cafe-products.destroy');
    });
});
*/

use App\Http\Controllers\Api\V1\Admin\ActivityAttendanceController;
use App\Http\Controllers\Api\V1\Admin\ActivityController;
use App\Http\Controllers\Api\V1\Admin\ActivityMembershipController;
use App\Http\Controllers\Api\V1\Admin\ActivityPricingPlanController;
use App\Http\Controllers\Api\V1\Admin\ActivityScheduleController;
use App\Http\Controllers\Api\V1\Admin\ActivitySessionController;
use App\Http\Controllers\Api\V1\Admin\ActivityUsageController;
use App\Http\Controllers\Api\V1\Admin\CafeProductController;
use App\Http\Controllers\Api\V1\Admin\DepartmentController;
use App\Http\Controllers\Api\V1\Admin\EmployeeAttendanceController;
use App\Http\Controllers\Api\V1\Admin\EmployeeContractController;
use App\Http\Controllers\Api\V1\Admin\EmployeeController;
use App\Http\Controllers\Api\V1\Admin\EmployeeLeaveController;
use App\Http\Controllers\Api\V1\Admin\EmployeePayrollController;
use App\Http\Controllers\Api\V1\Admin\EmployeePermissionController;
use App\Http\Controllers\Api\V1\Admin\ExportPayrollReportController;
use App\Http\Controllers\Api\V1\Admin\FinalizePayrollController;
use App\Http\Controllers\Api\V1\Admin\GenerateActivitySessionsController;
use App\Http\Controllers\Api\V1\Admin\GeneratePayrollController;
use App\Http\Controllers\Api\V1\Admin\InventoryItemController;
use App\Http\Controllers\Api\V1\Admin\JobTitleController;
use App\Http\Controllers\Api\V1\Admin\LeavePayrollPolicyController;
use App\Http\Controllers\Api\V1\Admin\LeaveTypeController;
use App\Http\Controllers\Api\V1\Admin\PayrollAdjustmentController;
use App\Http\Controllers\Api\V1\Admin\PayrollPaymentController;
use App\Http\Controllers\Api\V1\Admin\PayrollPeriodController;
use App\Http\Controllers\Api\V1\Admin\PayrollReportController;
use App\Http\Controllers\Api\V1\Admin\PayrollSettingController;
use App\Http\Controllers\Api\V1\Admin\PayrollSummaryController;
use App\Http\Controllers\Api\V1\Admin\PayslipController;
use App\Http\Controllers\Api\V1\Admin\RecalculatePayrollController;
use App\Http\Controllers\Api\V1\Admin\StockMovementController;
use App\Http\Controllers\Api\V1\Admin\SyncEmployeeAttendanceController;
use App\Http\Controllers\Api\V1\Admin\VisitController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->middleware(['locale', 'auth:sanctum'])->group(function (): void {
    Route::prefix('departments')->group(function (): void {
        Route::get('/', [DepartmentController::class, 'index'])->middleware('permission:departments.index');
        Route::post('/', [DepartmentController::class, 'store'])->middleware('permission:departments.store');
        Route::get('/{department}', [DepartmentController::class, 'show'])->middleware('permission:departments.show');
        Route::put('/{department}', [DepartmentController::class, 'update'])->middleware('permission:departments.update');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->middleware('permission:departments.destroy');
    });

    Route::prefix('job-titles')->group(function (): void {
        Route::get('/', [JobTitleController::class, 'index'])->middleware('permission:job-titles.index');
        Route::post('/', [JobTitleController::class, 'store'])->middleware('permission:job-titles.store');
        Route::get('/{jobTitle}', [JobTitleController::class, 'show'])->middleware('permission:job-titles.show');
        Route::put('/{jobTitle}', [JobTitleController::class, 'update'])->middleware('permission:job-titles.update');
        Route::delete('/{jobTitle}', [JobTitleController::class, 'destroy'])->middleware('permission:job-titles.destroy');
    });

    Route::prefix('employees')->group(function (): void {
        Route::get('/', [EmployeeController::class, 'index'])->middleware('permission:employees.index');
        Route::post('/', [EmployeeController::class, 'store'])->middleware('permission:employees.store');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->middleware('permission:employees.show');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:employees.destroy');
    });

    Route::prefix('employees/{employee}/contracts')->group(function (): void {
        Route::get('/', [EmployeeContractController::class, 'index'])->middleware('permission:employee-contracts.index');
        Route::post('/', [EmployeeContractController::class, 'store'])->middleware('permission:employee-contracts.store');
    });

    Route::prefix('contracts/{contract}')->group(function (): void {
        Route::get('/', [EmployeeContractController::class, 'show'])->middleware('permission:employee-contracts.show');
        Route::put('/', [EmployeeContractController::class, 'update'])->middleware('permission:employee-contracts.update');
        Route::delete('/', [EmployeeContractController::class, 'destroy'])->middleware('permission:employee-contracts.destroy');
    });

    Route::prefix('employee-permissions')->group(function (): void {
        Route::get('/', [EmployeePermissionController::class, 'index'])->middleware('permission:employee-permissions.index');
        Route::post('/', [EmployeePermissionController::class, 'store'])->middleware('permission:employee-permissions.store');
        Route::get('/{permission}', [EmployeePermissionController::class, 'show'])->middleware('permission:employee-permissions.show');
        Route::put('/{permission}', [EmployeePermissionController::class, 'update'])->middleware('permission:employee-permissions.update');
        Route::delete('/{permission}', [EmployeePermissionController::class, 'destroy'])->middleware('permission:employee-permissions.destroy');
        Route::patch('/{permission}/approve', [EmployeePermissionController::class, 'approve'])->middleware('permission:employee-permissions.approve');
        Route::patch('/{permission}/reject', [EmployeePermissionController::class, 'reject'])->middleware('permission:employee-permissions.reject');
    });

    Route::prefix('leave-types')->group(function (): void {
        Route::get('/', [LeaveTypeController::class, 'index'])->middleware('permission:leave-types.index');
        Route::post('/', [LeaveTypeController::class, 'store'])->middleware('permission:leave-types.store');
        Route::get('/{leaveType}', [LeaveTypeController::class, 'show'])->middleware('permission:leave-types.show');
        Route::put('/{leaveType}', [LeaveTypeController::class, 'update'])->middleware('permission:leave-types.update');
        Route::delete('/{leaveType}', [LeaveTypeController::class, 'destroy'])->middleware('permission:leave-types.destroy');
    });

    Route::prefix('employee-leaves')->group(function (): void {
        Route::get('/', [EmployeeLeaveController::class, 'index'])->middleware('permission:employee-leaves.index');
        Route::post('/', [EmployeeLeaveController::class, 'store'])->middleware('permission:employee-leaves.store');
        Route::get('/{leave}', [EmployeeLeaveController::class, 'show'])->middleware('permission:employee-leaves.show');
        Route::put('/{leave}', [EmployeeLeaveController::class, 'update'])->middleware('permission:employee-leaves.update');
        Route::delete('/{leave}', [EmployeeLeaveController::class, 'destroy'])->middleware('permission:employee-leaves.destroy');
        Route::patch('/{leave}/approve', [EmployeeLeaveController::class, 'approve'])->middleware('permission:employee-leaves.approve');
        Route::patch('/{leave}/reject', [EmployeeLeaveController::class, 'reject'])->middleware('permission:employee-leaves.reject');
    });

    Route::prefix('employee-attendances')->group(function (): void {
        Route::post('/sync-day', SyncEmployeeAttendanceController::class)->middleware('permission:employee-attendances.sync-day');
        Route::get('/', [EmployeeAttendanceController::class, 'index'])->middleware('permission:employee-attendances.index');
        Route::post('/', [EmployeeAttendanceController::class, 'store'])->middleware('permission:employee-attendances.store');
        Route::get('/{attendance}', [EmployeeAttendanceController::class, 'show'])->middleware('permission:employee-attendances.show');
        Route::put('/{attendance}', [EmployeeAttendanceController::class, 'update'])->middleware('permission:employee-attendances.update');
        Route::delete('/{attendance}', [EmployeeAttendanceController::class, 'destroy'])->middleware('permission:employee-attendances.destroy');
    });

    Route::prefix('leave-payroll-policies')->group(function (): void {
        Route::get('/', [LeavePayrollPolicyController::class, 'index'])->middleware('permission:leave-payroll-policies.index');
        Route::post('/', [LeavePayrollPolicyController::class, 'store'])->middleware('permission:leave-payroll-policies.store');
        Route::get('/{policy}', [LeavePayrollPolicyController::class, 'show'])->middleware('permission:leave-payroll-policies.show');
        Route::put('/{policy}', [LeavePayrollPolicyController::class, 'update'])->middleware('permission:leave-payroll-policies.update');
        Route::delete('/{policy}', [LeavePayrollPolicyController::class, 'destroy'])->middleware('permission:leave-payroll-policies.destroy');
    });

    Route::prefix('payroll-periods')->group(function (): void {
        Route::get('/', [PayrollPeriodController::class, 'index'])->middleware('permission:payroll-periods.index');
        Route::post('/', [PayrollPeriodController::class, 'store'])->middleware('permission:payroll-periods.store');
        Route::post('/{period}/generate', GeneratePayrollController::class)->middleware('permission:payroll-periods.generate');
        Route::post('/{period}/recalculate', RecalculatePayrollController::class)->middleware('permission:payroll-periods.recalculate');
        Route::post('/{period}/finalize', FinalizePayrollController::class)->middleware('permission:payroll-periods.finalize');
        Route::get('/{period}/summary', PayrollSummaryController::class)->middleware('permission:payroll-periods.summary');
        Route::get('/{period}/employees', [EmployeePayrollController::class, 'index'])->middleware('permission:employee-payrolls.index');
        Route::get('/{period}/employees/{employeePayroll}', [EmployeePayrollController::class, 'show'])->middleware('permission:employee-payrolls.show');
        Route::post('/{period}/employees/{employeePayroll}/adjustments', [PayrollAdjustmentController::class, 'store'])->middleware('permission:payroll-adjustments.store');
        Route::get('/{period}/employees/{employeePayroll}/adjustments/{adjustment}', [PayrollAdjustmentController::class, 'show'])->middleware('permission:payroll-adjustments.show');
        Route::put('/{period}/employees/{employeePayroll}/adjustments/{adjustment}', [PayrollAdjustmentController::class, 'update'])->middleware('permission:payroll-adjustments.update');
        Route::delete('/{period}/employees/{employeePayroll}/adjustments/{adjustment}', [PayrollAdjustmentController::class, 'destroy'])->middleware('permission:payroll-adjustments.destroy');
        Route::get('/{period}', [PayrollPeriodController::class, 'show'])->middleware('permission:payroll-periods.show');
        Route::put('/{period}', [PayrollPeriodController::class, 'update'])->middleware('permission:payroll-periods.update');
        Route::delete('/{period}', [PayrollPeriodController::class, 'destroy'])->middleware('permission:payroll-periods.destroy');
        Route::get('/{period}/employees/{employeePayroll}/payslip', PayslipController::class)->middleware('permission:employee-payrolls.payslip');
    });

    Route::prefix('payroll-settings')->group(function (): void {
        Route::get('/', [PayrollSettingController::class, 'show'])->middleware('permission:payroll-settings.show');
        Route::patch('/', [PayrollSettingController::class, 'update'])->middleware('permission:payroll-settings.update');
    });

    Route::prefix('reports')->group(function (): void {
        Route::get('/payroll/{period}', PayrollReportController::class)->middleware('permission:payroll-reports.show');
        Route::get('/payroll/{period}/export', ExportPayrollReportController::class)->middleware('permission:payroll-reports.export');
    });

    Route::prefix('employee-payrolls')->group(function (): void {
        Route::get('/{employeePayroll}/payments', [PayrollPaymentController::class, 'index'])->middleware('permission:payroll-payments.index');
        Route::post('/{employeePayroll}/payments', [PayrollPaymentController::class, 'store'])->middleware('permission:payroll-payments.store');
    });

    Route::prefix('activities')->group(function (): void {
        Route::get('/', [ActivityController::class, 'index'])->middleware('permission:activities.index');
        Route::post('/', [ActivityController::class, 'store'])->middleware('permission:activities.store');
        Route::get('/{activity}', [ActivityController::class, 'show'])->middleware('permission:activities.show');
        Route::put('/{activity}', [ActivityController::class, 'update'])->middleware('permission:activities.update');
        Route::delete('/{activity}', [ActivityController::class, 'destroy'])->middleware('permission:activities.destroy');
    });

    Route::prefix('activity-pricing-plans')->group(function (): void {
        Route::get('/', [ActivityPricingPlanController::class, 'index'])->middleware('permission:activity-pricing-plans.index');
        Route::post('/', [ActivityPricingPlanController::class, 'store'])->middleware('permission:activity-pricing-plans.store');
        Route::get('/{pricingPlan}', [ActivityPricingPlanController::class, 'show'])->middleware('permission:activity-pricing-plans.show');
        Route::put('/{pricingPlan}', [ActivityPricingPlanController::class, 'update'])->middleware('permission:activity-pricing-plans.update');
        Route::delete('/{pricingPlan}', [ActivityPricingPlanController::class, 'destroy'])->middleware('permission:activity-pricing-plans.destroy');
    });

    Route::prefix('activity-schedules')->group(function (): void {
        Route::get('/', [ActivityScheduleController::class, 'index'])->middleware('permission:activity-schedules.index');
        Route::post('/', [ActivityScheduleController::class, 'store'])->middleware('permission:activity-schedules.store');
        Route::post('/{schedule}/generate-sessions', GenerateActivitySessionsController::class)->middleware('permission:activity-schedules.generate-sessions');
        Route::get('/{schedule}', [ActivityScheduleController::class, 'show'])->middleware('permission:activity-schedules.show');
        Route::put('/{schedule}', [ActivityScheduleController::class, 'update'])->middleware('permission:activity-schedules.update');
        Route::delete('/{schedule}', [ActivityScheduleController::class, 'destroy'])->middleware('permission:activity-schedules.destroy');
    });

    Route::prefix('activity-sessions')->group(function (): void {
        Route::get('/', [ActivitySessionController::class, 'index'])->middleware('permission:activity-sessions.index');
        Route::post('/', [ActivitySessionController::class, 'store'])->middleware('permission:activity-sessions.store');
        Route::get('/{session}', [ActivitySessionController::class, 'show'])->middleware('permission:activity-sessions.show');
        Route::put('/{session}', [ActivitySessionController::class, 'update'])->middleware('permission:activity-sessions.update');
        Route::delete('/{session}', [ActivitySessionController::class, 'destroy'])->middleware('permission:activity-sessions.destroy');
    });

    Route::prefix('activity-memberships')->group(function (): void {
        Route::get('/', [ActivityMembershipController::class, 'index'])->middleware('permission:activity-memberships.index');
        Route::post('/', [ActivityMembershipController::class, 'store'])->middleware('permission:activity-memberships.store');
        Route::get('/{membership}', [ActivityMembershipController::class, 'show'])->middleware('permission:activity-memberships.show');
        Route::put('/{membership}', [ActivityMembershipController::class, 'update'])->middleware('permission:activity-memberships.update');
        Route::delete('/{membership}', [ActivityMembershipController::class, 'destroy'])->middleware('permission:activity-memberships.destroy');
    });

    Route::prefix('activity-attendances')->group(function (): void {
        Route::get('/', [ActivityAttendanceController::class, 'index'])->middleware('permission:activity-attendances.index');
        Route::post('/', [ActivityAttendanceController::class, 'store'])->middleware('permission:activity-attendances.store');
        Route::get('/{attendance}', [ActivityAttendanceController::class, 'show'])->middleware('permission:activity-attendances.show');
        Route::put('/{attendance}', [ActivityAttendanceController::class, 'update'])->middleware('permission:activity-attendances.update');
        Route::delete('/{attendance}', [ActivityAttendanceController::class, 'destroy'])->middleware('permission:activity-attendances.destroy');
    });

    Route::prefix('activity-usages')->group(function (): void {
        Route::get('/', [ActivityUsageController::class, 'index'])->middleware('permission:activity-usages.index');
        Route::post('/start', [ActivityUsageController::class, 'start'])->middleware('permission:activity-usages.start');
        Route::get('/{usage}', [ActivityUsageController::class, 'show'])->middleware('permission:activity-usages.show');
        Route::post('/{usage}/pause', [ActivityUsageController::class, 'pause'])->middleware('permission:activity-usages.pause');
        Route::post('/{usage}/resume', [ActivityUsageController::class, 'resume'])->middleware('permission:activity-usages.resume');
        Route::patch('/{usage}/change-type', [ActivityUsageController::class, 'changeType'])->middleware('permission:activity-usages.change-type');
        Route::post('/{usage}/close', [ActivityUsageController::class, 'close'])->middleware('permission:activity-usages.close');
        Route::post('/{usage}/cancel', [ActivityUsageController::class, 'cancel'])->middleware('permission:activity-usages.cancel');
    });

    Route::prefix('visits')->group(function (): void {
        Route::get('/', [VisitController::class, 'index'])->middleware('permission:visits.index');
        Route::post('/', [VisitController::class, 'store'])->middleware('permission:visits.store');
        Route::get('/{visit}', [VisitController::class, 'show'])->middleware('permission:visits.show');
        Route::post('/{visit}/close', [VisitController::class, 'close'])->middleware('permission:visits.close');
        Route::post('/{visit}/cancel', [VisitController::class, 'cancel'])->middleware('permission:visits.cancel');
    });

    Route::prefix('inventory-items')->group(function (): void {
        Route::get('/', [InventoryItemController::class, 'index'])->middleware('permission:inventory-items.index');
        Route::post('/', [InventoryItemController::class, 'store'])->middleware('permission:inventory-items.store');
        Route::get('/{inventoryItem}', [InventoryItemController::class, 'show'])->middleware('permission:inventory-items.show');
        Route::put('/{inventoryItem}', [InventoryItemController::class, 'update'])->middleware('permission:inventory-items.update');
        Route::delete('/{inventoryItem}', [InventoryItemController::class, 'destroy'])->middleware('permission:inventory-items.destroy');
    });

    Route::prefix('stock-movements')->group(function (): void {
        Route::get('/', [StockMovementController::class, 'index'])->middleware('permission:stock-movements.index');
        Route::post('/', [StockMovementController::class, 'store'])->middleware('permission:stock-movements.store');
        Route::get('/{stockMovement}', [StockMovementController::class, 'show'])->middleware('permission:stock-movements.show');
    });

    Route::prefix('cafe-products')->group(function (): void {
        Route::get('/', [CafeProductController::class, 'index'])->middleware('permission:cafe-products.index');
        Route::post('/', [CafeProductController::class, 'store'])->middleware('permission:cafe-products.store');
        Route::get('/{cafeProduct}', [CafeProductController::class, 'show'])->middleware('permission:cafe-products.show');
        Route::put('/{cafeProduct}', [CafeProductController::class, 'update'])->middleware('permission:cafe-products.update');
        Route::delete('/{cafeProduct}', [CafeProductController::class, 'destroy'])->middleware('permission:cafe-products.destroy');
    });
});
