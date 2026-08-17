<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Enums\ActivitySessionStatusEnum;
use App\Exceptions\Activity\ActivityScheduleAlreadyExistsException;
use App\Models\ActivitySchedule;
use App\Models\ActivitySession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityScheduleService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(max((int) $request->integer('perPage', 15), 1), 100);

        return QueryBuilder::for(ActivitySchedule::class)
            ->with(['activity'])
            ->withCount(['sessions'])
            ->allowedFilters([
                AllowedFilter::exact('activityId', 'activity_id'),
                AllowedFilter::exact('status'),
            ])
            ->latest('id')
            ->paginate($perPage);
    }

    public function createSchedule(array $data): ActivitySchedule
    {
        return DB::transaction(function () use ($data): ActivitySchedule {
            $this->ensureScheduleDoesNotExist(
                activityId: (int) $data['activityId'],
                startDate: $data['startDate'],
                endDate: $data['endDate'],
                startTime: $data['startTime'],
                endTime: $data['endTime'],
                weekDays: $data['weekDays'],
            );

            $schedule = ActivitySchedule::create([
                'activity_id' => $data['activityId'],
                'name' => $data['name'],
                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],
                'start_time' => $data['startTime'],
                'end_time' => $data['endTime'],
                'week_days' => $this->normalizeWeekDays($data['weekDays']),
                'status' => $data['status'] ?? 1,
                'notes' => $data['notes'] ?? null,
            ]);

            return $schedule->load(['activity']);
        });
    }

    public function editSchedule(ActivitySchedule $schedule): ActivitySchedule
    {
        return $schedule->load(['activity']);
    }

    public function updateSchedule(ActivitySchedule $schedule, array $data): ActivitySchedule
    {
        return DB::transaction(function () use ($schedule, $data): ActivitySchedule {
            $activityId = (int) ($data['activityId'] ?? $schedule->activity_id);
            $startDate = $data['startDate'] ?? $schedule->start_date->format('Y-m-d');
            $endDate = $data['endDate'] ?? $schedule->end_date->format('Y-m-d');
            $startTime = $data['startTime'] ?? $schedule->start_time;
            $endTime = $data['endTime'] ?? $schedule->end_time;
            $weekDays = $data['weekDays'] ?? $schedule->week_days;

            $this->ensureScheduleDoesNotExist(
                activityId: $activityId,
                startDate: $startDate,
                endDate: $endDate,
                startTime: $startTime,
                endTime: $endTime,
                weekDays: $weekDays,
                ignoreScheduleId: $schedule->id,
            );

            $schedule->update([
                'activity_id' => $activityId,
                'name' => $data['name'] ?? $schedule->name,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'week_days' => $this->normalizeWeekDays($weekDays),
                'status' => $data['status'] ?? $schedule->status->value,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $schedule->notes,
            ]);

            return $schedule->refresh()->load(['activity']);
        });
    }

    public function deleteSchedule(ActivitySchedule $schedule): bool
    {
        return DB::transaction(fn (): bool => (bool) $schedule->delete());
    }

    public function generateSessions(ActivitySchedule $schedule, array $employeeIds = []): int
    {
        return DB::transaction(function () use ($schedule, $employeeIds): int {
            $startDate = $schedule->start_date->copy();
            $endDate = $schedule->end_date->copy();
            $weekDays = $this->normalizeWeekDays($schedule->week_days);
            $generatedCount = 0;

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if (! in_array($date->dayOfWeek, $weekDays, true)) {
                    continue;
                }

                $session = ActivitySession::query()->firstOrCreate(
                    [
                        'activity_schedule_id' => $schedule->id,
                        'session_date' => $date->format('Y-m-d'),
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                    ],
                    [
                        'activity_id' => $schedule->activity_id,
                        'title' => $schedule->name,
                        'status' => ActivitySessionStatusEnum::SCHEDULED->value,
                        'notes' => $schedule->notes,
                    ]
                );

                if ($session->wasRecentlyCreated) {
                    $generatedCount++;
                }

                if (! empty($employeeIds)) {
                    $session->employees()->syncWithoutDetaching($employeeIds);
                }
            }

            return $generatedCount;
        });
    }

    private function ensureScheduleDoesNotExist(
        int $activityId,
        string $startDate,
        string $endDate,
        string $startTime,
        string $endTime,
        array $weekDays,
        ?int $ignoreScheduleId = null
    ): void {
        $normalizedWeekDays = $this->normalizeWeekDays($weekDays);

        $query = ActivitySchedule::query()
            ->where('activity_id', $activityId)
            ->whereDate('start_date', $startDate)
            ->whereDate('end_date', $endDate)
            ->where('start_time', $startTime)
            ->where('end_time', $endTime);

        if ($ignoreScheduleId !== null) {
            $query->where('id', '!=', $ignoreScheduleId);
        }

        $duplicateExists = $query
            ->get(['id', 'week_days'])
            ->contains(
                fn (ActivitySchedule $schedule): bool =>
                    $this->normalizeWeekDays($schedule->week_days) === $normalizedWeekDays
            );

        if ($duplicateExists) {
            throw new ActivityScheduleAlreadyExistsException();
        }
    }

    private function normalizeWeekDays(array $weekDays): array
    {
        $weekDays = array_values(array_unique(array_map('intval', $weekDays)));
        sort($weekDays);

        return $weekDays;
    }
}
