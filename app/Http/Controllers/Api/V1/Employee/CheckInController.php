<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Employee;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EmployeeAttendance\EmployeeAttendanceResource;
use App\Services\Employee\EmployeeAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CheckInController extends Controller
{
    public function __construct(
        private readonly EmployeeAttendanceService $employeeAttendanceService
    ) {
    }

    public function __invoke(
        Request $request
    ): JsonResponse {
        $attendance = $this->employeeAttendanceService
            ->checkIn(
                $request->user()
            );

        return ApiResponse::success(
            new EmployeeAttendanceResource($attendance),
            __('employee.check_in_successfully')
        );
    }
}
