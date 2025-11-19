<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScoringSystemRequest extends FormRequest
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
            'scoring_system' => ['required', 'string', Rule::in(['percentage', '1-10', '1-5', 'points'])],
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
            'scoring_system.required' => 'Please select a scoring system.',
            'scoring_system.in' => 'The selected scoring system is invalid. Please choose from: percentage, 1-10, 1-5, or points.',
        ];
    }
}
