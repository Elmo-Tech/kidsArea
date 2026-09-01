<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ActivityMembership\StoreActivityMembershipRequest;
use App\Http\Requests\V1\ActivityMembership\UpdateActivityMembershipRequest;
use App\Http\Resources\V1\ActivityMembership\ActivityMembershipCollection;
use App\Http\Resources\V1\ActivityMembership\ActivityMembershipResource;
use App\Models\ActivityMembership;
use App\Services\Activity\ActivityMembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\V1\ActivityMembership\RenewActivityMembershipRequest;

final class ActivityMembershipController extends Controller
{
    public function __construct(
        private readonly ActivityMembershipService $activityMembershipService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $memberships = $this->activityMembershipService
            ->all($request);

        return ApiResponse::success(
            new ActivityMembershipCollection($memberships)
        );
    }

    public function store(
        StoreActivityMembershipRequest $request
    ): JsonResponse {
        $membership = $this->activityMembershipService
            ->createMembership(
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityMembershipResource($membership),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        ActivityMembership $membership
    ): JsonResponse {
        $membership = $this->activityMembershipService
            ->editMembership($membership);

        return ApiResponse::success(
            new ActivityMembershipResource($membership)
        );
    }

    public function update(
        UpdateActivityMembershipRequest $request,
        ActivityMembership $membership
    ): JsonResponse {
        $membership = $this->activityMembershipService
            ->updateMembership(
                $membership,
                $request->validated()
            );

        return ApiResponse::success(
            new ActivityMembershipResource($membership),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        ActivityMembership $membership
    ): JsonResponse {
        $this->activityMembershipService
            ->deleteMembership($membership);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }

    public function renew(
        RenewActivityMembershipRequest $request,
        ActivityMembership $activityMembership
    ): JsonResponse {
        $membership = $this->activityMembershipService->renewMembership(
            $activityMembership,
            $request->validated()
        );

        return ApiResponse::success(
            new ActivityMembershipResource($membership),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }
}
