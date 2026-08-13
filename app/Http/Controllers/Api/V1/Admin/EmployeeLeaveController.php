<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EmployeeLeave\StoreEmployeeLeaveRequest;
use App\Http\Requests\V1\EmployeeLeave\UpdateEmployeeLeaveRequest;
use App\Http\Resources\V1\EmployeeLeave\EmployeeLeaveCollection;
use App\Http\Resources\V1\EmployeeLeave\EmployeeLeaveResource;
use App\Models\EmployeeLeave;
use App\Services\Employee\EmployeeLeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmployeeLeaveController extends Controller
{
    public function __construct(
        private readonly EmployeeLeaveService $employeeLeaveService
    ) {
    }

    /**
     * Display paginated employee leaves.
     *
     * GET /api/v1/admin/employee-leaves
     */
    public function index(Request $request): JsonResponse
    {
        $leaves = $this->employeeLeaveService->all($request);

        return ApiResponse::success(
            new EmployeeLeaveCollection($leaves)
        );
    }

    /**
     * Create employee leave.
     *
     * POST /api/v1/admin/employee-leaves
     */
    public function store(
        StoreEmployeeLeaveRequest $request
    ): JsonResponse {
        $leave = $this->employeeLeaveService
            ->createEmployeeLeave(
                $request->validated()
            );

        return ApiResponse::success(
            new EmployeeLeaveResource($leave),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    /**
     * Display employee leave data for editing.
     *
     * GET /api/v1/admin/employee-leaves/{leave}/edit
     */
    public function show(
        EmployeeLeave $leave
    ): JsonResponse {
        $leave = $this->employeeLeaveService
            ->editEmployeeLeave($leave);

        return ApiResponse::success(
            new EmployeeLeaveResource($leave)
        );
    }

    /**
     * Update employee leave.
     *
     * PATCH /api/v1/admin/employee-leaves/{leave}
     */
    public function update(
        UpdateEmployeeLeaveRequest $request,
        EmployeeLeave $leave
    ): JsonResponse {
        $leave = $this->employeeLeaveService
            ->updateEmployeeLeave(
                $leave,
                $request->validated()
            );

        return ApiResponse::success(
            new EmployeeLeaveResource($leave),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete employee leave.
     *
     * DELETE /api/v1/admin/employee-leaves/{leave}
     */
    public function destroy(
        EmployeeLeave $leave
    ): JsonResponse {
        $this->employeeLeaveService
            ->deleteEmployeeLeave($leave);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }

    /**
     * Approve employee leave.
     *
     * PATCH /api/v1/admin/employee-leaves/{leave}/approve
     */
    public function approve(
        EmployeeLeave $leave
    ): JsonResponse {
        $leave = $this->employeeLeaveService
            ->approveEmployeeLeave($leave);

        return ApiResponse::success(
            null,
            __('messages.updated_successfully')
        );
    }

    /**
     * Reject employee leave.
     *
     * PATCH /api/v1/admin/employee-leaves/{leave}/reject
     */
    public function reject(
        EmployeeLeave $leave
    ): JsonResponse {
        $leave = $this->employeeLeaveService
            ->rejectEmployeeLeave($leave);

        return ApiResponse::success(
            null,
            __('messages.updated_successfully')
        );
    }
}
