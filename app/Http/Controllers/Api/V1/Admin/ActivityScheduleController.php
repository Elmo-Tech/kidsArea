<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ActivitySchedule\StoreActivityScheduleRequest;
use App\Http\Requests\V1\ActivitySchedule\UpdateActivityScheduleRequest;
use App\Http\Resources\V1\ActivitySchedule\ActivityScheduleCollection;
use App\Http\Resources\V1\ActivitySchedule\ActivityScheduleResource;
use App\Models\ActivitySchedule;
use App\Services\Activity\ActivityScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ActivityScheduleController extends Controller
{
    public function __construct(
        private readonly ActivityScheduleService $activityScheduleService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $schedules = $this->activityScheduleService
            ->all($request);

        return ApiResponse::success(
            new ActivityScheduleCollection($schedules)
        );
    }

    public function store(
        StoreActivityScheduleRequest $request
    ): JsonResponse {
        $schedule = $this->activityScheduleService
            ->createSchedule(
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityScheduleResource($schedule),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        ActivitySchedule $schedule
    ): JsonResponse {
        $schedule = $this->activityScheduleService
            ->editSchedule($schedule);

        return ApiResponse::success(
            new ActivityScheduleResource($schedule)
        );
    }

    public function update(
        UpdateActivityScheduleRequest $request,
        ActivitySchedule $schedule
    ): JsonResponse {
        $schedule = $this->activityScheduleService
            ->updateSchedule(
                $schedule,
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityScheduleResource($schedule),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        ActivitySchedule $schedule
    ): JsonResponse {
        $this->activityScheduleService
            ->deleteSchedule($schedule);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
