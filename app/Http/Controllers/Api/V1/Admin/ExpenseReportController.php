<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Report\ExpenseReportRequest;
use App\Services\Report\ExpenseReportService;
use Illuminate\Http\JsonResponse;

class ExpenseReportController extends Controller
{
    public function __construct(
        private readonly ExpenseReportService $expenseReportService
    ) {}

    public function __invoke(ExpenseReportRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->expenseReportService->report(
                $request->validated()
            )
        );
    }
}
