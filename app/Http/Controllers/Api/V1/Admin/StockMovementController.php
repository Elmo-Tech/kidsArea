<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Inventory\StoreStockMovementRequest;
use App\Http\Resources\V1\Inventory\StockMovementCollection;
use App\Http\Resources\V1\Inventory\StockMovementResource;
use App\Models\StockMovement;
use App\Services\Inventory\StockMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StockMovementController extends Controller
{
    public function __construct(
        private readonly StockMovementService $stockMovementService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $movements = $this->stockMovementService
            ->all($request);

        return ApiResponse::success(
            new StockMovementCollection($movements)
        );
    }

    public function store(
        StoreStockMovementRequest $request
    ): JsonResponse {
        $movement = $this->stockMovementService
            ->createMovement(
                $request->validated()
            );

        return ApiResponse::success(
            new StockMovementResource($movement),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        StockMovement $stockMovement
    ): JsonResponse {
        $movement = $this->stockMovementService
            ->showMovement($stockMovement);

        return ApiResponse::success(
            new StockMovementResource($movement)
        );
    }
}
