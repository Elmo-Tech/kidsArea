<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityMembership;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;

class ActivityMembershipPaymentSummaryController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function __invoke(
        ActivityMembership $activityMembership
    ): JsonResponse {

        return response()->json(
            $this->paymentService->getPaymentSummary(
                $activityMembership
            )
        );
    }
}
