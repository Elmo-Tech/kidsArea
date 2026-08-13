<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EmployeePermission\StoreEmployeePermissionRequest;
use App\Http\Requests\V1\EmployeePermission\UpdateEmployeePermissionRequest;
use App\Http\Resources\V1\EmployeePermission\EmployeePermissionCollection;
use App\Http\Resources\V1\EmployeePermission\EmployeePermissionResource;
use App\Models\EmployeePermission;
use App\Services\Employee\EmployeePermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmployeePermissionController extends Controller
{
    public function __construct(
        private readonly EmployeePermissionService $employeePermissionService
    ) {
    }

    /**
     * Display paginated employee permissions.
     *
     * GET /api/v1/admin/permissions
     */
    public function index(Request $request): JsonResponse
    {
        $permissions = $this->employeePermissionService->all($request);

        return ApiResponse::success(
            new EmployeePermissionCollection($permissions)
        );
    }

    /**
     * Create a new employee permission.
     *
     * POST /api/v1/admin/permissions
     */
    public function store(
        StoreEmployeePermissionRequest $request
    ): JsonResponse {
        $permission = $this->employeePermissionService
            ->createPermission(
                $request->validated()
            );

        return ApiResponse::success(
            new EmployeePermissionResource($permission),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    /**
     * Display a specific employee permission.
     *
     * GET /api/v1/admin/permissions/{permission}
     */
    public function show(
        EmployeePermission $permission
    ): JsonResponse {
        $permission = $this->employeePermissionService
            ->showPermission($permission);

        return ApiResponse::success(
            new EmployeePermissionResource($permission)
        );
    }

    /**
     * Update employee permission.
     *
     * PUT/PATCH /api/v1/admin/permissions/{permission}
     */
    public function update(
        UpdateEmployeePermissionRequest $request,
        EmployeePermission $permission
    ): JsonResponse {
        $permission = $this->employeePermissionService
            ->updatePermission(
                $permission,
                $request->validated()
            );

        return ApiResponse::success(
            new EmployeePermissionResource($permission),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete employee permission.
     *
     * DELETE /api/v1/admin/permissions/{permission}
     */
    public function destroy(
        EmployeePermission $permission
    ): JsonResponse {
        $this->employeePermissionService
            ->deletePermission($permission);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }

    /**
     * Approve employee permission.
     *
     * PATCH /api/v1/admin/permissions/{permission}/approve
     */
    public function approve(
        EmployeePermission $permission
    ): JsonResponse {
        $permission = $this->employeePermissionService
            ->approvePermission($permission);

        return ApiResponse::success(
            null,
            __('messages.updated_successfully')
        );
    }

    /**
     * Reject employee permission.
     *
     * PATCH /api/v1/admin/permissions/{permission}/reject
     */
    public function reject(
        EmployeePermission $permission
    ): JsonResponse {
        $permission = $this->employeePermissionService
            ->rejectPermission($permission);

        return ApiResponse::success(
            null,
            __('messages.updated_successfully')
        );
    }
}
