<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for updating existing applicants
 * 
 * Centralizes validation rules and error messages
 * for applicant update operations.
 */
class UpdateApplicantRequest extends FormRequest
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
        $applicantId = $this->route('id');

        return [
            'first_name' => 'required|string|max:255|min:2',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255|min:2',
            'preferred_course' => 'nullable|string|max:255',
            'email_address' => [
                'required',
                'email',
                'max:255',
                Rule::unique('applicants', 'email_address')->ignore($applicantId, 'applicant_id')
            ],
            'phone_number' => 'nullable|string|max:20|regex:/^[\d\s\-\+\(\)]+$/',
            'exam_set_id' => 'nullable|exists:exam_sets,exam_set_id',
            'status' => 'required|in:pending,exam-completed,interview-scheduled,interview-completed,admitted,rejected',
            'score' => 'nullable|numeric|min:0|max:100',
            'verbal_description' => 'nullable|string|max:255',
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
            'first_name.required' => 'First name is required.',
            'first_name.min' => 'First name must be at least 2 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'email_address.required' => 'Email address is required.',
            'email_address.email' => 'Please provide a valid email address.',
            'email_address.unique' => 'This email address is already registered to another applicant.',
            'phone_number.regex' => 'Please provide a valid phone number.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'score.numeric' => 'Score must be a number.',
            'score.max' => 'Score cannot exceed 100.',
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
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'last name',
            'preferred_course' => 'preferred course',
            'email_address' => 'email address',
            'phone_number' => 'phone number',
            'exam_set_id' => 'exam set',
            'verbal_description' => 'verbal description',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email_address' => strtolower(trim($this->email_address)),
            'first_name' => trim($this->first_name ?? ''),
            'middle_name' => trim($this->middle_name ?? ''),
            'last_name' => trim($this->last_name ?? ''),
            'preferred_course' => trim($this->preferred_course ?? ''),
            'phone_number' => $this->phone_number ? preg_replace('/[^\d\-\+\(\)\s]/', '', $this->phone_number) : null,
        ]);
    }
}
