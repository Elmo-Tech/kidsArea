<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Models\EmployeePayroll;
use App\Models\PayrollPeriod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PayrollReportService
{
    public function all(
        PayrollPeriod $period,
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(EmployeePayroll::class)
            ->where(
                'payroll_period_id',
                $period->id
            )
            ->with([
                'employee.jobTitle',
            ])
            ->allowedFilters(
                AllowedFilter::exact(
                    'employeeId',
                    'employee_id'
                ),

                AllowedFilter::exact(
                    'salaryType',
                    'salary_type'
                ),

                AllowedFilter::callback(
                    'search',
                    function (
                        Builder $query,
                        mixed $value
                    ): void {
                        $search = trim((string) $value);

                        if ($search === '') {
                            return;
                        }

                        $query->whereHas(
                            'employee',
                            function (Builder $query) use ($search): void {
                                $query->where(function (
                                    Builder $query
                                ) use ($search): void {
                                    $query
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'national_id',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'phone',
                                            'like',
                                            "%{$search}%"
                                        );
                                });
                            }
                        );
                    }
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function getSummary(
        PayrollPeriod $period
    ): array {
        $query = EmployeePayroll::query()
            ->where(
                'payroll_period_id',
                $period->id
            );

        return [
            'employeesCount' =>
                (clone $query)->count(),

            'basicSalaryTotal' =>
                $this->formatAmount(
                    (clone $query)->sum('basic_salary')
                ),

            'proratedBasicSalaryTotal' =>
                $this->formatAmount(
                    (clone $query)->sum('prorated_basic_salary')
                ),

            'hourlyEarningsTotal' =>
                $this->formatAmount(
                    (clone $query)->sum('hourly_earnings')
                ),

            'sessionEarningsTotal' =>
                $this->formatAmount(
                    (clone $query)->sum('session_earnings')
                ),

            'additionsTotal' =>
                $this->formatAmount(
                    (clone $query)->sum('additions_total')
                ),

            'attendanceDeductionsTotal' =>
                $this->formatAmount(
                    (clone $query)->sum('attendance_deductions')
                ),

            'manualDeductionsTotal' =>
                $this->formatAmount(
                    (clone $query)->sum('deductions_total')
                ),

            'grossSalaryTotal' =>
                $this->formatAmount(
                    (clone $query)->sum('gross_salary')
                ),

            'netSalaryTotal' =>
                $this->formatAmount(
                    (clone $query)->sum('net_salary')
                ),
        ];
    }

    private function formatAmount(
        int|float|string|null $amount
    ): string {
        return number_format(
            (float) ($amount ?? 0),
            2,
            '.',
            ''
        );
    }
}
