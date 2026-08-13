<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Payroll\PayrollPeriodResource;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollPeriodService;
use Illuminate\Http\JsonResponse;

final class FinalizePayrollController extends Controller
{
    public function __construct(
        private readonly PayrollPeriodService $payrollPeriodService
    ) {
    }

    public function __invoke(
        PayrollPeriod $period
    ): JsonResponse {
        $period = $this->payrollPeriodService
            ->finalizePayroll($period);

        return ApiResponse::success(
            new PayrollPeriodResource($period),
            __('messages.updated_successfully')
        );
    }
}
