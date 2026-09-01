<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Enums\ActivitySessionEmployeeAttendanceStatusEnum;
use App\Enums\EmployeeAttendanceStatusEnum;
use App\Enums\EmployeeLeaveStatusEnum;
use App\Enums\EmployeeSessionStatusEnum;
use App\Enums\LeavePayrollEffectEnum;
use App\Enums\PayrollDeductionMethodEnum;
use App\Enums\PayrollPeriodStatusEnum;
use App\Enums\PayrollProrationMethodEnum;
use App\Enums\SalaryTypeEnum;
use App\Exceptions\Payroll\PayrollHasIncompleteAttendanceException;
use App\Exceptions\Payroll\PayrollHasMissingAttendanceException;
use App\Exceptions\Payroll\PayrollHasPendingSessionsException;
use App\Exceptions\Payroll\PayrollPeriodAlreadyExistsException;
use App\Exceptions\Payroll\PayrollPeriodFinalizedException;
use App\Exceptions\Payroll\PayrollPeriodHasNoEmployeesException;
use App\Models\ActivitySessionEmployeeAttendance;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeContract;
use App\Models\EmployeeLeave;
use App\Models\EmployeePayroll;
use App\Models\EmployeeSession;
use App\Models\LeavePayrollPolicy;
use App\Models\PayrollPeriod;
use App\Models\PayrollSetting;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PayrollPeriodService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(PayrollPeriod::class)
            ->withCount('employeePayrolls')
            ->withSum('employeePayrolls', 'net_salary')
            ->allowedFilters(
                AllowedFilter::exact('year'),
                AllowedFilter::exact('month'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('prorationMethod', 'proration_method'),
            )
            ->latest('year')
            ->latest('month')
            ->paginate($perPage);
    }

    public function createPayrollPeriod(array $data): PayrollPeriod
    {
        return DB::transaction(function () use ($data): PayrollPeriod {
            $this->ensurePeriodDoesNotExist(
                (int) $data['year'],
                (int) $data['month']
            );

            $settings = PayrollSetting::query()->firstOrCreate([]);

            $startDate = Carbon::create(
                (int) $data['year'],
                (int) $data['month'],
                1
            )->startOfMonth();

            $endDate = $startDate->copy()->endOfMonth();

            $period = PayrollPeriod::create([
                'year' => $data['year'],
                'month' => $data['month'],
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'proration_method' =>
                    $data['prorationMethod']
                    ?? $settings->proration_method->value,
                'status' => PayrollPeriodStatusEnum::DRAFT->value,
                'notes' => $data['notes'] ?? null,
            ]);

            return $period->load('finalizedBy');
        });
    }

    public function editPayrollPeriod(PayrollPeriod $period): PayrollPeriod
    {
        return $period->load('finalizedBy');
    }

    public function updatePayrollPeriod(
        PayrollPeriod $period,
        array $data
    ): PayrollPeriod {
        return DB::transaction(function () use ($period, $data): PayrollPeriod {
            $this->ensurePeriodIsDraft($period);

            $period->update([
                'proration_method' =>
                    $data['prorationMethod']
                    ?? $period->proration_method->value,
                'notes' =>
                    array_key_exists('notes', $data)
                        ? $data['notes']
                        : $period->notes,
            ]);

            return $period
                ->refresh()
                ->load('finalizedBy');
        });
    }

    public function deletePayrollPeriod(PayrollPeriod $period): bool
    {
        return DB::transaction(function () use ($period): bool {
            $this->ensurePeriodIsDraft($period);

            return (bool) $period->delete();
        });
    }

    public function generatePayroll(PayrollPeriod $period): PayrollPeriod
    {
        return DB::transaction(function () use ($period): PayrollPeriod {
            $this->ensurePeriodIsDraft($period);

            $this->generatePayrollData($period);

            return $period
                ->refresh()
                ->load('finalizedBy');
        });
    }

    public function recalculatePayroll(PayrollPeriod $period): PayrollPeriod
    {
        return DB::transaction(function () use ($period): PayrollPeriod {
            $this->ensurePeriodIsDraft($period);

            $this->generatePayrollData($period);

            return $period
                ->refresh()
                ->load('finalizedBy');
        });
    }

    public function finalizePayroll(PayrollPeriod $period): PayrollPeriod
    {
        return DB::transaction(function () use ($period): PayrollPeriod {
            $this->ensurePeriodIsDraft($period);

            $this->generatePayrollData($period);

            $this->ensurePayrollHasEmployees($period);

            $settings = PayrollSetting::query()->firstOrCreate([]);

            $this->ensureNoMissingAttendance($period);

            if ($settings->block_finalize_on_incomplete_attendance) {
                $this->ensureNoIncompleteAttendance($period);
            }

            $this->ensureNoPendingSessions($period);

            $period->update([
                'status' => PayrollPeriodStatusEnum::FINALIZED->value,
                'finalized_by' => Auth::id(),
                'finalized_at' => now(),
            ]);

            return $period
                ->refresh()
                ->load('finalizedBy');
        });
    }

    private function generatePayrollData(PayrollPeriod $period): void
    {
        $settings = PayrollSetting::query()->firstOrCreate([]);

        $contracts = EmployeeContract::query()
            ->whereDate(
                'start_date',
                '<=',
                $period->end_date->format('Y-m-d')
            )
            ->where(function ($query) use ($period): void {
                $query
                    ->whereNull('end_date')
                    ->orWhereDate(
                        'end_date',
                        '>=',
                        $period->start_date->format('Y-m-d')
                    );
            })
            ->orderBy('employee_id')
            ->orderBy('start_date')
            ->get();

        $contractsByEmployee = $contracts->groupBy('employee_id');

        foreach ($contractsByEmployee as $employeeContracts) {
            $this->generateEmployeePayroll(
                $period,
                $employeeContracts,
                $settings
            );
        }
    }

    private function generateEmployeePayroll(
        PayrollPeriod $period,
        Collection $contracts,
        PayrollSetting $settings
    ): EmployeePayroll {
        /** @var EmployeeContract $firstContract */
        $firstContract = $contracts->first();

        /** @var EmployeeContract $lastContract */
        $lastContract = $contracts->last();

        $employeeId = (int) $firstContract->employee_id;

        $payroll = EmployeePayroll::query()
            ->firstOrNew([
                'payroll_period_id' => $period->id,
                'employee_id' => $employeeId,
            ]);

        $additionsTotal = $payroll->exists
            ? (float) $payroll->additions_total
            : 0;

        $deductionsTotal = $payroll->exists
            ? (float) $payroll->deductions_total
            : 0;

        $payableFrom = $this->getPayableFrom($period, $firstContract);
        $payableTo = $this->getPayableTo($period, $lastContract);

        $prorationDays = $this->calculateProrationDays(
            $payableFrom,
            $payableTo
        );

        $proratedBasicSalary = 0;
        $hourlyEarnings = 0;
        $sessionEarnings = 0;
        $completedSessions = 0;

        $attendanceDeduction = 0;
        $leaveDeduction = 0;

        $workedMinutes = 0;
        $absenceDays = 0;
        $unexcusedLateMinutes = 0;
        $unexcusedEarlyLeaveMinutes = 0;

        foreach ($contracts as $contract) {
            $segmentFrom = $this->getPayableFrom($period, $contract);
            $segmentTo = $this->getPayableTo($period, $contract);

            if ($segmentFrom->greaterThan($segmentTo)) {
                continue;
            }

            $attendanceData = $this->getAttendanceData(
                employeeId: $employeeId,
                from: $segmentFrom->format('Y-m-d'),
                to: $segmentTo->format('Y-m-d')
            );

            $segmentBasicSalary = $this->calculateBasicSalary(
                contract: $contract,
                period: $period,
                payableFrom: $segmentFrom,
                payableTo: $segmentTo
            );

            $segmentHourlyEarnings = $this->calculateHourlyEarnings(
                $contract,
                $attendanceData['workedMinutes']
            );

            $segmentSessions = $this->getCompletedSessionsCount(
                employeeId: $employeeId,
                from: $segmentFrom->format('Y-m-d'),
                to: $segmentTo->format('Y-m-d')
            );

            $segmentSessionEarnings = $this->calculateSessionEarnings(
                $contract,
                $segmentSessions
            );

            $segmentAttendanceDeduction =
                $this->calculateAttendanceDeduction(
                    contract: $contract,
                    period: $period,
                    attendanceData: $attendanceData,
                    settings: $settings
                );

            $segmentLeaveDeduction =
                $this->calculateLeaveDeduction(
                    contract: $contract,
                    period: $period,
                    from: $segmentFrom->format('Y-m-d'),
                    to: $segmentTo->format('Y-m-d')
                );

            $proratedBasicSalary += $segmentBasicSalary;
            $hourlyEarnings += $segmentHourlyEarnings;
            $completedSessions += $segmentSessions;
            $sessionEarnings += $segmentSessionEarnings;

            $attendanceDeduction += $segmentAttendanceDeduction;
            $leaveDeduction += $segmentLeaveDeduction;

            $workedMinutes += $attendanceData['workedMinutes'];
            $absenceDays += $attendanceData['absenceDays'];
            $unexcusedLateMinutes +=
                $attendanceData['unexcusedLateMinutes'];
            $unexcusedEarlyLeaveMinutes +=
                $attendanceData['unexcusedEarlyLeaveMinutes'];
        }

        $proratedBasicSalary = round($proratedBasicSalary, 2);
        $hourlyEarnings = round($hourlyEarnings, 2);
        $sessionEarnings = round($sessionEarnings, 2);

        $attendanceDeductions = round(
            $attendanceDeduction + $leaveDeduction,
            2
        );

        $grossSalary = round(
            $proratedBasicSalary
            + $hourlyEarnings
            + $sessionEarnings
            + $additionsTotal,
            2
        );

        $netSalary = max(
            0,
            round(
                $grossSalary
                - $attendanceDeductions
                - $deductionsTotal,
                2
            )
        );

        $payroll->fill([
            /*
             * Snapshot/reference fields.
             *
             * employee_contract_id points to the latest contract
             * used in this payroll period. Detailed calculations
             * are aggregated from all contract segments.
             */
            'employee_contract_id' => $lastContract->id,

            'contract_start_date' =>
                $firstContract->start_date->format('Y-m-d'),

            'contract_end_date' =>
                $lastContract->end_date?->format('Y-m-d'),

            'payable_from' =>
                $payableFrom->format('Y-m-d'),

            'payable_to' =>
                $payableTo->format('Y-m-d'),

            'proration_days' =>
                $prorationDays,

            /*
             * The latest contract is stored as the current
             * salary snapshot for informational purposes.
             */
            'salary_type' =>
                $lastContract->salary_type->value,

            'basic_salary' =>
                $lastContract->basic_salary,

            'hourly_rate' =>
                $lastContract->hourly_rate,

            'session_rate' =>
                $lastContract->session_rate,

            'prorated_basic_salary' =>
                $proratedBasicSalary,

            'worked_minutes' =>
                $workedMinutes,

            'hourly_earnings' =>
                $hourlyEarnings,

            'completed_sessions' =>
                $completedSessions,

            'session_earnings' =>
                $sessionEarnings,

            'absence_days' =>
                $absenceDays,

            'unexcused_late_minutes' =>
                $unexcusedLateMinutes,

            'unexcused_early_leave_minutes' =>
                $unexcusedEarlyLeaveMinutes,

            'attendance_deductions' =>
                $attendanceDeductions,

            'additions_total' =>
                $additionsTotal,

            'deductions_total' =>
                $deductionsTotal,

            'gross_salary' =>
                $grossSalary,

            'net_salary' =>
                $netSalary,
        ]);

        $payroll->save();

        return $payroll;
    }

    private function getPayableFrom(
        PayrollPeriod $period,
        EmployeeContract $contract
    ): Carbon {
        return $contract->start_date->greaterThan($period->start_date)
            ? $contract->start_date->copy()
            : $period->start_date->copy();
    }

    private function getPayableTo(
        PayrollPeriod $period,
        EmployeeContract $contract
    ): Carbon {
        if (
            $contract->end_date
            && $contract->end_date->lessThan($period->end_date)
        ) {
            return $contract->end_date->copy();
        }

        return $period->end_date->copy();
    }

    private function calculateProrationDays(
        Carbon $from,
        Carbon $to
    ): int {
        return (int) $from->diffInDays($to) + 1;
    }

    private function calculateBasicSalary(
        EmployeeContract $contract,
        PayrollPeriod $period,
        Carbon $payableFrom,
        Carbon $payableTo
    ): float {
        if (
            ! in_array(
                $contract->salary_type,
                [
                    SalaryTypeEnum::MONTHLY,
                    SalaryTypeEnum::MONTHLY_PLUS_SESSION,
                ],
                true
            )
        ) {
            return 0;
        }

        $basicSalary = (float) $contract->basic_salary;

        if (
            $payableFrom->isSameDay($period->start_date)
            && $payableTo->isSameDay($period->end_date)
        ) {
            return round($basicSalary, 2);
        }

        $payableDays =
            (int) $payableFrom->diffInDays($payableTo) + 1;

        $divisor = $this->getProrationDivisor($period);

        return round(
            ($basicSalary / $divisor) * $payableDays,
            2
        );
    }

    private function getProrationDivisor(
        PayrollPeriod $period
    ): int {
        return match ($period->proration_method) {
            PayrollProrationMethodEnum::FIXED_30_DAYS => 30,

            PayrollProrationMethodEnum::CALENDAR_DAYS =>
                $period->start_date->daysInMonth,
        };
    }

    private function getAttendanceData(
        int $employeeId,
        string $from,
        string $to
    ): array {
        $attendances = EmployeeAttendance::query()
            ->where('employee_id', $employeeId)
            ->whereBetween(
                'attendance_date',
                [$from, $to]
            )
            ->get();

        return [
            'workedMinutes' =>
                (int) $attendances->sum('worked_minutes'),

            'absenceDays' =>
                $attendances
                    ->where(
                        'status',
                        EmployeeAttendanceStatusEnum::ABSENT
                    )
                    ->count(),

            'unexcusedLateMinutes' => max(
                0,
                (int) $attendances->sum('late_minutes')
                - (int) $attendances->sum('excused_late_minutes')
            ),

            'unexcusedEarlyLeaveMinutes' => max(
                0,
                (int) $attendances->sum('early_leave_minutes')
                - (int) $attendances
                    ->sum('excused_early_leave_minutes')
            ),
        ];
    }

    private function calculateHourlyEarnings(
        EmployeeContract $contract,
        int $workedMinutes
    ): float {
        if (
            $contract->salary_type !==
            SalaryTypeEnum::HOURLY
        ) {
            return 0;
        }

        return round(
            ($workedMinutes / 60)
            * (float) $contract->hourly_rate,
            2
        );
    }

    private function getCompletedSessionsCount(
        int $employeeId,
        string $from,
        string $to
    ): int {
        $standaloneSessionsCount = EmployeeSession::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('session_date', [$from, $to])
            ->where('status', EmployeeSessionStatusEnum::COMPLETED->value)
            ->count();

        $activitySessionsCount = ActivitySessionEmployeeAttendance::query()
            ->where('employee_id', $employeeId)
            ->whereIn('status', [
                ActivitySessionEmployeeAttendanceStatusEnum::PRESENT->value,
                ActivitySessionEmployeeAttendanceStatusEnum::LATE->value,
            ])
            ->whereHas('session', function ($query) use ($from, $to): void {
                $query->whereBetween('session_date', [$from, $to]);
            })
            ->count();

        return $standaloneSessionsCount + $activitySessionsCount;
    }

    private function calculateSessionEarnings(
        EmployeeContract $contract,
        int $completedSessions
    ): float {
        if (
            ! in_array(
                $contract->salary_type,
                [
                    SalaryTypeEnum::SESSION,
                    SalaryTypeEnum::MONTHLY_PLUS_SESSION,
                ],
                true
            )
        ) {
            return 0;
        }

        return round(
            $completedSessions
            * (float) $contract->session_rate,
            2
        );
    }

    private function calculateAttendanceDeduction(
        EmployeeContract $contract,
        PayrollPeriod $period,
        array $attendanceData,
        PayrollSetting $settings
    ): float {
        if (
            ! in_array(
                $contract->salary_type,
                [
                    SalaryTypeEnum::MONTHLY,
                    SalaryTypeEnum::MONTHLY_PLUS_SESSION,
                ],
                true
            )
        ) {
            return 0;
        }

        $basicSalary = (float) $contract->basic_salary;
        $daysDivisor = $this->getProrationDivisor($period);

        $dailyRate = $basicSalary / $daysDivisor;

        $deduction = 0;

        if (
            $settings->absence_deduction_method ===
            PayrollDeductionMethodEnum::BY_DAY
        ) {
            $deduction +=
                $attendanceData['absenceDays']
                * $dailyRate;
        }

        if (
            $contract->work_start_time
            && $contract->work_end_time
        ) {
            $dailyWorkingMinutes = $this->calculateMinutes(
                $contract->work_start_time,
                $contract->work_end_time
            );

            if ($dailyWorkingMinutes > 0) {
                $minuteRate =
                    $dailyRate / $dailyWorkingMinutes;

                if (
                    $settings->late_deduction_method ===
                    PayrollDeductionMethodEnum::BY_MINUTE
                ) {
                    $deduction +=
                        $attendanceData['unexcusedLateMinutes']
                        * $minuteRate;
                }

                if (
                    $settings->early_leave_deduction_method ===
                    PayrollDeductionMethodEnum::BY_MINUTE
                ) {
                    $deduction +=
                        $attendanceData[
                            'unexcusedEarlyLeaveMinutes'
                        ]
                        * $minuteRate;
                }
            }
        }

        return round($deduction, 2);
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

    private function calculateLeaveDeduction(
        EmployeeContract $contract,
        PayrollPeriod $period,
        string $from,
        string $to
    ): float {
        if (
            ! in_array(
                $contract->salary_type,
                [
                    SalaryTypeEnum::MONTHLY,
                    SalaryTypeEnum::MONTHLY_PLUS_SESSION,
                ],
                true
            )
        ) {
            return 0;
        }

        $leaves = EmployeeLeave::query()
            ->where('employee_id', $contract->employee_id)
            ->where(
                'status',
                EmployeeLeaveStatusEnum::APPROVED->value
            )
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from)
            ->get();

        if ($leaves->isEmpty()) {
            return 0;
        }

        $dailyRate =
            (float) $contract->basic_salary
            / $this->getProrationDivisor($period);

        $deduction = 0;

        foreach ($leaves as $leave) {
            $policy = LeavePayrollPolicy::query()
                ->where(
                    'leave_type_id',
                    $leave->leave_type_id
                )
                ->where(
                    'salary_type',
                    $contract->salary_type->value
                )
                ->first();

            if (! $policy) {
                continue;
            }

            if (
                ! in_array(
                    $policy->effect,
                    [
                        LeavePayrollEffectEnum::UNPAID,
                        LeavePayrollEffectEnum::UNPAID_BASIC,
                    ],
                    true
                )
            ) {
                continue;
            }

            $fromDate = Carbon::parse($from);
            $toDate = Carbon::parse($to);

            $leaveStart = $leave->start_date->greaterThan($fromDate)
                ? $leave->start_date->copy()
                : $fromDate;

            $leaveEnd = $leave->end_date->lessThan($toDate)
                ? $leave->end_date->copy()
                : $toDate;

            if ($leaveStart->greaterThan($leaveEnd)) {
                continue;
            }

            $leaveDays =
                (int) $leaveStart->diffInDays($leaveEnd) + 1;

            $deduction +=
                $leaveDays * $dailyRate;
        }

        return round($deduction, 2);
    }

    private function ensurePeriodDoesNotExist(
        int $year,
        int $month
    ): void {
        $exists = PayrollPeriod::query()
            ->where('year', $year)
            ->where('month', $month)
            ->exists();

        if ($exists) {
            throw new PayrollPeriodAlreadyExistsException();
        }
    }

    private function ensurePeriodIsDraft(
        PayrollPeriod $period
    ): void {
        if (
            $period->status !==
            PayrollPeriodStatusEnum::DRAFT
        ) {
            throw new PayrollPeriodFinalizedException();
        }
    }

    private function ensurePayrollHasEmployees(
        PayrollPeriod $period
    ): void {
        $exists = EmployeePayroll::query()
            ->where(
                'payroll_period_id',
                $period->id
            )
            ->exists();

        if (! $exists) {
            throw new PayrollPeriodHasNoEmployeesException();
        }
    }

    private function ensureNoMissingAttendance(
        PayrollPeriod $period
    ): void {
        $payrolls = EmployeePayroll::query()
            ->where('payroll_period_id', $period->id)
            ->with('employee:id,name')
            ->get([
                'employee_id',
                'payable_from',
                'payable_to',
            ]);

        foreach ($payrolls as $payroll) {
            $from = $payroll->payable_from->format('Y-m-d');
            $to = $payroll->payable_to->format('Y-m-d');

            $attendanceDates = EmployeeAttendance::query()
                ->where('employee_id', $payroll->employee_id)
                ->whereBetween('attendance_date', [$from, $to])
                ->pluck('attendance_date')
                ->map(
                    fn ($date): string =>
                        Carbon::parse($date)->format('Y-m-d')
                )
                ->flip();

            $contracts = EmployeeContract::query()
                ->where('employee_id', $payroll->employee_id)
                ->whereDate('start_date', '<=', $to)
                ->where(function ($query) use ($from): void {
                    $query
                        ->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $from);
                })
                ->whereNotNull('work_start_time')
                ->whereNotNull('work_end_time')
                ->whereNotNull('work_days')
                ->orderBy('start_date')
                ->get();

            foreach ($contracts as $contract) {
                $segmentFrom = $contract->start_date->greaterThan(
                    $payroll->payable_from
                )
                    ? $contract->start_date->copy()
                    : $payroll->payable_from->copy();

                $segmentTo = $contract->end_date
                    && $contract->end_date->lessThan(
                        $payroll->payable_to
                    )
                        ? $contract->end_date->copy()
                        : $payroll->payable_to->copy();

                if ($segmentFrom->greaterThan($segmentTo)) {
                    continue;
                }

                $workDays = array_map(
                    'strtolower',
                    $contract->work_days ?? []
                );

                if ($workDays === []) {
                    continue;
                }

                $date = $segmentFrom->copy()->startOfDay();

                while ($date->lessThanOrEqualTo($segmentTo)) {
                    $dayName = strtolower($date->format('l'));
                    $dateString = $date->format('Y-m-d');

                    if (
                        in_array($dayName, $workDays, true)
                        && ! $attendanceDates->has($dateString)
                    ) {
                        throw new PayrollHasMissingAttendanceException(
                            employeeName:
                                $payroll->employee?->name
                                ?? (string) $payroll->employee_id,
                            date: $dateString
                        );
                    }

                    $date->addDay();
                }
            }
        }
    }

    private function ensureNoIncompleteAttendance(
        PayrollPeriod $period
    ): void {
        $payrolls = EmployeePayroll::query()
            ->where(
                'payroll_period_id',
                $period->id
            )
            ->get([
                'employee_id',
                'payable_from',
                'payable_to',
            ]);

        foreach ($payrolls as $payroll) {
            $exists = EmployeeAttendance::query()
                ->where(
                    'employee_id',
                    $payroll->employee_id
                )
                ->whereBetween(
                    'attendance_date',
                    [
                        $payroll->payable_from->format('Y-m-d'),
                        $payroll->payable_to->format('Y-m-d'),
                    ]
                )
                ->where(
                    'status',
                    EmployeeAttendanceStatusEnum::INCOMPLETE->value
                )
                ->exists();

            if ($exists) {
                throw new PayrollHasIncompleteAttendanceException();
            }
        }
    }

    private function ensureNoPendingSessions(
        PayrollPeriod $period
    ): void {
        $payrolls = EmployeePayroll::query()
            ->where('payroll_period_id', $period->id)
            ->whereIn('salary_type', [
                SalaryTypeEnum::SESSION->value,
                SalaryTypeEnum::MONTHLY_PLUS_SESSION->value,
            ])
            ->get([
                'employee_id',
                'payable_from',
                'payable_to',
            ]);

        foreach ($payrolls as $payroll) {
            $hasPendingStandaloneSessions = EmployeeSession::query()
                ->where('employee_id', $payroll->employee_id)
                ->whereBetween('session_date', [
                    $payroll->payable_from->format('Y-m-d'),
                    $payroll->payable_to->format('Y-m-d'),
                ])
                ->where('status', EmployeeSessionStatusEnum::PENDING->value)
                ->exists();

            if ($hasPendingStandaloneSessions) {
                throw new PayrollHasPendingSessionsException();
            }

            $hasUnresolvedActivitySessions = DB::table('activity_session_employees')
                ->join(
                    'activity_sessions',
                    'activity_sessions.id',
                    '=',
                    'activity_session_employees.activity_session_id'
                )
                ->leftJoin('activity_session_employee_attendances', function ($join): void {
                    $join->on(
                        'activity_session_employee_attendances.activity_session_id',
                        '=',
                        'activity_session_employees.activity_session_id'
                    )->on(
                        'activity_session_employee_attendances.employee_id',
                        '=',
                        'activity_session_employees.employee_id'
                    );
                })
                ->where('activity_session_employees.employee_id', $payroll->employee_id)
                ->whereBetween('activity_sessions.session_date', [
                    $payroll->payable_from->format('Y-m-d'),
                    $payroll->payable_to->format('Y-m-d'),
                ])
                ->whereNull('activity_session_employee_attendances.id')
                ->exists();

            if ($hasUnresolvedActivitySessions) {
                throw new PayrollHasPendingSessionsException();
            }
        }
    }

}
