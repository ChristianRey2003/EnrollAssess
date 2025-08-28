<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BulkAdmissionDecisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('department-head');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'interview_ids' => [
                'required',
                'array',
                'min:1'
            ],
            'interview_ids.*' => [
                'required',
                'integer',
                'exists:interviews,interview_id'
            ],
            'decision' => [
                'required',
                'string',
                'in:admit,reject,pending'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'interview_ids.required' => 'Please select at least one interview to process.',
            'interview_ids.array' => 'Invalid interview selection format.',
            'interview_ids.min' => 'You must select at least one interview.',
            'interview_ids.*.exists' => 'One or more selected interviews do not exist.',
            'decision.required' => 'Please specify an admission decision.',
            'decision.in' => 'Invalid admission decision. Must be admit, reject, or pending.',
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
            'interview_ids' => 'interview selection',
            'decision' => 'admission decision',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
