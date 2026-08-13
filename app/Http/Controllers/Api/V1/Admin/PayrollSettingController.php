<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Payroll\UpdatePayrollSettingRequest;
use App\Http\Resources\V1\Payroll\PayrollSettingResource;
use App\Services\Payroll\PayrollSettingService;
use Illuminate\Http\JsonResponse;

final class PayrollSettingController extends Controller
{
    public function __construct(
        private readonly PayrollSettingService $payrollSettingService
    ) {
    }

    /**
     * Display payroll settings.
     *
     * GET /api/v1/admin/payroll-settings
     */
    public function show(): JsonResponse
    {
        $settings = $this->payrollSettingService
            ->getSettings();

        return ApiResponse::success(
            new PayrollSettingResource($settings)
        );
    }

    /**
     * Update payroll settings.
     *
     * PATCH /api/v1/admin/payroll-settings
     */
    public function update(
        UpdatePayrollSettingRequest $request
    ): JsonResponse {
        $settings = $this->payrollSettingService
            ->updateSettings(
                $request->validated()
            );

        return ApiResponse::success(
            new PayrollSettingResource($settings),
            __('messages.updated_successfully')
        );
    }
}
