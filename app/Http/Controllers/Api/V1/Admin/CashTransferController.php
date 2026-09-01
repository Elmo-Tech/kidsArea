<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CashTransfer\StoreCashTransferRequest;
use App\Http\Resources\V1\CashTransfer\CashTransferResource;
use App\Services\Cash\CashTransferService;
use Illuminate\Http\JsonResponse;

final class CashTransferController extends Controller
{
    public function __construct(
        private readonly CashTransferService $cashTransferService
    ) {}

    public function store(
        StoreCashTransferRequest $request
    ): JsonResponse {
        $transfer = $this->cashTransferService
            ->transferCurrentUserShiftToMain(
                $request->validated()
            );

        return ApiResponse::success(
            new CashTransferResource($transfer),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }
}
