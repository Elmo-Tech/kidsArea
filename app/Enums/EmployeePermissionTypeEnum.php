<?php

namespace App\Enums;


enum EmployeePermissionTypeEnum: int
{
    case LATE_ARRIVAL = 0;
    case EARLY_LEAVE = 1;
    case DURING_WORK = 2;
}
