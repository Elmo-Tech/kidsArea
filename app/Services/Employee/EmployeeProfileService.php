<?php

declare(strict_types=1);

namespace App\Services\Employee;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class EmployeeProfileService
{
    public function getProfile(): Employee
    {
        $employeeId = Auth::user()?->employee_id;

        return Employee::query()
            ->with([
                'department',
                'jobTitle',
                'user',
            ])
            ->findOrFail($employeeId);
    }
}
