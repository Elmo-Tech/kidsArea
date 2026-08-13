<?php

namespace App\Http\Requests\V1\LeaveType;

use App\Enums\HttpStatusCode;
use App\Enums\StatusEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:leave_types,name',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'sometimes',
                Rule::enum(StatusEnum::class),
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
