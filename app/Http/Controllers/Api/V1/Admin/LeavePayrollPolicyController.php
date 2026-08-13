<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Payroll\StoreLeavePayrollPolicyRequest;
use App\Http\Requests\V1\Payroll\UpdateLeavePayrollPolicyRequest;
use App\Http\Resources\V1\Payroll\LeavePayrollPolicyCollection;
use App\Http\Resources\V1\Payroll\LeavePayrollPolicyResource;
use App\Models\LeavePayrollPolicy;
use App\Services\Payroll\LeavePayrollPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LeavePayrollPolicyController extends Controller
{
    public function __construct(
        private readonly LeavePayrollPolicyService $leavePayrollPolicyService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $policies = $this->leavePayrollPolicyService->all($request);

        return ApiResponse::success(
            new LeavePayrollPolicyCollection($policies)
        );
    }

    public function store(
        StoreLeavePayrollPolicyRequest $request
    ): JsonResponse {
        $policy = $this->leavePayrollPolicyService
            ->createLeavePayrollPolicy(
                $request->validated()
            );

        return ApiResponse::success(
            new LeavePayrollPolicyResource($policy),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        LeavePayrollPolicy $policy
    ): JsonResponse {
        $policy = $this->leavePayrollPolicyService
            ->editLeavePayrollPolicy($policy);

        return ApiResponse::success(
            new LeavePayrollPolicyResource($policy)
        );
    }

    public function update(
        UpdateLeavePayrollPolicyRequest $request,
        LeavePayrollPolicy $policy
    ): JsonResponse {
        $policy = $this->leavePayrollPolicyService
            ->updateLeavePayrollPolicy(
                $policy,
                $request->validated()
            );

        return ApiResponse::success(
            new LeavePayrollPolicyResource($policy),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        LeavePayrollPolicy $policy
    ): JsonResponse {
        $this->leavePayrollPolicyService
            ->deleteLeavePayrollPolicy($policy);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
