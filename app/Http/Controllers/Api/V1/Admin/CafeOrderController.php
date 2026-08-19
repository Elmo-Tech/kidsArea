<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CafeOrder\CancelCafeOrderRequest;
use App\Http\Requests\V1\CafeOrder\ConfirmCafeOrderRequest;
use App\Http\Requests\V1\CafeOrder\StoreCafeOrderRequest;
use App\Http\Requests\V1\CafeOrder\UpdateCafeOrderRequest;
use App\Http\Resources\V1\CafeOrder\CafeOrderCollection;
use App\Http\Resources\V1\CafeOrder\CafeOrderResource;
use App\Models\CafeOrder;
use App\Services\Cafe\CafeOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CafeOrderController extends Controller
{
    public function __construct(
        private readonly CafeOrderService $cafeOrderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->cafeOrderService->all($request);

        return ApiResponse::success(
            new CafeOrderCollection($orders)
        );
    }

    public function store(StoreCafeOrderRequest $request): JsonResponse
    {
        $order = $this->cafeOrderService->createOrder(
            $request->validated()
        );

        return ApiResponse::success(
            new CafeOrderResource($order),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(CafeOrder $cafeOrder): JsonResponse
    {
        $order = $this->cafeOrderService->showOrder($cafeOrder);

        return ApiResponse::success(
            new CafeOrderResource($order)
        );
    }

    public function update(
        UpdateCafeOrderRequest $request,
        CafeOrder $cafeOrder
    ): JsonResponse {
        $order = $this->cafeOrderService->updateOrder(
            $cafeOrder,
            $request->validated()
        );

        return ApiResponse::success(
            new CafeOrderResource($order),
            __('messages.updated_successfully')
        );
    }

    public function confirm(
        ConfirmCafeOrderRequest $request,
        CafeOrder $cafeOrder
    ): JsonResponse {
        $order = $this->cafeOrderService->confirmOrder(
            $cafeOrder
        );

        return ApiResponse::success(
            new CafeOrderResource($order),
            __('messages.updated_successfully')
        );
    }

    public function complete(CafeOrder $cafeOrder): JsonResponse
    {
        $order = $this->cafeOrderService->completeOrder(
            $cafeOrder
        );

        return ApiResponse::success(
            new CafeOrderResource($order),
            __('messages.updated_successfully')
        );
    }

    public function cancel(
        CancelCafeOrderRequest $request,
        CafeOrder $cafeOrder
    ): JsonResponse {
        $order = $this->cafeOrderService->cancelOrder(
            $cafeOrder,
            $request->validated()
        );

        return ApiResponse::success(
            new CafeOrderResource($order),
            __('messages.updated_successfully')
        );
    }
}
