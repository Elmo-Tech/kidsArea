<?php

namespace App\Services\Select;

use App\Models\Department;

class DepartmentSelectService
{
    public function getAllDepartments()
    {
        return Department::all(['id as value','name as label']);
    }
}
