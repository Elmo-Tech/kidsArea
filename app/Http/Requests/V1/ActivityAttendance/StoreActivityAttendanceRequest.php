<?php

namespace App\Http\Requests\V1\ActivityAttendance;

use App\Enums\ActivityAttendanceStatusEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreActivityAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activitySessionId' => [
                'required',
                'integer',
                'exists:activity_sessions,id',
            ],

            'childId' => [
                'required',
                'integer',
                'exists:children,id',
            ],

            'activityMembershipId' => [
                'nullable',
                'integer',
                'exists:activity_memberships,id',
            ],

            'checkInAt' => [
                'nullable',
                'date_format:H:i',
            ],

            'checkOutAt' => [
                'nullable',
                'date_format:H:i',
                'after:checkInAt',
            ],

            'status' => [
                'required',
                Rule::enum(ActivityAttendanceStatusEnum::class),
            ],

            'notes' => [
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
