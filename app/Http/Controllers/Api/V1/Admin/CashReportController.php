<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CashReport\CashReportRequest;
use App\Services\Cash\CashReportService;
use Illuminate\Http\JsonResponse;

final class CashReportController extends Controller
{
    public function __construct(
        private readonly CashReportService $cashReportService
    ) {}

    public function __invoke(
        CashReportRequest $request
    ): JsonResponse {
        return ApiResponse::success(
            $this->cashReportService->report(
                $request->validated()
            )
        );
    }
}
