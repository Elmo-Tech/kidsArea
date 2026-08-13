<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PayrollSummary\PayrollSummaryResource;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollSummaryService;
use Illuminate\Http\JsonResponse;

final class PayrollSummaryController extends Controller
{
    public function __construct(
        private readonly PayrollSummaryService $payrollSummaryService
    ) {
    }

    /**
     * Display payroll period summary.
     *
     * GET /api/v1/admin/payroll-periods/{period}/summary
     */
    public function __invoke(
        PayrollPeriod $period
    ): JsonResponse {
        $summary = $this->payrollSummaryService
            ->getSummary($period);

        return ApiResponse::success(
            new PayrollSummaryResource($summary)
        );
    }
}
