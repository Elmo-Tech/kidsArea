<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ActivityMembership\StoreActivityMembershipPaymentRequest;
use App\Http\Resources\V1\Payment\PaymentResource;
use App\Models\ActivityMembership;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;

class ActivityMembershipPaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function store(
        StoreActivityMembershipPaymentRequest $request,
        ActivityMembership $activityMembership
    ): JsonResponse {
        $payment = $this->paymentService->createPayment(
            $activityMembership,
            $request->validated()
        );

        return ApiResponse::success(
            new PaymentResource($payment),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }
}
