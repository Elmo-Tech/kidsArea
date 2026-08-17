<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Enums\ActivityMembershipStatusEnum;
use App\Enums\ActivityPricingTypeEnum;
use App\Enums\ActivitySessionStatusEnum;
use App\Exceptions\Activity\InvalidActivityMembershipPricingPlanException;
use App\Models\ActivityMembership;
use App\Models\ActivityPricingPlan;
use App\Models\ActivitySession;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityMembershipService
{
    public function all(
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(ActivityMembership::class)
            ->with([
                'child',
                'activity',
                'pricingPlan',
            ])
            ->allowedFilters(
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
                    'status'
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createMembership(
        array $data
    ): ActivityMembership {
        return DB::transaction(function () use ($data): ActivityMembership {
            $pricingPlan = ActivityPricingPlan::query()
                ->findOrFail(
                    $data['pricingPlanId']
                );

            $this->ensurePricingPlanSupportsMembership(
                $pricingPlan
            );

            $startDate = Carbon::parse(
                $data['startDate']
            );

            $endDate = null;
            $sessionsTotal = null;

            if (
                $pricingPlan->type ===
                ActivityPricingTypeEnum::SUBSCRIPTION
            ) {
                $endDate = $this->calculateSubscriptionEndDate(
                    $startDate,
                    (int) $pricingPlan->duration_value,
                    $pricingPlan->duration_unit->value
                );
            }

            if (
                $pricingPlan->type ===
                ActivityPricingTypeEnum::PACKAGE
            ) {
                $sessionsTotal =
                    $pricingPlan->sessions_count;
            }

            $membership = ActivityMembership::create([
                'child_id' =>
                    $data['childId'],

                'activity_id' =>
                    $pricingPlan->activity_id,

                'activity_pricing_plan_id' =>
                    $pricingPlan->id,

                'start_date' =>
                    $startDate->format('Y-m-d'),

                'end_date' =>
                    $endDate?->format('Y-m-d'),

                'sessions_total' =>
                    $sessionsTotal,

                'price' =>
                    $pricingPlan->price,

                'status' =>
                    $data['status']
                    ?? ActivityMembershipStatusEnum::ACTIVE->value,

                'notes' =>
                    $data['notes'] ?? null,
            ]);

            $this->syncSubscriptionSessions(
                $membership
            );

            return $membership->load([
                'child',
                'activity',
                'pricingPlan',
            ]);
        });
    }

    public function editMembership(
        ActivityMembership $membership
    ): ActivityMembership {
        return $membership->load([
            'child',
            'activity',
            'pricingPlan',
        ]);
    }

    public function updateMembership(
        ActivityMembership $membership,
        array $data
    ): ActivityMembership {
        return DB::transaction(function () use (
            $membership,
            $data
        ): ActivityMembership {
            $membership->loadMissing(
                'pricingPlan'
            );

            $startDate = array_key_exists(
                'startDate',
                $data
            )
                ? Carbon::parse(
                    $data['startDate']
                )
                : $membership->start_date->copy();

            $endDate = $membership->end_date;

            if (
                $membership->pricingPlan->type ===
                ActivityPricingTypeEnum::SUBSCRIPTION
            ) {
                $endDate = $this->calculateSubscriptionEndDate(
                    $startDate,
                    (int) $membership
                        ->pricingPlan
                        ->duration_value,
                    $membership
                        ->pricingPlan
                        ->duration_unit
                        ->value
                );
            }

            $membership->update([
                'start_date' =>
                    $startDate->format('Y-m-d'),

                'end_date' =>
                    $endDate?->format('Y-m-d'),

                'status' =>
                    $data['status']
                    ?? $membership->status->value,

                'notes' =>
                    array_key_exists('notes', $data)
                        ? $data['notes']
                        : $membership->notes,
            ]);

            $membership->refresh();

            $this->syncSubscriptionSessions(
                $membership
            );

            return $membership->load([
                'child',
                'activity',
                'pricingPlan',
            ]);
        });
    }

    public function deleteMembership(
        ActivityMembership $membership
    ): bool {
        return DB::transaction(
            fn (): bool => (bool) $membership->delete()
        );
    }

    private function ensurePricingPlanSupportsMembership(
        ActivityPricingPlan $pricingPlan
    ): void {
        if (
            ! in_array(
                $pricingPlan->type,
                [
                    ActivityPricingTypeEnum::SUBSCRIPTION,
                    ActivityPricingTypeEnum::PACKAGE,
                ],
                true
            )
        ) {
            throw new InvalidActivityMembershipPricingPlanException();
        }
    }

    private function syncSubscriptionSessions(
        ActivityMembership $membership
    ): void {
        $membership->loadMissing(
            'pricingPlan'
        );

        if (
            $membership->pricingPlan->type !==
            ActivityPricingTypeEnum::SUBSCRIPTION
        ) {
            return;
        }

        if (
            $membership->status !==
            ActivityMembershipStatusEnum::ACTIVE
        ) {
            return;
        }

        if ($membership->end_date === null) {
            return;
        }

        ActivitySession::query()
            ->where(
                'activity_id',
                $membership->activity_id
            )
            ->whereDate(
                'session_date',
                '>=',
                $membership->start_date
            )
            ->whereDate(
                'session_date',
                '<=',
                $membership->end_date
            )
            ->where(
                'status',
                '!=',
                ActivitySessionStatusEnum::CANCELLED->value
            )
            ->chunkById(
                100,
                function ($sessions) use ($membership): void {
                    foreach ($sessions as $session) {
                        $session
                            ->children()
                            ->syncWithoutDetaching([
                                $membership->child_id,
                            ]);
                    }
                }
            );
    }

    private function calculateSubscriptionEndDate(
        Carbon $startDate,
        int $durationValue,
        int $durationUnit
    ): Carbon {
        $endDate = $startDate->copy();

        return match ($durationUnit) {
            0 => $endDate
                ->addDays($durationValue)
                ->subDay(),

            1 => $endDate
                ->addWeeks($durationValue)
                ->subDay(),

            2 => $endDate
                ->addMonthsNoOverflow($durationValue)
                ->subDay(),

            3 => $endDate
                ->addYears($durationValue)
                ->subDay(),
        };
    }
}
