<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Reports\PayrollReportCollection;
use App\Models\PayrollPeriod;
use App\Services\Reports\PayrollReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PayrollReportController extends Controller
{
    public function __construct(
        private readonly PayrollReportService $payrollReportService
    ) {
    }

    /**
     * Display payroll report for a specific payroll period.
     *
     * GET /api/v1/admin/reports/payroll/{period}
     */
    public function __invoke(
        PayrollPeriod $period,
        Request $request
    ): JsonResponse {
        $payrolls = $this->payrollReportService->all(
            $period,
            $request
        );

        $summary = $this->payrollReportService
            ->getSummary($period);

        return ApiResponse::success([
            'summary' => $summary,
            'report' => new PayrollReportCollection($payrolls),
        ]);
    }
}
