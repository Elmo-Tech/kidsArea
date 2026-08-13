<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EmployeePayroll\EmployeePayrollCollection;
use App\Http\Resources\V1\EmployeePayroll\EmployeePayrollResource;
use App\Models\EmployeePayroll;
use App\Models\PayrollPeriod;
use App\Services\Payroll\EmployeePayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmployeePayrollController extends Controller
{
    public function __construct(
        private readonly EmployeePayrollService $employeePayrollService
    ) {
    }

    /**
     * Display employee payrolls for a payroll period.
     *
     * GET /api/v1/admin/payroll-periods/{period}/employees
     */
    public function index(
        PayrollPeriod $period,
        Request $request
    ): JsonResponse {
        $employeePayrolls = $this->employeePayrollService
            ->allForPeriod(
                $period,
                $request
            );

        return ApiResponse::success(
            new EmployeePayrollCollection($employeePayrolls)
        );
    }

    /**
     * Display employee payroll details.
     *
     * GET /api/v1/admin/payroll-periods/{period}/employees/{employeePayroll}
     */
    public function show(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll
    ): JsonResponse {
        $employeePayroll = $this->employeePayrollService
            ->showForPeriod(
                $period,
                $employeePayroll
            );

        return ApiResponse::success(
            new EmployeePayrollResource($employeePayroll)
        );
    }
}
