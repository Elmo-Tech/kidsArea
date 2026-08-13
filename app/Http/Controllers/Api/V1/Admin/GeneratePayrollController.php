<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Payroll\PayrollPeriodResource;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollPeriodService;
use Illuminate\Http\JsonResponse;

final class GeneratePayrollController extends Controller
{
    public function __construct(
        private readonly PayrollPeriodService $payrollPeriodService
    ) {
    }

    /**
     * Generate employee payrolls for payroll period.
     *
     * POST /api/v1/admin/payroll-periods/{period}/generate
     */
    public function __invoke(
        PayrollPeriod $period
    ): JsonResponse {
        $period = $this->payrollPeriodService
            ->generatePayroll($period);

        return ApiResponse::success(
            new PayrollPeriodResource($period),
            __('messages.updated_successfully')
        );
    }
}
