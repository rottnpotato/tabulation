<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinalScoreConfigRequest extends FormRequest
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
            'final_score_mode' => ['required', 'string', 'in:fresh,inherit'],
            'final_score_inheritance' => ['required_if:final_score_mode,inherit', 'nullable', 'array'],
            'final_score_inheritance.*' => ['numeric', 'min:0', 'max:100'],
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
            'final_score_mode.required' => 'Please select a final score mode.',
            'final_score_mode.in' => 'The final score mode must be either "fresh" or "inherit".',
            'final_score_inheritance.required_if' => 'Please configure inheritance percentages when using inherit mode.',
            'final_score_inheritance.*.numeric' => 'Each percentage must be a number.',
            'final_score_inheritance.*.min' => 'Percentages cannot be negative.',
            'final_score_inheritance.*.max' => 'Percentages cannot exceed 100.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('final_score_mode') === 'inherit') {
                $inheritance = $this->input('final_score_inheritance', []);
                
                if (is_array($inheritance) && !empty($inheritance)) {
                    $total = array_sum($inheritance);
                    
                    // Allow a small tolerance for floating point issues
                    if (abs($total - 100) > 0.01) {
                        $validator->errors()->add(
                            'final_score_inheritance',
                            "The inheritance percentages must sum to exactly 100%. Current total: {$total}%"
                        );
                    }
                }
            }
        });
    }
}
