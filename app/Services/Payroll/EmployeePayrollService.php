<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Exceptions\Payroll\EmployeePayrollNotBelongToPeriodException;
use App\Models\EmployeePayroll;
use App\Models\PayrollPeriod;
use App\QueryFilters\EmployeePayrollSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeePayrollService
{
    public function allForPeriod(
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
                'contract',
            ])
            ->allowedFilters(
                AllowedFilter::custom(
                    'search',
                    new EmployeePayrollSearchFilter()
                ),

                AllowedFilter::exact(
                    'employeeId',
                    'employee_id'
                ),

                AllowedFilter::exact(
                    'salaryType',
                    'salary_type'
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function showForPeriod(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll
    ): EmployeePayroll {
        $this->ensureEmployeePayrollBelongsToPeriod(
            $period,
            $employeePayroll
        );

        return $employeePayroll->load([
            'employee.jobTitle',
            'contract',
            'adjustments',
        ]);
    }

    private function ensureEmployeePayrollBelongsToPeriod(
        PayrollPeriod $period,
        EmployeePayroll $employeePayroll
    ): void {
        if (
            $employeePayroll->payroll_period_id !== $period->id
        ) {
            throw new EmployeePayrollNotBelongToPeriodException();
        }
    }
}
