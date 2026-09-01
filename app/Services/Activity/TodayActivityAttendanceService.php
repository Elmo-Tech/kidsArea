<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Models\ActivitySession;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

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
            ->orderBy('start_time')
            ->get();

        $rows = $sessions->flatMap(function (ActivitySession $session): array {
            return $session->children
                ->map(function ($child) use ($session): array {
                    $attendance = $session->attendances
                        ->firstWhere('child_id', $child->id);

                    return [
                        'session' => $session,
                        'child' => $child,
                        'attendance' => $attendance,
                    ];
                })
                ->all();
        });

        return $rows
            ->when(
                $request->filled('filter.activityId'),
                fn ($collection) => $collection->filter(
                    fn ($row) =>
                        (int) $row['session']->activity_id ===
                        (int) $request->input('filter.activityId')
                )
            )
            ->when(
                $request->filled('filter.sessionId'),
                fn ($collection) => $collection->filter(
                    fn ($row) =>
                        (int) $row['session']->id ===
                        (int) $request->input('filter.sessionId')
                )
            )
            ->when(
                $request->filled('filter.search'),
                function ($collection) use ($request) {
                    $search = mb_strtolower(
                        (string) $request->input('filter.search')
                    );

                    return $collection->filter(
                        fn ($row) =>
                            str_contains(
                                mb_strtolower($row['child']->name),
                                $search
                            )
                    );
                }
            )
            ->when(
                $request->filled('filter.status'),
                fn ($collection) => $this->filterByStatus(
                    $collection,
                    (string) $request->input('filter.status')
                )
            )
            ->values();
    }

    private function filterByStatus(
        Collection $rows,
        string $status
    ): Collection {
        return match ($status) {
            'pending' =>
                $rows->filter(
                    fn ($row) =>
                        $row['attendance'] === null
                ),

            'present' =>
                $rows->filter(
                    fn ($row) =>
                        $row['attendance'] !== null
                        && $row['attendance']->status->name === 'PRESENT'
                ),

            'absent' =>
                $rows->filter(
                    fn ($row) =>
                        $row['attendance'] !== null
                        && $row['attendance']->status->name === 'ABSENT'
                ),

            default => $rows,
        };
    }
}
