<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Visit\CheckoutVisitRequest;
use App\Http\Resources\V1\ActivityUsage\ActivityUsageResource;
use App\Http\Resources\V1\CafeOrder\CafeOrderResource;
use App\Http\Resources\V1\VisitCheckout\VisitCheckoutResource;
use App\Models\Visit;
use App\Services\Visit\VisitCheckoutWorkflowService;
use Illuminate\Http\JsonResponse;

final class CheckoutVisitController extends Controller
{
    public function __construct(
        private readonly VisitCheckoutWorkflowService $workflowService
    ) {}

    public function __invoke(
        CheckoutVisitRequest $request,
        Visit $visit
    ): JsonResponse {
        $result = $this->workflowService->checkout(
            $visit,
            $request->validated()
        );

        return ApiResponse::success([
            'checkout' => new VisitCheckoutResource(
                $result['checkout']
            ),

            'paymentSummary' => $result['paymentSummary'],

            'activities' => ActivityUsageResource::collection(
                $result['activities']
            ),

            'orders' => CafeOrderResource::collection(
                $result['orders']
            ),

            'visitClosed' => $result['visitClosed'],
        ]);
    }
}
