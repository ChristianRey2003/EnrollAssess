<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for creating new applicants
 * 
 * Centralizes validation rules and error messages
 * for applicant creation operations.
 */
class StoreApplicantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'department-head';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255|min:2',
            'email_address' => 'required|email|max:255|unique:applicants,email_address',
            'phone_number' => 'nullable|string|max:20|regex:/^[\d\s\-\+\(\)]+$/',
            'address' => 'nullable|string|max:500',
            'education_background' => 'nullable|string|max:255',
            'exam_set_id' => 'nullable|exists:exam_sets,exam_set_id',
            'generate_access_code' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required.',
            'full_name.min' => 'Full name must be at least 2 characters.',
            'email_address.required' => 'Email address is required.',
            'email_address.email' => 'Please provide a valid email address.',
            'email_address.unique' => 'This email address is already registered.',
            'phone_number.regex' => 'Please provide a valid phone number.',
            'exam_set_id.exists' => 'The selected exam set is invalid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'full name',
            'email_address' => 'email address',
            'phone_number' => 'phone number',
            'education_background' => 'education background',
            'exam_set_id' => 'exam set',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email_address' => strtolower(trim($this->email_address)),
            'full_name' => trim($this->full_name),
            'phone_number' => $this->phone_number ? preg_replace('/[^\d\-\+\(\)\s]/', '', $this->phone_number) : null,
        ]);
    }
}
