<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ActivitySession\StoreActivitySessionRequest;
use App\Http\Requests\V1\ActivitySession\UpdateActivitySessionRequest;
use App\Http\Resources\V1\ActivitySession\ActivitySessionCollection;
use App\Http\Resources\V1\ActivitySession\ActivitySessionResource;
use App\Models\ActivitySession;
use App\Services\Activity\ActivitySessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ActivitySessionController extends Controller
{
    public function __construct(
        private readonly ActivitySessionService $activitySessionService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $sessions = $this->activitySessionService
            ->all($request);

        return ApiResponse::success(
            new ActivitySessionCollection($sessions)
        );
    }

    public function store(
        StoreActivitySessionRequest $request
    ): JsonResponse {
        $session = $this->activitySessionService
            ->createSession(
                $request->validated()
            );

        return ApiResponse::success(
            new ActivitySessionResource($session),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        ActivitySession $session
    ): JsonResponse {
        $session = $this->activitySessionService
            ->editSession($session);

        return ApiResponse::success(
            new ActivitySessionResource($session)
        );
    }

    public function update(
        UpdateActivitySessionRequest $request,
        ActivitySession $session
    ): JsonResponse {
        $session = $this->activitySessionService
            ->updateSession(
                $session,
                $request->validated()
            );

        return ApiResponse::success(
            new ActivitySessionResource($session),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        ActivitySession $session
    ): JsonResponse {
        $this->activitySessionService
            ->deleteSession($session);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
