<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ActivityPricingPlan\StoreActivityPricingPlanRequest;
use App\Http\Requests\V1\ActivityPricingPlan\UpdateActivityPricingPlanRequest;
use App\Http\Resources\V1\ActivityPricingPlan\ActivityPricingPlanCollection;
use App\Http\Resources\V1\ActivityPricingPlan\ActivityPricingPlanResource;
use App\Models\ActivityPricingPlan;
use App\Services\Activity\ActivityPricingPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ActivityPricingPlanController extends Controller
{
    public function __construct(
        private readonly ActivityPricingPlanService $activityPricingPlanService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $pricingPlans = $this->activityPricingPlanService
            ->all($request);

        return ApiResponse::success(
            new ActivityPricingPlanCollection($pricingPlans)
        );
    }

    public function store(
        StoreActivityPricingPlanRequest $request
    ): JsonResponse {
        $pricingPlan = $this->activityPricingPlanService
            ->createPricingPlan(
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityPricingPlanResource($pricingPlan),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        ActivityPricingPlan $pricingPlan
    ): JsonResponse {
        $pricingPlan = $this->activityPricingPlanService
            ->editPricingPlan($pricingPlan);

        return ApiResponse::success(
            new ActivityPricingPlanResource($pricingPlan)
        );
    }

    public function update(
        UpdateActivityPricingPlanRequest $request,
        ActivityPricingPlan $pricingPlan
    ): JsonResponse {
        $pricingPlan = $this->activityPricingPlanService
            ->updatePricingPlan(
                $pricingPlan,
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityPricingPlanResource($pricingPlan),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        ActivityPricingPlan $pricingPlan
    ): JsonResponse {
        $this->activityPricingPlanService
            ->deletePricingPlan($pricingPlan);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
