<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EmployeeAttendance\SyncEmployeeAttendanceRequest;
use App\Services\Employee\EmployeeAttendanceService;
use Illuminate\Http\JsonResponse;

final class SyncEmployeeAttendanceController extends Controller
{
    public function __construct(
        private readonly EmployeeAttendanceService $employeeAttendanceService
    ) {
    }

    /**
     * Synchronize employee attendance for a specific day.
     *
     * POST /api/v1/admin/employee-attendances/sync-day
     */
    public function __invoke(
        SyncEmployeeAttendanceRequest $request
    ): JsonResponse {
        $this->employeeAttendanceService->syncDay(
            $request->validated('date')
        );

        return ApiResponse::success(
            null,
            __('messages.updated_successfully')
        );
    }
}
