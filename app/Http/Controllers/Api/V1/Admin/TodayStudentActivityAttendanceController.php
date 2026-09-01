<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ActivityAttendance\DailyStudentAttendanceResource;
use App\Services\ActivityAttendance\TodayStudentActivityAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TodayStudentActivityAttendanceController extends Controller
{
    public function __construct(
        private readonly TodayStudentActivityAttendanceService $service
    ) {}

    public function __invoke(
        Request $request
    ): JsonResponse {
        $rows = $this->service->all($request);

        return ApiResponse::success(
            DailyStudentAttendanceResource::collection($rows)
        );
    }
}
