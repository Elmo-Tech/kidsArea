<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Employee\StoreEmployeeRequest;
use App\Http\Requests\V1\Employee\UpdateEmployeeRequest;
use App\Http\Resources\V1\Employee\EmployeeCollection;
use App\Http\Resources\V1\Employee\EmployeeResource;
use App\Models\Employee;
use App\Services\Employee\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService
    ) {
    }

    /**
     * Display paginated employees.
     *
     * GET /api/v1/admin/employees
     */
    public function index(Request $request): JsonResponse
    {
        $employees = $this->employeeService->all($request);

        return ApiResponse::success(
            new EmployeeCollection($employees)
        );
    }

    /**
     * Create a new employee.
     *
     * POST /api/v1/admin/employees
     */
    public function store(
        StoreEmployeeRequest $request
    ): JsonResponse {
        $employee = $this->employeeService->createEmployee(
            $request->validated()
        );

        return ApiResponse::success(
            new EmployeeResource($employee),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    /**
     * Display employee data for editing.
     *
     * GET /api/v1/admin/employees/{employee}/edit
     */
    public function show(
        Employee $employee
    ): JsonResponse {
        $employee = $this->employeeService->editEmployee($employee);

        return ApiResponse::success(
            new EmployeeResource($employee)
        );
    }

    /**
     * Update employee.
     *
     * PUT/PATCH /api/v1/admin/employees/{employee}
     */
    public function update(
        UpdateEmployeeRequest $request,
        Employee $employee
    ): JsonResponse {
        $employee = $this->employeeService->updateEmployee(
            $employee,
            $request->validated()
        );

        return ApiResponse::success(
            new EmployeeResource($employee),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete employee.
     *
     * DELETE /api/v1/admin/employees/{employee}
     */
    public function destroy(
        Employee $employee
    ): JsonResponse {
        $this->employeeService->deleteEmployee($employee);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
