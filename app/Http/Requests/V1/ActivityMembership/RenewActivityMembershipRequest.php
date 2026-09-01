<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\ActivityMembership;

use App\Enums\HttpStatusCode;
use App\Enums\PaymentMethodEnum;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class RenewActivityMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pricingPlanId' => ['nullable', 'integer', 'exists:activity_pricing_plans,id'],
            'startDate' => ['nullable', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:5000'],

            'amount' => ['nullable', 'numeric', 'gt:0'],
            'paymentMethod' => ['required_with:amount', 'nullable', Rule::enum(PaymentMethodEnum::class)],
            'cashShiftId' => [
                Rule::requiredIf(fn (): bool =>
                    $this->filled('amount')
                    && (int) $this->input('paymentMethod') === PaymentMethodEnum::CASH->value
                ),
                'nullable',
                'integer',
                'exists:cash_shifts,id',
            ],
            'paidAt' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'reference' => ['nullable', 'string', 'max:255'],
            'paymentNotes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors()->toArray(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }
}
