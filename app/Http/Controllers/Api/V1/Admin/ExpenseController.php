<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Expense\StoreExpenseRequest;
use App\Http\Requests\V1\Expense\UpdateExpenseRequest;
use App\Http\Resources\V1\Expense\ExpenseCollection;
use App\Http\Resources\V1\Expense\ExpenseResource;
use App\Models\Expense;
use App\Services\Expense\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            ExpenseCollection::make(
                $this->expenseService->all($request)
            )
        );
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $this->expenseService->create($request->validated());

        return ApiResponse::success(
            new ExpenseResource($expense),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(Expense $expense): JsonResponse
    {
        return ApiResponse::success(
            new ExpenseResource(
                $this->expenseService->show($expense)
            )
        );
    }

    public function update(
        UpdateExpenseRequest $request,
        Expense $expense
    ): JsonResponse {
        $expense = $this->expenseService->update(
            $expense,
            $request->validated()
        );

        return ApiResponse::success(
            new ExpenseResource($expense),
            __('messages.updated_successfully')
        );
    }
}
