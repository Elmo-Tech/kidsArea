<?php

namespace App\Http\Requests\V1\EmployeePermission;

use App\Enums\EmployeePermissionTypeEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreEmployeePermissionRequest extends FormRequest
{
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
            'employeeId' => [
                'required',
                'integer',
                'exists:employees,id',
            ],
            'permissionDate' => [
                'required',
                'date',
            ],

            'type' => [
                'required',
                Rule::enum(EmployeePermissionTypeEnum::class),
            ],

            'fromTime' => [
                'required',
                'date_format:H:i',
            ],

            'toTime' => [
                'required',
                'date_format:H:i',
                'after:fromTime',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:500',
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
            'employeeId.required' =>
                __('validation.custom.employeeId.required'),
            'permissionDate.required' =>
                __('validation.custom.permissionDate.required'),

            'type.required' =>
                __('validation.custom.permissionType.required'),

            'fromTime.date_format' =>
                __('validation.custom.fromTime.date_format'),
            'fromTime.required' =>
                __('validation.custom.fromTime.required'),

            'toTime.date_format' =>
                __('validation.custom.toTime.date_format'),
            'toTime.required' =>
                __('validation.custom.toTime.required'),

            'toTime.after' =>
                __('validation.custom.toTime.after'),
        ];
    }
}
