<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Payroll\StorePayrollAdjustmentRequest;
use App\Http\Requests\V1\Payroll\UpdatePayrollAdjustmentRequest;
use App\Http\Resources\V1\Payroll\PayrollAdjustmentResource;
use App\Models\EmployeePayroll;
use App\Models\PayrollAdjustment;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollAdjustmentService;
use Illuminate\Http\JsonResponse;

final class PayrollAdjustmentController extends Controller
{
    public function __construct(
        private readonly PayrollAdjustmentService $payrollAdjustmentService
    ) {
    }

    public function store(
        StorePayrollAdjustmentRequest $request,
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll
    ): JsonResponse {
        $adjustment = $this->payrollAdjustmentService
            ->createAdjustment(
                $period,
                $employeePayroll,
                $request->validated()
            );

        return ApiResponse::success(
            new PayrollAdjustmentResource($adjustment),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll,
        PayrollAdjustment $adjustment
    ): JsonResponse {
        $adjustment = $this->payrollAdjustmentService
            ->editAdjustment(
                $period,
                $employeePayroll,
                $adjustment
            );

        return ApiResponse::success(
            new PayrollAdjustmentResource($adjustment)
        );
    }

    public function update(
        UpdatePayrollAdjustmentRequest $request,
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll,
        PayrollAdjustment $adjustment
    ): JsonResponse {
        $adjustment = $this->payrollAdjustmentService
            ->updateAdjustment(
                $period,
                $employeePayroll,
                $adjustment,
                $request->validated()
            );

        return ApiResponse::success(
            new PayrollAdjustmentResource($adjustment),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll,
        PayrollAdjustment $adjustment
    ): JsonResponse {
        $this->payrollAdjustmentService
            ->deleteAdjustment(
                $period,
                $employeePayroll,
                $adjustment
            );

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
