<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Enums\ActivitySessionStatusEnum;
use App\Models\ActivitySchedule;
use App\Models\ActivitySession;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityScheduleService
{
    public function all(
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(ActivitySchedule::class)
            ->with([
                'activity',
            ])
            ->withCount([
                'sessions',
            ])
            ->allowedFilters(
                AllowedFilter::exact(
                    'activityId',
                    'activity_id'
                ),

                AllowedFilter::exact(
                    'status'
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createSchedule(
        array $data
    ): ActivitySchedule {
        return DB::transaction(function () use ($data): ActivitySchedule {
            $schedule = ActivitySchedule::create([
                'activity_id' =>
                    $data['activityId'],

                'name' =>
                    $data['name'],

                'start_date' =>
                    $data['startDate'],

                'end_date' =>
                    $data['endDate'],

                'start_time' =>
                    $data['startTime'],

                'end_time' =>
                    $data['endTime'],

                'week_days' =>
                    $data['weekDays'],

                'status' =>
                    $data['status'] ?? 1,

                'notes' =>
                    $data['notes'] ?? null,
            ]);

            return $schedule->load([
                'activity',
            ]);
        });
    }

    public function editSchedule(
        ActivitySchedule $schedule
    ): ActivitySchedule {
        return $schedule->load([
            'activity',
        ]);
    }

    public function updateSchedule(
        ActivitySchedule $schedule,
        array $data
    ): ActivitySchedule {
        return DB::transaction(function () use (
            $schedule,
            $data
        ): ActivitySchedule {
            $schedule->update([
                'activity_id' =>
                    $data['activityId']
                    ?? $schedule->activity_id,

                'name' =>
                    $data['name']
                    ?? $schedule->name,

                'start_date' =>
                    $data['startDate']
                    ?? $schedule->start_date->format('Y-m-d'),

                'end_date' =>
                    $data['endDate']
                    ?? $schedule->end_date->format('Y-m-d'),

                'start_time' =>
                    $data['startTime']
                    ?? $schedule->start_time,

                'end_time' =>
                    $data['endTime']
                    ?? $schedule->end_time,

                'week_days' =>
                    $data['weekDays']
                    ?? $schedule->week_days,

                'status' =>
                    $data['status']
                    ?? $schedule->status->value,

                'notes' =>
                    array_key_exists('notes', $data)
                        ? $data['notes']
                        : $schedule->notes,
            ]);

            return $schedule
                ->refresh()
                ->load([
                    'activity',
                ]);
        });
    }

    public function deleteSchedule(
        ActivitySchedule $schedule
    ): bool {
        return DB::transaction(
            fn (): bool => (bool) $schedule->delete()
        );
    }

    public function generateSessions(
        ActivitySchedule $schedule,
        array $employeeIds = []
    ): int {
        return DB::transaction(function () use (
            $schedule,
            $employeeIds
        ): int {
            $startDate = $schedule->start_date->copy();
            $endDate = $schedule->end_date->copy();

            $weekDays = array_map(
                'intval',
                $schedule->week_days
            );

            $generatedCount = 0;

            for (
                $date = $startDate->copy();
                $date->lte($endDate);
                $date->addDay()
            ) {
                $weekDay = $date->dayOfWeek;

                if (! in_array(
                    $weekDay,
                    $weekDays,
                    true
                )) {
                    continue;
                }

                $session = ActivitySession::query()
                    ->firstOrCreate(
                        [
                            'activity_schedule_id' =>
                                $schedule->id,

                            'session_date' =>
                                $date->format('Y-m-d'),

                            'start_time' =>
                                $schedule->start_time,

                            'end_time' =>
                                $schedule->end_time,
                        ],
                        [
                            'activity_id' =>
                                $schedule->activity_id,

                            'title' =>
                                $schedule->name,

                            'status' =>
                                ActivitySessionStatusEnum::SCHEDULED->value,

                            'notes' =>
                                $schedule->notes,
                        ]
                    );

                if ($session->wasRecentlyCreated) {
                    $generatedCount++;
                }

                if (! empty($employeeIds)) {
                    $session->employees()->syncWithoutDetaching(
                        $employeeIds
                    );
                }
            }

            return $generatedCount;
        });
    }
}
