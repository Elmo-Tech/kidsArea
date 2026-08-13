<?php

namespace App\Http\Requests\V1\Payroll;

use App\Enums\HttpStatusCode;
use App\Enums\LeavePayrollEffectEnum;
use App\Enums\SalaryTypeEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreLeavePayrollPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leaveTypeId' => [
                'required',
                'integer',
                'exists:leave_types,id',
            ],

            'salaryType' => [
                'required',
                Rule::enum(SalaryTypeEnum::class),
            ],

            'effect' => [
                'required',
                Rule::enum(LeavePayrollEffectEnum::class),
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
