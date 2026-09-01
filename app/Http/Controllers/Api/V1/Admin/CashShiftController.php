<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CashShift\CloseCashShiftRequest;
use App\Http\Requests\V1\CashShift\OpenCashShiftRequest;
use App\Http\Resources\V1\CashShift\CashShiftResource;
use App\Models\CashShift;
use App\Services\Cash\CashShiftService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\V1\CashShift\CashShiftCollection;
use Illuminate\Http\Request;
final class CashShiftController extends Controller
{
    public function __construct(
        private readonly CashShiftService $cashShiftService
    ) {}


    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new CashShiftCollection(
                $this->cashShiftService->all($request)
            )
        );
    }
    public function open(OpenCashShiftRequest $request): JsonResponse
    {
        $shift = $this->cashShiftService->open($request->validated());

        return ApiResponse::success(
            new CashShiftResource($shift),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(CashShift $cashShift): JsonResponse
    {
        return ApiResponse::success(
            new CashShiftResource(
                $this->cashShiftService->show($cashShift)
            )
        );
    }

    public function summary(CashShift $cashShift): JsonResponse
    {
        return ApiResponse::success(
            $this->cashShiftService->summary($cashShift)
        );
    }

    public function close(
        CloseCashShiftRequest $request,
        CashShift $cashShift
    ): JsonResponse {
        $shift = $this->cashShiftService->close(
            $cashShift,
            $request->validated()
        );

        return ApiResponse::success(
            new CashShiftResource($shift),
            __('messages.updated_successfully')
        );
    }
}
