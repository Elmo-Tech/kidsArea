<?php

namespace App\Http\Requests\V1\EmployeeContract;

use App\Enums\ContractStatusEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateEmployeeContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'endDate' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'requiredMonthlyHours' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],

            'workStartTime' => [
                'sometimes',
                'nullable',
                'date_format:H:i',
            ],

            'workEndTime' => [
                'sometimes',
                'nullable',
                'date_format:H:i',
            ],

            'workDays' => [
                'sometimes',
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
                'sometimes',
                'required',
                Rule::enum(ContractStatusEnum::class),
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

    public function messages(): array
    {
        return [
            'endDate.date' => __('validation.custom.endDate.date'),

            'requiredMonthlyHours.numeric' =>
                __('validation.custom.requiredMonthlyHours.numeric'),

            'workStartTime.date_format' =>
                __('validation.custom.workStartTime.date_format'),

            'workEndTime.date_format' =>
                __('validation.custom.workEndTime.date_format'),

            'workDays.array' =>
                __('validation.custom.workDays.array'),

            'status.required' =>
                __('validation.custom.status.required'),

            'notes.string' =>
                __('validation.custom.notes.string'),

            'notes.max' =>
                __('validation.custom.notes.max'),
        ];
    }
}
