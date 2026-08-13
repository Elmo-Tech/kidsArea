<?php

namespace App\Services\Employee;

use App\Enums\ContractStatusEnum;
use App\Exceptions\Employee\EmployeeHasActiveContractException;
use App\Models\Employee;
use App\Models\EmployeeContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeContractService
{
    public function allEmployeeContracts(Employee $employee, Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(EmployeeContract::class)
            ->allowedFilters(
                AllowedFilter::exact('salaryType', 'salary_type'),
                AllowedFilter::exact('status'),
            )
            ->where('employee_id', $employee->id)
            ->latest('id')
            ->paginate($perPage);
    }

    public function createEmployeeContract(
        Employee $employee,
        array $data
    ): EmployeeContract {
        return DB::transaction(function () use ($employee, $data): EmployeeContract {

            $this->ensureEmployeeHasNoActiveContract($employee->id);

            return EmployeeContract::create([
                'employee_id' => $employee->id,

                'start_date' => $data['startDate'],
                'end_date' => $data['endDate'] ?? null,

                'salary_type' => $data['salaryType'],

                'basic_salary' => $data['basicSalary'] ?? null,
                'hourly_rate' => $data['hourlyRate'] ?? null,
                'session_rate' => $data['sessionRate'] ?? null,

                'required_monthly_hours' => $data['requiredMonthlyHours'] ?? null,

                'work_start_time' => $data['workStartTime'] ?? null,
                'work_end_time' => $data['workEndTime'] ?? null,
                'work_days' => $data['workDays'] ?? null,

                'status' => $data['status']
                    ?? ContractStatusEnum::ACTIVE->value,

                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
    public function editEmployeeContract(EmployeeContract $contract): EmployeeContract
    {
        return $contract;
    }

    public function updateEmployeeContract(
        EmployeeContract $contract,
        array $data
    ): EmployeeContract {
        return DB::transaction(function () use ($contract, $data): EmployeeContract {

            $contract->update([
                'end_date' => array_key_exists('endDate', $data)
                    ? $data['endDate']
                    : $contract->end_date,

                'required_monthly_hours' => array_key_exists('requiredMonthlyHours', $data)
                    ? $data['requiredMonthlyHours']
                    : $contract->required_monthly_hours,

                'work_start_time' => array_key_exists('workStartTime', $data)
                    ? $data['workStartTime']
                    : $contract->work_start_time,

                'work_end_time' => array_key_exists('workEndTime', $data)
                    ? $data['workEndTime']
                    : $contract->work_end_time,

                'work_days' => array_key_exists('workDays', $data)
                    ? $data['workDays']
                    : $contract->work_days,

                'status' => array_key_exists('status', $data)
                    ? $data['status']
                    : $contract->status,

                'notes' => array_key_exists('notes', $data)
                    ? $data['notes']
                    : $contract->notes,
            ]);

            return $contract->refresh();
        });
    }

    public function deleteEmployeeContract(EmployeeContract $contract): bool
    {
        return DB::transaction(
            fn (): bool => (bool) $contract->delete()
        );
    }

    private function ensureEmployeeHasNoActiveContract(
        int $employeeId
    ): void {
        $exists = EmployeeContract::query()
            ->where('employee_id', $employeeId)
            ->where('status', ContractStatusEnum::ACTIVE->value)
            ->exists();

        if ($exists) {
            throw new EmployeeHasActiveContractException();
        }
    }
}
