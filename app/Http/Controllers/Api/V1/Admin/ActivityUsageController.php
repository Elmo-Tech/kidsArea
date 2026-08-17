<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ActivityUsage\CancelActivityUsageRequest;
use App\Http\Requests\V1\ActivityUsage\ChangeActivityUsageTypeRequest;
use App\Http\Requests\V1\ActivityUsage\CloseActivityUsageRequest;
use App\Http\Requests\V1\ActivityUsage\StartActivityUsageRequest;
use App\Http\Resources\V1\ActivityUsage\ActivityUsageCollection;
use App\Http\Resources\V1\ActivityUsage\ActivityUsageResource;
use App\Models\ActivityUsage;
use App\Services\Activity\ActivityUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ActivityUsageController extends Controller
{
    public function __construct(
        private readonly ActivityUsageService $activityUsageService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $usages = $this->activityUsageService
            ->all($request);

        return ApiResponse::success(
            new ActivityUsageCollection($usages)
        );
    }

    public function show(
        ActivityUsage $usage
    ): JsonResponse {
        $usage = $this->activityUsageService
            ->showUsage($usage);

        return ApiResponse::success(
            new ActivityUsageResource($usage)
        );
    }

    public function start(
        StartActivityUsageRequest $request
    ): JsonResponse {
        $usage = $this->activityUsageService
            ->startUsage(
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityUsageResource($usage),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function pause(
        ActivityUsage $usage
    ): JsonResponse {
        $usage = $this->activityUsageService
            ->pauseUsage($usage);

        return ApiResponse::success(
            new ActivityUsageResource($usage),
            __('messages.updated_successfully')
        );
    }

    public function resume(
        ActivityUsage $usage
    ): JsonResponse {
        $usage = $this->activityUsageService
            ->resumeUsage($usage);

        return ApiResponse::success(
            new ActivityUsageResource($usage),
            __('messages.updated_successfully')
        );
    }

    public function changeType(
        ChangeActivityUsageTypeRequest $request,
        ActivityUsage $usage
    ): JsonResponse {
        $usage = $this->activityUsageService
            ->changeUsageType(
                $usage,
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityUsageResource($usage),
            __('messages.updated_successfully')
        );
    }

    public function close(
        CloseActivityUsageRequest $request,
        ActivityUsage $usage
    ): JsonResponse {
        $usage = $this->activityUsageService
            ->closeUsage(
                $usage,
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityUsageResource($usage),
            __('messages.updated_successfully')
        );
    }

    public function cancel(
        CancelActivityUsageRequest $request,
        ActivityUsage $usage
    ): JsonResponse {
        $usage = $this->activityUsageService
            ->cancelUsage(
                $usage,
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityUsageResource($usage),
            __('messages.updated_successfully')
        );
    }
}
