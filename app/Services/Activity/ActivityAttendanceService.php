<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Enums\ActivityAttendanceStatusEnum;
use App\Enums\ActivityMembershipStatusEnum;
use App\Enums\ActivityPricingTypeEnum;
use App\Exceptions\Activity\ActivityAttendanceAlreadyExistsException;
use App\Exceptions\Activity\ActivityMembershipExpiredException;
use App\Exceptions\Activity\ActivityMembershipNotActiveException;
use App\Exceptions\Activity\ActivityMembershipNotBelongToActivityException;
use App\Exceptions\Activity\ActivityMembershipNotBelongToChildException;
use App\Exceptions\Activity\ActivityPackageHasNoRemainingSessionsException;
use App\Exceptions\Activity\ChildNotAssignedToActivitySessionException;
use App\Models\ActivityAttendance;
use App\Models\ActivityMembership;
use App\Models\ActivitySession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityAttendanceService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(ActivityAttendance::class)
            ->with(['child', 'membership.pricingPlan', 'session.activity'])
            ->allowedFilters(
                AllowedFilter::exact('childId', 'child_id'),
                AllowedFilter::exact('activitySessionId', 'activity_session_id'),
                AllowedFilter::exact('activityMembershipId', 'activity_membership_id'),
                AllowedFilter::exact('status')
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createAttendance(array $data): ActivityAttendance
    {
        return DB::transaction(function () use ($data): ActivityAttendance {
            $session = ActivitySession::query()
                ->with('activity')
                ->findOrFail($data['activitySessionId']);

            $this->ensureChildAssignedToSession($session, (int) $data['childId']);
            $this->ensureAttendanceDoesNotExist($session->id, (int) $data['childId']);

            $membership = null;

            if (! empty($data['activityMembershipId'])) {
                $membership = $this->findMembershipForUpdate((int) $data['activityMembershipId']);

                $this->validateMembership(
                    membership: $membership,
                    session: $session,
                    childId: (int) $data['childId'],
                    attendanceStatus: (int) $data['status']
                );
            }

            $attendance = ActivityAttendance::create([
                'activity_session_id' => $session->id,
                'child_id' => $data['childId'],
                'activity_membership_id' => $membership?->id,
                'check_in_at' => $data['checkInAt'] ?? null,
                'check_out_at' => $data['checkOutAt'] ?? null,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncPackageMembershipStatus($membership);

            return $attendance->load([
                'child',
                'membership.pricingPlan',
                'session.activity',
            ]);
        });
    }

    public function editAttendance(ActivityAttendance $attendance): ActivityAttendance
    {
        return $attendance->load([
            'child',
            'membership.pricingPlan',
            'session.activity',
        ]);
    }

    public function updateAttendance(ActivityAttendance $attendance, array $data): ActivityAttendance
    {
        return DB::transaction(function () use ($attendance, $data): ActivityAttendance {
            $attendance = ActivityAttendance::query()
                ->whereKey($attendance->id)
                ->lockForUpdate()
                ->firstOrFail();

            $attendance->load(['session.activity', 'membership.pricingPlan']);

            $oldMembershipId = $attendance->activity_membership_id !== null
                ? (int) $attendance->activity_membership_id
                : null;

            $membership = $oldMembershipId !== null
                ? $this->findMembershipForUpdate($oldMembershipId)
                : null;

            if (array_key_exists('activityMembershipId', $data)) {
                $membership = ! empty($data['activityMembershipId'])
                    ? $this->findMembershipForUpdate((int) $data['activityMembershipId'])
                    : null;
            }

            $status = array_key_exists('status', $data)
                ? (int) $data['status']
                : $attendance->status->value;

            if ($membership !== null) {
                $isSameMembership = $oldMembershipId !== null
                    && $oldMembershipId === (int) $membership->id;

                $allowExpiredPackageCorrection =
                    $isSameMembership
                    && $membership->status === ActivityMembershipStatusEnum::EXPIRED
                    && $membership->pricingPlan->type === ActivityPricingTypeEnum::PACKAGE;

                $this->validateMembership(
                    membership: $membership,
                    session: $attendance->session,
                    childId: (int) $attendance->child_id,
                    attendanceStatus: $status,
                    currentAttendanceId: $attendance->id,
                    requireActive: ! $allowExpiredPackageCorrection
                );
            }

            $attendance->update([
                'activity_membership_id' => $membership?->id,
                'check_in_at' => array_key_exists('checkInAt', $data)
                    ? $data['checkInAt']
                    : $attendance->check_in_at,
                'check_out_at' => array_key_exists('checkOutAt', $data)
                    ? $data['checkOutAt']
                    : $attendance->check_out_at,
                'status' => $status,
                'notes' => array_key_exists('notes', $data)
                    ? $data['notes']
                    : $attendance->notes,
            ]);

            if ($oldMembershipId !== null) {
                $oldMembership = $membership !== null && $oldMembershipId === (int) $membership->id
                    ? $membership
                    : $this->findMembershipForUpdate($oldMembershipId);

                $this->syncPackageMembershipStatus($oldMembership);
            }

            if ($membership !== null && $oldMembershipId !== (int) $membership->id) {
                $this->syncPackageMembershipStatus($membership);
            }

            return $attendance
                ->refresh()
                ->load([
                    'child',
                    'membership.pricingPlan',
                    'session.activity',
                ]);
        });
    }

    public function deleteAttendance(ActivityAttendance $attendance): bool
    {
        return DB::transaction(function () use ($attendance): bool {
            $attendance = ActivityAttendance::query()
                ->whereKey($attendance->id)
                ->lockForUpdate()
                ->firstOrFail();

            $membershipId = $attendance->activity_membership_id !== null
                ? (int) $attendance->activity_membership_id
                : null;

            $deleted = (bool) $attendance->delete();

            if ($membershipId !== null) {
                $membership = $this->findMembershipForUpdate($membershipId);
                $this->syncPackageMembershipStatus($membership);
            }

            return $deleted;
        });
    }

    private function validateMembership(
        ActivityMembership $membership,
        ActivitySession $session,
        int $childId,
        int $attendanceStatus,
        ?int $currentAttendanceId = null,
        bool $requireActive = true
    ): void {
        $this->ensureMembershipBelongsToChild($membership, $childId);
        $this->ensureMembershipBelongsToActivity($membership, $session);

        if ($requireActive) {
            $this->ensureMembershipIsActive($membership);
        }

        $this->ensureMembershipIsValidForSessionDate($membership, $session);

        if (
            $membership->pricingPlan->type === ActivityPricingTypeEnum::PACKAGE
            && $attendanceStatus === ActivityAttendanceStatusEnum::PRESENT->value
        ) {
            $this->ensurePackageHasRemainingSessions($membership, $currentAttendanceId);
        }
    }

    private function ensureAttendanceDoesNotExist(int $activitySessionId, int $childId): void
    {
        $exists = ActivityAttendance::query()
            ->where('activity_session_id', $activitySessionId)
            ->where('child_id', $childId)
            ->exists();

        if ($exists) {
            throw new ActivityAttendanceAlreadyExistsException();
        }
    }

    private function ensureMembershipBelongsToChild(ActivityMembership $membership, int $childId): void
    {
        if ((int) $membership->child_id !== $childId) {
            throw new ActivityMembershipNotBelongToChildException();
        }
    }

    private function ensureMembershipBelongsToActivity(
        ActivityMembership $membership,
        ActivitySession $session
    ): void {
        if ((int) $membership->activity_id !== (int) $session->activity_id) {
            throw new ActivityMembershipNotBelongToActivityException();
        }
    }

    private function ensureMembershipIsActive(ActivityMembership $membership): void
    {
        if ($membership->status !== ActivityMembershipStatusEnum::ACTIVE) {
            throw new ActivityMembershipNotActiveException();
        }
    }

    private function ensureMembershipIsValidForSessionDate(
        ActivityMembership $membership,
        ActivitySession $session
    ): void {
        $sessionDate = $session->session_date;

        if ($sessionDate->lt($membership->start_date)) {
            throw new ActivityMembershipExpiredException();
        }

        if ($membership->end_date !== null && $sessionDate->gt($membership->end_date)) {
            throw new ActivityMembershipExpiredException();
        }
    }

    private function ensurePackageHasRemainingSessions(
        ActivityMembership $membership,
        ?int $currentAttendanceId = null
    ): void {
        $query = ActivityAttendance::query()
            ->where('activity_membership_id', $membership->id)
            ->where('status', ActivityAttendanceStatusEnum::PRESENT->value);

        if ($currentAttendanceId !== null) {
            $query->where('id', '!=', $currentAttendanceId);
        }

        $usedSessions = $query->count();
        $sessionsTotal = (int) $membership->sessions_total;

        if ($usedSessions >= $sessionsTotal) {
            throw new ActivityPackageHasNoRemainingSessionsException();
        }
    }

    private function ensureChildAssignedToSession(ActivitySession $session, int $childId): void
    {
        $exists = $session->children()
            ->where('children.id', $childId)
            ->exists();

        if (! $exists) {
            throw new ChildNotAssignedToActivitySessionException();
        }
    }

    private function syncPackageMembershipStatus(?ActivityMembership $membership): void
    {
        if ($membership === null) {
            return;
        }

        $membership->loadMissing('pricingPlan');

        if ($membership->pricingPlan->type !== ActivityPricingTypeEnum::PACKAGE) {
            return;
        }

        if (
            in_array(
                $membership->status,
                [
                    ActivityMembershipStatusEnum::CANCELLED,
                    ActivityMembershipStatusEnum::INACTIVE,
                ],
                true
            )
        ) {
            return;
        }

        $usedSessions = $membership->attendances()
            ->where('status', ActivityAttendanceStatusEnum::PRESENT->value)
            ->count();

        $newStatus = $usedSessions >= (int) $membership->sessions_total
            ? ActivityMembershipStatusEnum::EXPIRED->value
            : ActivityMembershipStatusEnum::ACTIVE->value;

        if ($membership->status->value === $newStatus) {
            return;
        }

        $membership->update([
            'status' => $newStatus,
        ]);
    }

    private function findMembershipForUpdate(int $membershipId): ActivityMembership
    {
        return ActivityMembership::query()
            ->with('pricingPlan')
            ->whereKey($membershipId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
