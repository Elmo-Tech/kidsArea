<?php

declare(strict_types=1);

namespace App\Services\Activity;

use App\Models\ActivitySession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivitySessionService
{
    public function all(
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(ActivitySession::class)
            ->with([
                'activity',
                'schedule',
            ])
            ->withCount([
                'employees',
                'children',
            ])
            ->allowedFilters(
                AllowedFilter::exact(
                    'activityId',
                    'activity_id'
                ),

                AllowedFilter::exact(
                    'activityScheduleId',
                    'activity_schedule_id'
                ),

                AllowedFilter::exact(
                    'status'
                ),

                AllowedFilter::exact(
                    'sessionDate',
                    'session_date'
                ),
            )
            ->latest('session_date')
            ->latest('start_time')
            ->paginate($perPage);
    }

    public function createSession(
        array $data
    ): ActivitySession {
        return DB::transaction(function () use ($data): ActivitySession {
            $session = ActivitySession::create([
                'activity_id' =>
                    $data['activityId'],

                'activity_schedule_id' =>
                    $data['activityScheduleId']
                    ?? null,

                'session_date' =>
                    $data['sessionDate'],

                'start_time' =>
                    $data['startTime'],

                'end_time' =>
                    $data['endTime'],

                'title' =>
                    $data['title'] ?? null,

                'status' =>
                    $data['status'] ?? 0,

                'notes' =>
                    $data['notes'] ?? null,
            ]);

            if (
                array_key_exists('employeeIds', $data)
            ) {
                $session->employees()->sync(
                    $data['employeeIds']
                );
            }

            if (
                array_key_exists('childIds', $data)
            ) {
                $session->children()->sync(
                    $data['childIds']
                );
            }

            return $session->load([
                'activity',
                'schedule',
                'employees.jobTitle',
                'children',
            ]);
        });
    }

    public function editSession(
        ActivitySession $session
    ): ActivitySession {
        return $session->load([
            'activity',
            'schedule',
            'employees.jobTitle',
            'children',
        ]);
    }

    public function updateSession(
        ActivitySession $session,
        array $data
    ): ActivitySession {
        return DB::transaction(function () use (
            $session,
            $data
        ): ActivitySession {
            $session->update([
                'activity_id' =>
                    $data['activityId']
                    ?? $session->activity_id,

                'activity_schedule_id' =>
                    array_key_exists(
                        'activityScheduleId',
                        $data
                    )
                        ? $data['activityScheduleId']
                        : $session->activity_schedule_id,

                'session_date' =>
                    $data['sessionDate']
                    ?? $session->session_date->format('Y-m-d'),

                'start_time' =>
                    $data['startTime']
                    ?? $session->start_time,

                'end_time' =>
                    $data['endTime']
                    ?? $session->end_time,

                'title' =>
                    array_key_exists('title', $data)
                        ? $data['title']
                        : $session->title,

                'status' =>
                    $data['status']
                    ?? $session->status->value,

                'notes' =>
                    array_key_exists('notes', $data)
                        ? $data['notes']
                        : $session->notes,
            ]);

            if (
                array_key_exists('employeeIds', $data)
            ) {
                $session->employees()->sync(
                    $data['employeeIds']
                );
            }

            if (
                array_key_exists('childIds', $data)
            ) {
                $session->children()->sync(
                    $data['childIds']
                );
            }

            return $session
                ->refresh()
                ->load([
                    'activity',
                    'schedule',
                    'employees.jobTitle',
                    'children',
                ]);
        });
    }

    public function deleteSession(
        ActivitySession $session
    ): bool {
        return DB::transaction(
            fn (): bool => (bool) $session->delete()
        );
    }
}
