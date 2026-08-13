<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Payslip\PayslipResource;
use App\Models\EmployeePayroll;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayslipService;
use Illuminate\Http\JsonResponse;

final class PayslipController extends Controller
{
    public function __construct(
        private readonly PayslipService $payslipService
    ) {
    }

    public function __invoke(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll
    ): JsonResponse {
        $payslip = $this->payslipService
            ->getPayslip(
                $period,
                $employeePayroll
            );

        return ApiResponse::success(
            new PayslipResource($payslip)
        );
    }
}
