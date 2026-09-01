<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Report\ActivityReportRequest;
use App\Services\Report\ActivityReportService;
use Illuminate\Http\JsonResponse;

class ActivityReportController extends Controller
{
    public function __construct(
        private readonly ActivityReportService $activityReportService
    ) {}

    public function __invoke(ActivityReportRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->activityReportService->report(
                $request->validated()
            )
        );
    }
}
