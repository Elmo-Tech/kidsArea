<?php

namespace App\Services\Select;

use App\Models\LeaveType;

class LeaveSelectService extends SelectService
{
    public function getAllLeaveTypes()
    {
        return LeaveType::all(['id as value','name as label']);
    }
}
