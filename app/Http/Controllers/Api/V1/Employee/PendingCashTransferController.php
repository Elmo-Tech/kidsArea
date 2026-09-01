<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Employee;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CashShift\CashShiftResource;
use App\Services\Employee\EmployeeCashShiftService;
use Illuminate\Http\JsonResponse;

final class PendingCashTransferController extends Controller
{
    public function __construct(
        private readonly EmployeeCashShiftService $employeeCashShiftService
    ) {}

    public function __invoke(): JsonResponse
    {
        $shift = $this->employeeCashShiftService->pendingTransfer();

        return ApiResponse::success(
            [
                'hasPendingTransfer' => $shift !== null,
                'cashShift' => $shift
                    ? new CashShiftResource($shift)
                    : null,
            ],
            '',
            HttpStatusCode::OK
        );
    }
}
