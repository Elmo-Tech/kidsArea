<?php

use App\Http\Controllers\Api\V1\Admin\ActivityController;
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


});
