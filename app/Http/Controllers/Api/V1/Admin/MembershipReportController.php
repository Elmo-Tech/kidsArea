<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Report\MembershipReportRequest;
use App\Services\Report\MembershipReportService;
use Illuminate\Http\JsonResponse;

class MembershipReportController extends Controller
{
    public function __construct(
        private readonly MembershipReportService $membershipReportService
    ) {}

    public function __invoke(MembershipReportRequest $request): JsonResponse
    {
        return ApiResponse::success(
            $this->membershipReportService->report(
                $request->validated()
            )
        );
    }
}
