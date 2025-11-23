<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoundRequest extends FormRequest
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
        $pageantId = $this->route('pageantId');

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', 'max:100'],
            'identifier' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('rounds')->where(function ($query) use ($pageantId) {
                    return $query->where('pageant_id', $pageantId);
                }),
            ],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'display_order' => [
                'required',
                'integer',
                'min:0',
                Rule::unique('rounds')->where(function ($query) use ($pageantId) {
                    return $query->where('pageant_id', $pageantId);
                }),
            ],
            'top_n_proceed' => ['nullable', 'integer', 'min:1'],
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
            'name.required' => 'The round name is required.',
            'name.max' => 'The round name must not exceed 255 characters.',
            'description.max' => 'The description must not exceed 1000 characters.',
            'type.required' => 'The round type is required.',
            'type.max' => 'The round type must not exceed 100 characters.',
            'identifier.max' => 'The identifier must not exceed 50 characters.',
            'identifier.unique' => 'This identifier is already used for another round in this pageant.',
            'weight.required' => 'The weight is required.',
            'weight.min' => 'The weight must be at least 1.',
            'weight.max' => 'The weight cannot exceed 100.',
            'display_order.required' => 'The display order is required.',
            'display_order.min' => 'The display order cannot be negative.',
            'display_order.unique' => 'This display order is already in use by another round in this pageant.',
            'top_n_proceed.min' => 'At least 1 contestant must proceed.',
        ];
    }
}
