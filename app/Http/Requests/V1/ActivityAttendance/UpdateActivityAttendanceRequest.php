<?php

namespace App\Http\Requests\V1\ActivityAttendance;

use App\Enums\ActivityAttendanceStatusEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateActivityAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activityMembershipId' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:activity_memberships,id',
            ],

            'checkInAt' => [
                'sometimes',
                'nullable',
                'date_format:H:i',
            ],

            'checkOutAt' => [
                'sometimes',
                'nullable',
                'date_format:H:i',
                'after:checkInAt',
            ],

            'status' => [
                'sometimes',
                'required',
                Rule::enum(ActivityAttendanceStatusEnum::class),
            ],

            'notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
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
