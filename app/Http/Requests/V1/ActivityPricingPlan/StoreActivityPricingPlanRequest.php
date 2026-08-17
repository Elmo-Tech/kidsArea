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

class StoreActivityPricingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'activityId' => [
                'required',
                'integer',
                'exists:activities,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                Rule::enum(ActivityPricingTypeEnum::class),
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'durationValue' => [
                Rule::requiredIf(
                    (int) $this->input('type') ===
                    ActivityPricingTypeEnum::SUBSCRIPTION->value
                ),
                'nullable',
                'integer',
                'min:1',
            ],

            'durationUnit' => [
                Rule::requiredIf(
                    (int) $this->input('type') ===
                    ActivityPricingTypeEnum::SUBSCRIPTION->value
                ),
                'nullable',
                Rule::enum(DurationUnitEnum::class),
            ],

            'sessionsCount' => [
                Rule::requiredIf(
                    (int) $this->input('type') ===
                    ActivityPricingTypeEnum::PACKAGE->value
                ),
                'nullable',
                'integer',
                'min:1',
            ],

            'status' => [
                'sometimes',
                Rule::enum(ActivityStatusEnum::class),
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
