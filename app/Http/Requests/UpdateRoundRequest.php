<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoundRequest extends FormRequest
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
        $roundId = $this->route('roundId');

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
                })->ignore($roundId),
            ],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'display_order' => [
                'required',
                'integer',
                'min:0',
                Rule::unique('rounds')->where(function ($query) use ($pageantId) {
                    return $query->where('pageant_id', $pageantId);
                })->ignore($roundId),
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
            'type.max' => 'The round type must not exceed 100 characters.',
            'identifier.unique' => 'This identifier is already used for another round.',
            'weight.min' => 'The weight must be at least 1.',
            'weight.max' => 'The weight cannot exceed 100.',
            'display_order.min' => 'The display order cannot be negative.',
            'display_order.unique' => 'This display order is already in use by another round in this pageant.',
            'top_n_proceed.min' => 'At least 1 contestant must proceed.',
        ];
    }
}
