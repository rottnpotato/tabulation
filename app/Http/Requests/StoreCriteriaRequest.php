<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCriteriaRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'weight' => ['required', 'integer', 'min:0', 'max:100'],
            'min_score' => ['nullable', 'numeric', 'min:0'],
            'max_score' => ['nullable', 'numeric', 'gt:min_score'],
            'allow_decimals' => ['nullable', 'boolean'],
            'decimal_places' => ['nullable', 'integer', 'min:0', 'max:5'],
            'segment_id' => ['nullable', 'integer', 'exists:segments,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
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
            'name.required' => 'The criterion name is required.',
            'name.max' => 'The criterion name must not exceed 255 characters.',
            'description.max' => 'The description must not exceed 1000 characters.',
            'weight.required' => 'The weight is required.',
            'weight.integer' => 'The weight must be a whole number.',
            'weight.min' => 'The weight must be at least 0.',
            'weight.max' => 'The weight cannot exceed 100.',
            'min_score.numeric' => 'The minimum score must be a number.',
            'min_score.min' => 'The minimum score cannot be negative.',
            'max_score.numeric' => 'The maximum score must be a number.',
            'max_score.gt' => 'The maximum score must be greater than the minimum score.',
            'decimal_places.integer' => 'Decimal places must be a whole number.',
            'decimal_places.min' => 'Decimal places cannot be negative.',
            'decimal_places.max' => 'Decimal places cannot exceed 5.',
            'segment_id.exists' => 'The selected segment does not exist.',
            'category_id.exists' => 'The selected category does not exist.',
        ];
    }
}
