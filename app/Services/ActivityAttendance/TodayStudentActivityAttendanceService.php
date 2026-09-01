<?php

declare(strict_types=1);

namespace App\Services\ActivityAttendance;

use App\Enums\ActivityAttendanceStatusEnum;
use App\Enums\ActivityMembershipStatusEnum;
use App\Models\ActivityMembership;
use App\Models\ActivitySession;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TodayStudentActivityAttendanceService
{
    public function all(Request $request): Collection
    {
        $sessions = ActivitySession::query()
            ->whereDate('session_date', today())
            ->with([
                'activity',
                'children',
                'attendances',
            ])
            ->when(
                $request->filled('filter.activityId'),
                function ($query) use ($request): void {
                    $query->where(
                        'activity_id',
                        (int) $request->input(
                            'filter.activityId'
                        )
                    );
                }
            )
            ->when(
                $request->filled('filter.sessionId'),
                function ($query) use ($request): void {
                    $query->whereKey(
                        (int) $request->input(
                            'filter.sessionId'
                        )
                    );
                }
            )
            ->orderBy('start_time')
            ->get();

        if ($sessions->isEmpty()) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | Collect activity and child ids
        |--------------------------------------------------------------------------
        */

        $activityIds = $sessions
            ->pluck('activity_id')
            ->unique()
            ->values();

        $childIds = $sessions
            ->flatMap(
                fn (ActivitySession $session) =>
                    $session->children->pluck('id')
            )
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Load active memberships once
        |--------------------------------------------------------------------------
        */

        $memberships = ActivityMembership::query()
            ->whereIn(
                'activity_id',
                $activityIds
            )
            ->whereIn(
                'child_id',
                $childIds
            )
            ->where(
                'status',
                ActivityMembershipStatusEnum::ACTIVE->value
            )
            ->get()
            ->groupBy(
                fn (ActivityMembership $membership): string =>
                    $membership->activity_id
                    . ':'
                    . $membership->child_id
            );

        /*
        |--------------------------------------------------------------------------
        | Build student rows
        |--------------------------------------------------------------------------
        */

        $rows = collect();

        foreach ($sessions as $session) {
            foreach ($session->children as $child) {
                $membershipKey =
                    $session->activity_id
                    . ':'
                    . $child->id;

                $membership = $memberships
                    ->get(
                        $membershipKey,
                        collect()
                    )
                    ->filter(
                        function (
                            ActivityMembership $membership
                        ) use ($session): bool {
                            $sessionDate =
                                $session->session_date;

                            if (
                                $membership->start_date
                                && $membership->start_date
                                    ->gt($sessionDate)
                            ) {
                                return false;
                            }

                            if (
                                $membership->end_date
                                && $membership->end_date
                                    ->lt($sessionDate)
                            ) {
                                return false;
                            }

                            return true;
                        }
                    )
                    ->sortByDesc('id')
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Skip child if no valid active membership
                |--------------------------------------------------------------------------
                */

                if (! $membership) {
                    continue;
                }

                $attendance = $session
                    ->attendances
                    ->firstWhere(
                        'child_id',
                        $child->id
                    );

                $rows->push([
                    'session' => $session,
                    'child' => $child,
                    'membership' => $membership,
                    'attendance' => $attendance,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Search by child name
        |--------------------------------------------------------------------------
        */

        if ($request->filled('filter.search')) {
            $search = mb_strtolower(
                trim(
                    (string) $request->input(
                        'filter.search'
                    )
                )
            );

            $rows = $rows->filter(
                function (array $row) use (
                    $search
                ): bool {
                    return str_contains(
                        mb_strtolower(
                            (string) $row['child']->name
                        ),
                        $search
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Attendance status
        |--------------------------------------------------------------------------
        |
        | 0 = ABSENT
        | 1 = PRESENT
        | 2 = EXCUSED
        |
        */

        if ($request->filled('filter.status')) {
            $status = (int) $request->input(
                'filter.status'
            );

            if (! in_array(
                $status,
                ActivityAttendanceStatusEnum::values(),
                true
            )) {
                return collect();
            }

            $rows = $rows->filter(
                function (array $row) use (
                    $status
                ): bool {
                    $attendance =
                        $row['attendance'];

                    return $attendance !== null
                        && $attendance
                            ->status
                            ->value === $status;
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Has attendance record
        |--------------------------------------------------------------------------
        |
        | 0 = لم يتم تسجيل حالة حضور حتى الآن
        | 1 = تم تسجيل حالة حضور
        |
        */

        if ($request->has(
            'filter.hasAttendance'
        )) {
            $hasAttendance = filter_var(
                $request->input(
                    'filter.hasAttendance'
                ),
                FILTER_VALIDATE_BOOLEAN
            );

            $rows = $rows->filter(
                function (array $row) use (
                    $hasAttendance
                ): bool {
                    return $hasAttendance
                        ? $row['attendance'] !== null
                        : $row['attendance'] === null;
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Final sorting
        |--------------------------------------------------------------------------
        */

        return $rows
            ->sortBy([
                fn (array $row) =>
                    $row['session']->start_time,

                fn (array $row) =>
                    $row['child']->name,
            ])
            ->values();
    }
}
