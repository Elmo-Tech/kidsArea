<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Enums\EmployeeAttendanceStatusEnum;
use App\Enums\EmployeeLeaveStatusEnum;
use App\Enums\EmployeePermissionStatusEnum;
use App\Enums\EmployeePermissionTypeEnum;
use App\Exceptions\Employee\EmployeeAttendanceAlreadyExistsException;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeContract;
use App\Models\EmployeeLeave;
use App\Models\EmployeePermission;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Exceptions\EmployeeAttendance\EmployeeAlreadyCheckedInException;
use App\Exceptions\EmployeeAttendance\EmployeeAlreadyCheckedOutException;
use App\Exceptions\EmployeeAttendance\EmployeeHasNoActiveContractException;
use App\Exceptions\EmployeeAttendance\EmployeeNotCheckedInException;
use App\Exceptions\EmployeeAttendance\EmployeeOnApprovedLeaveException;
use App\Exceptions\EmployeeAttendance\UserHasNoEmployeeException;
use App\Models\Employee;
use App\Models\User;

class EmployeeAttendanceService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(EmployeeAttendance::class)
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
                    'attendanceDate',
                    'attendance_date'
                ),
            )
            ->latest('attendance_date')
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Synchronize attendance records for a specific day.
     *
     * Creates:
     * - LEAVE for employees with approved leave.
     * - ABSENT for employees who should have worked but did not attend.
     */
    public function syncDay(string $date): void
{
    DB::transaction(function () use ($date): void {
        $attendanceDate = Carbon::parse($date)->startOfDay();

        $dayName = strtolower(
            $attendanceDate->format('l')
        );

        /*
         * Get employee contracts applicable to this date.
         *
         * We don't depend on the current contract status because
         * this method may also be used to sync historical dates.
         */
        $contracts = EmployeeContract::query()
            ->whereDate('start_date', '<=', $date)
            ->where(function ($query) use ($date): void {
                $query
                    ->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $date);
            })
            ->whereNotNull('work_start_time')
            ->whereNotNull('work_end_time')
            ->whereNotNull('work_days')
            ->whereJsonContains('work_days', $dayName)
            ->get();

        foreach ($contracts as $contract) {
            $employeeId = (int) $contract->employee_id;

            /*
             * Check if employee already has an attendance record
             * for this date.
             */
            $attendance = EmployeeAttendance::query()
                ->where('employee_id', $employeeId)
                ->whereDate('attendance_date', $date)
                ->first();

            /*
             * Attendance already exists.
             */
            if ($attendance) {

                /*
                 * Employee checked in but forgot to check out.
                 *
                 * Never assume the checkout time.
                 * Keep the record incomplete for HR review.
                 */
                if (
                    $attendance->check_in_at !== null
                    && $attendance->check_out_at === null
                ) {
                    $attendance->update([
                        'status' =>
                            EmployeeAttendanceStatusEnum::INCOMPLETE->value,
                    ]);
                }

                /*
                 * PRESENT / INCOMPLETE / LEAVE / ABSENT
                 * already has a record, so don't create another one.
                 */
                continue;
            }

            /*
             * Check approved employee leave.
             *
             * Leave has priority over absence.
             */
            $hasApprovedLeave = EmployeeLeave::query()
                ->where('employee_id', $employeeId)
                ->where(
                    'status',
                    EmployeeLeaveStatusEnum::APPROVED->value
                )
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();

            /*
             * Employee is on approved leave.
             */
            if ($hasApprovedLeave) {
                EmployeeAttendance::create([
                    'employee_id' => $employeeId,

                    'attendance_date' => $date,

                    'check_in_at' => null,
                    'check_out_at' => null,

                    'worked_minutes' => 0,

                    'late_minutes' => 0,
                    'excused_late_minutes' => 0,

                    'early_leave_minutes' => 0,
                    'excused_early_leave_minutes' => 0,

                    'status' =>
                        EmployeeAttendanceStatusEnum::LEAVE->value,

                    'notes' => null,
                ]);

                continue;
            }

            /*
             * Never mark future dates as absent.
             */
            if ($attendanceDate->isFuture()) {
                continue;
            }

            /*
             * If this is today, don't mark the employee absent
             * before their scheduled work day has ended.
             */
            if ($attendanceDate->isToday()) {
                $workEnd = Carbon::parse(
                    $date . ' ' . $contract->work_end_time
                );

                if (now()->lessThan($workEnd)) {
                    continue;
                }
            }

            /*
             * No attendance + no approved leave + workday ended
             * = absent.
             */
            EmployeeAttendance::create([
                'employee_id' => $employeeId,

                'attendance_date' => $date,

                'check_in_at' => null,
                'check_out_at' => null,

                'worked_minutes' => 0,

                'late_minutes' => 0,
                'excused_late_minutes' => 0,

                'early_leave_minutes' => 0,
                'excused_early_leave_minutes' => 0,

                'status' =>
                    EmployeeAttendanceStatusEnum::ABSENT->value,

                'notes' => null,
            ]);
        }
    });
}

    public function createEmployeeAttendance(
        array $data
    ): EmployeeAttendance {
        return DB::transaction(function () use ($data): EmployeeAttendance {
            /*
             * Important:
             *
             * syncDay() may already have generated an ABSENT record.
             * If HR later corrects the attendance manually,
             * we should update that row instead of creating a duplicate.
             */
            $existingAttendance = EmployeeAttendance::query()
                ->where(
                    'employee_id',
                    $data['employeeId']
                )
                ->whereDate(
                    'attendance_date',
                    $data['attendanceDate']
                )
                ->first();

            if (
                $existingAttendance
                && $existingAttendance->status
                    !== EmployeeAttendanceStatusEnum::ABSENT
            ) {
                throw new EmployeeAttendanceAlreadyExistsException();
            }

            $contract = $this->getContractForDate(
                (int) $data['employeeId'],
                $data['attendanceDate']
            );

            $checkIn = $data['checkInAt'];
            $checkOut = $data['checkOutAt'] ?? null;

            $metrics = $this->calculateAttendanceMetrics(
                employeeId: (int) $data['employeeId'],
                date: $data['attendanceDate'],
                checkIn: $checkIn,
                checkOut: $checkOut,
                contract: $contract
            );

            $attendanceData = [
                'employee_id' => $data['employeeId'],

                'attendance_date' =>
                    $data['attendanceDate'],

                'check_in_at' => $checkIn,
                'check_out_at' => $checkOut,

                'worked_minutes' =>
                    $metrics['workedMinutes'],

                'late_minutes' =>
                    $metrics['lateMinutes'],

                'excused_late_minutes' =>
                    $metrics['excusedLateMinutes'],

                'early_leave_minutes' =>
                    $metrics['earlyLeaveMinutes'],

                'excused_early_leave_minutes' =>
                    $metrics['excusedEarlyLeaveMinutes'],

                'status' => $checkOut !== null
                    ? EmployeeAttendanceStatusEnum::PRESENT->value
                    : EmployeeAttendanceStatusEnum::INCOMPLETE->value,

                'notes' => $data['notes'] ?? null,
            ];

            /*
             * Correct previously generated ABSENT.
             */
            if ($existingAttendance) {
                $existingAttendance->update(
                    $attendanceData
                );

                return $existingAttendance
                    ->refresh()
                    ->load([
                        'employee.jobTitle',
                    ]);
            }

            $attendance = EmployeeAttendance::create(
                $attendanceData
            );

            return $attendance->load([
                'employee.jobTitle',
            ]);
        });
    }

    public function editEmployeeAttendance(
        EmployeeAttendance $attendance
    ): EmployeeAttendance {
        return $attendance->load([
            'employee.jobTitle',
        ]);
    }

    public function updateEmployeeAttendance(
        EmployeeAttendance $attendance,
        array $data
    ): EmployeeAttendance {
        return DB::transaction(
            function () use ($attendance, $data): EmployeeAttendance {
                $checkIn = $data['checkInAt']
                    ?? $attendance->check_in_at;

                $checkOut = array_key_exists(
                    'checkOutAt',
                    $data
                )
                    ? $data['checkOutAt']
                    : $attendance->check_out_at;

                $date = $attendance
                    ->attendance_date
                    ->format('Y-m-d');

                $contract = $this->getContractForDate(
                    (int) $attendance->employee_id,
                    $date
                );

                $metrics = $this->calculateAttendanceMetrics(
                    employeeId: (int) $attendance->employee_id,
                    date: $date,
                    checkIn: $checkIn,
                    checkOut: $checkOut,
                    contract: $contract
                );

                $attendance->update([
                    'check_in_at' => $checkIn,
                    'check_out_at' => $checkOut,

                    'worked_minutes' =>
                        $metrics['workedMinutes'],

                    'late_minutes' =>
                        $metrics['lateMinutes'],

                    'excused_late_minutes' =>
                        $metrics['excusedLateMinutes'],

                    'early_leave_minutes' =>
                        $metrics['earlyLeaveMinutes'],

                    'excused_early_leave_minutes' =>
                        $metrics['excusedEarlyLeaveMinutes'],

                    'status' => $checkOut !== null
                        ? EmployeeAttendanceStatusEnum::PRESENT->value
                        : EmployeeAttendanceStatusEnum::INCOMPLETE->value,

                    'notes' => array_key_exists(
                        'notes',
                        $data
                    )
                        ? $data['notes']
                        : $attendance->notes,
                ]);

                return $attendance
                    ->refresh()
                    ->load([
                        'employee.jobTitle',
                    ]);
            }
        );
    }

    public function deleteEmployeeAttendance(
        EmployeeAttendance $attendance
    ): bool {
        return DB::transaction(
            fn (): bool => (bool) $attendance->delete()
        );
    }

    private function getContractForDate(
        int $employeeId,
        string $date
    ): ?EmployeeContract {
        return EmployeeContract::query()
            ->where(
                'employee_id',
                $employeeId
            )
            ->whereDate(
                'start_date',
                '<=',
                $date
            )
            ->where(function ($query) use ($date): void {
                $query
                    ->whereNull('end_date')
                    ->orWhereDate(
                        'end_date',
                        '>=',
                        $date
                    );
            })
            ->latest('start_date')
            ->first();
    }

    private function calculateAttendanceMetrics(
        int $employeeId,
        string $date,
        string $checkIn,
        ?string $checkOut,
        ?EmployeeContract $contract
    ): array {
        $lateMinutes = 0;
        $excusedLateMinutes = 0;

        $earlyLeaveMinutes = 0;
        $excusedEarlyLeaveMinutes = 0;

        $workedMinutes = null;

        if ($contract?->work_start_time) {
            $lateMinutes = $this->calculateLateMinutes(
                $contract->work_start_time,
                $checkIn
            );

            $excusedLateMinutes =
                $this->calculateExcusedLateMinutes(
                    $employeeId,
                    $date,
                    $lateMinutes
                );
        }

        if ($checkOut !== null) {
            $workedMinutes = $this->calculateMinutes(
                $checkIn,
                $checkOut
            );

            if ($contract?->work_end_time) {
                $earlyLeaveMinutes =
                    $this->calculateEarlyLeaveMinutes(
                        $contract->work_end_time,
                        $checkOut
                    );

                $excusedEarlyLeaveMinutes =
                    $this->calculateExcusedEarlyLeaveMinutes(
                        $employeeId,
                        $date,
                        $earlyLeaveMinutes
                    );
            }
        }

        return [
            'workedMinutes' => $workedMinutes,

            'lateMinutes' => $lateMinutes,
            'excusedLateMinutes' =>
                $excusedLateMinutes,

            'earlyLeaveMinutes' =>
                $earlyLeaveMinutes,

            'excusedEarlyLeaveMinutes' =>
                $excusedEarlyLeaveMinutes,
        ];
    }

    private function calculateMinutes(
        string $from,
        string $to
    ): int {
        return (int) Carbon::parse($from)
            ->diffInMinutes(
                Carbon::parse($to)
            );
    }

    private function calculateLateMinutes(
        string $expectedStart,
        string $actualStart
    ): int {
        $expected = Carbon::parse($expectedStart);
        $actual = Carbon::parse($actualStart);

        if ($actual->lessThanOrEqualTo($expected)) {
            return 0;
        }

        return (int) $expected
            ->diffInMinutes($actual);
    }

    private function calculateEarlyLeaveMinutes(
        string $expectedEnd,
        string $actualEnd
    ): int {
        $expected = Carbon::parse($expectedEnd);
        $actual = Carbon::parse($actualEnd);

        if ($actual->greaterThanOrEqualTo($expected)) {
            return 0;
        }

        return (int) $actual
            ->diffInMinutes($expected);
    }

    private function calculateExcusedLateMinutes(
        int $employeeId,
        string $date,
        int $lateMinutes
    ): int {
        if ($lateMinutes === 0) {
            return 0;
        }

        $permissionMinutes = EmployeePermission::query()
            ->where(
                'employee_id',
                $employeeId
            )
            ->whereDate(
                'permission_date',
                $date
            )
            ->where(
                'type',
                EmployeePermissionTypeEnum::LATE_ARRIVAL->value
            )
            ->where(
                'status',
                EmployeePermissionStatusEnum::APPROVED->value
            )
            ->sum('minutes');

        return min(
            $lateMinutes,
            (int) $permissionMinutes
        );
    }

    private function calculateExcusedEarlyLeaveMinutes(
        int $employeeId,
        string $date,
        int $earlyLeaveMinutes
    ): int {
        if ($earlyLeaveMinutes === 0) {
            return 0;
        }

        $permissionMinutes = EmployeePermission::query()
            ->where(
                'employee_id',
                $employeeId
            )
            ->whereDate(
                'permission_date',
                $date
            )
            ->where(
                'type',
                EmployeePermissionTypeEnum::EARLY_LEAVE->value
            )
            ->where(
                'status',
                EmployeePermissionStatusEnum::APPROVED->value
            )
            ->sum('minutes');

        return min(
            $earlyLeaveMinutes,
            (int) $permissionMinutes
        );
    }

    public function checkIn(
        User $user
    ): EmployeeAttendance {
        return DB::transaction(function () use ($user): EmployeeAttendance {

            $employee = $this->getEmployeeFromUser($user);

            $date = now()->toDateString();
            $checkIn = now()->format('H:i:s');

            $contract = $this->getContractForDate(
                $employee->id,
                $date
            );

            if (! $contract) {
                throw new EmployeeHasNoActiveContractException();
            }

            $this->ensureEmployeeHasNoApprovedLeave(
                $employee->id,
                $date
            );

            $attendance = EmployeeAttendance::query()
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $date)
                ->first();

            /*
            * Existing attendance record.
            */
            if ($attendance) {

                if ($attendance->check_in_at !== null) {
                    throw new EmployeeAlreadyCheckedInException();
                }

                /*
                * If ABSENT was previously generated by syncDay,
                * convert the same row to an actual check-in.
                */
                $metrics = $this->calculateAttendanceMetrics(
                    employeeId: $employee->id,
                    date: $date,
                    checkIn: $checkIn,
                    checkOut: null,
                    contract: $contract
                );

                $attendance->update([
                    'check_in_at' => $checkIn,
                    'check_out_at' => null,

                    'worked_minutes' => null,

                    'late_minutes' =>
                        $metrics['lateMinutes'],

                    'excused_late_minutes' =>
                        $metrics['excusedLateMinutes'],

                    'early_leave_minutes' => 0,
                    'excused_early_leave_minutes' => 0,

                    'status' =>
                        EmployeeAttendanceStatusEnum::INCOMPLETE->value,
                ]);

                return $attendance
                    ->refresh()
                    ->load('employee.jobTitle');
            }

            $metrics = $this->calculateAttendanceMetrics(
                employeeId: $employee->id,
                date: $date,
                checkIn: $checkIn,
                checkOut: null,
                contract: $contract
            );

            $attendance = EmployeeAttendance::create([
                'employee_id' => $employee->id,

                'attendance_date' => $date,

                'check_in_at' => $checkIn,
                'check_out_at' => null,

                'worked_minutes' => null,

                'late_minutes' =>
                    $metrics['lateMinutes'],

                'excused_late_minutes' =>
                    $metrics['excusedLateMinutes'],

                'early_leave_minutes' => 0,
                'excused_early_leave_minutes' => 0,

                'status' =>
                    EmployeeAttendanceStatusEnum::INCOMPLETE->value,

                'notes' => null,
            ]);

            return $attendance->load(
                'employee.jobTitle'
            );
        });
    }

    public function checkOut(
        User $user
    ): EmployeeAttendance {
        return DB::transaction(function () use ($user): EmployeeAttendance {

            $employee = $this->getEmployeeFromUser($user);

            $date = now()->toDateString();

            $attendance = EmployeeAttendance::query()
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $date)
                ->first();

            if (
                ! $attendance
                || $attendance->check_in_at === null
            ) {
                throw new EmployeeNotCheckedInException();
            }

            if ($attendance->check_out_at !== null) {
                throw new EmployeeAlreadyCheckedOutException();
            }

            $contract = $this->getContractForDate(
                $employee->id,
                $date
            );

            if (! $contract) {
                throw new EmployeeHasNoActiveContractException();
            }

            $checkIn = substr(
                (string) $attendance->check_in_at,
                0,
                8
            );

            $checkOut = now()->format('H:i:s');

            $metrics = $this->calculateAttendanceMetrics(
                employeeId: $employee->id,
                date: $date,
                checkIn: $checkIn,
                checkOut: $checkOut,
                contract: $contract
            );

            $attendance->update([
                'check_out_at' => $checkOut,

                'worked_minutes' =>
                    $metrics['workedMinutes'],

                /*
                * Recalculate everything again in case
                * permissions were approved after check-in.
                */
                'late_minutes' =>
                    $metrics['lateMinutes'],

                'excused_late_minutes' =>
                    $metrics['excusedLateMinutes'],

                'early_leave_minutes' =>
                    $metrics['earlyLeaveMinutes'],

                'excused_early_leave_minutes' =>
                    $metrics['excusedEarlyLeaveMinutes'],

                'status' =>
                    EmployeeAttendanceStatusEnum::PRESENT->value,
            ]);

            return $attendance
                ->refresh()
                ->load('employee.jobTitle');
        });
    }

    public function today(
        User $user
    ): array {
        $employee = $this->getEmployeeFromUser($user);

        $attendance = EmployeeAttendance::query()
            ->where(
                'employee_id',
                $employee->id
            )
            ->whereDate(
                'attendance_date',
                now()->toDateString()
            )
            ->first();

        return [
            'attendance' => $attendance,

            'canCheckIn' =>
                ! $attendance
                || $attendance->check_in_at === null,

            'canCheckOut' =>
                $attendance !== null
                && $attendance->check_in_at !== null
                && $attendance->check_out_at === null,
        ];
    }
    private function getEmployeeFromUser(
        User $user
    ): Employee {
        $employee = $user->employee;

        if (! $employee) {
            throw new UserHasNoEmployeeException();
        }

        return $employee;
    }
    private function ensureEmployeeHasNoApprovedLeave(
        int $employeeId,
        string $date
    ): void {
        $exists = EmployeeLeave::query()
            ->where(
                'employee_id',
                $employeeId
            )
            ->where(
                'status',
                EmployeeLeaveStatusEnum::APPROVED->value
            )
            ->whereDate(
                'start_date',
                '<=',
                $date
            )
            ->whereDate(
                'end_date',
                '>=',
                $date
            )
            ->exists();

        if ($exists) {
            throw new EmployeeOnApprovedLeaveException();
        }
    }
}
