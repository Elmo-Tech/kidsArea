<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\VisitCheckout\CancelVisitCheckoutRequest;
use App\Http\Requests\V1\VisitCheckout\StoreVisitCheckoutRequest;
use App\Http\Requests\V1\VisitCheckout\UpdateVisitCheckoutRequest;
use App\Http\Resources\V1\VisitCheckout\VisitCheckoutCollection;
use App\Http\Resources\V1\VisitCheckout\VisitCheckoutResource;
use App\Models\VisitCheckout;
use App\Services\Visit\VisitCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VisitCheckoutController extends Controller
{
    public function __construct(
        private readonly VisitCheckoutService $visitCheckoutService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $checkouts = $this->visitCheckoutService->all($request);

        return ApiResponse::success(
            new VisitCheckoutCollection($checkouts)
        );
    }

    public function store(StoreVisitCheckoutRequest $request): JsonResponse
    {
        $checkout = $this->visitCheckoutService->createCheckout(
            $request->validated()
        );

        return ApiResponse::success(
            new VisitCheckoutResource($checkout),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(VisitCheckout $visitCheckout): JsonResponse
    {
        $checkout = $this->visitCheckoutService->showCheckout($visitCheckout);

        return ApiResponse::success(
            new VisitCheckoutResource($checkout)
        );
    }

    public function update(
        UpdateVisitCheckoutRequest $request,
        VisitCheckout $visitCheckout
    ): JsonResponse {
        $checkout = $this->visitCheckoutService->updateCheckout(
            $visitCheckout,
            $request->validated()
        );

        return ApiResponse::success(
            new VisitCheckoutResource($checkout),
            __('messages.updated_successfully')
        );
    }

    public function finalize(VisitCheckout $visitCheckout): JsonResponse
    {
        $checkout = $this->visitCheckoutService->finalizeCheckout(
            $visitCheckout
        );

        return ApiResponse::success(
            new VisitCheckoutResource($checkout),
            __('messages.updated_successfully')
        );
    }

    public function cancel(
        CancelVisitCheckoutRequest $request,
        VisitCheckout $visitCheckout
    ): JsonResponse {
        $checkout = $this->visitCheckoutService->cancelCheckout(
            $visitCheckout,
            $request->validated()
        );

        return ApiResponse::success(
            new VisitCheckoutResource($checkout),
            __('messages.updated_successfully')
        );
    }
}
