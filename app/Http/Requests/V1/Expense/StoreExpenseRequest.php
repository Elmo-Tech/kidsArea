<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Expense;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expenseCategoryId' => [
                'required',
                'integer',
                'exists:expense_categories,id',
            ],
            'cashRegisterId' => [
                'required',
                'integer',
                'exists:cash_registers,id',
            ],

            'cashShiftId' => [
                'nullable',
                'integer',
                'exists:cash_shifts,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'expenseAt' => [
                'sometimes',
                'nullable',
                'date_format:Y-m-d H:i:s',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    protected function failedValidation(
        Validator $validator
    ): void {
        throw new HttpResponseException(
            ApiResponse::error(
                '',
                $validator->errors()->toArray(),
                HttpStatusCode::UNPROCESSABLE_ENTITY
            )
        );
    }
}
