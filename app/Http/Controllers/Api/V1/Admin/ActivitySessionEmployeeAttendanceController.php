<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ActivitySessionEmployeeAttendance\StoreActivitySessionEmployeeAttendanceRequest;
use App\Http\Requests\V1\ActivitySessionEmployeeAttendance\UpdateActivitySessionEmployeeAttendanceRequest;
use App\Http\Resources\V1\ActivitySessionEmployeeAttendance\ActivitySessionEmployeeAttendanceCollection;
use App\Http\Resources\V1\ActivitySessionEmployeeAttendance\ActivitySessionEmployeeAttendanceResource;
use App\Models\ActivitySessionEmployeeAttendance;
use App\Services\Activity\ActivitySessionEmployeeAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ActivitySessionEmployeeAttendanceController extends Controller
{
    public function __construct(
        private readonly ActivitySessionEmployeeAttendanceService $attendanceService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $attendances = $this->attendanceService->all($request);

        return ApiResponse::success(
            new ActivitySessionEmployeeAttendanceCollection($attendances)
        );
    }

    public function store(
        StoreActivitySessionEmployeeAttendanceRequest $request
    ): JsonResponse {
        $attendance = $this->attendanceService->createAttendance(
            $request->validated()
        );

        return ApiResponse::success(
            new ActivitySessionEmployeeAttendanceResource($attendance),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        ActivitySessionEmployeeAttendance $activitySessionEmployeeAttendance
    ): JsonResponse {
        $attendance = $this->attendanceService->showAttendance(
            $activitySessionEmployeeAttendance
        );

        return ApiResponse::success(
            new ActivitySessionEmployeeAttendanceResource($attendance)
        );
    }

    public function update(
        UpdateActivitySessionEmployeeAttendanceRequest $request,
        ActivitySessionEmployeeAttendance $activitySessionEmployeeAttendance
    ): JsonResponse {
        $attendance = $this->attendanceService->updateAttendance(
            $activitySessionEmployeeAttendance,
            $request->validated()
        );

        return ApiResponse::success(
            new ActivitySessionEmployeeAttendanceResource($attendance),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        ActivitySessionEmployeeAttendance $activitySessionEmployeeAttendance
    ): JsonResponse {
        $this->attendanceService->deleteAttendance(
            $activitySessionEmployeeAttendance
        );

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
