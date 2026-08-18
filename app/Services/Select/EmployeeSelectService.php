<?php

namespace App\Services\Select;

use App\Models\Employee;

class EmployeeSelectService
{
    public function getAllEmployees()
    {
        return Employee::all(['id as value','name as label']);
    }

    public function getAllEmployeesWithJob()
    {
        return Employee::with('jobTitle:id,name')
            ->get(['id', 'name', 'job_title_id'])
            ->map(function ($employee) {
                return [
                    'value' => $employee->id,
                    'label' => $employee->name . ' - ' . ($employee->jobTitle?->name ?? ''),
                ];
            });
    }
}
