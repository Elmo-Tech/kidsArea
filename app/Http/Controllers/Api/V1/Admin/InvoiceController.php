<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Invoice\InvoiceResource;
use App\Models\ActivityMembership;
use App\Models\ActivityUsage;
use App\Models\CafeOrder;
use App\Models\Invoice;
use App\Models\Visit;
use App\Services\Invoice\InvoiceService;
use Illuminate\Http\JsonResponse;

final class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice = $this->invoiceService->show($invoice);

        return ApiResponse::success(
            new InvoiceResource($invoice)
        );
    }

    public function showActivityUsage(ActivityUsage $usage): JsonResponse
    {
        $invoice = $this->invoiceService->findFor($usage);

        return ApiResponse::success(
            new InvoiceResource($invoice)
        );
    }

    public function showCafeOrder(CafeOrder $cafeOrder): JsonResponse
    {
        $invoice = $this->invoiceService->findFor($cafeOrder);

        return ApiResponse::success(
            new InvoiceResource($invoice)
        );
    }

    public function showVisit(Visit $visit): JsonResponse
    {
        $checkout = $visit->checkout()->firstOrFail();
        $invoice = $this->invoiceService->findFor($checkout);

        return ApiResponse::success(
            new InvoiceResource($invoice)
        );
    }

    public function showActivityMembership(
        ActivityMembership $membership
    ): JsonResponse {

        $invoice = $this->invoiceService->findFor(
            $membership
        );

        return ApiResponse::success(
            new InvoiceResource($invoice)
        );
    }
}
