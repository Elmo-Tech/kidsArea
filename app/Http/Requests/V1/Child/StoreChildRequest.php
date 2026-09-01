<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Child;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreChildRequest extends FormRequest
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
                'max:150',
            ],

            'birthDate' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'gender' => [
                'nullable',
                'integer',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'guardianName' => [
                'required',
                'string',
                'max:150',
            ],

            'guardianPhone' => [
                'required',
                'string',
                'max:50',
            ],

            'guardianRelation' => [
                'nullable',
                'string',
                'max:100',
            ],

            'guardianEmail' => [
                'nullable',
                'email',
                'max:150',
            ],

            'guardianNotes' => [
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
