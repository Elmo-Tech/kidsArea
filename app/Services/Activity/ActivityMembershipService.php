<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Enums\ActivityAttendanceStatusEnum;
use App\Enums\ActivityMembershipStatusEnum;
use App\Enums\ActivityPricingTypeEnum;
use App\Enums\ActivitySessionStatusEnum;
use App\Exceptions\Activity\ActivityMembershipAlreadyRenewedException;
use App\Exceptions\Activity\ActivityMembershipNotRenewableException;
use App\Exceptions\Activity\ChildAlreadyHasActiveMembershipException;
use App\Exceptions\Activity\InvalidActivityMembershipPricingPlanException;
use App\Exceptions\Activity\RenewalPricingPlanActivityMismatchException;
use App\Models\ActivityMembership;
use App\Models\ActivityPricingPlan;
use App\Models\ActivitySession;
use App\Services\Payment\PaymentService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityMembershipService
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function all(Request $request): LengthAwarePaginator
    {
        $this->expireEndedMemberships();

        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(ActivityMembership::class)
            ->with(['child', 'activity', 'pricingPlan'])
            ->allowedFilters(
                AllowedFilter::exact('childId', 'child_id'),
                AllowedFilter::exact('activityId', 'activity_id'),
                AllowedFilter::exact('pricingPlanId', 'activity_pricing_plan_id'),
                AllowedFilter::exact('status')
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createMembership(array $data): ActivityMembership
    {
        return DB::transaction(function () use ($data): ActivityMembership {
            $this->expireEndedMemberships();

            $pricingPlan = ActivityPricingPlan::query()->findOrFail($data['pricingPlanId']);
            $this->ensurePricingPlanSupportsMembership($pricingPlan);

            $startDate = Carbon::parse($data['startDate']);
            $endDate = null;
            $sessionsTotal = null;

            if ($pricingPlan->type === ActivityPricingTypeEnum::SUBSCRIPTION) {
                $endDate = $this->calculateSubscriptionEndDate(
                    $startDate,
                    (int) $pricingPlan->duration_value,
                    $pricingPlan->duration_unit->value
                );
            }

            if ($pricingPlan->type === ActivityPricingTypeEnum::PACKAGE) {
                $sessionsTotal = $pricingPlan->sessions_count;
            }

            $status = $data['status'] ?? ActivityMembershipStatusEnum::ACTIVE->value;

            if ((int) $status === ActivityMembershipStatusEnum::ACTIVE->value) {
                $this->ensureChildHasNoActiveMembership(
                    (int) $data['childId'],
                    (int) $pricingPlan->activity_id
                );
            }

            $membership = ActivityMembership::create([
                'renewed_from_membership_id' => $data['renewedFromMembershipId'] ?? null,
                'child_id' => $data['childId'],
                'activity_id' => $pricingPlan->activity_id,
                'activity_pricing_plan_id' => $pricingPlan->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate?->format('Y-m-d'),
                'sessions_total' => $sessionsTotal,
                'price' => $pricingPlan->price,
                'status' => $status,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncSubscriptionSessions($membership);

            return $this->prepareMembershipResponse($membership);
        });
    }

    public function editMembership(ActivityMembership $membership): ActivityMembership
    {
        $this->expireEndedMemberships();

        $membership->refresh();
        $this->expireMembershipIfConsumed($membership);
        $membership->refresh();

        return $this->prepareMembershipResponse($membership);
    }

    public function updateMembership(ActivityMembership $membership, array $data): ActivityMembership
    {
        return DB::transaction(function () use ($membership, $data): ActivityMembership {
            $this->expireEndedMemberships();

            $membership = ActivityMembership::query()
                ->with('pricingPlan')
                ->whereKey($membership->id)
                ->lockForUpdate()
                ->firstOrFail();

            $startDate = array_key_exists('startDate', $data)
                ? Carbon::parse($data['startDate'])
                : $membership->start_date->copy();

            $endDate = $membership->end_date;

            if ($membership->pricingPlan->type === ActivityPricingTypeEnum::SUBSCRIPTION) {
                $endDate = $this->calculateSubscriptionEndDate(
                    $startDate,
                    (int) $membership->pricingPlan->duration_value,
                    $membership->pricingPlan->duration_unit->value
                );
            }

            $newStatus = $data['status'] ?? $membership->status->value;

            if ((int) $newStatus === ActivityMembershipStatusEnum::ACTIVE->value) {
                $this->ensureChildHasNoActiveMembership(
                    (int) $membership->child_id,
                    (int) $membership->activity_id,
                    (int) $membership->id
                );
            }

            $membership->update([
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate?->format('Y-m-d'),
                'status' => $newStatus,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $membership->notes,
            ]);

            $membership->refresh()->load('pricingPlan');
            $this->syncSubscriptionSessions($membership);

            return $this->prepareMembershipResponse($membership);
        });
    }

    public function deleteMembership(ActivityMembership $membership): bool
    {
        return DB::transaction(function () use ($membership): bool {
            $membership = ActivityMembership::query()
                ->whereKey($membership->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->clearFutureMembershipSessionAssignments($membership);

            return (bool) $membership->delete();
        });
    }

    public function renewMembership(
        ActivityMembership $membership,
        array $data
    ): ActivityMembership {
        return DB::transaction(function () use ($membership, $data): ActivityMembership {
            $this->expireEndedMemberships();

            $membership = ActivityMembership::query()
                ->with('pricingPlan')
                ->whereKey($membership->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->expireMembershipIfConsumed($membership);

            $membership->refresh()->load([
                'pricingPlan',
                'renewal',
            ]);

            if ($membership->status !== ActivityMembershipStatusEnum::EXPIRED) {
                throw new ActivityMembershipNotRenewableException();
            }

            if ($membership->renewal !== null) {
                throw new ActivityMembershipAlreadyRenewedException();
            }

            $pricingPlanId = $data['pricingPlanId'] ?? $membership->activity_pricing_plan_id;
            $pricingPlan = ActivityPricingPlan::query()->findOrFail($pricingPlanId);

            $this->ensurePricingPlanSupportsMembership($pricingPlan);

            if ((int) $pricingPlan->activity_id !== (int) $membership->activity_id) {
                throw new RenewalPricingPlanActivityMismatchException();
            }

            $newMembership = $this->createMembership([
                'renewedFromMembershipId' => $membership->id,
                'childId' => $membership->child_id,
                'pricingPlanId' => $pricingPlan->id,
                'startDate' => $data['startDate'] ?? today()->format('Y-m-d'),
                'status' => ActivityMembershipStatusEnum::ACTIVE->value,
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['amount'])) {
                $this->paymentService->createPayment(
                    $newMembership,
                    [
                        'amount' => $data['amount'],
                        'paymentMethod' => $data['paymentMethod'],
                        'cashShiftId' => $data['cashShiftId'] ?? null,
                        'paidAt' => $data['paidAt'] ?? null,
                        'reference' => $data['reference'] ?? null,
                        'notes' => $data['paymentNotes'] ?? null,
                    ]
                );
            }

            return $this->prepareMembershipResponse(
                $newMembership->refresh()
            );
        });
    }

    private function prepareMembershipResponse(ActivityMembership $membership): ActivityMembership
    {
        $membership->load([
            'child',
            'activity',
            'pricingPlan',
            'renewedFrom',
            'renewal',
            'payments',
            'invoice',
        ]);

        $membership->setAttribute(
            'payment_summary',
            $this->paymentService->getPaymentSummary($membership)
        );

        if ($membership->pricingPlan->type === ActivityPricingTypeEnum::PACKAGE) {
            $sessionsUsed = $membership->attendances()
                ->where('status', ActivityAttendanceStatusEnum::PRESENT->value)
                ->count();

            $sessionsTotal = (int) $membership->sessions_total;

            $membership->setAttribute('sessions_used', $sessionsUsed);
            $membership->setAttribute(
                'sessions_remaining',
                max(0, $sessionsTotal - $sessionsUsed)
            );
        }

        return $membership;
    }

    private function expireEndedMemberships(): void
    {
        ActivityMembership::query()
            ->where('status', ActivityMembershipStatusEnum::ACTIVE->value)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', today())
            ->update([
                'status' => ActivityMembershipStatusEnum::EXPIRED->value,
            ]);
    }

    private function ensurePricingPlanSupportsMembership(ActivityPricingPlan $pricingPlan): void
    {
        if (! in_array(
            $pricingPlan->type,
            [ActivityPricingTypeEnum::SUBSCRIPTION, ActivityPricingTypeEnum::PACKAGE],
            true
        )) {
            throw new InvalidActivityMembershipPricingPlanException();
        }
    }

    private function syncSubscriptionSessions(ActivityMembership $membership): void
    {
        $membership->loadMissing('pricingPlan');

        $this->clearFutureMembershipSessionAssignments($membership);

        if ($membership->pricingPlan->type !== ActivityPricingTypeEnum::SUBSCRIPTION) {
            return;
        }

        if ($membership->status !== ActivityMembershipStatusEnum::ACTIVE) {
            return;
        }

        if ($membership->end_date === null) {
            return;
        }

        ActivitySession::query()
            ->where('activity_id', $membership->activity_id)
            ->whereDate('session_date', '>=', $membership->start_date)
            ->whereDate('session_date', '<=', $membership->end_date)
            ->where('status', '!=', ActivitySessionStatusEnum::CANCELLED->value)
            ->chunkById(100, function ($sessions) use ($membership): void {
                foreach ($sessions as $session) {
                    $this->attachMembershipToSession($session, $membership);
                }
            });
    }

    private function attachMembershipToSession(
        ActivitySession $session,
        ActivityMembership $membership
    ): void {
        $existingRow = DB::table('activity_session_children')
            ->where('activity_session_id', $session->id)
            ->where('child_id', $membership->child_id)
            ->lockForUpdate()
            ->first();

        if ($existingRow) {
            DB::table('activity_session_children')
                ->where('id', $existingRow->id)
                ->update([
                    'activity_membership_id' => $membership->id,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('activity_session_children')->insert([
            'activity_session_id' => $session->id,
            'child_id' => $membership->child_id,
            'activity_membership_id' => $membership->id,
            'assigned_manually' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function clearFutureMembershipSessionAssignments(ActivityMembership $membership): void
    {
        $rows = DB::table('activity_session_children')
            ->join(
                'activity_sessions',
                'activity_sessions.id',
                '=',
                'activity_session_children.activity_session_id'
            )
            ->where('activity_session_children.activity_membership_id', $membership->id)
            ->whereDate('activity_sessions.session_date', '>=', today())
            ->select([
                'activity_session_children.id',
                'activity_session_children.assigned_manually',
            ])
            ->lockForUpdate()
            ->get();

        foreach ($rows as $row) {
            if ((bool) $row->assigned_manually) {
                DB::table('activity_session_children')
                    ->where('id', $row->id)
                    ->update([
                        'activity_membership_id' => null,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('activity_session_children')
                ->where('id', $row->id)
                ->delete();
        }
    }

    private function calculateSubscriptionEndDate(
        Carbon $startDate,
        int $durationValue,
        int $durationUnit
    ): Carbon {
        $endDate = $startDate->copy();

        return match ($durationUnit) {
            0 => $endDate->addDays($durationValue)->subDay(),
            1 => $endDate->addWeeks($durationValue)->subDay(),
            2 => $endDate->addMonthsNoOverflow($durationValue)->subDay(),
            3 => $endDate->addYears($durationValue)->subDay(),
        };
    }

    private function ensureChildHasNoActiveMembership(
        int $childId,
        int $activityId,
        ?int $exceptMembershipId = null
    ): void {
        $query = ActivityMembership::query()
            ->where('child_id', $childId)
            ->where('activity_id', $activityId)
            ->where('status', ActivityMembershipStatusEnum::ACTIVE->value);

        if ($exceptMembershipId !== null) {
            $query->whereKeyNot($exceptMembershipId);
        }

        if ($query->exists()) {
            throw new ChildAlreadyHasActiveMembershipException();
        }
    }

    private function expireMembershipIfConsumed(ActivityMembership $membership): void
    {
        if ($membership->status !== ActivityMembershipStatusEnum::ACTIVE) {
            return;
        }

        $membership->loadMissing('pricingPlan');

        if ($membership->pricingPlan->type !== ActivityPricingTypeEnum::PACKAGE) {
            return;
        }

        $usedSessions = $membership->attendances()
            ->where('status', ActivityAttendanceStatusEnum::PRESENT->value)
            ->count();

        if ($usedSessions < (int) $membership->sessions_total) {
            return;
        }

        $membership->update([
            'status' => ActivityMembershipStatusEnum::EXPIRED->value,
        ]);
    }
}
