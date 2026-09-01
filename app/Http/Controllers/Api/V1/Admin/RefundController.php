<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Refund\StoreRefundRequest;
use App\Http\Resources\V1\Refund\RefundCollection;
use App\Http\Resources\V1\Refund\RefundResource;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\Refund\RefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RefundController extends Controller
{
    public function __construct(
        private readonly RefundService $refundService
    ) {}

public function index(Request $request): JsonResponse
{
    return ApiResponse::success(
        new RefundCollection(
            $this->refundService->all($request)
        )
    );
}

    public function store(
        StoreRefundRequest $request,
        Payment $payment
    ): JsonResponse {
        $refund = $this->refundService->create(
            $payment,
            $request->validated()
        );

        return ApiResponse::success(
            new RefundResource($refund),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(Refund $refund): JsonResponse
    {
        return ApiResponse::success(
            new RefundResource(
                $this->refundService->show($refund)
            )
        );
    }
}
