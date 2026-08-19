<?php

declare(strict_types=1);

namespace App\Services\Visit;

use App\Enums\VisitCheckoutStatusEnum;
use App\Enums\VisitPaymentStatusEnum;
use App\Exceptions\Visit\VisitPaymentAmountRequiredException;
use App\Models\Visit;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\DB;

class VisitCheckoutWorkflowService
{
    public function __construct(
        private readonly VisitCheckoutService $visitCheckoutService,
        private readonly PaymentService $paymentService,
        private readonly VisitService $visitService
    ) {}

    public function checkout(Visit $visit, array $data): array
    {
        return DB::transaction(function () use ($visit, $data): array {
            $visit = Visit::query()
                ->whereKey($visit->id)
                ->lockForUpdate()
                ->firstOrFail();

            $checkout = $visit->checkout()->first();

            if (! $checkout) {
                $checkout = $this->visitCheckoutService->createCheckout([
                    'visitId' => $visit->id,
                    'discount' => $data['discount'] ?? 0,
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            if ($checkout->status === VisitCheckoutStatusEnum::DRAFT) {
                $checkout = $this->visitCheckoutService->finalizeCheckout($checkout);
            }

            $summary = $this->paymentService->getPaymentSummary($checkout);

            if (
                $summary['paymentStatus'] !== VisitPaymentStatusEnum::PAID->value
                && (float) $checkout->total > 0
            ) {
                if (! isset($data['amount'])) {
                    throw new VisitPaymentAmountRequiredException();
                }

                $this->paymentService->createPayment($checkout, [
                    'amount' => $data['amount'],
                    'paidAt' => $data['paidAt'] ?? null,
                    'reference' => $data['reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                $summary = $this->paymentService->getPaymentSummary($checkout);
            }

            $this->paymentService->ensureInvoiceIfPaid($checkout);

            $visitClosed = false;

            if ($summary['paymentStatus'] === VisitPaymentStatusEnum::PAID->value) {
                $visit = $this->visitService->closeVisit($visit);
                $visitClosed = true;
            }

            $activities = $visit->activityUsages()
                ->with([
                    'activity',
                    'pricingPlan',
                ])
                ->latest('started_at')
                ->get();

            $orders = $visit->cafeOrders()
                ->with([
                    'items.product',
                ])
                ->latest('id')
                ->get();

            return [
                'visit' => $visit,
                'checkout' => $checkout->refresh(),
                'paymentSummary' => $summary,
                'activities' => $activities,
                'orders' => $orders,
                'visitClosed' => $visitClosed,
            ];
        });
    }
}
