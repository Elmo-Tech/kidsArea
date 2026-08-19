<?php

declare(strict_types=1);

namespace App\Services\Visit;

use App\Enums\VisitCheckoutStatusEnum;
use App\Enums\VisitPaymentStatusEnum;
use App\Exceptions\VisitPayment\VisitCheckoutAlreadyPaidException;
use App\Exceptions\VisitPayment\VisitCheckoutCancelledException;
use App\Exceptions\VisitPayment\VisitCheckoutNotFinalizedException;
use App\Exceptions\VisitPayment\VisitPaymentExceedsRemainingAmountException;
use App\Models\VisitCheckout;
use App\Models\VisitPayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitPaymentService
{
    public function all(
        VisitCheckout $checkout,
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return $checkout->payments()
            ->latest('paid_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function createPayment(
        VisitCheckout $checkout,
        array $data
    ): VisitPayment {
        return DB::transaction(function () use ($checkout, $data): VisitPayment {
            $checkout = VisitCheckout::query()
                ->whereKey($checkout->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureCheckoutCanReceivePayment($checkout);

            $paidAmount = $this->getPaidAmount($checkout);
            $remainingAmount = round((float) $checkout->total - $paidAmount, 2);
            $amount = round((float) $data['amount'], 2);

            if ($remainingAmount <= 0) {
                throw new VisitCheckoutAlreadyPaidException();
            }

            if ($amount > $remainingAmount) {
                throw new VisitPaymentExceedsRemainingAmountException();
            }

            return VisitPayment::create([
                'visit_checkout_id' => $checkout->id,
                'amount' => $amount,
                'paid_at' => $data['paidAt'] ?? now(),
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function showPayment(VisitPayment $payment): VisitPayment
    {
        return $payment->load([
            'checkout',
        ]);
    }

    public function getPaymentSummary(VisitCheckout $checkout): array
    {
        $checkout = VisitCheckout::query()->findOrFail($checkout->id);

        $total = (float) $checkout->total;
        $paidAmount = $this->getPaidAmount($checkout);
        $remainingAmount = max(0, round($total - $paidAmount, 2));

        return [
            'total' => round($total, 2),
            'paidAmount' => round($paidAmount, 2),
            'remainingAmount' => $remainingAmount,
            'paymentStatus' => $this->resolvePaymentStatus(
                total: $total,
                paidAmount: $paidAmount
            )->value,
        ];
    }

    private function ensureCheckoutCanReceivePayment(
        VisitCheckout $checkout
    ): void {
        if ($checkout->status === VisitCheckoutStatusEnum::CANCELLED) {
            throw new VisitCheckoutCancelledException();
        }

        if ($checkout->status !== VisitCheckoutStatusEnum::FINALIZED) {
            throw new VisitCheckoutNotFinalizedException();
        }
    }

    private function getPaidAmount(
        VisitCheckout $checkout
    ): float {
        return round(
            (float) $checkout->payments()->sum('amount'),
            2
        );
    }

    private function resolvePaymentStatus(
        float $total,
        float $paidAmount
    ): VisitPaymentStatusEnum {
        if ($paidAmount <= 0) {
            return VisitPaymentStatusEnum::UNPAID;
        }

        if ($paidAmount < $total) {
            return VisitPaymentStatusEnum::PARTIALLY_PAID;
        }

        return VisitPaymentStatusEnum::PAID;
    }
}
