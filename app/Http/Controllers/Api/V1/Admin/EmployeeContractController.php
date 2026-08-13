<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EmployeeContract\StoreEmployeeContractRequest;
use App\Http\Requests\V1\EmployeeContract\UpdateEmployeeContractRequest;
use App\Http\Resources\V1\EmployeeContract\EmployeeContractCollection;
use App\Http\Resources\V1\EmployeeContract\EmployeeContractResource;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Services\Employee\EmployeeContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmployeeContractController extends Controller
{
    public function __construct(
        private readonly EmployeeContractService $employeeContractService
    ) {
    }

    /**
     * Display paginated contracts for an employee.
     *
     * GET /api/v1/admin/employees/{employee}/contracts
     */
    public function index(
        Employee $employee,
        Request $request
    ): JsonResponse {
        $contracts = $this->employeeContractService->allEmployeeContracts(
            $employee,
            $request
        );

        return ApiResponse::success(
            new EmployeeContractCollection($contracts)
        );
    }

    /**
     * Create a new contract for an employee.
     *
     * POST /api/v1/admin/employees/{employee}/contracts
     */
    public function store(
        StoreEmployeeContractRequest $request,
        Employee $employee
    ): JsonResponse {
        $contract = $this->employeeContractService
            ->createEmployeeContract(
                $employee,
                $request->validated()
            );

        return ApiResponse::success(
            new EmployeeContractResource($contract),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    /**
     * Display a specific employee contract.
     *
     * GET /api/v1/admin/employees/{employee}/contracts/{contract}
     */
    public function show(
        Employee $employee,
        EmployeeContract $contract
    ): JsonResponse {
        $contract = $this->employeeContractService
            ->editEmployeeContract(
                $contract
            );

        return ApiResponse::success(
            new EmployeeContractResource($contract)
        );
    }

    /**
     * Update employee contract.
     *
     * PUT/PATCH /api/v1/admin/employees/{employee}/contracts/{contract}
     */
    public function update(
        UpdateEmployeeContractRequest $request,
        Employee $employee,
        EmployeeContract $contract
    ): JsonResponse {
        $contract = $this->employeeContractService
            ->updateEmployeeContract(
                $contract,
                $request->validated()
            );

        return ApiResponse::success(
            new EmployeeContractResource($contract),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete employee contract.
     *
     * DELETE /api/v1/admin/employees/{employee}/contracts/{contract}
     */
    public function destroy(
        Employee $employee,
        EmployeeContract $contract
    ): JsonResponse {
        $this->employeeContractService
            ->deleteEmployeeContract(
                $contract
            );

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
