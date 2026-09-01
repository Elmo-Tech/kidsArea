<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\CashTransaction;

use App\Enums\CashTransactionTypeEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreCashTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cashShiftId' => [
                'required',
                'integer',
                'exists:cash_shifts,id',
            ],

            'type' => [
                'required',
                Rule::enum(CashTransactionTypeEnum::class),
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'transactionAt' => [
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

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                '',
                $validator->errors()->toArray(),
                HttpStatusCode::UNPROCESSABLE_ENTITY
            )
        );
    }
}
