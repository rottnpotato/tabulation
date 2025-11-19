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
            'type' => ['required', 'string', Rule::in(['semi-final', 'final'])],
            'identifier' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('rounds')->where(function ($query) use ($pageantId) {
                    return $query->where('pageant_id', $pageantId);
                })->ignore($roundId),
            ],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'display_order' => ['required', 'integer', 'min:0'],
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
            'type.in' => 'The round type must be either semi-final or final.',
            'identifier.unique' => 'This identifier is already used for another round.',
            'weight.min' => 'The weight must be at least 1.',
            'weight.max' => 'The weight cannot exceed 100.',
            'display_order.min' => 'The display order cannot be negative.',
        ];
    }
}
