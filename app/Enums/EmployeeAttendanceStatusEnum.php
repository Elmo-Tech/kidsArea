<?php

namespace App\Enums;

enum EmployeeAttendanceStatusEnum: int
{
    case PRESENT = 0;
    case ABSENT = 1;
    case LEAVE = 2;
    case INCOMPLETE = 3;
}
