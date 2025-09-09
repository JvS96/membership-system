<?php

namespace App\Http\Requests;

use App\Rules\SouthAfricanCellphone;
use App\Rules\SouthAfricanId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_number' => [
                'required',
                'string',
                'size:13',
                new SouthAfricanId(),
                'unique:members,id_number'
            ],
            'first_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'unique:members,email'
            ],
            'cellphone' => [
                'required',
                'string',
                new SouthAfricanCellphone(),
                'unique:members,cellphone'
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive', 'suspended'])
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'id_number.required' => 'The ID number is required.',
            'id_number.size' => 'The ID number must be exactly 13 digits.',
            'id_number.unique' => 'This ID number is already registered.',
            'first_name.regex' => 'The first name may only contain letters and spaces.',
            'last_name.regex' => 'The last name may only contain letters and spaces.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'cellphone.unique' => 'This cellphone number is already registered.',
            'status.in' => 'The status must be one of: active, inactive, suspended.'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->id_number) {
                // Additional business rule validation can be added here
                // For example, age restrictions based on date of birth
            }
        });
    }
}
