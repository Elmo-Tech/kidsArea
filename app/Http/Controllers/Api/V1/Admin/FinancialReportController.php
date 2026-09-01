<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Report\FinancialReportRequest;
use App\Services\Report\FinancialReportService;
use Illuminate\Http\JsonResponse;

class FinancialReportController extends Controller
{
    public function __construct(
        private readonly FinancialReportService $financialReportService
    ) {}

    public function __invoke(FinancialReportRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->financialReportService->report(
                $request->validated()
            )
        );
    }
}
