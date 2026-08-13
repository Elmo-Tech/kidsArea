<?php

namespace App\Http\Requests\V1\Payroll;

use App\Enums\HttpStatusCode;
use App\Enums\PayrollDeductionMethodEnum;
use App\Enums\PayrollProrationMethodEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePayrollSettingRequest extends FormRequest
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

            'lateDeductionMethod' => [
                'sometimes',
                'required',
                Rule::enum(PayrollDeductionMethodEnum::class),
            ],

            'earlyLeaveDeductionMethod' => [
                'sometimes',
                'required',
                Rule::enum(PayrollDeductionMethodEnum::class),
            ],

            'absenceDeductionMethod' => [
                'sometimes',
                'required',
                Rule::enum(PayrollDeductionMethodEnum::class),
            ],

            'blockFinalizeOnIncompleteAttendance' => [
                'sometimes',
                'required',
                'boolean',
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
