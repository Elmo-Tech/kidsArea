<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ExpenseCategory\StoreExpenseCategoryRequest;
use App\Http\Requests\V1\ExpenseCategory\UpdateExpenseCategoryRequest;
use App\Http\Resources\V1\ExpenseCategory\ExpenseCategoryCollection;
use App\Http\Resources\V1\ExpenseCategory\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use App\Services\ExpenseCategory\ExpenseCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ExpenseCategoryController extends Controller
{
    public function __construct(
        private readonly ExpenseCategoryService $expenseCategoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            ExpenseCategoryCollection::make(
                $this->expenseCategoryService->all($request)
            )
        );
    }

    public function store(StoreExpenseCategoryRequest $request): JsonResponse
    {
        $category = $this->expenseCategoryService->create($request->validated());

        return ApiResponse::success(
            new ExpenseCategoryResource($category),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(ExpenseCategory $expenseCategory): JsonResponse
    {
        return ApiResponse::success(
            new ExpenseCategoryResource(
                $this->expenseCategoryService->show($expenseCategory)
            )
        );
    }

    public function update(
        UpdateExpenseCategoryRequest $request,
        ExpenseCategory $expenseCategory
    ): JsonResponse {
        $category = $this->expenseCategoryService->update(
            $expenseCategory,
            $request->validated()
        );

        return ApiResponse::success(
            new ExpenseCategoryResource($category),
            __('messages.updated_successfully')
        );
    }

    public function destroy(ExpenseCategory $expenseCategory): JsonResponse
    {
        $this->expenseCategoryService->delete($expenseCategory);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
