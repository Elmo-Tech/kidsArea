<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Enums\ActivityMembershipStatusEnum;
use App\Enums\ActivityPricingTypeEnum;
use App\Enums\ActivitySessionStatusEnum;
use App\Models\ActivityMembership;
use App\Models\ActivitySession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivitySessionService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(ActivitySession::class)
            ->with(['activity', 'schedule'])
            ->withCount(['employees', 'children'])
            ->allowedFilters(
                AllowedFilter::exact('activityId', 'activity_id'),
                AllowedFilter::exact('activityScheduleId', 'activity_schedule_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('sessionDate', 'session_date')
            )
            ->latest('session_date')
            ->latest('start_time')
            ->paginate($perPage);
    }

    public function createSession(array $data): ActivitySession
    {
        return DB::transaction(function () use ($data): ActivitySession {
            $session = ActivitySession::create([
                'activity_id' => $data['activityId'],
                'activity_schedule_id' => $data['activityScheduleId'] ?? null,
                'session_date' => $data['sessionDate'],
                'start_time' => $data['startTime'],
                'end_time' => $data['endTime'],
                'title' => $data['title'] ?? null,
                'status' => $data['status'] ?? ActivitySessionStatusEnum::SCHEDULED->value,
                'notes' => $data['notes'] ?? null,
            ]);

            if (array_key_exists('employeeIds', $data)) {
                $session->employees()->sync($data['employeeIds']);
            }

            if (array_key_exists('childIds', $data)) {
                $this->syncManualChildren($session, $data['childIds']);
            }

            $this->syncSubscriptionChildren($session);

            return $session->load([
                'activity',
                'schedule',
                'employees.jobTitle',
                'children',
            ]);
        });
    }

    public function editSession(ActivitySession $session): ActivitySession
    {
        return $session->load([
            'activity',
            'schedule',
            'employees.jobTitle',
            'children',
        ]);
    }

    public function updateSession(ActivitySession $session, array $data): ActivitySession
    {
        return DB::transaction(function () use ($session, $data): ActivitySession {
            $session = ActivitySession::query()
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            $session->update([
                'activity_id' => $data['activityId'] ?? $session->activity_id,
                'activity_schedule_id' => array_key_exists('activityScheduleId', $data)
                    ? $data['activityScheduleId']
                    : $session->activity_schedule_id,
                'session_date' => $data['sessionDate'] ?? $session->session_date->format('Y-m-d'),
                'start_time' => $data['startTime'] ?? $session->start_time,
                'end_time' => $data['endTime'] ?? $session->end_time,
                'title' => array_key_exists('title', $data) ? $data['title'] : $session->title,
                'status' => $data['status'] ?? $session->status->value,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $session->notes,
            ]);

            if (array_key_exists('employeeIds', $data)) {
                $session->employees()->sync($data['employeeIds']);
            }

            if (array_key_exists('childIds', $data)) {
                $this->syncManualChildren($session, $data['childIds']);
            }

            $session->refresh();
            $this->syncSubscriptionChildren($session);

            return $session->refresh()->load([
                'activity',
                'schedule',
                'employees.jobTitle',
                'children',
            ]);
        });
    }

    public function deleteSession(ActivitySession $session): bool
    {
        return DB::transaction(function () use ($session): bool {
            $session = ActivitySession::query()
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            return (bool) $session->delete();
        });
    }

    private function syncManualChildren(ActivitySession $session, array $childIds): void
    {
        $manualChildIds = collect($childIds)
            ->map(fn ($childId): int => (int) $childId)
            ->filter(fn (int $childId): bool => $childId > 0)
            ->unique()
            ->values();

        $existingRows = DB::table('activity_session_children')
            ->where('activity_session_id', $session->id)
            ->lockForUpdate()
            ->get()
            ->keyBy('child_id');

        foreach ($existingRows as $childId => $row) {
            $childId = (int) $childId;

            if ($manualChildIds->contains($childId)) {
                if (! (bool) $row->assigned_manually) {
                    DB::table('activity_session_children')
                        ->where('id', $row->id)
                        ->update([
                            'assigned_manually' => true,
                            'updated_at' => now(),
                        ]);
                }

                continue;
            }

            if (! (bool) $row->assigned_manually) {
                continue;
            }

            if ($row->activity_membership_id !== null) {
                DB::table('activity_session_children')
                    ->where('id', $row->id)
                    ->update([
                        'assigned_manually' => false,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('activity_session_children')
                ->where('id', $row->id)
                ->delete();
        }

        foreach ($manualChildIds as $childId) {
            if ($existingRows->has($childId)) {
                continue;
            }

            DB::table('activity_session_children')->insert([
                'activity_session_id' => $session->id,
                'child_id' => $childId,
                'activity_membership_id' => null,
                'assigned_manually' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function syncSubscriptionChildren(ActivitySession $session): void
    {
        $automaticRows = DB::table('activity_session_children')
            ->where('activity_session_id', $session->id)
            ->whereNotNull('activity_membership_id')
            ->lockForUpdate()
            ->get();

        foreach ($automaticRows as $row) {
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

        if ($session->status === ActivitySessionStatusEnum::CANCELLED) {
            return;
        }

        $sessionDate = $session->session_date->format('Y-m-d');

        $memberships = ActivityMembership::query()
            ->where('activity_id', $session->activity_id)
            ->where('status', ActivityMembershipStatusEnum::ACTIVE->value)
            ->whereDate('start_date', '<=', $sessionDate)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', $sessionDate)
            ->whereHas('pricingPlan', function ($query): void {
                $query->where('type', ActivityPricingTypeEnum::SUBSCRIPTION->value);
            })
            ->get(['id', 'child_id']);

        foreach ($memberships as $membership) {
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

                continue;
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
    }
}
