<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Payroll\StorePayrollPeriodRequest;
use App\Http\Requests\V1\Payroll\UpdatePayrollPeriodRequest;
use App\Http\Resources\V1\Payroll\PayrollPeriodCollection;
use App\Http\Resources\V1\Payroll\PayrollPeriodResource;
use App\Models\PayrollPeriod;
use App\Services\Payroll\PayrollPeriodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PayrollPeriodController extends Controller
{
    public function __construct(
        private readonly PayrollPeriodService $payrollPeriodService
    ) {
    }

    /**
     * Display paginated payroll periods.
     *
     * GET /api/v1/admin/payroll-periods
     */
    public function index(
        Request $request
    ): JsonResponse {
        $periods = $this->payrollPeriodService
            ->all($request);

        return ApiResponse::success(
            new PayrollPeriodCollection($periods)
        );
    }

    /**
     * Create payroll period.
     *
     * POST /api/v1/admin/payroll-periods
     */
    public function store(
        StorePayrollPeriodRequest $request
    ): JsonResponse {
        $period = $this->payrollPeriodService
            ->createPayrollPeriod(
                $request->validated()
            );

        return ApiResponse::success(
            new PayrollPeriodResource($period),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    /**
     * Display payroll period.
     *
     * GET /api/v1/admin/payroll-periods/{period}
     */
    public function show(
        PayrollPeriod $period
    ): JsonResponse {
        $period = $this->payrollPeriodService
            ->editPayrollPeriod($period);

        return ApiResponse::success(
            new PayrollPeriodResource($period)
        );
    }

    /**
     * Update payroll period.
     *
     * PATCH /api/v1/admin/payroll-periods/{period}
     */
    public function update(
        UpdatePayrollPeriodRequest $request,
        PayrollPeriod $period
    ): JsonResponse {
        $period = $this->payrollPeriodService
            ->updatePayrollPeriod(
                $period,
                $request->validated()
            );

        return ApiResponse::success(
            new PayrollPeriodResource($period),
            __('messages.updated_successfully')
        );
    }

    /**
     * Delete payroll period.
     *
     * DELETE /api/v1/admin/payroll-periods/{period}
     */
    public function destroy(
        PayrollPeriod $period
    ): JsonResponse {
        $this->payrollPeriodService
            ->deletePayrollPeriod($period);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
