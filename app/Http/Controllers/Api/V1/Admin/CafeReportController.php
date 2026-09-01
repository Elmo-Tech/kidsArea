<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Report\CafeReportRequest;
use App\Services\Report\CafeReportService;
use Illuminate\Http\JsonResponse;

class CafeReportController extends Controller
{
    public function __construct(
        private readonly CafeReportService $cafeReportService
    ) {}

    public function __invoke(CafeReportRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->cafeReportService->report($request->validated())
        );
    }
}
