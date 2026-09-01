<?php

declare(strict_types=1);

namespace App\Services\ActivityAttendance;

use App\Models\ActivitySession;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TodayActivityAttendanceService
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
                        $request->input('filter.activityId')
                    );
                }
            )
            ->when(
                $request->filled('filter.sessionId'),
                function ($query) use ($request): void {
                    $query->whereKey(
                        $request->input('filter.sessionId')
                    );
                }
            )
            ->orderBy('start_time')
            ->get();

        $rows = $sessions->flatMap(
            function (ActivitySession $session): Collection {
                return $session->children->map(
                    function ($child) use ($session): array {
                        $attendance = $session->attendances
                            ->firstWhere(
                                'child_id',
                                $child->id
                            );

                        return [
                            'session' => $session,
                            'child' => $child,
                            'attendance' => $attendance,
                        ];
                    }
                );
            }
        );

        if ($request->filled('filter.search')) {
            $search = mb_strtolower(
                trim(
                    (string) $request->input(
                        'filter.search'
                    )
                )
            );

            $rows = $rows->filter(
                fn (array $row): bool =>
                    str_contains(
                        mb_strtolower(
                            (string) $row['child']->name
                        ),
                        $search
                    )
            );
        }

        if ($request->filled('filter.attendance')) {
            $rows = $this->filterAttendance(
                $rows,
                (string) $request->input(
                    'filter.attendance'
                )
            );
        }

        return $rows->values();
    }

    private function filterAttendance(
        Collection $rows,
        string $status
    ): Collection {
        return match ($status) {
            'pending' => $rows->filter(
                fn (array $row): bool =>
                    $row['attendance'] === null
            ),

            'present' => $rows->filter(
                fn (array $row): bool =>
                    $row['attendance'] !== null
                    && $row['attendance']->status->name === 'PRESENT'
            ),

            'absent' => $rows->filter(
                fn (array $row): bool =>
                    $row['attendance'] !== null
                    && $row['attendance']->status->name === 'ABSENT'
            ),

            default => $rows,
        };
    }
}
