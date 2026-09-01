<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CashTransaction\StoreCashTransactionRequest;
use App\Http\Resources\V1\CashTransaction\CashTransactionCollection;
use App\Http\Resources\V1\CashTransaction\CashTransactionResource;
use App\Models\CashRegister;
use App\Models\CashShift;
use App\Services\Cash\CashTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CashTransactionController extends Controller
{
    public function __construct(
        private readonly CashTransactionService $cashTransactionService
    ) {}

    public function indexForShift(
        Request $request,
        CashShift $cashShift
    ): JsonResponse {
        $transactions = $this->cashTransactionService->allForShift(
            $cashShift,
            $request
        );

        return ApiResponse::success(
            new CashTransactionCollection($transactions)
        );
    }

    public function indexForRegister(
        Request $request,
        CashRegister $cashRegister
    ): JsonResponse {
        $transactions = $this->cashTransactionService->allForRegister(
            $cashRegister,
            $request
        );

        return ApiResponse::success(
            new CashTransactionCollection($transactions)
        );
    }

    public function store(
        StoreCashTransactionRequest $request
    ): JsonResponse {
        $transaction = $this->cashTransactionService->createManual(
            $request->validated()
        );

        return ApiResponse::success(
            new CashTransactionResource($transaction),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }
}
