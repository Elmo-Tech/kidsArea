<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Report\AttendanceReportRequest;
use App\Services\Report\AttendanceReportService;
use Illuminate\Http\JsonResponse;

class AttendanceReportController extends Controller
{
    public function __construct(
        private readonly AttendanceReportService $attendanceReportService
    ) {}

    public function __invoke(AttendanceReportRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->attendanceReportService->report($request->validated())
        );
    }
}
