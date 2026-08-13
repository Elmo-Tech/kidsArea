<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LeaveType\StoreLeaveTypeRequest;
use App\Http\Requests\V1\LeaveType\UpdateLeaveTypeRequest;
use App\Http\Resources\V1\LeaveType\LeaveTypeCollection;
use App\Http\Resources\V1\LeaveType\LeaveTypeResource;
use App\Models\LeaveType;
use App\Services\Employee\LeaveTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LeaveTypeController extends Controller
{
    public function __construct(
        private readonly LeaveTypeService $leaveTypeService
    ) {
    }

    /**
     * Display paginated leave types.
     *
     * GET /api/v1/admin/leave-types
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            max($request->integer('perPage', 15), 1),
            100
        );

        $leaveTypes = $this->leaveTypeService->all($perPage);

        return ApiResponse::success(
            new LeaveTypeCollection($leaveTypes)
        );
    }

    /**
     * Create leave type.
     *
     * POST /api/v1/admin/leave-types
     */
    public function store(
        StoreLeaveTypeRequest $request
    ): JsonResponse {
        $leaveType = $this->leaveTypeService->createLeaveType(
            $request->validated()
        );

        return ApiResponse::success(
            new LeaveTypeResource($leaveType),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    /**
     * Display leave type data for editing.
     *
     * GET /api/v1/admin/leave-types/{leaveType}/edit
     */
    public function show(
        LeaveType $leaveType
    ): JsonResponse {
        $leaveType = $this->leaveTypeService
            ->editLeaveType($leaveType);

        return ApiResponse::success(
            new LeaveTypeResource($leaveType)
        );
    }

    /**
     * Update leave type.
     *
     * PATCH /api/v1/admin/leave-types/{leaveType}
     */
    public function update(
        UpdateLeaveTypeRequest $request,
        LeaveType $leaveType
    ): JsonResponse {
        $leaveType = $this->leaveTypeService
            ->updateLeaveType(
                $leaveType,
                $request->validated()
            );

        return ApiResponse::success(
            new LeaveTypeResource($leaveType),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete leave type.
     *
     * DELETE /api/v1/admin/leave-types/{leaveType}
     */
    public function destroy(
        LeaveType $leaveType
    ): JsonResponse {
        $this->leaveTypeService
            ->deleteLeaveType($leaveType);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
