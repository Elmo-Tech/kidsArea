<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Models\ActivitySession;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Exceptions\ActivitySessionEmployeeAttendance\EmployeeNotAssignedToSessionException;

class MyActivitySessionService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $employeeId = Auth::user()?->employee_id;

        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(ActivitySession::class)
            ->whereHas('employees', function ($query) use ($employeeId): void {
                $query->where(
                    'employees.id',
                    $employeeId
                );
            })
            ->with([
                'activity',

                'employeeAttendances' => function ($query) use ($employeeId): void {
                    $query->where(
                        'employee_id',
                        $employeeId
                    );
                },
            ])
            ->allowedFilters([
                AllowedFilter::exact(
                    'status'
                ),

                AllowedFilter::callback(
                    'from',
                    function ($query, $value): void {
                        $query->whereDate(
                            'session_date',
                            '>=',
                            $value
                        );
                    }
                ),

                AllowedFilter::callback(
                    'to',
                    function ($query, $value): void {
                        $query->whereDate(
                            'session_date',
                            '<=',
                            $value
                        );
                    }
                ),
            ])
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->paginate($perPage);
    }

    public function attendance(
        ActivitySession $activitySession
    ): ActivitySession {
        $employeeId = Auth::user()?->employee_id;

        $isAssigned = $activitySession
            ->employees()
            ->where('employees.id', $employeeId)
            ->exists();

        if (! $isAssigned) {
            throw new EmployeeNotAssignedToSessionException();
        }

        return $activitySession->load([
            'activity',

            'children',

            'attendances' => function ($query): void {
                $query->with('child');
            },
        ]);
    }
}
