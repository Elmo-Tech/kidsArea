<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ActivityAttendance\StoreActivityAttendanceRequest;
use App\Http\Requests\V1\ActivityAttendance\UpdateActivityAttendanceRequest;
use App\Http\Resources\V1\ActivityAttendance\ActivityAttendanceCollection;
use App\Http\Resources\V1\ActivityAttendance\ActivityAttendanceResource;
use App\Models\ActivityAttendance;
use App\Services\Activity\ActivityAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ActivityAttendanceController extends Controller
{
    public function __construct(
        private readonly ActivityAttendanceService $activityAttendanceService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $attendances = $this->activityAttendanceService
            ->all($request);

        return ApiResponse::success(
            new ActivityAttendanceCollection($attendances)
        );
    }

    public function store(
        StoreActivityAttendanceRequest $request
    ): JsonResponse {
        $attendance = $this->activityAttendanceService
            ->createAttendance(
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityAttendanceResource($attendance),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        ActivityAttendance $attendance
    ): JsonResponse {
        $attendance = $this->activityAttendanceService
            ->editAttendance($attendance);

        return ApiResponse::success(
            new ActivityAttendanceResource($attendance)
        );
    }

    public function update(
        UpdateActivityAttendanceRequest $request,
        ActivityAttendance $attendance
    ): JsonResponse {
        $attendance = $this->activityAttendanceService
            ->updateAttendance(
                $attendance,
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityAttendanceResource($attendance),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        ActivityAttendance $attendance
    ): JsonResponse {
        $this->activityAttendanceService
            ->deleteAttendance($attendance);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
