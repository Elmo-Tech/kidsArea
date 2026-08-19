<?php

declare(strict_types=1);

namespace App\Services\Visit;

use App\Enums\ActivityUsageStatusEnum;
use App\Enums\CafeOrderStatusEnum;
use App\Enums\VisitCheckoutStatusEnum;
use App\Exceptions\VisitCheckout\VisitCheckoutAlreadyCancelledException;
use App\Exceptions\VisitCheckout\VisitCheckoutAlreadyExistsException;
use App\Exceptions\VisitCheckout\VisitCheckoutAlreadyFinalizedException;
use App\Exceptions\VisitCheckout\VisitCheckoutHasActiveUsagesException;
use App\Exceptions\VisitCheckout\VisitCheckoutHasOpenCafeOrdersException;
use App\Exceptions\VisitCheckout\VisitCheckoutNotEditableException;
use App\Models\Visit;
use App\Models\VisitCheckout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class VisitCheckoutService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(VisitCheckout::class)
            ->with(['visit'])
            ->allowedFilters([
                AllowedFilter::exact('visitId', 'visit_id'),
                AllowedFilter::exact('status'),
            ])
            ->latest('id')
            ->paginate($perPage);
    }

    public function createCheckout(array $data): VisitCheckout
    {
        return DB::transaction(function () use ($data): VisitCheckout {
            $visit = Visit::query()
                ->whereKey($data['visitId'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($visit->checkout()->exists()) {
                throw new VisitCheckoutAlreadyExistsException();
            }

            $this->ensureVisitCanBeCheckedOut($visit);

            $activityTotal = $this->calculateActivityTotal($visit);
            $cafeTotal = $this->calculateCafeTotal($visit);
            $subtotal = round($activityTotal + $cafeTotal, 2);
            $discount = (float) ($data['discount'] ?? 0);
            $total = max(0, round($subtotal - $discount, 2));

            $checkout = VisitCheckout::create([
                'visit_id' => $visit->id,
                'activity_total' => $activityTotal,
                'cafe_total' => $cafeTotal,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => VisitCheckoutStatusEnum::DRAFT->value,
                'finalized_at' => null,
                'cancelled_at' => null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $this->loadCheckout($checkout);
        });
    }

    public function showCheckout(VisitCheckout $checkout): VisitCheckout
    {
        return $this->loadCheckout($checkout);
    }

    public function updateCheckout(VisitCheckout $checkout, array $data): VisitCheckout
    {
        return DB::transaction(function () use ($checkout, $data): VisitCheckout {
            $checkout = $this->lockCheckout($checkout);

            $this->ensureCheckoutIsEditable($checkout);

            $visit = Visit::query()
                ->whereKey($checkout->visit_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureVisitCanBeCheckedOut($visit);

            $activityTotal = $this->calculateActivityTotal($visit);
            $cafeTotal = $this->calculateCafeTotal($visit);
            $subtotal = round($activityTotal + $cafeTotal, 2);
            $discount = array_key_exists('discount', $data) ? (float) $data['discount'] : (float) $checkout->discount;
            $total = max(0, round($subtotal - $discount, 2));

            $checkout->update([
                'activity_total' => $activityTotal,
                'cafe_total' => $cafeTotal,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $checkout->notes,
            ]);

            return $this->loadCheckout($checkout->refresh());
        });
    }

    public function finalizeCheckout(VisitCheckout $checkout): VisitCheckout
    {
        return DB::transaction(function () use ($checkout): VisitCheckout {
            $checkout = $this->lockCheckout($checkout);

            if ($checkout->status === VisitCheckoutStatusEnum::FINALIZED) {
                throw new VisitCheckoutAlreadyFinalizedException();
            }

            if ($checkout->status === VisitCheckoutStatusEnum::CANCELLED) {
                throw new VisitCheckoutAlreadyCancelledException();
            }

            if ($checkout->status !== VisitCheckoutStatusEnum::DRAFT) {
                throw new VisitCheckoutNotEditableException();
            }

            $visit = Visit::query()
                ->whereKey($checkout->visit_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureVisitCanBeCheckedOut($visit);

            $activityTotal = $this->calculateActivityTotal($visit);
            $cafeTotal = $this->calculateCafeTotal($visit);
            $subtotal = round($activityTotal + $cafeTotal, 2);
            $total = max(0, round($subtotal - (float) $checkout->discount, 2));

            $checkout->update([
                'activity_total' => $activityTotal,
                'cafe_total' => $cafeTotal,
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => VisitCheckoutStatusEnum::FINALIZED->value,
                'finalized_at' => now(),
            ]);

            return $this->loadCheckout($checkout->refresh());
        });
    }

    public function cancelCheckout(VisitCheckout $checkout, array $data): VisitCheckout
    {
        return DB::transaction(function () use ($checkout, $data): VisitCheckout {
            $checkout = $this->lockCheckout($checkout);

            if ($checkout->status === VisitCheckoutStatusEnum::FINALIZED) {
                throw new VisitCheckoutAlreadyFinalizedException();
            }

            if ($checkout->status === VisitCheckoutStatusEnum::CANCELLED) {
                throw new VisitCheckoutAlreadyCancelledException();
            }

            if ($checkout->status !== VisitCheckoutStatusEnum::DRAFT) {
                throw new VisitCheckoutNotEditableException();
            }

            $checkout->update([
                'status' => VisitCheckoutStatusEnum::CANCELLED->value,
                'cancelled_at' => now(),
                'notes' => $this->appendCancellationReason($checkout->notes, $data['reason']),
            ]);

            return $this->loadCheckout($checkout->refresh());
        });
    }

    private function ensureVisitCanBeCheckedOut(Visit $visit): void
    {
        $hasActiveUsages = $visit->activityUsages()
            ->whereIn('status', [
                ActivityUsageStatusEnum::ACTIVE->value,
                ActivityUsageStatusEnum::PAUSED->value,
            ])
            ->exists();

        if ($hasActiveUsages) {
            throw new VisitCheckoutHasActiveUsagesException();
        }

        $hasOpenCafeOrders = $visit->cafeOrders()
            ->whereIn('status', [
                CafeOrderStatusEnum::DRAFT->value,
                CafeOrderStatusEnum::CONFIRMED->value,
            ])
            ->exists();

        if ($hasOpenCafeOrders) {
            throw new VisitCheckoutHasOpenCafeOrdersException();
        }
    }

    private function calculateActivityTotal(Visit $visit): float
    {
        return round(
            (float) $visit->activityUsages()
                ->where('status', ActivityUsageStatusEnum::CLOSED->value)
                ->sum('final_amount'),
            2
        );
    }

    private function calculateCafeTotal(Visit $visit): float
    {
        return round(
            (float) $visit->cafeOrders()
                ->where('status', CafeOrderStatusEnum::COMPLETED->value)
                ->sum('total'),
            2
        );
    }

    private function ensureCheckoutIsEditable(VisitCheckout $checkout): void
    {
        if ($checkout->status !== VisitCheckoutStatusEnum::DRAFT) {
            throw new VisitCheckoutNotEditableException();
        }
    }

    private function lockCheckout(VisitCheckout $checkout): VisitCheckout
    {
        return VisitCheckout::query()
            ->whereKey($checkout->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function loadCheckout(VisitCheckout $checkout): VisitCheckout
    {
        return $checkout->load([
            'visit',
        ]);
    }

    private function appendCancellationReason(?string $notes, string $reason): string
    {
        $cancellationNote = "Cancellation reason: {$reason}";

        if ($notes === null || trim($notes) === '') {
            return $cancellationNote;
        }

        return $notes . PHP_EOL . $cancellationNote;
    }
}
