<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Inventory\StoreInventoryItemRequest;
use App\Http\Requests\V1\Inventory\UpdateInventoryItemRequest;
use App\Http\Resources\V1\Inventory\InventoryItemCollection;
use App\Http\Resources\V1\Inventory\InventoryItemResource;
use App\Models\InventoryItem;
use App\Services\Inventory\InventoryItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InventoryItemController extends Controller
{
    public function __construct(
        private readonly InventoryItemService $inventoryItemService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $inventoryItems = $this->inventoryItemService
            ->all($request);

        return ApiResponse::success(
            new InventoryItemCollection($inventoryItems)
        );
    }

    public function store(
        StoreInventoryItemRequest $request
    ): JsonResponse {
        $inventoryItem = $this->inventoryItemService
            ->createInventoryItem(
                $request->validated()
            );

        return ApiResponse::success(
            new InventoryItemResource($inventoryItem),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        InventoryItem $inventoryItem
    ): JsonResponse {
        $inventoryItem = $this->inventoryItemService
            ->editInventoryItem($inventoryItem);

        return ApiResponse::success(
            new InventoryItemResource($inventoryItem)
        );
    }

    public function update(
        UpdateInventoryItemRequest $request,
        InventoryItem $inventoryItem
    ): JsonResponse {
        $inventoryItem = $this->inventoryItemService
            ->updateInventoryItem(
                $inventoryItem,
                $request->validated()
            );

        return ApiResponse::success(
            new InventoryItemResource($inventoryItem),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        InventoryItem $inventoryItem
    ): JsonResponse {
        $this->inventoryItemService
            ->deleteInventoryItem($inventoryItem);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
