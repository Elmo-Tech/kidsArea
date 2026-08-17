<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Visit\CancelVisitRequest;
use App\Http\Requests\V1\Visit\StoreVisitRequest;
use App\Http\Resources\V1\Visit\VisitCollection;
use App\Http\Resources\V1\Visit\VisitResource;
use App\Models\Visit;
use App\Services\Visit\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VisitController extends Controller
{
    public function __construct(
        private readonly VisitService $visitService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $visits = $this->visitService
            ->all($request);

        return ApiResponse::success(
            new VisitCollection($visits)
        );
    }

    public function store(
        StoreVisitRequest $request
    ): JsonResponse {
        $visit = $this->visitService
            ->openVisit(
                $request->validated()
            );

        return ApiResponse::success(
            new VisitResource($visit),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        Visit $visit
    ): JsonResponse {
        $visit = $this->visitService
            ->showVisit($visit);

        return ApiResponse::success(
            new VisitResource($visit)
        );
    }

    public function close(
        Visit $visit
    ): JsonResponse {
        $visit = $this->visitService
            ->closeVisit($visit);

        return ApiResponse::success(
            new VisitResource($visit),
            __('messages.updated_successfully')
        );
    }

    public function cancel(
        CancelVisitRequest $request,
        Visit $visit
    ): JsonResponse {
        $visit = $this->visitService
            ->cancelVisit(
                $visit,
                $request->validated()
            );

        return ApiResponse::success(
            new VisitResource($visit),
            __('messages.updated_successfully')
        );
    }
}
