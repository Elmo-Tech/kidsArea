<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Enums\EmployeeSessionStatusEnum;
use App\Exceptions\EmployeeSession\EmployeeSessionCompletedException;
use App\Models\EmployeeSession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeSessionService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(EmployeeSession::class)
            ->with([
                'employee.jobTitle',
            ])
            ->allowedFilters(
                AllowedFilter::exact(
                    'employeeId',
                    'employee_id'
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
            ->latest('id')
            ->paginate($perPage);
    }

    public function createEmployeeSession(
        array $data
    ): EmployeeSession {
        return DB::transaction(function () use ($data): EmployeeSession {

            $session = EmployeeSession::create([
                'employee_id' => $data['employeeId'],

                'session_date' => $data['sessionDate'],

                'start_time' => $data['startTime'] ?? null,
                'end_time' => $data['endTime'] ?? null,

                'title' => $data['title'] ?? null,

                'status' =>
                    $data['status']
                    ?? EmployeeSessionStatusEnum::PENDING->value,

                'notes' => $data['notes'] ?? null,
            ]);

            return $session->load([
                'employee.jobTitle',
            ]);
        });
    }

    public function editEmployeeSession(
        EmployeeSession $session
    ): EmployeeSession {
        return $session->load([
            'employee.jobTitle',
        ]);
    }

    public function updateEmployeeSession(
        EmployeeSession $session,
        array $data
    ): EmployeeSession {
        return DB::transaction(function () use ($session, $data): EmployeeSession {

            $this->ensureSessionIsEditable($session);

            $session->update([
                'employee_id' =>
                    $data['employeeId']
                    ?? $session->employee_id,

                'session_date' =>
                    $data['sessionDate']
                    ?? $session->session_date,

                'start_time' =>
                    array_key_exists('startTime', $data)
                        ? $data['startTime']
                        : $session->start_time,

                'end_time' =>
                    array_key_exists('endTime', $data)
                        ? $data['endTime']
                        : $session->end_time,

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

            return $session
                ->refresh()
                ->load([
                    'employee.jobTitle',
                ]);
        });
    }

    public function deleteEmployeeSession(
        EmployeeSession $session
    ): bool {
        return DB::transaction(function () use ($session): bool {

            $this->ensureSessionIsEditable($session);

            return (bool) $session->delete();
        });
    }

    public function completeEmployeeSession(
        EmployeeSession $session
    ): EmployeeSession {
        return DB::transaction(function () use ($session): EmployeeSession {

            if ($session->status === EmployeeSessionStatusEnum::COMPLETED) {
                throw new EmployeeSessionCompletedException();
            }

            $session->update([
                'status' =>
                    EmployeeSessionStatusEnum::COMPLETED->value,
            ]);

            return $session
                ->refresh()
                ->load([
                    'employee.jobTitle',
                ]);
        });
    }

    public function cancelEmployeeSession(
        EmployeeSession $session
    ): EmployeeSession {
        return DB::transaction(function () use ($session): EmployeeSession {

            $this->ensureSessionIsEditable($session);

            $session->update([
                'status' =>
                    EmployeeSessionStatusEnum::CANCELLED->value,
            ]);

            return $session
                ->refresh()
                ->load([
                    'employee.jobTitle',
                ]);
        });
    }

    private function ensureSessionIsEditable(
        EmployeeSession $session
    ): void {
        if (
            $session->status ===
            EmployeeSessionStatusEnum::COMPLETED
        ) {
            throw new EmployeeSessionCompletedException();
        }
    }
}
