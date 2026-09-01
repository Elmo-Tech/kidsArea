<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Cash\CashSummaryService;
use Illuminate\Http\JsonResponse;

final class CashSummaryController extends Controller
{
    public function __construct(
        private readonly CashSummaryService $cashSummaryService
    ) {}

    public function general(): JsonResponse
    {
        return ApiResponse::success(
            $this->cashSummaryService->general()
        );
    }
}
