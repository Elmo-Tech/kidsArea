<?php

namespace App\Http\Requests\V1\ActivitySession;

use App\Enums\ActivitySessionStatusEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateActivitySessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activityId' => [
                'sometimes',
                'required',
                'integer',
                'exists:activities,id',
            ],

            'activityScheduleId' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:activity_schedules,id',
            ],

            'sessionDate' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
            ],

            'startTime' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],

            'endTime' => [
                'sometimes',
                'required',
                'date_format:H:i',
            ],

            'title' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'employeeIds' => [
                'sometimes',
                'array',
            ],

            'employeeIds.*' => [
                'required',
                'integer',
                'distinct',
                'exists:employees,id',
            ],

            'status' => [
                'sometimes',
                Rule::enum(ActivitySessionStatusEnum::class),
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
