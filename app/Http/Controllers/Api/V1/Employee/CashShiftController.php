<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Employee;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CashShift\CloseCashShiftRequest;
use App\Http\Requests\V1\CashShift\OpenCashShiftRequest;
use App\Http\Requests\V1\EmployeeCashShift\TransferToMainCashRegisterRequest;
use App\Http\Resources\V1\CashShift\CashShiftResource;
use App\Http\Resources\V1\CashTransfer\CashTransferResource;
use App\Services\Cash\CashTransferService;
use App\Services\Employee\EmployeeCashShiftService;
use Illuminate\Http\JsonResponse;

final class CashShiftController extends Controller
{
   public function __construct(
    private readonly EmployeeCashShiftService $cashShiftService,
    private readonly CashTransferService $cashTransferService
) {}

    public function current(): JsonResponse
    {
        $shift = $this->cashShiftService->current();

        return ApiResponse::success(
            $shift
                ? new CashShiftResource($shift)
                : null
        );
    }

    public function open(
        OpenCashShiftRequest $request
    ): JsonResponse {
        $shift = $this->cashShiftService->open(
            $request->validated()
        );

        return ApiResponse::success(
            new CashShiftResource($shift)
        );
    }

    public function close(
        CloseCashShiftRequest $request
    ): JsonResponse {
        $shift = $this->cashShiftService->close(
            $request->validated()
        );

        return ApiResponse::success(
            new CashShiftResource($shift)
        );
    }

    public function transferToMain(
        TransferToMainCashRegisterRequest $request
    ): JsonResponse {
        $transfer = $this->cashTransferService
            ->transferCurrentUserShiftToMain(
                $request->validated()
            );

        return ApiResponse::success(
            new CashTransferResource($transfer)
        );
    }
}
