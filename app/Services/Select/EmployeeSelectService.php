<?php

namespace App\Services\Select;

use App\Models\Employee;
use App\Models\User;

class EmployeeSelectService
{
    public function getAllEmployees()
    {
        return Employee::all(['id as value','name as label']);
    }
}
