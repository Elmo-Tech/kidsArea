<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Payment\StorePaymentRequest;
use App\Http\Resources\V1\Payment\PaymentResource;
use App\Models\ActivityUsage;
use App\Models\CafeOrder;
use App\Models\VisitCheckout;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;

final class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function storeActivityUsage(
        StorePaymentRequest $request,
        ActivityUsage $usage
    ): JsonResponse {
        $payment = $this->paymentService->createPayment(
            $usage,
            $request->validated()
        );

        return ApiResponse::success(
            new PaymentResource($payment),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function activityUsageSummary(
        ActivityUsage $usage
    ): JsonResponse {
        return ApiResponse::success(
            $this->paymentService->getPaymentSummary($usage)
        );
    }

    public function storeCafeOrder(
        StorePaymentRequest $request,
        CafeOrder $cafeOrder
    ): JsonResponse {
        $payment = $this->paymentService->createPayment(
            $cafeOrder,
            $request->validated()
        );

        return ApiResponse::success(
            new PaymentResource($payment),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function cafeOrderSummary(
        CafeOrder $cafeOrder
    ): JsonResponse {
        return ApiResponse::success(
            $this->paymentService->getPaymentSummary($cafeOrder)
        );
    }

    public function storeVisitCheckout(
        StorePaymentRequest $request,
        VisitCheckout $visitCheckout
    ): JsonResponse {
        $payment = $this->paymentService->createPayment(
            $visitCheckout,
            $request->validated()
        );

        return ApiResponse::success(
            new PaymentResource($payment),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function visitCheckoutSummary(
        VisitCheckout $visitCheckout
    ): JsonResponse {
        return ApiResponse::success(
            $this->paymentService->getPaymentSummary($visitCheckout)
        );
    }
}
