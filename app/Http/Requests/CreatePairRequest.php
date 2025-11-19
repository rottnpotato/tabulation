<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePairRequest extends FormRequest
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
            'member_ids' => ['required', 'array', 'size:2'],
            'member_ids.*' => ['required', 'integer', 'distinct', 'exists:contestants,id'],
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
            'member_ids.required' => 'Please select two contestants to create a pair.',
            'member_ids.array' => 'Invalid member selection format.',
            'member_ids.size' => 'You must select exactly two contestants to create a pair.',
            'member_ids.*.required' => 'All member IDs are required.',
            'member_ids.*.integer' => 'Each member ID must be a valid number.',
            'member_ids.*.distinct' => 'You cannot select the same contestant twice.',
            'member_ids.*.exists' => 'One or more selected contestants do not exist.',
        ];
    }
}
