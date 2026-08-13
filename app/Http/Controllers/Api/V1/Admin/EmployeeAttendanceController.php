<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EmployeeAttendance\StoreEmployeeAttendanceRequest;
use App\Http\Requests\V1\EmployeeAttendance\UpdateEmployeeAttendanceRequest;
use App\Http\Resources\V1\EmployeeAttendance\AllEmployeeAttendanceResource;
use App\Http\Resources\V1\EmployeeAttendance\EmployeeAttendanceCollection;
use App\Http\Resources\V1\EmployeeAttendance\EmployeeAttendanceResource;
use App\Models\EmployeeAttendance;
use App\Services\Employee\EmployeeAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmployeeAttendanceController extends Controller
{
    public function __construct(
        private readonly EmployeeAttendanceService $employeeAttendanceService
    ) {
    }

    /**
     * Display paginated employee attendances.
     *
     * GET /api/v1/admin/employee-attendances
     */
    public function index(Request $request): JsonResponse
    {
        $attendances = $this->employeeAttendanceService->all($request);

        return ApiResponse::success(
            new EmployeeAttendanceCollection($attendances)
        );
    }

    /**
     * Create employee attendance manually.
     *
     * POST /api/v1/admin/employee-attendances
     */
    public function store(
        StoreEmployeeAttendanceRequest $request
    ): JsonResponse {
        $attendance = $this->employeeAttendanceService
            ->createEmployeeAttendance(
                $request->validated()
            );

        return ApiResponse::success(
            new AllEmployeeAttendanceResource($attendance),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    /**
     * Display attendance data for editing.
     *
     * GET /api/v1/admin/employee-attendances/{attendance}/edit
     */
    public function edit(
        EmployeeAttendance $attendance
    ): JsonResponse {
        $attendance = $this->employeeAttendanceService
            ->editEmployeeAttendance($attendance);

        return ApiResponse::success(
            new EmployeeAttendanceResource($attendance)
        );
    }

    /**
     * Update employee attendance.
     *
     * PATCH /api/v1/admin/employee-attendances/{attendance}
     */
    public function update(
        UpdateEmployeeAttendanceRequest $request,
        EmployeeAttendance $attendance
    ): JsonResponse {
        $attendance = $this->employeeAttendanceService
            ->updateEmployeeAttendance(
                $attendance,
                $request->validated()
            );

        return ApiResponse::success(
            new AllEmployeeAttendanceResource($attendance),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete employee attendance.
     *
     * DELETE /api/v1/admin/employee-attendances/{attendance}
     */
    public function destroy(
        EmployeeAttendance $attendance
    ): JsonResponse {
        $this->employeeAttendanceService
            ->deleteEmployeeAttendance($attendance);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }

    /**
     * Synchronize attendance for a specific day.
     *
     * POST /api/v1/admin/employee-attendances/sync-day
     */
    public function syncDay(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'date' => [
                'required',
                'date_format:Y-m-d',
            ],
        ]);

        $this->employeeAttendanceService
            ->syncDay(
                $validated['date']
            );

        return ApiResponse::success(
            null,
            __('messages.updated_successfully')
        );
    }
}
