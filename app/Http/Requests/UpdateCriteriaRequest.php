<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCriteriaRequest extends FormRequest
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
            'display_order' => ['nullable', 'integer', 'min:0'],
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
            'weight.min' => 'The weight must be at least 0.',
            'weight.max' => 'The weight cannot exceed 100.',
            'max_score.gt' => 'The maximum score must be greater than the minimum score.',
            'decimal_places.max' => 'Decimal places cannot exceed 5.',
            'display_order.min' => 'Display order cannot be negative.',
        ];
    }
}
