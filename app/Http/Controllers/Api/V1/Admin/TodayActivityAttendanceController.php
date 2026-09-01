<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ActivityAttendance\TodayActivityAttendanceResource;
use App\Services\Activity\TodayActivityAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TodayActivityAttendanceController extends Controller
{
    public function __construct(
        private readonly TodayActivityAttendanceService $attendanceService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $rows = $this->attendanceService->all($request);

        return ApiResponse::success(
            TodayActivityAttendanceResource::collection($rows)
        );
    }
}
