<?php

namespace App\Http\Requests\V1\ActivityPricingPlan;

use App\Enums\ActivityPricingTypeEnum;
use App\Enums\ActivityStatusEnum;
use App\Enums\DurationUnitEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateActivityPricingPlanRequest extends FormRequest
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

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'sometimes',
                'required',
                Rule::enum(ActivityPricingTypeEnum::class),
            ],

            'price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            'durationValue' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],

            'durationUnit' => [
                'sometimes',
                'nullable',
                Rule::enum(DurationUnitEnum::class),
            ],

            'sessionsCount' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],

            'status' => [
                'sometimes',
                Rule::enum(ActivityStatusEnum::class),
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
