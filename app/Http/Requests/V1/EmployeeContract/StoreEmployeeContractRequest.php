<?php

namespace App\Http\Requests\V1\EmployeeContract;

use App\Enums\ContractStatusEnum;
use App\Enums\HttpStatusCode;
use App\Enums\SalaryTypeEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreEmployeeContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'startDate' => ['required', 'date'],
            'endDate' => [
                'nullable',
                'date',
                'after_or_equal:startDate',
            ],
            'salaryType' => [
                'required',
                Rule::enum(SalaryTypeEnum::class),
            ],
            'basicSalary' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf(
                    in_array($this->salaryType, [
                        SalaryTypeEnum::MONTHLY->value,
                        SalaryTypeEnum::MONTHLY_PLUS_SESSION->value,
                    ], true)
                ),
            ],

            'hourlyRate' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf(
                    $this->salaryType === SalaryTypeEnum::HOURLY->value
                ),
            ],

            'sessionRate' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf(
                    in_array($this->salaryType, [
                        SalaryTypeEnum::SESSION->value,
                        SalaryTypeEnum::MONTHLY_PLUS_SESSION->value,
                    ], true)
                ),
            ],

            'requiredMonthlyHours' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'workStartTime' => [
                'nullable',
                'date_format:H:i',
            ],

            'workEndTime' => [
                'nullable',
                'date_format:H:i',
            ],

            'workDays' => [
                'nullable',
                'array',
            ],

            'workDays.*' => [
                'string',
                Rule::in([
                    'saturday',
                    'sunday',
                    'monday',
                    'tuesday',
                    'wednesday',
                    'thursday',
                    'friday',
                ]),
            ],

            'status' => [
                'required',
                Rule::enum(ContractStatusEnum::class),
            ],

            'notes' => [
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

    public function messages(): array
    {
        return [
            'startDate.required' => __('validation.custom.startDate.required'),

            'endDate.after_or_equal' => __('validation.custom.endDate.after_or_equal'),

            'salaryType.required' => __('validation.custom.salaryType.required'),

            'basicSalary.required' => __('validation.custom.basicSalary.required'),

            'hourlyRate.required' => __('validation.custom.hourlyRate.required'),

            'sessionRate.required' => __('validation.custom.sessionRate.required'),
        ];
    }
}
