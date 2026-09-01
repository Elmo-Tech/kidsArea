<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\ActivitySessionEmployeeAttendance;

use App\Enums\ActivitySessionEmployeeAttendanceStatusEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateActivitySessionEmployeeAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checkInAt' => [
                'sometimes',
                'nullable',
                'date_format:Y-m-d H:i:s',
            ],

            'checkOutAt' => [
                'sometimes',
                'nullable',
                'date_format:Y-m-d H:i:s',
                'after:checkInAt',
            ],

            'status' => [
                'sometimes',
                'required',
                Rule::enum(ActivitySessionEmployeeAttendanceStatusEnum::class),
            ],

            'notes' => [
                'sometimes',
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
