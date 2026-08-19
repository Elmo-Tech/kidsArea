<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\VisitPayment\StoreVisitPaymentRequest;
use App\Http\Resources\V1\VisitPayment\VisitPaymentCollection;
use App\Http\Resources\V1\VisitPayment\VisitPaymentResource;
use App\Models\VisitCheckout;
use App\Models\VisitPayment;
use App\Services\Visit\VisitPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VisitPaymentController extends Controller
{
    public function __construct(
        private readonly VisitPaymentService $visitPaymentService
    ) {}

    public function index(
        Request $request,
        VisitCheckout $visitCheckout
    ): JsonResponse {
        $payments = $this->visitPaymentService->all(
            $visitCheckout,
            $request
        );

        return ApiResponse::success(
            new VisitPaymentCollection($payments)
        );
    }

    public function store(
        StoreVisitPaymentRequest $request,
        VisitCheckout $visitCheckout
    ): JsonResponse {
        $payment = $this->visitPaymentService->createPayment(
            $visitCheckout,
            $request->validated()
        );

        return ApiResponse::success(
            new VisitPaymentResource($payment),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        VisitPayment $visitPayment
    ): JsonResponse {
        $payment = $this->visitPaymentService->showPayment(
            $visitPayment
        );

        return ApiResponse::success(
            new VisitPaymentResource($payment)
        );
    }

    public function summary(
        VisitCheckout $visitCheckout
    ): JsonResponse {
        $summary = $this->visitPaymentService->getPaymentSummary(
            $visitCheckout
        );

        return ApiResponse::success(
            $summary
        );
    }
}
