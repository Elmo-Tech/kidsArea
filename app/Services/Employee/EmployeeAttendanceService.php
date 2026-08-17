<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Enums\EmployeeAttendanceStatusEnum;
use App\Enums\EmployeeLeaveStatusEnum;
use App\Enums\EmployeePermissionStatusEnum;
use App\Enums\EmployeePermissionTypeEnum;
use App\Exceptions\Employee\EmployeeAttendanceAlreadyExistsException;
use App\Exceptions\EmployeeAttendance\EmployeeAlreadyCheckedInException;
use App\Exceptions\EmployeeAttendance\EmployeeAlreadyCheckedOutException;
use App\Exceptions\EmployeeAttendance\EmployeeHasNoActiveContractException;
use App\Exceptions\EmployeeAttendance\EmployeeNotCheckedInException;
use App\Exceptions\EmployeeAttendance\EmployeeOnApprovedLeaveException;
use App\Exceptions\EmployeeAttendance\UserHasNoEmployeeException;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeContract;
use App\Models\EmployeeLeave;
use App\Models\EmployeePermission;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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
            ->allowedFilters([
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
            ])
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

                $attendance = EmployeeAttendance::query()
                    ->where(
                        'employee_id',
                        $employeeId
                    )
                    ->whereDate(
                        'attendance_date',
                        $date
                    )
                    ->first();

                if ($attendance) {
                    if (
                        $attendance->check_in_at !== null
                        && $attendance->check_out_at === null
                    ) {
                        $attendance->update([
                            'status' =>
                                EmployeeAttendanceStatusEnum::INCOMPLETE->value,
                        ]);
                    }

                    continue;
                }

                $hasApprovedLeave = EmployeeLeave::query()
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

                if ($hasApprovedLeave) {
                    EmployeeAttendance::create([
                        'employee_id' =>
                            $employeeId,

                        'attendance_date' =>
                            $date,

                        'check_in_at' =>
                            null,

                        'check_out_at' =>
                            null,

                        'worked_minutes' =>
                            0,

                        'late_minutes' =>
                            0,

                        'excused_late_minutes' =>
                            0,

                        'early_leave_minutes' =>
                            0,

                        'excused_early_leave_minutes' =>
                            0,

                        'status' =>
                            EmployeeAttendanceStatusEnum::LEAVE->value,

                        'notes' =>
                            null,
                    ]);

                    continue;
                }

                if ($attendanceDate->isFuture()) {
                    continue;
                }

                [
                    ,
                    $scheduledEnd,
                ] = $this->buildScheduledRange(
                    $date,
                    (string) $contract->work_start_time,
                    (string) $contract->work_end_time
                );

                /*
                 * Do not mark ABSENT before the actual shift end.
                 * This also supports overnight shifts.
                 *
                 * Example:
                 * 2026-08-17 19:00 -> 2026-08-18 00:00
                 */
                if (now()->lessThan($scheduledEnd)) {
                    continue;
                }

                EmployeeAttendance::create([
                    'employee_id' =>
                        $employeeId,

                    'attendance_date' =>
                        $date,

                    'check_in_at' =>
                        null,

                    'check_out_at' =>
                        null,

                    'worked_minutes' =>
                        0,

                    'late_minutes' =>
                        0,

                    'excused_late_minutes' =>
                        0,

                    'early_leave_minutes' =>
                        0,

                    'excused_early_leave_minutes' =>
                        0,

                    'status' =>
                        EmployeeAttendanceStatusEnum::ABSENT->value,

                    'notes' =>
                        null,
                ]);
            }
        });
    }

    public function createEmployeeAttendance(
        array $data
    ): EmployeeAttendance {
        return DB::transaction(function () use ($data): EmployeeAttendance {
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

            $checkIn = $this->combineDateAndTime(
                $data['attendanceDate'],
                $data['checkInAt']
            );

            $checkOut = array_key_exists(
                'checkOutAt',
                $data
            ) && $data['checkOutAt'] !== null
                ? $this->combineCheckoutDateTime(
                    $checkIn,
                    $data['checkOutAt']
                )
                : null;

            $metrics = $this->calculateAttendanceMetrics(
                employeeId: (int) $data['employeeId'],
                date: $data['attendanceDate'],
                checkIn: $checkIn,
                checkOut: $checkOut,
                contract: $contract
            );

            $attendanceData = [
                'employee_id' =>
                    $data['employeeId'],

                'attendance_date' =>
                    $data['attendanceDate'],

                'check_in_at' =>
                    $checkIn,

                'check_out_at' =>
                    $checkOut,

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

                'status' =>
                    $checkOut !== null
                        ? EmployeeAttendanceStatusEnum::PRESENT->value
                        : EmployeeAttendanceStatusEnum::INCOMPLETE->value,

                'notes' =>
                    $data['notes'] ?? null,
            ];

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
        return DB::transaction(function () use (
            $attendance,
            $data
        ): EmployeeAttendance {
            $date = $attendance
                ->attendance_date
                ->format('Y-m-d');

            $checkIn = array_key_exists(
                'checkInAt',
                $data
            )
                ? $this->combineDateAndTime(
                    $date,
                    $data['checkInAt']
                )
                : Carbon::parse(
                    $attendance->check_in_at
                );

            $checkOut = array_key_exists(
                'checkOutAt',
                $data
            )
                ? (
                    $data['checkOutAt'] !== null
                        ? $this->combineCheckoutDateTime(
                            $checkIn,
                            $data['checkOutAt']
                        )
                        : null
                )
                : (
                    $attendance->check_out_at !== null
                        ? Carbon::parse(
                            $attendance->check_out_at
                        )
                        : null
                );

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
                'check_in_at' =>
                    $checkIn,

                'check_out_at' =>
                    $checkOut,

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

                'status' =>
                    $checkOut !== null
                        ? EmployeeAttendanceStatusEnum::PRESENT->value
                        : EmployeeAttendanceStatusEnum::INCOMPLETE->value,

                'notes' =>
                    array_key_exists(
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
        });
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
        CarbonInterface $checkIn,
        ?CarbonInterface $checkOut,
        ?EmployeeContract $contract
    ): array {
        $lateMinutes = 0;
        $excusedLateMinutes = 0;

        $earlyLeaveMinutes = 0;
        $excusedEarlyLeaveMinutes = 0;

        $workedMinutes = null;

        $scheduledStart = null;
        $scheduledEnd = null;

        if (
            $contract?->work_start_time
            && $contract?->work_end_time
        ) {
            [
                $scheduledStart,
                $scheduledEnd,
            ] = $this->buildScheduledRange(
                $date,
                (string) $contract->work_start_time,
                (string) $contract->work_end_time
            );
        }

        if ($scheduledStart !== null) {
            $lateMinutes = $this->calculateLateMinutes(
                $scheduledStart,
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

            if ($scheduledEnd !== null) {
                $earlyLeaveMinutes =
                    $this->calculateEarlyLeaveMinutes(
                        $scheduledEnd,
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
            'workedMinutes' =>
                $workedMinutes,

            'lateMinutes' =>
                $lateMinutes,

            'excusedLateMinutes' =>
                $excusedLateMinutes,

            'earlyLeaveMinutes' =>
                $earlyLeaveMinutes,

            'excusedEarlyLeaveMinutes' =>
                $excusedEarlyLeaveMinutes,
        ];
    }

    private function calculateMinutes(
        CarbonInterface $from,
        CarbonInterface $to
    ): int {
        if ($to->lessThan($from)) {
            return 0;
        }

        return (int) $from->diffInMinutes(
            $to
        );
    }

    private function calculateLateMinutes(
        CarbonInterface $expectedStart,
        CarbonInterface $actualStart
    ): int {
        if (
            $actualStart->lessThanOrEqualTo(
                $expectedStart
            )
        ) {
            return 0;
        }

        return (int) $expectedStart
            ->diffInMinutes(
                $actualStart
            );
    }

    private function calculateEarlyLeaveMinutes(
        CarbonInterface $expectedEnd,
        CarbonInterface $actualEnd
    ): int {
        if (
            $actualEnd->greaterThanOrEqualTo(
                $expectedEnd
            )
        ) {
            return 0;
        }

        return (int) $actualEnd
            ->diffInMinutes(
                $expectedEnd
            );
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
            $employee = $this->getEmployeeFromUser(
                $user
            );

            $checkIn = now();

            [
                'attendanceDate' => $attendanceDate,
                'contract' => $contract,
            ] = $this->resolveAttendanceDateForCheckIn(
                $employee->id,
                $checkIn
            );

            if (! $contract) {
                throw new EmployeeHasNoActiveContractException();
            }

            $this->ensureEmployeeHasNoApprovedLeave(
                $employee->id,
                $attendanceDate
            );

            /*
             * Prevent duplicate open attendance even across midnight.
             */
            $openAttendance = EmployeeAttendance::query()
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->whereNotNull(
                    'check_in_at'
                )
                ->whereNull(
                    'check_out_at'
                )
                ->lockForUpdate()
                ->latest('check_in_at')
                ->first();

            if ($openAttendance) {
                throw new EmployeeAlreadyCheckedInException();
            }

            $attendance = EmployeeAttendance::query()
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->whereDate(
                    'attendance_date',
                    $attendanceDate
                )
                ->lockForUpdate()
                ->first();

            if (
                $attendance
                && $attendance->check_in_at !== null
            ) {
                throw new EmployeeAlreadyCheckedInException();
            }

            $metrics = $this->calculateAttendanceMetrics(
                employeeId: $employee->id,
                date: $attendanceDate,
                checkIn: $checkIn,
                checkOut: null,
                contract: $contract
            );

            $attendanceData = [
                'employee_id' =>
                    $employee->id,

                'attendance_date' =>
                    $attendanceDate,

                'check_in_at' =>
                    $checkIn,

                'check_out_at' =>
                    null,

                'worked_minutes' =>
                    null,

                'late_minutes' =>
                    $metrics['lateMinutes'],

                'excused_late_minutes' =>
                    $metrics['excusedLateMinutes'],

                'early_leave_minutes' =>
                    0,

                'excused_early_leave_minutes' =>
                    0,

                'status' =>
                    EmployeeAttendanceStatusEnum::INCOMPLETE->value,
            ];

            if ($attendance) {
                $attendance->update(
                    $attendanceData
                );

                return $attendance
                    ->refresh()
                    ->load(
                        'employee.jobTitle'
                    );
            }

            $attendance = EmployeeAttendance::create([
                ...$attendanceData,
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
            $employee = $this->getEmployeeFromUser(
                $user
            );

            /*
             * Do NOT filter by today's date.
             *
             * The attendance may belong to yesterday when the shift
             * crosses midnight.
             */
            $attendance = EmployeeAttendance::query()
                ->where(
                    'employee_id',
                    $employee->id
                )
                ->whereNotNull(
                    'check_in_at'
                )
                ->whereNull(
                    'check_out_at'
                )
                ->lockForUpdate()
                ->latest('check_in_at')
                ->first();

            if (! $attendance) {
                $latestAttendance = EmployeeAttendance::query()
                    ->where(
                        'employee_id',
                        $employee->id
                    )
                    ->whereNotNull(
                        'check_in_at'
                    )
                    ->latest('check_in_at')
                    ->first();

                if (
                    $latestAttendance
                    && $latestAttendance->check_out_at !== null
                ) {
                    throw new EmployeeAlreadyCheckedOutException();
                }

                throw new EmployeeNotCheckedInException();
            }

            $attendanceDate = $attendance
                ->attendance_date
                ->format('Y-m-d');

            $contract = $this->getContractForDate(
                $employee->id,
                $attendanceDate
            );

            if (! $contract) {
                throw new EmployeeHasNoActiveContractException();
            }

            $checkIn = Carbon::parse(
                $attendance->check_in_at
            );

            $checkOut = now();

            $metrics = $this->calculateAttendanceMetrics(
                employeeId: $employee->id,
                date: $attendanceDate,
                checkIn: $checkIn,
                checkOut: $checkOut,
                contract: $contract
            );

            $attendance->update([
                'check_out_at' =>
                    $checkOut,

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

                'status' =>
                    EmployeeAttendanceStatusEnum::PRESENT->value,
            ]);

            return $attendance
                ->refresh()
                ->load(
                    'employee.jobTitle'
                );
        });
    }

    public function today(
        User $user
    ): array {
        $employee = $this->getEmployeeFromUser(
            $user
        );

        /*
         * Open overnight attendance always has priority.
         */
        $openAttendance = EmployeeAttendance::query()
            ->where(
                'employee_id',
                $employee->id
            )
            ->whereNotNull(
                'check_in_at'
            )
            ->whereNull(
                'check_out_at'
            )
            ->latest('check_in_at')
            ->first();

        if ($openAttendance) {
            return [
                'attendance' =>
                    $openAttendance,

                'canCheckIn' =>
                    false,

                'canCheckOut' =>
                    true,
            ];
        }

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
            'attendance' =>
                $attendance,

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

    /**
     * Resolve which attendanceDate should own a real-time check-in.
     *
     * If the current time falls inside yesterday's overnight shift,
     * attendanceDate is yesterday.
     *
     * Otherwise it belongs to today.
     */
    private function resolveAttendanceDateForCheckIn(
        int $employeeId,
        CarbonInterface $checkIn
    ): array {
        $today = $checkIn->toDateString();
        $yesterday = $checkIn
            ->copy()
            ->subDay()
            ->toDateString();

        $yesterdayContract = $this->getContractForDate(
            $employeeId,
            $yesterday
        );

        if (
            $yesterdayContract?->work_start_time
            && $yesterdayContract?->work_end_time
        ) {
            [
                $yesterdayStart,
                $yesterdayEnd,
            ] = $this->buildScheduledRange(
                $yesterday,
                (string) $yesterdayContract->work_start_time,
                (string) $yesterdayContract->work_end_time
            );

            $isOvernight =
                $yesterdayStart->toDateString()
                !== $yesterdayEnd->toDateString();

            if (
                $isOvernight
                && $checkIn->lessThanOrEqualTo(
                    $yesterdayEnd
                )
            ) {
                return [
                    'attendanceDate' =>
                        $yesterday,

                    'contract' =>
                        $yesterdayContract,
                ];
            }
        }

        return [
            'attendanceDate' =>
                $today,

            'contract' =>
                $this->getContractForDate(
                    $employeeId,
                    $today
                ),
        ];
    }

    /**
     * Build the expected shift range from attendanceDate + contract times.
     *
     * If end <= start, the shift ends on the next calendar day.
     */
    private function buildScheduledRange(
        string $attendanceDate,
        string $startTime,
        string $endTime
    ): array {
        $scheduledStart = Carbon::parse(
            "{$attendanceDate} {$startTime}"
        );

        $scheduledEnd = Carbon::parse(
            "{$attendanceDate} {$endTime}"
        );

        if (
            $scheduledEnd->lessThanOrEqualTo(
                $scheduledStart
            )
        ) {
            $scheduledEnd->addDay();
        }

        return [
            $scheduledStart,
            $scheduledEnd,
        ];
    }

    private function combineDateAndTime(
        string $attendanceDate,
        string $time
    ): Carbon {
        return Carbon::parse(
            "{$attendanceDate} {$time}"
        );
    }

    /**
     * If checkout clock time <= check-in clock time,
     * checkout belongs to the next day.
     */
    private function combineCheckoutDateTime(
        CarbonInterface $checkIn,
        string $checkOutTime
    ): Carbon {
        $checkOut = Carbon::parse(
            $checkIn->format('Y-m-d')
            . ' '
            . $checkOutTime
        );

        if (
            $checkOut->lessThanOrEqualTo(
                $checkIn
            )
        ) {
            $checkOut->addDay();
        }

        return $checkOut;
    }
}
