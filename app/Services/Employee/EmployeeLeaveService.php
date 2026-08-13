<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Enums\EmployeeLeaveStatusEnum;
use App\Enums\StatusEnum;
use App\Exceptions\Employee\EmployeeLeaveNotPendingException;
use App\Models\EmployeeLeave;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeLeaveService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(EmployeeLeave::class)
            ->with([
                'employee.jobTitle',
                'leaveType',
                'approvedBy',
            ])
            ->allowedFilters(
                AllowedFilter::exact('employeeId', 'employee_id'),
                AllowedFilter::exact('leaveTypeId', 'leave_type_id'),
                AllowedFilter::exact('status'),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createEmployeeLeave(array $data): EmployeeLeave
    {
        return DB::transaction(function () use ($data): EmployeeLeave {

            $this->ensureLeaveTypeIsActive(
                (int) $data['leaveTypeId']
            );

            $daysCount = $this->calculateDaysCount(
                $data['startDate'],
                $data['endDate']
            );

            $leave = EmployeeLeave::create([
                'employee_id' => $data['employeeId'],
                'leave_type_id' => $data['leaveTypeId'],

                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'],

                'days_count' => $daysCount,

                'reason' => $data['reason'] ?? null,

                'status' => EmployeeLeaveStatusEnum::PENDING->value,

                'approved_by' => null,
                'approved_at' => null,

                'notes' => $data['notes'] ?? null,
            ]);

            return $leave->load([
                'employee.jobTitle',
                'leaveType',
                'approvedBy',
            ]);
        });
    }

    public function editEmployeeLeave(
        EmployeeLeave $leave
    ): EmployeeLeave {
        return $leave->load([
            'leaveType',
            'approvedBy',
        ]);
    }

    public function updateEmployeeLeave(
        EmployeeLeave $leave,
        array $data
    ): EmployeeLeave {
        return DB::transaction(function () use ($leave, $data): EmployeeLeave {

            $this->ensureLeaveIsPending($leave);

            if (array_key_exists('leaveTypeId', $data)) {
                $this->ensureLeaveTypeIsActive(
                    (int) $data['leaveTypeId']
                );
            }

            $startDate = $data['startDate']
                ?? $leave->start_date->format('Y-m-d');

            $endDate = $data['endDate']
                ?? $leave->end_date->format('Y-m-d');

            if ($endDate < $startDate) {
                throw ValidationException::withMessages([
                    'endDate' => [
                        'End date must be after or equal to start date.',
                    ],
                ]);
            }

            $leave->update([
                'employee_id' => $data['employeeId']
                    ?? $leave->employee_id,

                'leave_type_id' => $data['leaveTypeId']
                    ?? $leave->leave_type_id,

                'start_date' => $startDate,
                'end_date' => $endDate,

                'days_count' => $this->calculateDaysCount(
                    $startDate,
                    $endDate
                ),

                'reason' => array_key_exists('reason', $data)
                    ? $data['reason']
                    : $leave->reason,

                'notes' => array_key_exists('notes', $data)
                    ? $data['notes']
                    : $leave->notes,
            ]);

            return $leave
                ->refresh()
                ->load([
                    'employee.jobTitle',
                    'leaveType',
                    'approvedBy',
                ]);
        });
    }

    public function deleteEmployeeLeave(
        EmployeeLeave $leave
    ): bool {
        return DB::transaction(function () use ($leave): bool {

            $this->ensureLeaveIsPending($leave);

            return (bool) $leave->delete();
        });
    }

    public function approveEmployeeLeave(
        EmployeeLeave $leave
    ): EmployeeLeave {
        return DB::transaction(function () use ($leave): EmployeeLeave {

            $this->ensureLeaveIsPending($leave);

            $leave->update([
                'status' => EmployeeLeaveStatusEnum::APPROVED->value,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            return $leave
                ->refresh()
                ->load([
                    'employee.jobTitle',
                    'leaveType',
                    'approvedBy',
                ]);
        });
    }

    public function rejectEmployeeLeave(
        EmployeeLeave $leave
    ): EmployeeLeave {
        return DB::transaction(function () use ($leave): EmployeeLeave {

            $this->ensureLeaveIsPending($leave);

            $leave->update([
                'status' => EmployeeLeaveStatusEnum::REJECTED->value,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            return $leave
                ->refresh()
                ->load([
                    'employee.jobTitle',
                    'leaveType',
                    'approvedBy',
                ]);
        });
    }

    private function calculateDaysCount(
        string $startDate,
        string $endDate
    ): int {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $days = $start->diffInDays($end);

        return (int) $days + 1;
    }


    private function ensureLeaveIsPending(
        EmployeeLeave $leave
    ): void {
        if (
            $leave->status !==
            EmployeeLeaveStatusEnum::PENDING
        ) {
            throw new EmployeeLeaveNotPendingException();
        }
    }

    private function ensureLeaveTypeIsActive(
        int $leaveTypeId
    ): void {
        $isActive = LeaveType::query()
            ->whereKey($leaveTypeId)
            ->active()
            ->exists();

        if (! $isActive) {
            throw ValidationException::withMessages([
                'leaveTypeId' => [
                    __('employee.leave_type_is_inactive'),
                ],
            ]);
        }
    }
}
