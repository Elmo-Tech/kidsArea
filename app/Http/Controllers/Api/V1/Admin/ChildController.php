<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Child\StoreChildRequest;
use App\Http\Requests\V1\Child\UpdateChildRequest;
use App\Http\Resources\V1\Child\ChildCollection;
use App\Http\Resources\V1\Child\ChildResource;
use App\Models\Child;
use App\Services\Child\ChildService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ChildController extends Controller
{
    public function __construct(
        private readonly ChildService $childService
    ) {}

    public function index(
        Request $request
    ): JsonResponse {
        return ApiResponse::success(
            new ChildCollection(
                $this->childService->all(
                    $request
                )
            )
        );
    }

    public function store(
        StoreChildRequest $request
    ): JsonResponse {
        $child = $this->childService->create(
            $request->validated()
        );

        return ApiResponse::success(
            new ChildResource($child),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        Child $child
    ): JsonResponse {
        $child = $this->childService->show(
            $child
        );

        return ApiResponse::success(
            new ChildResource($child)
        );
    }

    public function update(
        UpdateChildRequest $request,
        Child $child
    ): JsonResponse {
        $child = $this->childService->update(
            $child,
            $request->validated()
        );

        return ApiResponse::success(
            new ChildResource($child),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        Child $child
    ): JsonResponse {
        $this->childService->delete(
            $child
        );

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
