<?php

namespace App\Http\Requests\V1\ActivityMembership;

use App\Enums\ActivityMembershipStatusEnum;
use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreActivityMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'childId' => [
                'required',
                'integer',
                'exists:children,id',
            ],

            'pricingPlanId' => [
                'required',
                'integer',
                'exists:activity_pricing_plans,id',
            ],

            'startDate' => [
                'required',
                'date_format:Y-m-d',
            ],

            'status' => [
                'sometimes',
                Rule::enum(ActivityMembershipStatusEnum::class),
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
