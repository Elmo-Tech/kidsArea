<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Activity\StoreActivityRequest;
use App\Http\Requests\V1\Activity\UpdateActivityRequest;
use App\Http\Resources\V1\Activity\ActivityCollection;
use App\Http\Resources\V1\Activity\ActivityResource;
use App\Models\Activity;
use App\Services\Activity\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $activities = $this->activityService
            ->all($request);

        return ApiResponse::success(
            new ActivityCollection($activities)
        );
    }

    public function store(
        StoreActivityRequest $request
    ): JsonResponse {
        $activity = $this->activityService
            ->createActivity(
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityResource($activity),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        Activity $activity
    ): JsonResponse {
        $activity = $this->activityService
            ->editActivity($activity);

        return ApiResponse::success(
            new ActivityResource($activity)
        );
    }

    public function update(
        UpdateActivityRequest $request,
        Activity $activity
    ): JsonResponse {
        $activity = $this->activityService
            ->updateActivity(
                $activity,
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityResource($activity),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        Activity $activity
    ): JsonResponse {
        $this->activityService
            ->deleteActivity($activity);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
