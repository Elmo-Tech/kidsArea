<?php

namespace App\Services\Select;

use App\Models\Employee;
use App\Models\JobTitle;

class JobTitleSelectService
{
    public function getAllJobTitles()
    {
        return JobTitle::all(['id as value','name as label']);
    }
}
