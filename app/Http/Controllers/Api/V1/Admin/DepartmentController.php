<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Department\StoreDepartmentRequest;
use App\Http\Requests\V1\Department\UpdateDepartmentRequest;
use App\Http\Resources\V1\Department\DepartmentCollection;
use App\Http\Resources\V1\Department\DepartmentResource;
use App\Models\Department;
use App\Services\Department\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $departmentService
    ) {
    }

    /**
     * Display paginated departments.
     *
     * GET /api/v1/admin/departments
     */
    public function index(Request $request): JsonResponse
    {
        $departments = $this->departmentService->all($request);

        return ApiResponse::success(
            new DepartmentCollection($departments)
        );
    }

    /**
     * Create a new department.
     *
     * POST /api/v1/admin/departments
     */
    public function store(
        StoreDepartmentRequest $request
    ): JsonResponse {
        $department = $this->departmentService->createDepartment(
            $request->validated()
        );

        return ApiResponse::success(
            new DepartmentResource($department),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    /**
     * Display department data for editing.
     *
     * GET /api/v1/admin/departments/{department}/edit
     */
    public function show(
        Department $department
    ): JsonResponse {
        $department = $this->departmentService->edit($department);

        return ApiResponse::success(
            new DepartmentResource($department)
        );
    }

    /**
     * Update department.
     *
     * PUT/PATCH /api/v1/admin/departments/{department}
     */
    public function update(
        UpdateDepartmentRequest $request,
        Department $department
    ): JsonResponse {
        $department = $this->departmentService->update(
            $department,
            $request->validated()
        );

        return ApiResponse::success(
            new DepartmentResource($department),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete department.
     *
     * DELETE /api/v1/admin/departments/{department}
     */
    public function destroy(
        Department $department
    ): JsonResponse {
        $this->departmentService->deleteDepartment($department);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
