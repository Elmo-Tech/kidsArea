<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Child;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateChildRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'birthDate' => [
                'sometimes',
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'gender' => [
                'sometimes',
                'nullable',
                'integer',
            ],

            'notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],

            'guardianName' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'guardianPhone' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],

            'guardianRelation' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'guardianEmail' => [
                'sometimes',
                'nullable',
                'email',
                'max:150',
            ],

            'guardianNotes' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    protected function failedValidation(
        Validator $validator
    ): void {
        throw new HttpResponseException(
            ApiResponse::error(
                '',
                $validator->errors()->toArray(),
                HttpStatusCode::UNPROCESSABLE_ENTITY
            )
        );
    }
}
