<?php

declare(strict_types=1);

namespace App\Services\Visit;

use App\Enums\ActivityUsageStatusEnum;
use App\Enums\VisitStatusEnum;
use App\Exceptions\Visit\VisitAlreadyCancelledException;
use App\Exceptions\Visit\VisitAlreadyClosedException;
use App\Exceptions\Visit\VisitCannotBeCancelledException;
use App\Exceptions\Visit\VisitHasActiveUsagesException;
use App\Models\Child;
use App\Models\Visit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

use App\Enums\VisitCheckoutStatusEnum;
use App\Exceptions\Visit\VisitCheckoutNotFinalizedException;
use App\Exceptions\Visit\VisitCheckoutRequiredException;
use App\Exceptions\Visit\VisitNotFullyPaidException;

class VisitService
{
    public function all(
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(Visit::class)
            ->with([
                'child',
            ])
            ->withCount([
                'activityUsages',
            ])
            ->allowedFilters(
                AllowedFilter::exact(
                    'childId',
                    'child_id'
                ),

                AllowedFilter::exact(
                    'status'
                ),
            )
            ->latest('started_at')
            ->paginate($perPage);
    }

    public function openVisit(
        array $data
    ): Visit {
        return DB::transaction(function () use ($data): Visit {
            $child = $this->resolveChild($data);

            $visit = Visit::create([
                'child_id' => $child->id,
                'started_at' => now(),
                'closed_at' => null,
                'status' => VisitStatusEnum::OPEN->value,
                'started_by' => Auth::id(),
                'closed_by' => null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $this->loadVisit($visit);
        });
    }

    public function showVisit(
        Visit $visit
    ): Visit {
        return $this->loadVisit(
            $visit
        );
    }
    public function closeVisit(Visit $visit): Visit
    {
        return DB::transaction(function () use ($visit): Visit {
            $visit = $this->lockVisit($visit);

            $this->ensureVisitCanBeClosed($visit);
            $this->ensureVisitIsFullyPaid($visit);

            $visit->update([
                'status' => VisitStatusEnum::CLOSED->value,
                'closed_at' => now(),
                'closed_by' => Auth::id(),
            ]);

            return $this->loadVisit($visit->refresh());
        });
    }

    public function cancelVisit(
        Visit $visit,
        array $data
    ): Visit {
        return DB::transaction(function () use (
            $visit,
            $data
        ): Visit {
            $visit = $this->lockVisit(
                $visit
            );

            $this->ensureVisitCanBeCancelled(
                $visit
            );

            $visit->update([
                'status' =>
                    VisitStatusEnum::CANCELLED->value,

                'closed_at' =>
                    now(),

                'closed_by' =>
                    Auth::id(),

                'notes' =>
                    $this->appendCancellationReason(
                        $visit->notes,
                        $data['reason']
                    ),
            ]);

            return $this->loadVisit(
                $visit->refresh()
            );
        });
    }

    private function resolveChild(array $data): Child
    {
        if (! empty($data['childId'])) {
            return Child::query()->findOrFail($data['childId']);
        }

        $child = Child::query()
            ->where('guardian_phone', $data['customerPhone'])
            ->first();

        if ($child) {
            return $child;
        }

        return Child::query()->create([
            'guardian_name' => $data['customerName'],
            'guardian_phone' => $data['customerPhone'],
        ]);
    }

    private function ensureVisitCanBeClosed(
        Visit $visit
    ): void {
        if (
            $visit->status ===
            VisitStatusEnum::CLOSED
        ) {
            throw new VisitAlreadyClosedException();
        }

        if (
            $visit->status ===
            VisitStatusEnum::CANCELLED
        ) {
            throw new VisitAlreadyCancelledException();
        }

        $hasActiveUsages = $visit
            ->activityUsages()
            ->whereIn(
                'status',
                [
                    ActivityUsageStatusEnum::ACTIVE->value,
                    ActivityUsageStatusEnum::PAUSED->value,
                ]
            )
            ->exists();

        if ($hasActiveUsages) {
            throw new VisitHasActiveUsagesException();
        }
    }

    private function ensureVisitCanBeCancelled(
        Visit $visit
    ): void {
        if (
            $visit->status ===
            VisitStatusEnum::CLOSED
        ) {
            throw new VisitAlreadyClosedException();
        }

        if (
            $visit->status ===
            VisitStatusEnum::CANCELLED
        ) {
            throw new VisitAlreadyCancelledException();
        }

        /*
         * لو فيه Usage اتقفلت بالفعل وبقى ليها قيمة فعلية،
         * الأفضل ما نسمحش بإلغاء الزيارة بالكامل.
         *
         * بعد بناء Cafe Orders هنضيف نفس التحقق عليها هنا.
         */
        $hasClosedUsages = $visit
            ->activityUsages()
            ->where(
                'status',
                ActivityUsageStatusEnum::CLOSED->value
            )
            ->exists();

        if ($hasClosedUsages) {
            throw new VisitCannotBeCancelledException();
        }

        /*
         * لو فيه Usage شغالة أو paused، لازم تتلغى الأول
         * بشكل صريح من ActivityUsage module.
         */
        $hasRunningUsages = $visit
            ->activityUsages()
            ->whereIn(
                'status',
                [
                    ActivityUsageStatusEnum::ACTIVE->value,
                    ActivityUsageStatusEnum::PAUSED->value,
                ]
            )
            ->exists();

        if ($hasRunningUsages) {
            throw new VisitCannotBeCancelledException();
        }
    }

    private function lockVisit(
        Visit $visit
    ): Visit {
        return Visit::query()
            ->whereKey(
                $visit->id
            )
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function loadVisit(
        Visit $visit
    ): Visit {
        return $visit->load([
            'child',

            'activityUsages' => function ($query): void {
                $query->latest('started_at');
            },

            'activityUsages.activity',
            'activityUsages.pricingPlan',

            'startedBy',
            'closedBy',
        ]);
    }

    private function appendCancellationReason(
        ?string $notes,
        string $reason
    ): string {
        $cancellationNote =
            "Cancellation reason: {$reason}";

        if (
            $notes === null
            || trim($notes) === ''
        ) {
            return $cancellationNote;
        }

        return $notes
            . PHP_EOL
            . $cancellationNote;
    }

    private function ensureVisitIsFullyPaid(Visit $visit): void
    {
        $checkout = $visit->checkout()
            ->with('payments')
            ->first();

        if (! $checkout) {
            throw new VisitCheckoutRequiredException();
        }

        if ($checkout->status !== VisitCheckoutStatusEnum::FINALIZED) {
            throw new VisitCheckoutNotFinalizedException();
        }

        $paidAmount = (float) $checkout->payments()->sum('amount');
        $total = (float) $checkout->total;

        if (round($paidAmount, 2) < round($total, 2)) {
            throw new VisitNotFullyPaidException();
        }
    }
}
