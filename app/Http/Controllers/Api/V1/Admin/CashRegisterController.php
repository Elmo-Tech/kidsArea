<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CashRegister\StoreCashRegisterRequest;
use App\Http\Requests\V1\CashRegister\UpdateCashRegisterRequest;
use App\Http\Resources\V1\CashRegister\CashRegisterCollection;
use App\Http\Resources\V1\CashRegister\CashRegisterResource;
use App\Models\CashRegister;
use App\Services\Cash\CashRegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CashRegisterController extends Controller
{
    public function __construct(
        private readonly CashRegisterService $cashRegisterService
    ) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new CashRegisterCollection(
                $this->cashRegisterService->all($request)
            )
        );
    }

    public function store(StoreCashRegisterRequest $request): JsonResponse
    {
        $register = $this->cashRegisterService->create($request->validated());

        return ApiResponse::success(
            new CashRegisterResource($register),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(CashRegister $cashRegister): JsonResponse
    {
        return ApiResponse::success(
            new CashRegisterResource(
                $this->cashRegisterService->show($cashRegister)
            )
        );
    }

    public function update(
        UpdateCashRegisterRequest $request,
        CashRegister $cashRegister
    ): JsonResponse {
        $register = $this->cashRegisterService->update(
            $cashRegister,
            $request->validated()
        );

        return ApiResponse::success(
            new CashRegisterResource($register),
            __('messages.updated_successfully')
        );
    }

    public function destroy(CashRegister $cashRegister): JsonResponse
    {
        $this->cashRegisterService->delete($cashRegister);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }

    public function main(): JsonResponse
    {
        return ApiResponse::success(
            new CashRegisterResource(
                $this->cashRegisterService->getMainRegister()
            )
        );
    }
}
