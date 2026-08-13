<?php

namespace App\Services\Employee;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\QueryFilters\EmployeeSearchFilter;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeService
{
    public function all(Request $request): LengthAwarePaginator
    {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(Employee::class)
            ->with([
                'department',
                'jobTitle',
                'user',
            ])
            ->allowedFilters(
                AllowedFilter::custom('search', new EmployeeSearchFilter()),
                AllowedFilter::exact('departmentId', 'department_id'),
                AllowedFilter::exact('jobTitleId', 'job_title_id')
            )
            ->latest('id')
            ->paginate($perPage);
    }
    public function createEmployee(array $data): Employee
    {
        return DB::transaction(function () use ($data): Employee {
            return Employee::create([
                'email' => $data['email'],
                'name' => $data['name'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'national_id' => $data['nationalId'],
                'department_id' => $data['departmentId'],
                'job_title_id' => $data['jobTitleId'],
            ])
                ->load([
                    'department',
                    'jobTitle',
                    'user',
                ]);
        });
    }

    public function editEmployee(Employee $employee): Employee
    {
        return $employee->load([
            'department',
            'jobTitle',
            'user',
            'currentContract'
        ]);
    }

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data): Employee {
            $employee->update([
                'email' => $data['email'],
                'name' => $data['name'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'national_id' => $data['nationalId'],
                'department_id' => $data['departmentId'],
                'job_title_id' => $data['jobTitleId'],
            ]);

            return $employee
                ->refresh()
                ->load([
                    'department',
                    'jobTitle',
                    'user',
                ]);
        });
    }

    public function deleteEmployee(Employee $employee): bool
    {
        return DB::transaction(function () use ($employee): bool {
            if ($employee->user) {
                $employee->user->tokens()->delete();

                $employee->user->update([
                    'employee_id' => null,
                    'status' => 0,
                ]);
            }

            return (bool) $employee->delete();
        });
    }
}
