<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Enums\ActivityPricingTypeEnum;
use App\Enums\ActivityUsageStatusEnum;
use App\Enums\ActivityUsageTypeEnum;
use App\Enums\VisitStatusEnum;
use App\Exceptions\Activity\ActivityUsageAlreadyActiveException;
use App\Exceptions\Activity\ActivityUsageAlreadyCancelledException;
use App\Exceptions\Activity\ActivityUsageAlreadyClosedException;
use App\Exceptions\Activity\ActivityUsageCannotChangeTypeException;
use App\Exceptions\Activity\ActivityUsageCannotPauseException;
use App\Exceptions\Activity\ActivityUsageCannotResumeException;
use App\Exceptions\Activity\InvalidActivityUsagePricingPlanException;
use App\Exceptions\Activity\VisitNotOpenException;
use App\Models\ActivityPricingPlan;
use App\Models\ActivityUsage;
use App\Models\ActivityUsagePause;
use App\Models\Child;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityUsageService
{
    public function all(
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(ActivityUsage::class)
            ->with([
                'visit',
                'activity',
                'pricingPlan',
            ])
            ->allowedFilters([
                AllowedFilter::exact(
                    'visitId',
                    'visit_id'
                ),

                AllowedFilter::exact(
                    'childId',
                    'child_id'
                ),

                AllowedFilter::exact(
                    'activityId',
                    'activity_id'
                ),

                AllowedFilter::exact(
                    'pricingPlanId',
                    'activity_pricing_plan_id'
                ),

                AllowedFilter::exact(
                    'usageType',
                    'usage_type'
                ),

                AllowedFilter::exact(
                    'status'
                ),
            ])
            ->latest('started_at')
            ->paginate($perPage);
    }

    public function startUsage(
        array $data
    ): ActivityUsage {
        return DB::transaction(function () use ($data): ActivityUsage {
            $pricingPlan = ActivityPricingPlan::query()
                ->with('activity')
                ->where('activity_id', $data['activityId'])
                ->where('type', ActivityPricingTypeEnum::HOURLY->value)
                ->where('status', 1)
                ->firstOrFail();
            $this->ensurePricingPlanSupportsUsage($pricingPlan);

            if (! empty($data['visitId'])) {
                $visit = Visit::query()->findOrFail($data['visitId']);
                $this->ensureVisitIsOpen($visit);
            }


            $this->ensureNoActiveUsageForSameActivity(
                customerPhone: $data['customerPhone'],
                activityId: (int) $pricingPlan->activity_id
            );

            $usageType = ActivityUsageTypeEnum::from((int) $data['usageType']);
            $startedAt = now();

            $plannedDurationMinutes = null;
            $plannedEndAt = null;

            if ($usageType === ActivityUsageTypeEnum::FIXED_DURATION) {
                $plannedDurationMinutes = (int) $data['plannedDurationMinutes'];
                $plannedEndAt = $startedAt->copy()->addMinutes($plannedDurationMinutes);
            }

            $usage = ActivityUsage::create([
                'visit_id' => $data['visitId'] ?? null,
                'activity_id' => $pricingPlan->activity_id,
                'activity_pricing_plan_id' => $pricingPlan->id,
                'usage_type' => $usageType->value,
                'started_at' => $startedAt,
                'customer_name' => $data['customerName'],
                'customer_phone' => $data['customerPhone'],
                'ended_at' => null,
                'planned_duration_minutes' => $plannedDurationMinutes,
                'planned_end_at' => $plannedEndAt,
                'total_paused_minutes' => 0,
                'duration_minutes' => 0,
                'hourly_price' => $pricingPlan->price,
                'expected_amount' => null,
                'final_amount' => null,
                'status' => ActivityUsageStatusEnum::ACTIVE->value,
                'started_by' => Auth::id(),
                'closed_by' => null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $this->loadUsage($usage);
        });
    }

    public function showUsage(
        ActivityUsage $usage
    ): ActivityUsage {
        return $this->loadUsage(
            $usage
        );
    }

    public function pauseUsage(
        ActivityUsage $usage
    ): ActivityUsage {
        return DB::transaction(function () use ($usage): ActivityUsage {

            $usage = $this->lockUsage(
                $usage
            );

            if (
                $usage->status !==
                ActivityUsageStatusEnum::ACTIVE
            ) {
                throw new ActivityUsageCannotPauseException();
            }

            ActivityUsagePause::create([
                'activity_usage_id' =>
                    $usage->id,

                'paused_at' =>
                    now(),

                'resumed_at' =>
                    null,

                'duration_minutes' =>
                    null,
            ]);

            $usage->update([
                'status' =>
                    ActivityUsageStatusEnum::PAUSED->value,
            ]);

            $this->syncUsageMetrics(
                $usage
            );

            return $this->loadUsage(
                $usage->refresh()
            );
        });
    }

    public function resumeUsage(
        ActivityUsage $usage
    ): ActivityUsage {
        return DB::transaction(function () use ($usage): ActivityUsage {

            $usage = $this->lockUsage(
                $usage
            );

            if (
                $usage->status !==
                ActivityUsageStatusEnum::PAUSED
            ) {
                throw new ActivityUsageCannotResumeException();
            }

            $pause = ActivityUsagePause::query()
                ->where(
                    'activity_usage_id',
                    $usage->id
                )
                ->whereNull('resumed_at')
                ->latest('paused_at')
                ->lockForUpdate()
                ->first();

            if (! $pause) {
                throw new ActivityUsageCannotResumeException();
            }

            $resumedAt = now();

            $pauseDuration = $this->calculateMinutes(
                $pause->paused_at,
                $resumedAt
            );

            $pause->update([
                'resumed_at' =>
                    $resumedAt,

                'duration_minutes' =>
                    $pauseDuration,
            ]);

            $usage->update([
                'status' =>
                    ActivityUsageStatusEnum::ACTIVE->value,
            ]);

            $this->syncUsageMetrics(
                $usage
            );

            return $this->loadUsage(
                $usage->refresh()
            );
        });
    }

    public function changeUsageType(
        ActivityUsage $usage,
        array $data
    ): ActivityUsage {
        return DB::transaction(function () use (
            $usage,
            $data
        ): ActivityUsage {

            $usage = $this->lockUsage(
                $usage
            );

            if (
                ! in_array(
                    $usage->status,
                    [
                        ActivityUsageStatusEnum::ACTIVE,
                        ActivityUsageStatusEnum::PAUSED,
                    ],
                    true
                )
            ) {
                throw new ActivityUsageCannotChangeTypeException();
            }

            $usageType = ActivityUsageTypeEnum::from(
                (int) $data['usageType']
            );

            $plannedDurationMinutes = null;
            $plannedEndAt = null;

            if (
                $usageType ===
                ActivityUsageTypeEnum::FIXED_DURATION
            ) {
                $plannedDurationMinutes =
                    (int) $data['plannedDurationMinutes'];

                /*
                 * Base planned end = start + active planned duration.
                 * Pause minutes are then added to move the end forward.
                 */
                $plannedEndAt = $usage
                    ->started_at
                    ->copy()
                    ->addMinutes(
                        $plannedDurationMinutes
                    )
                    ->addMinutes(
                        $this->calculateCurrentPausedMinutes(
                            $usage
                        )
                    );
            }

            $usage->update([
                'usage_type' =>
                    $usageType->value,

                'planned_duration_minutes' =>
                    $plannedDurationMinutes,

                'planned_end_at' =>
                    $plannedEndAt,
            ]);

            $this->syncUsageMetrics(
                $usage
            );

            return $this->loadUsage(
                $usage->refresh()
            );
        });
    }

    public function closeUsage(
        ActivityUsage $usage,
        array $data
    ): ActivityUsage {
        return DB::transaction(function () use (
            $usage,
            $data
        ): ActivityUsage {

            $usage = $this->lockUsage(
                $usage
            );

            if (
                $usage->status ===
                ActivityUsageStatusEnum::CLOSED
            ) {
                throw new ActivityUsageAlreadyClosedException();
            }

            if (
                $usage->status ===
                ActivityUsageStatusEnum::CANCELLED
            ) {
                throw new ActivityUsageAlreadyCancelledException();
            }

            /*
            * If usage is currently paused,
            * close the open pause automatically.
            */
            if (
                $usage->status ===
                ActivityUsageStatusEnum::PAUSED
            ) {
                $this->closeOpenPause(
                    $usage
                );
            }

            $endedAt = now();

            /*
            * First close the usage.
            */
            $usage->update([
                'ended_at' =>
                    $endedAt,

                'status' =>
                    ActivityUsageStatusEnum::CLOSED->value,

                'closed_by' =>
                    Auth::id(),

                'notes' =>
                    array_key_exists('notes', $data)
                        ? $data['notes']
                        : $usage->notes,
            ]);

            /*
            * Calculate:
            * - paused minutes
            * - actual duration
            * - expected amount
            */
            $this->syncUsageMetrics(
                $usage
            );

            $usage->refresh();

            /*
            * Final amount is calculated by the system,
            * not received from frontend.
            */
            $usage->update([
                'final_amount' =>
                    $usage->expected_amount,
            ]);

            return $this->loadUsage(
                $usage->refresh()
            );
        });
    }

    public function cancelUsage(
        ActivityUsage $usage,
        array $data
    ): ActivityUsage {
        return DB::transaction(function () use (
            $usage,
            $data
        ): ActivityUsage {

            $usage = $this->lockUsage(
                $usage
            );

            if (
                $usage->status ===
                ActivityUsageStatusEnum::CLOSED
            ) {
                throw new ActivityUsageAlreadyClosedException();
            }

            if (
                $usage->status ===
                ActivityUsageStatusEnum::CANCELLED
            ) {
                throw new ActivityUsageAlreadyCancelledException();
            }

            if (
                $usage->status ===
                ActivityUsageStatusEnum::PAUSED
            ) {
                $this->closeOpenPause(
                    $usage
                );
            }

            $usage->update([
                'ended_at' =>
                    now(),

                'status' =>
                    ActivityUsageStatusEnum::CANCELLED->value,

                'expected_amount' =>
                    null,

                'final_amount' =>
                    null,

                'closed_by' =>
                    Auth::id(),

                'notes' =>
                    $this->appendCancellationReason(
                        $usage->notes,
                        $data['reason']
                    ),
            ]);

            $this->syncUsageMetrics(
                $usage,
                calculateAmount: false
            );

            return $this->loadUsage(
                $usage->refresh()
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Metrics
    |--------------------------------------------------------------------------
    */

    private function syncUsageMetrics(
        ActivityUsage $usage,
        bool $calculateAmount = true
    ): void {
        $usage->refresh();

        $now = $usage->ended_at
            ?? now();

        $totalPausedMinutes = $this
            ->calculateCurrentPausedMinutes(
                $usage,
                $now
            );

        $elapsedMinutes = $this->calculateMinutes(
            $usage->started_at,
            $now
        );

        $durationMinutes = max(
            0,
            $elapsedMinutes - $totalPausedMinutes
        );

        $expectedAmount = null;

        if ($calculateAmount) {
            $expectedAmount = round(
                (
                    (float) $usage->hourly_price
                    / 60
                ) * $durationMinutes,
                2
            );
        }

        $plannedEndAt = null;

        if (
            $usage->usage_type ===
            ActivityUsageTypeEnum::FIXED_DURATION
            && $usage->planned_duration_minutes !== null
        ) {
            $plannedEndAt = $usage
                ->started_at
                ->copy()
                ->addMinutes(
                    (int) $usage->planned_duration_minutes
                )
                ->addMinutes(
                    $totalPausedMinutes
                );
        }

        $usage->update([
            'total_paused_minutes' =>
                $totalPausedMinutes,

            'duration_minutes' =>
                $durationMinutes,

            'expected_amount' =>
                $expectedAmount,

            'planned_end_at' =>
                $plannedEndAt,
        ]);
    }

    private function calculateCurrentPausedMinutes(
        ActivityUsage $usage,
        ?Carbon $until = null
    ): int {
        $until ??= now();

        $pauses = ActivityUsagePause::query()
            ->where(
                'activity_usage_id',
                $usage->id
            )
            ->get();

        $total = 0;

        foreach ($pauses as $pause) {
            $end = $pause->resumed_at
                ?? $until;

            $total += $this->calculateMinutes(
                $pause->paused_at,
                $end
            );
        }

        return $total;
    }

    private function closeOpenPause(
        ActivityUsage $usage
    ): void {
        $pause = ActivityUsagePause::query()
            ->where(
                'activity_usage_id',
                $usage->id
            )
            ->whereNull('resumed_at')
            ->latest('paused_at')
            ->lockForUpdate()
            ->first();

        if (! $pause) {
            return;
        }

        $resumedAt = now();

        $pause->update([
            'resumed_at' =>
                $resumedAt,

            'duration_minutes' =>
                $this->calculateMinutes(
                    $pause->paused_at,
                    $resumedAt
                ),
        ]);
    }

    private function calculateMinutes(
        Carbon|string $from,
        Carbon|string $to
    ): int {
        return (int) Carbon::parse($from)
            ->diffInMinutes(
                Carbon::parse($to)
            );
    }

    private function resolveChild(array $data): Child
    {
        if (! empty($data['childId'])) {
            return Child::query()->findOrFail($data['childId']);
        }

        $child = Child::query()
            ->where('phone', $data['childPhone'])
            ->first();

        if ($child) {
            return $child;
        }

        return Child::query()->create([
            'name' => $data['childName'],
            'phone' => $data['childPhone'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Guards
    |--------------------------------------------------------------------------
    */

    private function ensurePricingPlanSupportsUsage(
        ActivityPricingPlan $pricingPlan
    ): void {
        if (
            $pricingPlan->type !==
            ActivityPricingTypeEnum::HOURLY
        ) {
            throw new InvalidActivityUsagePricingPlanException();
        }
    }

    private function ensureVisitIsOpen(
        Visit $visit
    ): void {
        if (
            $visit->status !==
            VisitStatusEnum::OPEN
        ) {
            throw new VisitNotOpenException();
        }
    }

    private function ensureNoActiveUsageForSameActivity(
        string $customerPhone,
        int $activityId
    ): void {
        $exists = ActivityUsage::query()
            ->where(
                'customer_phone',
                $customerPhone
            )
            ->where(
                'activity_id',
                $activityId
            )
            ->whereIn(
                'status',
                [
                    ActivityUsageStatusEnum::ACTIVE->value,
                    ActivityUsageStatusEnum::PAUSED->value,
                ]
            )
            ->exists();

        if ($exists) {
            throw new ActivityUsageAlreadyActiveException();
        }
    }

    private function lockUsage(
        ActivityUsage $usage
    ): ActivityUsage {
        return ActivityUsage::query()
            ->whereKey(
                $usage->id
            )
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function loadUsage(
        ActivityUsage $usage
    ): ActivityUsage {
        return $usage->load([
            'visit',
            //'child',
            'activity',
            'pricingPlan',
            'pauses',
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
}
