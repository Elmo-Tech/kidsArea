<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Report;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExpenseReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'expenseCategoryId' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'cashRegisterId' => ['nullable', 'integer', 'exists:cash_registers,id'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors()->toArray(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }
}
