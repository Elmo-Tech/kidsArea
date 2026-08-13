<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PayrollPayment\PayEmployeePayrollRequest;
use App\Http\Resources\V1\PayrollPayment\PayrollPaymentCollection;
use App\Http\Resources\V1\PayrollPayment\PayrollPaymentResource;
use App\Models\EmployeePayroll;
use App\Services\Payroll\PayrollPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PayrollPaymentController extends Controller
{
    public function __construct(
        private readonly PayrollPaymentService $payrollPaymentService
    ) {
    }

    public function index(
        EmployeePayroll $employeePayroll,
        Request $request
    ): JsonResponse {
        $payments = $this->payrollPaymentService
            ->allForEmployeePayroll(
                $employeePayroll,
                $request
            );

        return ApiResponse::success(
            new PayrollPaymentCollection($payments)
        );
    }

    public function store(
        PayEmployeePayrollRequest $request,
        EmployeePayroll $employeePayroll
    ): JsonResponse {
        $payment = $this->payrollPaymentService
            ->payRemainingSalary(
                $employeePayroll,
                $request->validated()
            );

        return ApiResponse::success(
            new PayrollPaymentResource($payment),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }
}
