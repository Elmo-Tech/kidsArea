<?php

namespace App\Http\Requests\V1\Payroll;

use App\Enums\HttpStatusCode;
use App\Enums\PayrollProrationMethodEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePayrollPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prorationMethod' => [
                'sometimes',
                'required',
                Rule::enum(PayrollProrationMethodEnum::class),
            ],

            'notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
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
